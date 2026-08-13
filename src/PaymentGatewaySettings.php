<?php
declare(strict_types=1);

final class PaymentGatewaySettings
{
    private static ?array $cache = null;

    public static function get(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        try {
            $row = Database::connection()
                ->query(
                    'SELECT *
                     FROM configuracoes_pagamentos
                     WHERE idConfiguracao=1'
                )
                ->fetch();

            self::$cache = $row ?: self::defaults();
        } catch (PDOException) {
            self::$cache = self::defaults();
        }

        return self::$cache;
    }

    public static function providerFor(string $method): string
    {
        $settings = self::get();

        return match ($method) {
            'PIX' => (string)($settings['provedor_pix'] ?? 'Asaas'),
            'Cartao' => (string)($settings['provedor_cartao'] ?? 'Asaas'),
            'Boleto' => (string)($settings['provedor_boleto'] ?? 'Asaas'),
            default => throw new InvalidArgumentException(
                'Forma de pagamento inválida.'
            ),
        };
    }

    public static function save(
        string $pix,
        string $card,
        string $boleto
    ): void {
        foreach ([
            'PIX' => $pix,
            'Cartao' => $card,
            'Boleto' => $boleto,
        ] as $method => $provider) {
            PaymentGatewayManager::assertSelectable(
                $provider,
                $method
            );
        }

        Database::connection()->prepare(
            'UPDATE configuracoes_pagamentos SET
                provedor_pix=:pix,
                provedor_cartao=:cartao,
                provedor_boleto=:boleto
             WHERE idConfiguracao=1'
        )->execute([
            ':pix' => $pix,
            ':cartao' => $card,
            ':boleto' => $boleto,
        ]);

        self::$cache = null;
    }

    private static function defaults(): array
    {
        return [
            'provedor_pix' => 'Asaas',
            'provedor_cartao' => 'Asaas',
            'provedor_boleto' => 'Asaas',
        ];
    }
}
