<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

Auth::require();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Support::redirect('/admin/pagamentos/');
}

$id = (int)($_POST['idPagamento'] ?? 0);

try {
    if (!Support::checkCsrf($_POST['_csrf'] ?? null)) {
        throw new RuntimeException('Sessão expirada.');
    }

    $payment = PagamentoRepository::byId($id);

    if (!$payment) {
        throw new RuntimeException(
            'Pagamento não encontrado.'
        );
    }

    $currentAsaasId = trim(
        (string)($payment['asaasPaymentId'] ?? '')
    );

    if ($currentAsaasId !== '') {
        Support::flash(
            'success',
            'Este pagamento já está vinculado ao Asaas.'
        );

        Support::redirect(
            '/admin/pagamentos/'
        );
    }

    $asaas = new AsaasService();

    $remote = $asaas->findPaymentByExternalReference(
        (string)$payment['codigo']
    );

    if (!$remote) {
        throw new RuntimeException(
            'Nenhuma cobrança foi encontrada no Asaas com a referência '
            . (string)$payment['codigo']
            . '.'
        );
    }

    PagamentoRepository::setAsaas(
        $id,
        $remote
    );

    $qrWarning = false;

    if (
        ($payment['formaPagamento'] ?? '') === 'PIX'
        && !in_array(
            PagamentoRepository::localStatus(
                (string)($remote['status'] ?? 'PENDING')
            ),
            ['Pago', 'Cancelado', 'Estornado', 'Vencido'],
            true
        )
    ) {
        try {
            $qr = $asaas->pixQrCode(
                (string)$remote['id']
            );

            PagamentoRepository::setPixData(
                $id,
                $qr
            );
        } catch (Throwable $e) {
            $qrWarning = true;

            PagamentoRepository::warning(
                $id,
                'Cobrança reconciliada com o Asaas, mas o QR Code Pix não está disponível: '
                . $e->getMessage()
            );
        }
    }

    NotificationService::paymentCreated(
        $id
    );

    Support::flash(
        'success',
        $qrWarning
            ? 'Cobrança reconciliada com o Asaas. O QR Code Pix ainda está indisponível.'
            : 'Cobrança reconciliada com o Asaas com sucesso.'
    );
} catch (Throwable $e) {
    Support::flash(
        'error',
        $e->getMessage()
    );
}

Support::redirect(
    '/admin/pagamentos/'
);
