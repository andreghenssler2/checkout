<?php

declare(strict_types=1);

final class PagamentoAdminService
{
    public static function normalizeFilters(array $input): array
    {
        $year = trim((string)($input['ano'] ?? ''));
        $date = trim((string)($input['data'] ?? ''));
        $type = trim((string)($input['tipo'] ?? ''));

        if (!preg_match('/^\d{4}$/', $year)) {
            $year = '';
        }

        if (!self::validDate($date)) {
            $date = '';
        }

        if (!in_array($type, ['Oferta', 'Palpite'], true)) {
            $type = '';
        }

        return [
            'ano' => $year,
            'data' => $date,
            'tipo' => $type,
        ];
    }

    public static function years(): array
    {
        $stmt = Database::connection()->query(
            "SELECT DISTINCT
                YEAR(COALESCE(dataPagamento,criadoEm)) AS ano
             FROM pagamentos
             WHERE COALESCE(dataPagamento,criadoEm) IS NOT NULL
             ORDER BY ano DESC"
        );

        return array_values(
            array_filter(
                array_map(
                    static fn (array $row): int =>
                        (int)($row['ano'] ?? 0),
                    $stmt->fetchAll()
                ),
                static fn (int $year): bool =>
                    $year > 0
            )
        );
    }

    public static function items(
        array $filters,
        int $limit = 500
    ): array {
        [$where, $params] = self::where($filters);

        $limit = max(1, min(500, $limit));

        $stmt = Database::connection()->prepare(
            "SELECT
                p.*,
                COALESCE(o.titulo,pe.titulo) AS titulo,
                CASE
                    WHEN p.idPalpite IS NOT NULL THEN 'Palpite'
                    ELSE 'Oferta'
                END AS tipo,
                pl.palpite,
                d.nome,
                d.cpf
             FROM pagamentos p
             LEFT JOIN ofertas o
               ON o.idOferta=p.idOferta
             LEFT JOIN palpites pl
               ON pl.idPalpite=p.idPalpite
             LEFT JOIN palpites_eventos pe
               ON pe.idEventoPalpite=pl.idEventoPalpite
             JOIN doadores d
               ON d.idDoador=p.idDoador
             {$where}
             ORDER BY COALESCE(p.dataPagamento,p.criadoEm) DESC,
                      p.idPagamento DESC
             LIMIT {$limit}"
        );

        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function summary(array $filters): array
    {
        [$where, $params] = self::where($filters);

        $stmt = Database::connection()->prepare(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN p.status='Pago' THEN 1 ELSE 0 END) AS pagos,
                SUM(CASE WHEN p.status='Pendente' THEN 1 ELSE 0 END) AS pendentes,
                SUM(CASE WHEN p.status='Vencido' THEN 1 ELSE 0 END) AS vencidos,
                COALESCE(
                    SUM(
                        CASE
                            WHEN p.status='Pago' THEN p.valor
                            ELSE 0
                        END
                    ),
                    0
                ) AS valorPago
             FROM pagamentos p
             {$where}"
        );

        $stmt->execute($params);

        return $stmt->fetch() ?: [
            'total' => 0,
            'pagos' => 0,
            'pendentes' => 0,
            'vencidos' => 0,
            'valorPago' => 0,
        ];
    }

