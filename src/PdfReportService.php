<?php

declare(strict_types=1);

final class PdfReportService
{
    private static bool $loaded = false;

    public static function autoloadCandidates(): array
    {
        $root = dirname(__DIR__);
        $parent = dirname($root);

        return [
            $root . '/vendor/autoload.php',
            $parent . '/vendor/autoload.php',
            $root . '/lib/dompdf/autoload.inc.php',
            $root . '/dompdf/autoload.inc.php',
        ];
    }

    public static function installedPath(): ?string
    {
        if (class_exists(\Dompdf\Dompdf::class)) {
            return 'already-loaded';
        }

        foreach (self::autoloadCandidates() as $loader) {
            if (!is_file($loader)) {
                continue;
            }

            try {
                require_once $loader;
            } catch (Throwable) {
                continue;
            }

            if (class_exists(\Dompdf\Dompdf::class)) {
                return $loader;
            }
        }

        return null;
    }

    public static function isInstalled(): bool
    {
        return self::installedPath() !== null;
    }

    public static function requirements(): array
    {
        return [
            'php' => [
                'ok' => version_compare(PHP_VERSION, '8.1.0', '>='),
                'value' => PHP_VERSION,
                'label' => 'PHP 8.1 ou superior',
            ],
            'mbstring' => [
                'ok' => extension_loaded('mbstring'),
                'value' => extension_loaded('mbstring')
                    ? 'Carregada'
                    : 'Ausente',
                'label' => 'Extensão mbstring',
            ],
            'gd' => [
                'ok' => extension_loaded('gd'),
                'value' => extension_loaded('gd')
                    ? 'Carregada'
                    : 'Ausente',
                'label' => 'Extensão GD',
            ],
            'dompdf' => [
                'ok' => self::isInstalled(),
                'value' => self::installedPath() ?: 'Não encontrado',
                'label' => 'Dompdf',
            ],
        ];
    }

    public static function loadDompdf(): bool
    {
        if (
            self::$loaded
            && class_exists(\Dompdf\Dompdf::class)
        ) {
            return true;
        }

        $path = self::installedPath();

        if (
            $path !== null
            && class_exists(\Dompdf\Dompdf::class)
        ) {
            self::$loaded = true;
            return true;
        }

        return false;
    }

    public static function stream(
        string $title,
        string $bodyHtml,
        string $filename,
        string $orientation = 'landscape'
    ): never {
        if (!self::loadDompdf()) {
            self::renderMissingDependency();
        }

        $orientation = $orientation === 'portrait'
            ? 'portrait'
            : 'landscape';

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set(
            'chroot',
            dirname(__DIR__)
        );

        $dompdf = new \Dompdf\Dompdf($options);

        $dompdf->loadHtml(
            self::document($title, $bodyHtml),
            'UTF-8'
        );

        $dompdf->setPaper(
            'A4',
            $orientation
        );

        $dompdf->render();

        try {
            $canvas = $dompdf->getCanvas();
            $fontMetrics = $dompdf->getFontMetrics();
            $font = $fontMetrics->getFont(
                'DejaVu Sans',
                'normal'
            );

            $width = $canvas->get_width();
            $height = $canvas->get_height();

            $canvas->page_text(
                $width - 112,
                $height - 24,
                'Página {PAGE_NUM} de {PAGE_COUNT}',
                $font,
                8,
                [90 / 255, 90 / 255, 90 / 255]
            );

            $canvas->page_text(
                28,
                $height - 24,
                'Checkout IECLB Parobé',
                $font,
                8,
                [90 / 255, 90 / 255, 90 / 255]
            );
        } catch (Throwable) {
            // A numeração é complementar; o PDF continua válido sem ela.
        }

        $filename = self::safeFilename($filename);

        $dompdf->stream(
            $filename,
            [
                'Attachment' => true,
            ]
        );

        exit;
    }

    public static function money(mixed $value): string
    {
        return Support::money(
            (float)($value ?? 0)
        );
    }

    public static function dateTime(
        mixed $value
    ): string {
        $value = trim((string)$value);

        if ($value === '') {
            return '—';
        }

        $timestamp = strtotime($value);

        return $timestamp !== false
            ? date('d/m/Y H:i', $timestamp)
            : $value;
    }

    public static function cpf(
        mixed $value
    ): string {
        $digits = Support::cpfDigits(
            (string)$value
        );

        if (strlen($digits) !== 11) {
            return (string)$value;
        }

        return substr($digits, 0, 3)
            . '.'
            . substr($digits, 3, 3)
            . '.'
            . substr($digits, 6, 3)
            . '-'
            . substr($digits, 9, 2);
    }

    public static function filterDescription(
        array $parts
    ): string {
        $parts = array_values(
            array_filter(
                array_map(
                    static fn (mixed $item): string =>
                        trim((string)$item),
                    $parts
                ),
                static fn (string $item): bool =>
                    $item !== ''
            )
        );

        return $parts
            ? implode(' | ', $parts)
            : 'Sem filtros adicionais';
    }

