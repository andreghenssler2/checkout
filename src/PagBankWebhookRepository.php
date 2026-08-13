<?php

declare(strict_types=1);

final class PagBankWebhookRepository
{
    public static function begin(
        string $rawPayload,
        array $payload
    ): ?int {
        $hash = hash('sha256', $rawPayload);

        try {
            $stmt = Database::connection()->prepare(
                'INSERT INTO pagbank_webhook_eventos (
                    hashPayload,
                    orderId,
                    chargeId,
                    referencia,
                    statusPagBank,
                    payload
                 ) VALUES (
                    :h,:o,:c,:r,:s,:p
                 )'
            );

            $stmt->execute([
                ':h' => $hash,
                ':o' =>
                    PagBankPaymentMapper::orderId($payload)
                    ?: null,
                ':c' =>
                    PagBankPaymentMapper::chargeId($payload)
                    ?: null,
                ':r' =>
                    PagBankPaymentMapper::reference($payload)
                    ?: null,
                ':s' =>
                    PagBankPaymentMapper::providerStatus($payload)
                    ?: null,
                ':p' => $rawPayload,
            ]);

            return (int)Database::connection()
                ->lastInsertId();
        } catch (PDOException $e) {
            if ((string)$e->getCode() === '23000') {
                return null;
            }

            throw $e;
        }
    }

    public static function finish(
        int $id,
        ?string $error = null
    ): void {
        Database::connection()->prepare(
            'UPDATE pagbank_webhook_eventos
             SET processadoEm=NOW(),
                 erro=:e
             WHERE id=:id'
        )->execute([
            ':e' => $error !== null
                ? mb_substr($error, 0, 1500)
                : null,
            ':id' => $id,
        ]);
    }
}
