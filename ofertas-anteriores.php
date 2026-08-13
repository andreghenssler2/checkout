<?php

require_once __DIR__ . '/bootstrap.php';

$now = new DateTimeImmutable('now');
$currentYear = (int)$now->format('Y');
$currentMonth = (int)$now->format('m');

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

$category = trim(
    (string)($_GET['categoria'] ?? '')
);

$selectedCategory = in_array(
    $category,
    OfertaRepository::categories(),
    true
)
    ? $category
    : null;

$page = max(
    1,
    (int)($_GET['pagina'] ?? 1)
);

$years = OfertaRepository::previousYears();

$result = OfertaRepository::previousFiltered(
    $currentYear,
    $currentMonth,
    $selectedYear,
    $selectedCategory,
    $page,
    12
);

$items = $result['items'];
$total = (int)$result['total'];
$currentPage = (int)$result['page'];
$totalPages = (int)$result['totalPages'];

function previousOfferPeriodLabel(array $offer): string
{
    $start = trim(
        (string)($offer['data_inicio'] ?? '')
    );

    $end = trim(
        (string)($offer['data_fim'] ?? '')
    );

    if ($start !== '' && $end !== '') {
        return date('d/m/Y', strtotime($start))
            . ' até '
            . date('d/m/Y', strtotime($end));
    }

    if ($start !== '') {
        return 'Iniciada em '
            . date('d/m/Y', strtotime($start));
    }

    return 'Período não informado';
}

function previousPageUrl(
    int $page,
    ?int $year,
    ?string $category
): string {
    $query = [
        'pagina' => $page,
    ];

    if ($year !== null) {
        $query['ano'] = $year;
    }

    if ($category !== null) {
        $query['categoria'] = $category;
    }

    return APP_URL
        . '/ofertas-anteriores?'
        . http_build_query($query);
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width,initial-scale=1"
    >

    <title><?= Support::e(
        SiteSettings::pageTitle(
            'Ofertas anteriores'
        )
    ) ?></title>

    <meta
        name="description"
        content="<?= Support::e(
            SiteSettings::description()
        ) ?>"
    >

    <?php SiteSettings::renderFavicon(); ?>

    <link
        rel="stylesheet"
        href="<?= APP_URL ?>/assets/css/app.css?v=1.8.9"
    >

    <?= AnalyticsService::renderHead() ?>
</head>

<body class="bg">
<header class="public-head">
    <a
        href="<?= APP_URL ?>/"
        class="public-brand"
    >
        <strong>
            <?= Support::e(
                SiteSettings::title()
            ) ?>
        </strong>
    </a>
</header>

