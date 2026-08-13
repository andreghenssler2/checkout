<?php

declare(strict_types=1);

final class ComprovanteService
{
    public static function ensureForPayment(int $paymentId): array
    {
        $db = Database::connection();

        $existing = self::byPayment($paymentId);

        if ($existing) {
            return $existing;
        }

        $payment = PagamentoRepository::detailsById($paymentId);

        if (!$payment) {
            throw new RuntimeException('Pagamento não encontrado.');
        }

        if (($payment['status'] ?? '') !== 'Pago') {
            throw new RuntimeException(
                'O comprovante só pode ser emitido após a aprovação do pagamento.'
            );
        }

        $numero = sprintf(
            'CMP-%s-%08d',
            date('Ymd'),
            $paymentId
        );

        $token = rtrim(
            strtr(
                base64_encode(random_bytes(32)),
                '+/',
                '-_'
            ),
            '='
        );

        try {
            $stmt = $db->prepare(
                "INSERT INTO comprovantes (
                    idPagamento,
                    numero,
                    token,
                    emitidoEm
                 ) VALUES (
                    :p,
                    :n,
                    :t,
                    NOW()
                 )"
            );

            $stmt->execute([
                ':p' => $paymentId,
                ':n' => $numero,
                ':t' => $token,
            ]);
        } catch (PDOException $e) {
            $existing = self::byPayment($paymentId);

            if ($existing) {
                return $existing;
            }

            throw $e;
        }

        return self::byPayment($paymentId)
            ?: throw new RuntimeException('Falha ao criar comprovante.');
    }

    public static function byPayment(int $paymentId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM comprovantes WHERE idPagamento=:p LIMIT 1'
        );
        $stmt->execute([':p' => $paymentId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function byToken(string $token): ?array
    {
        $token = trim($token);

        if ($token === '') {
            return null;
        }

        $stmt = Database::connection()->prepare(
            "SELECT
                c.*,
                p.codigo,
                p.valor,
                p.formaPagamento,
                p.status,
                p.asaasPaymentId,
                p.dataPagamento,
                COALESCE(o.titulo, pe.titulo) AS titulo,
                CASE
                    WHEN p.idPalpite IS NOT NULL THEN 'Palpite'
                    ELSE 'Oferta'
                END AS tipoOrigem,
                d.nome,
                d.cpf,
                d.email,
                d.telefone,
                pl.palpite AS palpiteTexto,
                pe.equipe_casa,
                pe.equipe_visitante,
                pe.data_jogo
             FROM comprovantes c
             JOIN pagamentos p
               ON p.idPagamento=c.idPagamento
             JOIN doadores d
               ON d.idDoador=p.idDoador
             LEFT JOIN ofertas o
               ON o.idOferta=p.idOferta
             LEFT JOIN palpites pl
               ON pl.idPalpite=p.idPalpite
             LEFT JOIN palpites_eventos pe
               ON pe.idEventoPalpite=pl.idEventoPalpite
             WHERE c.token=:t
             LIMIT 1"
        );

        $stmt->execute([':t' => $token]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function url(array $receipt): string
    {
        return APP_URL
            . '/comprovante/'
            . rawurlencode((string)$receipt['token']);
    }
}
