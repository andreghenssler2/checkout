<?php

require_once dirname(__DIR__, 2) . '/bootstrap.php';

Auth::require();

$id = (int)($_GET['id'] ?? $_POST['idEventoPalpite'] ?? 0);
$event = PalpiteRepository::find($id);

if (!$event) {
    http_response_code(404);
    die('Formulário de palpite não encontrado.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!Support::checkCsrf($_POST['_csrf'] ?? null)) {
            throw new RuntimeException('Sessão expirada.');
        }

        if (($_POST['acao'] ?? '') !== 'finalizar') {
            throw new RuntimeException('Ação inválida.');
        }

        PalpiteRepository::updateGame(
            $id,
            'Finalizado',
            $_POST['placar_casa'] ?? '',
            $_POST['placar_visitante'] ?? ''
        );

        $winnerEmails = NotificationService::predictionWinners(
            $id
        );

        Support::flash(
            'success',
            'Jogo finalizado. Os ganhadores aparecem primeiro. '
            . $winnerEmails
            . ' notificação(ões) de ganhador foram preparada(s) por e-mail.'
        );

        Support::redirect(
            '/admin/palpites/acompanhamento.php?id=' . $id
        );
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }

    $event = PalpiteRepository::find($id) ?: $event;
}

$filter = PalpiteLiveService::normalizeFilter(
    trim(
        (string)($_GET['filtro'] ?? 'todos')
    )
);

$snapshot = PalpiteLiveService::snapshot(
    $id,
    $filter
);

$allItems = PalpiteRepository::annotateEntries(
    PalpiteRepository::entries($id),
    $event
);

$items = PalpiteRepository::filterAnnotatedEntries(
    $allItems,
    $filter
);

$isFinal = ($event['status_jogo'] ?? '') === 'Finalizado';

$pageTitle = 'Acompanhar jogo - ' . $event['titulo'];

require dirname(__DIR__) . '/_header.php';
?>

<?php if ($error): ?>
    <div class="alert error"><?= Support::e($error) ?></div>
<?php endif; ?>

<div class="actions">
    <a class="btn" href="<?= APP_URL ?>/admin/palpites/">
        Voltar
    </a>

    <a
        class="btn"
        href="<?= APP_URL ?>/admin/palpites/participacoes.php?id=<?= $id ?>"
    >
        Participações
    </a>

    <a
        class="btn"
        href="<?= APP_URL ?>/admin/relatorios/palpites.php?idEventoPalpite=<?= $id ?>"
    >
        Relatório
    </a>
</div>

<div class="game-live-head panel">
    <div>
        <span class="content-type-badge">Acompanhamento do jogo</span>
        <h1><?= Support::e($event['titulo']) ?></h1>

        <p>
            <?= Support::e($event['equipe_casa']) ?>
            x
            <?= Support::e($event['equipe_visitante']) ?>
        </p>
    </div>

    <div class="game-live-score">
        <strong data-live-score-display>
            <?php if (
                $event['placar_casa'] !== null
                && $event['placar_visitante'] !== null
            ): ?>
                <?= (int)$event['placar_casa'] ?>
                <span>x</span>
                <?= (int)$event['placar_visitante'] ?>
            <?php else: ?>
                — <span>x</span> —
            <?php endif; ?>
        </strong>

        <span
            class="badge <?= $isFinal ? 'paid' : 'muted' ?>"
            data-live-status-badge
        >
            <?= Support::e(
                PalpiteRepository::gameStatusLabel(
                    (string)($event['status_jogo'] ?? 'Agendado')
                )
            ) ?>
        </span>
    </div>
</div>

<?php if ($isFinal): ?>
    <div class="panel game-finished-lock">
        <div>
            <h2>Jogo finalizado</h2>

            <p>
                O placar final foi confirmado e este jogo está
                <strong>bloqueado para edição</strong>.
            </p>
        </div>

        <div class="final-score-lock">
            <span><?= Support::e($event['equipe_casa']) ?></span>
            <strong><?= (int)$event['placar_casa'] ?></strong>
            <b>x</b>
            <strong><?= (int)$event['placar_visitante'] ?></strong>
            <span><?= Support::e($event['equipe_visitante']) ?></span>
        </div>
    </div>
