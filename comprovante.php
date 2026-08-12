<?php
require_once __DIR__ . '/bootstrap.php';

$token = trim((string)($_GET['token'] ?? ''));
$receipt = ComprovanteService::byToken($token);

if (!$receipt || ($receipt['status'] ?? '') !== 'Pago') {
    http_response_code(404);
    die('Comprovante não encontrado.');
}

function maskCpf(string $cpf): string
{
    $cpf = preg_replace('/\D+/', '', $cpf) ?? '';

    if (strlen($cpf) !== 11) {
        return '***';
    }

    return '***.***.***-' . substr($cpf, -2);
}

function paymentMethodLabel(string $method): string
{
    return match ($method) {
        'PIX' => 'PIX',
        'Boleto' => 'Boleto',
        'Cartao' => 'Cartão de Crédito',
        default => $method,
    };
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Comprovante <?= Support::e($receipt['numero']) ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css">
    <?= AnalyticsService::renderHead() ?>
</head>
<body class="receipt-page">

<main class="receipt-shell">
    <section class="receipt-card">
        <header class="receipt-head">
            <div>
                <span class="receipt-brand">IECLB Parobé</span>
                <h1>Comprovante de Pagamento</h1>
            </div>
            <span class="badge paid">Pago</span>
        </header>

        <div class="receipt-number">
            <small>Número do comprovante</small>
            <strong><?= Support::e($receipt['numero']) ?></strong>
        </div>

        <div class="receipt-grid">
            <div>
                <small>Pagador</small>
                <strong><?= Support::e($receipt['nome']) ?></strong>
            </div>

            <div>
                <small>CPF</small>
                <strong><?= Support::e(maskCpf((string)$receipt['cpf'])) ?></strong>
            </div>

            <div>
                <small>Tipo</small>
                <strong><?= Support::e($receipt['tipoOrigem']) ?></strong>
            </div>

            <div>
                <small><?= $receipt['tipoOrigem'] === 'Palpite' ? 'Formulário' : 'Oferta' ?></small>
                <strong><?= Support::e($receipt['titulo']) ?></strong>
            </div>

            <?php if (
                $receipt['tipoOrigem'] === 'Palpite'
                && !empty($receipt['palpiteTexto'])
            ): ?>
                <div class="receipt-full">
                    <small>Palpite</small>
                    <strong><?= Support::e($receipt['palpiteTexto']) ?></strong>
                </div>
            <?php endif; ?>

            <div>
                <small>Valor pago</small>
                <strong class="receipt-value">
                    <?= Support::money((float)$receipt['valor']) ?>
                </strong>
            </div>

            <div>
                <small>Forma de pagamento</small>
                <strong>
                    <?= Support::e(
                        paymentMethodLabel(
                            (string)$receipt['formaPagamento']
                        )
                    ) ?>
                </strong>
            </div>

            <div>
                <small>Data do pagamento</small>
                <strong>
                    <?= Support::e(
                        !empty($receipt['dataPagamento'])
                            ? date(
                                'd/m/Y H:i:s',
                                strtotime((string)$receipt['dataPagamento'])
                            )
                            : date(
                                'd/m/Y H:i:s',
                                strtotime((string)$receipt['emitidoEm'])
                            )
                    ) ?>
                </strong>
            </div>

            <div>
                <small>Código da transação</small>
                <strong><?= Support::e($receipt['codigo']) ?></strong>
            </div>
        </div>

        <div class="receipt-validation">
            <strong>Pagamento confirmado</strong>
            <p>
                Este comprovante foi emitido automaticamente pelo
                Checkout IECLB Parobé após a confirmação do pagamento.
            </p>
        </div>

        <p class="receipt-fiscal-note">
            Este comprovante confirma o pagamento realizado no Checkout
            IECLB Parobé e não substitui documento fiscal quando este for
            legalmente exigido.
        </p>

        <div class="receipt-transparency">
            <strong>Transparência sobre o recebimento</strong>
            <p>
                <?= Support::e(TransparencyNotice::receiptText()) ?>
            </p>
        </div>

        <div class="receipt-actions no-print">
            <button
                class="btn primary"
                type="button"
                onclick="window.print()"
            >
                Imprimir / Salvar em PDF
            </button>

            <a class="btn" href="<?= APP_URL ?>/">
                Voltar ao início
            </a>
        </div>
    </section>
</main>

</body>
</html>
