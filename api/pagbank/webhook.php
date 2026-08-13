<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2)
    . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'message' => 'method_not_allowed',
    ]);
    exit;
}

$raw = file_get_contents('php://input');

if (!is_string($raw) || $raw === '') {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'message' => 'empty_payload',
    ]);
    exit;
}

$signature = strtolower(
    trim(
        (string)(
            $_SERVER['HTTP_X_AUTHENTICITY_TOKEN']
            ?? ''
        )
    )
);

$validSignature = false;

foreach (PagBankSettings::tokens() as $token) {
    $expected = hash(
        'sha256',
        $token . '-' . $raw
    );

    if (
        $signature !== ''
        && hash_equals(
            strtolower($expected),
            $signature
        )
    ) {
        $validSignature = true;
        break;
    }
}

if (!$validSignature) {
    http_response_code(401);
    echo json_encode([
        'ok' => false,
        'message' => 'invalid_signature',
    ]);
    exit;
}

$payload = json_decode($raw, true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'message' => 'invalid_json',
    ]);
    exit;
}

$eventId = null;

try {
    $eventId = PagBankWebhookRepository::begin(
        $raw,
        $payload
    );

    if ($eventId === null) {
        http_response_code(200);
        echo json_encode([
            'ok' => true,
            'duplicate' => true,
        ]);
        exit;
    }

    $orderId =
        PagBankPaymentMapper::orderId($payload);

    $reference =
        PagBankPaymentMapper::reference($payload);

    $local = $orderId !== ''
        ? PagamentoRepository::byProviderPayment(
            'PagBank',
            $orderId
        )
        : null;

    if (!$local && $reference !== '') {
        $local =
            PagamentoRepository::linkPagBankByCode(
                $reference,
                $payload
            );
    }

    if (!$local) {
        PagBankWebhookRepository::finish(
            $eventId,
            'Pagamento local não encontrado.'
        );

        http_response_code(200);
        echo json_encode([
            'ok' => true,
            'linked' => false,
        ]);
        exit;
    }

    $beforeStatus = (string)(
        $local['status'] ?? 'Pendente'
    );

    PagamentoRepository::setPagBank(
        (int)$local['idPagamento'],
        $payload
    );

    $after = PagamentoRepository::byId(
        (int)$local['idPagamento']
    );

    $afterStatus = (string)(
        $after['status'] ?? $beforeStatus
    );

    if (
        $beforeStatus !== 'Pago'
        && $afterStatus === 'Pago'
    ) {
        NotificationService::paymentApproved(
            (int)$local['idPagamento']
        );
    }

    PagBankWebhookRepository::finish($eventId);

    http_response_code(200);
    echo json_encode([
        'ok' => true,
        'linked' => true,
        'status' => $afterStatus,
    ]);
} catch (Throwable $e) {
    if ($eventId !== null) {
        try {
            PagBankWebhookRepository::finish(
                $eventId,
                $e->getMessage()
            );
        } catch (Throwable) {
        }
    }

    error_log(
        'Webhook PagBank Checkout: '
        . $e->getMessage()
    );

    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'processing_error',
    ]);
}
