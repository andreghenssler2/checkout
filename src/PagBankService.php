<?php

declare(strict_types=1);

final class PagBankService
{
    private string $baseUrl;
    private string $token;
    private string $environment;

    public function __construct()
    {
        if (!PagBankSettings::enabled()) {
            throw new RuntimeException(
                'Integração PagBank desativada.'
            );
        }

        $this->environment = PagBankSettings::activeEnvironment();
        $this->token = PagBankSettings::token();

        if ($this->token === '') {
            throw new RuntimeException(
                'Token PagBank não configurado para o ambiente selecionado.'
            );
        }

        $this->baseUrl = $this->environment === 'producao'
            ? 'https://api.pagseguro.com'
            : 'https://sandbox.api.pagseguro.com';
    }

    public function environment(): string
    {
        return $this->environment;
    }

    public function createPix(
        array $payer,
        float $value,
        string $description,
        string $reference
    ): array {
        $cents = self::toCents($value);

        $expires = (new DateTimeImmutable(
            'now',
            new DateTimeZone(APP_TIMEZONE)
        ))
            ->modify('+1 hour')
            ->format('Y-m-d\TH:i:sP');

        $payload = [
            'reference_id' => self::reference($reference),
            'customer' => self::customer($payer),
            'items' => [
                [
                    'reference_id' => self::reference($reference),
                    'name' => self::text($description, 190),
                    'quantity' => 1,
                    'unit_amount' => $cents,
                ],
            ],
            'qr_codes' => [
                [
                    'amount' => [
                        'value' => $cents,
                    ],
                    'expiration_date' => $expires,
                ],
            ],
            'notification_urls' => [
                PagBankSettings::webhookUrl(),
            ],
        ];

        return $this->request(
            'POST',
            '/orders',
            $payload,
            self::idempotency($reference)
        );
    }

    public function pixQrCode(string $orderId): array
    {
        $order = $this->getOrder($orderId);
        $encoded = null;

        $codes = $order['qr_codes'] ?? $order['qr_code'] ?? [];
        $first = is_array($codes) ? ($codes[0] ?? []) : [];

        if (is_array($first)) {
            foreach (($first['links'] ?? []) as $link) {
                if (!is_array($link)) {
                    continue;
                }

                if (
                    strtoupper(trim((string)($link['rel'] ?? '')))
                    !== 'QRCODE.BASE64'
                ) {
                    continue;
                }

                $url = trim((string)($link['href'] ?? ''));

                if ($url === '') {
                    continue;
                }

                try {
                    $encoded = $this->requestTextUrl($url);
                    $encoded = preg_replace(
                        '#^data:image/[^;]+;base64,#i',
                        '',
                        trim($encoded)
                    ) ?: trim($encoded);
                } catch (Throwable) {
                    $encoded = null;
                }

                break;
            }
        }

        return PagBankPaymentMapper::pixData(
            $order,
            $encoded
        );
    }

    public function createBoleto(
        array $payer,
        float $value,
        string $description,
        string $reference,
        DateTimeImmutable $dueDate
    ): array {
        $address = self::address($payer);

        if ($address === null) {
            throw new RuntimeException(
                'O PagBank exige endereço completo para emissão do boleto.'
            );
        }

        $cents = self::toCents($value);
        $customer = self::customer($payer);
        $due = $dueDate->format('Y-m-d');

        $payload = [
            'reference_id' => self::reference($reference),
            'customer' => $customer,
            'items' => [
                [
                    'reference_id' => self::reference($reference),
                    'name' => self::text($description, 190),
                    'quantity' => 1,
                    'unit_amount' => $cents,
                ],
            ],
            'shipping' => [
                'address' => $address,
            ],
            'charges' => [
                [
                    'reference_id' => self::reference($reference),
                    'description' => self::text($description, 60),
                    'amount' => [
                        'value' => $cents,
                        'currency' => 'BRL',
                    ],
                    'payment_method' => [
                        'type' => 'BOLETO',
                        'boleto' => [
                            'template' => 'proposta',
                            'due_date' => $due,
                            'days_until_expiration' => '30',
                            'holder' => [
                                'name' => $customer['name'],
                                'tax_id' => $customer['tax_id'],
                                'email' => $customer['email'],
                                'address' => [
                                    'street' => $address['street'],
                                    'number' => $address['number'],
                                    'postal_code' => $address['postal_code'],
                                    'locality' => $address['locality'],
                                    'city' => $address['city'],
                                    'region' => $address['region_code'],
                                    'region_code' => $address['region_code'],
                                    'country' => 'Brasil',
                                ],
                            ],
                            'instruction_lines' => [
                                'line_1' => 'Pagamento até a data de vencimento',
                                'line_2' => 'Checkout IECLB Parobé',
                            ],
                        ],
                    ],
                ],
            ],
            'notification_urls' => [
                PagBankSettings::webhookUrl(),
            ],
        ];

        return $this->request(
            'POST',
            '/orders',
            $payload,
            self::idempotency($reference)
        );
    }

