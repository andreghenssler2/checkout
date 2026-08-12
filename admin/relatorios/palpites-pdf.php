<?php

require_once dirname(__DIR__, 2) . '/bootstrap.php';

Auth::require();

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

$options = [
    'secaoResumo' => !empty($_GET['secaoResumo']),
    'secaoGanhadores' => !empty($_GET['secaoGanhadores']),
    'secaoResumoPalpites' => !empty($_GET['secaoResumoPalpites']),
    'secaoParticipantes' => !empty($_GET['secaoParticipantes']),
    'mostrarFiltros' => !empty($_GET['mostrarFiltros']),
];

$fieldNames = [
    'campoResultado',
    'campoJogo',
    'campoNome',
    'campoEmail',
    'campoTelefone',
    'campoCpf',
    'campoPalpite',
    'campoPagamento',
    'campoForma',
    'campoValor',
    'campoLiquido',
    'campoData',
    'campoAsaas',
];

$fields = [];

foreach ($fieldNames as $field) {
    $fields[$field] = !empty($_GET[$field]);
}

$scope = trim(
    (string)($_GET['escopoParticipantes'] ?? 'todos')
);

if (
    !in_array(
        $scope,
        ['todos', 'pagos', 'ganhadores'],
        true
    )
) {
    $scope = 'todos';
}

/*
 * Se alguém acessar a rota diretamente sem nenhuma opção,
 * aplica um conjunto padrão seguro em vez de gerar PDF vazio.
 */
if (
    !$options['secaoResumo']
    && !$options['secaoGanhadores']
    && !$options['secaoResumoPalpites']
    && !$options['secaoParticipantes']
) {
    $options['secaoResumo'] = true;
    $options['secaoParticipantes'] = true;
}

if (
    $options['secaoParticipantes']
    && !array_filter($fields)
) {
    $fields['campoNome'] = true;
    $fields['campoPalpite'] = true;
    $fields['campoPagamento'] = true;
    $fields['campoValor'] = true;
}

$events = PalpiteRepository::all();
$summary = ReportRepository::palpiteSummary($filters);
$breakdown = ReportRepository::palpiteBreakdown($filters);
$details = ReportRepository::palpiteDetails($filters);

$winnerItems = array_values(
    array_filter(
        $details,
        static fn (array $item): bool =>
            !empty($item['_ganhador'])
    )
);

$participantItems = array_values(
    array_filter(
        $details,
        static function (array $item) use ($scope): bool {
            return match ($scope) {
                'pagos' => (
                    ($item['pagamentoStatus']
                        ?? $item['statusPagamento']
                        ?? '') === 'Pago'
                ),
                'ganhadores' => !empty($item['_ganhador']),
                default => true,
            };
        }
    )
);

$eventLabel = 'Todos os jogos';

