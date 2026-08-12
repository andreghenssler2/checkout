<?php
require_once __DIR__ . '/bootstrap.php';

$code = trim((string)($_GET['codigo'] ?? ''));
$payment = PagamentoRepository::byCode($code);

$sandboxPixDisabled = $payment
    && AsaasSettings::activeEnvironment() === 'sandbox'
    && str_contains(
        (string)($payment['erro'] ?? ''),
        '[SANDBOX_PIX_DISABLED]'
    );

if (!$payment) {
    http_response_code(404);
    die('Pagamento não encontrado.');
}

$isPrediction = ($payment['tipoOrigem'] ?? '') === 'Palpite';

$paymentTitle = match ((string)$payment['formaPagamento']) {
    'PIX' => 'Pagamento via PIX',
    'Boleto' => 'Pagamento via Boleto',
    default => 'Pagamento com Cartão',
};
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Pagamento - Checkout</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css">
    <?= AnalyticsService::renderHead() ?>
</head>
<body class="bg">

<header class="public-head">
    <a href="<?= APP_URL ?>/">
        <strong>Checkout</strong>
        <span>IECLB Parobé</span>
    </a>
</header>

<main class="result-container">
    <div class="panel center">
        <?php foreach (Support::flashes() as $flash): ?>
            <div class="alert <?= Support::e($flash['type']) ?>">
                <?= Support::e($flash['message']) ?>
            </div>
        <?php endforeach; ?>

        <span
            class="badge <?= $payment['status'] === 'Pago' ? 'paid' : 'muted' ?>"
            id="statusBadge"
        >
            <?= Support::e($payment['status']) ?>
        </span>

        <h1><?= Support::e($paymentTitle) ?></h1>

        <p>
            <strong><?= Support::e($payment['titulo']) ?></strong>
        </p>

        <?php if ($isPrediction): ?>
            <div class="prediction-confirmation">
                <small>Seu palpite</small>
                <strong>
                    <?= Support::e($payment['palpiteTexto'] ?? '') ?>
                </strong>
            </div>
        <?php endif; ?>

        <div class="result-value">
            <?= Support::money((float)$payment['valor']) ?>
        </div>

        <?php if (
            $payment['formaPagamento'] === 'PIX'
            && $payment['status'] !== 'Pago'
            && !empty($payment['pixQrCode'])
        ): ?>
            <img
                class="qr"
                src="data:image/png;base64,<?= Support::e($payment['pixQrCode']) ?>"
                alt="QR Code Pix"
            >

            <label>
                PIX Copia e Cola
                <textarea
                    id="pixCode"
                    readonly
                    rows="5"
                ><?= Support::e($payment['pixCopiaCola']) ?></textarea>
            </label>

            <button
                class="btn"
                onclick="navigator.clipboard.writeText(document.getElementById('pixCode').value)"
            >
                Copiar código PIX
            </button>

            <div class="pix-expiration-warning">
                <strong>Validade do Pix:</strong>
                a chave Pix Copia e Cola tem validade de
                <strong>1 hora após a geração</strong>.
            </div>

            <p>
                Após o pagamento, esta página atualizará automaticamente.
            </p>


        <?php elseif (
            $payment['formaPagamento'] === 'PIX'
            && $payment['status'] !== 'Pago'
            && !empty($payment['asaasPaymentId'])
        ): ?>
            <?php if ($sandboxPixDisabled): ?>
                <div class="alert muted pix-charge-created">
                    <strong>Cobrança de teste criada.</strong>
                    <p>
                        Esta conta Sandbox do Asaas está com recebimentos
                        via Pix desabilitados. Por isso não existe QR Code
                        para exibir neste teste.
                    </p>
                    <p>
                        A cobrança já está registrada. Não crie outra.
                    </p>
                </div>

                <div class="sandbox-test-instructions">
                    <strong>Como continuar o teste</strong>
                    <p>
                        No painel do Asaas Sandbox, localize esta cobrança e
                        confirme o pagamento manualmente. O webhook atualizará
                        o status nesta página quando o evento for enviado.
                    </p>
                    <code><?= Support::e($payment['asaasPaymentId'] ?? '') ?></code>
                </div>

                <p class="pix-sandbox-public-note">
                    Esta limitação é do ambiente Sandbox. Em Produção,
                    o Checkout continua usando o QR Code Pix normalmente.
                </p>
            <?php else: ?>
                <div class="alert muted pix-charge-created">
                    <strong>Cobrança criada no Asaas.</strong>
                    <p>
                        O pagamento está registrado, porém o QR Code Pix
                        ainda não pôde ser carregado nesta aplicação.
                    </p>
                    <p>
                        Você não precisa criar outra cobrança.
                    </p>
                </div>

                <form
                    method="post"
                    action="<?= APP_URL ?>/recarregar-pix.php"
                    class="actions pix-retry-actions"
                >
                    <input
                        type="hidden"
                        name="_csrf"
                        value="<?= Support::csrf() ?>"
                    >
                    <input
                        type="hidden"
                        name="codigo"
                        value="<?= Support::e($code) ?>"
                    >

                    <button class="btn primary" type="submit">
                        Tentar carregar QR Code novamente
                    </button>
                </form>
            <?php endif; ?>

        <?php elseif (
            $payment['formaPagamento'] === 'Boleto'
            && $payment['status'] !== 'Pago'
        ): ?>
            <div class="boleto-result">
                <?php if (!empty($payment['dataVencimento'])): ?>
                    <div class="boleto-due-date">
                        <small>Vencimento</small>
                        <strong>
                            <?= Support::e(
                                date(
                                    'd/m/Y',
                                    strtotime((string)$payment['dataVencimento'])
                                )
                            ) ?>
                        </strong>
                    </div>
                <?php endif; ?>

                <?php if (!empty($payment['boletoLinhaDigitavel'])): ?>
                    <label>
                        Linha digitável
                        <textarea
                            id="boletoCode"
                            readonly
                            rows="3"
                        ><?= Support::e($payment['boletoLinhaDigitavel']) ?></textarea>
                    </label>

                    <button
                        class="btn"
                        type="button"
                        onclick="navigator.clipboard.writeText(document.getElementById('boletoCode').value)"
                    >
                        Copiar linha digitável
                    </button>
                <?php endif; ?>

                <?php if (!empty($payment['bankSlipUrl'])): ?>
                    <a
                        class="btn primary boleto-open"
                        href="<?= Support::e($payment['bankSlipUrl']) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Abrir boleto
                    </a>
                <?php endif; ?>

                <?php if (
                    empty($payment['boletoLinhaDigitavel'])
                    && empty($payment['bankSlipUrl'])
                ): ?>
                    <div class="alert error">
                        O boleto foi gerado, mas os dados de exibição ainda não
                        estão disponíveis. Atualize esta página em instantes.
                    </div>
                <?php endif; ?>

                <p>
                    A confirmação poderá levar algum tempo após o pagamento
                    bancário.
                </p>
            </div>

        <?php elseif ($payment['status'] === 'Pago'): ?>
            <?php $receipt = ComprovanteService::ensureForPayment((int)$payment['idPagamento']); ?>

            <div class="alert success">
                <?php if ($isPrediction): ?>
                    Pagamento confirmado. Seu palpite está registrado!
                <?php else: ?>
                    Oferta recebida com sucesso. Muito obrigado!
                <?php endif; ?>
            </div>

            <a
                class="btn primary"
                href="<?= Support::e(ComprovanteService::url($receipt)) ?>"
                target="_blank"
                rel="noopener noreferrer"
            >
                Abrir comprovante de pagamento
            </a>

        <?php elseif ($payment['status'] === 'Recusado'): ?>
            <div class="alert error">
                O pagamento não foi aprovado. Você pode voltar e tentar novamente.
            </div>

            <a
                class="btn primary"
                href="<?= APP_URL ?>/<?= $isPrediction ? 'palpite' : 'oferta' ?>/<?= Support::e($payment['slug']) ?>"
            >
                Tentar novamente
            </a>

        <?php elseif ($payment['status'] === 'Vencido'): ?>
            <div class="alert error">
                Este pagamento venceu.
            </div>

            <?php if (!$isPrediction): ?>
                <a
                    class="btn primary"
                    href="<?= APP_URL ?>/oferta/<?= Support::e($payment['slug']) ?>"
                >
                    Voltar para a oferta
                </a>
            <?php endif; ?>

        <?php else: ?>
            <p>
                Estamos aguardando a atualização do pagamento.
            </p>
        <?php endif; ?>
        <div class="payment-transparency-short">
            <strong>Transparência:</strong>
            <?= Support::e(TransparencyNotice::shortText()) ?>
        </div>
    </div>
</main>

<?php if (!in_array($payment['status'], ['Pago', 'Vencido', 'Cancelado', 'Estornado'], true)): ?>
<script>
setInterval(async () => {
    try {
        const response = await fetch(
            '<?= APP_URL ?>/status.php?codigo=<?= rawurlencode($code) ?>',
            {cache: 'no-store'}
        );

        const data = await response.json();

        if (
            ['Pago', 'Vencido', 'Cancelado', 'Estornado'].includes(data.status)
        ) {
            location.reload();
            return;
        }

        const badge = document.getElementById('statusBadge');

        if (badge) {
            badge.textContent = data.status;
        }
    } catch (e) {}
}, 5000);
</script>
<?php endif; ?>

</body>
</html>
