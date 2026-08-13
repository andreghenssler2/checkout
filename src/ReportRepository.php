<?php

declare(strict_types=1);

final class ReportRepository
{
    public static function paymentMethodLabel(string $method): string
    {
        return match ($method) {
            'Cartao' => 'Cartão de Crédito',
            'Boleto' => 'Boleto',
            default => 'PIX',
        };
    }

    public static function offerSummary(array $filters): array
    {
        [$where, $params] = self::offerWhere($filters);

        $stmt = Database::connection()->prepare(
            "SELECT
                COUNT(*) AS quantidade,
                COALESCE(SUM(p.valor),0) AS bruto,
                COALESCE(SUM(CASE
                    WHEN p.valorLiquido IS NOT NULL
                    THEN p.valorLiquido
                    ELSE 0
                END),0) AS liquido,
                COALESCE(SUM(CASE
                    WHEN p.taxa IS NOT NULL
                    THEN p.taxa
                    ELSE 0
                END),0) AS taxas,
                SUM(CASE
                    WHEN p.valorLiquido IS NULL
                    THEN 1 ELSE 0
                END) AS semLiquido
             FROM pagamentos p
             JOIN ofertas o ON o.idOferta=p.idOferta
             {$where}"
        );
        $stmt->execute($params);

        return $stmt->fetch() ?: [];
    }

    public static function offerSeries(array $filters): array
    {
        [$where, $params] = self::offerWhere($filters);

        $group = in_array(
            $filters['agrupar'] ?? 'dia',
            ['dia', 'mes', 'ano'],
            true
        )
            ? (string)$filters['agrupar']
            : 'dia';

        $dateExpr = self::periodExpression($group);

        $stmt = Database::connection()->prepare(
            "SELECT
                {$dateExpr} AS periodo,
                p.formaPagamento,
                COUNT(*) AS quantidade,
                SUM(p.valor) AS bruto,
                SUM(COALESCE(p.valorLiquido,0)) AS liquido,
                SUM(COALESCE(p.taxa,0)) AS taxas,
                SUM(CASE
                    WHEN p.valorLiquido IS NULL
                    THEN 1 ELSE 0
                END) AS semLiquido
             FROM pagamentos p
             JOIN ofertas o ON o.idOferta=p.idOferta
             {$where}
             GROUP BY periodo,p.formaPagamento
             ORDER BY periodo DESC,p.formaPagamento"
        );
        $stmt->execute($params);

        $pivot = [];

        foreach ($stmt->fetchAll() as $row) {
            $period = (string)$row['periodo'];

            if (!isset($pivot[$period])) {
                $pivot[$period] = [
                    'periodo' => $period,
                    'PIX' => 0.0,
                    'Cartao' => 0.0,
                    'Boleto' => 0.0,
                    'bruto' => 0.0,
                    'liquido' => 0.0,
                    'taxas' => 0.0,
                    'quantidade' => 0,
                    'semLiquido' => 0,
                ];
            }

            $method = (string)$row['formaPagamento'];
            $gross = (float)$row['bruto'];

            if (array_key_exists($method, $pivot[$period])) {
                $pivot[$period][$method] += $gross;
            }

            $pivot[$period]['bruto'] += $gross;
            $pivot[$period]['liquido'] += (float)$row['liquido'];
            $pivot[$period]['taxas'] += (float)$row['taxas'];
            $pivot[$period]['quantidade'] += (int)$row['quantidade'];
            $pivot[$period]['semLiquido'] += (int)$row['semLiquido'];
        }

        return array_values($pivot);
    }

