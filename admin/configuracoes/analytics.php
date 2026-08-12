<?php

require_once dirname(__DIR__, 2) . '/bootstrap.php';

Auth::require();

$pageTitle = 'Google Analytics';
$error = '';

$settings = AnalyticsSettings::get();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!Support::checkCsrf($_POST['_csrf'] ?? null)) {
            throw new RuntimeException(
                'Sessão expirada.'
            );
        }

        AnalyticsSettings::save(
            !empty($_POST['ativo']),
            (string)($_POST['measurement_id'] ?? '')
        );

        Support::flash(
            'success',
            'Configuração do Google Analytics salva.'
        );

        Support::redirect(
            '/admin/configuracoes/analytics.php'
        );
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$settings = AnalyticsSettings::get();
$measurementId = AnalyticsSettings::measurementId();

require dirname(__DIR__) . '/_header.php';
?>

<?php if ($error): ?>
    <div class="alert error">
        <?= Support::e($error) ?>
    </div>
<?php endif; ?>

<div class="panel">
    <span class="content-type-badge">
        Integração
    </span>

    <h2>Google Analytics 4</h2>

    <p>
        Informe o ID de medição da propriedade Google Analytics.
        Quando estiver ativo, a tag será adicionada automaticamente
        nas páginas públicas do Checkout.
    </p>

    <form method="post">
        <input
            type="hidden"
            name="_csrf"
            value="<?= Support::csrf() ?>"
        >

        <div class="checks">
            <label>
                <input
                    type="checkbox"
                    name="ativo"
                    value="1"
                    <?= !empty($settings['ativo'])
                        ? 'checked'
                        : '' ?>
                >

                Google Analytics ativo
            </label>
        </div>

        <div class="grid2">
            <label class="full">
                ID de medição (Measurement ID)
                <input
                    type="text"
                    name="measurement_id"
                    value="<?= Support::e(
                        $settings['measurement_id'] ?? ''
                    ) ?>"
                    placeholder="G-XXXXXXXXXX"
                    autocomplete="off"
                >

                <small>
                    Exemplo: G-XXXXXXXXXX
                </small>
            </label>
        </div>

        <button
            class="btn primary"
            type="submit"
        >
            Salvar configuração
        </button>
    </form>
</div>

<div class="panel">
    <h2>Status</h2>

    <?php if (AnalyticsSettings::enabled()): ?>
        <div class="alert success">
            <strong>Google Analytics ativo.</strong>
            A tag está sendo inserida nas páginas públicas com o ID
            <?= Support::e($measurementId) ?>.
        </div>
    <?php elseif (!empty($settings['ativo'])): ?>
        <div class="alert error">
            A integração está marcada como ativa, mas o ID de medição
            não é válido.
        </div>
    <?php else: ?>
        <div class="alert muted">
            Google Analytics está desativado.
        </div>
    <?php endif; ?>

    <p>
        O Analytics não é carregado nas páginas do painel administrativo,
        evitando que acessos do administrador sejam misturados com os
        acessos públicos.
    </p>
</div>

<div class="panel">
    <h2>Função disponível no código</h2>

    <p>
        Para páginas públicas adicionais, use dentro do
        <code>&lt;head&gt;</code>:
    </p>

    <pre class="code-block">&lt;?= AnalyticsService::renderHead() ?&gt;</pre>

    <p>
        Também existe uma função para eventos personalizados:
    </p>

    <pre class="code-block">&lt;?= AnalyticsService::renderEvent(
    'nome_do_evento',
    ['chave' =&gt; 'valor']
) ?&gt;</pre>
</div>

<?php require dirname(__DIR__) . '/_footer.php'; ?>
