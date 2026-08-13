<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Support::redirect('/');
}

$code = trim((string)($_POST['codigo'] ?? ''));
$payment = PagamentoRepository::byCode($code);

if (!$payment) {
    http_response_code(404);
    die('Pagamento não encontrado.');
}

try {
    if (!Support::checkCsrf($_POST['_csrf'] ?? null)) {
        throw new RuntimeException(
            'Sessão expirada. Atualize a página e tente novamente.'
        );
    }

    if (($payment['formaPagamento'] ?? '') !== 'PIX') {
        throw new RuntimeException('Este pagamento não é PIX.');
    }

    if (
        in_array(
            (string)$payment['status'],
            ['Pago','Cancelado','Estornado','Vencido'],
            true
        )
    ) {
        throw new RuntimeException(
            'Este pagamento não permite atualizar o QR Code.'
        );
    }

    $provider = trim(
        (string)($payment['provedor'] ?? 'Asaas')
    );

    $providerPaymentId = trim(
        (string)(
            $payment['provedorPaymentId']
            ?? $payment['asaasPaymentId']
            ?? ''
        )
    );

    if ($providerPaymentId === '') {
        throw new RuntimeException(
            'A cobrança ainda não está vinculada ao provedor.'
        );
    }

    $gateway = PaymentGatewayManager::provider(
        $provider,
        'PIX'
    );

    $qr = $gateway->pixQrCode($providerPaymentId);

    PagamentoRepository::setPixData(
        (int)$payment['idPagamento'],
        $qr
    );

    Support::flash(
        'success',
        'QR Code Pix carregado com sucesso.'
    );
} catch (Throwable $e) {
    if (
        ($payment['provedor'] ?? 'Asaas') === 'Asaas'
        && AsaasSettings::activeEnvironment() === 'sandbox'
        && AsaasService::isPixReceivingDisabledError($e)
    ) {
        PagamentoRepository::warning(
            (int)$payment['idPagamento'],
            '[SANDBOX_PIX_DISABLED] Cobrança criada no Asaas. '
            . 'O recebimento via Pix está desabilitado nesta conta Sandbox. '
            . 'Confirme manualmente a cobrança no painel do Asaas Sandbox para testar o webhook.'
        );

        Support::flash(
            'success',
            'A cobrança de teste já está criada. Nesta conta Sandbox o Asaas não disponibiliza QR Code Pix.'
        );
    } else {
        PagamentoRepository::warning(
            (int)$payment['idPagamento'],
            'A cobrança já existe no provedor, mas o QR Code Pix ainda não está disponível: '
            . $e->getMessage()
        );

        Support::flash(
            'error',
            'A cobrança já existe, mas o QR Code Pix ainda não pôde ser carregado.'
        );
    }
}

Support::redirect('/pagamento/' . $code);