if ($filters['idEventoPalpite'] > 0) {
    foreach ($events as $event) {
        if (
            (int)$event['idEventoPalpite']
            === $filters['idEventoPalpite']
        ) {
            $eventLabel = (string)$event['titulo'];
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

$scopeLabel = match ($scope) {
    'pagos' => 'Somente participantes pagos',
    'ganhadores' => 'Somente ganhadores',
    default => 'Todos os participantes',
};

ob_start();
?>

<?php if ($options['mostrarFiltros']): ?>
    <div class="filters">
        <strong>Filtros:</strong>
        <?= Support::e(
            PdfReportService::filterDescription([
                'Jogo: ' . $eventLabel,
                'Forma: ' . $methodLabel,
                'Período: ' . $period,
                'Participantes: ' . $scopeLabel,
            ])
        ) ?>
    </div>
<?php endif; ?>

<?php if ($options['secaoResumo']): ?>
    <table class="summary">
        <tr>
            <td>
                <span class="label">Palpites enviados</span>
                <span class="value">
                    <?= (int)($summary['totalPalpites'] ?? 0) ?>
                </span>
            </td>

            <td>
                <span class="label">Palpites pagos</span>
                <span class="value">
                    <?= (int)($summary['palpitesPagos'] ?? 0) ?>
                </span>
            </td>

            <td>
                <span class="label">Valor bruto recebido</span>
                <span class="value">
                    <?= Support::e(
                        PdfReportService::money(
                            $summary['bruto'] ?? 0
                        )
                    ) ?>
                </span>
            </td>

            <td>
                <span class="label">Valor líquido</span>
                <span class="value">
                    <?= Support::e(
                        PdfReportService::money(
                            $summary['liquido'] ?? 0
                        )
                    ) ?>
                </span>
            </td>

            <td>
                <span class="label">Ganhadores pagos</span>
                <span class="value">
                    <?= count($winnerItems) ?>
                </span>
            </td>
        </tr>
    </table>

    <?php if ((int)($summary['semLiquido'] ?? 0) > 0): ?>
        <div class="notice">
            Existem pagamentos antigos sem valor líquido/tarifa registrado.
            O valor bruto permanece correto.
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($options['secaoGanhadores'] && $winnerItems): ?>
    <div class="winner-box">
        <strong>Ganhadores do(s) jogo(s) finalizado(s)</strong>

        <?php foreach ($winnerItems as $item): ?>
            <p>
                🏆
                <strong><?= Support::e($item['nome']) ?></strong>
                -
                <?= Support::e($item['eventoTitulo']) ?>
                -
                Palpite:
                <strong><?= Support::e($item['palpite']) ?></strong>
                -
                <?= $item['valor'] !== null
                    ? Support::e(
                        PdfReportService::money(
                            $item['valor']
                        )
                    )
                    : '—' ?>
            </p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($options['secaoResumoPalpites']): ?>
    <h2>Resumo por palpite marcado</h2>

    <table class="report">
        <thead>
        <tr>
            <th>Jogo</th>
            <th>Palpite</th>
            <th>Participações</th>
            <th>Pagos</th>
            <th>Bruto</th>
            <th>Líquido</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($breakdown as $row): ?>
            <tr>
                <td><?= Support::e($row['eventoTitulo']) ?></td>
                <td>
                    <strong><?= Support::e($row['palpite']) ?></strong>
                </td>
                <td class="center"><?= (int)$row['participacoes'] ?></td>
                <td class="center"><?= (int)$row['pagos'] ?></td>
                <td class="right">
                    <?= Support::e(
                        PdfReportService::money($row['bruto'])
                    ) ?>
                </td>
                <td class="right">
                    <?= Support::e(
                        PdfReportService::money($row['liquido'])
                    ) ?>
                </td>
            </tr>
        <?php endforeach; ?>

        <?php if (!$breakdown): ?>
            <tr>
                <td colspan="6">Nenhum palpite encontrado.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php if ($options['secaoParticipantes']): ?>
    <?php if (
        $options['secaoResumo']
        || $options['secaoGanhadores']
        || $options['secaoResumoPalpites']
    ): ?>
        <div class="page-break"></div>
    <?php endif; ?>

    <h2>Participantes e pagamentos</h2>

    <p class="small">
        <?= Support::e($scopeLabel) ?>.
        Em jogos finalizados, os ganhadores aparecem primeiro.
    </p>

    <table class="report">
        <thead>
        <tr>
            <?php if ($fields['campoResultado']): ?>
                <th>Resultado</th>
            <?php endif; ?>

            <?php if ($fields['campoJogo']): ?>
                <th>Jogo</th>
            <?php endif; ?>

            <?php if ($fields['campoNome']): ?>
                <th>Nome</th>
            <?php endif; ?>

            <?php if ($fields['campoEmail']): ?>
                <th>E-mail</th>
            <?php endif; ?>

            <?php if ($fields['campoTelefone']): ?>
                <th>Telefone</th>
            <?php endif; ?>

            <?php if ($fields['campoCpf']): ?>
                <th>CPF</th>
            <?php endif; ?>

            <?php if ($fields['campoPalpite']): ?>
                <th>Palpite</th>
            <?php endif; ?>

            <?php if ($fields['campoPagamento']): ?>
                <th>Pagamento</th>
            <?php endif; ?>

            <?php if ($fields['campoForma']): ?>
                <th>Forma</th>
            <?php endif; ?>

            <?php if ($fields['campoValor']): ?>
                <th>Valor</th>
            <?php endif; ?>

            <?php if ($fields['campoLiquido']): ?>
                <th>Líquido</th>
            <?php endif; ?>

            <?php if ($fields['campoData']): ?>
                <th>Data</th>
            <?php endif; ?>

            <?php if ($fields['campoAsaas']): ?>
                <th>Asaas</th>
            <?php endif; ?>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($participantItems as $item): ?>
            <?php
            $rowClass = !empty($item['_ganhador'])
                ? 'winner'
                : (
                    !empty($item['_acertando'])
                        ? 'correct'
                        : ''
                );
            ?>

            <tr class="<?= $rowClass ?>">
                <?php if ($fields['campoResultado']): ?>
                    <td>
                        <?php if (!empty($item['_ganhador'])): ?>
                            <strong class="paid">🏆 Ganhador</strong>
                        <?php elseif (
                            ($item['status_jogo'] ?? '') === 'EmAndamento'
                            && !empty($item['_acertando'])
                        ): ?>
                            <span class="paid">Acertando</span>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                <?php endif; ?>

                <?php if ($fields['campoJogo']): ?>
                    <td>
                        <strong>
                            <?= Support::e($item['eventoTitulo']) ?>
                        </strong>

                        <?php if (
                            $item['placar_casa'] !== null
                            && $item['placar_visitante'] !== null
                        ): ?>
                            <br>
                            <span class="small">
                                Placar:
                                <?= (int)$item['placar_casa'] ?>
                                x
                                <?= (int)$item['placar_visitante'] ?>
                            </span>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>

                <?php if ($fields['campoNome']): ?>
                    <td>
                        <strong><?= Support::e($item['nome']) ?></strong>
                    </td>
                <?php endif; ?>

                <?php if ($fields['campoEmail']): ?>
                    <td><?= Support::e($item['email']) ?></td>
                <?php endif; ?>

                <?php if ($fields['campoTelefone']): ?>
                    <td><?= Support::e($item['telefone']) ?></td>
                <?php endif; ?>

                <?php if ($fields['campoCpf']): ?>
                    <td>
                        <?= Support::e(
                            PdfReportService::cpf($item['cpf'])
                        ) ?>
                    </td>
                <?php endif; ?>

                <?php if ($fields['campoPalpite']): ?>
                    <td>
                        <strong><?= Support::e($item['palpite']) ?></strong>
                    </td>
                <?php endif; ?>

                <?php if ($fields['campoPagamento']): ?>
                    <td>
                        <?= Support::e(
                            $item['pagamentoStatus']
                            ?? $item['statusPagamento']
                        ) ?>
                    </td>
                <?php endif; ?>

                <?php if ($fields['campoForma']): ?>
                    <td>
                        <?= !empty($item['formaPagamento'])
                            ? Support::e(
                                ReportRepository::paymentMethodLabel(
                                    (string)$item['formaPagamento']
                                )
                            )
                            : '—' ?>
                    </td>
                <?php endif; ?>

                <?php if ($fields['campoValor']): ?>
                    <td class="right">
                        <?= $item['valor'] !== null
                            ? Support::e(
                                PdfReportService::money($item['valor'])
                            )
                            : '—' ?>
                    </td>
                <?php endif; ?>

                <?php if ($fields['campoLiquido']): ?>
                    <td class="right">
                        <?= $item['valorLiquido'] !== null
                            ? Support::e(
                                PdfReportService::money(
                                    $item['valorLiquido']
                                )
                            )
                            : '—' ?>
                    </td>
                <?php endif; ?>

                <?php if ($fields['campoData']): ?>
                    <td>
                        <?= Support::e(
                            PdfReportService::dateTime(
                                $item['dataPagamento']
                                ?: $item['criadoEm']
                            )
                        ) ?>
                    </td>
                <?php endif; ?>

                <?php if ($fields['campoAsaas']): ?>
                    <td class="small">
                        <?= Support::e(
                            $item['asaasPaymentId'] ?? '—'
                        ) ?>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>

        <?php if (!$participantItems): ?>
            <tr>
                <td colspan="<?= max(1, count(array_filter($fields))) ?>">
                    Nenhum participante encontrado neste escopo.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
$body = (string)ob_get_clean();

PdfReportService::stream(
    'Relatório de Palpites',
    $body,
    'relatorio-palpites-' . date('Ymd-His') . '.pdf',
    'landscape'
);
