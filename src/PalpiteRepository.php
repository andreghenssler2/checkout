<?php

declare(strict_types=1);

final class PalpiteRepository
{
    public static function active(): array
    {
        $sql = "
            SELECT
                e.*,
                (SELECT MIN(valor)
                 FROM palpites_valores v
                 WHERE v.idEventoPalpite=e.idEventoPalpite
                   AND v.ativo=1) AS menorValor
            FROM palpites_eventos e
            WHERE e.ativo=1
              AND (e.data_inicio IS NULL OR e.data_inicio <= NOW())
              AND (e.data_fim IS NULL OR e.data_fim >= NOW())
            ORDER BY COALESCE(e.data_jogo, e.criadoEm) ASC, e.criadoEm DESC
        ";
        return Database::connection()->query($sql)->fetchAll();
    }

    public static function all(
        ?int $year = null
    ): array {
        $sql = "
            SELECT e.*,
                (SELECT COUNT(*)
                 FROM palpites p
                 WHERE p.idEventoPalpite=e.idEventoPalpite) AS totalPalpites,
                (SELECT COUNT(*)
                 FROM palpites p
                 WHERE p.idEventoPalpite=e.idEventoPalpite
                   AND p.statusPagamento='Pago') AS totalPagos
            FROM palpites_eventos e
        ";

        $params = [];

        if ($year !== null && $year >= 2000 && $year <= 2100) {
            $sql .= "
                WHERE YEAR(
                    COALESCE(
                        e.data_jogo,
                        e.data_inicio,
                        e.criadoEm
                    )
                )=:ano
            ";

            $params[':ano'] = $year;
        }

        $sql .= "
            ORDER BY
                COALESCE(
                    e.data_jogo,
                    e.data_inicio,
                    e.criadoEm
                ) DESC,
                e.idEventoPalpite DESC
        ";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function years(): array
    {
        $stmt = Database::connection()->query(
            "SELECT DISTINCT
                YEAR(
                    COALESCE(
                        data_jogo,
                        data_inicio,
                        criadoEm
                    )
                ) AS ano
             FROM palpites_eventos
             WHERE COALESCE(
                 data_jogo,
                 data_inicio,
                 criadoEm
             ) IS NOT NULL
             ORDER BY ano DESC"
        );

        return array_values(
            array_filter(
                array_map(
                    static fn (array $row): int =>
                        (int)($row['ano'] ?? 0),
                    $stmt->fetchAll()
                ),
                static fn (int $year): bool =>
                    $year >= 2000 && $year <= 2100
            )
        );
    }


    public static function publicHistory(): array
    {
        $sql = "
            SELECT
                e.*,
                (
                    SELECT COUNT(*)
                    FROM palpites p
                    WHERE p.idEventoPalpite=e.idEventoPalpite
                ) AS totalPalpites,
                (
                    SELECT COUNT(*)
                    FROM palpites p
                    WHERE p.idEventoPalpite=e.idEventoPalpite
                      AND p.statusPagamento='Pago'
                ) AS totalPagos,
                (
                    SELECT COALESCE(SUM(pg.valor),0)
                    FROM pagamentos pg
                    INNER JOIN palpites p
                        ON p.idPalpite=pg.idPalpite
                    WHERE p.idEventoPalpite=e.idEventoPalpite
                      AND pg.status='Pago'
                ) AS valorRecebido,
                (
                    SELECT MIN(v.valor)
                    FROM palpites_valores v
                    WHERE v.idEventoPalpite=e.idEventoPalpite
                      AND v.ativo=1
                ) AS menorValor
            FROM palpites_eventos e
            ORDER BY
                CASE
                    WHEN e.status_jogo='Finalizado'
                         OR (
                            e.data_jogo IS NOT NULL
                            AND e.data_jogo <= NOW()
                         )
                    THEN 1
                    ELSE 0
                END ASC,
                CASE
                    WHEN e.status_jogo='Finalizado'
                         OR (
                            e.data_jogo IS NOT NULL
                            AND e.data_jogo <= NOW()
                         )
                    THEN NULL
                    ELSE COALESCE(
                        e.data_jogo,
                        e.data_inicio,
                        e.criadoEm
                    )
                END ASC,
                CASE
                    WHEN e.status_jogo='Finalizado'
                         OR (
                            e.data_jogo IS NOT NULL
                            AND e.data_jogo <= NOW()
                         )
                    THEN COALESCE(
                        e.data_jogo,
                        e.finalizadoEm,
                        e.criadoEm
                    )
                    ELSE NULL
                END DESC,
                e.idEventoPalpite DESC
        ";

        return Database::connection()
            ->query($sql)
            ->fetchAll();
    }

    public static function isPastGame(array $event): bool
    {
        if (
            ($event['status_jogo'] ?? '')
            === 'Finalizado'
        ) {
            return true;
        }

        if (empty($event['data_jogo'])) {
            return false;
        }

        try {
            $gameDate = new DateTimeImmutable(
                (string)$event['data_jogo'],
                new DateTimeZone(APP_TIMEZONE)
            );

            $now = new DateTimeImmutable(
                'now',
                new DateTimeZone(APP_TIMEZONE)
            );

            return $gameDate <= $now;
        } catch (Throwable) {
            return false;
        }
    }

    public static function bySlug(string $slug, bool $onlyActive = true): ?array
    {
        $sql = 'SELECT * FROM palpites_eventos WHERE slug=:slug';
        if ($onlyActive) {
            $sql .= " AND ativo=1
                      AND (data_inicio IS NULL OR data_inicio <= NOW())
                      AND (data_fim IS NULL OR data_fim >= NOW())";
        }
        $sql .= ' LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM palpites_eventos WHERE idEventoPalpite=:id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function values(int $id): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM palpites_valores
             WHERE idEventoPalpite=:id AND ativo=1
             ORDER BY ordem, valor'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetchAll();
    }

    public static function options(int $id): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM palpites_opcoes
             WHERE idEventoPalpite=:id AND ativo=1
             ORDER BY ordem, idOpcao'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetchAll();
    }


