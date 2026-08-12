<?php

declare(strict_types=1);

final class NotificationService
{
    public static function paymentCreated(int $paymentId): void
    {
        try {
            $payment = PagamentoRepository::detailsById($paymentId);

            if (!$payment) {
                return;
            }

            $isPrediction = ($payment['tipoOrigem'] ?? '') === 'Palpite';

            $subject = $isPrediction
                ? 'Recebemos seu palpite - ' . $payment['titulo']
                : 'Recebemos sua oferta - ' . $payment['titulo'];

            $paymentUrl = APP_URL
                . '/pagamento/'
                . rawurlencode((string)$payment['codigo']);

            $intro = $isPrediction
                ? 'Seu palpite foi registrado e o pagamento foi criado.'
                : 'Sua oferta foi registrada e o pagamento foi criado.';

            $extra = '';

            if ($isPrediction && !empty($payment['palpiteTexto'])) {
                $extra = self::row(
                    'Palpite',
                    (string)$payment['palpiteTexto']
                );
            }

            $methodText = self::methodLabel(
                (string)$payment['formaPagamento']
            );

            $body = self::layout(
                'Recebemos sua solicitação',
                '<p>' . self::e($intro) . '</p>'
                . self::row(
                    $isPrediction ? 'Formulário' : 'Oferta',
                    (string)$payment['titulo']
                )
                . $extra
                . self::row(
                    'Valor',
                    Support::money((float)$payment['valor'])
                )
                . self::row(
                    'Forma de pagamento',
                    $methodText
                )
                . self::row(
                    'Status',
                    (string)$payment['status']
                )
                . '<p style="margin:24px 0 8px">'
                . '<a href="' . self::e($paymentUrl) . '" '
                . 'style="display:inline-block;background:#526c58;color:#fff;'
                . 'text-decoration:none;padding:12px 18px;border-radius:8px;'
                . 'font-weight:700">Acompanhar pagamento</a></p>'
                . '<p style="color:#6b746e;font-size:13px">'
                . 'PIX, boleto e atualizações do pagamento podem ser consultados por esse link.'
                . '</p>'
            );

            EmailService::queueUnique(
                $paymentId,
                'Criacao',
                (string)$payment['email'],
                $subject,
                $body
            );

            if (($payment['status'] ?? '') === 'Pago') {
                self::paymentApproved($paymentId);
            }
        } catch (Throwable $e) {
            error_log(
                'Falha ao preparar e-mail de criação do pagamento: '
                . $e->getMessage()
            );
        }
    }

    public static function paymentApproved(int $paymentId): void
    {
        try {
            $payment = PagamentoRepository::detailsById($paymentId);

            if (!$payment || ($payment['status'] ?? '') !== 'Pago') {
                return;
            }

            $receipt = ComprovanteService::ensureForPayment(
                $paymentId
            );

            $receiptUrl = ComprovanteService::url($receipt);

            $isPrediction = ($payment['tipoOrigem'] ?? '') === 'Palpite';

            $subject = 'Pagamento aprovado - '
                . (string)$payment['titulo'];

            $extra = '';

            if ($isPrediction && !empty($payment['palpiteTexto'])) {
                $extra = self::row(
                    'Palpite',
                    (string)$payment['palpiteTexto']
                );
            }

            $body = self::layout(
                'Pagamento aprovado',
                '<p>Seu pagamento foi confirmado com sucesso.</p>'
                . self::row(
                    $isPrediction ? 'Formulário' : 'Oferta',
                    (string)$payment['titulo']
                )
                . $extra
                . self::row(
                    'Valor pago',
                    Support::money((float)$payment['valor'])
                )
                . self::row(
                    'Forma de pagamento',
                    self::methodLabel(
                        (string)$payment['formaPagamento']
                    )
                )
                . self::row(
                    'Comprovante',
                    (string)$receipt['numero']
                )
                . '<p style="margin:24px 0 8px">'
                . '<a href="' . self::e($receiptUrl) . '" '
                . 'style="display:inline-block;background:#526c58;color:#fff;'
                . 'text-decoration:none;padding:12px 18px;border-radius:8px;'
                . 'font-weight:700">Abrir comprovante</a></p>'
                . '<p style="color:#6b746e;font-size:13px">'
                . 'O comprovante pode ser impresso ou salvo em PDF pelo navegador.'
                . '</p>'
            );

            EmailService::queueUnique(
                $paymentId,
                'Aprovado',
                (string)$payment['email'],
                $subject,
                $body
            );

            if ($isPrediction) {
                self::predictionWinnerIfApplicable(
                    $paymentId
                );
            }
        } catch (Throwable $e) {
            error_log(
                'Falha ao preparar comprovante/e-mail de aprovação: '
                . $e->getMessage()
            );
        }
    }


