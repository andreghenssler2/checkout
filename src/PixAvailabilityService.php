<?php

declare(strict_types=1);

final class PixAvailabilityService
{
    private const CACHE_MINUTES = 10;

    public static function checkNow(): array
    {
        $environment = AsaasSettings::activeEnvironment();

        if (!AsaasSettings::enabled()) {
            return [
                'ambiente' => $environment,
                'disponivel' => false,
                'verificadoEm' => date('Y-m-d H:i:s'),
                'chave' => null,
                'chaves' => [],
                'mensagem' => 'Integração Asaas desativada.',
            ];
        }

        $asaas = new AsaasService();
        $keys = $asaas->listActivePixKeys();

        $available = count($keys) > 0;
        $firstKey = null;

        if ($available) {
            $firstKey = trim(
                (string)($keys[0]['key'] ?? '')
            ) ?: null;
        }

        AsaasSettings::savePixVerification(
            $environment,
            $available,
            $firstKey
        );

        return [
            'ambiente' => $environment,
            'disponivel' => $available,
            'verificadoEm' => date('Y-m-d H:i:s'),
            'chave' => $firstKey,
            'chaves' => $keys,
            'mensagem' => $available
                ? 'Chave Pix ativa encontrada.'
                : 'Nenhuma chave Pix ACTIVE foi encontrada para esta API Key.',
        ];
    }

    public static function status(bool $allowRefresh = true): array
    {
        $environment = AsaasSettings::activeEnvironment();
        $saved = AsaasSettings::pixVerification($environment);

        if (!$allowRefresh) {
            return $saved;
        }

        $verifiedAt = trim(
            (string)($saved['verificadoEm'] ?? '')
        );

        $stale = true;

        if ($verifiedAt !== '') {
            try {
                $checked = new DateTimeImmutable(
                    $verifiedAt,
                    new DateTimeZone(APP_TIMEZONE)
                );

                $limit = (new DateTimeImmutable(
                    'now',
                    new DateTimeZone(APP_TIMEZONE)
                ))->modify('-' . self::CACHE_MINUTES . ' minutes');

                $stale = $checked < $limit;
            } catch (Throwable) {
                $stale = true;
            }
        }

        if (!$stale && $saved['disponivel'] !== null) {
            return $saved;
        }

        try {
            return self::checkNow();
        } catch (Throwable $e) {
            /*
             * Em indisponibilidade temporária da API, preserva a última
             * informação conhecida. Se nunca houve teste, não bloqueia
             * automaticamente o PIX para evitar falso negativo.
             */
            if ($saved['disponivel'] !== null) {
                $saved['erro'] = $e->getMessage();
                return $saved;
            }

            return [
                'ambiente' => $environment,
                'disponivel' => true,
                'verificadoEm' => null,
                'chave' => null,
                'erro' => $e->getMessage(),
            ];
        }
    }

    public static function isAvailable(): bool
    {
        $status = self::status(true);

        return (bool)($status['disponivel'] ?? true);
    }

    /**
     * Disponibilidade do PIX para o Checkout.
     *
     * A mesma regra vale para Sandbox e Produção:
     * integração ativa, API Key configurada e ao menos uma
     * chave Pix ACTIVE confirmada pelo diagnóstico.
     */
    public static function checkoutAvailable(): bool
    {
        if (!AsaasSettings::enabled()) {
            return false;
        }

        if (trim(AsaasSettings::apiKey()) === '') {
            return false;
        }

        return self::isAvailable();
    }

    public static function assertAvailable(): void
    {
        if (!self::checkoutAvailable()) {
            throw new RuntimeException(
                'PIX indisponível para o ambiente Asaas atual.'
            );
        }
    }
}