    public function boletoData(string $orderId): array
    {
        return PagBankPaymentMapper::boletoData(
            $this->getOrder($orderId)
        );
    }

public function createCreditCard(
    array $payer,
    float $value,
    string $description,
    string $reference,
    string $encryptedCard,
    array $holder
): array {
    $encryptedCard = trim($encryptedCard);

    if (
        strlen($encryptedCard) < 80
        || strlen($encryptedCard) > 6000
    ) {
        throw new RuntimeException(
            'O cartão criptografado pelo PagBank é inválido. Atualize a página e tente novamente.'
        );
    }

    $holderName = trim(
        (string)($holder['name'] ?? '')
    );

    $holderTaxId = Support::cpfDigits(
        (string)($holder['cpfCnpj'] ?? '')
    );

    if (
        $holderName === ''
        || !Support::validCpf($holderTaxId)
    ) {
        throw new RuntimeException(
            'Confira o nome e o CPF do titular do cartão.'
        );
    }

    $cents = self::toCents($value);

    $payload = [
        'reference_id' => self::reference($reference),
        'customer' => self::customer($payer),
        'items' => [
            [
                'reference_id' =>
                    self::reference($reference),
                'name' =>
                    self::text($description, 190),
                'quantity' => 1,
                'unit_amount' => $cents,
            ],
        ],
        'notification_urls' => [
            PagBankSettings::webhookUrl(),
        ],
        'charges' => [
            [
                'reference_id' =>
                    self::reference($reference),
                'description' =>
                    self::text($description, 60),
                'amount' => [
                    'value' => $cents,
                    'currency' => 'BRL',
                ],
                'payment_method' => [
                    'type' => 'CREDIT_CARD',
                    'installments' => 1,
                    'capture' => true,
                    'card' => [
                        'encrypted' => $encryptedCard,
                        'store' => false,
                    ],
                    'holder' => [
                        'name' =>
                            mb_substr(
                                $holderName,
                                0,
                                30
                            ),
                        'tax_id' => $holderTaxId,
                    ],
                ],
            ],
        ],
    ];

    return $this->request(
        'POST',
        '/orders',
        $payload,
        self::idempotency($reference)
    );
}

    public function getOrder(string $orderId): array
    {
        $orderId = trim($orderId);

        if ($orderId === '') {
            throw new InvalidArgumentException(
                'ID do pedido PagBank não informado.'
            );
        }

        return $this->request(
            'GET',
            '/orders/' . rawurlencode($orderId)
        );
    }

    public function getCardPublicKey(): array
    {
        return $this->request(
            'GET',
            '/public-keys/card'
        );
    }

    public function createCardPublicKey(): array
    {
        return $this->request(
            'POST',
            '/public-keys',
            [
                'type' => 'card',
            ],
            self::idempotency(
                'public-key-card-' . $this->environment
            )
        );
    }

    public function ensureCardPublicKey(): string
    {
        /*
         * No Sandbox o PagBank utiliza uma chave pública padrão.
         * Ainda consultamos a API para validar o token, mas o Checkout
         * usa a chave oficial documentada para criptografar o cartão.
         */
        if ($this->environment === 'sandbox') {
            try {
                $this->getCardPublicKey();
            } catch (PagBankApiException $e) {
                if (!in_array($e->httpStatus(), [400,404], true)) {
                    throw $e;
                }

                $this->createCardPublicKey();
            }

            $key = PagBankSettings::sandboxPublicKey();

            PagBankSettings::savePublicKey(
                'sandbox',
                $key
            );

            return $key;
        }

        try {
            $response = $this->getCardPublicKey();
        } catch (PagBankApiException $e) {
            if (!in_array($e->httpStatus(), [400,404], true)) {
                throw $e;
            }

            $response = $this->createCardPublicKey();
        }

        $key = self::findPublicKey($response);

        if ($key === '') {
            throw new RuntimeException(
                'O PagBank respondeu, mas não retornou a chave pública do cartão.'
            );
        }

        PagBankSettings::savePublicKey(
            'producao',
            $key
        );

        return $key;
    }

