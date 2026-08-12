<?php
require_once __DIR__ . '/bootstrap.php';

$slug = trim((string)($_GET['slug'] ?? ''));
$offer = OfertaRepository::bySlug($slug);

if (!$offer) {
    $offerAnyStatus = $slug !== ''
        ? OfertaRepository::bySlug($slug, false)
        : null;

    AvailabilityPage::render('oferta', $offerAnyStatus);
}

$values = OfertaRepository::values((int)$offer['idOferta']);
$min = max(APP_MIN_OFFER, (float)$offer['valor_minimo']);
$checkoutError = (string)($_SESSION['_checkout_error'] ?? '');
unset($_SESSION['_checkout_error']);

$boleto = BoletoOfertaService::disponibilidade($offer);
$boletoDisponivel = (bool)$boleto['disponivel'];

$pixStatus = PixAvailabilityService::status(true);
$temPixConfigurado = !empty($offer['pix_ativo']);
$temPix = $temPixConfigurado
    && PixAvailabilityService::checkoutAvailable();

$temCartao = !empty($offer['cartao_ativo']);
$temBoleto = $boletoDisponivel;
$temFormaDisponivel = $temPix || $temCartao || $temBoleto;

$formaInicial = $temPix
    ? 'PIX'
    : ($temCartao ? 'Cartao' : ($temBoleto ? 'Boleto' : ''));

$futureStart = null;