<main class="public-container previous-offers-page">
    <div class="previous-page-heading">
        <div>
            <span class="content-type-badge">
                Histórico
            </span>

            <h1>Ofertas anteriores</h1>

            <p>
                Consulte campanhas de períodos anteriores e filtre
                por ano ou categoria.
            </p>
        </div>

        <a
            class="btn"
            href="<?= APP_URL ?>/"
        >
            Voltar ao início
        </a>
    </div>

    <form
        method="get"
        class="previous-public-filters"
    >
        <label>
            Ano

            <select name="ano">
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

        <label>
            Categoria

            <select name="categoria">
                <option value="">
                    Todas as categorias
                </option>

                <?php foreach (
                    OfertaRepository::categories()
                    as $filterCategory
                ): ?>
                    <option
                        value="<?= Support::e($filterCategory) ?>"
                        <?= $selectedCategory === $filterCategory
                            ? 'selected'
                            : '' ?>
                    >
                        <?= Support::e($filterCategory) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <div class="previous-filter-actions">
            <button
                class="btn primary"
                type="submit"
            >
                Filtrar
            </button>

            <a
                class="btn"
                href="<?= APP_URL ?>/ofertas-anteriores"
            >
                Limpar
            </a>
        </div>
    </form>

    <div class="previous-results-meta">
        <strong><?= $total ?></strong>
        oferta(s) encontrada(s).

        <?php if ($selectedYear !== null): ?>
            <span>
                Ano:
                <strong><?= $selectedYear ?></strong>
            </span>
        <?php endif; ?>

        <?php if ($selectedCategory !== null): ?>
            <span>
                Categoria:
                <strong>
                    <?= Support::e($selectedCategory) ?>
                </strong>
            </span>
        <?php endif; ?>
    </div>

    <?php if ($items): ?>
        <div class="previous-public-grid">
            <?php foreach ($items as $offer): ?>
                <article class="previous-public-card">
                    <a
                        class="previous-public-image"
                        href="<?= APP_URL ?>/oferta/<?= Support::e(
                            $offer['slug']
                        ) ?>"
                    >
                        <?php if (!empty($offer['imagem'])): ?>
                            <img
                                src="<?= APP_URL ?>/<?= Support::e(
                                    $offer['imagem']
                                ) ?>"
                                alt="<?= Support::e(
                                    $offer['titulo']
                                ) ?>"
                            >
                        <?php else: ?>
                            <div class="home-offer-placeholder">
                                <span>
                                    <?= Support::e(
                                        OfertaRepository::categoryLabel(
                                            $offer['categoria']
                                            ?? 'Local'
                                        )
                                    ) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </a>

                    <div class="previous-public-body">
                        <span class="content-type-badge">
                            Oferta
                            <?= Support::e(
                                OfertaRepository::categoryLabel(
                                    $offer['categoria']
                                    ?? 'Local'
                                )
                            ) ?>
                        </span>

                        <h2>
                            <a
                                href="<?= APP_URL ?>/oferta/<?= Support::e(
                                    $offer['slug']
                                ) ?>"
                            >
                                <?= Support::e($offer['titulo']) ?>
                            </a>
                        </h2>

                        <p class="home-offer-period">
                            <?= Support::e(
                                previousOfferPeriodLabel($offer)
                            ) ?>
                        </p>

                        <p class="home-offer-description">
                            <?= Support::e(
                                mb_strimwidth(
                                    strip_tags(
                                        (string)$offer['descricao']
                                    ),
                                    0,
                                    145,
                                    '…'
                                )
                            ) ?>
                        </p>

                        <a
                            class="btn primary"
                            href="<?= APP_URL ?>/oferta/<?= Support::e(
                                $offer['slug']
                            ) ?>"
                        >
                            Ver oferta
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav
                class="public-pagination"
                aria-label="Paginação das ofertas anteriores"
            >
                <?php if ($currentPage > 1): ?>
                    <a
                        class="btn"
                        href="<?= Support::e(
                            previousPageUrl(
                                $currentPage - 1,
                                $selectedYear,
                                $selectedCategory
                            )
                        ) ?>"
                    >
                        ← Anterior
                    </a>
                <?php endif; ?>

                <div class="public-page-numbers">
                    <?php
                    $startPage = max(
                        1,
                        $currentPage - 2
                    );

                    $endPage = min(
                        $totalPages,
                        $currentPage + 2
                    );
                    ?>

                    <?php for (
                        $pageNumber = $startPage;
                        $pageNumber <= $endPage;
                        $pageNumber++
                    ): ?>
                        <a
                            class="public-page-number <?= $pageNumber === $currentPage
                                ? 'active'
                                : '' ?>"
                            href="<?= Support::e(
                                previousPageUrl(
                                    $pageNumber,
                                    $selectedYear,
                                    $selectedCategory
                                )
                            ) ?>"
                        >
                            <?= $pageNumber ?>
                        </a>
                    <?php endfor; ?>
                </div>

                <?php if ($currentPage < $totalPages): ?>
                    <a
                        class="btn"
                        href="<?= Support::e(
                            previousPageUrl(
                                $currentPage + 1,
                                $selectedYear,
                                $selectedCategory
                            )
                        ) ?>"
                    >
                        Próxima →
                    </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <section class="home-empty-state">
            <span class="content-type-badge">
                Histórico
            </span>

            <h2>Nenhuma oferta encontrada</h2>

            <p class="home-empty-lead">
                Não encontramos Ofertas anteriores com os filtros
                selecionados.
            </p>

            <a
                class="btn primary"
                href="<?= APP_URL ?>/ofertas-anteriores"
            >
                Limpar filtros
            </a>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
