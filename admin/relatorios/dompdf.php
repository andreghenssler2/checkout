<?php

require_once dirname(__DIR__, 2) . '/bootstrap.php';

Auth::require();

$pageTitle = 'Diagnóstico do Dompdf';

$requirements = PdfReportService::requirements();
$installed = !empty($requirements['dompdf']['ok']);
$root = dirname(__DIR__, 2);

require dirname(__DIR__) . '/_header.php';
?>

<div class="actions">
    <a
        class="btn"
        href="<?= APP_URL ?>/admin/relatorios/"
    >
        Voltar aos relatórios
    </a>
</div>

<div class="panel">
    <h2>Dompdf</h2>

    <?php if ($installed): ?>
        <div class="alert success">
            <strong>Dompdf instalado.</strong>
            A exportação dos relatórios em PDF está disponível.
        </div>
    <?php else: ?>
        <div class="alert error">
            <strong>Dompdf não encontrado.</strong>
            Instale a dependência no servidor para liberar a exportação em PDF.
        </div>
    <?php endif; ?>

    <div class="tablewrap">
        <table>
            <thead>
            <tr>
                <th>Requisito</th>
                <th>Status</th>
                <th>Detalhe</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($requirements as $item): ?>
                <tr>
                    <td>
                        <strong><?= Support::e($item['label']) ?></strong>
                    </td>
                    <td>
                        <span class="badge <?= !empty($item['ok']) ? 'paid' : 'muted' ?>">
                            <?= !empty($item['ok']) ? 'OK' : 'Pendente' ?>
                        </span>
                    </td>
                    <td>
                        <small><?= Support::e($item['value']) ?></small>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (!$installed): ?>
    <div class="panel">
        <h2>Instalar pelo Composer</h2>

        <p>
            No Terminal do cPanel, execute na raiz do Checkout:
        </p>

        <pre class="code-block">cd <?= Support::e($root) ?>
composer install --no-dev --optimize-autoloader</pre>

        <p>
            Depois da instalação, deve existir:
        </p>

        <pre class="code-block"><?= Support::e($root) ?>/vendor/autoload.php</pre>

        <p>
            Atualize esta página após executar o comando.
        </p>
    </div>

    <div class="panel">
        <h2>Instalação manual</h2>

        <p>
            Se não houver Composer no servidor, use a distribuição
            <strong>empacotada oficial</strong> do Dompdf e extraia para:
        </p>

        <pre class="code-block"><?= Support::e($root) ?>/lib/dompdf/</pre>

        <p>
            O arquivo abaixo precisa existir:
        </p>

        <pre class="code-block"><?= Support::e($root) ?>/lib/dompdf/autoload.inc.php</pre>
    </div>
<?php endif; ?>

<?php require dirname(__DIR__) . '/_footer.php'; ?>
