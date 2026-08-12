<?php

require_once __DIR__ . '/bootstrap.php';

$now = new DateTimeImmutable('now');

$currentYear = (int)$now->format('Y');
$currentMonth = (int)$now->format('m');

$monthNames = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Março',
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro',
];

$currentMonthName = $monthNames[$currentMonth];

$monthOffers = OfertaRepository::activeForMonth(
    $currentYear,
    $currentMonth,
    4
);

$monthOfferIds = array_map(
    static fn (array $offer): int =>
        (int)$offer['idOferta'],
    $monthOffers
);

$upcomingOffers = OfertaRepository::upcomingAfterMonth(
    $currentYear,
    $currentMonth,
    $monthOfferIds,
    4
);

$upcomingOfferIds = array_map(
    static fn (array $offer): int =>
        (int)$offer['idOferta'],
    $upcomingOffers
);

$previousOffers = OfertaRepository::previousOffers(
    $currentYear,
    $currentMonth,
    array_merge(
        $monthOfferIds,
        $upcomingOfferIds
    ),
    6
);

function offerPeriodLabel(array $offer): string
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
        return 'A partir de '
            . date('d/m/Y', strtotime($start));
    }

    if ($end !== '') {
        return 'Até '
            . date('d/m/Y', strtotime($end));
    }

    return 'Disponível agora';
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

    <title>Checkout - IECLB Parobé</title>

    <meta
        name="description"
        content="Campanhas de ofertas da IECLB Parobé"
    >

    <link
        rel="stylesheet"
        href="<?= APP_URL ?>/assets/css/app.css?v=1.7.0"
    >

    <?= AnalyticsService::renderHead() ?>
</head>

<body class="bg">
<header class="public-head">
    <div>
        <strong>Checkout</strong>
        <span>IECLB Parobé</span>
    </div>
</header>

<main class="public-container home-offers-page">
    <?php if ($monthOffers): ?>
        <section class="home-month-section">
            <div class="home-section-heading">
                <div>
                    <span class="content-type-badge">
                        Ofertas do mês
                    </span>

                    <h1>
                        <?= Support::e($currentMonthName) ?>
                        <span><?= $currentYear ?></span>
                    </h1>

                    <p>
                        Confira as ofertas com início neste mês.
                    </p>
                </div>
            </div>

            <div class="home-featured-offers">
                <?php foreach ($monthOffers as $offer): ?>
                    <article class="home-featured-card">
                        <a
                            class="home-featured-image"
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

                        <div class="home-featured-body">
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
                                    offerPeriodLabel($offer)
                                ) ?>
                            </p>

                            <p class="home-offer-description">
                                <?= Support::e(
                                    mb_strimwidth(
                                        strip_tags(
                                            (string)$offer['descricao']
                                        ),
                                        0,
                                        125,
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
        </section>
    <?php else: ?>
        <section class="home-month-empty">
            <span class="content-type-badge">
                <?= Support::e($currentMonthName) ?>
                <?= $currentYear ?>
            </span>

            <h1>Nenhuma oferta com início neste mês</h1>

            <p>
                Confira abaixo as próximas campanhas já programadas.
            </p>
        </section>
    <?php endif; ?>

    <?php if ($upcomingOffers): ?>
        <section class="home-upcoming-section">
            <div class="home-section-heading home-upcoming-heading">
                <div>
                    <span class="content-type-badge">
                        Em breve
                    </span>

                    <h2>Próximas ofertas</h2>

                    <p>
                        As 4 próximas campanhas programadas.
                    </p>
                </div>
            </div>

            <div class="home-upcoming-list">
                <?php foreach ($upcomingOffers as $offer): ?>
                    <article class="home-upcoming-card">
                        <a
                            class="home-upcoming-thumb"
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
                                <div class="home-offer-placeholder compact">
                                    <span>Oferta</span>
                                </div>
                            <?php endif; ?>
                        </a>

                        <div class="home-upcoming-content">
                            <div>
                                <span class="home-offer-category">
                                    <?= Support::e(
                                        OfertaRepository::categoryLabel(
                                            $offer['categoria']
                                            ?? 'Local'
                                        )
                                    ) ?>
                                </span>

                                <h3>
                                    <a
                                        href="<?= APP_URL ?>/oferta/<?= Support::e(
                                            $offer['slug']
                                        ) ?>"
                                    >
                                        <?= Support::e(
                                            $offer['titulo']
                                        ) ?>
                                    </a>
                                </h3>

                                <p>
                                    <?= Support::e(
                                        offerPeriodLabel($offer)
                                    ) ?>
                                </p>
                            </div>

                            <a
                                class="btn"
                                href="<?= APP_URL ?>/oferta/<?= Support::e(
                                    $offer['slug']
                                ) ?>"
                            >
                                Abrir
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($previousOffers): ?>
        <section class="home-previous-section">
            <div class="home-section-heading home-previous-heading">
                <div>
                    <span class="content-type-badge">
                        Histórico
                    </span>

                    <h2>Ofertas anteriores</h2>

                    <p>
                        Campanhas iniciadas antes do mês atual.
                    </p>
                </div>

                <a
                    class="btn"
                    href="<?= APP_URL ?>/ofertas-anteriores"
                >
                    Ver todas as ofertas anteriores
                </a>
            </div>

            <div class="home-previous-list">
                <?php foreach ($previousOffers as $offer): ?>
                    <article class="home-previous-card">
                        <a
                            class="home-previous-thumb"
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
                                <div class="home-offer-placeholder compact">
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

                        <div class="home-previous-content">
                            <span class="home-offer-category">
                                <?= Support::e(
                                    OfertaRepository::categoryLabel(
                                        $offer['categoria']
                                        ?? 'Local'
                                    )
                                ) ?>
                            </span>

                            <h3>
                                <a
                                    href="<?= APP_URL ?>/oferta/<?= Support::e(
                                        $offer['slug']
                                    ) ?>"
                                >
                                    <?= Support::e($offer['titulo']) ?>
                                </a>
                            </h3>

                            <p>
                                <?= Support::e(
                                    offerPeriodLabel($offer)
                                ) ?>
                            </p>

                            <a
                                class="btn small"
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
        </section>
    <?php endif; ?>

    <?php if (!$monthOffers && !$upcomingOffers && !$previousOffers): ?>
        <section class="home-empty-state">
            <span class="content-type-badge">
                Checkout IECLB Parobé
            </span>

            <h1>Nenhuma oferta disponível agora</h1>

            <p class="home-empty-lead">
                No momento não temos campanhas de oferta abertas
                ou programadas.
            </p>

            <div class="home-empty-status">
                <span class="home-empty-status-dot"></span>
                <span>Aguardando novas campanhas</span>
            </div>

            <button
                class="btn primary"
                type="button"
                onclick="location.reload()"
            >
                Atualizar página
            </button>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
