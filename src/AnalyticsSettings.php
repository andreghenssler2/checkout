<?php

declare(strict_types=1);

final class AnalyticsSettings
{
    private static ?array $cache = null;

    public static function get(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        try {
            $db = Database::connection();

            $row = $db->query(
                'SELECT *
                 FROM configuracoes_analytics
                 WHERE idConfiguracao=1'
            )->fetch();

            if (!$row) {
                $db->exec(
                    "INSERT INTO configuracoes_analytics (
                        idConfiguracao,
                        ativo,
                        measurement_id
                     ) VALUES (
                        1,
                        0,
                        NULL
                     )"
                );

                $row = $db->query(
                    'SELECT *
                     FROM configuracoes_analytics
                     WHERE idConfiguracao=1'
                )->fetch();
            }

            self::$cache = $row ?: self::defaults();
        } catch (PDOException) {
            /*
             * Evita derrubar as páginas públicas caso os arquivos sejam
             * enviados antes da migration do Analytics.
             */
            self::$cache = self::defaults();
        }

        return self::$cache;
    }

    public static function enabled(): bool
    {
        $settings = self::get();

        return (int)($settings['ativo'] ?? 0) === 1
            && self::isValidMeasurementId(
                (string)($settings['measurement_id'] ?? '')
            );
    }

    public static function measurementId(): string
    {
        $measurementId = strtoupper(
            trim(
                (string)(
                    self::get()['measurement_id']
                    ?? ''
                )
            )
        );

        return self::isValidMeasurementId($measurementId)
            ? $measurementId
            : '';
    }

    public static function save(
        bool $active,
        string $measurementId
    ): void {
        $measurementId = strtoupper(
            trim($measurementId)
        );

        if (
            $measurementId !== ''
            && !self::isValidMeasurementId($measurementId)
        ) {
            throw new InvalidArgumentException(
                'Informe um ID de medição válido do Google Analytics, por exemplo G-XXXXXXXXXX.'
            );
        }

        if ($active && $measurementId === '') {
            throw new InvalidArgumentException(
                'Informe o ID de medição antes de ativar o Google Analytics.'
            );
        }

        try {
            Database::connection()->prepare(
                'UPDATE configuracoes_analytics
                 SET ativo=:ativo,
                     measurement_id=:measurement
                 WHERE idConfiguracao=1'
            )->execute([
                ':ativo' => $active ? 1 : 0,
                ':measurement' => $measurementId !== ''
                    ? $measurementId
                    : null,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException(
                'A tabela de configuração do Google Analytics ainda não existe. Execute a atualização SQL da v1.6.3.',
                0,
                $e
            );
        }

        self::$cache = null;
    }

    public static function isValidMeasurementId(
        string $measurementId
    ): bool {
        return preg_match(
            '/^G-[A-Z0-9]{4,24}$/',
            strtoupper(
                trim($measurementId)
            )
        ) === 1;
    }

    private static function defaults(): array
    {
        return [
            'idConfiguracao' => 1,
            'ativo' => 0,
            'measurement_id' => null,
        ];
    }
}
