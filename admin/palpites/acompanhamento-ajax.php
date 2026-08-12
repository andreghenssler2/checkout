<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

Auth::require();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

function liveRespond(
    int $status,
    array $payload
): never {
    http_response_code($status);

    echo json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
    );

    exit;
}

try {
    $id = (int)(
        $_POST['idEventoPalpite']
        ?? $_GET['id']
        ?? 0
    );

    if ($id <= 0) {
        throw new InvalidArgumentException(
            'Jogo inválido.'
        );
    }

    $filter = PalpiteLiveService::normalizeFilter(
        trim(
            (string)(
                $_POST['filtro']
                ?? $_GET['filtro']
                ?? 'todos'
            )
        )
    );

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!Support::checkCsrf($_POST['_csrf'] ?? null)) {
            liveRespond(
                419,
                [
                    'ok' => false,
                    'message' => 'Sessão expirada. Atualize a página.',
                ]
            );
        }

        $event = PalpiteRepository::find($id);

        if (!$event) {
            throw new RuntimeException(
                'Jogo não encontrado.'
            );
        }

        if (($event['status_jogo'] ?? '') === 'Finalizado') {
            liveRespond(
                409,
                [
                    'ok' => false,
                    'finalizado' => true,
                    'message' => 'O jogo já foi finalizado e está bloqueado para edição.',
                ]
            );
        }

        $status = trim(
            (string)($_POST['status_jogo'] ?? 'EmAndamento')
        );

        if (!in_array(
            $status,
            ['Agendado', 'EmAndamento'],
            true
        )) {
            $status = 'EmAndamento';
        }

        PalpiteRepository::updateGame(
            $id,
            $status,
            $_POST['placar_casa'] ?? '',
            $_POST['placar_visitante'] ?? ''
        );
    }

    liveRespond(
        200,
        [
            'ok' => true,
            'snapshot' => PalpiteLiveService::snapshot(
                $id,
                $filter
            ),
        ]
    );
} catch (Throwable $e) {
    liveRespond(
        400,
        [
            'ok' => false,
            'message' => $e->getMessage(),
        ]
    );
}