<?php else: ?>
    <form
        method="post"
        class="panel game-score-form"
        data-live-score-form
        data-live-endpoint="<?= APP_URL ?>/admin/palpites/acompanhamento-ajax.php"
        data-event-id="<?= $id ?>"
        data-current-filter="<?= Support::e($filter) ?>"
    >
        <input
            type="hidden"
            name="_csrf"
            value="<?= Support::csrf() ?>"
            data-live-csrf
        >

        <input
            type="hidden"
            name="idEventoPalpite"
            value="<?= $id ?>"
        >

        <div class="grid2">
            <label>
                Situação do jogo

                <select
                    name="status_jogo"
                    data-live-status
                >
                    <option
                        value="Agendado"
                        <?= ($event['status_jogo'] ?? 'Agendado') === 'Agendado'
                            ? 'selected'
                            : '' ?>
                    >
                        Agendado
                    </option>

                    <option
                        value="EmAndamento"
                        <?= ($event['status_jogo'] ?? '') === 'EmAndamento'
                            ? 'selected'
                            : '' ?>
                    >
                        Em andamento
                    </option>
                </select>
            </label>

            <div>
                <span
                    class="live-save-indicator"
                    data-live-save-indicator
                >
                    Alterações do placar são salvas automaticamente.
                </span>
            </div>

            <label>
                <?= Support::e($event['equipe_casa']) ?>

                <input
                    type="number"
                    min="0"
                    max="99"
                    name="placar_casa"
                    data-live-home
                    value="<?= $event['placar_casa'] !== null
                        ? (int)$event['placar_casa']
                        : 0 ?>"
                >
            </label>

            <label>
                <?= Support::e($event['equipe_visitante']) ?>

                <input
                    type="number"
                    min="0"
                    max="99"
                    name="placar_visitante"
                    data-live-away
                    value="<?= $event['placar_visitante'] !== null
                        ? (int)$event['placar_visitante']
                        : 0 ?>"
                >
            </label>
        </div>

        <p class="report-help">
            Ao alterar o placar, a classificação é recalculada
            automaticamente. Não é necessário clicar em Atualizar.
        </p>

        <div class="actions game-score-actions">
            <button
                class="btn"
                type="submit"
                name="acao"
                value="finalizar"
                onclick="return confirm('Finalizar o jogo com este placar? Depois disso o placar não poderá mais ser alterado e os ganhadores serão notificados por e-mail.');"
            >
                Finalizar jogo com este placar
            </button>
        </div>
    </form>
<?php endif; ?>

<div class="stats game-stats">
    <div class="stat">
        <small>Palpites</small>
        <strong data-stat-total><?= (int)$snapshot['stats']['total'] ?></strong>
    </div>

    <div class="stat">
        <small>Pagos</small>
        <strong data-stat-paid><?= (int)$snapshot['stats']['paid'] ?></strong>
    </div>

    <div class="stat">
        <small data-stat-correct-label>
            <?= $isFinal ? 'Placar correto' : 'Acertando agora' ?>
        </small>
        <strong data-stat-correct><?= (int)$snapshot['stats']['correct'] ?></strong>
    </div>

    <div class="stat">
        <small>Ganhadores pagos</small>
        <strong data-stat-winners><?= (int)$snapshot['stats']['winners'] ?></strong>
    </div>

    <div class="stat">
        <small>Total recebido</small>
        <strong data-stat-received><?= Support::e($snapshot['stats']['received']) ?></strong>
    </div>
</div>

