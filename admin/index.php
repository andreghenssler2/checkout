<?php
require_once dirname(__DIR__) . '/bootstrap.php';
Auth::require();

$db = Database::connection();
$pageTitle = 'Dashboard';

$cards = [
    'Ofertas ativas' => (int)$db->query(
        'SELECT COUNT(*) FROM ofertas WHERE ativo=1'
    )->fetchColumn(),
    'Palpites ativos' => (int)$db->query(
        'SELECT COUNT(*) FROM palpites_eventos WHERE ativo=1'
    )->fetchColumn(),
    'Palpites pagos' => (int)$db->query(
        "SELECT COUNT(*) FROM palpites WHERE statusPagamento='Pago'"
    )->fetchColumn(),
    'Pagamentos pagos' => (int)$db->query(
        "SELECT COUNT(*) FROM pagamentos WHERE status='Pago'"
    )->fetchColumn(),
    'Total recebido' => (float)$db->query(
        "SELECT COALESCE(SUM(valor),0) FROM pagamentos WHERE status='Pago'"
    )->fetchColumn(),
    'Pendentes' => (int)$db->query(
        "SELECT COUNT(*) FROM pagamentos WHERE status='Pendente'"
    )->fetchColumn(),
];

require __DIR__ . '/_header.php';
?>
<div class="stats">
    <?php foreach ($cards as $key => $value): ?>
        <div class="stat">
            <small><?= Support::e($key) ?></small>
            <strong>
                <?= $key === 'Total recebido'
                    ? Support::money((float)$value)
                    : Support::e($value) ?>
            </strong>
        </div>
    <?php endforeach; ?>
</div>

<div class="panel">
    <h2>Acesso rápido</h2>
    <div class="actions">
        <a class="btn primary" href="<?= APP_URL ?>/admin/ofertas/form.php">Nova oferta</a>
        <a class="btn primary" href="<?= APP_URL ?>/admin/palpites/form.php">Novo palpite</a>
        <a class="btn" href="<?= APP_URL ?>/admin/relatorios/">Relatórios</a>
        <a class="btn" href="<?= APP_URL ?>/admin/configuracoes/asaas.php">Configurar Asaas</a>
    </div>
</div>
<?php require __DIR__ . '/_footer.php'; ?>
