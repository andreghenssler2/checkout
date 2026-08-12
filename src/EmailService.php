<?php

declare(strict_types=1);

final class EmailService
{
    public static function queueUnique(
        ?int $paymentId,
        string $type,
        string $to,
        string $subject,
        string $html
    ): int {
        $to = strtolower(trim($to));
        $type = trim($type);

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('E-mail de destino inválido.');
        }

        $db = Database::connection();

        if ($paymentId !== null) {
            $stmt = $db->prepare(
                'SELECT idEmail
                 FROM emails_envios
                 WHERE idPagamento=:p AND tipo=:t
                 LIMIT 1'
            );
            $stmt->execute([
                ':p' => $paymentId,
                ':t' => $type,
            ]);

            $existing = (int)$stmt->fetchColumn();

            if ($existing > 0) {
                return $existing;
            }
        }

        $stmt = $db->prepare(
            "INSERT INTO emails_envios (
                idPagamento,
                tipo,
                destinatario,
                assunto,
                corpoHtml,
                status,
                tentativas,
                criadoEm
             ) VALUES (
                :p,
                :t,
                :d,
                :a,
                :c,
                'Pendente',
                0,
                NOW()
             )"
        );

        try {
            $stmt->execute([
                ':p' => $paymentId,
                ':t' => $type,
                ':d' => $to,
                ':a' => mb_substr($subject, 0, 220),
                ':c' => $html,
            ]);
        } catch (PDOException $e) {
            if ($paymentId !== null) {
                $check = $db->prepare(
                    'SELECT idEmail
                     FROM emails_envios
                     WHERE idPagamento=:p AND tipo=:t
                     LIMIT 1'
                );
                $check->execute([
                    ':p' => $paymentId,
                    ':t' => $type,
                ]);

                $existing = (int)$check->fetchColumn();

                if ($existing > 0) {
                    return $existing;
                }
            }

            throw $e;
        }

        $id = (int)$db->lastInsertId();

        self::trySend($id);

        return $id;
    }

    public static function trySend(int $idEmail): bool
    {
        $db = Database::connection();

        $stmt = $db->prepare(
            'SELECT * FROM emails_envios WHERE idEmail=:id LIMIT 1'
        );
        $stmt->execute([':id' => $idEmail]);
        $email = $stmt->fetch();

        if (!$email) {
            return false;
        }

        if (($email['status'] ?? '') === 'Enviado') {
            return true;
        }

        if (!EmailSettings::enabled()) {
            return false;
        }

        $settings = EmailSettings::get();

        $fromEmail = strtolower(
            trim((string)($settings['remetente_email'] ?? ''))
        );

        $fromName = trim(
            (string)($settings['remetente_nome'] ?? APP_NAME)
        );

        $replyTo = strtolower(
            trim((string)($settings['reply_to'] ?? ''))
        );

        if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            self::markError(
                $idEmail,
                'Configure um e-mail de remetente válido.'
            );
            return false;
        }

        $to = trim((string)$email['destinatario']);

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            self::markError(
                $idEmail,
                'Destinatário inválido.'
            );
            return false;
        }

        $subject = (string)$email['assunto'];

        if (function_exists('mb_encode_mimeheader')) {
            $subject = mb_encode_mimeheader(
                $subject,
                'UTF-8',
                'B',
                "\r\n"
            );
        }

        $safeFromName = preg_replace(
            '/[\r\n]+/',
            ' ',
            $fromName
        ) ?: APP_NAME;

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            'From: ' . $safeFromName . ' <' . $fromEmail . '>',
            'X-Mailer: Checkout IECLB Parobé',
        ];

        if (filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: ' . $replyTo;
        }

        $sent = false;
        $error = '';

        try {
            $sent = mail(
                $to,
                $subject,
                (string)$email['corpoHtml'],
                implode("\r\n", $headers)
            );

            if (!$sent) {
                $error = 'A função mail() retornou false.';
            }
        } catch (Throwable $e) {
            $sent = false;
            $error = $e->getMessage();
        }

        if ($sent) {
            $db->prepare(
                "UPDATE emails_envios
                 SET
                    status='Enviado',
                    tentativas=tentativas+1,
                    enviadoEm=NOW(),
                    ultimoErro=NULL
                 WHERE idEmail=:id"
            )->execute([':id' => $idEmail]);

            return true;
        }

        self::markError(
            $idEmail,
            $error !== '' ? $error : 'Falha desconhecida no envio.'
        );

        return false;
    }

    public static function processPending(int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));

        $stmt = Database::connection()->query(
            "SELECT idEmail
             FROM emails_envios
             WHERE status IN ('Pendente','Erro')
               AND tentativas < 10
             ORDER BY criadoEm ASC
             LIMIT {$limit}"
        );

        $ids = array_map(
            'intval',
            array_column($stmt->fetchAll(), 'idEmail')
        );

        $sent = 0;
        $failed = 0;

        foreach ($ids as $id) {
            if (self::trySend($id)) {
                $sent++;
            } else {
                $failed++;
            }
        }

        return [
            'processados' => count($ids),
            'enviados' => $sent,
            'falhas' => $failed,
        ];
    }

    private static function markError(
        int $idEmail,
        string $message
    ): void {
        Database::connection()->prepare(
            "UPDATE emails_envios
             SET
                status='Erro',
                tentativas=tentativas+1,
                ultimoErro=:e
             WHERE idEmail=:id"
        )->execute([
            ':e' => mb_substr($message, 0, 1000),
            ':id' => $idEmail,
        ]);
    }
}
