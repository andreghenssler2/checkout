<?php

require_once dirname(__DIR__, 2) . '/bootstrap.php';

Auth::require();

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

$offerLabel = 'Todas as ofertas';

if ($filters['idOferta'] > 0) {
    foreach ($offers as $offer) {
        if (
            (int)$offer['idOferta']
            === $filters['idOferta']
        ) {
            $offerLabel = (string)$offer['titulo'];
            break;
        }
    }
}

$methodLabel = $filters['formaPagamento'] !== ''
    ? ReportRepository::paymentMethodLabel(
        $filters['formaPagamento']
    )
    : 'Todas as formas';

$period = 'Todo o período';

if (
    $filters['dataInicio'] !== ''
    || $filters['dataFim'] !== ''
) {
    $period = ($filters['dataInicio'] ?: 'início')
        . ' até '
        . ($filters['dataFim'] ?: 'hoje');
}

$groupLabel = match ($filters['agrupar']) {
    'ano' => 'Ano',
    'mes' => 'Mês',
    default => 'Dia',
};

ob_start();
?>

<div class="filters">
    <strong>Filtros:</strong>
    <?= Support::e(
        PdfReportService::filterDescription([
            'Oferta: ' . $offerLabel,
            'Forma: ' . $methodLabel,
            'Período: ' . $period,
            'Agrupamento: ' . $groupLabel,
        ])
    ) ?>
</div>

<table class="summary">
    <tr>
        <td>
            <span class="label">Pagamentos recebidos</span>
            <span class="value">
                <?= (int)($summary['quantidade'] ?? 0) ?>
            </span>
        </td>

        <td>
            <span class="label">Valor bruto</span>
            <span class="value">
                <?= Support::e(
                    PdfReportService::money(
                        $summary['bruto'] ?? 0
                    )
                ) ?>
            </span>
        </td>

        <td>
            <span class="label">Valor líquido registrado</span>
            <span class="value">
                <?= Support::e(
                    PdfReportService::money(
                        $summary['liquido'] ?? 0
                    )
                ) ?>
            </span>
        </td>

        <td>
            <span class="label">Tarifas registradas</span>
            <span class="value">
                <?= Support::e(
                    PdfReportService::money(
                        $summary['taxas'] ?? 0
                    )
                ) ?>
            </span>
        </td>

        <td>
            <span class="label">Sem líquido histórico</span>
            <span class="value">
                <?= (int)($summary['semLiquido'] ?? 0) ?>
            </span>
        </td>
    </tr>
</table>

<?php if ((int)($summary['semLiquido'] ?? 0) > 0): ?>
    <div class="notice">
        Existem pagamentos antigos sem valor líquido/tarifa registrado.
        O valor bruto permanece correto; líquido e tarifas consideram
        somente os registros em que o Asaas informou esses dados.
    </div>
<?php endif; ?>

<h2>Recebimentos por <?= Support::e(mb_strtolower($groupLabel)) ?></h2>

<table class="report">
    <thead>
    <tr>
        <th style="width:13%">Período</th>
        <th style="width:11%">PIX</th>
        <th style="width:11%">Cartão</th>
        <th style="width:11%">Boleto</th>
        <th style="width:14%">Total bruto</th>
        <th style="width:14%">Líquido</th>
        <th style="width:13%">Tarifas</th>
        <th style="width:7%">Qtd.</th>
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
            <td class="right"><?= Support::e(PdfReportService::money($row['PIX'])) ?></td>
            <td class="right"><?= Support::e(PdfReportService::money($row['Cartao'])) ?></td>
            <td class="right"><?= Support::e(PdfReportService::money($row['Boleto'])) ?></td>
            <td class="right"><strong><?= Support::e(PdfReportService::money($row['bruto'])) ?></strong></td>
            <td class="right"><?= Support::e(PdfReportService::money($row['liquido'])) ?></td>
            <td class="right"><?= Support::e(PdfReportService::money($row['taxas'])) ?></td>
            <td class="center"><?= (int)$row['quantidade'] ?></td>
        </tr>
    <?php endforeach; ?>

    <?php if (!$series): ?>
        <tr>
            <td colspan="8">
                Nenhum recebimento encontrado.
            </td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

<h2>Resumo por forma de pagamento</h2>

<table class="report">
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
            <td class="center"><?= (int)$row['quantidade'] ?></td>
            <td class="right"><?= Support::e(PdfReportService::money($row['bruto'])) ?></td>
            <td class="right"><?= Support::e(PdfReportService::money($row['liquido'])) ?></td>
            <td class="right"><?= Support::e(PdfReportService::money($row['taxas'])) ?></td>
        </tr>
    <?php endforeach; ?>

    <?php if (!$methods): ?>
        <tr>
            <td colspan="5">Nenhum pagamento recebido.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

<div class="page-break"></div>

<h2>Pagamentos recebidos</h2>

<table class="report">
    <thead>
    <tr>
        <th style="width:10%">Data</th>
        <th style="width:16%">Oferta</th>
        <th style="width:22%">Pagador</th>
        <th style="width:11%">Forma</th>
        <th style="width:10%">Bruto</th>
        <th style="width:10%">Líquido</th>
        <th style="width:9%">Tarifa</th>
        <th style="width:12%">Asaas</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($details as $item): ?>
        <tr>
            <td>
                <?= Support::e(
                    PdfReportService::dateTime(
                        $item['dataPagamento']
                        ?: $item['criadoEm']
                    )
                ) ?>
            </td>

            <td>
                <strong><?= Support::e($item['ofertaTitulo']) ?></strong>
            </td>

            <td>
                <strong><?= Support::e($item['nome']) ?></strong><br>
                <span class="small">
                    <?= Support::e($item['email']) ?><br>
                    <?= Support::e($item['telefone']) ?><br>
                    CPF: <?= Support::e(PdfReportService::cpf($item['cpf'])) ?>
                </span>
            </td>

            <td>
                <?= Support::e(
                    ReportRepository::paymentMethodLabel(
                        (string)$item['formaPagamento']
                    )
                ) ?>
            </td>

            <td class="right">
                <strong>
                    <?= Support::e(
                        PdfReportService::money($item['valor'])
                    ) ?>
                </strong>
            </td>

            <td class="right">
                <?= $item['valorLiquido'] !== null
                    ? Support::e(
                        PdfReportService::money(
                            $item['valorLiquido']
                        )
                    )
                    : '—' ?>
            </td>

            <td class="right">
                <?= $item['taxa'] !== null
                    ? Support::e(
                        PdfReportService::money(
                            $item['taxa']
                        )
                    )
                    : '—' ?>
            </td>

            <td class="small">
                <?= Support::e($item['asaasPaymentId'] ?? '—') ?>
            </td>
        </tr>
    <?php endforeach; ?>

    <?php if (!$details): ?>
        <tr>
            <td colspan="8">
                Nenhum pagamento encontrado.
            </td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

<?php
$body = (string)ob_get_clean();

PdfReportService::stream(
    'Relatório de Ofertas',
    $body,
    'relatorio-ofertas-' . date('Ymd-His') . '.pdf',
    'landscape'
);
