<?php

declare(strict_types=1);

final class PaymentGatewayManager
{
    public static function implemented(): array
    {
        return [
            'Asaas' => [
                'label' => 'Asaas',
                'class' => AsaasPaymentGateway::class,
                'methods' => ['PIX','Cartao','Boleto'],
            ],
            'PagBank' => [
                'label' => 'PagBank',
                'class' => PagBankPaymentGateway::class,
                'methods' => ['PIX','Cartao','Boleto'],
            ],
        ];
    }

    public static function roadmap(): array
    {
        return [
            'Sicredi' => [
                'label' => 'Sicredi',
                'methods' => [],
                'note' =>
                    'Estrutura preparada. Os métodos dependem das APIs liberadas para a conta Sicredi.',
            ],
        ];
    }

    public static function forMethod(
        string $method
    ): PaymentGatewayInterface {
        return self::provider(
            PaymentGatewaySettings::providerFor($method),
            $method
        );
    }

    public static function provider(
        string $provider,
        ?string $method = null
    ): PaymentGatewayInterface {
        $providers = self::implemented();

        if (!isset($providers[$provider])) {
            throw new RuntimeException(
                'O provedor "' . $provider . '" ainda não está habilitado.'
            );
        }

        $class = $providers[$provider]['class'];
        $gateway = new $class();

        if (
            $method !== null
            && !$gateway->supports($method)
        ) {
            throw new RuntimeException(
                $gateway->label()
                . ' não suporta '
                . $method
                . ' nesta versão.'
            );
        }

        return $gateway;
    }

    public static function assertSelectable(
        string $provider,
        string $method
    ): void {
        $providers = self::implemented();

        if (
            !isset($providers[$provider])
            || !in_array($method, $providers[$provider]['methods'], true)
        ) {
            throw new RuntimeException(
                'O provedor selecionado ainda não suporta '
                . $method
                . '.'
            );
        }
    }

    public static function selectableFor(
        string $method
    ): array {
        $result = [];

        foreach (self::implemented() as $key => $item) {
            if (in_array($method, $item['methods'], true)) {
                $result[$key] = $item['label'];
            }
        }

        return $result;
    }

    public static function configuredFor(string $method): bool
    {
        try {
            $provider = PaymentGatewaySettings::providerFor($method);

            return match ($provider) {
                'Asaas' =>
                    AsaasSettings::enabled()
                    && trim(AsaasSettings::apiKey()) !== '',
                'PagBank' =>
                    PagBankSettings::enabled()
                    && trim(PagBankSettings::token()) !== ''
                    && (
                        $method !== 'Cartao'
                        || PagBankSettings::publicKey() !== ''
                    ),
                default => false,
            };
        } catch (Throwable) {
            return false;
        }
    }

    public static function isMethodAvailable(string $method): bool
    {
        if (!self::configuredFor($method)) {
            return false;
        }

        try {
            $gateway = self::forMethod($method);
            $gateway->assertReady($method);
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public static function labelForMethod(string $method): string
    {
        $provider = PaymentGatewaySettings::providerFor($method);
        $providers = self::implemented();

        return (string)($providers[$provider]['label'] ?? $provider);
    }
}