<div class="panel">
    <div class="report-toolbar">
        <div>
            <h2 data-live-table-title>
                <?= $isFinal
                    ? 'Resultado dos palpites'
                    : 'Palpites durante o jogo' ?>
            </h2>

            <p class="report-help" data-live-table-help>
                <?= $isFinal
                    ? 'Os ganhadores pagos aparecem sempre no início da lista.'
                    : 'Quem está acertando o placar atual aparece primeiro.' ?>
            </p>
        </div>

        <?php if (!$isFinal): ?>
            <span class="live-refresh-badge">
                Atualização automática
            </span>
        <?php endif; ?>
    </div>

    <div class="report-filter-links">
        <?php
        $filters = [
            'todos' => 'Todos',
            'acertando' => 'Acertando',
            'errando' => 'Não acertando',
            'pagos' => 'Pagos',
            'pendentes' => 'Não pagos',
        ];

        if ($isFinal) {
            $filters['ganhadores'] = 'Ganhadores';
        }
        ?>

        <?php foreach ($filters as $key => $label): ?>
            <a
                class="report-filter-chip <?= $filter === $key ? 'active' : '' ?>"
                href="<?= APP_URL ?>/admin/palpites/acompanhamento.php?id=<?= $id ?>&filtro=<?= Support::e($key) ?>"
                data-live-filter="<?= Support::e($key) ?>"
            >
                <?= Support::e($label) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="tablewrap">
        <table>
            <thead>
            <tr>
                <th>Posição</th>
                <th>Participante</th>
                <th>Palpite</th>
                <th>Situação</th>
                <th>Pagamento</th>
                <th>Valor</th>
            </tr>
            </thead>

            <tbody data-live-table-body>
            <?php foreach ($items as $index => $item): ?>
                <tr class="<?=
                    !empty($item['_ganhador'])
                        ? 'winner-row'
                        : (!empty($item['_acertando'])
                            ? 'correct-row'
                            : '')
                ?>">
                    <td>
                        <?= !empty($item['_ganhador'])
                            ? '🏆'
                            : $index + 1 ?>
                    </td>

                    <td>
                        <strong><?= Support::e($item['nome']) ?></strong><br>
                        <small><?= Support::e($item['telefone']) ?></small><br>
                        <small><?= Support::e($item['email']) ?></small>
                    </td>

                    <td>
                        <strong><?= Support::e($item['palpite']) ?></strong>
                    </td>

                    <td>
                        <?php if (!empty($item['_ganhador'])): ?>
                            <span class="badge paid">Ganhador</span>
                        <?php elseif (!empty($item['_acertando'])): ?>
                            <span class="badge paid">Acertando</span>
                        <?php else: ?>
                            <span class="badge muted">Fora do placar atual</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <span class="badge <?= !empty($item['_pago'])
                            ? 'paid'
                            : 'muted' ?>">
                            <?= Support::e(
                                $item['pagamentoStatus']
                                ?? $item['statusPagamento']
                            ) ?>
                        </span>
                    </td>

                    <td>
                        <?= isset($item['valor'])
                            ? Support::money((float)$item['valor'])
                            : '—' ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$items): ?>
                <tr>
                    <td colspan="6">
                        Nenhum palpite encontrado neste filtro.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (!$isFinal): ?>
