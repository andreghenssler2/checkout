<?php
require_once dirname(__DIR__, 2) . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

function respond(int $code, array $body): never
{
    http_response_code($code);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['ok' => false]);
}

$expectedTokens = AsaasSettings::webhookTokens();
$received = trim(
    (string)($_SERVER['HTTP_ASAAS_ACCESS_TOKEN'] ?? '')
);

$tokenValid = false;

if ($received !== '') {
    foreach ($expectedTokens as $expectedToken) {
        if (hash_equals($expectedToken, $received)) {
            $tokenValid = true;
            break;
        }
    }
}

if (!$tokenValid) {
    respond(
        401,
        [
            'ok' => false,
            'message' => 'Token inválido',
        ]
    );
}

$raw = file_get_contents('php://input');
$data = json_decode((string)$raw, true);

if (!is_array($data)) {
    respond(400, ['ok' => false]);
}

$eventId = trim((string)($data['id'] ?? ''));
$event = trim((string)($data['event'] ?? ''));

$payment = is_array($data['payment'] ?? null)
    ? $data['payment']
    : [];

$asaasId = trim((string)($payment['id'] ?? ''));

if ($eventId === '' || $event === '') {
    respond(400, ['ok' => false]);
}

/*
 * Este endpoint é usado apenas para cobranças.
 * Eventos de conta/API Key, como ACCESS_TOKEN_CREATED, não alteram
 * dados financeiros do Checkout e são confirmados sem processamento.
 */
if (!str_starts_with($event, 'PAYMENT_')) {
    respond(
        200,
        [
            'ok' => true,
            'ignored' => true,
            'event' => $event,
        ]
    );
}

$db = Database::connection();
$localPaymentId = 0;
$localStatus = '';

try {
    $db->beginTransaction();

    $stmt = $db->prepare(
        'INSERT IGNORE INTO asaas_webhook_eventos (
            eventoId,
            evento,
            asaasPaymentId,
            payload
         ) VALUES (
            :i,
            :e,
            :p,
            :r
         )'
    );

    $stmt->execute([
        ':i' => $eventId,
        ':e' => $event,
        ':p' => $asaasId !== '' ? $asaasId : null,
        ':r' => $raw,
    ]);

    if ($stmt->rowCount() === 0) {
        $db->commit();
        respond(
            200,
            [
                'ok' => true,
                'duplicate' => true,
            ]
        );
    }

    if ($asaasId !== '') {
        /*
         * O PAYMENT_CREATED pode chegar enquanto o POST /payments
         * ainda está sendo processado pela aplicação.
         *
         * Se o asaasPaymentId ainda não estiver salvo, usamos o
         * externalReference enviado ao Asaas, que corresponde ao
         * pagamentos.codigo, para reconciliar a cobrança.
         */
        $local = PagamentoRepository::byAsaas(
            $asaasId
        );

        if (!$local) {
            $externalReference = trim(
                (string)(
                    $payment['externalReference']
                    ?? ''
                )
            );

            if ($externalReference !== '') {
                $local = PagamentoRepository::linkAsaasByCode(
                    $externalReference,
                    $payment
                );
            }
        }

        if ($local) {
            PagamentoRepository::updateWebhook(
                $asaasId,
                (string)($payment['status'] ?? ''),
                $event,
                (string)(
                    $payment['paymentDate']
                    ?? $payment['confirmedDate']
                    ?? ''
                ),
                $payment
            );

            $local = PagamentoRepository::byAsaas(
                $asaasId
            );

            if ($local) {
                $localPaymentId = (int)$local['idPagamento'];
                $localStatus = (string)$local['status'];
            }
        }
    }

    $db->prepare(
        'UPDATE asaas_webhook_eventos
         SET processadoEm=NOW(),erro=NULL
         WHERE eventoId=:i'
    )->execute([':i' => $eventId]);

    $db->commit();
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    error_log(
        'Webhook Asaas Checkout: '
        . $e->getMessage()
    );

    respond(500, ['ok' => false]);
}

/*
 * E-mail e comprovante são processados após a transação principal.
 * Falhas de e-mail não desfazem a atualização financeira.
 */
if (
    $localPaymentId > 0
    && $localStatus === 'Pago'
) {
    NotificationService::paymentApproved(
        $localPaymentId
    );
}

respond(200, ['ok' => true]);
