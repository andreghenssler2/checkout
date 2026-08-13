<?php

declare(strict_types=1);

final class BoletoOfertaService
{
    /**
     * Vencimento: 1 dia útil após a geração.
     * Para esta regra, dias úteis são segunda a sexta-feira.
     */
    public static function proximoDiaUtil(?DateTimeImmutable $base = null): DateTimeImmutable
    {
        $timezone = new DateTimeZone(APP_TIMEZONE);
        $base = $base
            ? $base->setTimezone($timezone)
            : new DateTimeImmutable('today', $timezone);

        $data = $base->setTime(0, 0)->modify('+1 day');

        while ((int)$data->format('N') >= 6) {
            $data = $data->modify('+1 day');
        }

        return $data;
    }

    /**
     * Retorna a situação do Boleto para uma Oferta.
     *
     * Regra:
     * - somente se boleto_ativo = 1;
     * - vencimento é no próximo dia útil;
     * - se a oferta encerrar no mesmo dia do vencimento ou antes,
     *   o boleto não pode ser gerado.
     */
    public static function disponibilidade(array $oferta): array
    {
        $vencimento = self::proximoDiaUtil();

        if (empty($oferta['boleto_ativo'])) {
            return [
                'habilitado' => false,
                'disponivel' => false,
                'vencimento' => $vencimento,
                'motivo' => 'Boleto não está habilitado para esta oferta.',
            ];
        }

        $fim = trim((string)($oferta['data_fim'] ?? ''));

        if ($fim !== '') {
            try {
                $dataFim = new DateTimeImmutable(
                    $fim,
                    new DateTimeZone(APP_TIMEZONE)
                );

                /*
                 * O boleto precisa vencer antes do dia de encerramento.
                 * Assim, se o formulário fecha amanhã e o boleto também
                 * venceria amanhã, ele não é oferecido.
                 */
                if ($dataFim->format('Y-m-d') <= $vencimento->format('Y-m-d')) {
                    return [
                        'habilitado' => true,
                        'disponivel' => false,
                        'vencimento' => $vencimento,
                        'motivo' => 'Boleto indisponível porque o vencimento de 1 dia útil ficaria no mesmo dia ou após o encerramento desta oferta.',
                    ];
                }
            } catch (Throwable) {
                return [
                    'habilitado' => true,
                    'disponivel' => false,
                    'vencimento' => $vencimento,
                    'motivo' => 'Não foi possível validar a data de encerramento para gerar o boleto.',
                ];
            }
        }

        return [
            'habilitado' => true,
            'disponivel' => true,
            'vencimento' => $vencimento,
            'motivo' => '',
        ];
    }

    public static function validarGeracao(array $oferta): DateTimeImmutable
    {
        $situacao = self::disponibilidade($oferta);

        if (!$situacao['disponivel']) {
            throw new RuntimeException(
                (string)($situacao['motivo'] ?: 'Boleto indisponível para esta oferta.')
            );
        }

        return $situacao['vencimento'];
    }

    public static function vencimentoFormatado(DateTimeImmutable $data): string
    {
        return $data->format('d/m/Y');
    }
}