    private function request(
        string $method,
        string $endpoint,
        array $data = [],
        ?string $idempotencyKey = null,
        int $timeout = 35
    ): array {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $method = strtoupper($method);

        $headers = [
            'Authorization: Bearer ' . $this->token,
            'Accept: application/json',
            'Content-Type: application/json',
            'User-Agent: Checkout-IECLB-Parobe/1.8.1',
        ];

        if ($idempotencyKey !== null && trim($idempotencyKey) !== '') {
            $headers[] = 'x-idempotency-key: ' . trim($idempotencyKey);
        }

        $ch = curl_init();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 8,
        ];

        if ($method !== 'GET' && $data !== []) {
            $options[CURLOPT_POSTFIELDS] = json_encode(
                $data,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        }

        curl_setopt_array($ch, $options);

        $body = curl_exec($ch);
        $curlError = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($body === false) {
            throw new RuntimeException(
                'Falha de comunicação com o PagBank: ' . $curlError
            );
        }

        $json = json_decode((string)$body, true);

        if ($status < 200 || $status >= 300) {
            $response = is_array($json) ? $json : [];

            throw new PagBankApiException(
                'PagBank HTTP '
                . $status
                . ' em '
                . $method
                . ' '
                . $endpoint
                . ': '
                . self::errorMessage($response, (string)$body),
                $status,
                $response
            );
        }

        if (!is_array($json)) {
            throw new RuntimeException(
                'Resposta inválida do PagBank.'
            );
        }

        return $json;
    }

    private function requestTextUrl(
        string $url,
        int $timeout = 25
    ): string {
        $parts = parse_url($url);
        $host = strtolower((string)($parts['host'] ?? ''));
        $scheme = strtolower((string)($parts['scheme'] ?? ''));

        if (
            $scheme !== 'https'
            || $host === ''
            || !(
                $host === 'pagseguro.com'
                || str_ends_with($host, '.pagseguro.com')
            )
        ) {
            throw new RuntimeException(
                'URL de QR Code PagBank inválida.'
            );
        }

        $ch = curl_init();

        curl_setopt_array(
            $ch,
            [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->token,
                    'Accept: text/plain',
                    'User-Agent: Checkout-IECLB-Parobe/1.8.1',
                ],
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => 8,
            ]
        );

        $body = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($body === false) {
            throw new RuntimeException(
                'Não foi possível obter a imagem do QR Code PagBank: '
                . $error
            );
        }

        if ($status < 200 || $status >= 300) {
            throw new RuntimeException(
                'O PagBank retornou HTTP '
                . $status
                . ' ao carregar o QR Code.'
            );
        }

