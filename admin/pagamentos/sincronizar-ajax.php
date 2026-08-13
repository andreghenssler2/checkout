<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

Auth::require();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

function paymentSyncRespond(
    int $status,
    array $payload
): never {
    http_response_code($status);

    echo json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
    );

    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        paymentSyncRespond(
            405,
            [
                'ok' => false,
                'message' => 'Método não permitido.',
            ]
        );
    }

    if (!Support::checkCsrf($_POST['_csrf'] ?? null)) {
        paymentSyncRespond(
            419,
            [
                'ok' => false,
                'message' => 'Sessão expirada. Atualize a página.',
            ]
        );
    }

    $filters = PagamentoAdminService::normalizeFilters(
        $_POST
    );

    $sync = PagamentoAdminService::syncBatch(
        $filters,
        8
    );

    $items = PagamentoAdminService::items(
        $filters
    );

    $summary = PagamentoAdminService::summary(
        $filters
    );

    ob_start();
    require __DIR__ . '/_linhas.php';
    $html = (string)ob_get_clean();

    paymentSyncRespond(
        200,
        [
            'ok' => true,
            'html' => $html,
            'summary' => [
                'total' => (int)($summary['total'] ?? 0),
                'pagos' => (int)($summary['pagos'] ?? 0),
                'pendentes' => (int)($summary['pendentes'] ?? 0),
                'vencidos' => (int)($summary['vencidos'] ?? 0),
                'valorPago' => Support::money(
                    (float)($summary['valorPago'] ?? 0)
                ),
            ],
            'sync' => $sync,
            'atualizadoEm' => date('d/m/Y H:i:s'),
        ]
    );
} catch (Throwable $e) {
    paymentSyncRespond(
        500,
        [
            'ok' => false,
            'message' => $e->getMessage(),
        ]
    );
}
