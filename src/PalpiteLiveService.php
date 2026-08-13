<?php

declare(strict_types=1);

final class PalpiteLiveService
{
    public static function normalizeFilter(
        string $filter
    ): string {
        return in_array(
            $filter,
            [
                'todos',
                'acertando',
                'errando',
                'pagos',
                'pendentes',
                'ganhadores',
            ],
            true
        )
            ? $filter
            : 'todos';
    }

    public static function snapshot(
        int $eventId,
        string $filter = 'todos'
    ): array {
        $event = PalpiteRepository::find($eventId);

        if (!$event) {
            throw new RuntimeException(
                'Formulário de palpite não encontrado.'
            );
        }

        $filter = self::normalizeFilter($filter);

        $all = PalpiteRepository::annotateEntries(
            PalpiteRepository::entries($eventId),
            $event
        );

        $items = PalpiteRepository::filterAnnotatedEntries(
            $all,
            $filter
        );

        $paid = count(
            array_filter(
                $all,
                static fn (array $item): bool =>
                    !empty($item['_pago'])
            )
        );

        $correct = count(
            array_filter(
                $all,
                static fn (array $item): bool =>
                    !empty($item['_acertando'])
            )
        );

        $winners = count(
            array_filter(
                $all,
                static fn (array $item): bool =>
                    !empty($item['_ganhador'])
            )
        );

        $received = array_reduce(
            $all,
            static fn (float $sum, array $item): float =>
                $sum
                + (
                    !empty($item['_pago'])
                        ? (float)($item['valor'] ?? 0)
                        : 0
                ),
            0.0
        );

        return [
            'event' => [
                'id' => (int)$event['idEventoPalpite'],
                'status' => (string)($event['status_jogo'] ?? 'Agendado'),
                'statusLabel' => PalpiteRepository::gameStatusLabel(
                    (string)($event['status_jogo'] ?? 'Agendado')
                ),
                'placarCasa' => $event['placar_casa'] !== null
                    ? (int)$event['placar_casa']
                    : null,
                'placarVisitante' => $event['placar_visitante'] !== null
                    ? (int)$event['placar_visitante']
                    : null,
                'finalizado' => (
                    ($event['status_jogo'] ?? '') === 'Finalizado'
                ),
            ],
            'stats' => [
                'total' => count($all),
                'paid' => $paid,
                'correct' => $correct,
                'winners' => $winners,
                'received' => Support::money($received),
            ],
            'filter' => $filter,
            'items' => array_map(
                static function (
                    array $item,
                    int $index
                ): array {
                    return [
                        'position' => $index + 1,
                        'winner' => !empty($item['_ganhador']),
                        'correct' => !empty($item['_acertando']),
                        'paid' => !empty($item['_pago']),
                        'name' => (string)($item['nome'] ?? ''),
                        'phone' => (string)($item['telefone'] ?? ''),
                        'email' => (string)($item['email'] ?? ''),
                        'prediction' => (string)($item['palpite'] ?? ''),
                        'paymentStatus' => (string)(
                            $item['pagamentoStatus']
                            ?? $item['statusPagamento']
                            ?? 'Pendente'
                        ),
                        'value' => isset($item['valor'])
                            ? Support::money(
                                (float)$item['valor']
                            )
                            : '—',
                    ];
                },
                $items,
                array_keys($items)
            ),
        ];
    }
}