    public static function predictionWinners(
        int $eventId
    ): int {
        try {
            $event = PalpiteRepository::find($eventId);

            if (
                !$event
                || ($event['status_jogo'] ?? '') !== 'Finalizado'
            ) {
                return 0;
            }

            $entries = PalpiteRepository::annotateEntries(
                PalpiteRepository::entries($eventId),
                $event
            );

            $queued = 0;

            foreach ($entries as $entry) {
                if (
                    empty($entry['_ganhador'])
                    || empty($entry['idPagamento'])
                ) {
                    continue;
                }

                if (
                    self::predictionWinnerIfApplicable(
                        (int)$entry['idPagamento']
                    )
                ) {
                    $queued++;
                }
            }

            return $queued;
        } catch (Throwable $e) {
            error_log(
                'Falha ao preparar e-mails dos ganhadores: '
                . $e->getMessage()
            );

            return 0;
        }
    }

    public static function predictionWinnerIfApplicable(
        int $paymentId
    ): bool {
        try {
            $payment = PagamentoRepository::detailsById(
                $paymentId
            );

            if (
                !$payment
                || ($payment['tipoOrigem'] ?? '') !== 'Palpite'
                || ($payment['status'] ?? '') !== 'Pago'
                || ($payment['status_jogo'] ?? '') !== 'Finalizado'
                || $payment['placar_casa'] === null
                || $payment['placar_visitante'] === null
            ) {
                return false;
            }

            $prediction = PalpiteRepository::predictionScore(
                (string)($payment['palpiteTexto'] ?? ''),
                $payment
            );

            if (
                !$prediction
                || (int)$prediction['casa']
                    !== (int)$payment['placar_casa']
                || (int)$prediction['visitante']
                    !== (int)$payment['placar_visitante']
            ) {
                return false;
            }

            $subject = 'Você acertou o palpite - '
                . (string)$payment['titulo'];

            $body = self::layout(
                'Parabéns! Você acertou o palpite',
                '<p>Seu palpite acertou exatamente o placar final do jogo.</p>'
                . self::row(
                    'Jogo',
                    (string)$payment['equipe_casa']
                    . ' x '
                    . (string)$payment['equipe_visitante']
                )
                . self::row(
                    'Placar final',
                    (int)$payment['placar_casa']
                    . ' x '
                    . (int)$payment['placar_visitante']
                )
                . self::row(
                    'Seu palpite',
                    (string)$payment['palpiteTexto']
                )
                . self::row(
                    'Pagamento',
                    'Confirmado'
                )
                . '<p style="margin-top:22px">'
                . 'Você está entre os ganhadores deste palpite. '
                . 'A organização entrará em contato caso sejam necessárias '
                . 'informações adicionais sobre o resultado ou premiação.'
                . '</p>'
            );

            EmailService::queueUnique(
                $paymentId,
                'PalpiteGanhador',
                (string)$payment['email'],
                $subject,
                $body
            );

            return true;
        } catch (Throwable $e) {
            error_log(
                'Falha ao preparar e-mail de ganhador do palpite: '
                . $e->getMessage()
            );

            return false;
        }
    }

    private static function layout(
        string $title,
        string $content
    ): string {
        return '<!doctype html>'
            . '<html lang="pt-BR"><head><meta charset="utf-8"></head>'
            . '<body style="margin:0;background:#f3f5f3;font-family:Arial,sans-serif;'
            . 'color:#273029">'
            . '<div style="max-width:640px;margin:0 auto;padding:28px 16px">'
            . '<div style="background:#526c58;color:#fff;padding:18px 22px;'
            . 'border-radius:12px 12px 0 0">'
            . '<strong style="font-size:20px">Checkout IECLB Parobé</strong>'
            . '</div>'
            . '<div style="background:#fff;padding:26px 22px;border:1px solid #dfe4e0;'
            . 'border-top:0;border-radius:0 0 12px 12px">'
            . '<h1 style="font-size:26px;margin:0 0 18px">'
            . self::e($title)
            . '</h1>'
            . $content
            . '<hr style="border:0;border-top:1px solid #e8ece9;margin:28px 0">'
            . '<p style="font-size:12px;color:#7a817d;margin:0">'
            . 'Mensagem automática do Checkout IECLB Parobé.'
            . '</p></div></div></body></html>';
    }

    private static function row(
        string $label,
        string $value
    ): string {
        return '<div style="margin:10px 0">'
            . '<span style="display:block;font-size:12px;color:#6d756f">'
            . self::e($label)
            . '</span>'
            . '<strong style="display:block;margin-top:3px">'
            . self::e($value)
            . '</strong></div>';
    }

    private static function methodLabel(string $method): string
    {
        return match ($method) {
            'PIX' => 'PIX',
            'Boleto' => 'Boleto',
            'Cartao' => 'Cartão de Crédito',
            default => $method,
        };
    }

    private static function e(string $value): string
    {
        return htmlspecialchars(
            $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}
