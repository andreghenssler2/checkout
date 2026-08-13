<?php

declare(strict_types=1);

final class PagBankPaymentMapper
{
    public static function orderId(array $order): string
    {
        return trim((string)($order['id'] ?? ''));
    }

    public static function reference(array $order): string
    {
        return trim((string)($order['reference_id'] ?? ''));
    }

    public static function chargeId(array $order): string
    {
        $charge = self::primaryCharge($order);
        return trim((string)($charge['id'] ?? ''));
    }

    public static function providerStatus(array $order): string
    {
        $statuses = [];

        foreach (self::charges($order) as $charge) {
            $status = strtoupper(trim((string)($charge['status'] ?? '')));
            if ($status !== '') {
                $statuses[] = $status;
            }
        }

        foreach (
            ['PAID','DECLINED','CANCELED','IN_ANALYSIS','AUTHORIZED','WAITING']
            as $priority
        ) {
            if (in_array($priority, $statuses, true)) {
                return $priority;
            }
        }

        if (self::qrCodes($order)) {
            return 'WAITING';
        }

        return 'WAITING';
    }

    public static function localStatus(array $order): string
    {
        $status = self::providerStatus($order);

        if ($status === 'PAID') {
            return 'Pago';
        }

        if ($status === 'DECLINED') {
            return 'Recusado';
        }

        if ($status === 'CANCELED') {
            return 'Cancelado';
        }

        if ($status === 'WAITING') {
            $dueDate = self::boletoData($order)['dueDate'] ?? null;

            if ($dueDate) {
                try {
                    $due = new DateTimeImmutable(
                        (string)$dueDate,
                        new DateTimeZone(APP_TIMEZONE)
                    );
                    $today = new DateTimeImmutable(
                        'today',
                        new DateTimeZone(APP_TIMEZONE)
                    );

                    if ($due < $today) {
                        return 'Vencido';
                    }
                } catch (Throwable) {
                }
            }
        }

        return 'Pendente';
    }

    public static function paidAt(array $order): ?string
    {
        foreach (self::charges($order) as $charge) {
            $paidAt = trim((string)($charge['paid_at'] ?? ''));

            if ($paidAt !== '') {
                try {
                    return (new DateTimeImmutable($paidAt))
                        ->format('Y-m-d H:i:s');
                } catch (Throwable) {
                    return null;
                }
            }
        }

        return null;
    }

    public static function invoiceUrl(array $order): ?string
    {
        return self::linkBy(
            $order['links'] ?? [],
            'SELF',
            'application/json'
        );
    }

    public static function pixData(
        array $order,
        ?string $encodedImage = null
    ): array {
        $qr = self::qrCodes($order)[0] ?? [];

        return [
            'encodedImage' => $encodedImage,
            'payload' => trim((string)($qr['text'] ?? '')) ?: null,
            'expirationDate' =>
                trim((string)($qr['expiration_date'] ?? '')) ?: null,
        ];
    }

    public static function boletoData(array $order): array
    {
        foreach (self::charges($order) as $charge) {
            $method = $charge['payment_method'] ?? [];

            if (
                strtoupper(trim((string)($method['type'] ?? '')))
                !== 'BOLETO'
            ) {
                continue;
            }

            $boleto = $method['boleto'] ?? [];

            $line = trim(
                (string)(
                    $boleto['formatted_barcode']
                    ?? $boleto['barcode']
                    ?? ''
                )
            );

            $pdf = self::linkBy(
                $charge['links'] ?? [],
                null,
                'application/pdf'
            );

            if ($pdf === null) {
                $pdf = self::linkBy(
                    $charge['links'] ?? [],
                    'SELF',
                    null
                );
            }

            return [
                'identificationField' => $line ?: null,
                'bankSlipUrl' => $pdf,
                'dueDate' =>
                    trim((string)($boleto['due_date'] ?? '')) ?: null,
            ];
        }

        return [
            'identificationField' => null,
            'bankSlipUrl' => null,
            'dueDate' => null,
        ];
    }

    public static function grossValue(array $order): ?float
    {
        $charge = self::primaryCharge($order);

        $value = $charge['amount']['value']
            ?? (self::qrCodes($order)[0]['amount']['value'] ?? null);

        if (!is_numeric($value)) {
            return null;
        }

        return round(((float)$value) / 100, 2);
    }

    private static function charges(array $order): array
    {
        $charges = $order['charges'] ?? [];

        return is_array($charges)
            ? array_values(array_filter($charges, 'is_array'))
            : [];
    }

    private static function primaryCharge(array $order): array
    {
        $charges = self::charges($order);

        foreach ($charges as $charge) {
            if (
                strtoupper(trim((string)($charge['status'] ?? '')))
                === 'PAID'
            ) {
                return $charge;
            }
        }

        return $charges[0] ?? [];
    }

    private static function qrCodes(array $order): array
    {
        $qr = $order['qr_codes'] ?? $order['qr_code'] ?? [];

        return is_array($qr)
            ? array_values(array_filter($qr, 'is_array'))
            : [];
    }

    private static function linkBy(
        mixed $links,
        ?string $rel,
        ?string $media
    ): ?string {
        if (!is_array($links)) {
            return null;
        }

        foreach ($links as $link) {
            if (!is_array($link)) {
                continue;
            }

            if (
                $rel !== null
                && strtoupper(trim((string)($link['rel'] ?? '')))
                    !== strtoupper($rel)
            ) {
                continue;
            }

            if (
                $media !== null
                && strtolower(trim((string)($link['media'] ?? '')))
                    !== strtolower($media)
            ) {
                continue;
            }

            $href = trim((string)($link['href'] ?? ''));

            if ($href !== '') {
                return $href;
            }
        }

        return null;
    }
}
