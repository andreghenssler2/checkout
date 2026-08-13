<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

$result = EmailService::processPending(30);

echo sprintf(
    "[%s] Processados: %d | Enviados: %d | Falhas: %d%s",
    date('d/m/Y H:i:s'),
    $result['processados'],
    $result['enviados'],
    $result['falhas'],
    PHP_EOL
);
