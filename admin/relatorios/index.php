<?php

require_once dirname(__DIR__, 2) . '/bootstrap.php';

Auth::require();

$pageTitle = 'Relatórios';

require dirname(__DIR__) . '/_header.php';
?>

<?php if (!PdfReportService::isInstalled()): ?>
    <div class="alert muted">
        <strong>Exportação PDF:</strong>
        o Dompdf ainda não está instalado neste servidor.
        <a href="<?= APP_URL ?>/admin/relatorios/dompdf.php">
            Ver como instalar
        </a>.
    </div>
<?php endif; ?>

<div class="report-card-grid">
    <a
        class="report-card"
        href="<?= APP_URL ?>/admin/relatorios/ofertas.php"
    >
        <span class="report-card-icon">R$</span>
        <div>
            <h2>Relatório de Ofertas</h2>
            <p>
                Valores recebidos por dia, mês ou ano, forma de pagamento,
                oferta, valor bruto, líquido e tarifas registradas.
            </p>
        </div>
    </a>

    <a
        class="report-card"
        href="<?= APP_URL ?>/admin/relatorios/palpites.php"
    >
        <span class="report-card-icon">⚽</span>
        <div>
            <h2>Relatório de Palpites</h2>
            <p>
                Veja quem participou, quem pagou, o valor recebido,
                o palpite marcado e os ganhadores dos jogos finalizados.
            </p>
        </div>
    </a>
</div>

<?php require dirname(__DIR__) . '/_footer.php'; ?>
