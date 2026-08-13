<?php
require_once dirname(__DIR__, 2) . '/bootstrap.php';
Auth::require();

$id = (int)($_GET['id'] ?? 0);
$event = PalpiteRepository::find($id);

if (!$event) {
    http_response_code(404);
    die('Formulário de palpite não encontrado.');
}

$pageTitle = 'Participações - ' . $event['titulo'];
$items = PalpiteRepository::annotateEntries(
    PalpiteRepository::entries($id),
    $event
);

require dirname(__DIR__) . '/_header.php';
?>

<div class="actions">
    <a class="btn" href="<?= APP_URL ?>/admin/palpites/">Voltar</a>
    <a class="btn primary" href="<?= APP_URL ?>/palpite/<?= Support::e($event['slug']) ?>" target="_blank">Abrir formulário</a>
    <a class="btn" href="<?= APP_URL ?>/admin/palpites/acompanhamento.php?id=<?= $id ?>">Acompanhar jogo</a>
    <a class="btn" href="<?= APP_URL ?>/admin/relatorios/palpites.php?idEventoPalpite=<?= $id ?>">Relatório</a>
</div>

<div class="panel">
    <h2><?= Support::e($event['titulo']) ?></h2>
    <p><?= Support::e($event['equipe_casa']) ?> x <?= Support::e($event['equipe_visitante']) ?></p>
    <p>
        <strong>Total:</strong> <?= count($items) ?>
        &nbsp; • &nbsp;
        <strong>Pagos:</strong> <?= count(array_filter($items, fn($i) => ($i['pagamentoStatus'] ?? '') === 'Pago')) ?>
    </p>
</div>

<?php if (($event['status_jogo'] ?? '') === 'Finalizado'): ?>
    <div class="alert success">
        <strong>Jogo finalizado.</strong>
        Os ganhadores com pagamento confirmado aparecem primeiro.
    </div>
<?php endif; ?>

<div class="panel tablewrap">
    <table>
        <thead>
        <tr>
            <th>Data</th>
            <th>Participante</th>
            <th>Palpite</th>
            <th>Forma</th>
            <th>Valor</th>
            <th>Pagamento</th>
            <th>Situação no jogo</th>
            <th>Asaas</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr class="<?= !empty($item['_ganhador'])
                ? 'winner-row'
                : (!empty($item['_acertando']) ? 'correct-row' : '') ?>">
                <td><?= Support::e($item['criadoEm']) ?></td>
                <td>
                    <strong><?= Support::e($item['nome']) ?></strong><br>
                    <small><?= Support::e($item['cpf']) ?></small><br>
                    <small><?= Support::e($item['telefone']) ?></small>
                </td>
                <td><strong><?= Support::e($item['palpite']) ?></strong></td>
                <td><?= Support::e($item['formaPagamento'] ?? '—') ?></td>
                <td><?= isset($item['valor']) ? Support::money((float)$item['valor']) : '—' ?></td>
                <td>
                    <span class="badge <?= ($item['pagamentoStatus'] ?? '') === 'Pago' ? 'paid' : 'muted' ?>">
                        <?= Support::e($item['pagamentoStatus'] ?? $item['statusPagamento']) ?>
                    </span>
                </td>
                <td>
                    <?php if (!empty($item['_ganhador'])): ?>
                        <span class="badge paid">🏆 Ganhador</span>
                    <?php elseif (!empty($item['_acertando'])): ?>
                        <span class="badge paid">Acertando</span>
                    <?php else: ?>
                        <span class="badge muted">—</span>
                    <?php endif; ?>
                </td>
                <td><small><?= Support::e($item['asaasPaymentId'] ?? '—') ?></small></td>
            </tr>
        <?php endforeach; ?>

        <?php if (!$items): ?>
            <tr><td colspan="8">Nenhum palpite enviado.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require dirname(__DIR__) . '/_footer.php'; ?>
