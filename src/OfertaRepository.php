<?php

declare(strict_types=1);

final class OfertaRepository
{
    public const CATEGORIES = [
        'Local',
        'Sinodal',
        'Nacional',
        'Especial',
    ];

    public static function categories(): array
    {
        return self::CATEGORIES;
    }


    /**
     * Retorna até $limit Ofertas que estão abertas em algum momento
     * do mês informado.
     */
    public static function activeForMonth(
        int $year,
        int $month,
        int $limit = 4
    ): array {
        $limit = max(1, min(20, $limit));

        $monthStart = sprintf(
            '%04d-%02d-01 00:00:00',
            $year,
            $month
        );

        $nextMonth = (new DateTimeImmutable($monthStart))
            ->modify('first day of next month')
            ->format('Y-m-d H:i:s');

        /*
         * "Ofertas do mês" significa: a data inicial pertence ao mês
         * corrente, mesmo que o dia de início já tenha passado.
         *
         * Ex.: em 11/08 uma Oferta iniciada em 08/08 continua em
         * "Agosto", e não vai para "Ofertas anteriores".
         *
         * Ofertas já encerradas continuam fora do index.
         */
        $stmt = Database::connection()->prepare(
            "SELECT o.*
             FROM ofertas o
             WHERE o.ativo=1
               AND o.data_inicio IS NOT NULL
               AND o.data_inicio >= :inicioMes
               AND o.data_inicio < :proximoMes
               AND (
                    o.data_fim IS NULL
                    OR o.data_fim > NOW()
               )
             ORDER BY o.data_inicio ASC,o.idOferta ASC
             LIMIT {$limit}"
        );

        $stmt->execute([
            ':inicioMes' => $monthStart,
            ':proximoMes' => $nextMonth,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Próximas Ofertas após o fim do mês atual.
     * Ofertas já exibidas na primeira seção podem ser excluídas.
     */
    public static function upcomingAfterMonth(
        int $year,
        int $month,
        array $excludeIds = [],
        int $limit = 4
    ): array {
        $limit = max(1, min(50, $limit));

        $monthStart = sprintf(
            '%04d-%02d-01 00:00:00',
            $year,
            $month
        );

        /*
         * A seção "Próximas ofertas" mostra tudo que ainda vai começar
         * a partir de agora e que não entrou entre as quatro Ofertas do mês.
         *
         * Isso inclui:
         * - ofertas restantes do próprio mês;
         * - ofertas dos meses seguintes.
         */
        $conditions = [
            'o.ativo=1',
            'o.data_inicio IS NOT NULL',
            'o.data_inicio >= NOW()',
            '(o.data_fim IS NULL OR o.data_fim > NOW())',
        ];

        $params = [];

        $ids = array_values(
            array_unique(
                array_filter(
                    array_map(
                        'intval',
                        $excludeIds
                    ),
                    static fn (int $id): bool =>
                        $id > 0
                )
            )
        );

        if ($ids) {
            $placeholders = [];

            foreach ($ids as $index => $id) {
                $key = ':exclude' . $index;
                $placeholders[] = $key;
                $params[$key] = $id;
            }

            $conditions[] =
                'o.idOferta NOT IN ('
                . implode(',', $placeholders)
                . ')';
        }

        $stmt = Database::connection()->prepare(
            "SELECT o.*
             FROM ofertas o
             WHERE " . implode(' AND ', $conditions) . "
             ORDER BY o.data_inicio ASC,o.idOferta ASC
             LIMIT {$limit}"
        );

        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Caso não haja Ofertas futuras com data de início, retorna outras
     * Ofertas ativas que ainda não estejam na seção principal.
     */
    public static function otherActive(
        array $excludeIds = [],
        int $limit = 12
    ): array {
        $limit = max(1, min(50, $limit));

        $conditions = [
            'o.ativo=1',
            '(o.data_inicio IS NULL OR o.data_inicio <= NOW())',
            '(o.data_fim IS NULL OR o.data_fim > NOW())',
        ];

        $params = [];

        $ids = array_values(
            array_unique(
                array_filter(
                    array_map(
                        'intval',
                        $excludeIds
                    ),
                    static fn (int $id): bool =>
                        $id > 0
                )
            )
        );

        if ($ids) {
            $placeholders = [];

            foreach ($ids as $index => $id) {
                $key = ':other' . $index;
                $placeholders[] = $key;
                $params[$key] = $id;
            }

            $conditions[] =
                'o.idOferta NOT IN ('
                . implode(',', $placeholders)
                . ')';
        }

        $stmt = Database::connection()->prepare(
            "SELECT o.*
             FROM ofertas o
             WHERE " . implode(' AND ', $conditions) . "
             ORDER BY
                CASE
                    WHEN o.data_fim IS NULL THEN 1
                    ELSE 0
                END,
                o.data_fim ASC,
                o.idOferta DESC
             LIMIT {$limit}"
        );

        $stmt->execute($params);

        return $stmt->fetchAll();
    }


    /**
     * Ofertas anteriores:
     * qualquer Oferta cuja data inicial já passou.
     *
     * A Oferta pode ainda estar ativa para acesso direto/pagamento;
     * esta classificação serve apenas para a organização do index.
     */
    public static function previousOffers(
        int $year,
        int $month,
        array $excludeIds = [],
        int $limit = 12
    ): array {
        $limit = max(1, min(50, $limit));

        $monthStart = sprintf(
            '%04d-%02d-01 00:00:00',
            $year,
            $month
        );

        /*
         * "Ofertas anteriores" são as que começaram ANTES do primeiro
         * dia do mês atual.
         *
         * Ex.: em agosto:
         * - 08/08 -> continua em "Agosto";
         * - 01/07 -> vai para "Ofertas anteriores".
         *
         * Como em todo o index, ofertas já encerradas ficam ocultas.
         */
        $conditions = [
            'o.ativo=1',
            'o.data_inicio IS NOT NULL',
            'o.data_inicio < :inicioMes',
            '(o.data_fim IS NULL OR o.data_fim > NOW())',
        ];

        $params = [
            ':inicioMes' => $monthStart,
        ];

        $ids = array_values(
            array_unique(
                array_filter(
                    array_map(
                        'intval',
                        $excludeIds
                    ),
                    static fn (int $id): bool =>
                        $id > 0
                )
            )
        );

        if ($ids) {
            $placeholders = [];

            foreach ($ids as $index => $id) {
                $key = ':previous' . $index;
                $placeholders[] = $key;
                $params[$key] = $id;
            }

            $conditions[] =
                'o.idOferta NOT IN ('
                . implode(',', $placeholders)
                . ')';
        }

        $stmt = Database::connection()->prepare(
            "SELECT o.*
             FROM ofertas o
             WHERE " . implode(' AND ', $conditions) . "
             ORDER BY o.data_inicio DESC,o.idOferta DESC
             LIMIT {$limit}"
        );

        $stmt->execute($params);

        return $stmt->fetchAll();
    }


    /**
     * Anos disponíveis no histórico público de Ofertas anteriores.
     */
    public static function previousYears(): array
    {
        $stmt = Database::connection()->query(
            "SELECT DISTINCT YEAR(data_inicio) AS ano
             FROM ofertas
             WHERE ativo=1
               AND data_inicio IS NOT NULL
               AND data_inicio < DATE_FORMAT(NOW(),'%Y-%m-01 00:00:00')
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

    /**
     * Histórico público paginado das Ofertas anteriores.
     *
     * Filtros suportados:
     * - ano
     * - categoria
     */
    public static function previousFiltered(
        int $year,
        int $month,
        ?int $filterYear = null,
        ?string $category = null,
        int $page = 1,
        int $perPage = 12
    ): array {
        $monthStart = sprintf(
            '%04d-%02d-01 00:00:00',
            $year,
            $month
        );

        $page = max(1, $page);
        $perPage = max(4, min(48, $perPage));

        $conditions = [
            'o.ativo=1',
            'o.data_inicio IS NOT NULL',
            'o.data_inicio < :inicioMes',
        ];

        $params = [
            ':inicioMes' => $monthStart,
        ];

        if (
            $filterYear !== null
            && $filterYear >= 2000
            && $filterYear <= 2100
        ) {
            $conditions[] = 'YEAR(o.data_inicio)=:ano';
            $params[':ano'] = $filterYear;
        }

        if (
            $category !== null
            && in_array($category, self::CATEGORIES, true)
        ) {
            $conditions[] = 'o.categoria=:categoria';
            $params[':categoria'] = $category;
        }

        $where = implode(' AND ', $conditions);

        $countStmt = Database::connection()->prepare(
            "SELECT COUNT(*)
             FROM ofertas o
             WHERE {$where}"
        );

        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $totalPages = max(
            1,
            (int)ceil($total / $perPage)
        );

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;

        $stmt = Database::connection()->prepare(
            "SELECT o.*
             FROM ofertas o
             WHERE {$where}
             ORDER BY o.data_inicio DESC,o.idOferta DESC
             LIMIT {$perPage}
             OFFSET {$offset}"
        );

        $stmt->execute($params);

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
        ];
    }

    public static function categoryLabel(
        mixed $category
    ): string {
        $category = trim((string)$category);

        return in_array(
            $category,
            self::CATEGORIES,
            true
        )
            ? $category
            : 'Local';
    }

    public static function active(): array
    {
        /*
         * Para Ofertas, data_inicio organiza a exibição no index,
         * mas NÃO bloqueia o recebimento antecipado de doações.
         *
         * Uma Oferta ativa pode receber doações antes da data inicial.
         * Somente data_fim encerra o recebimento.
         */
        $sql = "SELECT o.*,
                    (SELECT MIN(valor)
                     FROM ofertas_valores v
                     WHERE v.idOferta=o.idOferta
                       AND v.ativo=1) AS menorValor
                FROM ofertas o
                WHERE o.ativo=1
                  AND (o.data_fim IS NULL OR o.data_fim > NOW())
                ORDER BY o.criadoEm DESC";

        return Database::connection()
            ->query($sql)
            ->fetchAll();
    }

    public static function all(
        ?int $year = null
    ): array {
        $sql = 'SELECT * FROM ofertas';
        $params = [];

        if ($year !== null && $year >= 2000 && $year <= 2100) {
            $sql .= ' WHERE YEAR(COALESCE(data_inicio,criadoEm))=:ano';
            $params[':ano'] = $year;
        }

        $sql .= ' ORDER BY COALESCE(data_inicio,criadoEm) DESC, idOferta DESC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function years(): array
    {
        $stmt = Database::connection()->query(
            "SELECT DISTINCT
                YEAR(COALESCE(data_inicio,criadoEm)) AS ano
             FROM ofertas
             WHERE COALESCE(data_inicio,criadoEm) IS NOT NULL
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

    public static function bySlug(
        string $slug,
        bool $onlyActive = true
    ): ?array {
        $sql = 'SELECT * FROM ofertas WHERE slug=:slug';

        if ($onlyActive) {
            /*
             * data_inicio não bloqueia o acesso público.
             * Ela serve para classificar a Oferta no index.
             */
            $sql .= "
                AND ativo=1
                AND (
                    data_fim IS NULL
                    OR data_fim > NOW()
                )
            ";
        }

        $sql .= ' LIMIT 1';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            ':slug' => $slug,
        ]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt=Database::connection()->prepare('SELECT * FROM ofertas WHERE idOferta=:id LIMIT 1'); $stmt->execute([':id'=>$id]); $r=$stmt->fetch(); return $r?:null;
    }

    public static function values(int $id): array
    {
        $stmt=Database::connection()->prepare('SELECT * FROM ofertas_valores WHERE idOferta=:id AND ativo=1 ORDER BY ordem, valor'); $stmt->execute([':id'=>$id]); return $stmt->fetchAll();
    }

    public static function save(array $d): int
    {
        $db=Database::connection();
        $id=(int)($d['idOferta']??0);
        $min=max(APP_MIN_OFFER, Support::decimal($d['valor_minimo']??APP_MIN_OFFER));

        $titulo=trim((string)($d['titulo']??''));
        if ($titulo==='') {
            throw new InvalidArgumentException('Informe o título da oferta.');
        }

        $categoria=trim((string)($d['categoria']??''));
        if (!in_array($categoria,self::CATEGORIES,true)) {
            throw new InvalidArgumentException(
                'Selecione uma categoria válida para a oferta.'
            );
        }

        /*
         * O slug da Oferta é sempre gerado pelo nome/título.
         * Ex.: "Missão no Sínodo Mato Grosso"
         *      -> missao-no-sinodo-mato-grosso
         */
        $slug=Support::slug($titulo);
        if ($slug==='') {
            throw new InvalidArgumentException('Não foi possível gerar o endereço da oferta.');
        }

        $base=$slug; $n=1;
        while (self::slugExists($slug,$id)) $slug=$base.'-'.(++$n);

        $params=[
            ':titulo'=>$titulo, ':slug'=>$slug, ':categoria'=>$categoria,
            ':descricao'=>trim((string)($d['descricao']??''))?:null,
            ':imagem'=>$d['imagem']??null, ':data_inicio'=>self::dt($d['data_inicio']??null), ':data_fim'=>self::dt($d['data_fim']??null),
            ':valor_minimo'=>$min, ':permitir_valor_livre'=>!empty($d['permitir_valor_livre'])?1:0,
            ':pix_ativo'=>!empty($d['pix_ativo'])?1:0, ':cartao_ativo'=>!empty($d['cartao_ativo'])?1:0, ':boleto_ativo'=>!empty($d['boleto_ativo'])?1:0, ':ativo'=>!empty($d['ativo'])?1:0,
        ];
        if (!$params[':pix_ativo'] && !$params[':cartao_ativo'] && !$params[':boleto_ativo']) throw new InvalidArgumentException('Habilite PIX, Cartão de Crédito e/ou Boleto.');
        $db->beginTransaction();
        try {
            if ($id>0) {
                $params[':id']=$id;
                $db->prepare('UPDATE ofertas SET titulo=:titulo,slug=:slug,categoria=:categoria,descricao=:descricao,imagem=:imagem,data_inicio=:data_inicio,data_fim=:data_fim,valor_minimo=:valor_minimo,permitir_valor_livre=:permitir_valor_livre,pix_ativo=:pix_ativo,cartao_ativo=:cartao_ativo,boleto_ativo=:boleto_ativo,ativo=:ativo WHERE idOferta=:id')->execute($params);
            } else {
                $db->prepare('INSERT INTO ofertas (titulo,slug,categoria,descricao,imagem,data_inicio,data_fim,valor_minimo,permitir_valor_livre,pix_ativo,cartao_ativo,boleto_ativo,ativo) VALUES (:titulo,:slug,:categoria,:descricao,:imagem,:data_inicio,:data_fim,:valor_minimo,:permitir_valor_livre,:pix_ativo,:cartao_ativo,:boleto_ativo,:ativo)')->execute($params);
                $id=(int)$db->lastInsertId();
            }
            $db->prepare('DELETE FROM ofertas_valores WHERE idOferta=:id')->execute([':id'=>$id]);
            $values=$d['valores']??[]; $order=0;
            foreach ($values as $v) {
                $v=Support::decimal($v); if ($v<APP_MIN_OFFER) continue;
                $db->prepare('INSERT INTO ofertas_valores (idOferta,valor,ordem,ativo) VALUES (:id,:v,:o,1)')->execute([':id'=>$id,':v'=>$v,':o'=>$order++]);
            }
            /*
             * O link curto é criado dentro da mesma transação.
             * Assim toda Oferta salva já possui um código único.
             */
            ShortUrlService::ensure(
                ShortUrlService::TYPE_OFFER,
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

    private static function slugExists(string $slug,int $ignore=0): bool
    {
        $sql='SELECT 1 FROM ofertas WHERE slug=:s'; $p=[':s'=>$slug]; if($ignore>0){$sql.=' AND idOferta<>:i';$p[':i']=$ignore;} $sql.=' LIMIT 1';
        $st=Database::connection()->prepare($sql);$st->execute($p);return(bool)$st->fetchColumn();
    }
    private static function dt(mixed $v): ?string { $v=trim((string)$v); if($v==='')return null; $v=str_replace('T',' ',$v); return strlen($v)===16?$v.':00':$v; }
}