        return trim((string)$body);
    }

    private static function customer(array $payer): array
    {
        $name = trim((string)($payer['nome'] ?? ''));
        $email = strtolower(trim((string)($payer['email'] ?? '')));
        $taxId = Support::cpfDigits((string)($payer['cpf'] ?? ''));
        $phone = Support::phoneDigits((string)($payer['telefone'] ?? ''));

        if (
            str_starts_with($phone, '55')
            && strlen($phone) >= 12
        ) {
            $phone = substr($phone, 2);
        }

        $area = substr($phone, 0, 2);
        $number = substr($phone, 2);

        if (
            $name === ''
            || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || strlen($taxId) !== 11
            || strlen($area) !== 2
            || !in_array(strlen($number), [8,9], true)
        ) {
            throw new RuntimeException(
                'Dados do pagador incompatíveis com a API PagBank.'
            );
        }

        return [
            'name' => self::text($name, 120),
            'email' => $email,
            'tax_id' => $taxId,
            'phones' => [
                [
                    'country' => '55',
                    'area' => $area,
                    'number' => $number,
                    'type' => 'MOBILE',
                ],
            ],
        ];
    }

    private static function address(array $payer): ?array
    {
        $input = $payer['endereco'] ?? null;

        if (!is_array($input)) {
            return null;
        }

        $postalCode = preg_replace(
            '/\D+/',
            '',
            (string)($input['cep'] ?? '')
        ) ?? '';

        $street = trim((string)($input['logradouro'] ?? ''));
        $number = trim((string)($input['numero'] ?? ''));
        $locality = trim((string)($input['bairro'] ?? ''));
        $city = trim((string)($input['cidade'] ?? ''));
        $region = strtoupper(trim((string)($input['estado'] ?? '')));

        if (
            strlen($postalCode) !== 8
            || $street === ''
            || $number === ''
            || $locality === ''
            || $city === ''
            || !preg_match('/^[A-Z]{2}$/', $region)
        ) {
            throw new RuntimeException(
                'Preencha CEP, logradouro, número, bairro, cidade e estado para o boleto PagBank.'
            );
        }

        $address = [
            'street' => self::text($street, 150),
            'number' => self::text($number, 20),
            'locality' => self::text($locality, 60),
            'city' => self::text($city, 90),
            'region_code' => $region,
            'country' => 'BRA',
            'postal_code' => $postalCode,
        ];

        $complement = trim((string)($input['complemento'] ?? ''));

        if ($complement !== '') {
            $address['complement'] = self::text($complement, 40);
        }

        return $address;
    }

    private static function toCents(float $value): int
    {
        $cents = (int)round($value * 100);

        if ($cents <= 0) {
            throw new InvalidArgumentException('Valor PagBank inválido.');
        }

        return $cents;
    }

    private static function reference(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException(
                'Referência PagBank não informada.'
            );
        }

        return mb_substr($value, 0, 64);
    }

    private static function idempotency(string $reference): string
    {
        return hash(
            'sha256',
            'checkout-ieclb-' . trim($reference)
        );
    }

    private static function text(string $value, int $limit): string
    {
        $value = trim($value);

        if ($value === '') {
            return 'Checkout IECLB Parobé';
        }

        return mb_substr($value, 0, $limit);
    }

    private static function findPublicKey(mixed $value): string
    {
        if (is_string($value)) {
            $value = trim($value);

            if (
                $value !== ''
                && str_starts_with(strtoupper($value), 'PUB')
            ) {
                return $value;
            }

            return '';
        }

        if (!is_array($value)) {
            return '';
        }

        foreach (['public_key','publicKey','key'] as $field) {
            if (
                isset($value[$field])
                && is_string($value[$field])
                && trim($value[$field]) !== ''
            ) {
                return trim($value[$field]);
            }
        }

        foreach ($value as $item) {
            $found = self::findPublicKey($item);

            if ($found !== '') {
                return $found;
            }
        }

        return '';
    }

    private static function errorMessage(
        array $json,
        string $body
    ): string {
        $rawText = strtolower(
            json_encode(
                $json,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            )
            . ' '
            . $body
        );

        /*
         * Regra de negócio do PagBank:
         * o e-mail informado em customer.email não pode ser o mesmo
         * e-mail da conta vendedora.
         *
         * O PagBank normalmente retorna:
         * [40002] buyer email must not be equals to merchant email
         */
        if (
            str_contains(
                $rawText,
                'charges[0].payment_method.card.encrypted'
            )
            && str_contains(
                $rawText,
                'invalid_parameter'
            )
        ) {
            return 'O PagBank rejeitou o cartão criptografado. '
                . 'No Sandbox, use somente um cartão de teste do PagBank e a chave pública do próprio Sandbox. '
                . 'Se o token ou o ambiente foram alterados, acesse Admin > PagBank e clique em Testar conexão / preparar chave pública antes de tentar novamente.';
        }

        if (
            str_contains(
                $rawText,
                'buyer email must not be equals to merchant email'
            )
            || (
                str_contains($rawText, 'buyer email')
                && str_contains($rawText, 'merchant email')
            )
        ) {
            return 'O e-mail do pagador não pode ser o mesmo e-mail cadastrado na conta PagBank. '
                . 'Informe outro e-mail para o comprador e tente novamente.';
        }

        $messages = [];

        foreach (['error_messages','errors'] as $field) {
            $items = $json[$field] ?? null;

            if (!is_array($items)) {
                continue;
            }

            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $code = trim((string)($item['code'] ?? ''));
                $description = trim(
                    (string)(
                        $item['description']
                        ?? $item['message']
                        ?? ''
                    )
                );

                $parameter = trim(
                    (string)(
                        $item['parameter_name']
                        ?? $item['parameter']
                        ?? ''
                    )
                );

                if ($description !== '') {
                    $message = $code !== ''
                        ? '[' . $code . '] ' . $description
                        : $description;

                    if ($parameter !== '') {
                        $message .= ' — campo: ' . $parameter;
                    }

                    $messages[] = $message;
                }
            }
        }

        if (!$messages) {
            $description = trim(
                (string)(
                    $json['message']
                    ?? $json['error_description']
                    ?? ''
                )
            );

            if ($description !== '') {
                $messages[] = $description;
            }
        }

        if ($messages) {
            return implode(' ', $messages);
        }

        $body = trim($body);

        return $body !== ''
            ? mb_substr($body, 0, 500)
            : 'Resposta sem detalhes.';
    }
}
