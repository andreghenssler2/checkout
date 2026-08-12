<?php

declare(strict_types=1);

final class AvailabilityPage
{
    public static function render(string $tipo, ?array $item): never
    {
        $tipo = strtolower(trim($tipo)) === 'palpite' ? 'palpite' : 'oferta';
        $existe = is_array($item);
        $agora = time();

        $titulo = $tipo === 'palpite'
            ? 'Palpite indisponível'
            : 'Oferta indisponível';

        $categoria = $tipo === 'palpite' ? 'Palpites' : 'Ofertas';
        $icone = $tipo === 'palpite' ? '⚽' : '❤';

        $mensagem = $tipo === 'palpite'
            ? 'Este formulário de palpite não está disponível neste momento.'
            : 'Esta campanha de oferta não está disponível neste momento.';

        $detalhe = 'Confira a página inicial para ver as opções disponíveis agora.';
        $rotuloData = '';
        $valorData = '';
        $classe = 'closed';

        if (!$existe) {
            http_response_code(404);

            $titulo = $tipo === 'palpite'
                ? 'Palpite não encontrado'
                : 'Oferta não encontrada';

            $mensagem = $tipo === 'palpite'
                ? 'O formulário que você tentou acessar não existe ou o endereço foi alterado.'
                : 'A campanha que você tentou acessar não existe ou o endereço foi alterado.';

            $detalhe = 'Você pode voltar à página inicial e escolher uma opção disponível.';
            $classe = 'not-found';
        } else {
            http_response_code(200);

            $ativo = (int)($item['ativo'] ?? 0) === 1;
            $inicio = !empty($item['data_inicio'])
                ? strtotime((string)$item['data_inicio'])
                : null;
            $fim = !empty($item['data_fim'])
                ? strtotime((string)$item['data_fim'])
                : null;

            if (!$ativo) {
                $titulo = $tipo === 'palpite'
                    ? 'Palpite temporariamente indisponível'
                    : 'Oferta temporariamente indisponível';

                $mensagem = $tipo === 'palpite'
                    ? 'As participações para este palpite estão desativadas no momento.'
                    : 'Esta campanha de oferta está desativada no momento.';

                $detalhe = 'Assim que estiver disponível novamente, você poderá acessar por este mesmo endereço.';
                $classe = 'paused';
            } elseif ($inicio !== null && $inicio > $agora) {
                $titulo = $tipo === 'palpite'
                    ? 'Os palpites ainda não começaram'
                    : 'Esta oferta ainda não começou';

                $mensagem = $tipo === 'palpite'
                    ? 'O formulário já está preparado, mas o período para enviar palpites ainda não iniciou.'
                    : 'A campanha já está preparada, mas o período para realizar ofertas ainda não iniciou.';

                $detalhe = 'Volte a partir da data indicada abaixo.';
                $rotuloData = 'Disponível a partir de';
                $valorData = date('d/m/Y \à\s H:i', $inicio);
                $classe = 'waiting';
            } elseif ($fim !== null && $fim < $agora) {
                $titulo = $tipo === 'palpite'
                    ? 'Palpites encerrados'
                    : 'Campanha encerrada';

                $mensagem = $tipo === 'palpite'
                    ? 'O período para participar deste palpite já foi encerrado.'
                    : 'O período desta campanha de oferta já foi encerrado.';

                $detalhe = $tipo === 'palpite'
                    ? 'Obrigado a todos que participaram. Confira a página inicial para ver novos jogos.'
                    : 'Obrigado a todos que contribuíram. Confira a página inicial para ver outras campanhas.';

                $rotuloData = 'Encerrado em';
                $valorData = date('d/m/Y \à\s H:i', $fim);
                $classe = 'finished';
            }
        }

        $nomeItem = $existe
            ? trim((string)($item['titulo'] ?? ''))
            : '';

        $imagem = $existe
            ? trim((string)($item['imagem'] ?? ''))
            : '';

        $e = static fn (mixed $valor): string =>
            htmlspecialchars(
                (string)$valor,
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );

        $home = APP_URL . '/';

        ?>
        <!doctype html>
        <html lang="pt-BR">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <meta name="robots" content="noindex,follow">
            <title><?= $e($titulo) ?> - Checkout IECLB Parobé</title>
            <link rel="stylesheet" href="<?= $e($home) ?>assets/css/app.css">
        </head>
        <body class="bg unavailable-body">

        <header class="public-head">
            <a href="<?= $e($home) ?>" class="public-brand">
                <strong>Checkout</strong>
                <span>IECLB Parobé</span>
            </a>
        </header>

        <main class="unavailable-shell">
            <section class="unavailable-card unavailable-<?= $e($classe) ?>">
                <div class="unavailable-visual">
                    <?php if ($imagem !== ''): ?>
                        <div class="unavailable-image-wrap">
                            <img
                                src="<?= $e($home . $imagem) ?>"
                                alt="<?= $e($nomeItem) ?>"
                                class="unavailable-image"
                            >
                            <div class="unavailable-image-overlay"></div>
                        </div>
                    <?php else: ?>
                        <div class="unavailable-icon-wrap" aria-hidden="true">
                            <div class="unavailable-icon-ring">
                                <span><?= $e($icone) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="unavailable-content">
                    <span class="content-type-badge"><?= $e($categoria) ?></span>

                    <?php if ($nomeItem !== ''): ?>
                        <p class="unavailable-item-title"><?= $e($nomeItem) ?></p>
                    <?php endif; ?>

                    <h1><?= $e($titulo) ?></h1>

                    <p class="unavailable-lead">
                        <?= $e($mensagem) ?>
                    </p>

                    <p class="unavailable-detail">
                        <?= $e($detalhe) ?>
                    </p>

                    <?php if ($valorData !== ''): ?>
                        <div class="unavailable-date">
                            <span><?= $e($rotuloData) ?></span>
                            <strong><?= $e($valorData) ?></strong>
                        </div>
                    <?php endif; ?>

                    <div class="unavailable-actions">
                        <a class="btn primary" href="<?= $e($home) ?>">
                            Ver opções disponíveis
                        </a>

                        <button
                            class="btn unavailable-back"
                            type="button"
                            onclick="history.length > 1 ? history.back() : location.href='<?= $e($home) ?>'"
                        >
                            Voltar
                        </button>
                    </div>

                    <div class="unavailable-help">
                        <span class="unavailable-help-icon">i</span>
                        <p>
                            A disponibilidade é definida pelo período e pelo
                            status configurados pelo administrador.
                        </p>
                    </div>
                </div>
            </section>

            <footer class="unavailable-footer">
                <strong>IECLB Parobé</strong>
                <span>Checkout seguro para ofertas e participações.</span>
            </footer>
        </main>

        </body>
        </html>
        <?php
        exit;
    }
}
