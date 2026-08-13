<?php

declare(strict_types=1);

final class SiteSettings
{
    private const DEFAULT_TITLE =
        'Checkout IECLB Parobé';

    private const DEFAULT_DESCRIPTION =
        'Campanhas de ofertas e palpites da IECLB Parobé';

    private static ?array $cache = null;

    public static function get(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        try {
            $row = Database::connection()
                ->query(
                    'SELECT *
                     FROM configuracoes_site
                     WHERE idConfiguracao=1'
                )
                ->fetch();

            self::$cache = $row ?: self::defaults();
        } catch (Throwable) {
            /*
             * Mantém o site funcionando durante uma atualização em que
             * os arquivos chegaram antes da migration.
             */
            self::$cache = self::defaults();
        }

        return self::$cache;
    }

    public static function title(): string
    {
        $title = trim(
            (string)(
                self::get()['titulo']
                ?? ''
            )
        );

        return $title !== ''
            ? $title
            : self::DEFAULT_TITLE;
    }

    public static function description(): string
    {
        $description = trim(
            (string)(
                self::get()['descricao']
                ?? ''
            )
        );

        return $description !== ''
            ? $description
            : self::DEFAULT_DESCRIPTION;
    }

    public static function faviconPath(): ?string
    {
        $favicon = trim(
            (string)(
                self::get()['favicon']
                ?? ''
            )
        );

        return $favicon !== ''
            ? ltrim($favicon, '/')
            : null;
    }

    public static function faviconUrl(): ?string
    {
        $path = self::faviconPath();

        return $path !== null
            ? APP_URL . '/' . $path
            : null;
    }

    public static function transparencyType(): string
    {
        $type = (string)(
            self::get()['transparencia_tipo']
            ?? 'Completa'
        );

        return in_array(
            $type,
            [
                'Completa',
                'Resumida',
                'Oculta',
            ],
            true
        )
            ? $type
            : 'Completa';
    }

    public static function pageTitle(
        ?string $page = null
    ): string {
        $page = trim(
            (string)$page
        );

        if ($page === '') {
            return self::title();
        }

        return $page . ' - ' . self::title();
    }

    public static function renderFavicon(): void
    {
        $url = self::faviconUrl();

        if ($url === null) {
            return;
        }

        ?>
        <link
            rel="icon"
            href="<?= Support::e($url) ?>"
        >
        <?php
    }

    public static function save(
        string $title,
        string $description,
        string $transparencyType,
        ?string $favicon = null,
        bool $changeFavicon = false
    ): void {
        $title = trim($title);
        $description = trim($description);

        if ($title === '') {
            throw new RuntimeException(
                'Informe o título do site.'
            );
        }

        if (mb_strlen($title) > 160) {
            throw new RuntimeException(
                'O título pode ter no máximo 160 caracteres.'
            );
        }

        if ($description === '') {
            throw new RuntimeException(
                'Informe a descrição do site.'
            );
        }

        if (mb_strlen($description) > 300) {
            throw new RuntimeException(
                'A descrição pode ter no máximo 300 caracteres.'
            );
        }

        if (
            !in_array(
                $transparencyType,
                [
                    'Completa',
                    'Resumida',
                    'Oculta',
                ],
                true
            )
        ) {
            throw new RuntimeException(
                'Tipo de transparência inválido.'
            );
        }

        $sql = 'UPDATE configuracoes_site
                SET titulo=:t,
                    descricao=:d,
                    transparencia_tipo=:tr';

        $params = [
            ':t' => $title,
            ':d' => $description,
            ':tr' => $transparencyType,
        ];

        if ($changeFavicon) {
            $sql .= ', favicon=:f';
            $params[':f'] = $favicon;
        }

        $sql .= ' WHERE idConfiguracao=1';

        Database::connection()
            ->prepare($sql)
            ->execute($params);

        self::$cache = null;
    }

    private static function defaults(): array
    {
        return [
            'idConfiguracao' => 1,
            'titulo' => self::DEFAULT_TITLE,
            'descricao' => self::DEFAULT_DESCRIPTION,
            'favicon' => null,
            'transparencia_tipo' => 'Completa',
        ];
    }
}
