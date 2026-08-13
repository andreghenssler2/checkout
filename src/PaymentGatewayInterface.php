<?php
declare(strict_types=1);

interface PaymentGatewayInterface
{
    public function key(): string;
    public function label(): string;
    public function supports(string $method): bool;
    public function assertReady(string $method): void;
    public function preparePayer(array $payer): array;

    public function createPix(
        string $customerId,
        float $value,
        string $description,
        string $reference
    ): array;

    public function pixQrCode(string $paymentId): array;

    public function createBoleto(
        string $customerId,
        float $value,
        string $description,
        string $reference,
        DateTimeImmutable $dueDate
    ): array;

    public function boletoData(string $paymentId): array;

    public function createCreditCard(
        string $customerId,
        float $value,
        string $description,
        string $reference,
        array $card,
        array $holder
    ): array;

    public function getPayment(string $paymentId): array;
}
