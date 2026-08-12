<?php

declare(strict_types=1);

final class PublicPredictionResultPage
{
    /**
     * Mostra o resultado público do Palpite sem expor dados pessoais.
     *
     * Regras:
     * - se o jogo ainda não estiver Finalizado, informa que o resultado
     *   está sendo aguardado;
     * - se estiver Finalizado, mostra o placar final;
     * - informa apenas se houve ganhador ou não;
     * - mostra a quantidade de participações confirmadas em cada opção;
     * - nunca mostra nome, CPF, telefone, e-mail ou palpite individual.
     */
    public static function render(array $event): never
    {
        $eventId = (int)($event['idEventoPalpite'] ?? 0);

        $distribution = $eventId > 0
            ? PalpiteRepository::publicDistribution($eventId)
            : [];

        $distributionTotal = array_reduce(
            $distribution,
            static fn (int $sum, array $item): int =>
                $sum + (int)($item['total'] ?? 0),
            0
        );

        $finished = (
            ($event['status_jogo'] ?? '') === 'Finalizado'
        );

        $hasScore = (
            $event['placar_casa'] !== null
            && $event['placar_visitante'] !== null
        );

        $hasWinner = false;

        if ($finished && $hasScore && $eventId > 0) {
            $entries = PalpiteRepository::annotateEntries(
                PalpiteRepository::entries($eventId),
                $event
            );

            foreach ($entries as $entry) {
                if (!empty($entry['_ganhador'])) {
                    $hasWinner = true;
                    break;
                }
            }
        }

        $home = trim(
            (string)($event['equipe_casa'] ?? '')
        );

        $away = trim(
            (string)($event['equipe_visitante'] ?? '')
        );

        $title = trim(
            (string)($event['titulo'] ?? 'Resultado do palpite')
        );

        $image = trim(
            (string)($event['imagem'] ?? '')
        );

        $gameDate = !empty($event['data_jogo'])
            ? date(
                'd/m/Y \à\s H:i',
                strtotime((string)$event['data_jogo'])
            )
            : '';

        $finishedDate = !empty($event['finalizadoEm'])
            ? date(
                'd/m/Y \à\s H:i',
                strtotime((string)$event['finalizadoEm'])
            )
            : '';

        $e = static fn (mixed $value): string =>
            htmlspecialchars(
                (string)$value,
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );

        http_response_code(200);
        ?>
        <!doctype html>
        <html lang="pt-BR">
        <head>
            <meta charset="utf-8">

            <meta
                name="viewport"
                content="width=device-width,initial-scale=1"
            >

            <title><?= $e($title) ?> - Resultado do Palpite</title>

            <meta
                name="description"
                content="Resultado do palpite e placar do jogo"
            >

            <link
                rel="stylesheet"
                href="<?= $e(APP_URL) ?>/assets/css/app.css?v=1.7.7"
            >

            <?= AnalyticsService::renderHead() ?>
        </head>

        <body class="bg">
        <header class="public-head">
            <a
                href="<?= $e(APP_URL) ?>/"
                class="public-brand"
            >
                <strong>Checkout</strong>
                <span>IECLB Parobé</span>
            </a>
        </header>

        <main class="prediction-result-shell">
            <section class="prediction-result-card">
                <?php if ($image !== ''): ?>
                    <div class="prediction-result-image">
                        <img
                            src="<?= $e(APP_URL . '/' . $image) ?>"
                            alt="<?= $e($title) ?>"
                        >
                    </div>
                <?php endif; ?>

                <div class="prediction-result-content">
                    <span class="content-type-badge">
                        Resultado do palpite
                    </span>

                    <h1><?= $e($title) ?></h1>

                    <?php if ($gameDate !== ''): ?>
                        <p class="prediction-result-date">
                            Jogo: <?= $e($gameDate) ?>
                        </p>
                    <?php endif; ?>

                    <div class="prediction-public-match">
                        <span>Partida</span>
                        <strong><?= $e($title) ?></strong>
                    </div>

                    <div class="prediction-public-score">
                        <div class="prediction-public-team">
                            <span><?= $e($home) ?></span>

                            <strong>
                                <?= $hasScore
                                    ? (int)$event['placar_casa']
                                    : '—' ?>
                            </strong>
                        </div>

                        <div class="prediction-public-score-x">
                            x
                        </div>

                        <div class="prediction-public-team">
                            <strong>
                                <?= $hasScore
                                    ? (int)$event['placar_visitante']
                                    : '—' ?>
                            </strong>

                            <span><?= $e($away) ?></span>
                        </div>
                    </div>

                    <section class="prediction-public-distribution">
                        <div class="prediction-public-distribution-head">
                            <div>
                                <span class="content-type-badge">
                                    Palpites
                                </span>

                                <h2>Distribuição dos palpites</h2>

                                <p>
                                    Quantidade de participações confirmadas
                                    em cada opção disponível.
                                </p>
                            </div>

                            <div class="prediction-public-total">
                                <small>Total confirmado</small>
                                <strong><?= $distributionTotal ?></strong>
                            </div>
                        </div>

                        <?php if ($distributionTotal === 0): ?>
                            <div class="prediction-no-participations">
                                <span
                                    class="prediction-no-participations-icon"
                                    aria-hidden="true"
                                >
                                    —
                                </span>

                                <div>
                                    <strong>Sem participações</strong>

                                    <p>
                                        Não houve nenhum palpite com pagamento
                                        confirmado para esta partida.
                                    </p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="prediction-public-options">
                                <?php foreach ($distribution as $item): ?>
                                    <div
                                        class="prediction-public-option <?= !empty($item['ganhador'])
                                            ? 'winning-option'
                                            : '' ?>"
                                    >
                                        <span class="prediction-option-count">
                                            <?= (int)$item['total'] ?>
                                        </span>

                                        <span class="prediction-option-for">
                                            para
                                        </span>

                                        <div class="prediction-option-label">
                                            <?php if (($item['tipo'] ?? '') === 'Outro'): ?>
                                                <small>
                                                    Palpite digitado
                                                </small>
                                            <?php endif; ?>

                                            <strong>
                                                <?= $e($item['rotulo']) ?>
                                            </strong>
                                        </div>

                                        <?php if (!empty($item['ganhador'])): ?>
                                            <span class="prediction-winning-badge">
                                                🏆 Placar vencedor
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <p class="prediction-public-distribution-note">
                                A contagem considera somente participações com
                                pagamento confirmado. Em "Outro", aparece apenas o
                                texto do palpite digitado, sem nome, CPF, telefone
                                ou e-mail do participante.
                            </p>
                        <?php endif; ?>
                    </section>

                    <?php if ($finished): ?>
                        <div
                            class="prediction-public-result-status <?= $hasWinner
                                ? 'has-winner'
                                : 'no-winner' ?>"
                        >
                            <?php if ($distributionTotal === 0): ?>
                                <span
                                    class="prediction-public-result-icon"
                                    aria-hidden="true"
                                >
                                    —
                                </span>

                                <div>
                                    <strong>Sem participações.</strong>

                                    <p>
                                        Não houve palpites confirmados nesta
                                        partida, portanto não houve ganhador.
                                    </p>
                                </div>
                            <?php elseif ($hasWinner): ?>
                                <span
                                    class="prediction-public-result-icon"
                                    aria-hidden="true"
                                >
                                    🏆
                                </span>

                                <div>
                                    <strong>Houve ganhador!</strong>

                                    <p>
                                        O placar final teve pelo menos um
                                        palpite vencedor com pagamento
                                        confirmado.
                                    </p>
                                </div>
                            <?php else: ?>
                                <span
                                    class="prediction-public-result-icon"
                                    aria-hidden="true"
                                >
                                    ⚽
                                </span>

                                <div>
                                    <strong>Não houve ganhador.</strong>

                                    <p>
                                        Nenhum palpite pago correspondeu
                                        exatamente ao placar final.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($finishedDate !== ''): ?>
                            <p class="prediction-result-finished-at">
                                Resultado finalizado em
                                <strong><?= $e($finishedDate) ?></strong>.
                            </p>
                        <?php endif; ?>

                        <div class="prediction-public-privacy">
                            <strong>Privacidade dos participantes</strong>

                            <p>
                                Os dados dos ganhadores e dos demais
                                participantes não são exibidos nesta página.
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="prediction-public-result-status waiting">
                            <span
                                class="prediction-public-result-icon"
                                aria-hidden="true"
                            >
                                ⏳
                            </span>

                            <div>
                                <strong>
                                    Palpites encerrados. Aguardando o
                                    resultado final do jogo.
                                </strong>

                                <p>
                                    Assim que o administrador finalizar o
                                    placar, esta página informará se houve
                                    ganhador ou não.
                                </p>
                            </div>
                        </div>

                        <script>
                        window.setInterval(function () {
                            window.location.reload();
                        }, 30000);
                        </script>
                    <?php endif; ?>

                    <div class="prediction-result-actions">
                        <a
                            class="btn primary"
                            href="<?= $e(APP_URL) ?>/palpites/historico"
                        >
                            Histórico de Palpites
                        </a>

                        <a
                            class="btn"
                            href="<?= $e(APP_URL) ?>/"
                        >
                            Voltar ao início
                        </a>
                    </div>
                </div>
            </section>
        </main>
        </body>
        </html>
        <?php
        exit;
    }
}
