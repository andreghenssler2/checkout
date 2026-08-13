<?php

require_once dirname(__DIR__, 2) . '/bootstrap.php';

Auth::require();

$pageTitle = 'Provedores de pagamento';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!Support::checkCsrf($_POST['_csrf'] ?? null)) {
            throw new RuntimeException('Sessão expirada.');
        }

        PaymentGatewaySettings::save(
            (string)($_POST['provedor_pix'] ?? 'Asaas'),
            (string)($_POST['provedor_cartao'] ?? 'Asaas'),
            (string)($_POST['provedor_boleto'] ?? 'Asaas')
        );

        Support::flash(
            'success',
            'Configuração dos provedores salva.'
        );

        Support::redirect(
            '/admin/configuracoes/pagamentos.php'
        );
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$settings = PaymentGatewaySettings::get();

require dirname(__DIR__) . '/_header.php';
?>

<?php if ($error): ?>
    <div class="alert error">
        <?= Support::e($error) ?>
    </div>
<?php endif; ?>

<div class="panel">
    <span class="content-type-badge">Multi-Gateway</span>

    <h2>Provedor por forma de pagamento</h2>

    <p>
        PIX, Cartão de Crédito e Boleto podem usar Asaas ou PagBank.
        Para Cartão PagBank, prepare primeiro a chave pública em Admin > PagBank.
    </p>

    <?php if (
        PagBankSettings::enabled()
        && PagBankSettings::publicKey() === ''
    ): ?>
        <div class="alert muted">
            <strong>Cartão PagBank ainda sem chave pública.</strong>
            <p>
                O PagBank já aparece no seletor de Cartão, mas só ficará
                disponível no Checkout depois de preparar a chave pública em
                Admin &gt; PagBank.
            </p>
        </div>
    <?php endif; ?>

    <form method="post">
        <input
            type="hidden"
            name="_csrf"
            value="<?= Support::csrf() ?>"
        >

        <div class="grid2">
            <?php
            $fields = [
                'PIX' => ['provedor_pix','PIX'],
                'Cartao' => ['provedor_cartao','Cartão de crédito'],
                'Boleto' => ['provedor_boleto','Boleto'],
            ];
            ?>

            <?php foreach (
                $fields
                as $method => [$field,$label]
            ): ?>
                <label>
                    <?= Support::e($label) ?>

                    <select name="<?= Support::e($field) ?>">
                        <?php foreach (
                            PaymentGatewayManager::selectableFor($method)
                            as $key => $providerLabel
                        ): ?>
                            <option
                                value="<?= Support::e($key) ?>"
                                <?= ($settings[$field] ?? 'Asaas') === $key
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= Support::e($providerLabel) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endforeach; ?>
        </div>

        <button class="btn primary" type="submit">
            Salvar provedores
        </button>
    </form>
</div>

<div class="panel">
    <h2>Provedores</h2>

    <div class="gateway-cards">
        <article class="gateway-card ready">
            <span class="badge paid">Implementado</span>
            <h3>Asaas</h3>
            <p>PIX, Cartão de Crédito e Boleto.</p>
            <a
                class="btn"
                href="<?= APP_URL ?>/admin/configuracoes/asaas.php"
            >
                Configurar Asaas
            </a>
        </article>

        <article class="gateway-card ready">
            <span class="badge paid">Implementado</span>
            <h3>PagBank</h3>
            <p>
                PIX, Cartão de Crédito e Boleto estão ativos na
                camada Multi-Gateway. O cartão utiliza criptografia no
                navegador com a chave pública PagBank.
            </p>
            <a
                class="btn"
                href="<?= APP_URL ?>/admin/configuracoes/pagbank.php"
            >
                Configurar PagBank
            </a>
        </article>

        <article class="gateway-card planned">
            <span class="badge muted">Preparado</span>
            <h3>Sicredi</h3>
            <p>
                A estrutura Multi-Gateway está pronta para receber
                um adapter Sicredi quando as APIs da conta estiverem
                definidas.
            </p>
        </article>
    </div>
</div>

<div class="panel">
    <h2>Configuração atual</h2>

    <div class="gateway-current-grid">
        <div>
            <small>PIX</small>
            <strong>
                <?= Support::e(
                    $settings['provedor_pix'] ?? 'Asaas'
                ) ?>
            </strong>
        </div>

        <div>
            <small>Cartão</small>
            <strong>
                <?= Support::e(
                    $settings['provedor_cartao'] ?? 'Asaas'
                ) ?>
            </strong>
        </div>

        <div>
            <small>Boleto</small>
            <strong>
                <?= Support::e(
                    $settings['provedor_boleto'] ?? 'Asaas'
                ) ?>
            </strong>
        </div>
    </div>
</div>

<?php require dirname(__DIR__) . '/_footer.php'; ?>