<script>
(function () {
    const form = document.querySelector('[data-live-score-form]');
    if (!form) return;

    const endpoint = form.dataset.liveEndpoint;
    const eventId = form.dataset.eventId;
    const csrf = form.querySelector('[data-live-csrf]').value;
    const statusInput = form.querySelector('[data-live-status]');
    const homeInput = form.querySelector('[data-live-home]');
    const awayInput = form.querySelector('[data-live-away]');
    const indicator = form.querySelector('[data-live-save-indicator]');
    const tbody = document.querySelector('[data-live-table-body]');

    let filter = form.dataset.currentFilter || 'todos';
    let timer = null;
    let saving = false;

    const escapeHtml = (value) => {
        const div = document.createElement('div');
        div.textContent = String(value ?? '');
        return div.innerHTML;
    };

    const badge = (text, paid) =>
        '<span class="badge ' + (paid ? 'paid' : 'muted') + '">'
        + escapeHtml(text)
        + '</span>';

    function renderRows(items) {
        if (!items.length) {
            tbody.innerHTML = '<tr><td colspan="6">'
                + 'Nenhum palpite encontrado neste filtro.'
                + '</td></tr>';
            return;
        }

        tbody.innerHTML = items.map(item => {
            const rowClass = item.winner
                ? 'winner-row'
                : (item.correct ? 'correct-row' : '');

            const situation = item.winner
                ? badge('Ganhador', true)
                : (
                    item.correct
                        ? badge('Acertando', true)
                        : badge('Fora do placar atual', false)
                );

            const position = item.winner
                ? '🏆'
                : escapeHtml(item.position);

            return '<tr class="' + rowClass + '">'
                + '<td>' + position + '</td>'
                + '<td><strong>' + escapeHtml(item.name) + '</strong><br>'
                + '<small>' + escapeHtml(item.phone) + '</small><br>'
                + '<small>' + escapeHtml(item.email) + '</small></td>'
                + '<td><strong>' + escapeHtml(item.prediction) + '</strong></td>'
                + '<td>' + situation + '</td>'
                + '<td>' + badge(item.paymentStatus, item.paid) + '</td>'
                + '<td>' + escapeHtml(item.value) + '</td>'
                + '</tr>';
        }).join('');
    }

    function applySnapshot(snapshot) {
        const event = snapshot.event;
        const stats = snapshot.stats;

        const scoreDisplay = document.querySelector(
            '[data-live-score-display]'
        );

        if (
            event.placarCasa !== null
            && event.placarVisitante !== null
        ) {
            scoreDisplay.innerHTML =
                escapeHtml(event.placarCasa)
                + ' <span>x</span> '
                + escapeHtml(event.placarVisitante);
        }

        document.querySelector('[data-live-status-badge]').textContent =
            event.statusLabel;

        document.querySelector('[data-stat-total]').textContent =
            stats.total;

        document.querySelector('[data-stat-paid]').textContent =
            stats.paid;

        document.querySelector('[data-stat-correct]').textContent =
            stats.correct;

        document.querySelector('[data-stat-winners]').textContent =
            stats.winners;

        document.querySelector('[data-stat-received]').textContent =
            stats.received;

        renderRows(snapshot.items);

        if (event.finalizado) {
            window.location.reload();
        }
    }

    async function requestSnapshot() {
        const url = endpoint
            + '?id=' + encodeURIComponent(eventId)
            + '&filtro=' + encodeURIComponent(filter);

        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (data.ok) {
            applySnapshot(data.snapshot);
        }
    }

    async function saveScore() {
        if (saving) return;

        saving = true;
        indicator.textContent = 'Salvando placar...';

        const body = new URLSearchParams({
            _csrf: csrf,
            idEventoPalpite: eventId,
            filtro: filter,
            status_jogo: statusInput.value,
            placar_casa: homeInput.value,
            placar_visitante: awayInput.value
        });

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type':
                        'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: body.toString()
            });

            const data = await response.json();

            if (!data.ok) {
                if (data.finalizado) {
                    window.location.reload();
                    return;
                }

                throw new Error(
                    data.message || 'Não foi possível salvar o placar.'
                );
            }

            applySnapshot(data.snapshot);
            indicator.textContent = 'Placar salvo automaticamente.';
        } catch (error) {
            indicator.textContent =
                error.message || 'Erro ao salvar automaticamente.';
        } finally {
            saving = false;
        }
    }

    function scheduleSave() {
        if (statusInput.value === 'Agendado') {
            statusInput.value = 'EmAndamento';
        }

        clearTimeout(timer);
        timer = setTimeout(saveScore, 550);
    }

    homeInput.addEventListener('input', scheduleSave);
    awayInput.addEventListener('input', scheduleSave);

    statusInput.addEventListener('change', function () {
        clearTimeout(timer);
        timer = setTimeout(saveScore, 150);
    });

    document.querySelectorAll('[data-live-filter]')
        .forEach(link => {
            link.addEventListener('click', async function (event) {
                event.preventDefault();

                filter = this.dataset.liveFilter || 'todos';
                form.dataset.currentFilter = filter;

                document.querySelectorAll('[data-live-filter]')
                    .forEach(item => item.classList.remove('active'));

                this.classList.add('active');

                try {
                    await requestSnapshot();

                    const url = new URL(window.location.href);
                    url.searchParams.set('filtro', filter);

                    history.replaceState(
                        null,
                        '',
                        url.toString()
                    );
                } catch (_) {
                    window.location.href = this.href;
                }
            });
        });

    setInterval(() => {
        if (!saving) {
            requestSnapshot().catch(() => {});
        }
    }, 5000);
})();
</script>
<?php endif; ?>

<?php require dirname(__DIR__) . '/_footer.php'; ?>