    /**
     * Retorna a distribuição pública das opções de Palpite.
     *
     * Somente participações com pagamento confirmado entram na contagem,
     * pois são as participações válidas para apuração dos ganhadores.
     *
     * Todas as opções configuradas são retornadas, inclusive com zero.
     * Se "Outro" estiver habilitado, ele também aparece como uma linha.
     *
     * Nenhum dado pessoal é retornado por este método.
     */
    public static function publicDistribution(
        int $eventId
    ): array {
        $event = self::find($eventId);

        if (!$event) {
            return [];
        }

        $options = self::options($eventId);

        /*
         * Contagem das opções normais.
         */
        $stmt = Database::connection()->prepare(
            "SELECT
                idOpcao,
                COUNT(*) AS total
             FROM palpites
             WHERE idEventoPalpite=:id
               AND statusPagamento='Pago'
               AND idOpcao IS NOT NULL
             GROUP BY idOpcao"
        );

        $stmt->execute([
            ':id' => $eventId,
        ]);

        $counts = [];

        foreach ($stmt->fetchAll() as $row) {
            $counts[(string)(int)$row['idOpcao']] =
                (int)($row['total'] ?? 0);
        }

        $finished = (
            ($event['status_jogo'] ?? '') === 'Finalizado'
            && $event['placar_casa'] !== null
            && $event['placar_visitante'] !== null
        );

        $result = [];

        foreach ($options as $option) {
            $idOption = (int)$option['idOpcao'];
            $label = trim((string)$option['rotulo']);

            $score = self::predictionScore(
                $label,
                $event
            );

            $totalOption = (int)(
                $counts[(string)$idOption]
                ?? 0
            );

            /*
             * Não existe "placar vencedor" público se ninguém participou
             * daquela opção. O placar pode coincidir com o resultado,
             * mas sem participação confirmada não há ganhador.
             */
            $winner = $finished
                && $totalOption > 0
                && $score !== null
                && (int)$score['casa']
                    === (int)$event['placar_casa']
                && (int)$score['visitante']
                    === (int)$event['placar_visitante'];

            $result[] = [
                'tipo' => 'Opcao',
                'idOpcao' => $idOption,
                'rotulo' => $label,
                'total' => $totalOption,
                'ganhador' => $winner,
            ];
        }

        /*
         * Quando "Outro" estiver habilitado, cada texto digitado pelos
         * participantes é exibido publicamente, porém SEM qualquer dado
         * pessoal. Textos iguais são agrupados e recebem uma contagem.
         */
        if (!empty($event['permitir_outro_palpite'])) {
            $otherStmt = Database::connection()->prepare(
                "SELECT
                    palpite,
                    COUNT(*) AS total
                 FROM palpites
                 WHERE idEventoPalpite=:id
                   AND statusPagamento='Pago'
                   AND idOpcao IS NULL
                   AND TRIM(palpite)<>''
                 GROUP BY palpite
                 ORDER BY total DESC,palpite ASC"
            );

            $otherStmt->execute([
                ':id' => $eventId,
            ]);

            $otherRows = $otherStmt->fetchAll();

            if ($otherRows) {
                foreach ($otherRows as $row) {
                    $label = trim(
                        (string)($row['palpite'] ?? '')
                    );

                    $score = self::predictionScore(
                        $label,
                        $event
                    );

                    $totalOther = (int)(
                        $row['total']
                        ?? 0
                    );

                    $winner = $finished
                        && $totalOther > 0
                        && $score !== null
                        && (int)$score['casa']
                            === (int)$event['placar_casa']
                        && (int)$score['visitante']
                            === (int)$event['placar_visitante'];

                    $result[] = [
                        'tipo' => 'Outro',
                        'idOpcao' => null,
                        'rotulo' => $label,
                        'total' => $totalOther,
                        'ganhador' => $winner,
                    ];
                }
            } else {
                /*
                 * Mantém a opção visível mesmo quando ninguém utilizou Outro.
                 */
                $result[] = [
                    'tipo' => 'Outro',
                    'idOpcao' => null,
                    'rotulo' => 'Outro',
                    'total' => 0,
                    'ganhador' => false,
                ];
            }
        }

        return $result;
    }

