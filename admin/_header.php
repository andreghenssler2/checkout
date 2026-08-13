<?php
Auth::require();
$pageTitle = $pageTitle ?? 'Administrador';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= Support::e(
        SiteSettings::pageTitle($pageTitle)
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
        href="<?= APP_URL ?>/assets/css/app.css?v=1.9.0"
    >
</head>

<body class="admin-body">
<div class="admin-layout" data-admin-layout>
    <aside
        class="sidebar"
        id="adminSidebar"
        data-admin-sidebar
        aria-label="Menu administrativo"
    >
        <div class="sidebar-head">
            <div class="brand">
                <?= Support::e(
                    SiteSettings::title()
                ) ?>
            </div>
        </div>

        <nav>
            <a href="<?= APP_URL ?>/admin/">Dashboard</a>
            <a href="<?= APP_URL ?>/admin/ofertas/">Ofertas</a>
            <a href="<?= APP_URL ?>/admin/palpites/">Palpites</a>
            <a href="<?= APP_URL ?>/admin/pagamentos/">Pagamentos</a>
            <a href="<?= APP_URL ?>/admin/relatorios/">Relatórios</a>
            <a href="<?= APP_URL ?>/admin/configuracoes/site.php">Site</a>
            <a href="<?= APP_URL ?>/admin/configuracoes/pagamentos.php">Provedores</a>
            <a href="<?= APP_URL ?>/admin/configuracoes/asaas.php">Asaas</a>
            <a href="<?= APP_URL ?>/admin/configuracoes/pagbank.php">PagBank</a>
            <a href="<?= APP_URL ?>/admin/configuracoes/email.php">E-mail</a>
            <a href="<?= APP_URL ?>/admin/configuracoes/analytics.php">
                Google Analytics
            </a>
            <a href="<?= APP_URL ?>/" target="_blank">Ver site</a>
            <a href="<?= APP_URL ?>/admin/logout.php">Sair</a>
        </nav>
    </aside>

    <button
        class="admin-sidebar-overlay"
        type="button"
        data-admin-menu-overlay
        aria-label="Fechar menu"
        tabindex="-1"
    ></button>

    <main class="admin-main">
        <header class="admin-top">
            <div class="admin-top-left">
                <button
                    class="admin-menu-toggle"
                    type="button"
                    data-admin-menu-toggle
                    aria-controls="adminSidebar"
                    aria-expanded="true"
                    aria-label="Abrir ou fechar menu"
                    title="Menu"
                >
                    <span class="hamburger-lines" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>

                <strong><?= Support::e($pageTitle) ?></strong>
            </div>

            <span class="admin-user-name">
                <?= Support::e(
                    $_SESSION['admin_nome'] ?? 'Administrador'
                ) ?>
            </span>
        </header>

        <?php foreach (Support::flashes() as $flash): ?>
            <div class="alert <?= Support::e($flash['type']) ?>">
                <?= Support::e($flash['message']) ?>
            </div>
        <?php endforeach; ?>
