<?php

declare(strict_types=1);

/*
 * Endpoint propositalmente mínimo:
 * não inicia sessão e não cria cookie no carregamento do pixel.
 */
const APP_TIMEZONE = 'America/Sao_Paulo';

date_default_timezone_set(APP_TIMEZONE);

$configFile = dirname(__DIR__)
    . '/config/database.php';

if (is_file($configFile)) {
    require_once $configFile;
    require_once dirname(__DIR__)
        . '/src/Database.php';

    $token = strtolower(
        trim(
            (string)(
                $_GET['token']
                ?? ''
            )
        )
    );

    if (
        preg_match(
            '/^[a-f0-9]{64}$/',
            $token
        )
    ) {
        try {
            Database::connection()->prepare(
                "UPDATE emails_envios
                 SET
                    abertoEm=COALESCE(
                        abertoEm,
                        NOW()
                    ),
                    ultimaAberturaEm=NOW(),
                    totalAberturas=
                        totalAberturas+1
                 WHERE rastreamento_token=:t
                   AND status='Enviado'"
            )->execute([
                ':t' => $token,
            ]);
        } catch (Throwable $e) {
            error_log(
                'Rastreamento de abertura de e-mail: '
                . $e->getMessage()
            );
        }
    }
}

/*
 * Sempre devolve a mesma imagem, inclusive para token inválido,
 * evitando confirmar externamente se um token existe.
 */
header('Content-Type: image/gif');
header('Content-Length: 43');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
header('X-Content-Type-Options: nosniff');

echo base64_decode(
    'R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=='
);
