<?php

declare(strict_types=1);

final class RateLimiter
{
    public static function hit(string $type, string $identity, int $maxAttempts, int $windowMinutes): void
    {
        $type = preg_replace('/[^a-z0-9_-]/i', '', $type) ?: 'generic';
        $key = hash('sha256', $type . '|' . $identity);
        $db = Database::connection();
        $db->beginTransaction();
        try {
            $st = $db->prepare('SELECT * FROM checkout_limites WHERE chave=:c AND tipo=:t FOR UPDATE');
            $st->execute([':c'=>$key, ':t'=>$type]);
            $row = $st->fetch();
            $now = new DateTimeImmutable();
            if (!$row) {
                $db->prepare('INSERT INTO checkout_limites (chave,tipo,janelaInicio,tentativas) VALUES (:c,:t,NOW(),1)')->execute([':c'=>$key,':t'=>$type]);
                $attempts = 1;
            } else {
                $start = new DateTimeImmutable((string)$row['janelaInicio']);
                if ($start->modify('+' . $windowMinutes . ' minutes') <= $now) {
                    $db->prepare('UPDATE checkout_limites SET janelaInicio=NOW(),tentativas=1 WHERE chave=:c AND tipo=:t')->execute([':c'=>$key,':t'=>$type]);
                    $attempts = 1;
                } else {
                    $attempts = (int)$row['tentativas'] + 1;
                    $db->prepare('UPDATE checkout_limites SET tentativas=:a WHERE chave=:c AND tipo=:t')->execute([':a'=>$attempts,':c'=>$key,':t'=>$type]);
                }
            }
            $db->commit();
            if ($attempts > $maxAttempts) {
                throw new RuntimeException('Muitas tentativas de pagamento em pouco tempo. Aguarde alguns minutos e tente novamente.');
            }
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }
}
