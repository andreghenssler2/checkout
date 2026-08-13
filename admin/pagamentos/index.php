<?php

require_once dirname(__DIR__, 2) . '/bootstrap.php';

Auth::require();

$pageTitle = 'Pagamentos';

$filters = PagamentoAdminService::normalizeFilters(
    $_GET
);

$years = PagamentoAdminService::years();

if (
    $filters['ano'] !== ''
    && !in_array((int)$filters['ano'], $years, true)
) {
    array_unshift(
        $years,
        (int)$filters['ano']
    );
}

$items = PagamentoAdminService::items(
    $filters
);

$summary = PagamentoAdminService::summary(
    $filters
);

require dirname(__DIR__) . '/_header.php';
?>

<div class="panel payment-admin-filter">
    <div class="payment-admin-filter-head">
        <div>
            <h2>Filtrar pagamentos</h2>
            <p class="report-help">
                A data considera a data do pagamento confirmado e,
                enquanto estiver pendente, a data de criação da cobrança.
            </p>
        </div>

        <div class="payment-live-sync">
            <span class="live-refresh-badge">
                Sincronização automática
            </span>

            <small data-payment-sync-status>
                Preparando primeira sincronização...
            </small>
        </div>
    </div>

    <form
        method="get"
        class="payment-filter-grid"
    >
        <label>
            Ano
            <select name="ano">
                <option value="">Todos os anos</option>

                <?php foreach ($years as $year): ?>
                    <option
                        value="<?= (int)$year ?>"
                        <?= $filters['ano'] === (string)$year
                            ? 'selected'
                            : '' ?>
                    >
                        <?= (int)$year ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Data
            <input
                type="date"
                name="data"
                value="<?= Support::e($filters['data']) ?>"
            >
        </label>

        <label>
            Tipo
            <select name="tipo">
                <option value="">Todos os tipos</option>

                <option
                    value="Oferta"
                    <?= $filters['tipo'] === 'Oferta'
                        ? 'selected'
                        : '' ?>
                >
                    Oferta
                </option>

                <option
                    value="Palpite"
                    <?= $filters['tipo'] === 'Palpite'
                        ? 'selected'
                        : '' ?>
                >
                    Palpite
                </option>
            </select>
        </label>

        <div class="payment-filter-actions">
            <button
                class="btn primary"
                type="submit"
            >
                Filtrar
            </button>

            <a
                class="btn"
                href="<?= APP_URL ?>/admin/pagamentos/"
            >
                Limpar
            </a>

            <button
                class="btn"
                type="button"
                data-payment-sync-now
            >
                Sincronizar agora
            </button>
        </div>
    </form>
</div>

<div class="stats payment-admin-stats">
    <div class="stat">
        <small>Total</small>
        <strong data-payment-stat-total>
            <?= (int)($summary['total'] ?? 0) ?>
        </strong>
    </div>

    <div class="stat">
        <small>Pagos</small>
        <strong data-payment-stat-paid>
            <?= (int)($summary['pagos'] ?? 0) ?>
        </strong>
    </div>

    <div class="stat">
        <small>Pendentes</small>
        <strong data-payment-stat-pending>
            <?= (int)($summary['pendentes'] ?? 0) ?>
        </strong>
    </div>

    <div class="stat">
        <small>Vencidos</small>
        <strong data-payment-stat-overdue>
            <?= (int)($summary['vencidos'] ?? 0) ?>
        </strong>
    </div>

    <div class="stat">
        <small>Total recebido</small>
        <strong data-payment-stat-value>
            <?= Support::money(
                (float)($summary['valorPago'] ?? 0)
            ) ?>
        </strong>
    </div>
</div>

<div class="panel tablewrap">
    <table>
        <thead>
        <tr>
            <th>Data</th>
            <th>Tipo</th>
            <th>Campanha / jogo</th>
            <th>Pagador</th>
            <th>Palpite</th>
            <th>Forma</th>
            <th>Valor</th>
            <th>Status</th>
            <th>Provedor</th>
            <th>Erro / retorno</th>
            <th>Comprovante</th>
        </tr>
        </thead>

        <tbody data-payment-table-body>
            <?php require __DIR__ . '/_linhas.php'; ?>
        </tbody>
    </table>
