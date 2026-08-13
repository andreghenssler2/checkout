<?php

declare(strict_types=1);

final class PagBankPaymentGateway implements PaymentGatewayInterface
{
    private PagBankService $service;
    private array $payer = [];

    public function __construct()
    {
        $this->service = new PagBankService();
    }

    public function key(): string
    {
        return 'PagBank';
    }

    public function label(): string
    {
        return 'PagBank';
    }

    public function supports(string $method): bool
    {
        return in_array($method, ['PIX','Cartao','Boleto'], true);
    }

    public function assertReady(string $method): void
    {
        if (!$this->supports($method)) {
            throw new RuntimeException(
                'Forma de pagamento não suportada pelo PagBank.'
            );
        }

        if (
            !PagBankSettings::enabled()
            || trim(PagBankSettings::token()) === ''
        ) {
            throw new RuntimeException(
                'Configure e ative o PagBank antes de utilizar esta forma de pagamento.'
            );
        }

        if (
            $method === 'Cartao'
            && PagBankSettings::publicKey() === ''
        ) {
            throw new RuntimeException(
                'A chave pública do cartão PagBank ainda não foi preparada. Acesse Admin > PagBank e teste a conexão.'
            );
        }
    }

    public function preparePayer(array $payer): array
    {
        $this->payer = $payer;

        $cpf = Support::cpfDigits((string)($payer['cpf'] ?? ''));

        if ($cpf === '') {
            throw new RuntimeException('CPF do pagador não informado.');
        }

        return [
            'id' => 'PGB-CPF-' . $cpf,
        ];
    }

    public function createPix(
        string $customerId,
        float $value,
        string $description,
        string $reference
    ): array {
        return $this->service->createPix(
            $this->payer,
            $value,
            $description,
            $reference
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
            $this->payer,
            $value,
            $description,
            $reference,
            $dueDate
        );
    }

    public function boletoData(string $paymentId): array
    {
        return $this->service->boletoData($paymentId);
    }

    public function createCreditCard(
        string $customerId,
        float $value,
        string $description,
        string $reference,
        array $card,
        array $holder
    ): array {
        $encrypted = trim(
            (string)($card['encrypted'] ?? '')
        );

        if ($encrypted === '') {
            throw new RuntimeException(
                'O cartão não foi criptografado pelo PagBank. Atualize a página e tente novamente.'
            );
        }

        return $this->service->createCreditCard(
            $this->payer,
            $value,
            $description,
            $reference,
            $encrypted,
            $holder
        );
    }

    public function getPayment(string $paymentId): array
    {
        return $this->service->getOrder($paymentId);
    }
}
