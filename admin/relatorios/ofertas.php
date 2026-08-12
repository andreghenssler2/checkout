<?php

require_once dirname(__DIR__, 2) . '/bootstrap.php';

Auth::require();

$pageTitle = 'Relatório de Ofertas';

$agrupar = trim(
    (string)($_GET['agrupar'] ?? 'dia')
);

if (!in_array($agrupar, ['dia', 'mes', 'ano'], true)) {
    $agrupar = 'dia';
}

$filters = [
    'idOferta' => (int)($_GET['idOferta'] ?? 0),
    'formaPagamento' => trim(
        (string)($_GET['formaPagamento'] ?? '')
    ),
    'dataInicio' => trim(
        (string)($_GET['dataInicio'] ?? '')
    ),
    'dataFim' => trim(
        (string)($_GET['dataFim'] ?? '')
    ),
    'agrupar' => $agrupar,
];

$offers = OfertaRepository::all();
$summary = ReportRepository::offerSummary($filters);
$series = ReportRepository::offerSeries($filters);
$methods = ReportRepository::offerByMethod($filters);
$details = ReportRepository::offerDetails($filters);

$pdfQuery = http_build_query([
    'idOferta' => $filters['idOferta'],
    'formaPagamento' => $filters['formaPagamento'],
    'dataInicio' => $filters['dataInicio'],
    'dataFim' => $filters['dataFim'],
    'agrupar' => $filters['agrupar'],
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
            href="<?= APP_URL ?>/admin/relatorios/ofertas-pdf.php?<?= Support::e($pdfQuery) ?>"
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
</div>

<form method="get" class="panel report-filters">
    <div class="report-filter-grid">
        <label>
            Oferta
            <select name="idOferta">
                <option value="0">Todas as ofertas</option>

                <?php foreach ($offers as $offer): ?>
                    <option
                        value="<?= (int)$offer['idOferta'] ?>"
                        <?= $filters['idOferta'] === (int)$offer['idOferta']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= Support::e($offer['titulo']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Forma de pagamento
            <select name="formaPagamento">
                <option value="">Todas</option>

                <?php foreach ([
                    'PIX' => 'PIX',
                    'Cartao' => 'Cartão de Crédito',
                    'Boleto' => 'Boleto',
                ] as $value => $label): ?>
                    <option
                        value="<?= Support::e($value) ?>"
                        <?= $filters['formaPagamento'] === $value
                            ? 'selected'
                            : '' ?>
                    >
                        <?= Support::e($label) ?>
                    </option>
                <?php endforeach; ?>
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

        <label>
            Agrupar recebimentos por
            <select name="agrupar">
                <option
                    value="dia"
                    <?= $filters['agrupar'] === 'dia' ? 'selected' : '' ?>
                >
                    Dia
                </option>

                <option
                    value="mes"
                    <?= $filters['agrupar'] === 'mes' ? 'selected' : '' ?>
                >
                    Mês
                </option>

                <option
                    value="ano"
                    <?= $filters['agrupar'] === 'ano' ? 'selected' : '' ?>
                >
                    Ano
                </option>
            </select>
        </label>
    </div>

    <div class="actions report-filter-actions">
        <button class="btn primary" type="submit">
            Aplicar filtros
        </button>

        <a
            class="btn"
            href="<?= APP_URL ?>/admin/relatorios/ofertas.php"
        >
            Limpar
        </a>
    </div>
</form>

<div class="stats report-stats">
    <div class="stat">
        <small>Pagamentos recebidos</small>
        <strong><?= (int)($summary['quantidade'] ?? 0) ?></strong>
    </div>

    <div class="stat">
        <small>Valor bruto</small>
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
</div>

<?php if ((int)($summary['semLiquido'] ?? 0) > 0): ?>
    <div class="alert muted">
        <?= (int)$summary['semLiquido'] ?>
        pagamento(s) antigo(s) não possuem valor líquido/tarifa registrado.
        O valor bruto permanece correto; líquido e tarifas consideram apenas
        os pagamentos em que o Asaas informou esses dados.
    </div>
<?php endif; ?>

<div class="panel tablewrap">
    <h2>
        Recebimentos por
        <?= $filters['agrupar'] === 'ano'
            ? 'ano'
            : ($filters['agrupar'] === 'mes' ? 'mês' : 'dia') ?>
    </h2>

    <table>
        <thead>
        <tr>
            <th>Período</th>
            <th>PIX</th>
            <th>Cartão</th>
            <th>Boleto</th>
            <th>Total bruto</th>
            <th>Líquido registrado</th>
            <th>Tarifas</th>
            <th>Qtd.</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($series as $row): ?>
            <tr>
                <td>
                    <strong>
                        <?= Support::e(
                            ReportRepository::periodLabel(
                                (string)$row['periodo'],
                                $filters['agrupar']
                            )
                        ) ?>
                    </strong>
                </td>
                <td><?= Support::money((float)$row['PIX']) ?></td>
                <td><?= Support::money((float)$row['Cartao']) ?></td>
                <td><?= Support::money((float)$row['Boleto']) ?></td>
                <td><strong><?= Support::money((float)$row['bruto']) ?></strong></td>
                <td><?= Support::money((float)$row['liquido']) ?></td>
                <td><?= Support::money((float)$row['taxas']) ?></td>
                <td><?= (int)$row['quantidade'] ?></td>
            </tr>
        <?php endforeach; ?>

        <?php if (!$series): ?>
            <tr>
                <td colspan="8">
                    Nenhum recebimento encontrado para os filtros selecionados.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="panel tablewrap">
    <h2>Por forma de pagamento</h2>

    <table>
        <thead>
        <tr>
            <th>Forma</th>
            <th>Pagamentos</th>
            <th>Bruto</th>
            <th>Líquido registrado</th>
            <th>Tarifas</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($methods as $row): ?>
            <tr>
                <td>
                    <strong>
                        <?= Support::e(
                            ReportRepository::paymentMethodLabel(
                                (string)$row['formaPagamento']
                            )
                        ) ?>
                    </strong>
                </td>
                <td><?= (int)$row['quantidade'] ?></td>
                <td><?= Support::money((float)$row['bruto']) ?></td>
                <td><?= Support::money((float)$row['liquido']) ?></td>
                <td><?= Support::money((float)$row['taxas']) ?></td>
            </tr>
        <?php endforeach; ?>

        <?php if (!$methods): ?>
            <tr>
                <td colspan="5">Nenhum pagamento recebido.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="panel tablewrap">
    <h2>Pagamentos recebidos</h2>
    <p class="report-help">
        Exibindo até os 2.000 pagamentos mais recentes dentro do filtro.
    </p>

    <table>
        <thead>
        <tr>
            <th>Data</th>
            <th>Oferta</th>
            <th>Pagador</th>
            <th>Forma</th>
            <th>Bruto</th>
            <th>Líquido</th>
            <th>Tarifa</th>
            <th>Asaas</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($details as $item): ?>
            <tr>
                <td>
                    <?= Support::e(
                        date(
                            'd/m/Y H:i',
                            strtotime(
                                $item['dataPagamento']
                                ?: $item['criadoEm']
                            )
                        )
                    ) ?>
                </td>

                <td>
                    <strong>
                        <?= Support::e($item['ofertaTitulo']) ?>
                    </strong>
                </td>

                <td>
                    <strong><?= Support::e($item['nome']) ?></strong><br>
                    <small><?= Support::e($item['email']) ?></small>
                </td>

                <td>
                    <?= Support::e(
                        ReportRepository::paymentMethodLabel(
                            (string)$item['formaPagamento']
                        )
                    ) ?>
                </td>

                <td>
                    <strong>
                        <?= Support::money((float)$item['valor']) ?>
                    </strong>
                </td>

                <td>
                    <?= $item['valorLiquido'] !== null
                        ? Support::money((float)$item['valorLiquido'])
                        : '—' ?>
                </td>

                <td>
                    <?= $item['taxa'] !== null
                        ? Support::money((float)$item['taxa'])
                        : '—' ?>
                </td>

                <td>
                    <small>
                        <?= Support::e($item['asaasPaymentId'] ?? '—') ?>
                    </small>
                </td>
            </tr>
        <?php endforeach; ?>

        <?php if (!$details): ?>
            <tr>
                <td colspan="8">Nenhum pagamento encontrado.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require dirname(__DIR__) . '/_footer.php'; ?>
