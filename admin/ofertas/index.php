<?php

require_once dirname(__DIR__, 2) . '/bootstrap.php';

Auth::require();

$pageTitle = 'Planos de oferta';

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

$years = OfertaRepository::years();

if (
    $selectedYear !== null
    && !in_array($selectedYear, $years, true)
) {
    array_unshift($years, $selectedYear);
}

$items = OfertaRepository::all(
    $selectedYear
);

require dirname(__DIR__) . '/_header.php';
?>

<div class="admin-list-toolbar">
    <div class="actions">
        <a
            class="btn primary"
            href="<?= APP_URL ?>/admin/ofertas/form.php"
        >
            + Nova oferta
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
                href="<?= APP_URL ?>/admin/ofertas/"
            >
                Limpar filtro
            </a>
        <?php endif; ?>
    </form>
</div>

<?php if ($selectedYear !== null): ?>
    <div class="admin-filter-result">
        Exibindo Ofertas de
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
            <th>Título</th>
            <th>Categoria</th>
            <th>Período</th>
            <th>PIX</th>
            <th>Cartão</th>
            <th>Status</th>
            <th>Link curto</th>
            <th></th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($items as $o): ?>
            <tr>
                <td>
                    <strong>
                        <?= Support::e($o['titulo']) ?>
                    </strong>
                    <br>
                    <small>
                        /oferta/<?= Support::e($o['slug']) ?>
                    </small>
                </td>

                <td>
                    <span class="badge muted">
                        <?= Support::e(
                            OfertaRepository::categoryLabel(
                                $o['categoria'] ?? 'Local'
                            )
                        ) ?>
                    </span>
                </td>

                <td>
                    <?= Support::e($o['data_inicio'] ?: '—') ?>
                    <br>
                    <?= Support::e($o['data_fim'] ?: '—') ?>
                </td>

                <td>
                    <?= $o['pix_ativo'] ? 'Sim' : 'Não' ?>
                </td>

                <td>
                    <?= $o['cartao_ativo'] ? 'Sim' : 'Não' ?>
                </td>

                <td>
                    <span
                        class="badge <?= $o['ativo']
                            ? 'paid'
                            : 'muted' ?>"
                    >
                        <?= $o['ativo'] ? 'Ativa' : 'Inativa' ?>
                    </span>
                </td>

                <td>
                    <?php
                    $shortUrl = ShortUrlService::urlFor(
                        ShortUrlService::TYPE_OFFER,
                        (int)$o['idOferta']
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
                    <a
                        class="btn small"
                        href="<?= APP_URL ?>/admin/ofertas/form.php?id=<?= (int)$o['idOferta'] ?>"
                    >
                        Editar
                    </a>

                    <a
                        class="btn small"
                        target="_blank"
                        href="<?= APP_URL ?>/oferta/<?= Support::e($o['slug']) ?>"
                    >
                        Abrir
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>

        <?php if (!$items): ?>
            <tr>
                <td colspan="8">
                    Nenhuma Oferta encontrada
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
