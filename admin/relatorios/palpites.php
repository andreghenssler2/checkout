<?php

require_once dirname(__DIR__, 2) . '/bootstrap.php';

Auth::require();

$pageTitle = 'Relatório de Palpites';

$filters = [
    'idEventoPalpite' => (int)($_GET['idEventoPalpite'] ?? 0),
    'formaPagamento' => trim(
        (string)($_GET['formaPagamento'] ?? '')
    ),
    'dataInicio' => trim(
        (string)($_GET['dataInicio'] ?? '')
    ),
    'dataFim' => trim(
        (string)($_GET['dataFim'] ?? '')
    ),
];

$events = PalpiteRepository::all();
$summary = ReportRepository::palpiteSummary($filters);
$breakdown = ReportRepository::palpiteBreakdown($filters);
$details = ReportRepository::palpiteDetails($filters);

$winnerCount = count(
    array_filter(
        $details,
        static fn (array $item): bool =>
            !empty($item['_ganhador'])
    )
);

$pdfQuery = http_build_query([
    'idEventoPalpite' => $filters['idEventoPalpite'],
    'formaPagamento' => $filters['formaPagamento'],
    'dataInicio' => $filters['dataInicio'],
    'dataFim' => $filters['dataFim'],
]);

$dompdfInstalled = PdfReportService::isInstalled();

require dirname(__DIR__) . '/_header.php';
?>

<div class="actions">
    <a class="btn" href="<?= APP_URL ?>/admin/relatorios/">
        Voltar aos relatórios
    </a>

    <?php if ($dompdfInstalled): ?>
        <a
            class="btn primary"
            href="<?= APP_URL ?>/admin/relatorios/palpites-exportar.php?<?= Support::e($pdfQuery) ?>"
        >
            Exportar PDF
        </a>
    <?php else: ?>
        <a
            class="btn"
            href="<?= APP_URL ?>/admin/relatorios/dompdf.php"
        >
            Configurar Dompdf
        </a>
    <?php endif; ?>

    <?php if ($filters['idEventoPalpite'] > 0): ?>
        <a
            class="btn primary"
            href="<?= APP_URL ?>/admin/palpites/acompanhamento.php?id=<?= $filters['idEventoPalpite'] ?>"
        >
            Acompanhar este jogo
        </a>
    <?php endif; ?>
</div>

