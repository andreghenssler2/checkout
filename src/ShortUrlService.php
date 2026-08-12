<?php

declare(strict_types=1);

final class ShortUrlService
{
    public const TYPE_OFFER = 'Oferta';
    public const TYPE_PREDICTION = 'Palpite';

    private const CODE_LENGTH = 8;

    /*
     * O link curto utiliza letras maiúsculas, letras minúsculas e números.
     * Alguns caracteres visualmente ambíguos são omitidos para facilitar
     * leitura e digitação manual.
     */
    private const LOWERCASE = 'abcdefghjkmnpqrstuvwxyz';
    private const UPPERCASE = 'ABCDEFGHJKMNPQRSTUVWXYZ';
    private const NUMBERS = '23456789';
    private const ALPHABET =
        self::LOWERCASE
        . self::UPPERCASE
        . self::NUMBERS;

    public static function ensure(
        string $type,
        int $referenceId
    ): string {
        self::validateType($type);

        if ($referenceId <= 0) {
            throw new InvalidArgumentException(
                'Referência inválida para o link curto.'
            );
        }

        $existing = self::codeFor(
            $type,
            $referenceId
        );

        if ($existing !== null) {
            return $existing;
        }

        $db = Database::connection();

        for ($attempt = 0; $attempt < 30; $attempt++) {
            $code = self::generateCode();

            try {
                $stmt = $db->prepare(
                    'INSERT INTO links_curtos (
                        codigo,tipo,idReferencia
                     ) VALUES (
                        :codigo,:tipo,:referencia
                     )'
                );

                $stmt->execute([
                    ':codigo' => $code,
                    ':tipo' => $type,
                    ':referencia' => $referenceId,
                ]);

                return $code;
            } catch (PDOException $e) {
                /*
                 * Pode ter ocorrido:
                 * - colisão do código aleatório;
                 * - outra requisição criou o link da mesma origem.
                 *
                 * A UNIQUE KEY do banco é a garantia final de que
                 * um código nunca será repetido.
                 */
                if ((string)$e->getCode() !== '23000') {
                    throw $e;
                }

                $existing = self::codeFor(
                    $type,
                    $referenceId
                );

                if ($existing !== null) {
                    return $existing;
                }
            }
        }

        throw new RuntimeException(
            'Não foi possível gerar um link curto único.'
        );
    }

    public static function codeFor(
        string $type,
        int $referenceId
    ): ?string {
        self::validateType($type);

        $stmt = Database::connection()->prepare(
            'SELECT codigo
             FROM links_curtos
             WHERE tipo=:tipo
               AND idReferencia=:referencia
             LIMIT 1'
        );

        $stmt->execute([
            ':tipo' => $type,
            ':referencia' => $referenceId,
        ]);

        $code = $stmt->fetchColumn();

        return $code !== false
            ? (string)$code
            : null;
    }

    public static function urlFor(
        string $type,
        int $referenceId
    ): string {
        return self::url(
            self::ensure(
                $type,
                $referenceId
            )
        );
    }

    public static function url(string $code): string
    {
        return rtrim(APP_URL, '/')
            . '/s/'
            . rawurlencode($code);
    }

    public static function resolve(
        string $code
    ): ?array {
        $code = trim($code);

        if (
            !preg_match(
                '/^[A-Za-z0-9]{4,16}$/',
                $code
            )
        ) {
            return null;
        }

        $stmt = Database::connection()->prepare(
            'SELECT tipo,idReferencia
             FROM links_curtos
             WHERE codigo=:codigo
             LIMIT 1'
        );

        $stmt->execute([
            ':codigo' => $code,
        ]);

        $link = $stmt->fetch();

        if (!$link) {
            return null;
        }

        $type = (string)$link['tipo'];
        $referenceId = (int)$link['idReferencia'];

        if ($type === self::TYPE_OFFER) {
            $stmt = Database::connection()->prepare(
                'SELECT slug
                 FROM ofertas
                 WHERE idOferta=:id
                 LIMIT 1'
            );
        } elseif ($type === self::TYPE_PREDICTION) {
            $stmt = Database::connection()->prepare(
                'SELECT slug
                 FROM palpites_eventos
                 WHERE idEventoPalpite=:id
                 LIMIT 1'
            );
        } else {
            return null;
        }

        $stmt->execute([
            ':id' => $referenceId,
        ]);

        $slug = $stmt->fetchColumn();

        if ($slug === false || trim((string)$slug) === '') {
            return null;
        }

        return [
            'codigo' => $code,
            'tipo' => $type,
            'idReferencia' => $referenceId,
            'slug' => (string)$slug,
            'urlDestino' => $type === self::TYPE_OFFER
                ? rtrim(APP_URL, '/')
                    . '/oferta/'
                    . rawurlencode((string)$slug)
                : rtrim(APP_URL, '/')
                    . '/palpite/'
                    . rawurlencode((string)$slug),
        ];
    }

    private static function validateType(
        string $type
    ): void {
        if (!in_array(
            $type,
            [
                self::TYPE_OFFER,
                self::TYPE_PREDICTION,
            ],
            true
        )) {
            throw new InvalidArgumentException(
                'Tipo de link curto inválido.'
            );
        }
    }

    private static function generateCode(): string
    {
        /*
         * Cada código novo possui obrigatoriamente:
         * - pelo menos uma letra minúscula;
         * - pelo menos uma letra maiúscula;
         * - pelo menos um número.
         *
         * Os demais caracteres são sorteados do alfabeto completo.
         */
        $chars = [
            self::randomCharacter(self::LOWERCASE),
            self::randomCharacter(self::UPPERCASE),
            self::randomCharacter(self::NUMBERS),
        ];

        while (count($chars) < self::CODE_LENGTH) {
            $chars[] = self::randomCharacter(
                self::ALPHABET
            );
        }

        /*
         * Embaralhamento Fisher-Yates usando random_int(),
         * evitando posição previsível para cada tipo de caractere.
         */
        for ($i = count($chars) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);

            [$chars[$i], $chars[$j]] = [
                $chars[$j],
                $chars[$i],
            ];
        }

        return implode('', $chars);
    }

    private static function randomCharacter(
        string $alphabet
    ): string {
        return $alphabet[
            random_int(
                0,
                strlen($alphabet) - 1
            )
        ];
    }
}
