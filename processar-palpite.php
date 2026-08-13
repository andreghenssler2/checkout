<?php
require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Support::redirect('/');
}

$id = (int)($_POST['idEventoPalpite'] ?? 0);
$event = PalpiteRepository::find($id);

if (!$event || empty($event['ativo'])) {
    die('Palpite indisponível.');
}

try {
    if (PalpiteRepository::isPastGame($event)) {
        throw new RuntimeException(
            'Esta partida já começou ou foi realizada. Novos palpites não são mais aceitos.'
        );
    }

    if (!Support::checkCsrf($_POST['_csrf'] ?? null)) {
        throw new RuntimeException('Sessão expirada. Atualize a página e tente novamente.');
    }

    if (
        (!empty($event['data_inicio']) && strtotime($event['data_inicio']) > time())
        || (!empty($event['data_fim']) && strtotime($event['data_fim']) < time())
    ) {
        throw new RuntimeException('Este formulário está fora do período de participação.');
    }

    if (!empty($_POST['website'])) {
        throw new RuntimeException('Requisição inválida.');
    }

    $selectedOption = trim((string)($_POST['palpite_opcao'] ?? ''));
    $optionId = null;
    $prediction = '';

    if ($selectedOption === 'outro') {
        if (empty($event['permitir_outro_palpite'])) {
            throw new RuntimeException('A opção de outro palpite não está habilitada.');
        }

        $prediction = trim((string)($_POST['palpite_outro'] ?? ''));
        if ($prediction === '') {
            throw new RuntimeException('Digite o seu palpite.');
        }
        $prediction = mb_substr($prediction, 0, 160);
    } else {
        $optionId = (int)$selectedOption;
        $option = PalpiteRepository::option($id, $optionId);

        if (!$option) {
            throw new RuntimeException('Selecione uma opção de palpite válida.');
        }

        $prediction = (string)$option['rotulo'];
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
        throw new RuntimeException('Confira nome, CPF, e-mail e telefone.');
    }

    $fixed = Support::decimal($_POST['valor_escolhido'] ?? 0);
    $free = Support::decimal($_POST['valor_livre'] ?? 0);
    $value = $free > 0 ? $free : $fixed;
    $min = max(APP_MIN_OFFER, (float)$event['valor_minimo']);

    if ($value < $min) {
        throw new RuntimeException(
            'O valor mínimo deste palpite é ' . Support::money($min) . '.'
        );
    }

    if ($free <= 0) {
        $allowed = array_map(
            fn($v) => round((float)$v['valor'], 2),
            PalpiteRepository::values($id)
        );

        if (!in_array(round($value, 2), $allowed, true)) {
            throw new RuntimeException('Selecione um valor permitido.');
        }
    } elseif (empty($event['permitir_valor_livre'])) {
        throw new RuntimeException('Este palpite não permite valor livre.');
    }

    $method = (string)($_POST['formaPagamento'] ?? '');

    if ($method === 'PIX' && empty($event['pix_ativo'])) {
        throw new RuntimeException('PIX não está habilitado.');
    }

    if ($method === 'Cartao' && empty($event['cartao_ativo'])) {
        throw new RuntimeException('Cartão não está habilitado.');
    }

    if (!in_array($method, ['PIX', 'Cartao'], true)) {
        throw new RuntimeException('Forma de pagamento inválida.');
    }

    RateLimiter::hit('checkout_ip', Support::clientIp(), 20, 15);

    if ($method === 'Cartao') {
        RateLimiter::hit('cartao_ip', Support::clientIp(), 8, 15);
        RateLimiter::hit('cartao_cpf', Support::cpfDigits($cpf), 5, 30);
    }

    $donor = DoadorRepository::upsert([
        'nome' => $nome,
        'cpf' => $cpf,
        'email' => $email,
        'telefone' => $telefone,
    ]);

    $gateway = PaymentGatewayManager::forMethod($method);
    $gateway->assertReady($method);

    $customer = $gateway->preparePayer([
        'nome' => $nome,
        'cpf' => $cpf,
        'email' => $email,
        'telefone' => $telefone,
    ]);

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

    $palpiteId = PalpiteRepository::createEntry(
        $id,
        (int)$donor['idDoador'],
        $optionId,
        $prediction
    );

    $code = Support::randomCode('PLP-');
    $paymentId = PagamentoRepository::createForPalpite(
        $palpiteId,
        (int)$donor['idDoador'],
        $code,
        $value,
        $method,
        $gateway->key()
    );

    $description = 'Palpite - '
        . (string)$event['titulo']
        . ' - '
        . $prediction;

    try {
        if ($method === 'PIX') {
            $response = $gateway->createPix(
                $customerId,
                $value,
                $description,
                $code
            );

            /*
             * Primeiro salva a cobrança criada no Asaas.
             * Depois tenta carregar o QR Code Pix.
             */
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
                    'PIX de palpite criado sem QR Code ['
                    . (string)$response['id']
                    . ']: '
                    . $qrError->getMessage()
                );
            }
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
                $_POST['card_holder']
                ?? '',
            'number' =>
                $_POST['card_number']
                ?? '',
            'expiryMonth' =>
                $_POST['card_month']
                ?? '',
            'expiryYear' =>
                $_POST['card_year']
                ?? '',
            'ccv' =>
                $_POST['card_ccv']
                ?? '',
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

            PagamentoRepository::setGateway($paymentId, $gateway->key(), $response);
        }
    } catch (Throwable $e) {
        PagamentoRepository::fail($paymentId, $e->getMessage());
        throw $e;
    }

    NotificationService::paymentCreated($paymentId);

    Support::redirect('/pagamento/' . $code);
} catch (Throwable $e) {
    $_SESSION['_palpite_error'] = $e->getMessage();
    Support::redirect('/palpite/' . $event['slug']);
}
