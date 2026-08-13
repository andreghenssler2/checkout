<?php

require_once dirname(__DIR__, 2) . '/bootstrap.php';

Auth::require();

$pageTitle = 'Configuração PagBank';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!Support::checkCsrf($_POST['_csrf'] ?? null)) {
            throw new RuntimeException('Sessão expirada.');
        }

        $action = (string)($_POST['acao'] ?? 'salvar');

        if ($action === 'salvar') {
            $environment = in_array(
                $_POST['ambiente'] ?? 'sandbox',
                ['sandbox','producao'],
                true
            )
                ? (string)$_POST['ambiente']
                : 'sandbox';

            $updates = [];
            $cardSettlementDays = (int)(
                $_POST['cartao_prazo_recebimento']
                ?? 30
            );

            if (
                !in_array(
                    $cardSettlementDays,
                    [14,30],
                    true
                )
            ) {
                throw new RuntimeException(
                    'Selecione um prazo válido para recebimento do Cartão PagBank.'
                );
            }

            $params = [
                ':ativo' => !empty($_POST['ativo']) ? 1 : 0,
                ':ambiente' => $environment,
                ':cartao_prazo' =>
                    $cardSettlementDays,
            ];

            foreach (
                ['token_sandbox','token_producao']
                as $field
            ) {
                $value = trim((string)($_POST[$field] ?? ''));

                if ($value === '') {
                    continue;
                }

                if (strlen($value) < 20) {
                    throw new RuntimeException(
                        'O token PagBank informado parece incompleto.'
                    );
                }

                $updates[] = "{$field}=:{$field}";
                $params[":{$field}"] = Crypto::encrypt($value);

                /*
                 * Se o token de Produção mudou, a chave pública salva
                 * precisa ser consultada novamente para a mesma conta.
                 */
                if ($field === 'token_producao') {
                    $updates[] = 'public_key_producao=NULL';
                }
            }

            $sql =
                'UPDATE configuracoes_pagbank
                 SET ativo=:ativo,
                     ambiente=:ambiente,
                     cartao_prazo_recebimento=:cartao_prazo'
                . (
                    $updates
                        ? ',' . implode(',', $updates)
                        : ''
                )
                . ' WHERE idConfiguracao=1';

            Database::connection()
                ->prepare($sql)
                ->execute($params);

            Support::flash(
                'success',
                'Configuração PagBank salva.'
            );

            Support::redirect(
                '/admin/configuracoes/pagbank.php'
            );
        }

        if ($action === 'testar') {
            $environment =
                PagBankSettings::activeEnvironment();

            try {
                $service = new PagBankService();
                $service->ensureCardPublicKey();

                PagBankSettings::saveTestResult(
                    $environment
                );

                Support::flash(
                    'success',
                    'Conexão PagBank confirmada no ambiente '
                    . (
                        $environment === 'producao'
                            ? 'Produção'
                            : 'Sandbox'
                    )
                    . '. A chave pública do cartão também está preparada.'
                );
            } catch (Throwable $e) {
                PagBankSettings::saveTestResult(
                    $environment,
                    $e->getMessage()
                );

                throw $e;
            }

            Support::redirect(
                '/admin/configuracoes/pagbank.php'
            );
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$settings = PagBankSettings::get();
$environment = PagBankSettings::activeEnvironment();

$publicKeySandbox =
    PagBankSettings::publicKey('sandbox');

$publicKeyProduction =
    PagBankSettings::publicKey('producao');

require dirname(__DIR__) . '/_header.php';
?>

<?php if ($error): ?>
    <div class="alert error">
        <?= Support::e($error) ?>
    </div>
<?php endif; ?>

<div class="panel">
    <span class="content-type-badge">PagBank</span>

    <h2>Credenciais PagBank</h2>

    <p class="report-help">
        O PagBank pode processar PIX, Cartão de Crédito e Boleto.
        Para cartão, a chave pública deve estar preparada no ambiente selecionado.
    </p>

    <div class="pagbank-merchant-email-note">
        <strong>Importante nos testes</strong>

        <p>
            O e-mail informado pelo comprador no Checkout deve ser
            diferente do e-mail cadastrado na conta PagBank que recebe
            o pagamento. Se forem iguais, o PagBank rejeita o pedido.
        </p>
    </div>

    <form method="post">
        <input
            type="hidden"
            name="_csrf"
            value="<?= Support::csrf() ?>"
        >

        <input type="hidden" name="acao" value="salvar">

        <label>
            <input
                type="checkbox"
                name="ativo"
                value="1"
                <?= !empty($settings['ativo'])
                    ? 'checked'
                    : '' ?>
            >
            Ativar integração PagBank
        </label>

        <label>
            Ambiente

            <select name="ambiente">
                <option
                    value="sandbox"
                    <?= $environment === 'sandbox'
                        ? 'selected'
                        : '' ?>
                >
                    Sandbox
                </option>

                <option
                    value="producao"
                    <?= $environment === 'producao'
                        ? 'selected'
                        : '' ?>
                >
                    Produção
                </option>
            </select>
        </label>

        <label>
            Prazo de recebimento do Cartão PagBank

            <select name="cartao_prazo_recebimento">
                <option
                    value="14"
                    <?= PagBankSettings::cardSettlementDays() === 14
                        ? 'selected'
                        : '' ?>
                >
                    14 dias — 4,99% + R$ 0,40
                </option>

                <option
                    value="30"
                    <?= PagBankSettings::cardSettlementDays() === 30
                        ? 'selected'
                        : '' ?>
                >
                    30 dias — 3,99% + R$ 0,40
                </option>
            </select>

            <small class="form-note">
                Esse prazo é usado para calcular a taxa e o valor líquido
                registrados pelo Checkout nos pagamentos por cartão.
            </small>
        </label>

        <div class="grid2">
            <label>
                Token Sandbox

                <input
                    type="password"
                    name="token_sandbox"
                    autocomplete="new-password"
                    placeholder="<?= trim(PagBankSettings::tokenFor('sandbox')) !== ''
                        ? 'Configurado — deixe em branco para manter'
                        : 'Cole o token Sandbox' ?>"
                >
            </label>

            <label>
                Token Produção

                <input
                    type="password"
                    name="token_producao"
                    autocomplete="new-password"
                    placeholder="<?= trim(PagBankSettings::tokenFor('producao')) !== ''
                        ? 'Configurado — deixe em branco para manter'
                        : 'Cole o token de Produção' ?>"
                >
            </label>
        </div>

        <button class="btn primary" type="submit">
            Salvar PagBank
        </button>
    </form>
</div>

<div class="panel">
    <h2>Diagnóstico do Cartão PagBank</h2>

    <div class="gateway-current-grid">
        <div>
            <small>Ambiente ativo</small>
            <strong>
                <?= $environment === 'producao'
                    ? 'Produção'
                    : 'Sandbox' ?>
            </strong>
        </div>

        <div>
            <small>Chave usada no Checkout</small>
            <strong>
                <?= PagBankSettings::publicKey() !== ''
                    ? 'Disponível'
                    : 'Não disponível' ?>
            </strong>
        </div>

        <div>
            <small>Sandbox</small>
            <strong>
                Chave oficial fixa
            </strong>
        </div>
    </div>

    <?php if ($environment === 'sandbox'): ?>
        <div class="alert muted">
            <strong>Teste com cartão fictício do PagBank</strong>

            <p>
                No ambiente Sandbox não utilize cartão real.
                Exemplo oficial de aprovação:
                <code>4539620659922097</code>,
                validade <code>12/2026</code>,
                CVV <code>123</code>.
            </p>
        </div>
    <?php endif; ?>
</div>

<div class="panel">
    <h2>Teste e chave pública</h2>

    <p>
        O teste consulta a API PagBank usando o token do ambiente
        selecionado. Se ainda não existir uma chave pública para cartão,
        o Checkout solicita a criação e a salva.
    </p>

    <div class="gateway-key-grid">
        <div>
            <small>Chave pública Sandbox</small>
            <strong>
                <?= $publicKeySandbox !== ''
                    ? 'Configurada'
                    : 'Não configurada' ?>
            </strong>

            <?php if ($publicKeySandbox !== ''): ?>
                <code>
                    <?= Support::e(
                        mb_substr($publicKeySandbox, 0, 28)
                    ) ?>…
                </code>
            <?php endif; ?>
        </div>

        <div>
            <small>Chave pública Produção</small>
            <strong>
                <?= $publicKeyProduction !== ''
                    ? 'Configurada'
                    : 'Não configurada' ?>
            </strong>

            <?php if ($publicKeyProduction !== ''): ?>
                <code>
                    <?= Support::e(
                        mb_substr($publicKeyProduction, 0, 28)
                    ) ?>…
                </code>
            <?php endif; ?>
        </div>
    </div>

    <form method="post">
        <input
            type="hidden"
            name="_csrf"
            value="<?= Support::csrf() ?>"
        >
        <input type="hidden" name="acao" value="testar">

        <button class="btn" type="submit">
            Testar conexão / preparar chave pública
        </button>
    </form>
</div>

<div class="panel">
    <h2>Webhook</h2>

    <p>
        O Checkout envia esta URL automaticamente em cada pedido
        criado no PagBank:
    </p>

    <code>
        <?= Support::e(PagBankSettings::webhookUrl()) ?>
    </code>

    <p class="form-note">
        A autenticidade é validada pelo header
        <code>x-authenticity-token</code>, comparando o SHA-256
        de <code>token-payload</code>. O endpoint reconhece os tokens
        configurados de Sandbox e Produção.
    </p>
</div>

<div class="panel">
    <h2>Taxas configuradas do PagBank</h2>

    <p>
        Quando o PagBank for o provedor selecionado, o Checkout usa estas
        taxas para registrar a taxa de processamento e o valor líquido
        estimado nos relatórios.
    </p>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Forma de pagamento</th>
                    <th>Prazo</th>
                    <th>Taxa</th>
                    <th>Uso no Checkout</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach (PagBankFeeService::rateTable() as $rate): ?>
                    <tr>
                        <td>
                            <?= Support::e($rate['method']) ?>
                        </td>

                        <td>
                            <?= Support::e($rate['settlement']) ?>
                        </td>

                        <td>
                            <strong>
                                <?= Support::e($rate['fee']) ?>
                            </strong>
                        </td>

                        <td>
                            <?php if ($rate['available']): ?>
                                <span class="badge paid">
                                    Disponível
                                </span>
                            <?php else: ?>
                                <span class="badge muted">
                                    Não utilizado
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p class="form-note">
        Cartão parcelado não está habilitado atualmente; o Checkout usa
        uma parcela à vista. Débito online também não é oferecido nesta
        versão.
    </p>
</div>

<div class="panel">
    <h2>Formas de pagamento</h2>

    <div class="gateway-status-list">
        <div>
            <span class="badge paid">Ativo na integração</span>
            <strong>PIX</strong>
            <small>
                QR Code e Pix Copia e Cola com validade de 1 hora.
            </small>
        </div>

        <div>
            <span class="badge paid">Ativo na integração</span>
            <strong>Boleto</strong>
            <small>
                O PagBank exige endereço completo do pagador.
                O Checkout solicita esses campos somente quando necessário.
            </small>
        </div>

        <div>
            <span class="badge paid">Ativo na integração</span>
            <strong>Cartão de crédito</strong>
            <small>
                O cartão é criptografado diretamente no navegador pelo SDK
                oficial do PagBank e a cobrança é capturada automaticamente.
                É necessário ter a chave pública preparada. Os dados do titular
                são enviados dentro de card.holder conforme a API de Pedidos.
            </small>
        </div>
    </div>
</div>

<?php require dirname(__DIR__) . '/_footer.php'; ?>
