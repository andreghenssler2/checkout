<?php

declare(strict_types=1);

final class EmailSettings
{
    public static function get(): array
    {
        $db = Database::connection();

        $row = $db->query(
            'SELECT * FROM configuracoes_email WHERE idConfiguracao = 1'
        )->fetch();

        if (!$row) {
            $db->exec(
                "INSERT INTO configuracoes_email (
                    idConfiguracao,
                    ativo,
                    rastrear_abertura,
                    remetente_nome,
                    remetente_email,
                    reply_to
                 ) VALUES (
                    1,
                    1,
                    1,
                    'IECLB Parobé',
                    'noreply@ieclbparobe.com.br',
                    'secretaria@ieclbparobe.com.br'
                 )"
            );

            $row = $db->query(
                'SELECT * FROM configuracoes_email WHERE idConfiguracao = 1'
            )->fetch();
        }

        return $row ?: [];
    }

    public static function enabled(): bool
    {
        return (int)(self::get()['ativo'] ?? 0) === 1;
    }


    public static function trackingEnabled(): bool
    {
        return (int)(
            self::get()['rastrear_abertura']
            ?? 1
        ) === 1;
    }
}
