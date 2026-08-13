<?php
require_once dirname(__DIR__, 2) . '/bootstrap.php';

Auth::require();

$pageTitle = 'Configuração Asaas';
$error = '';
$pixResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!Support::checkCsrf($_POST['_csrf'] ?? null)) {
            throw new RuntimeException('Sessão expirada.');
        }

        $action = (string)($_POST['acao'] ?? 'salvar');

        if ($action === 'salvar') {
            $environment = in_array(
                $_POST['ambiente'] ?? 'sandbox',
                ['sandbox', 'producao'],
                true
            )
                ? (string)$_POST['ambiente']
                : 'sandbox';

            $fields = [
                'api_key_sandbox',
                'api_key_producao',
                'webhook_token_sandbox',
                'webhook_token_producao',
            ];

            $updates = [];
            $params = [
                ':ativo' => !empty($_POST['ativo']) ? 1 : 0,
                ':amb' => $environment,
            ];

            foreach ($fields as $field) {
                $value = trim(
                    (string)($_POST[$field] ?? '')
                );

                if ($value === '') {
                    continue;
                }

                if (
                    str_starts_with($field, 'webhook_')
                    && strlen($value) < 32
                ) {
                    throw new RuntimeException(
                        'O token de webhook deve ter pelo menos 32 caracteres.'
                    );
                }

                $updates[] = "{$field}=:{$field}";
                $params[":{$field}"] = Crypto::encrypt($value);
            }

            $sql = 'UPDATE configuracoes_asaas
                    SET ativo=:ativo,
                        ambiente=:amb'
                    . ($updates ? ',' . implode(',', $updates) : '')
                    . ' WHERE idConfiguracao=1';

            Database::connection()
                ->prepare($sql)
                ->execute($params);

            /*
             * Trocando credenciais ou ambiente, força nova verificação
             * na próxima consulta.
             */
            AsaasSettings::clearPixVerification($environment);

            Support::flash(
                'success',
                'Configuração Asaas salva. Faça o teste do PIX abaixo.'
            );

            Support::redirect(
                '/admin/configuracoes/asaas.php'
            );
        }

        if ($action === 'testar_pix') {
            $pixResult = PixAvailabilityService::checkNow();
        }

        if ($action === 'sincronizar_webhook') {
            $token = AsaasSettings::webhookToken();

            if ($token === '') {
                throw new RuntimeException(
                    'Configure e salve o token do webhook do ambiente atual antes de sincronizar.'
                );
            }

            $emailSettings = EmailSettings::get();

            $webhookEmail = trim(
                (string)(
                    $emailSettings['reply_to']
                    ?? $emailSettings['remetente_email']
                    ?? ''
                )
            );

            if (!filter_var($webhookEmail, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException(
                    'Configure um e-mail válido em Admin > E-mail antes de sincronizar o webhook.'
                );
            }

            $asaas = new AsaasService();

            $synced = $asaas->syncPaymentWebhook(
                APP_URL . '/api/asaas/webhook.php',
                $webhookEmail,
                $token
            );

            $actionText = ($synced['_checkoutAction'] ?? '') === 'created'
                ? 'criado'
                : 'atualizado';

            $message = 'Webhook de pagamentos ' . $actionText
                . ' e fila reativada no ambiente '
                . (
                    AsaasSettings::activeEnvironment() === 'producao'
                        ? 'Produção'
                        : 'Sandbox'
                )
                . '.';

            if ((int)($synced['_checkoutDuplicates'] ?? 0) > 0) {
                $message .= ' Atenção: existe mais de um webhook cadastrado com a mesma URL no Asaas.';
            }

            Support::flash(
                'success',
                $message
            );

            Support::redirect(
                '/admin/configuracoes/asaas.php'
            );
        }

        if ($action === 'aprovar_sandbox') {
            $asaas = new AsaasService();
            $asaas->approveSandboxAccount();

            Support::flash(
                'success',
                'Solicitação de aprovação do Sandbox realizada. Consulte novamente a situação cadastral.'
            );

            Support::redirect(
                '/admin/configuracoes/asaas.php'
            );
        }

        if ($action === 'criar_pix') {
            if (AsaasSettings::activeEnvironment() !== 'sandbox') {
                throw new RuntimeException(
                    'A criação automática de chave por esta tela está disponível somente no Sandbox.'
                );
            }

            $asaas = new AsaasService();
            $created = $asaas->createRandomPixKey();

            /*
             * Testa novamente. Caso a chave ainda não esteja ACTIVE,
             * o painel mostrará que é necessário aguardar.
             */
            try {
                $pixResult = PixAvailabilityService::checkNow();
                $pixResult['criada'] = $created;
            } catch (Throwable $e) {
                $pixResult = [
                    'ambiente' => 'sandbox',
                    'disponivel' => false,
                    'verificadoEm' => date('Y-m-d H:i:s'),
                    'chave' => $created['key'] ?? null,
                    'chaves' => [],
                    'mensagem' => 'Chave solicitada, mas ainda não foi possível confirmá-la como ACTIVE.',
                    'erro' => $e->getMessage(),
                    'criada' => $created,
                ];
            }
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$s = AsaasSettings::get();
$environment = AsaasSettings::activeEnvironment();
$pixSaved = AsaasSettings::pixVerification($environment);

$accountStatus = null;
$accountStatusError = '';

try {
    if (AsaasSettings::enabled() && AsaasSettings::apiKey() !== '') {
        $accountStatus = (new AsaasService())->getAccountStatus();
    }
} catch (Throwable $e) {
    $accountStatusError = $e->getMessage();
}

require dirname(__DIR__) . '/_header.php';
?>

<?php if ($error): ?>
    <div class="alert error"><?= Support::e($error) ?></div>
<?php endif; ?>

<div class="panel">
    <h2>Credenciais Asaas</h2>

    <p>
        As credenciais ficam criptografadas neste banco independente.
        Deixe um campo secreto vazio para manter o valor já salvo.
    </p>

    <form method="post">
        <input
            type="hidden"
            name="_csrf"
            value="<?= Support::csrf() ?>"
        >
        <input type="hidden" name="acao" value="salvar">

        <div class="checks">
            <label>
                <input
                    type="checkbox"
                    name="ativo"
                    value="1"
                    <?= !empty($s['ativo']) ? 'checked' : '' ?>
                >
                Integração ativa
            </label>
        </div>

        <div class="grid2">
            <label>
                Ambiente
                <select name="ambiente">
                    <option
                        value="sandbox"
                        <?= $s['ambiente'] === 'sandbox' ? 'selected' : '' ?>
                    >
                        Sandbox
                    </option>

                    <option
                        value="producao"
                        <?= $s['ambiente'] === 'producao' ? 'selected' : '' ?>
                    >
                        Produção
                    </option>
                </select>
            </label>

            <span></span>

            <label>
                API Key Sandbox
                <input
                    type="password"
                    name="api_key_sandbox"
                    placeholder="<?= !empty($s['api_key_sandbox'])
                        ? 'Configurada — deixe vazio para manter'
                        : 'Cole a API Key' ?>"
                >
            </label>

            <label>
                Webhook Token Sandbox
                <input
                    type="password"
                    name="webhook_token_sandbox"
                    placeholder="<?= !empty($s['webhook_token_sandbox'])
                        ? 'Configurado — deixe vazio para manter'
                        : 'mínimo 32 caracteres' ?>"
                >
            </label>

            <label>
                API Key Produção
                <input
                    type="password"
                    name="api_key_producao"
                    placeholder="<?= !empty($s['api_key_producao'])
                        ? 'Configurada — deixe vazio para manter'
                        : 'Cole a API Key' ?>"
                >
            </label>

            <label>
                Webhook Token Produção
                <input
                    type="password"
                    name="webhook_token_producao"
                    placeholder="<?= !empty($s['webhook_token_producao'])
                        ? 'Configurado — deixe vazio para manter'
                        : 'mínimo 32 caracteres' ?>"
                >
            </label>
        </div>

        <button class="btn primary" type="submit">
            Salvar
        </button>
    </form>

    <hr>

    <h3>Webhook</h3>

    <p>URL utilizada pelo Checkout:</p>
    <code><?= APP_URL ?>/api/asaas/webhook.php</code>

    <p>
        O Asaas envia o token no header
        <code>asaas-access-token</code>. O Checkout aceita os tokens
        configurados de Sandbox e Produção nesta mesma URL.
    </p>

    <div class="alert muted">
        <strong>Recomendado:</strong>
        use o botão abaixo para criar ou atualizar o webhook do ambiente
        selecionado. A sincronização limita os eventos a cobranças,
        aplica o token configurado e define
        <code>interrupted=false</code> para reativar a fila.
    </div>

    <form method="post">
        <input
            type="hidden"
            name="_csrf"
            value="<?= Support::csrf() ?>"
        >
        <input
            type="hidden"
            name="acao"
            value="sincronizar_webhook"
        >

        <button class="btn primary" type="submit">
            Sincronizar webhook de pagamentos
        </button>
    </form>
</div>


<div class="panel">
    <div class="asaas-pix-head">
        <div>
            <h2>Situação cadastral da conta</h2>
            <p>
                Esta situação influencia a emissão de cobranças no Asaas.
            </p>
        </div>

        <?php if (($accountStatus['general'] ?? '') === 'APPROVED'): ?>
            <span class="badge paid">Conta aprovada</span>
        <?php elseif ($accountStatus): ?>
            <span class="badge error-badge">
                <?= Support::e($accountStatus['general'] ?? 'Pendente') ?>
            </span>
        <?php else: ?>
            <span class="badge muted">Não consultado</span>
        <?php endif; ?>
    </div>

    <?php if ($accountStatus): ?>
        <div class="stats account-status-grid">
            <div class="stat">
                <small>Geral</small>
                <strong><?= Support::e($accountStatus['general'] ?? '—') ?></strong>
            </div>

            <div class="stat">
                <small>Dados comerciais</small>
                <strong><?= Support::e($accountStatus['commercialInfo'] ?? '—') ?></strong>
            </div>

            <div class="stat">
                <small>Documentação</small>
                <strong><?= Support::e($accountStatus['documentation'] ?? '—') ?></strong>
            </div>

            <div class="stat">
                <small>Conta bancária</small>
                <strong><?= Support::e($accountStatus['bankAccountInfo'] ?? '—') ?></strong>
            </div>
        </div>

        <?php if (($accountStatus['general'] ?? '') !== 'APPROVED'): ?>
            <div class="alert error">
                A conta ainda não está 100% aprovada. Enquanto isso,
                determinadas formas de cobrança podem ser recusadas pelo Asaas.
            </div>

            <?php if ($environment === 'sandbox'): ?>
                <form
                    method="post"
                    onsubmit="return confirm('Solicitar aprovação da conta Sandbox agora?');"
                >
                    <input
                        type="hidden"
                        name="_csrf"
                        value="<?= Support::csrf() ?>"
                    >
                    <input
                        type="hidden"
                        name="acao"
                        value="aprovar_sandbox"
                    >

                    <button class="btn primary" type="submit">
                        Aprovar conta Sandbox
                    </button>
                </form>
            <?php endif; ?>
        <?php endif; ?>

    <?php elseif ($accountStatusError): ?>
        <div class="alert error">
            <?= Support::e($accountStatusError) ?>
        </div>
    <?php else: ?>
        <div class="alert muted">
            Ative a integração e configure a API Key para consultar
            a situação cadastral.
        </div>
    <?php endif; ?>
</div>

<div class="panel">
    <div class="asaas-pix-head">

        <div>
            <h2>Diagnóstico do PIX</h2>
            <p>
                Ambiente atual:
                <strong><?= $environment === 'producao' ? 'Produção' : 'Sandbox' ?></strong>
            </p>
        </div>

        <?php
        $showStatus = $pixResult ?: $pixSaved;
        $available = $showStatus['disponivel'] ?? null;
        ?>

        <?php if ($available === true): ?>
            <span class="badge paid">
                <?= $environment === 'sandbox'
                    ? 'Chave PIX ativa'
                    : 'PIX disponível' ?>
            </span>
        <?php elseif ($available === false): ?>
            <span class="badge error-badge">PIX indisponível</span>
        <?php else: ?>
            <span class="badge muted">Não verificado</span>
        <?php endif; ?>
    </div>

    <?php if ($pixResult): ?>
        <?php if (!empty($pixResult['disponivel'])): ?>
            <div class="alert success">
                <strong>Chave Pix ativa encontrada.</strong>

                A API Key do ambiente atual possui pelo menos uma
                chave PIX <code>ACTIVE</code>.
                Verifique também no painel Asaas se
                <strong>Recebimentos via Pix</strong> está habilitado.
            </div>

            <?php if (!empty($pixResult['chaves'])): ?>
                <div class="tablewrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Chave</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>Criada em</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($pixResult['chaves'] as $key): ?>
                            <tr>
                                <td>
                                    <code><?= Support::e($key['key'] ?? '') ?></code>
                                </td>
                                <td><?= Support::e($key['type'] ?? '') ?></td>
                                <td>
                                    <span class="badge paid">
                                        <?= Support::e($key['status'] ?? '') ?>
                                    </span>
                                </td>
                                <td><?= Support::e($key['dateCreated'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert error">
                <strong>Nenhuma chave Pix ACTIVE encontrada.</strong>
                <?= Support::e(
                    $pixResult['mensagem']
                    ?? 'O PIX não está disponível para esta API Key.'
                ) ?>
            </div>

            <?php if (!empty($pixResult['erro'])): ?>
                <p class="asaas-tech-error">
                    <strong>Retorno:</strong>
                    <?= Support::e($pixResult['erro']) ?>
                </p>
            <?php endif; ?>
        <?php endif; ?>
    <?php elseif (!empty($pixSaved['verificadoEm'])): ?>
        <div class="pix-last-check">
            <span>
                Última verificação:
                <strong>
                    <?= Support::e(
                        date(
                            'd/m/Y H:i:s',
                            strtotime((string)$pixSaved['verificadoEm'])
                        )
                    ) ?>
                </strong>
            </span>

            <?php if (!empty($pixSaved['chave'])): ?>
                <span>
                    Chave ativa:
                    <code><?= Support::e($pixSaved['chave']) ?></code>
                </span>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="alert muted">
            Clique em <strong>Testar PIX</strong> para verificar a API Key
            do ambiente selecionado.
        </div>
    <?php endif; ?>

    <div class="actions">
        <form method="post">
            <input
                type="hidden"
                name="_csrf"
                value="<?= Support::csrf() ?>"
            >
            <input
                type="hidden"
                name="acao"
                value="testar_pix"
            >

            <button class="btn primary" type="submit">
                Testar PIX
            </button>
        </form>

        <?php if ($environment === 'sandbox'): ?>
            <form
                method="post"
                onsubmit="return confirm('Criar uma nova chave Pix aleatória no Sandbox?');"
            >
                <input
                    type="hidden"
                    name="_csrf"
                    value="<?= Support::csrf() ?>"
                >
                <input
                    type="hidden"
                    name="acao"
                    value="criar_pix"
                >

                <button class="btn" type="submit">
                    Criar chave aleatória no Sandbox
                </button>
            </form>
        <?php endif; ?>
    </div>

    <?php if ($environment === 'sandbox'): ?>
        <p class="form-note">
            No Sandbox, a chave criada pela API é do tipo aleatória (EVP).
            Aguarde pelo menos 1 minuto antes de tentar criar outra chave.
        </p>
    <?php endif; ?>

    <div class="alert muted">
        <strong>Importante para o PIX:</strong>
        além de possuir uma chave <code>ACTIVE</code>, o recebimento via Pix
        também precisa estar habilitado na conta Asaas do ambiente atual.
        O Checkout cria cobranças Pix somente com
        <code>billingType=PIX</code>.
    </div>
</div>

<div class="panel">
    <h3>Notificações ao pagador</h3>

    <p>
        O Asaas não deve enviar notificações de cobrança ao cliente.
        Os e-mails são enviados exclusivamente pelo Checkout.
    </p>

    <a
        class="btn"
        href="<?= APP_URL ?>/admin/configuracoes/asaas-notificacoes.php"
    >
        Gerenciar notificações Asaas
    </a>
</div>

<?php require dirname(__DIR__) . '/_footer.php'; ?>
