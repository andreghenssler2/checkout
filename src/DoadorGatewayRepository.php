<?php
declare(strict_types=1);

final class DoadorGatewayRepository
{
    public static function set(
        int $donorId,
        string $provider,
        string $customerId
    ): void {
        $provider = trim($provider);
        $customerId = trim($customerId);

        if (
            $donorId <= 0
            || $provider === ''
            || $customerId === ''
        ) {
            return;
        }

        Database::connection()->prepare(
            'INSERT INTO doadores_provedores (
                idDoador,provedor,customerId
             ) VALUES (
                :d,:p,:c
             )
             ON DUPLICATE KEY UPDATE
                customerId=VALUES(customerId),
                atualizadoEm=NOW()'
        )->execute([
            ':d' => $donorId,
            ':p' => $provider,
            ':c' => $customerId,
        ]);
    }

    public static function get(
        int $donorId,
        string $provider
    ): ?string {
        $stmt = Database::connection()->prepare(
            'SELECT customerId
             FROM doadores_provedores
             WHERE idDoador=:d
               AND provedor=:p
             LIMIT 1'
        );

        $stmt->execute([
            ':d' => $donorId,
            ':p' => $provider,
        ]);

        $value = $stmt->fetchColumn();

        return $value !== false
            ? (string)$value
            : null;
    }
}
