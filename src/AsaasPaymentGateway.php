<?php
declare(strict_types=1);

final class AsaasPaymentGateway implements PaymentGatewayInterface
{
    private AsaasService $service;

    public function __construct()
    {
        $this->service = new AsaasService();
    }

    public function key(): string
    {
        return 'Asaas';
    }

    public function label(): string
    {
        return 'Asaas';
    }

    public function supports(string $method): bool
    {
        return in_array(
            $method,
            ['PIX','Cartao','Boleto'],
            true
        );
    }

    public function assertReady(string $method): void
    {
        if (!$this->supports($method)) {
            throw new RuntimeException(
                'Forma de pagamento não suportada pelo Asaas.'
            );
        }

        $this->service->assertAccountApproved();

        if ($method === 'PIX') {
            PixAvailabilityService::assertAsaasAvailable();
        }
    }

    public function preparePayer(array $payer): array
    {
        return $this->service->getOrCreateCustomer($payer);
    }

    public function createPix(
        string $customerId,
        float $value,
        string $description,
        string $reference
    ): array {
        return $this->service->createPix(
            $customerId,$value,$description,$reference
        );
    }

    public function pixQrCode(string $paymentId): array
    {
        return $this->service->pixQrCode($paymentId);
    }

    public function createBoleto(
        string $customerId,
        float $value,
        string $description,
        string $reference,
        DateTimeImmutable $dueDate
    ): array {
        return $this->service->createBoleto(
            $customerId,$value,$description,$reference,$dueDate
        );
    }

    public function boletoData(string $paymentId): array
    {
        return $this->service->boletoLinhaDigitavel($paymentId);
    }

    public function createCreditCard(
        string $customerId,
        float $value,
        string $description,
        string $reference,
        array $card,
        array $holder
    ): array {
        return $this->service->createCreditCard(
            $customerId,
            $value,
            $description,
            $reference,
            $card,
            $holder
        );
    }

    public function getPayment(string $paymentId): array
    {
        return $this->service->getPayment($paymentId);
    }
}