<form method="get" class="panel report-filters">
    <div class="report-filter-grid">
        <label>
            Jogo / formulário
            <select name="idEventoPalpite">
                <option value="0">Todos os jogos</option>

                <?php foreach ($events as $event): ?>
                    <option
                        value="<?= (int)$event['idEventoPalpite'] ?>"
                        <?= $filters['idEventoPalpite']
                            === (int)$event['idEventoPalpite']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= Support::e($event['titulo']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Forma de pagamento
            <select name="formaPagamento">
                <option value="">Todas</option>

                <option
                    value="PIX"
                    <?= $filters['formaPagamento'] === 'PIX'
                        ? 'selected'
                        : '' ?>
                >
                    PIX
                </option>

                <option
                    value="Cartao"
                    <?= $filters['formaPagamento'] === 'Cartao'
                        ? 'selected'
                        : '' ?>
                >
                    Cartão de Crédito
                </option>
            </select>
        </label>

        <label>
            De
            <input
                type="date"
                name="dataInicio"
                value="<?= Support::e($filters['dataInicio']) ?>"
            >
        </label>

        <label>
            Até
            <input
                type="date"
                name="dataFim"
                value="<?= Support::e($filters['dataFim']) ?>"
            >
        </label>
    </div>

    <div class="actions report-filter-actions">
        <button class="btn primary" type="submit">
            Aplicar filtros
        </button>

        <a
            class="btn"
            href="<?= APP_URL ?>/admin/relatorios/palpites.php"
        >
            Limpar
        </a>
    </div>
</form>

<div class="stats report-stats">
    <div class="stat">
        <small>Palpites enviados</small>
        <strong><?= (int)($summary['totalPalpites'] ?? 0) ?></strong>
    </div>

    <div class="stat">
        <small>Palpites pagos</small>
        <strong><?= (int)($summary['palpitesPagos'] ?? 0) ?></strong>
    </div>

    <div class="stat">
        <small>Valor bruto recebido</small>
        <strong>
            <?= Support::money((float)($summary['bruto'] ?? 0)) ?>
        </strong>
    </div>

    <div class="stat">
        <small>Valor líquido registrado</small>
        <strong>
            <?= Support::money((float)($summary['liquido'] ?? 0)) ?>
        </strong>
    </div>

    <div class="stat">
        <small>Tarifas registradas</small>
        <strong>
            <?= Support::money((float)($summary['taxas'] ?? 0)) ?>
        </strong>
    </div>

    <div class="stat">
        <small>Ganhadores pagos</small>
        <strong><?= $winnerCount ?></strong>
    </div>
</div>

<?php if ((int)($summary['semLiquido'] ?? 0) > 0): ?>
    <div class="alert muted">
        Alguns pagamentos antigos não possuem valor líquido/tarifa registrado.
        Os valores líquidos consideram somente os dados já recebidos do Asaas.
    </div>
<?php endif; ?>

<?php if ($winnerCount > 0): ?>
    <div class="panel winners-panel">
        <span class="content-type-badge">Resultado final</span>
        <h2>🏆 Ganhadores</h2>
        <p>
            Os ganhadores abaixo acertaram o placar final e possuem
            pagamento confirmado.
        </p>

        <div class="winner-cards">
            <?php foreach ($details as $item): ?>
                <?php if (empty($item['_ganhador'])) continue; ?>

                <div class="winner-card">
                    <strong><?= Support::e($item['nome']) ?></strong>
                    <span><?= Support::e($item['eventoTitulo']) ?></span>
                    <b><?= Support::e($item['palpite']) ?></b>
                    <small>
                        <?= isset($item['valor'])
                            ? Support::money((float)$item['valor'])
                            : '—' ?>
                    </small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div class="panel tablewrap">
    <h2>Resumo por palpite marcado</h2>

    <table>
        <thead>
        <tr>
            <th>Jogo</th>
            <th>Palpite</th>
            <th>Participações</th>
            <th>Pagos</th>
            <th>Valor bruto recebido</th>
            <th>Líquido registrado</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($breakdown as $row): ?>
            <tr>
                <td><?= Support::e($row['eventoTitulo']) ?></td>
                <td><strong><?= Support::e($row['palpite']) ?></strong></td>
                <td><?= (int)$row['participacoes'] ?></td>
                <td><?= (int)$row['pagos'] ?></td>
                <td><?= Support::money((float)$row['bruto']) ?></td>
                <td><?= Support::money((float)$row['liquido']) ?></td>
            </tr>
        <?php endforeach; ?>

        <?php if (!$breakdown): ?>
            <tr>
                <td colspan="6">Nenhum palpite encontrado.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="panel tablewrap">
    <h2>Participantes e pagamentos</h2>

    <p class="report-help">
        Em jogos finalizados, os ganhadores pagos aparecem primeiro.
    </p>

    <table>
        <thead>
        <tr>
            <th>Resultado</th>
            <th>Jogo</th>
            <th>Quem participou</th>
            <th>Palpite marcado</th>
            <th>Pagamento</th>
            <th>Forma</th>
            <th>Valor</th>
            <th>Líquido</th>
            <th>Data</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($details as $item): ?>
            <tr class="<?=
                !empty($item['_ganhador'])
                    ? 'winner-row'
                    : (!empty($item['_acertando'])
                        ? 'correct-row'
                        : '')
            ?>">
                <td>
                    <?php if (!empty($item['_ganhador'])): ?>
                        <span class="badge paid">🏆 Ganhador</span>
                    <?php elseif (
                        ($item['status_jogo'] ?? '') === 'EmAndamento'
                        && !empty($item['_acertando'])
                    ): ?>
                        <span class="badge paid">Acertando</span>
                    <?php else: ?>
                        <span class="badge muted">—</span>
                    <?php endif; ?>
                </td>

                <td>
                    <strong><?= Support::e($item['eventoTitulo']) ?></strong>

                    <?php if (
                        $item['placar_casa'] !== null
                        && $item['placar_visitante'] !== null
                    ): ?>
                        <br>
                        <small>
                            Placar:
                            <?= (int)$item['placar_casa'] ?>
                            x
                            <?= (int)$item['placar_visitante'] ?>
                        </small>
                    <?php endif; ?>
                </td>

                <td>
                    <strong><?= Support::e($item['nome']) ?></strong><br>
                    <small><?= Support::e($item['email']) ?></small><br>
                    <small><?= Support::e($item['telefone']) ?></small><br>
                    <small>CPF: <?= Support::e($item['cpf']) ?></small>
                </td>

                <td>
                    <strong><?= Support::e($item['palpite']) ?></strong>
                </td>

                <td>
                    <span class="badge <?= ($item['pagamentoStatus'] ?? '') === 'Pago'
                        ? 'paid'
                        : 'muted' ?>">
                        <?= Support::e(
                            $item['pagamentoStatus']
                            ?? $item['statusPagamento']
                        ) ?>
                    </span>
                </td>

                <td>
                    <?= !empty($item['formaPagamento'])
                        ? Support::e(
                            ReportRepository::paymentMethodLabel(
                                (string)$item['formaPagamento']
                            )
                        )
                        : '—' ?>
                </td>

                <td>
                    <?= $item['valor'] !== null
                        ? Support::money((float)$item['valor'])
                        : '—' ?>
                </td>

                <td>
                    <?= $item['valorLiquido'] !== null
                        ? Support::money((float)$item['valorLiquido'])
                        : '—' ?>
                </td>

                <td>
                    <?= !empty($item['dataPagamento'])
                        ? Support::e(
                            date(
                                'd/m/Y H:i',
                                strtotime($item['dataPagamento'])
                            )
                        )
                        : Support::e(
                            date(
                                'd/m/Y H:i',
                                strtotime($item['criadoEm'])
                            )
                        ) ?>
                </td>
            </tr>
        <?php endforeach; ?>

        <?php if (!$details): ?>
            <tr>
                <td colspan="9">
                    Nenhuma participação encontrada.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require dirname(__DIR__) . '/_footer.php'; ?>
