<?php
require_once __DIR__ . '/bootstrap.php';

$items = PalpiteRepository::publicHistory();

$upcoming = [];
$past = [];

foreach ($items as $item) {
    if (PalpiteRepository::isPastGame($item)) {
        $past[] = $item;
    } else {
        $upcoming[] = $item;
    }
}

function phDate(array $event): string
{
    if (empty($event['data_jogo'])) {
        return 'Data não informada';
    }

    return date(
        'd/m/Y \à\s H:i',
        strtotime((string)$event['data_jogo'])
    );
}

function phStatus(array $event): string
{
    if (($event['status_jogo'] ?? '') === 'Finalizado') {
        return 'Finalizado';
    }

    if (PalpiteRepository::isPastGame($event)) {
        return 'Aguardando resultado final';
    }

    if (($event['status_jogo'] ?? '') === 'EmAndamento') {
        return 'Em andamento';
    }

    return 'Agendado';
}

function phReceived(array $event): string
{
    $value = (float)($event['valorRecebido'] ?? 0);

    return $value > 0
        ? Support::money($value)
        : 'Sem valor recebido';
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Histórico de Palpites - Checkout IECLB Parobé</title>
    <meta
        name="description"
        content="Jogos de palpites realizados e próximos jogos"
    >
    <link
        rel="stylesheet"
        href="<?= APP_URL ?>/assets/css/app.css?v=1.7.7"
    >
    <?= AnalyticsService::renderHead() ?>
</head>
<body class="bg">

<header class="public-head">
    <a href="<?= APP_URL ?>/" class="public-brand">
        <strong>Checkout</strong>
        <span>IECLB Parobé</span>
    </a>
</header>

<main class="public-container prediction-history-page">
    <section class="prediction-history-hero">
        <span class="content-type-badge">Palpites</span>
        <h1>Histórico de Palpites</h1>
        <p>
            Consulte os próximos jogos e os resultados das partidas
            já realizadas.
        </p>
    </section>

    <section class="prediction-history-section">
        <div class="prediction-history-heading">
            <div>
                <span class="content-type-badge">Próximos</span>
                <h2>Jogos a serem realizados</h2>
                <p>Escolha uma partida para fazer seu palpite.</p>
            </div>

            <strong class="prediction-history-count">
                <?= count($upcoming) ?>
            </strong>
        </div>

        <?php if ($upcoming): ?>
            <div class="prediction-history-grid">
                <?php foreach ($upcoming as $event): ?>
                    <article class="prediction-history-card upcoming">
                        <?php if (!empty($event['imagem'])): ?>
                            <a
                                class="prediction-history-image"
                                href="<?= APP_URL ?>/palpite/<?= Support::e($event['slug']) ?>"
                            >
                                <img
                                    src="<?= APP_URL ?>/<?= Support::e($event['imagem']) ?>"
                                    alt="<?= Support::e($event['titulo']) ?>"
                                >
                            </a>
                        <?php endif; ?>

                        <div class="prediction-history-body">
                            <span class="badge muted">
                                <?= Support::e(phStatus($event)) ?>
                            </span>

                            <h3><?= Support::e($event['titulo']) ?></h3>

                            <div class="prediction-history-versus">
                                <strong><?= Support::e($event['equipe_casa']) ?></strong>
                                <span>x</span>
                                <strong><?= Support::e($event['equipe_visitante']) ?></strong>
                            </div>

                            <p class="prediction-history-date">
                                <?= Support::e(phDate($event)) ?>
                            </p>

                            <?php if ($event['menorValor'] !== null): ?>
                                <p class="prediction-history-value">
                                    Palpite a partir de
                                    <strong>
                                        <?= Support::money((float)$event['menorValor']) ?>
                                    </strong>
                                </p>
                            <?php endif; ?>

                            <a
                                class="btn primary"
                                href="<?= APP_URL ?>/palpite/<?= Support::e($event['slug']) ?>"
                            >
                                Palpitar
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="prediction-history-empty">
                Nenhum próximo jogo cadastrado.
            </div>
        <?php endif; ?>
    </section>

    <section class="prediction-history-section past-games">
        <div class="prediction-history-heading">
            <div>
                <span class="content-type-badge">Histórico</span>
                <h2>Jogos realizados</h2>
                <p>
                    Consulte placar, participações e valor recebido.
                </p>
            </div>

            <strong class="prediction-history-count">
                <?= count($past) ?>
            </strong>
        </div>

        <?php if ($past): ?>
            <div class="prediction-history-grid">
                <?php foreach ($past as $event): ?>
                    <?php
                    $hasScore = (
                        $event['placar_casa'] !== null
                        && $event['placar_visitante'] !== null
                    );
                    ?>

                    <article class="prediction-history-card finished">
                        <?php if (!empty($event['imagem'])): ?>
                            <a
                                class="prediction-history-image"
                                href="<?= APP_URL ?>/palpite/<?= Support::e($event['slug']) ?>"
                            >
                                <img
                                    src="<?= APP_URL ?>/<?= Support::e($event['imagem']) ?>"
                                    alt="<?= Support::e($event['titulo']) ?>"
                                >
                            </a>
                        <?php endif; ?>

                        <div class="prediction-history-body">
                            <span
                                class="badge <?= ($event['status_jogo'] ?? '') === 'Finalizado'
                                    ? 'paid'
                                    : 'muted' ?>"
                            >
                                <?= Support::e(phStatus($event)) ?>
                            </span>

                            <h3><?= Support::e($event['titulo']) ?></h3>

                            <div class="prediction-history-score">
                                <span><?= Support::e($event['equipe_casa']) ?></span>
                                <strong>
                                    <?= $hasScore ? (int)$event['placar_casa'] : '—' ?>
                                </strong>
                                <b>x</b>
                                <strong>
                                    <?= $hasScore ? (int)$event['placar_visitante'] : '—' ?>
                                </strong>
                                <span><?= Support::e($event['equipe_visitante']) ?></span>
                            </div>

                            <p class="prediction-history-date">
                                <?= Support::e(phDate($event)) ?>
                            </p>

                            <div class="prediction-history-stats">
                                <div>
                                    <small>Palpites pagos</small>
                                    <strong><?= (int)($event['totalPagos'] ?? 0) ?></strong>
                                </div>

                                <div>
                                    <small>Valor recebido</small>
                                    <strong><?= Support::e(phReceived($event)) ?></strong>
                                </div>
                            </div>

                            <a
                                class="btn"
                                href="<?= APP_URL ?>/palpite/<?= Support::e($event['slug']) ?>"
                            >
                                Ver histórico
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="prediction-history-empty">
                Ainda não existem jogos realizados.
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
