<?php
require_once __DIR__ . '/bootstrap.php';

$slug = trim((string)($_GET['slug'] ?? ''));
$event = PalpiteRepository::bySlug($slug);

if (!$event) {
    $eventAnyStatus = $slug !== ''
        ? PalpiteRepository::bySlug($slug, false)
        : null;

    /*
     * Depois que as participações se encerram, o mesmo endereço público
     * passa a mostrar o placar/resultado em vez da tela genérica de
     * indisponibilidade.
     */
    if ($eventAnyStatus) {
        $ended = !empty($eventAnyStatus['data_fim'])
            && strtotime(
                (string)$eventAnyStatus['data_fim']
            ) < time();

        $finished = (
            ($eventAnyStatus['status_jogo'] ?? '')
            === 'Finalizado'
        );

        if (
            $ended
            || $finished
            || PalpiteRepository::isPastGame($eventAnyStatus)
        ) {
            PublicPredictionResultPage::render(
                $eventAnyStatus
            );
        }
    }

    AvailabilityPage::render(
        'palpite',
        $eventAnyStatus
    );
}

/*
 * A partir do horário da partida, o link público deixa de aceitar
 * novos palpites e passa a mostrar somente histórico/resultado.
 */
if (
    PalpiteRepository::isPastGame($event)
    || ($event['status_jogo'] ?? '') === 'Finalizado'
) {
    PublicPredictionResultPage::render(
        $event
    );
}

$options = PalpiteRepository::options((int)$event['idEventoPalpite']);
$values = PalpiteRepository::values((int)$event['idEventoPalpite']);
$min = max(APP_MIN_OFFER, (float)$event['valor_minimo']);

$pixProvider =
    PaymentGatewaySettings::providerFor('PIX');

$cartaoProvider =
    PaymentGatewaySettings::providerFor('Cartao');

$pixStatus = PixAvailabilityService::status(true);

$pixConfigurado = !empty($event['pix_ativo']);
$pixDisponivel = $pixConfigurado
    && PixAvailabilityService::checkoutAvailable();

$cartaoConfigurado =
    !empty($event['cartao_ativo']);

$cartaoDisponivel =
    $cartaoConfigurado
    && PaymentGatewayManager::configuredFor(
        'Cartao'
    );

$checkoutError = (string)($_SESSION['_palpite_error'] ?? '');
unset($_SESSION['_palpite_error']);

