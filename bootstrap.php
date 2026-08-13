<?php

declare(strict_types=1);

const APP_NAME = 'Checkout IECLB Parobé';
const APP_URL = 'https://checkout.ieclbparobe.com.br';
const APP_TIMEZONE = 'America/Sao_Paulo';
const APP_MIN_OFFER = 10.00;

date_default_timezone_set(APP_TIMEZONE);

$configFile = __DIR__ . '/config/database.php';
$isInstaller = str_contains((string)($_SERVER['SCRIPT_NAME'] ?? ''), '/install/');

if (!is_file($configFile)) {
    if (!$isInstaller && PHP_SAPI !== 'cli') {
        header('Location: ' . APP_URL . '/install/');
        exit;
    }
} else {
    require_once $configFile;
}

if (PHP_SAPI !== 'cli') {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Permissions-Policy: camera=(), microphone=(), geolocation=()");

    $forwardedProtoHeader = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    $requestIsHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || $forwardedProtoHeader === 'https'
    );

    if ($requestIsHttps) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    if (!headers_sent()) {
        $forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
        $secure = (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $forwardedProto === 'https'
            || str_starts_with(APP_URL, 'https://')
        );
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

spl_autoload_register(static function (string $class): void {
    $file = __DIR__ . '/src/' . str_replace('\\', '/', $class) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});
