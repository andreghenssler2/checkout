<?php
require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Support::redirect('/');
}

$id = (int)($_POST['idOferta'] ?? 0);
$offer = OfertaRepository::find($id);

if (!$offer || empty($offer['ativo'])) {
    die('Oferta indisponível.');
}

try {
    if (!Support::checkCsrf($_POST['_csrf'] ?? null)) {
        throw new RuntimeException(
            'Sessão expirada. Atualize a página e tente novamente.'
        );
    }

    /*
     * Ofertas podem receber doações antes da data_inicio.
     * A data inicial serve para organização/calendário no index.
     * O recebimento só é bloqueado quando a Oferta estiver inativa
     * ou quando data_fim já tiver sido atingida.
     */
    if (
        !empty($offer['data_fim'])
        && strtotime((string)$offer['data_fim']) <= time()
    ) {
        throw new RuntimeException(
            'Esta campanha já encerrou o período de recebimento.'
        );
    }

    if (!empty($_POST['website'])) {
        throw new RuntimeException('Requisição inválida.');
    }

    $nome = trim((string)($_POST['nome'] ?? ''));
    $cpf = (string)($_POST['cpf'] ?? '');
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $telefone = trim((string)($_POST['telefone'] ?? ''));

    if (
        $nome === ''
        || !Support::validCpf($cpf)
        || !filter_var($email, FILTER_VALIDATE_EMAIL)
        || strlen(Support::phoneDigits($telefone)) < 10
    ) {
        throw new RuntimeException(
            'Confira nome, CPF, e-mail e telefone.'
        );
    }

    $fixed = Support::decimal($_POST['valor_escolhido'] ?? 0);
    $free = Support::decimal($_POST['valor_livre'] ?? 0);
    $value = $free > 0 ? $free : $fixed;
    $min = max(APP_MIN_OFFER, (float)$offer['valor_minimo']);

    if ($value < $min) {
        throw new RuntimeException(
            'O valor mínimo desta oferta é '
            . Support::money($min)
            . '.'
        );
    }

    if ($free <= 0) {
        $allowed = array_map(
            fn($v) => round((float)$v['valor'], 2),
            OfertaRepository::values($id)
        );

        if (!in_array(round($value, 2), $allowed, true)) {
            throw new RuntimeException('Selecione um valor permitido.');
        }
    } elseif (empty($offer['permitir_valor_livre'])) {
        throw new RuntimeException(
            'Esta oferta não permite valor livre.'
        );
    }

    $method = (string)($_POST['formaPagamento'] ?? '');

    if ($method === 'PIX' && empty($offer['pix_ativo'])) {
        throw new RuntimeException('PIX não está habilitado.');
    }

    if ($method === 'Cartao' && empty($offer['cartao_ativo'])) {
        throw new RuntimeException('Cartão não está habilitado.');
    }

    $vencimentoBoleto = null;

    if ($method === 'Boleto') {
        /*
         * A validação é feita novamente no servidor para impedir que
         * alguém force Boleto alterando manualmente o formulário.
         */
        $vencimentoBoleto = BoletoOfertaService::validarGeracao($offer);
    }

    if (!in_array($method, ['PIX', 'Cartao', 'Boleto'], true)) {
        throw new RuntimeException('Forma de pagamento inválida.');
    }

    RateLimiter::hit(
        'checkout_ip',
        Support::clientIp(),
        20,
        15
    );

    if ($method === 'Cartao') {
        RateLimiter::hit(
            'cartao_ip',
            Support::clientIp(),
            8,
            15
        );

        RateLimiter::hit(
            'cartao_cpf',
            Support::cpfDigits($cpf),
            5,
            30
        );
    }

    $donor = DoadorRepository::upsert([
        'nome' => $nome,
        'cpf' => $cpf,
        'email' => $email,
        'telefone' => $telefone,
    ]);

    $gateway = PaymentGatewayManager::forMethod($method);
    $gateway->assertReady($method);

    $payerPayload = [
        'nome' => $nome,
        'cpf' => $cpf,
        'email' => $email,
        'telefone' => $telefone,
    ];

    if (
        $method === 'Boleto'
        && $gateway->key() === 'PagBank'
    ) {
        $payerPayload['endereco'] = [
            'cep' => $_POST['pagbank_cep'] ?? '',
            'logradouro' => $_POST['pagbank_logradouro'] ?? '',
            'numero' => $_POST['pagbank_numero'] ?? '',
            'complemento' => $_POST['pagbank_complemento'] ?? '',
            'bairro' => $_POST['pagbank_bairro'] ?? '',
            'cidade' => $_POST['pagbank_cidade'] ?? '',
            'estado' => $_POST['pagbank_estado'] ?? '',
        ];
    }

    $customer = $gateway->preparePayer(
        $payerPayload
    );

    $customerId = trim((string)($customer['id'] ?? ''));

    if ($customerId === '') {
        throw new RuntimeException(
            'O provedor de pagamento não retornou a identificação do pagador.'
        );
    }

    DoadorGatewayRepository::set(
        (int)$donor['idDoador'],
        $gateway->key(),
        $customerId
    );

    if ($gateway->key() === 'Asaas') {
        DoadorRepository::setAsaas(
            (int)$donor['idDoador'],
            $customerId
        );
    }

    $code = Support::randomCode('OFR-');

    $paymentId = PagamentoRepository::create(
        $id,
        (int)$donor['idDoador'],
        $code,
        $value,
        $method,
        $gateway->key()
    );

    $description = 'Oferta - ' . (string)$offer['titulo'];

    try {
        if ($method === 'PIX') {
            /*
             * Etapa 1: cria e SALVA a cobrança.
             * Etapa 2: tenta recuperar o QR Code.
             *
             * Se a etapa 2 falhar, a cobrança continua existindo e
             * permanece Pendente. Não deve ser marcada como Recusada.
             */
            $response = $gateway->createPix(
                $customerId,
                $value,
                $description,
                $code
            );

            PagamentoRepository::setGateway(
                $paymentId,
                $gateway->key(),
                $response
            );

            try {
                $qr = $gateway->pixQrCode(
                    (string)$response['id']
                );

                PagamentoRepository::setPixData(
                    $paymentId,
                    $qr
                );
            } catch (Throwable $qrError) {
                if (
                    $gateway->key() === 'Asaas'
                    && AsaasSettings::activeEnvironment() === 'sandbox'
                    && AsaasService::isPixReceivingDisabledError($qrError)
                ) {
                    PagamentoRepository::warning(
                        $paymentId,
                        '[SANDBOX_PIX_DISABLED] Cobrança criada no Asaas. '
                        . 'O recebimento via Pix está desabilitado nesta conta Sandbox. '
                        . 'Confirme manualmente a cobrança no painel do Asaas Sandbox para testar o webhook.'
                    );
                } else {
                    PagamentoRepository::warning(
                        $paymentId,
                        'Cobrança criada no '
                        . $gateway->label()
                        . ', mas o QR Code Pix não pôde ser carregado: '
                        . $qrError->getMessage()
                    );
                }

                error_log(
                    'PIX criado sem QR Code ['
                    . (string)$response['id']
                    . ']: '
                    . $qrError->getMessage()
                );
            }
        } elseif ($method === 'Boleto') {
            if (!$vencimentoBoleto instanceof DateTimeImmutable) {
                throw new RuntimeException(
                    'Não foi possível definir o vencimento do boleto.'
                );
            }

            $response = $gateway->createBoleto(
                $customerId,
                $value,
                $description,
                $code,
                $vencimentoBoleto
            );

            /*
             * A cobrança já existe no provedor neste ponto.
             * Uma eventual falha ao recuperar a linha digitável não deve
             * transformar a cobrança válida em recusada.
             */
            $linha = [];

            try {
                $linha = $gateway->boletoData(
                    (string)$response['id']
                );
            } catch (Throwable $lineError) {
                error_log(
                    'Boleto criado, mas linha digitável não recuperada: '
                    . $lineError->getMessage()
                );
            }

            PagamentoRepository::setGateway(
                $paymentId,
                $gateway->key(),
                $response,
                $linha
            );
} else {
    if ($gateway->key() === 'PagBank') {
        $card = [
            'holderName' =>
                $_POST['card_holder'] ?? '',
            'encrypted' =>
                $_POST['pagbank_encrypted_card']
                ?? '',
        ];

        $holder = [
            'name' =>
                $_POST['card_holder']
                ?? $nome,
            'email' => $email,
            'cpfCnpj' =>
                $_POST['holder_cpf']
                ?? $cpf,
            'mobilePhone' => $telefone,
        ];

        if (
            trim(
                (string)$card['holderName']
            ) === ''
            || strlen(
                trim(
                    (string)$card['encrypted']
                )
            ) < 80
            || !Support::validCpf(
                (string)$holder['cpfCnpj']
            )
        ) {
            throw new RuntimeException(
                'Confira os dados do cartão. O cartão PagBank precisa ser criptografado novamente antes do envio.'
            );
        }
    } else {
        $card = [
            'holderName' =>
                $_POST['card_holder'] ?? '',
            'number' =>
                $_POST['card_number'] ?? '',
            'expiryMonth' =>
                $_POST['card_month'] ?? '',
            'expiryYear' =>
                $_POST['card_year'] ?? '',
            'ccv' =>
                $_POST['card_ccv'] ?? '',
        ];

        $holder = [
            'name' => $nome,
            'email' => $email,
            'cpfCnpj' =>
                $_POST['holder_cpf']
                ?? $cpf,
            'postalCode' =>
                $_POST['holder_cep']
                ?? '',
            'addressNumber' =>
                $_POST['holder_numero']
                ?? '',
            'addressComplement' =>
                $_POST['holder_complemento']
                ?? '',
            'mobilePhone' => $telefone,
        ];

        if (
            trim(
                (string)$card['holderName']
            ) === ''
            || strlen(
                preg_replace(
                    '/\D+/',
                    '',
                    (string)$card['number']
                )
            ) < 13
            || !in_array(
                strlen(
                    preg_replace(
                        '/\D+/',
                        '',
                        (string)$card['ccv']
                    )
                ),
                [3, 4],
                true
            )
            || !Support::validCpf(
                (string)$holder['cpfCnpj']
            )
            || strlen(
                preg_replace(
                    '/\D+/',
                    '',
                    (string)$holder['postalCode']
                )
            ) !== 8
            || trim(
                (string)$holder['addressNumber']
            ) === ''
        ) {
            throw new RuntimeException(
                'Preencha corretamente os dados do cartão e do titular.'
            );
        }
    }

            $response = $gateway->createCreditCard(
                $customerId,
                $value,
                $description,
                $code,
                $card,
                $holder
            );

            PagamentoRepository::setGateway(
                $paymentId,
                $gateway->key(),
                $response
            );
        }
    } catch (Throwable $e) {
        PagamentoRepository::fail(
            $paymentId,
            $e->getMessage()
        );

        throw $e;
    }

    NotificationService::paymentCreated($paymentId);

    Support::redirect('/pagamento/' . $code);
} catch (Throwable $e) {
    $_SESSION['_checkout_error'] = $e->getMessage();

    Support::redirect(
        '/oferta/' . (string)($offer['slug'] ?? '')
    );
}