    public static function offerByMethod(array $filters): array
    {
        [$where, $params] = self::offerWhere($filters);

        $stmt = Database::connection()->prepare(
            "SELECT
                p.formaPagamento,
                COUNT(*) AS quantidade,
                SUM(p.valor) AS bruto,
                SUM(COALESCE(p.valorLiquido,0)) AS liquido,
                SUM(COALESCE(p.taxa,0)) AS taxas,
                SUM(CASE
                    WHEN p.valorLiquido IS NULL
                    THEN 1 ELSE 0
                END) AS semLiquido
             FROM pagamentos p
             JOIN ofertas o ON o.idOferta=p.idOferta
             {$where}
             GROUP BY p.formaPagamento
             ORDER BY bruto DESC"
        );
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function offerDetails(array $filters): array
    {
        [$where, $params] = self::offerWhere($filters);

        $stmt = Database::connection()->prepare(
            "SELECT
                p.*,
                o.titulo AS ofertaTitulo,
                d.nome,d.email,d.cpf,d.telefone
             FROM pagamentos p
             JOIN ofertas o ON o.idOferta=p.idOferta
             JOIN doadores d ON d.idDoador=p.idDoador
             {$where}
             ORDER BY COALESCE(p.dataPagamento,p.criadoEm) DESC
             LIMIT 2000"
        );
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function palpiteSummary(array $filters): array
    {
        [$where, $params] = self::palpiteWhere(
            $filters,
            true
        );

        $stmt = Database::connection()->prepare(
            "SELECT
                COUNT(*) AS quantidadePagamentos,
                COUNT(DISTINCT pl.idPalpite) AS palpitesPagos,
                COALESCE(SUM(pg.valor),0) AS bruto,
                COALESCE(SUM(CASE
                    WHEN pg.valorLiquido IS NOT NULL
                    THEN pg.valorLiquido
                    ELSE 0
                END),0) AS liquido,
                COALESCE(SUM(CASE
                    WHEN pg.taxa IS NOT NULL
                    THEN pg.taxa
                    ELSE 0
                END),0) AS taxas,
                SUM(CASE
                    WHEN pg.valorLiquido IS NULL
                    THEN 1 ELSE 0
                END) AS semLiquido
             FROM pagamentos pg
             JOIN palpites pl ON pl.idPalpite=pg.idPalpite
             JOIN palpites_eventos pe
               ON pe.idEventoPalpite=pl.idEventoPalpite
             {$where}"
        );
        $stmt->execute($params);

        $summary = $stmt->fetch() ?: [];

        [$allWhere, $allParams] = self::palpiteWhere(
            $filters,
            false
        );

        $stmt = Database::connection()->prepare(
            "SELECT COUNT(DISTINCT pl.idPalpite)
             FROM palpites pl
             JOIN palpites_eventos pe
               ON pe.idEventoPalpite=pl.idEventoPalpite
             LEFT JOIN pagamentos pg
               ON pg.idPalpite=pl.idPalpite
             {$allWhere}"
        );
        $stmt->execute($allParams);

        $summary['totalPalpites'] = (int)$stmt->fetchColumn();

        return $summary;
    }

    public static function palpiteBreakdown(array $filters): array
    {
        [$where, $params] = self::palpiteWhere(
            $filters,
            false
        );

        $stmt = Database::connection()->prepare(
            "SELECT
                pe.idEventoPalpite,
                pe.titulo AS eventoTitulo,
                pl.palpite,
                COUNT(DISTINCT pl.idPalpite) AS participacoes,
                SUM(CASE
                    WHEN pg.status='Pago'
                    THEN 1 ELSE 0
                END) AS pagos,
                COALESCE(SUM(CASE
                    WHEN pg.status='Pago'
                    THEN pg.valor ELSE 0
                END),0) AS bruto,
                COALESCE(SUM(CASE
                    WHEN pg.status='Pago'
                     AND pg.valorLiquido IS NOT NULL
                    THEN pg.valorLiquido ELSE 0
                END),0) AS liquido
             FROM palpites pl
             JOIN palpites_eventos pe
               ON pe.idEventoPalpite=pl.idEventoPalpite
             LEFT JOIN pagamentos pg
               ON pg.idPalpite=pl.idPalpite
             {$where}
             GROUP BY
                pe.idEventoPalpite,
                pe.titulo,
                pl.palpite
             ORDER BY pagos DESC,participacoes DESC,pl.palpite"
        );
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function palpiteDetails(array $filters): array
    {
        [$where, $params] = self::palpiteWhere(
            $filters,
            false
        );

        $stmt = Database::connection()->prepare(
            "SELECT
                pl.*,
                pe.titulo AS eventoTitulo,
                pe.equipe_casa,
                pe.equipe_visitante,
                pe.status_jogo,
                pe.placar_casa,
                pe.placar_visitante,
                pe.finalizadoEm,
                d.nome,d.email,d.cpf,d.telefone,
                pg.codigo,
                pg.valor,
                pg.valorLiquido,
                pg.taxa,
                pg.formaPagamento,
                pg.status AS pagamentoStatus,
                pg.asaasPaymentId,
                pg.dataPagamento,
                pg.criadoEm AS pagamentoCriadoEm
             FROM palpites pl
             JOIN palpites_eventos pe
               ON pe.idEventoPalpite=pl.idEventoPalpite
             JOIN doadores d ON d.idDoador=pl.idDoador
             LEFT JOIN pagamentos pg
               ON pg.idPalpite=pl.idPalpite
             {$where}
             ORDER BY pl.criadoEm DESC
             LIMIT 3000"
        );
        $stmt->execute($params);

        $rows = $stmt->fetchAll();

        /*
         * Classifica cada linha com a mesma regra da tela de acompanhamento.
         * Como um relatório pode conter vários jogos, agrupa e classifica
         * por evento antes de juntar novamente.
         */
        $groups = [];

        foreach ($rows as $row) {
            $eventId = (int)$row['idEventoPalpite'];
            $groups[$eventId][] = $row;
        }

        $result = [];

        foreach ($groups as $eventRows) {
            $first = $eventRows[0];

            $event = [
                'equipe_casa' => $first['equipe_casa'],
                'equipe_visitante' => $first['equipe_visitante'],
                'status_jogo' => $first['status_jogo'],
                'placar_casa' => $first['placar_casa'],
                'placar_visitante' => $first['placar_visitante'],
            ];

            $annotated = PalpiteRepository::annotateEntries(
                $eventRows,
                $event
            );

            foreach ($annotated as $item) {
                $result[] = $item;
            }
        }

        usort(
            $result,
            static function (array $a, array $b): int {
                $winner = ((int)$b['_ganhador'])
                    <=> ((int)$a['_ganhador']);

                if ($winner !== 0) {
                    return $winner;
                }

                $correct = ((int)$b['_acertando'])
                    <=> ((int)$a['_acertando']);

                if ($correct !== 0) {
                    return $correct;
                }

                return strcmp(
                    (string)($b['criadoEm'] ?? ''),
                    (string)($a['criadoEm'] ?? '')
                );
            }
        );

        return $result;
    }

    public static function periodLabel(
        string $period,
        string $group
    ): string {
        try {
            if ($group === 'ano') {
                return $period;
            }

            if ($group === 'mes') {
                $date = DateTimeImmutable::createFromFormat(
                    '!Y-m',
                    $period
                );

                return $date
                    ? $date->format('m/Y')
                    : $period;
            }

            $date = DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $period
            );

            return $date
                ? $date->format('d/m/Y')
                : $period;
        } catch (Throwable) {
            return $period;
        }
    }

    private static function offerWhere(array $filters): array
    {
        $conditions = [
            "p.status='Pago'",
            'p.idOferta IS NOT NULL',
        ];
        $params = [];

        $offerId = (int)($filters['idOferta'] ?? 0);

        if ($offerId > 0) {
            $conditions[] = 'p.idOferta=:oferta';
            $params[':oferta'] = $offerId;
        }

        $method = trim(
            (string)($filters['formaPagamento'] ?? '')
        );

        if (
            in_array(
                $method,
                ['PIX', 'Cartao', 'Boleto'],
                true
            )
        ) {
            $conditions[] = 'p.formaPagamento=:forma';
            $params[':forma'] = $method;
        }

        self::appendDateFilters(
            $conditions,
            $params,
            $filters,
            'COALESCE(p.dataPagamento,p.criadoEm)'
        );

        return [
            'WHERE ' . implode(' AND ', $conditions),
            $params,
        ];
    }

    private static function palpiteWhere(
        array $filters,
        bool $paidOnly
    ): array {
        $conditions = [
            'pl.idPalpite IS NOT NULL',
        ];
        $params = [];

        if ($paidOnly) {
            $conditions[] = "pg.status='Pago'";
        }

        $eventId = (int)($filters['idEventoPalpite'] ?? 0);

        if ($eventId > 0) {
            $conditions[] = 'pl.idEventoPalpite=:evento';
            $params[':evento'] = $eventId;
        }

        $method = trim(
            (string)($filters['formaPagamento'] ?? '')
        );

        if (
            in_array(
                $method,
                ['PIX', 'Cartao'],
                true
            )
        ) {
            $conditions[] = 'pg.formaPagamento=:forma';
            $params[':forma'] = $method;
        }

        self::appendDateFilters(
            $conditions,
            $params,
            $filters,
            'COALESCE(pg.dataPagamento,pg.criadoEm,pl.criadoEm)'
        );

        return [
            'WHERE ' . implode(' AND ', $conditions),
            $params,
        ];
    }

    private static function appendDateFilters(
        array &$conditions,
        array &$params,
        array $filters,
        string $expression
    ): void {
        $start = trim(
            (string)($filters['dataInicio'] ?? '')
        );

        $end = trim(
            (string)($filters['dataFim'] ?? '')
        );

        if (
            preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)
        ) {
            $conditions[] = "{$expression} >= :inicio";
            $params[':inicio'] = $start . ' 00:00:00';
        }

        if (
            preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)
        ) {
            $conditions[] = "{$expression} <= :fim";
            $params[':fim'] = $end . ' 23:59:59';
        }
    }

    private static function periodExpression(
        string $group
    ): string {
        return match ($group) {
            'ano' => "DATE_FORMAT(COALESCE(p.dataPagamento,p.criadoEm),'%Y')",
            'mes' => "DATE_FORMAT(COALESCE(p.dataPagamento,p.criadoEm),'%Y-%m')",
            default => "DATE(COALESCE(p.dataPagamento,p.criadoEm))",
        };
    }
}
