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

$pixProvider =
    PaymentGatewaySettings::providerFor('PIX');

$cartaoProvider =
    PaymentGatewaySettings::providerFor('Cartao');

$boletoProvider =
    PaymentGatewaySettings::providerFor('Boleto');

$pixStatus = PixAvailabilityService::status(true);

$temPixConfigurado =
    !empty($offer['pix_ativo']);

$temPix = $temPixConfigurado
    && PixAvailabilityService::checkoutAvailable();

$temCartaoConfigurado =
    !empty($offer['cartao_ativo']);

$temCartao = $temCartaoConfigurado
    && PaymentGatewayManager::configuredFor(
        'Cartao'
    );

$boletoGatewayConfigurado =
    PaymentGatewayManager::configuredFor(
        'Boleto'
    );

$temBoleto = $boletoDisponivel
    && $boletoGatewayConfigurado;

$boletoExigeEndereco =
    $temBoleto
    && $boletoProvider === 'PagBank';

$temFormaDisponivel =
    $temPix || $temCartao || $temBoleto;

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
    <title><?= Support::e(
        SiteSettings::pageTitle(
            (string)$offer['titulo']
        )
    ) ?></title>

    <meta
        name="description"
        content="<?= Support::e(
            SiteSettings::description()
        ) ?>"
    >

    <?php SiteSettings::renderFavicon(); ?>

    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css?v=1.8.9">
    <?= AnalyticsService::renderHead() ?>
</head>
<body class="bg">

<header class="public-head">
    <a href="<?= APP_URL ?>/">
        <strong>
            <?= Support::e(
                SiteSettings::title()
            ) ?>
        </strong>
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
                        O provedor PIX configurado
                        (<?= Support::e($pixProvider) ?>)
                        não está disponível neste momento.
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
                    <small>
                        <?= Support::e(
                            !$boletoDisponivel
                                ? (string)$boleto['motivo']
                                : 'O provedor de Boleto configurado ('
                                    . $boletoProvider
                                    . ') ainda não está configurado/ativo.'
                        ) ?>
                    </small>
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

<?php if ($boletoProvider === 'PagBank'): ?>
    <div
        id="pagbankBoletoAddress"
        class="pagbank-boleto-address hidden"
    >
        <h3>Endereço para o Boleto PagBank</h3>

        <p class="section-help">
            O PagBank exige o endereço completo do pagador para
            emitir o boleto.
        </p>

        <div class="grid2">
            <label>
                CEP*
                <input
                    name="pagbank_cep"
                    inputmode="numeric"
                    autocomplete="postal-code"
                    maxlength="9"
                >
            </label>

            <label>
                Estado (UF)*
                <input
                    name="pagbank_estado"
                    maxlength="2"
                    autocomplete="address-level1"
                    placeholder="RS"
                >
            </label>

            <label class="full">
                Logradouro*
                <input
                    name="pagbank_logradouro"
                    autocomplete="address-line1"
                >
            </label>

            <label>
                Número*
                <input
                    name="pagbank_numero"
                    autocomplete="address-line2"
                >
            </label>

            <label>
                Bairro*
                <input
                    name="pagbank_bairro"
                    autocomplete="address-level3"
                >
            </label>

            <label>
                Cidade*
                <input
                    name="pagbank_cidade"
                    autocomplete="address-level2"
                >
            </label>

            <label>
                Complemento
                <input
                    name="pagbank_complemento"
                    autocomplete="address-line3"
                >
            </label>
        </div>
    </div>
<?php endif; ?>

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

                <?php if ($cartaoProvider === 'Asaas'): ?>
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
                        <input
                            name="holder_numero"
                            autocomplete="address-line2"
                        >
                    </label>

                    <label class="full">
                        Complemento
                        <input name="holder_complemento">
                    </label>
                <?php else: ?>
                    <input
                        type="hidden"
                        name="pagbank_encrypted_card"
                        id="pagbankEncryptedCard"
                        value=""
                    >
                <?php endif; ?>
            </div>

            <?php if (
                $cartaoProvider === 'PagBank'
                && PagBankSettings::activeEnvironment() === 'sandbox'
            ): ?>
                <div class="pagbank-sandbox-card-note">
                    <strong>Ambiente Sandbox PagBank</strong>
                    <p>
                        Use somente cartões de teste PagBank.
                        Exemplo aprovado:
                        <code>4539620659922097</code>,
                        validade <code>12/2026</code>,
                        CVV <code>123</code>.
                    </p>
                </div>
            <?php endif; ?>

            <p class="privacy">
                <?php if ($cartaoProvider === 'PagBank'): ?>
                    Número, validade e CVV são criptografados no navegador
                    pelo SDK do PagBank. O Checkout envia ao servidor apenas
                    o cartão criptografado e não grava os dados sensíveis.
                <?php else: ?>
                    Os dados do cartão são enviados ao provedor
                    <?= Support::e($cartaoProvider) ?> para processamento
                    e não são gravados pelo sistema.
                <?php endif; ?>
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
window.checkoutBoletoRequiresAddress =
    <?= $boletoExigeEndereco ? 'true' : 'false' ?>;
window.checkoutBoletoProvider =
    <?= json_encode($boletoProvider) ?>;
window.checkoutCardProvider =
    <?= json_encode($cartaoProvider) ?>;
window.checkoutPagBankPublicKey =
    <?= json_encode(
        $cartaoProvider === 'PagBank'
            ? PagBankSettings::publicKey()
            : ''
    ) ?>;
window.checkoutPagBankEnvironment =
    <?= json_encode(
        $cartaoProvider === 'PagBank'
            ? PagBankSettings::activeEnvironment()
            : ''
    ) ?>;
</script>

<?php if ($cartaoProvider === 'PagBank' && $temCartao): ?>
    <script src="https://assets.pagseguro.com.br/checkout-sdk-js/rc/dist/browser/pagseguro.min.js"></script>
    <script src="<?= APP_URL ?>/assets/js/pagbank-card.js?v=1.8.9"></script>
<?php endif; ?>

<script src="<?= APP_URL ?>/assets/js/checkout.js?v=1.8.9"></script>
</body>
</html>
