<?php

declare(strict_types=1);

final class AsaasService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        if (!AsaasSettings::enabled()) throw new RuntimeException('Integração Asaas desativada.');
        $this->apiKey = AsaasSettings::apiKey();
        if ($this->apiKey === '') throw new RuntimeException('API Key do Asaas não configurada para o ambiente selecionado.');
        $this->baseUrl = AsaasSettings::activeEnvironment() === 'producao'
            ? 'https://api.asaas.com/v3'
            : 'https://api-sandbox.asaas.com/v3';
    }

    private function request(string $method, string $endpoint, array $data = [], int $timeout = 30): array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $method = strtoupper($method);
        $ch = curl_init();
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'access_token: ' . $this->apiKey,
            'User-Agent: Checkout-IECLB-Parobe/1.4.7',
        ];
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 8,
        ];
        if ($method !== 'GET' && $data !== []) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        curl_setopt_array($ch, $options);
        $body = curl_exec($ch);
        $curlError = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === false) throw new RuntimeException('Falha de comunicação com o Asaas: ' . $curlError);
        $json = json_decode((string)$body, true);
        if ($status < 200 || $status >= 300) {
            $messages = [];

            if (
                is_array($json)
                && isset($json['errors'])
                && is_array($json['errors'])
            ) {
                foreach ($json['errors'] as $error) {
                    if (!is_array($error)) {
                        continue;
                    }

                    $code = trim(
                        (string)($error['code'] ?? '')
                    );

                    $description = trim(
                        (string)(
                            $error['description']
                            ?? $error['message']
                            ?? 'Erro'
                        )
                    );

                    $messages[] = $code !== ''
                        ? '[' . $code . '] ' . $description
                        : $description;
                }
            }

            $detail = $messages
                ? implode(' ', $messages)
                : 'Resposta sem detalhes.';

            throw new RuntimeException(
                'Asaas HTTP '
                . $status
                . ' em '
                . $method
                . ' '
                . $endpoint
                . ': '
                . $detail
            );
        }
        if (!is_array($json)) throw new RuntimeException('Resposta inválida do Asaas.');
        return $json;
    }



    public static function paymentWebhookEvents(): array
    {
        return [
            'PAYMENT_CREATED',
            'PAYMENT_UPDATED',
            'PAYMENT_AWAITING_RISK_ANALYSIS',
            'PAYMENT_APPROVED_BY_RISK_ANALYSIS',
            'PAYMENT_REPROVED_BY_RISK_ANALYSIS',
            'PAYMENT_CONFIRMED',
            'PAYMENT_RECEIVED',
            'PAYMENT_CREDIT_CARD_CAPTURE_REFUSED',
            'PAYMENT_OVERDUE',
            'PAYMENT_DELETED',
            'PAYMENT_RESTORED',
            'PAYMENT_REFUNDED',
            'PAYMENT_PARTIALLY_REFUNDED',
            'PAYMENT_REFUND_IN_PROGRESS',
            'PAYMENT_CHARGEBACK_REQUESTED',
        ];
    }

    public function listWebhooks(): array
    {
        $response = $this->request(
            'GET',
            '/webhooks?offset=0&limit=100'
        );

        $data = $response['data'] ?? [];

        return is_array($data)
            ? $data
            : [];
    }

    /**
     * Cria ou atualiza o webhook do Checkout no ambiente atualmente
     * selecionado. Também reativa uma eventual fila interrompida.
     */
    public function syncPaymentWebhook(
        string $url,
        string $email,
        string $authToken
    ): array {
        $url = trim($url);
        $email = strtolower(trim($email));
        $authToken = trim($authToken);

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(
                'URL do webhook inválida.'
            );
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                'E-mail do webhook inválido.'
            );
        }

        if (
            strlen($authToken) < 32
            || strlen($authToken) > 255
            || preg_match('/\\s/', $authToken)
        ) {
            throw new InvalidArgumentException(
                'O token do webhook deve possuir entre 32 e 255 caracteres e não pode conter espaços.'
            );
        }

        $webhooks = $this->listWebhooks();
        $matches = [];

        foreach ($webhooks as $webhook) {
            if (
                is_array($webhook)
                && rtrim(
                    (string)($webhook['url'] ?? ''),
                    '/'
                ) === rtrim($url, '/')
            ) {
                $matches[] = $webhook;
            }
        }

        $environment = AsaasSettings::activeEnvironment();

        $payload = [
            'name' => 'checkout-pagamentos-' . $environment,
            'url' => $url,
            'enabled' => true,
            'interrupted' => false,
            'sendType' => 'SEQUENTIALLY',
            'authToken' => $authToken,
            'events' => self::paymentWebhookEvents(),
        ];

        if ($matches) {
            $webhook = $matches[0];
            $id = trim(
                (string)($webhook['id'] ?? '')
            );

            if ($id === '') {
                throw new RuntimeException(
                    'Webhook encontrado sem identificador.'
                );
            }

            $updated = $this->request(
                'PUT',
                '/webhooks/' . rawurlencode($id),
                $payload
            );

            $updated['_checkoutAction'] = 'updated';
            $updated['_checkoutDuplicates'] = max(
                0,
                count($matches) - 1
            );

            return $updated;
        }

        $payload['email'] = $email;

        $created = $this->request(
            'POST',
            '/webhooks',
            $payload
        );

        $created['_checkoutAction'] = 'created';
        $created['_checkoutDuplicates'] = 0;

        return $created;
    }

    public function findPaymentByExternalReference(
        string $reference
    ): ?array {
        $reference = trim($reference);

        if ($reference === '') {
            return null;
        }

        $query = http_build_query([
            'externalReference' => $reference,
            'limit' => 10,
            'offset' => 0,
        ]);

        $response = $this->request(
            'GET',
            '/payments?' . $query
        );

        $data = $response['data'] ?? [];

        if (!is_array($data)) {
            return null;
        }

        foreach ($data as $payment) {
            if (
                is_array($payment)
                && trim(
                    (string)($payment['externalReference'] ?? '')
                ) === $reference
                && empty($payment['deleted'])
            ) {
                return $payment;
            }
        }

        return null;
    }

    public function findCustomer(string $cpf): ?array
    {
        $query = http_build_query(['cpfCnpj' => Support::cpfDigits($cpf), 'limit' => 1]);
        $r = $this->request('GET', '/customers?' . $query);
        $data = $r['data'] ?? [];
        return is_array($data) && isset($data[0]) && is_array($data[0]) ? $data[0] : null;
    }

    public function getOrCreateCustomer(array $payer): array
    {
        $existing = $this->findCustomer((string)$payer['cpf']);

        if ($existing) {
            $customerId = trim((string)($existing['id'] ?? ''));

            if ($customerId === '') {
                throw new RuntimeException(
                    'Cliente Asaas encontrado sem identificador.'
                );
            }

            /*
             * O Checkout controla toda a comunicação com o pagador.
             * Portanto, antes de qualquer nova cobrança, garantimos
             * que as notificações padrão do Asaas estejam desabilitadas.
             */
            return $this->disableCustomerNotifications($customerId);
        }

        return $this->request('POST', '/customers', [
            'name' => trim((string)$payer['nome']),
            'cpfCnpj' => Support::cpfDigits((string)$payer['cpf']),
            'email' => trim((string)$payer['email']),
            'mobilePhone' => Support::phoneDigits((string)$payer['telefone']),
            'externalReference' => 'checkout-' . Support::cpfDigits((string)$payer['cpf']),
            'notificationDisabled' => true,
        ]);
    }

    public function disableCustomerNotifications(string $customerId): array
    {
        $customerId = trim($customerId);

        if ($customerId === '') {
            throw new InvalidArgumentException(
                'ID do cliente Asaas não informado.'
            );
        }

        return $this->request(
            'PUT',
            '/customers/' . rawurlencode($customerId),
            [
                'notificationDisabled' => true,
            ]
        );
    }



    public function getAccountStatus(): array
    {
        return $this->request(
            'GET',
            '/myAccount/status/'
        );
    }

    public function assertAccountApproved(): void
    {
        $status = $this->getAccountStatus();
        $general = strtoupper(
            trim((string)($status['general'] ?? ''))
        );

        if ($general !== 'APPROVED') {
            $commercial = strtoupper(
                trim((string)($status['commercialInfo'] ?? ''))
            );

            $documentation = strtoupper(
                trim((string)($status['documentation'] ?? ''))
            );

            throw new RuntimeException(
                'A conta Asaas do ambiente atual ainda não está 100% aprovada. '
                . 'Geral: '
                . ($general !== '' ? $general : 'não informado')
                . '; Dados comerciais: '
                . ($commercial !== '' ? $commercial : 'não informado')
                . '; Documentação: '
                . ($documentation !== '' ? $documentation : 'não informado')
                . '.'
            );
        }
    }

    public function approveSandboxAccount(): array
    {
        if (AsaasSettings::activeEnvironment() !== 'sandbox') {
            throw new RuntimeException(
                'A aprovação automática por esta rota só está disponível no Sandbox.'
            );
        }

        return $this->request(
            'POST',
            '/sandbox/myAccount/approve'
        );
    }

    public function listPixKeys(?string $status = null): array
    {
        $query = [
            'limit' => 100,
            'offset' => 0,
        ];

        if ($status !== null && trim($status) !== '') {
            $query['status'] = trim($status);
        }

        return $this->request(
            'GET',
            '/pix/addressKeys?' . http_build_query($query)
        );
    }

    public function listActivePixKeys(): array
    {
        $response = $this->listPixKeys('ACTIVE');
        $data = $response['data'] ?? [];

        if (!is_array($data)) {
            return [];
        }

        return array_values(
            array_filter(
                $data,
                static fn ($item): bool =>
                    is_array($item)
                    && strtoupper((string)($item['status'] ?? '')) === 'ACTIVE'
            )
        );
    }

    public function createRandomPixKey(): array
    {
        return $this->request(
            'POST',
            '/pix/addressKeys',
            [
                'type' => 'EVP',
            ]
        );
    }

    public function createPix(
        string $customerId,
        float $value,
        string $description,
        string $reference
    ): array {
        return $this->request(
            'POST',
            '/payments',
            [
                'customer' => $customerId,
                'billingType' => 'PIX',
                'value' => round($value, 2),
                'dueDate' => date('Y-m-d'),
                'description' => mb_substr(
                    $description,
                    0,
                    500
                ),
                'externalReference' => $reference,
            ]
        );
    }


    public static function isPixReceivingDisabledError(
        Throwable $e
    ): bool {
        $message = mb_strtolower(
            $e->getMessage(),
            'UTF-8'
        );

        return str_contains(
            $message,
            'pix.receivingwithpixdisabled'
        )
        || str_contains(
            $message,
            'recebimentos via pix desabilitado'
        );
    }

    public function pixQrCode(string $paymentId): array
    {
        return $this->request('GET', '/payments/' . rawurlencode($paymentId) . '/pixQrCode');
    }


    public function createBoleto(
        string $customerId,
        float $value,
        string $description,
        string $reference,
        DateTimeImmutable $dueDate
    ): array {
        return $this->request('POST', '/payments', [
            'customer' => $customerId,
            'billingType' => 'BOLETO',
            'value' => round($value, 2),
            'dueDate' => $dueDate->format('Y-m-d'),
            'description' => mb_substr($description, 0, 500),
            'externalReference' => $reference,
        ]);
    }

    public function boletoLinhaDigitavel(string $paymentId): array
    {
        return $this->request(
            'GET',
            '/payments/' . rawurlencode($paymentId) . '/identificationField'
        );
    }

    public function createCreditCard(string $customerId, float $value, string $description, string $reference, array $card, array $holder): array
    {
        $payload = [
            'customer' => $customerId,
            'billingType' => 'CREDIT_CARD',
            'value' => $value,
            'dueDate' => date('Y-m-d'),
            'description' => mb_substr($description, 0, 500),
            'externalReference' => $reference,
            'creditCard' => [
                'holderName' => trim((string)$card['holderName']),
                'number' => preg_replace('/\D+/', '', (string)$card['number']),
                'expiryMonth' => str_pad((string)(int)$card['expiryMonth'], 2, '0', STR_PAD_LEFT),
                'expiryYear' => (string)$card['expiryYear'],
                'ccv' => preg_replace('/\D+/', '', (string)$card['ccv']),
            ],
            'creditCardHolderInfo' => [
                'name' => trim((string)$holder['name']),
                'email' => trim((string)$holder['email']),
                'cpfCnpj' => Support::cpfDigits((string)$holder['cpfCnpj']),
                'postalCode' => preg_replace('/\D+/', '', (string)$holder['postalCode']),
                'addressNumber' => trim((string)$holder['addressNumber']),
                'addressComplement' => trim((string)($holder['addressComplement'] ?? '')) ?: null,
                'mobilePhone' => Support::phoneDigits((string)$holder['mobilePhone']),
            ],
            'remoteIp' => Support::clientIp(),
        ];
        return $this->request('POST', '/payments', $payload, 65);
    }

    public function getPayment(string $paymentId): array
    {
        return $this->request('GET', '/payments/' . rawurlencode($paymentId));
    }
}