</div>

<form
    hidden
    data-payment-sync-config
    data-endpoint="<?= APP_URL ?>/admin/pagamentos/sincronizar-ajax.php"
    data-year="<?= Support::e($filters['ano']) ?>"
    data-date="<?= Support::e($filters['data']) ?>"
    data-type="<?= Support::e($filters['tipo']) ?>"
>
    <input
        type="hidden"
        name="_csrf"
        value="<?= Support::csrf() ?>"
        data-payment-sync-csrf
    >
</form>

<script>
(function () {
    const config = document.querySelector(
        '[data-payment-sync-config]'
    );

    if (!config) return;

    const endpoint = config.dataset.endpoint;
    const csrf = config.querySelector(
        '[data-payment-sync-csrf]'
    ).value;

    const tbody = document.querySelector(
        '[data-payment-table-body]'
    );

    const status = document.querySelector(
        '[data-payment-sync-status]'
    );

    const button = document.querySelector(
        '[data-payment-sync-now]'
    );

    let syncing = false;
    let timer = null;

    function setStatus(message, error = false) {
        status.textContent = message;
        status.classList.toggle(
            'payment-sync-error',
            error
        );
    }

    function applySummary(summary) {
        document.querySelector(
            '[data-payment-stat-total]'
        ).textContent = summary.total;

        document.querySelector(
            '[data-payment-stat-paid]'
        ).textContent = summary.pagos;

        document.querySelector(
            '[data-payment-stat-pending]'
        ).textContent = summary.pendentes;

        document.querySelector(
            '[data-payment-stat-overdue]'
        ).textContent = summary.vencidos;

        document.querySelector(
            '[data-payment-stat-value]'
        ).textContent = summary.valorPago;
    }

    async function synchronize(manual = false) {
        if (syncing) return;

        syncing = true;

        if (button) {
            button.disabled = true;
        }

        setStatus(
            manual
                ? 'Sincronizando agora...'
                : 'Sincronizando pagamentos...'
        );

        const body = new URLSearchParams({
            _csrf: csrf,
            ano: config.dataset.year || '',
            data: config.dataset.date || '',
            tipo: config.dataset.type || ''
        });

        try {
            const response = await fetch(
                endpoint,
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type':
                            'application/x-www-form-urlencoded;charset=UTF-8'
                    },
                    body: body.toString()
                }
            );

            const data = await response.json();

            if (!data.ok) {
                throw new Error(
                    data.message
                    || 'Não foi possível sincronizar.'
                );
            }

            tbody.innerHTML = data.html;
            applySummary(data.summary);

            const sync = data.sync || {};
            const changed = Number(
                sync.alterados || 0
            );
            const consulted = Number(
                sync.consultados || 0
            );
            const errors = Array.isArray(sync.erros)
                ? sync.erros.length
                : 0;

            let message =
                'Última sincronização: '
                + data.atualizadoEm
                + ' · '
                + consulted
                + ' cobrança(s) consultada(s)';

            if (changed > 0) {
                message += ' · '
                    + changed
                    + ' status atualizado(s)';
            }

            if (errors > 0) {
                message += ' · '
                    + errors
                    + ' falha(s)';
            }

            setStatus(
                message,
                errors > 0
            );
        } catch (error) {
            setStatus(
                error.message
                || 'Erro na sincronização automática.',
                true
            );
        } finally {
            syncing = false;

            if (button) {
                button.disabled = false;
            }
        }
    }

    if (button) {
        button.addEventListener(
            'click',
            function () {
                synchronize(true);
            }
        );
    }

    /*
     * Primeira atualização quase imediata e depois a cada 10 segundos.
     * O endpoint sincroniza somente um lote pequeno por ciclo para
     * evitar excesso de consultas ao Asaas.
     */
    timer = window.setTimeout(
        function firstSync() {
            synchronize(false);

            timer = window.setInterval(
                function () {
                    if (!document.hidden) {
                        synchronize(false);
                    }
                },
                10000
            );
        },
        1200
    );
})();
</script>

<?php require dirname(__DIR__) . '/_footer.php'; ?>
