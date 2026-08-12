<?php

declare(strict_types=1);

final class PagamentoRepository
{
    public static function create(
        int $offerId,
        int $donorId,
        string $code,
        float $value,
        string $method
    ): int {
        $stmt = Database::connection()->prepare(
            "INSERT INTO pagamentos (
                idOferta,idPalpite,idDoador,codigo,valor,formaPagamento,status
             ) VALUES (
                :o,NULL,:d,:c,:v,:f,'Pendente'
             )"
        );
        $stmt->execute([
            ':o' => $offerId,
            ':d' => $donorId,
            ':c' => $code,
            ':v' => $value,
            ':f' => $method,
        ]);
        return (int)Database::connection()->lastInsertId();
    }

    public static function createForPalpite(
        int $palpiteId,
        int $donorId,
        string $code,
        float $value,
        string $method
    ): int {
        $stmt = Database::connection()->prepare(
            "INSERT INTO pagamentos (
                idOferta,idPalpite,idDoador,codigo,valor,formaPagamento,status
             ) VALUES (
                NULL,:p,:d,:c,:v,:f,'Pendente'
             )"
        );
        $stmt->execute([
            ':p' => $palpiteId,
            ':d' => $donorId,
            ':c' => $code,
            ':v' => $value,
            ':f' => $method,
        ]);
        return (int)Database::connection()->lastInsertId();
    }

    public static function setAsaas(int $id, array $response, array $extra = []): void
    {
        $status = self::localStatus((string)($response['status'] ?? 'PENDING'));
        $financial = self::financialData($response);

        $stmt = Database::connection()->prepare(
            'UPDATE pagamentos SET
                asaasPaymentId=:p,
                asaasStatus=:s,
                invoiceUrl=:u,
                status=:st,
                valorLiquido=COALESCE(:vl,valorLiquido),
                taxa=COALESCE(:tx,taxa),
                pixQrCode=:q,
                pixCopiaCola=:pc,
                pixExpiracao=:pe,
                bankSlipUrl=:bu,
                boletoLinhaDigitavel=:bl,
                dataVencimento=:dv,
                dataPagamento=:dp,
                erro=NULL,
                atualizadoEm=NOW()
             WHERE idPagamento=:id'
        );
        $stmt->execute([
            ':p' => $response['id'] ?? null,
            ':s' => $response['status'] ?? null,
            ':u' => $response['invoiceUrl'] ?? null,
            ':st' => $status,
            ':vl' => $financial['net'],
            ':tx' => $financial['fee'],
            ':q' => $extra['encodedImage'] ?? null,
            ':pc' => $extra['payload'] ?? null,
            ':pe' => self::dateTime($extra['expirationDate'] ?? null),
            ':bu' => $response['bankSlipUrl'] ?? null,
            ':bl' => $extra['identificationField'] ?? null,
            ':dv' => self::dateOnly($response['dueDate'] ?? null),
            ':dp' => $status === 'Pago' ? date('Y-m-d H:i:s') : null,
            ':id' => $id,
        ]);
        self::syncLinkedPalpite($id, $status);
    }


    /**
     * Salva o QR Code depois que a cobrança já foi criada no Asaas.
     */
    public static function setPixData(
        int $id,
        array $pix
    ): void {
        Database::connection()->prepare(
            'UPDATE pagamentos SET
                pixQrCode=:q,
                pixCopiaCola=:p,
                pixExpiracao=:e,
                erro=NULL,
                atualizadoEm=NOW()
             WHERE idPagamento=:id'
        )->execute([
            ':q' => $pix['encodedImage'] ?? null,
            ':p' => $pix['payload'] ?? null,
            ':e' => self::dateTime(
                $pix['expirationDate'] ?? null
            ),
            ':id' => $id,
        ]);
    }

    /**
     * Registra um aviso técnico sem transformar a cobrança em recusada.
     */
    public static function warning(
        int $id,
        string $message
    ): void {
        Database::connection()->prepare(
            'UPDATE pagamentos
             SET erro=:e,atualizadoEm=NOW()
             WHERE idPagamento=:id'
        )->execute([
            ':e' => mb_substr($message, 0, 1000),
            ':id' => $id,
        ]);
    }

    public static function hasAsaasPayment(int $id): bool
    {
        $payment = self::byId($id);

        return $payment
            && trim(
                (string)($payment['asaasPaymentId'] ?? '')
            ) !== '';
    }

    /**
     * Reconcilia um PAYMENT_CREATED que chegou por webhook antes de
     * a resposta do POST /payments ter sido persistida pela aplicação.
     *
     * externalReference do Asaas = pagamentos.codigo.
     */
    public static function linkAsaasByCode(
        string $code,
        array $remotePayment
    ): ?array {
        $code = trim($code);
        $asaasId = trim(
            (string)($remotePayment['id'] ?? '')
        );

        if ($code === '' || $asaasId === '') {
            return null;
        }

        $stmt = Database::connection()->prepare(
            'SELECT *
             FROM pagamentos
             WHERE codigo=:c
             LIMIT 1'
        );
        $stmt->execute([':c' => $code]);
        $local = $stmt->fetch();

        if (!$local) {
            return null;
        }

        $currentAsaasId = trim(
            (string)($local['asaasPaymentId'] ?? '')
        );

        if (
            $currentAsaasId !== ''
            && $currentAsaasId !== $asaasId
        ) {
            return null;
        }

        $status = self::localStatus(
            (string)($remotePayment['status'] ?? 'PENDING')
        );
        $financial = self::financialData($remotePayment);

        Database::connection()->prepare(
            'UPDATE pagamentos SET
                asaasPaymentId=:a,
                asaasStatus=:s,
                invoiceUrl=:i,
                bankSlipUrl=:b,
                valorLiquido=COALESCE(:vl,valorLiquido),
                taxa=COALESCE(:tx,taxa),
                dataVencimento=:d,
                status=:st,
                erro=NULL,
                atualizadoEm=NOW()
             WHERE idPagamento=:id'
        )->execute([
            ':a' => $asaasId,
            ':s' => $remotePayment['status'] ?? null,
            ':i' => $remotePayment['invoiceUrl'] ?? null,
            ':b' => $remotePayment['bankSlipUrl'] ?? null,
            ':vl' => $financial['net'],
            ':tx' => $financial['fee'],
            ':d' => self::dateOnly(
                $remotePayment['dueDate'] ?? null
            ),
            ':st' => $status,
            ':id' => $local['idPagamento'],
        ]);

        self::syncLinkedPalpite(
            (int)$local['idPagamento'],
            $status
        );

        return self::byId(
            (int)$local['idPagamento']
        );
    }

    public static function fail(int $id, string $message): void
    {
        Database::connection()->prepare(
            "UPDATE pagamentos
             SET status='Recusado',erro=:e,atualizadoEm=NOW()
             WHERE idPagamento=:id"
        )->execute([
            ':e' => mb_substr($message, 0, 1000),
            ':id' => $id,
        ]);
        self::syncLinkedPalpite($id, 'Recusado');
    }

    public static function byId(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM pagamentos WHERE idPagamento=:id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }


    public static function detailsById(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT
                p.*,
                COALESCE(o.titulo, pe.titulo) AS titulo,
                COALESCE(o.slug, pe.slug) AS slug,
                COALESCE(o.imagem, pe.imagem) AS imagem,
                CASE
                    WHEN p.idPalpite IS NOT NULL THEN 'Palpite'
                    ELSE 'Oferta'
                END AS tipoOrigem,
                d.nome,
                d.email,
                d.cpf,
                d.telefone,
                pl.palpite AS palpiteTexto,
                pe.equipe_casa,
                pe.equipe_visitante,
                pe.data_jogo,
                pe.status_jogo,
                pe.placar_casa,
                pe.placar_visitante,
                pe.finalizadoEm,
                pe.status_jogo,
                pe.placar_casa,
                pe.placar_visitante,
                pe.finalizadoEm
             FROM pagamentos p
             LEFT JOIN ofertas o
               ON o.idOferta=p.idOferta
             LEFT JOIN palpites pl
               ON pl.idPalpite=p.idPalpite
             LEFT JOIN palpites_eventos pe
               ON pe.idEventoPalpite=pl.idEventoPalpite
             JOIN doadores d
               ON d.idDoador=p.idDoador
             WHERE p.idPagamento=:id
             LIMIT 1"
        );

        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function byCode(string $code): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT
                p.*,
                COALESCE(o.titulo, pe.titulo) AS titulo,
                COALESCE(o.slug, pe.slug) AS slug,
                COALESCE(o.imagem, pe.imagem) AS imagem,
                CASE WHEN p.idPalpite IS NOT NULL THEN 'Palpite' ELSE 'Oferta' END AS tipoOrigem,
                d.nome,d.email,d.cpf,d.telefone,
                pl.palpite AS palpiteTexto,
                pe.equipe_casa,
                pe.equipe_visitante,
                pe.data_jogo
             FROM pagamentos p
             LEFT JOIN ofertas o ON o.idOferta=p.idOferta
             LEFT JOIN palpites pl ON pl.idPalpite=p.idPalpite
             LEFT JOIN palpites_eventos pe ON pe.idEventoPalpite=pl.idEventoPalpite
             JOIN doadores d ON d.idDoador=p.idDoador
             WHERE p.codigo=:c
             LIMIT 1"
        );
        $stmt->execute([':c' => $code]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function byAsaas(string $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM pagamentos WHERE asaasPaymentId=:id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function updateWebhook(
        string $paymentId,
        string $asaasStatus,
        string $event,
        ?string $date = null,
        ?array $remotePayment = null
    ): bool {
        $payment = self::byAsaas($paymentId);
        if (!$payment) {
            return false;
        }

        $status = self::localStatus($asaasStatus, $event);
        $paid = $status === 'Pago'
            ? ($date ?: date('Y-m-d H:i:s'))
            : null;

        $financial = self::financialData(
            $remotePayment ?? []
        );

        Database::connection()->prepare(
            'UPDATE pagamentos SET
                status=:st,
                asaasStatus=:as,
                dataPagamento=:dp,
                valorLiquido=COALESCE(:vl,valorLiquido),
                taxa=COALESCE(:tx,taxa),
                atualizadoEm=NOW()
             WHERE idPagamento=:id'
        )->execute([
            ':st' => $status,
            ':as' => $asaasStatus ?: null,
            ':dp' => $paid,
            ':vl' => $financial['net'],
            ':tx' => $financial['fee'],
            ':id' => $payment['idPagamento'],
        ]);

        if (!empty($payment['idPalpite'])) {
            PalpiteRepository::updatePaymentStatus(
                (int)$payment['idPalpite'],
                $status
            );
        }

        return true;
    }

    public static function localStatus(string $asaasStatus, string $event = ''): string
    {
        $status = strtoupper(trim($asaasStatus));
        $event = strtoupper(trim($event));

        if (in_array($event, [
            'PAYMENT_RECEIVED',
            'PAYMENT_CONFIRMED',
            'PAYMENT_APPROVED_BY_RISK_ANALYSIS'
        ], true)) {
            return 'Pago';
        }

        if (in_array($event, [
            'PAYMENT_CREDIT_CARD_CAPTURE_REFUSED',
            'PAYMENT_REPROVED_BY_RISK_ANALYSIS'
        ], true)) {
            return 'Recusado';
        }

        if (in_array($event, [
            'PAYMENT_REFUNDED',
            'PAYMENT_CHARGEBACK_REQUESTED'
        ], true)) {
            return 'Estornado';
        }

        if ($event === 'PAYMENT_DELETED') {
            return 'Cancelado';
        }

        if ($event === 'PAYMENT_OVERDUE') {
            return 'Vencido';
        }

        return match ($status) {
            'RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH' => 'Pago',
            'REFUNDED', 'REFUND_REQUESTED', 'CHARGEBACK_REQUESTED' => 'Estornado',
            'DELETED' => 'Cancelado',
            'OVERDUE' => 'Vencido',
            'AWAITING_RISK_ANALYSIS', 'PENDING' => 'Pendente',
            default => 'Pendente'
        };
    }

    private static function syncLinkedPalpite(int $paymentId, string $status): void
    {
        $payment = self::byId($paymentId);
        if ($payment && !empty($payment['idPalpite'])) {
            PalpiteRepository::updatePaymentStatus(
                (int)$payment['idPalpite'],
                $status
            );
        }
    }



    private static function financialData(array $remote): array
    {
        $gross = isset($remote['value']) && is_numeric($remote['value'])
            ? round((float)$remote['value'], 2)
            : null;

        $net = isset($remote['netValue']) && is_numeric($remote['netValue'])
            ? round((float)$remote['netValue'], 2)
            : null;

        $fee = null;

        if ($gross !== null && $net !== null) {
            $fee = round(max(0, $gross - $net), 2);
        }

        return [
            'gross' => $gross,
            'net' => $net,
            'fee' => $fee,
        ];
    }

    private static function dateOnly(mixed $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return (new DateTimeImmutable((string)$value))
                ->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }

    private static function dateTime(mixed $value): ?string
    {
        if (!$value) {
            return null;
        }
        try {
            return (new DateTimeImmutable((string)$value))
                ->format('Y-m-d H:i:s');
        } catch (Throwable) {
            return null;
        }
    }
}
