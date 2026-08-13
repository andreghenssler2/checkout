<?php

require_once dirname(__DIR__, 2) . '/bootstrap.php';

Auth::require();

$pageTitle = 'Palpites';

$year = trim(
    (string)($_GET['ano'] ?? '')
);

$selectedYear = preg_match('/^\d{4}$/', $year)
    ? (int)$year
    : null;

if (
    $selectedYear !== null
    && ($selectedYear < 2000 || $selectedYear > 2100)
) {
    $selectedYear = null;
}

$years = PalpiteRepository::years();

if (
    $selectedYear !== null
    && !in_array($selectedYear, $years, true)
) {
    array_unshift($years, $selectedYear);
}

$items = PalpiteRepository::all(
    $selectedYear
);

require dirname(__DIR__) . '/_header.php';
?>

<div class="admin-list-toolbar">
    <div class="actions">
        <a
            class="btn primary"
            href="<?= APP_URL ?>/admin/palpites/form.php"
        >
            Novo formulário de palpite
        </a>
    </div>

    <form
        method="get"
        class="admin-year-filter"
    >
        <label>
            Ano

            <select
                name="ano"
                onchange="this.form.submit()"
            >
                <option value="">
                    Todos os anos
                </option>

                <?php foreach ($years as $filterYear): ?>
                    <option
                        value="<?= (int)$filterYear ?>"
                        <?= $selectedYear === (int)$filterYear
                            ? 'selected'
                            : '' ?>
                    >
                        <?= (int)$filterYear ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <?php if ($selectedYear !== null): ?>
            <a
                class="btn"
                href="<?= APP_URL ?>/admin/palpites/"
            >
                Limpar filtro
            </a>
        <?php endif; ?>
    </form>
</div>

<?php if ($selectedYear !== null): ?>
    <div class="admin-filter-result">
        Exibindo Palpites de
        <strong><?= $selectedYear ?></strong>.
        <span>
            <?= count($items) ?>
            registro(s) encontrado(s).
        </span>
    </div>
<?php endif; ?>

<div class="panel tablewrap">
    <table>
        <thead>
        <tr>
            <th>Formulário / Jogo</th>
            <th>Data</th>
            <th>Participações</th>
            <th>Jogo</th>
            <th>Status formulário</th>
            <th>Link</th>
            <th></th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td>
                    <strong>
                        <?= Support::e($item['titulo']) ?>
                    </strong>
                    <br>
                    <small>
                        <?= Support::e($item['equipe_casa']) ?>
                        x
                        <?= Support::e($item['equipe_visitante']) ?>
                    </small>
                </td>

                <td>
                    <?= !empty($item['data_jogo'])
                        ? Support::e(
                            date(
                                'd/m/Y H:i',
                                strtotime($item['data_jogo'])
                            )
                        )
                        : '—' ?>
                </td>

                <td>
                    <?= (int)$item['totalPalpites'] ?> enviados
                    <br>
                    <small>
                        <?= (int)$item['totalPagos'] ?> pagos
                    </small>
                </td>

                <td>
                    <span
                        class="badge <?= ($item['status_jogo'] ?? '') === 'Finalizado'
                            ? 'paid'
                            : 'muted' ?>"
                    >
                        <?= Support::e(
                            PalpiteRepository::gameStatusLabel(
                                (string)(
                                    $item['status_jogo']
                                    ?? 'Agendado'
                                )
                            )
                        ) ?>
                    </span>

                    <?php if (
                        $item['placar_casa'] !== null
                        && $item['placar_visitante'] !== null
                    ): ?>
                        <br>
                        <strong>
                            <?= (int)$item['placar_casa'] ?>
                            x
                            <?= (int)$item['placar_visitante'] ?>
                        </strong>
                    <?php endif; ?>
                </td>

                <td>
                    <span
                        class="badge <?= !empty($item['ativo'])
                            ? 'paid'
                            : 'muted' ?>"
                    >
                        <?= !empty($item['ativo'])
                            ? 'Ativo'
                            : 'Inativo' ?>
                    </span>
                </td>

                <td>
                    <?php
                    $shortUrl = ShortUrlService::urlFor(
                        ShortUrlService::TYPE_PREDICTION,
                        (int)$item['idEventoPalpite']
                    );
                    ?>

                    <a
                        href="<?= Support::e($shortUrl) ?>"
                        target="_blank"
                    >
                        <?= Support::e(
                            str_replace(
                                APP_URL . '/',
                                '',
                                $shortUrl
                            )
                        ) ?>
                    </a>

                    <br>

                    <button
                        class="btn small"
                        type="button"
                        data-copy-url="<?= Support::e($shortUrl) ?>"
                    >
                        Copiar
                    </button>
                </td>

                <td>
                    <div class="actions compact">
                        <a
                            class="btn small primary"
                            href="<?= APP_URL ?>/admin/palpites/acompanhamento.php?id=<?= (int)$item['idEventoPalpite'] ?>"
                        >
                            Acompanhar
                        </a>

                        <a
                            class="btn small"
                            href="<?= APP_URL ?>/admin/palpites/participacoes.php?id=<?= (int)$item['idEventoPalpite'] ?>"
                        >
                            Participações
                        </a>

                        <a
                            class="btn small"
                            href="<?= APP_URL ?>/admin/relatorios/palpites.php?idEventoPalpite=<?= (int)$item['idEventoPalpite'] ?>"
                        >
                            Relatório
                        </a>

                        <a
                            class="btn small"
                            href="<?= APP_URL ?>/admin/palpites/form.php?id=<?= (int)$item['idEventoPalpite'] ?>"
                        >
                            Editar
                        </a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>

        <?php if (!$items): ?>
            <tr>
                <td colspan="7">
                    Nenhum formulário de palpite encontrado
                    <?= $selectedYear !== null
                        ? 'para o ano selecionado.'
                        : '.' ?>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require dirname(__DIR__) . '/_footer.php'; ?>