    private static function renderMissingDependency(): never
    {
        http_response_code(503);
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        $root = dirname(__DIR__);
        $rootEscaped = Support::e($root);
        $statusUrl = APP_URL . '/admin/relatorios/dompdf.php';

        echo '<!doctype html><html lang="pt-br"><head><meta charset="utf-8">';
        echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
        echo '<title>Dompdf não instalado</title>';
        echo '<style>
            body{font-family:Arial,sans-serif;background:#f3f4f6;color:#202124;margin:0;padding:32px}
            .box{max-width:850px;margin:0 auto;background:#fff;border:1px solid #ddd;border-radius:12px;padding:26px}
            h1{margin-top:0} code,pre{background:#f2f3f5;border-radius:6px}
            pre{padding:14px;overflow:auto} a{color:#8b1234;font-weight:700}
            .warn{padding:12px 14px;background:#fff4df;border:1px solid #efd4a2;border-radius:8px}
        </style></head><body><div class="box">';
        echo '<h1>Dompdf ainda não está instalado</h1>';
        echo '<p>A tela de relatório está funcionando, mas o servidor ainda não possui a biblioteca necessária para gerar o PDF.</p>';
        echo '<p class="warn"><strong>Nenhum dado foi perdido.</strong> Depois de instalar o Dompdf, use o mesmo botão Exportar PDF novamente.</p>';
        echo '<h2>Instalação recomendada</h2>';
        echo '<p>No Terminal do cPanel, execute:</p>';
        echo '<pre>cd ' . $rootEscaped . "\ncomposer install --no-dev --optimize-autoloader</pre>";
        echo '<p>Ao terminar, deve existir:</p>';
        echo '<pre>' . $rootEscaped . '/vendor/autoload.php</pre>';
        echo '<p><a href="' . Support::e($statusUrl) . '">Ver diagnóstico do Dompdf</a></p>';
        echo '</div></body></html>';

        exit;
    }

    private static function safeFilename(
        string $filename
    ): string {
        $filename = trim($filename);

        if ($filename === '') {
            $filename = 'relatorio.pdf';
        }

        if (!str_ends_with(strtolower($filename), '.pdf')) {
            $filename .= '.pdf';
        }

        $base = pathinfo(
            $filename,
            PATHINFO_FILENAME
        );

        $base = Support::slug($base);

        return $base . '.pdf';
    }

    private static function document(
        string $title,
        string $bodyHtml
    ): string {
        $escapedTitle = Support::e($title);
        $generated = Support::e(
            date('d/m/Y H:i:s')
        );

        return <<<HTML
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>{$escapedTitle}</title>
<style>
    @page { margin: 34px 28px 42px; }
    * { box-sizing: border-box; }
    body {
        margin: 0;
        font-family: "DejaVu Sans", sans-serif;
        font-size: 9px;
        line-height: 1.35;
        color: #202124;
    }
    h1 { margin:0 0 4px;font-size:19px;color:#171717; }
    h2 { margin:18px 0 7px;font-size:13px;color:#171717; }
    p { margin:4px 0; }
    .header { border-bottom:2px solid #7a1630;padding-bottom:10px;margin-bottom:13px; }
    .header-table { width:100%;border-collapse:collapse; }
    .header-table td { padding:0;border:0;vertical-align:top; }
    .header-right { text-align:right;color:#666;font-size:8px; }
    .subtitle { color:#666;font-size:9px; }
    .filters {
        margin:9px 0 13px;padding:8px 10px;background:#f5f6f7;
        border:1px solid #dedfe2;border-radius:4px;color:#454545;
    }
    .summary {
        width:100%;border-collapse:separate;border-spacing:5px;
        margin:0 -5px 10px;
    }
    .summary td {
        width:20%;padding:9px;border:1px solid #dfe2e5;
        background:#fafafa;vertical-align:top;
    }
    .summary .label {
        display:block;color:#666;font-size:7.5px;
        text-transform:uppercase;margin-bottom:4px;
    }
    .summary .value {
        display:block;font-size:14px;font-weight:bold;color:#1f1f1f;
    }
    .notice {
        margin:8px 0 12px;padding:8px 10px;background:#fff8e5;
        border:1px solid #eedba4;color:#684f0b;
    }
    table.report {
        width:100%;border-collapse:collapse;margin:0 0 12px;table-layout:fixed;
    }
    table.report thead { display:table-header-group; }
    table.report tr { page-break-inside:avoid; }
    table.report th {
        padding:5px 4px;background:#eeeeef;border:1px solid #d7d7d9;
        text-align:left;font-size:7.5px;font-weight:bold;
    }
    table.report td {
        padding:5px 4px;border:1px solid #dedee0;vertical-align:top;
        overflow-wrap:anywhere;
    }
    .right { text-align:right; }
    .center { text-align:center; }
    .small { color:#666;font-size:7.5px; }
    .paid { color:#08783f;font-weight:bold; }
    .winner { background:#eef9f1;font-weight:bold; }
    .correct { background:#f5fbf7; }
    .winner-box {
        margin:8px 0 14px;padding:9px 11px;border:1px solid #b9dfc7;
        background:#eef9f1;
    }
    .winner-box strong { color:#08783f; }
    .page-break { page-break-before:always; }
</style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    <h1>{$escapedTitle}</h1>
                    <div class="subtitle">Checkout IECLB Parobé</div>
                </td>
                <td class="header-right">
                    Gerado em<br>
                    <strong>{$generated}</strong>
                </td>
            </tr>
        </table>
    </div>
    {$bodyHtml}
</body>
</html>
HTML;
    }
}