if (!empty($offer['data_inicio'])) {
    try {
        $startDate = new DateTimeImmutable(
            (string)$offer['data_inicio'],
            new DateTimeZone(APP_TIMEZONE)
        );

        $now = new DateTimeImmutable(
            'now',
            new DateTimeZone(APP_TIMEZONE)
        );

        if ($startDate > $now) {
            $futureStart = $startDate;
        }
    } catch (Throwable) {
        $futureStart = null;
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= Support::e($offer['titulo']) ?> - Checkout</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css?v=1.7.2">
    <?= AnalyticsService::renderHead() ?>
</head>
<body class="bg">

<header class="public-head">
    <a href="<?= APP_URL ?>/">
        <strong>Checkout</strong>
        <span>IECLB Parobé</span>
    </a>
</header>

<main class="checkout-wrap">
    <form
        class="checkout-main panel"
        action="<?= APP_URL ?>/processar.php"
        method="post"
        autocomplete="on"
    >
        <input type="hidden" name="_csrf" value="<?= Support::csrf() ?>">
        <input type="hidden" name="idOferta" value="<?= (int)$offer['idOferta'] ?>">
        <input
            type="text"
            name="website"
            value=""
            tabindex="-1"
            autocomplete="off"
            class="honeypot-field"
            aria-hidden="true"
        >

        <?php if ($checkoutError): ?>
            <div class="alert error"><?= Support::e($checkoutError) ?></div>
        <?php endif; ?>

        <h2>Escolha o valor da oferta</h2>

        <div class="amounts">
            <?php foreach ($values as $i => $value): ?>
                <label class="amount-option">
                    <input
                        type="radio"
                        name="valor_escolhido"
                        value="<?= number_format((float)$value['valor'], 2, '.', '') ?>"
                        <?= $i === 0 ? 'checked' : '' ?>
                    >
                    <span><?= Support::money((float)$value['valor']) ?></span>
                </label>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($offer['permitir_valor_livre'])): ?>
            <label>
                Ou digite outro valor*
                <input
                    id="valorLivre"
                    name="valor_livre"
                    inputmode="decimal"
                    placeholder="Mínimo <?= Support::money($min) ?>"
                >
            </label>
        <?php endif; ?>

        <h2>Dados da pessoa pagadora</h2>

        <div class="grid2">
            <label>
                Nome completo*
                <input name="nome" autocomplete="name" required>
            </label>

            <label>
                CPF*
                <input
                    name="cpf"
                    inputmode="numeric"
                    autocomplete="off"
                    maxlength="14"
                    required
                >
            </label>

            <label>
                E-mail*
                <input type="email" name="email" autocomplete="email" required>
            </label>

            <label>
                Telefone*
                <input name="telefone" inputmode="tel" autocomplete="tel" required>
            </label>
        </div>

        <h2>Escolha a forma de pagamento</h2>

        <div class="payment-methods">
            <?php if ($temPix): ?>
                <label>
                    <input
                        type="radio"
                        name="formaPagamento"
                        value="PIX"
                        <?= $formaInicial === 'PIX' ? 'checked' : '' ?>
                    >
                    <span>◆ <b>Pix</b></span>
                </label>
            <?php endif; ?>

            <?php if ($temPixConfigurado && !$temPix): ?>
                <div class="payment-method-disabled">
                    <span>◆ <b>PIX indisponível</b></span>
                    <small>
                        A integração Asaas ou a API Key do ambiente atual
                        não está disponível para processar PIX.
                    </small>
                </div>
            <?php endif; ?>

            <?php if ($temCartao): ?>
                <label>
                    <input
                        type="radio"
                        name="formaPagamento"
                        value="Cartao"
                        <?= $formaInicial === 'Cartao' ? 'checked' : '' ?>
                    >
                    <span>▣ <b>Cartão de crédito</b></span>
                </label>
            <?php endif; ?>

            <?php if ($temBoleto): ?>
                <label>
                    <input
                        type="radio"
                        name="formaPagamento"
                        value="Boleto"
                        <?= $formaInicial === 'Boleto' ? 'checked' : '' ?>
                    >
                    <span>▤ <b>Boleto</b></span>
                </label>
            <?php elseif (!empty($offer['boleto_ativo'])): ?>
                <div class="payment-method-disabled">
                    <span>▤ <b>Boleto indisponível</b></span>
                    <small><?= Support::e((string)$boleto['motivo']) ?></small>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!$temFormaDisponivel): ?>
            <div class="alert error">
                Nenhuma forma de pagamento está disponível para esta oferta neste momento.
            </div>
        <?php endif; ?>

        <div id="pixMessage" class="payment-info pix-payment-info hidden">
            <span class="payment-info-icon" aria-hidden="true">◆</span>
            <div>
                <strong>Pagamento via Pix</strong>
                <p>
                    O QR Code e o código Pix Copia e Cola serão exibidos após
                    clicar no botão <b>Pagar agora</b>.
                </p>
                <p class="pix-validity-note">
                    <strong>Validade:</strong>
                    a chave Pix Copia e Cola tem validade de 1 hora após a geração.
                </p>
            </div>
        </div>

        <div id="boletoMessage" class="payment-info boleto-payment-info hidden">
            <span class="payment-info-icon" aria-hidden="true">▤</span>
            <div>
                <strong>Pagamento via Boleto</strong>
                <p>
                    O boleto e a linha digitável serão exibidos após clicar em
                    <b>Pagar agora</b>.
                </p>
                <p>
                    Vencimento previsto:
                    <b><?= Support::e(BoletoOfertaService::vencimentoFormatado($boleto['vencimento'])) ?></b>.
                </p>
            </div>
        </div>

        <div id="cardFields" class="card-fields hidden">
            <h3>Dados do cartão</h3>

            <div class="grid2">
                <label class="full">
                    Nome no cartão*
                    <input name="card_holder" autocomplete="cc-name">
                </label>

                <label class="full">
                    Número do cartão*
                    <input
                        name="card_number"
                        inputmode="numeric"
                        autocomplete="cc-number"
                        maxlength="23"
                    >
                </label>

                <label>
                    Mês*
                    <select name="card_month">
                        <option value="">Mês</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>">
                                <?= str_pad((string)$m, 2, '0', STR_PAD_LEFT) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </label>

                <label>
                    Ano*
                    <select name="card_year">
                        <option value="">Ano</option>
                        <?php for ($y = (int)date('Y'); $y <= (int)date('Y') + 15; $y++): ?>
                            <option><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </label>

                <label>
                    CVV*
                    <input
                        name="card_ccv"
                        inputmode="numeric"
                        autocomplete="cc-csc"
                        maxlength="4"
                    >
                </label>

                <label>
                    CPF do titular*
                    <input name="holder_cpf" inputmode="numeric">
                </label>

                <label>
                    CEP*
                    <input
                        name="holder_cep"
                        inputmode="numeric"
                        autocomplete="postal-code"
                    >
                </label>

                <label>
                    Número*
                    <input name="holder_numero" autocomplete="address-line2">
                </label>

                <label class="full">
                    Complemento
                    <input name="holder_complemento">
                </label>
            </div>

            <p class="privacy">
                Os dados do cartão são enviados diretamente ao Asaas para
                processamento e não são gravados pelo sistema.
            </p>
        </div>

        <?php TransparencyNotice::render(); ?>

        <button
            class="btn primary paybtn"
            type="submit"
            <?= !$temFormaDisponivel ? 'disabled' : '' ?>
        >
            Pagar agora
        </button>
    </form>

    <aside class="checkout-summary panel">
        <?php if (!empty($offer['imagem'])): ?>
            <img
                src="<?= APP_URL ?>/<?= Support::e($offer['imagem']) ?>"
                alt="<?= Support::e($offer['titulo']) ?>"
            >
        <?php endif; ?>

        <span class="content-type-badge">
            Oferta <?= Support::e(
                OfertaRepository::categoryLabel(
                    $offer['categoria'] ?? 'Local'
                )
            ) ?>
        </span>

        <h2>Oferta Selecionada</h2>
        <h3><?= Support::e($offer['titulo']) ?></h3>

        <?php if (!empty($offer['data_inicio']) || !empty($offer['data_fim'])): ?>
            <p>
                <?= Support::e(
                    $offer['data_inicio']
                        ? date('d/m/Y', strtotime($offer['data_inicio']))
                        : ''
                ) ?>
                <?= $offer['data_inicio'] && $offer['data_fim'] ? ' – ' : '' ?>
                <?= Support::e(
                    $offer['data_fim']
                        ? date('d/m/Y', strtotime($offer['data_fim']))
                        : ''
                ) ?>
            </p>
        <?php endif; ?>

        <hr>

        <div class="total">
            <strong>Total</strong>
            <span id="summaryTotal">
                <?= Support::money($values ? (float)$values[0]['valor'] : $min) ?>
            </span>
        </div>

        <?php if ($futureStart instanceof DateTimeImmutable): ?>
            <div class="future-offer-donation-notice summary-notice">
                <span class="content-type-badge">
                    Doações antecipadas abertas
                </span>

                <strong>
                    Esta Oferta está programada para
                    <?= Support::e(
                        $futureStart->format('d/m/Y')
                    ) ?>,
                    mas já pode receber doações.
                </strong>

                <p>
                    A data inicial organiza a campanha no calendário.
                    Você pode contribuir normalmente agora.
                </p>
            </div>
        <?php endif; ?>
    </aside>
</main>

<script>
window.offerMin = <?= json_encode($min) ?>;
</script>
<script src="<?= APP_URL ?>/assets/js/checkout.js"></script>
</body>
</html>