    /**
     * Sincroniza um lote pequeno dos pagamentos visíveis.
     *
     * A prioridade é:
     * 1. pagamentos pendentes;
     * 2. pagamentos vencidos;
     * 3. pagamentos sem id Asaas.
     *
     * Isso evita consultar centenas de cobranças em cada ciclo do AJAX.
     */
    public static function syncBatch(
        array $filters,
        int $limit = 8
    ): array {
        [$where, $params] = self::where($filters);

        $limit = max(1, min(15, $limit));

        $conditions = $where === ''
            ? 'WHERE '
            : $where . ' AND ';

        $stmt = Database::connection()->prepare(
            "SELECT p.*
             FROM pagamentos p
             {$conditions}
             p.provedor IN ('Asaas','PagBank')
             AND (
                p.status IN ('Pendente','Vencido')
                OR p.provedorPaymentId IS NULL
                OR p.provedorPaymentId=''
             )
             ORDER BY
                CASE
                    WHEN p.status='Pendente' THEN 0
                    WHEN p.status='Vencido' THEN 1
                    ELSE 2
                END,
                COALESCE(p.atualizadoEm,p.criadoEm) ASC,
                p.idPagamento ASC
             LIMIT {$limit}"
        );

        $stmt->execute($params);
        $candidates = $stmt->fetchAll();

        if (!$candidates) {
            return [
                'consultados' => 0,
                'alterados' => 0,
                'erros' => [],
            ];
        }

        $consulted = 0;
        $changed = 0;
        $errors = [];

        foreach ($candidates as $local) {
            $id = (int)$local['idPagamento'];
            $provider = trim(
                (string)($local['provedor'] ?? 'Asaas')
            );

            try {
                $beforeStatus = (string)$local['status'];

                if ($provider === 'Asaas') {
                    $asaas = new AsaasService();

                    $asaasId = trim(
                        (string)(
                            $local['asaasPaymentId']
                            ?? $local['provedorPaymentId']
                            ?? ''
                        )
                    );

                    if ($asaasId === '') {
                        $remote =
                            $asaas->findPaymentByExternalReference(
                                (string)$local['codigo']
                            );

                        if (!$remote) {
                            continue;
                        }

                        PagamentoRepository::linkAsaasByCode(
                            (string)$local['codigo'],
                            $remote
                        );

                        $asaasId = trim(
                            (string)($remote['id'] ?? '')
                        );
                    } else {
                        $remote =
                            $asaas->getPayment($asaasId);
                    }

                    if ($asaasId === '') {
                        continue;
                    }

                    $consulted++;

                    PagamentoRepository::updateWebhook(
                        $asaasId,
                        (string)($remote['status'] ?? 'PENDING'),
                        '',
                        self::remotePaymentDate($remote),
                        $remote
                    );
                } elseif ($provider === 'PagBank') {
                    $orderId = trim(
                        (string)(
                            $local['provedorPaymentId']
                            ?? ''
                        )
                    );

                    if ($orderId === '') {
                        /*
                         * O webhook reconcilia pelo reference_id.
                         * Sem order_id não existe consulta direta segura.
                         */
                        continue;
                    }

                    $pagbank = new PagBankService();
                    $remote =
                        $pagbank->getOrder($orderId);

                    $consulted++;

                    PagamentoRepository::setPagBank(
                        $id,
                        $remote
                    );
                } else {
                    continue;
                }

                $after = PagamentoRepository::byId($id);
                $afterStatus = (string)(
                    $after['status'] ?? $beforeStatus
                );

                if ($afterStatus !== $beforeStatus) {
                    $changed++;
                }

                if (
                    $beforeStatus !== 'Pago'
                    && $afterStatus === 'Pago'
                ) {
                    NotificationService::paymentApproved(
                        $id
                    );
                }
            } catch (Throwable $e) {
                $errors[] = [
                    'idPagamento' => $id,
                    'mensagem' =>
                        $provider . ': ' . $e->getMessage(),
                ];
            }
        }

        return [
            'consultados' => $consulted,
            'alterados' => $changed,
            'erros' => $errors,
        ];
    }

    private static function where(array $filters): array
    {
        $filters = self::normalizeFilters($filters);

        $conditions = [];
        $params = [];

        /*
         * Para pagamentos aprovados usamos dataPagamento.
         * Para os demais, usamos a data em que a cobrança foi registrada.
         */
        $dateExpression = 'COALESCE(p.dataPagamento,p.criadoEm)';

        if ($filters['ano'] !== '') {
            $conditions[] = "YEAR({$dateExpression})=:ano";
            $params[':ano'] = (int)$filters['ano'];
        }

        if ($filters['data'] !== '') {
            $conditions[] = "DATE({$dateExpression})=:data";
            $params[':data'] = $filters['data'];
        }

        if ($filters['tipo'] === 'Oferta') {
            $conditions[] = 'p.idOferta IS NOT NULL';
        } elseif ($filters['tipo'] === 'Palpite') {
            $conditions[] = 'p.idPalpite IS NOT NULL';
        }

        return [
            $conditions
                ? 'WHERE ' . implode(' AND ', $conditions)
                : '',
            $params,
        ];
    }

    private static function validDate(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        $date = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $value
        );

        return $date !== false
            && $date->format('Y-m-d') === $value;
    }

    private static function remotePaymentDate(
        array $remote
    ): ?string {
        $value = trim(
            (string)(
                $remote['paymentDate']
                ?? $remote['confirmedDate']
                ?? $remote['clientPaymentDate']
                ?? ''
            )
        );

        if ($value === '') {
            return null;
        }

        try {
            return (new DateTimeImmutable($value))
                ->format('Y-m-d H:i:s');
        } catch (Throwable) {
            return null;
        }
    }
}
