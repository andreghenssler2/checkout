<?php

declare(strict_types=1);

final class PagBankSettings
{
    public static function get(): array
    {
        $db = Database::connection();

        $row = $db->query(
            'SELECT *
             FROM configuracoes_pagbank
             WHERE idConfiguracao=1'
        )->fetch();

        if (!$row) {
            $db->exec(
                "INSERT INTO configuracoes_pagbank (
                    idConfiguracao,ativo,ambiente
                 ) VALUES (
                    1,0,'sandbox'
                 )"
            );

            $row = $db->query(
                'SELECT *
                 FROM configuracoes_pagbank
                 WHERE idConfiguracao=1'
            )->fetch();
        }

        return $row ?: [];
    }

    public static function enabled(): bool
    {
        return (int)(self::get()['ativo'] ?? 0) === 1;
    }

    public static function activeEnvironment(): string
    {
        return (self::get()['ambiente'] ?? 'sandbox') === 'producao'
            ? 'producao'
            : 'sandbox';
    }

public static function cardSettlementDays(): int
{
    $days = (int)(
        self::get()['cartao_prazo_recebimento']
        ?? 30
    );

    return in_array(
        $days,
        [14,30],
        true
    )
        ? $days
        : 30;
}

    public static function token(): string
    {
        return self::tokenFor(
            self::activeEnvironment()
        );
    }

    public static function tokenFor(string $environment): string
    {
        $settings = self::get();

        $field = $environment === 'producao'
            ? 'token_producao'
            : 'token_sandbox';

        return Crypto::decrypt(
            $settings[$field] ?? null
        );
    }

    public static function tokens(): array
    {
        return array_values(
            array_unique(
                array_filter(
                    [
                        trim(self::tokenFor('sandbox')),
                        trim(self::tokenFor('producao')),
                    ],
                    static fn (string $value): bool => $value !== ''
                )
            )
        );
    }

    public static function publicKey(?string $environment = null): string
    {
        $environment = $environment ?: self::activeEnvironment();

        /*
         * O PagBank documenta uma chave pública padrão e fixa para
         * o ambiente Sandbox. Usá-la diretamente evita que uma chave
         * de outro ambiente/conta seja utilizada por engano.
         */
        if ($environment === 'sandbox') {
            return self::sandboxPublicKey();
        }

        $settings = self::get();

        return trim(
            (string)(
                $settings['public_key_producao']
                ?? ''
            )
        );
    }

    public static function sandboxPublicKey(): string
    {
        return 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAr+ZqgD892U9/HXsa7XqBZUayPquAfh9xx4iwUbTSUAvTlmiXFQNTp0Bvt/5vK2FhMj39qSv1zi2OuBjvW38q1E374nzx6NNBL5JosV0+SDINTlCG0cmigHuBOyWzYmjgca+mtQu4WczCaApNaSuVqgb8u7Bd9GCOL4YJotvV5+81frlSwQXralhwRzGhj/A57CGPgGKiuPT+AOGmykIGEZsSD9RKkyoKIoc0OS8CPIzdBOtTQCIwrLn2FxI83Clcg55W8gkFSOS6rWNbG5qFZWMll6yl02HtunalHmUlRUL66YeGXdMDC2PuRcmZbGO5a/2tbVppW6mfSWG3NPRpgwIDAQAB';
    }

    public static function savePublicKey(
        string $environment,
        string $publicKey
    ): void {
        if (!in_array($environment, ['sandbox','producao'], true)) {
            throw new InvalidArgumentException('Ambiente PagBank inválido.');
        }

        $field = $environment === 'producao'
            ? 'public_key_producao'
            : 'public_key_sandbox';

        Database::connection()->prepare(
            "UPDATE configuracoes_pagbank
             SET {$field}=:k
             WHERE idConfiguracao=1"
        )->execute([
            ':k' => trim($publicKey),
        ]);
    }

    public static function saveTestResult(
        string $environment,
        ?string $error = null
    ): void {
        if (!in_array($environment, ['sandbox','producao'], true)) {
            return;
        }

        $testField = $environment === 'producao'
            ? 'ultimo_teste_producao'
            : 'ultimo_teste_sandbox';

        $errorField = $environment === 'producao'
            ? 'ultimo_erro_producao'
            : 'ultimo_erro_sandbox';

        Database::connection()->prepare(
            "UPDATE configuracoes_pagbank
             SET {$testField}=NOW(),
                 {$errorField}=:e
             WHERE idConfiguracao=1"
        )->execute([
            ':e' => $error !== null
                ? mb_substr($error, 0, 1500)
                : null,
        ]);
    }

    public static function webhookUrl(): string
    {
        return APP_URL . '/api/pagbank/webhook.php';
    }
}
