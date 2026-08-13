<?php

declare(strict_types=1);

final class AnalyticsService
{
    /**
     * Renderiza a tag do Google Analytics 4.
     *
     * Uso no <head>:
     *     <?= AnalyticsService::renderHead() ?>
     *
     * A função retorna string vazia quando a integração estiver desativada
     * ou o Measurement ID não for válido.
     */
    public static function renderHead(): string
    {
        if (!AnalyticsSettings::enabled()) {
            return '';
        }

        $measurementId = AnalyticsSettings::measurementId();

        if ($measurementId === '') {
            return '';
        }

        $idAttribute = htmlspecialchars(
            $measurementId,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );

        $idJson = json_encode(
            $measurementId,
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
        );

        return <<<HTML
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$idAttribute}"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', {$idJson});
</script>
HTML;
    }

    /**
     * Gera um evento GA4 opcional para uso futuro.
     *
     * Exemplo:
     * <?= AnalyticsService::renderEvent(
     *     'view_offer',
     *     ['offer_id' => 10]
     * ) ?>
     */
    public static function renderEvent(
        string $eventName,
        array $parameters = []
    ): string {
        if (!AnalyticsSettings::enabled()) {
            return '';
        }

        $eventName = trim($eventName);

        if (
            $eventName === ''
            || preg_match(
                '/^[A-Za-z][A-Za-z0-9_]{0,39}$/',
                $eventName
            ) !== 1
        ) {
            return '';
        }

        $eventJson = json_encode(
            $eventName,
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
        );

        $paramsJson = json_encode(
            self::sanitizeParameters($parameters),
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
        );

        return <<<HTML
<script>
if (typeof gtag === 'function') {
    gtag('event', {$eventJson}, {$paramsJson});
}
</script>
HTML;
    }

    private static function sanitizeParameters(
        array $parameters
    ): array {
        $clean = [];

        foreach ($parameters as $key => $value) {
            $key = trim((string)$key);

            if (
                preg_match(
                    '/^[A-Za-z][A-Za-z0-9_]{0,39}$/',
                    $key
                ) !== 1
            ) {
                continue;
            }

            if (
                is_string($value)
                || is_int($value)
                || is_float($value)
                || is_bool($value)
                || $value === null
            ) {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }
}