$initialValue = $values ? (float)$values[0]['valor'] : $min;
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= Support::e(
        SiteSettings::pageTitle(
            (string)$event['titulo']
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
    <form class="checkout-main panel" action="<?= APP_URL ?>/processar-palpite.php" method="post" autocomplete="on">
        <input type="hidden" name="_csrf" value="<?= Support::csrf() ?>">
        <input type="hidden" name="idEventoPalpite" value="<?= (int)$event['idEventoPalpite'] ?>">
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

        <section class="game-intro">
            <span class="content-type-badge">Palpite</span>
            <h1><?= Support::e($event['titulo']) ?></h1>
            <div class="versus">
                <strong><?= Support::e($event['equipe_casa']) ?></strong>
                <span>x</span>
                <strong><?= Support::e($event['equipe_visitante']) ?></strong>
            </div>

            <?php if (!empty($event['data_jogo'])): ?>
                <p><strong>Jogo:</strong> <?= Support::e(date('d/m/Y \à\s H:i', strtotime($event['data_jogo']))) ?></p>
            <?php endif; ?>

            <?php if (!empty($event['descricao'])): ?>
                <div class="game-description"><?= nl2br(Support::e($event['descricao'])) ?></div>
            <?php endif; ?>
        </section>

        <h2>Meu palpite é de:</h2>
        <p class="section-help">Escolha uma das opções abaixo. Caso o resultado desejado não esteja na lista, marque “Outro”.</p>

        <div class="prediction-options">
            <?php foreach ($options as $i => $option): ?>
                <label class="prediction-option">
                    <input type="radio"
                           name="palpite_opcao"
                           value="<?= (int)$option['idOpcao'] ?>"
                           <?= $i === 0 ? 'checked' : '' ?>
                           required>
                    <span><?= Support::e($option['rotulo']) ?></span>
                </label>
            <?php endforeach; ?>

            <?php if (!empty($event['permitir_outro_palpite'])): ?>
                <label class="prediction-option prediction-other">
                    <input type="radio" name="palpite_opcao" value="outro" required>
                    <span>Outro:</span>
                    <input type="text"
                           id="palpiteOutro"
                           name="palpite_outro"
                           maxlength="160"
                           placeholder="Digite seu palpite">
                </label>
            <?php endif; ?>
        </div>

        <h2>Escolha o valor</h2>
        <div class="amounts">
            <?php foreach ($values as $i => $value): ?>
                <label class="amount-option">
                    <input type="radio"
                           name="valor_escolhido"
                           value="<?= number_format((float)$value['valor'], 2, '.', '') ?>"
                           <?= $i === 0 ? 'checked' : '' ?>>
                    <span><?= Support::money((float)$value['valor']) ?></span>
                </label>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($event['permitir_valor_livre'])): ?>
            <label>
                Ou digite outro valor*
                <input id="valorLivre"
                       name="valor_livre"
                       inputmode="decimal"
                       placeholder="Mínimo <?= Support::money($min) ?>">
            </label>
        <?php endif; ?>

        <p class="minimum-note">Valor mínimo permitido: <strong><?= Support::money($min) ?></strong></p>

        <h2>Dados da pessoa pagadora</h2>
        <div class="grid2">
            <label>Nome completo*<input name="nome" autocomplete="name" required></label>
            <label>CPF*<input name="cpf" inputmode="numeric" autocomplete="off" maxlength="14" required></label>
            <label>E-mail*<input type="email" name="email" autocomplete="email" required></label>
            <label>Telefone / WhatsApp*<input name="telefone" inputmode="tel" autocomplete="tel" required></label>
        </div>

        <h2>Escolha a forma de pagamento</h2>
        <div class="payment-methods">
            <?php if ($pixDisponivel): ?>
                <label>
                    <input
                        type="radio"
                        name="formaPagamento"
                        value="PIX"
                        checked
                    >
                    <span>◆ <b>Pix</b></span>
                </label>
            <?php elseif ($pixConfigurado): ?>
                <div class="payment-method-disabled">
                    <span>◆ <b>PIX indisponível</b></span>
                    <small>
                        O provedor PIX configurado
                        (<?= Support::e($pixProvider) ?>)
                        não está disponível neste momento.
                    </small>
                </div>
            <?php endif; ?>

            <?php if ($cartaoDisponivel): ?>
                <label>
                    <input
                        type="radio"
                        name="formaPagamento"
                        value="Cartao"
                        <?= !$pixDisponivel ? 'checked' : '' ?>
                    >
                    <span>▣ <b>Cartão de crédito</b></span>
                </label>
            <?php elseif ($cartaoConfigurado): ?>
                <div class="payment-method-disabled">
                    <span>▣ <b>Cartão indisponível</b></span>
                    <small>
                        O provedor de Cartão configurado
                        (<?= Support::e($cartaoProvider) ?>)
                        ainda não está disponível.
                    </small>
                </div>
            <?php endif; ?>
        </div>

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

        <div id="cardFields" class="card-fields hidden">
            <h3>Dados do cartão</h3>
            <div class="grid2">
                <label class="full">Nome no cartão*<input name="card_holder" autocomplete="cc-name"></label>
                <label class="full">Número do cartão*<input name="card_number" inputmode="numeric" autocomplete="cc-number" maxlength="23"></label>
                <label>
                    Mês*
                    <select name="card_month">
                        <option value="">Mês</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>"><?= str_pad((string)$m, 2, '0', STR_PAD_LEFT) ?></option>
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
                <label>CVV*<input name="card_ccv" inputmode="numeric" autocomplete="cc-csc" maxlength="4"></label>
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

        <button class="btn primary paybtn" type="submit">Confirmar palpite e pagar</button>
    </form>

    <aside class="checkout-summary panel">
        <?php if (!empty($event['imagem'])): ?>
            <img src="<?= APP_URL ?>/<?= Support::e($event['imagem']) ?>"
                 alt="<?= Support::e($event['titulo']) ?>">
        <?php endif; ?>

        <h2>Palpite selecionado</h2>
        <h3><?= Support::e($event['titulo']) ?></h3>
        <p><?= Support::e($event['equipe_casa']) ?> x <?= Support::e($event['equipe_visitante']) ?></p>

        <?php if (!empty($event['data_jogo'])): ?>
            <p><?= Support::e(date('d/m/Y H:i', strtotime($event['data_jogo']))) ?></p>
        <?php endif; ?>

        <div class="summary-prediction">
            <small>Seu palpite</small>
            <strong id="summaryPrediction"><?= Support::e($options[0]['rotulo'] ?? 'Selecione') ?></strong>
        </div>

        <hr>

        <div class="total">
            <strong>Total</strong>
            <span id="summaryTotal"><?= Support::money($initialValue) ?></span>
        </div>
    </aside>
</main>

<script>
window.offerMin = <?= json_encode($min) ?>;
window.checkoutBoletoRequiresAddress = false;
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

<?php if ($cartaoProvider === 'PagBank' && $cartaoDisponivel): ?>
    <script src="https://assets.pagseguro.com.br/checkout-sdk-js/rc/dist/browser/pagseguro.min.js"></script>
    <script src="<?= APP_URL ?>/assets/js/pagbank-card.js?v=1.8.9"></script>
<?php endif; ?>

<script src="<?= APP_URL ?>/assets/js/checkout.js?v=1.8.9"></script>
<script>
(() => {
    const radios = [...document.querySelectorAll('input[name="palpite_opcao"]')];
    const other = document.getElementById('palpiteOutro');
    const summary = document.getElementById('summaryPrediction');

    function updatePrediction() {
        const selected = radios.find(r => r.checked);
        if (!selected) return;

        const isOther = selected.value === 'outro';
        if (other) {
            other.required = isOther;
            other.disabled = !isOther;
            if (!isOther) other.value = '';
        }

        if (isOther) {
            summary.textContent = other?.value.trim() || 'Outro palpite';
        } else {
            const text = selected.closest('label')?.querySelector('span')?.textContent?.trim();
            summary.textContent = text || 'Palpite selecionado';
        }
    }

    radios.forEach(r => r.addEventListener('change', updatePrediction));
    other?.addEventListener('input', updatePrediction);
    updatePrediction();
})();
</script>
</body>
</html>
