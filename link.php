<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$code = trim(
    (string)($_GET['codigo'] ?? '')
);

$link = ShortUrlService::resolve(
    $code
);

if (!$link) {
    http_response_code(404);
    ?>
    <!doctype html>
    <html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta
            name="viewport"
            content="width=device-width,initial-scale=1"
        >
        <title>Link não encontrado - Checkout</title>
        <link
            rel="stylesheet"
            href="<?= APP_URL ?>/assets/css/app.css"
        >
    </head>
    <body class="bg">
        <header class="public-head">
            <div>
                <strong>Checkout</strong>
                <span>IECLB Parobé</span>
            </div>
        </header>

        <main class="public-container">
            <section class="home-empty-state">
                <span class="content-type-badge">
                    Link curto
                </span>

                <h1>Este link não foi encontrado</h1>

                <p class="home-empty-lead">
                    Verifique se o endereço foi copiado corretamente.
                </p>

                <a
                    class="btn primary"
                    href="<?= APP_URL ?>/"
                >
                    Voltar ao início
                </a>
            </section>
        </main>
    </body>
    </html>
    <?php
    exit;
}

/*
 * 302 é intencional: se o título/slug da Oferta ou Palpite mudar,
 * o mesmo link curto continua apontando para o endereço atual.
 */
header(
    'Location: ' . $link['urlDestino'],
    true,
    302
);

exit;
