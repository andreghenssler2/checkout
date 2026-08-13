<?php

declare(strict_types=1);

final class PagBankFeeService
{
    public const PIX_PERCENT = 1.89;
    public const BOLETO_FIXED = 3.99;

    public const CARD_14_PERCENT = 4.99;
    public const CARD_30_PERCENT = 3.99;
    public const CARD_FIXED = 0.40;

    public const DEBIT_PERCENT = 2.39;

    public static function calculate(
        string $method,
        float $gross
    ): array {
        $gross = round(
            max(0, $gross),
            2
        );

        $fee = null;
        $label = '';
        $settlement = null;

        if ($method === 'PIX') {
            $fee = self::percentage(
                $gross,
                self::PIX_PERCENT
            );

            $label = '1,89%';
            $settlement = 'Na hora';
        } elseif ($method === 'Boleto') {
            $fee = min(
                $gross,
                self::BOLETO_FIXED
            );

            $label = 'R$ 3,99';
            $settlement = '3 dias';
        } elseif ($method === 'Cartao') {
            $days =
                PagBankSettings::cardSettlementDays();

            $percent = $days === 14
                ? self::CARD_14_PERCENT
                : self::CARD_30_PERCENT;

            $fee = self::percentage(
                $gross,
                $percent
            ) + self::CARD_FIXED;

            $fee = min(
                $gross,
                round($fee, 2)
            );

            $label = number_format(
                $percent,
                2,
                ',',
                '.'
            )
                . '% + R$ 0,40';

            $settlement = $days . ' dias';
        }

        if ($fee === null) {
            return [
                'fee' => null,
                'net' => null,
                'label' => null,
                'settlement' => null,
            ];
        }

        $fee = round(
            min($gross, max(0, $fee)),
            2
        );

        return [
            'fee' => $fee,
            'net' => round(
                max(0, $gross - $fee),
                2
            ),
            'label' => $label,
            'settlement' => $settlement,
        ];
    }

    public static function rateTable(): array
    {
        return [
            [
                'method' => 'Débito online',
                'settlement' => '1 dia',
                'fee' => '2,39%',
                'available' => false,
            ],
            [
                'method' => 'Cartão de crédito à vista',
                'settlement' => '14 dias',
                'fee' => '4,99% + R$ 0,40',
                'available' => true,
            ],
            [
                'method' => 'Cartão de crédito à vista',
                'settlement' => '30 dias',
                'fee' => '3,99% + R$ 0,40',
                'available' => true,
            ],
            [
                'method' => 'Pix',
                'settlement' => 'Na hora',
                'fee' => '1,89%',
                'available' => true,
            ],
            [
                'method' => 'Boleto',
                'settlement' => '3 dias',
                'fee' => 'R$ 3,99',
                'available' => true,
            ],
            [
                'method' => 'Parcelado',
                'settlement' => '—',
                'fee' => 'Pode haver taxa adicional',
                'available' => false,
            ],
        ];
    }

    public static function activePublicRates(): array
    {
        $items = [];

        try {
            if (
                PaymentGatewaySettings::providerFor('PIX')
                === 'PagBank'
            ) {
                $items[] = [
                    'method' => 'Pix',
                    'settlement' => 'Na hora',
                    'fee' => '1,89%',
                ];
            }

            if (
                PaymentGatewaySettings::providerFor('Cartao')
                === 'PagBank'
            ) {
                $days =
                    PagBankSettings::cardSettlementDays();

                $rate = $days === 14
                    ? '4,99% + R$ 0,40'
                    : '3,99% + R$ 0,40';

                $items[] = [
                    'method' => 'Cartão de crédito à vista',
                    'settlement' => $days . ' dias',
                    'fee' => $rate,
                ];
            }

            if (
                PaymentGatewaySettings::providerFor('Boleto')
                === 'PagBank'
            ) {
                $items[] = [
                    'method' => 'Boleto',
                    'settlement' => '3 dias',
                    'fee' => 'R$ 3,99',
                ];
            }
        } catch (Throwable) {
            return [];
        }

        return $items;
    }

    private static function percentage(
        float $gross,
        float $percent
    ): float {
        return round(
            $gross * ($percent / 100),
            2
        );
    }
}