    public static function publicDistributionTotal(
        int $eventId
    ): int {
        return array_reduce(
            self::publicDistribution($eventId),
            static fn (int $sum, array $item): int =>
                $sum + (int)($item['total'] ?? 0),
            0
        );
    }

    public static function option(int $eventId, int $optionId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM palpites_opcoes
             WHERE idEventoPalpite=:e AND idOpcao=:o AND ativo=1
             LIMIT 1'
        );
        $stmt->execute([':e' => $eventId, ':o' => $optionId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function save(array $d): int
    {
        $db = Database::connection();
        $id = (int)($d['idEventoPalpite'] ?? 0);
        $min = max(APP_MIN_OFFER, Support::decimal($d['valor_minimo'] ?? APP_MIN_OFFER));

        $titulo = trim((string)($d['titulo'] ?? ''));
        $casa = trim((string)($d['equipe_casa'] ?? ''));
        $visitante = trim((string)($d['equipe_visitante'] ?? ''));
        $dataJogo = self::dt($d['data_jogo'] ?? null);

        if ($titulo === '') {
            throw new InvalidArgumentException('Informe o título do formulário de palpite.');
        }
        if ($casa === '' || $visitante === '') {
            throw new InvalidArgumentException('Informe as duas equipes do jogo.');
        }
        if ($dataJogo === null) {
            throw new InvalidArgumentException('Informe a data e hora do jogo.');
        }

        /*
         * O slug do Palpite é sempre gerado pelo título.
         *
         * Ex.: "Copa do Mundo da JEP - Brasil x Escócia"
         *      -> copa-do-mundo-da-jep-brasil-x-escocia
         */
        $slug = Support::slug($titulo);

        if ($slug === '') {
            throw new InvalidArgumentException('Não foi possível gerar o endereço do palpite.');
        }

        $base = $slug;
        $n = 1;

        while (self::slugExists($slug, $id)) {
            $slug = $base . '-' . (++$n);
        }

        $pix = !empty($d['pix_ativo']) ? 1 : 0;
        $cartao = !empty($d['cartao_ativo']) ? 1 : 0;
        if (!$pix && !$cartao) {
            throw new InvalidArgumentException('Habilite PIX e/ou Cartão de Crédito.');
        }

        $values = [];
        foreach (($d['valores'] ?? []) as $value) {
            $value = Support::decimal($value);
            if ($value >= $min) {
                $values[] = round($value, 2);
            }
        }
        $values = array_values(array_unique($values));

        $allowFree = !empty($d['permitir_valor_livre']) ? 1 : 0;
        if (!$values && !$allowFree) {
            throw new InvalidArgumentException('Informe pelo menos um valor fixo ou permita valor livre.');
        }

        $options = [];
        foreach (($d['opcoes'] ?? []) as $option) {
            $option = trim((string)$option);
            if ($option !== '') {
                $options[] = mb_substr($option, 0, 160);
            }
        }
        $options = array_values(array_unique($options));
        if (!$options) {
            throw new InvalidArgumentException('Informe pelo menos uma opção de palpite.');
        }

        $params = [
            ':titulo' => $titulo,
            ':slug' => $slug,
            ':descricao' => trim((string)($d['descricao'] ?? '')) ?: null,
            ':imagem' => $d['imagem'] ?? null,
            ':equipe_casa' => $casa,
            ':equipe_visitante' => $visitante,
            ':data_jogo' => $dataJogo,
            ':data_inicio' => self::dt($d['data_inicio'] ?? null),
            ':data_fim' => self::dt($d['data_fim'] ?? null),
            ':valor_minimo' => $min,
            ':permitir_valor_livre' => $allowFree,
            ':permitir_outro_palpite' => !empty($d['permitir_outro_palpite']) ? 1 : 0,
            ':pix_ativo' => $pix,
            ':cartao_ativo' => $cartao,
            ':ativo' => !empty($d['ativo']) ? 1 : 0,
        ];

        $db->beginTransaction();
        try {
            if ($id > 0) {
                $params[':id'] = $id;
                $db->prepare(
                    'UPDATE palpites_eventos SET
                        titulo=:titulo,
                        slug=:slug,
                        descricao=:descricao,
                        imagem=:imagem,
                        equipe_casa=:equipe_casa,
                        equipe_visitante=:equipe_visitante,
                        data_jogo=:data_jogo,
                        data_inicio=:data_inicio,
                        data_fim=:data_fim,
                        valor_minimo=:valor_minimo,
                        permitir_valor_livre=:permitir_valor_livre,
                        permitir_outro_palpite=:permitir_outro_palpite,
                        pix_ativo=:pix_ativo,
                        cartao_ativo=:cartao_ativo,
                        ativo=:ativo
                     WHERE idEventoPalpite=:id'
                )->execute($params);
            } else {
                $db->prepare(
                    'INSERT INTO palpites_eventos (
                        titulo,slug,descricao,imagem,equipe_casa,equipe_visitante,
                        data_jogo,data_inicio,data_fim,valor_minimo,
                        permitir_valor_livre,permitir_outro_palpite,
                        pix_ativo,cartao_ativo,ativo
                    ) VALUES (
                        :titulo,:slug,:descricao,:imagem,:equipe_casa,:equipe_visitante,
                        :data_jogo,:data_inicio,:data_fim,:valor_minimo,
                        :permitir_valor_livre,:permitir_outro_palpite,
                        :pix_ativo,:cartao_ativo,:ativo
                    )'
                )->execute($params);
                $id = (int)$db->lastInsertId();
            }

            $db->prepare('DELETE FROM palpites_valores WHERE idEventoPalpite=:id')
                ->execute([':id' => $id]);
            $order = 0;
            foreach ($values as $value) {
                $db->prepare(
                    'INSERT INTO palpites_valores (idEventoPalpite,valor,ordem,ativo)
                     VALUES (:id,:v,:o,1)'
                )->execute([':id' => $id, ':v' => $value, ':o' => $order++]);
            }

            /*
             * As opções antigas podem estar ligadas a palpites já enviados.
             * O FK usa ON DELETE SET NULL e o texto do palpite é preservado em
             * palpites.palpite, portanto editar a lista não altera o histórico.
             */
            $db->prepare('DELETE FROM palpites_opcoes WHERE idEventoPalpite=:id')
                ->execute([':id' => $id]);
            $order = 0;
            foreach ($options as $option) {
                $db->prepare(
                    'INSERT INTO palpites_opcoes (idEventoPalpite,rotulo,ordem,ativo)
                     VALUES (:id,:r,:o,1)'
                )->execute([':id' => $id, ':r' => $option, ':o' => $order++]);
            }

            /*
             * O link curto é criado dentro da mesma transação.
             * Assim todo Palpite salvo já possui um código único.
             */
            ShortUrlService::ensure(
                ShortUrlService::TYPE_PREDICTION,
                $id
            );

            $db->commit();
            return $id;
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    public static function createEntry(
        int $eventId,
        int $donorId,
        ?int $optionId,
        string $prediction
    ): int {
        $prediction = trim($prediction);
        if ($prediction === '') {
            throw new InvalidArgumentException('Informe o seu palpite.');
        }

        $stmt = Database::connection()->prepare(
            "INSERT INTO palpites (
                idEventoPalpite,idDoador,idOpcao,palpite,statusPagamento
             ) VALUES (
                :e,:d,:o,:p,'Pendente'
             )"
        );
        $stmt->execute([
            ':e' => $eventId,
            ':d' => $donorId,
            ':o' => $optionId,
            ':p' => mb_substr($prediction, 0, 160),
        ]);
        return (int)Database::connection()->lastInsertId();
    }

    public static function updatePaymentStatus(int $idPalpite, string $status): void
    {
        if ($idPalpite <= 0) {
            return;
        }
        Database::connection()->prepare(
            'UPDATE palpites
             SET statusPagamento=:s, atualizadoEm=NOW()
             WHERE idPalpite=:id'
        )->execute([':s' => $status, ':id' => $idPalpite]);
    }

    public static function entries(int $eventId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT
                p.*,
                d.nome,d.cpf,d.email,d.telefone,
                pg.idPagamento,pg.codigo,pg.valor,pg.valorLiquido,pg.taxa,
                pg.formaPagamento,pg.status AS pagamentoStatus,
                pg.asaasPaymentId,pg.criadoEm AS pagamentoCriadoEm,
                pg.dataPagamento
             FROM palpites p
             JOIN doadores d ON d.idDoador=p.idDoador
             LEFT JOIN pagamentos pg ON pg.idPalpite=p.idPalpite
             WHERE p.idEventoPalpite=:id
             ORDER BY p.criadoEm DESC"
        );
        $stmt->execute([':id' => $eventId]);
        return $stmt->fetchAll();
    }


    public static function updateGame(
        int $eventId,
        string $status,
        mixed $homeScore,
        mixed $awayScore
    ): void {
        $event = self::find($eventId);

        if (!$event) {
            throw new RuntimeException(
                'Formulário de palpite não encontrado.'
            );
        }

        if (($event['status_jogo'] ?? '') === 'Finalizado') {
            throw new RuntimeException(
                'Este jogo já foi finalizado e não pode mais ser editado.'
            );
        }

        $allowed = [
            'Agendado',
            'EmAndamento',
            'Finalizado',
        ];

        if (!in_array($status, $allowed, true)) {
            throw new InvalidArgumentException(
                'Situação do jogo inválida.'
            );
        }

        $home = null;
        $away = null;

        if ($status !== 'Agendado') {
            if (
                $homeScore === ''
                || $awayScore === ''
                || !is_numeric($homeScore)
                || !is_numeric($awayScore)
            ) {
                throw new InvalidArgumentException(
                    'Informe o placar atual do jogo.'
                );
            }

            $home = (int)$homeScore;
            $away = (int)$awayScore;

            if (
                $home < 0
                || $away < 0
                || $home > 99
                || $away > 99
            ) {
                throw new InvalidArgumentException(
                    'Informe um placar válido entre 0 e 99.'
                );
            }
        }

        $finishedAt = null;

        if ($status === 'Finalizado') {
            $finishedAt = !empty($event['finalizadoEm'])
                ? (string)$event['finalizadoEm']
                : date('Y-m-d H:i:s');
        }

        Database::connection()->prepare(
            'UPDATE palpites_eventos
             SET status_jogo=:s,
                 placar_casa=:c,
                 placar_visitante=:v,
                 finalizadoEm=:f,
                 atualizadoEm=NOW()
             WHERE idEventoPalpite=:id'
        )->execute([
            ':s' => $status,
            ':c' => $home,
            ':v' => $away,
            ':f' => $finishedAt,
            ':id' => $eventId,
        ]);
    }

    public static function predictionScore(
        string $prediction,
        array $event
    ): ?array {
        $prediction = trim($prediction);

        if ($prediction === '') {
            return null;
        }

        /*
         * Primeiro tenta reconhecer:
         * "Brasil 2 x 1 Escócia"
         * "Escócia 1 x 0 Brasil"
         *
         * Se a ordem das equipes estiver invertida no texto, o placar
         * é convertido para a ordem equipe_casa/equipe_visitante.
         */
        if (
            preg_match(
                '/^\s*(.*?)\s+(\d{1,2})\s*(?:x|×)\s*(\d{1,2})\s+(.*?)\s*$/iu',
                $prediction,
                $m
            )
        ) {
            $left = self::normalizeTeam((string)$m[1]);
            $right = self::normalizeTeam((string)$m[4]);
            $homeName = self::normalizeTeam(
                (string)($event['equipe_casa'] ?? '')
            );
            $awayName = self::normalizeTeam(
                (string)($event['equipe_visitante'] ?? '')
            );

            $leftScore = (int)$m[2];
            $rightScore = (int)$m[3];

            if (
                self::teamMatches($left, $awayName)
                && self::teamMatches($right, $homeName)
            ) {
                return [
                    'casa' => $rightScore,
                    'visitante' => $leftScore,
                ];
            }

            return [
                'casa' => $leftScore,
                'visitante' => $rightScore,
            ];
        }

        /*
         * Fallback para "2x1", "2 x 1" ou texto personalizado que
         * contenha somente o placar sem os nomes das equipes.
         */
        if (
            preg_match(
                '/(\d{1,2})\s*(?:x|×)\s*(\d{1,2})/iu',
                $prediction,
                $m
            )
        ) {
            return [
                'casa' => (int)$m[1],
                'visitante' => (int)$m[2],
            ];
        }

        return null;
    }

    public static function annotateEntries(
        array $entries,
        array $event
    ): array {
        $hasScore = $event['placar_casa'] !== null
            && $event['placar_visitante'] !== null;

        $home = $hasScore
            ? (int)$event['placar_casa']
            : null;

        $away = $hasScore
            ? (int)$event['placar_visitante']
            : null;

        $finished = ($event['status_jogo'] ?? 'Agendado')
            === 'Finalizado';

        foreach ($entries as &$entry) {
            $score = self::predictionScore(
                (string)($entry['palpite'] ?? ''),
                $event
            );

            $entry['_placarPalpite'] = $score;
            $entry['_pago'] = (
                ($entry['pagamentoStatus'] ?? $entry['statusPagamento'] ?? '')
                === 'Pago'
            );

            $entry['_acertando'] = $hasScore
                && $score !== null
                && $score['casa'] === $home
                && $score['visitante'] === $away;

            /*
             * Ganhador = acertou o placar FINAL e está com pagamento Pago.
             */
            $entry['_ganhador'] = $finished
                && $entry['_acertando']
                && $entry['_pago'];
        }
        unset($entry);

        usort(
            $entries,
            static function (array $a, array $b) use ($finished): int {
                if ($finished) {
                    $cmp = ((int)$b['_ganhador'])
                        <=> ((int)$a['_ganhador']);

                    if ($cmp !== 0) {
                        return $cmp;
                    }
                } else {
                    $cmp = ((int)$b['_acertando'])
                        <=> ((int)$a['_acertando']);

                    if ($cmp !== 0) {
                        return $cmp;
                    }
                }

                $cmp = ((int)$b['_pago'])
                    <=> ((int)$a['_pago']);

                if ($cmp !== 0) {
                    return $cmp;
                }

                return strcmp(
                    (string)($b['criadoEm'] ?? ''),
                    (string)($a['criadoEm'] ?? '')
                );
            }
        );

        return $entries;
    }

    public static function filterAnnotatedEntries(
        array $entries,
        string $filter
    ): array {
        return array_values(
            array_filter(
                $entries,
                static function (array $entry) use ($filter): bool {
                    return match ($filter) {
                        'acertando' => !empty($entry['_acertando']),
                        'errando' => empty($entry['_acertando']),
                        'pagos' => !empty($entry['_pago']),
                        'pendentes' => empty($entry['_pago']),
                        'ganhadores' => !empty($entry['_ganhador']),
                        default => true,
                    };
                }
            )
        );
    }

    public static function gameStatusLabel(string $status): string
    {
        return match ($status) {
            'EmAndamento' => 'Em andamento',
            'Finalizado' => 'Finalizado',
            default => 'Agendado',
        };
    }

    private static function normalizeTeam(string $value): string
    {
        $value = trim(mb_strtolower($value, 'UTF-8'));

        $ascii = iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            $value
        );

        if ($ascii !== false) {
            $value = strtolower($ascii);
        }

        return trim(
            preg_replace('/[^a-z0-9]+/', ' ', $value)
            ?? $value
        );
    }

    private static function teamMatches(
        string $candidate,
        string $team
    ): bool {
        if ($candidate === '' || $team === '') {
            return false;
        }

        return $candidate === $team
            || str_contains($candidate, $team)
            || str_contains($team, $candidate);
    }

    private static function slugExists(string $slug, int $ignore = 0): bool
    {
        $sql = 'SELECT 1 FROM palpites_eventos WHERE slug=:s';
        $params = [':s' => $slug];
        if ($ignore > 0) {
            $sql .= ' AND idEventoPalpite<>:i';
            $params[':i'] = $ignore;
        }
        $sql .= ' LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetchColumn();
    }

    private static function dt(mixed $value): ?string
    {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }
        $value = str_replace('T', ' ', $value);
        return strlen($value) === 16 ? $value . ':00' : $value;
    }
}
