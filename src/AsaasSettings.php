<?php

declare(strict_types=1);

final class AsaasSettings
{
    public static function get(): array
    {
        $db = Database::connection();

        $row = $db->query(
            'SELECT * FROM configuracoes_asaas WHERE idConfiguracao = 1'
        )->fetch();

        if (!$row) {
            $db->exec(
                "INSERT INTO configuracoes_asaas (
                    idConfiguracao,
                    ativo,
                    ambiente
                 ) VALUES (
                    1,
                    0,
                    'sandbox'
                 )"
            );

            $row = $db->query(
                'SELECT * FROM configuracoes_asaas WHERE idConfiguracao = 1'
            )->fetch();
        }

        return $row ?: [];
    }

    public static function activeEnvironment(): string
    {
        $s = self::get();

        return (($s['ambiente'] ?? 'sandbox') === 'producao')
            ? 'producao'
            : 'sandbox';
    }

    public static function apiKey(): string
    {
        $s = self::get();

        $field = self::activeEnvironment() === 'producao'
            ? 'api_key_producao'
            : 'api_key_sandbox';

        return Crypto::decrypt($s[$field] ?? null);
    }

    public static function webhookToken(): string
    {
        return self::webhookTokenFor(
            self::activeEnvironment()
        );
    }

    public static function webhookTokenFor(
        string $environment
    ): string {
        $s = self::get();

        $field = $environment === 'producao'
            ? 'webhook_token_producao'
            : 'webhook_token_sandbox';

        return Crypto::decrypt(
            $s[$field] ?? null
        );
    }

    /**
     * A mesma URL pública recebe eventos de Sandbox e Produção.
     * Por isso o endpoint precisa reconhecer os dois tokens,
     * mesmo quando outro ambiente estiver selecionado no Admin.
     */
    public static function webhookTokens(): array
    {
        $tokens = [
            self::webhookTokenFor('sandbox'),
            self::webhookTokenFor('producao'),
        ];

        return array_values(
            array_unique(
                array_filter(
                    array_map(
                        static fn (string $token): string =>
                            trim($token),
                        $tokens
                    ),
                    static fn (string $token): bool =>
                        $token !== ''
                )
            )
        );
    }

    public static function enabled(): bool
    {
        return (int)(self::get()['ativo'] ?? 0) === 1;
    }

    public static function pixVerification(?string $environment = null): array
    {
        $environment = $environment ?: self::activeEnvironment();

        if (!in_array($environment, ['sandbox', 'producao'], true)) {
            $environment = 'sandbox';
        }

        $s = self::get();

        return [
            'ambiente' => $environment,
            'disponivel' => array_key_exists(
                'pix_disponivel_' . $environment,
                $s
            ) && $s['pix_disponivel_' . $environment] !== null
                ? (bool)$s['pix_disponivel_' . $environment]
                : null,
            'verificadoEm' => $s['pix_verificado_em_' . $environment] ?? null,
            'chave' => $s['pix_chave_' . $environment] ?? null,
        ];
    }

    public static function savePixVerification(
        string $environment,
        bool $available,
        ?string $key = null
    ): void {
        if (!in_array($environment, ['sandbox', 'producao'], true)) {
            throw new InvalidArgumentException(
                'Ambiente Asaas inválido.'
            );
        }

        $fieldAvailable = 'pix_disponivel_' . $environment;
        $fieldChecked = 'pix_verificado_em_' . $environment;
        $fieldKey = 'pix_chave_' . $environment;

        $sql = "UPDATE configuracoes_asaas
                SET {$fieldAvailable}=:a,
                    {$fieldChecked}=NOW(),
                    {$fieldKey}=:k
                WHERE idConfiguracao=1";

        Database::connection()->prepare($sql)->execute([
            ':a' => $available ? 1 : 0,
            ':k' => $key !== null && trim($key) !== ''
                ? trim($key)
                : null,
        ]);
    }

    public static function clearPixVerification(
        ?string $environment = null
    ): void {
        $environment = $environment ?: self::activeEnvironment();

        if (!in_array($environment, ['sandbox', 'producao'], true)) {
            return;
        }

        $fieldAvailable = 'pix_disponivel_' . $environment;
        $fieldChecked = 'pix_verificado_em_' . $environment;
        $fieldKey = 'pix_chave_' . $environment;

        Database::connection()->exec(
            "UPDATE configuracoes_asaas
             SET {$fieldAvailable}=NULL,
                 {$fieldChecked}=NULL,
                 {$fieldKey}=NULL
             WHERE idConfiguracao=1"
        );
    }
}
