<?php

require_once dirname(__DIR__, 2) . '/bootstrap.php';

Auth::require();

$pageTitle = 'Exportar Relatório de Palpites';

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

$selectedEvent = null;

if ($filters['idEventoPalpite'] > 0) {
    foreach ($events as $event) {
        if (
            (int)$event['idEventoPalpite']
            === $filters['idEventoPalpite']
        ) {
            $selectedEvent = $event;
            break;
        }
    }
}

require dirname(__DIR__) . '/_header.php';
?>

<div class="actions">
    <a
        class="btn"
        href="<?= APP_URL ?>/admin/relatorios/palpites.php?<?= Support::e(
            http_build_query($filters)
        ) ?>"
    >
        Voltar ao relatório
    </a>
</div>

<div class="panel">
    <h2>Escolha o que deve aparecer no PDF</h2>

    <p class="report-help">
        Marque as seções e os dados que deseja incluir.
        Essas opções valem somente para esta exportação.
    </p>

    <?php if ($selectedEvent): ?>
        <div class="alert muted">
            <strong>Jogo selecionado:</strong>
            <?= Support::e($selectedEvent['titulo']) ?>
        </div>
    <?php endif; ?>

    <form
        method="get"
        action="<?= APP_URL ?>/admin/relatorios/palpites-pdf.php"
        class="pdf-options-form"
    >
        <input
            type="hidden"
            name="idEventoPalpite"
            value="<?= (int)$filters['idEventoPalpite'] ?>"
        >
        <input
            type="hidden"
            name="formaPagamento"
            value="<?= Support::e($filters['formaPagamento']) ?>"
        >
        <input
            type="hidden"
            name="dataInicio"
            value="<?= Support::e($filters['dataInicio']) ?>"
        >
        <input
            type="hidden"
            name="dataFim"
            value="<?= Support::e($filters['dataFim']) ?>"
        >

        <div class="pdf-option-section">
            <h3>Seções do relatório</h3>

            <div class="pdf-option-grid">
                <label class="pdf-option-card">
                    <input
                        type="checkbox"
                        name="secaoResumo"
                        value="1"
                        checked
                    >
                    <span>
                        <strong>Resumo geral</strong>
                        <small>
                            Total de palpites, pagos, valores e quantidade
                            de ganhadores.
                        </small>
                    </span>
                </label>

                <label class="pdf-option-card">
                    <input
                        type="checkbox"
                        name="secaoGanhadores"
                        value="1"
                        checked
                    >
                    <span>
                        <strong>Ganhadores</strong>
                        <small>
                            Lista dos ganhadores dos jogos finalizados.
                        </small>
                    </span>
                </label>

                <label class="pdf-option-card">
                    <input
                        type="checkbox"
                        name="secaoResumoPalpites"
                        value="1"
                        checked
                    >
                    <span>
                        <strong>Resumo por palpite</strong>
                        <small>
                            Quantidade de pessoas e valores por opção marcada.
                        </small>
                    </span>
                </label>

                <label class="pdf-option-card">
                    <input
                        type="checkbox"
                        name="secaoParticipantes"
                        value="1"
                        checked
                    >
                    <span>
                        <strong>Participantes</strong>
                        <small>
                            Relação detalhada das pessoas e seus palpites.
                        </small>
                    </span>
                </label>
            </div>
        </div>

        <div class="pdf-option-section">
            <h3>Quais participantes devem aparecer?</h3>

            <div class="pdf-radio-row">
                <label>
                    <input
                        type="radio"
                        name="escopoParticipantes"
                        value="todos"
                        checked
                    >
                    Todos
                </label>

                <label>
                    <input
                        type="radio"
                        name="escopoParticipantes"
                        value="pagos"
                    >
                    Somente pagos
                </label>

                <label>
                    <input
                        type="radio"
                        name="escopoParticipantes"
                        value="ganhadores"
                    >
                    Somente ganhadores
                </label>
            </div>
        </div>

        <div class="pdf-option-section">
            <div class="pdf-option-title-row">
                <h3>Dados da tabela de participantes</h3>

                <div class="actions">
                    <button
                        class="btn small"
                        type="button"
                        data-pdf-select-all
                    >
                        Marcar todos
                    </button>

                    <button
                        class="btn small"
                        type="button"
                        data-pdf-clear-all
                    >
                        Desmarcar todos
                    </button>
                </div>
            </div>

            <div class="pdf-field-grid" data-pdf-fields>
                <?php
                $fields = [
                    'campoResultado' => [
                        'Resultado',
                        'Ganhador / acertando',
                    ],
                    'campoJogo' => [
                        'Jogo',
                        'Título e placar',
                    ],
                    'campoNome' => [
                        'Nome',
                        'Nome do participante',
                    ],
                    'campoEmail' => [
                        'E-mail',
                        'E-mail informado',
                    ],
                    'campoTelefone' => [
                        'Telefone',
                        'Telefone informado',
                    ],
                    'campoCpf' => [
                        'CPF',
                        'CPF do participante',
                    ],
                    'campoPalpite' => [
                        'Palpite',
                        'Opção marcada',
                    ],
                    'campoPagamento' => [
                        'Status do pagamento',
                        'Pago / pendente',
                    ],
                    'campoForma' => [
                        'Forma de pagamento',
                        'PIX / Cartão',
                    ],
                    'campoValor' => [
                        'Valor pago',
                        'Valor bruto',
                    ],
                    'campoLiquido' => [
                        'Valor líquido',
                        'Valor líquido registrado',
                    ],
                    'campoData' => [
                        'Data',
                        'Data do pagamento/participação',
                    ],
                    'campoAsaas' => [
                        'Código Asaas',
                        'Identificador da cobrança',
                    ],
                ];
                ?>

                <?php foreach ($fields as $name => [$label, $help]): ?>
                    <label class="pdf-field-option">
                        <input
                            type="checkbox"
                            name="<?= Support::e($name) ?>"
                            value="1"
                            checked
                        >

                        <span>
                            <strong><?= Support::e($label) ?></strong>
                            <small><?= Support::e($help) ?></small>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="pdf-option-section">
            <h3>Outras opções</h3>

            <label class="pdf-option-card pdf-option-card-inline">
                <input
                    type="checkbox"
                    name="mostrarFiltros"
                    value="1"
                    checked
                >
                <span>
                    <strong>Mostrar filtros utilizados</strong>
                    <small>
                        Exibe jogo, período e forma de pagamento no topo.
                    </small>
                </span>
            </label>
        </div>

        <div class="actions pdf-generate-actions">
            <button
                class="btn primary"
                type="submit"
            >
                Gerar PDF
            </button>

            <a
                class="btn"
                href="<?= APP_URL ?>/admin/relatorios/palpites.php?<?= Support::e(
                    http_build_query($filters)
                ) ?>"
            >
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.querySelector('[data-pdf-fields]');
    if (!container) return;

    const boxes = () => Array.from(
        container.querySelectorAll('input[type="checkbox"]')
    );

    document.querySelector('[data-pdf-select-all]')
        ?.addEventListener('click', function () {
            boxes().forEach(box => box.checked = true);
        });

    document.querySelector('[data-pdf-clear-all]')
        ?.addEventListener('click', function () {
            boxes().forEach(box => box.checked = false);
        });
});
</script>

<?php require dirname(__DIR__) . '/_footer.php'; ?>
