<?php
require_once dirname(__DIR__, 2) . '/bootstrap.php';
Auth::require();

$id = (int)($_GET['id'] ?? $_POST['idEventoPalpite'] ?? 0);
$event = $id ? PalpiteRepository::find($id) : null;
$pageTitle = $id ? 'Editar palpite' : 'Novo palpite';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!Support::checkCsrf($_POST['_csrf'] ?? null)) {
            throw new RuntimeException('Sessão expirada.');
        }

        $image = $event['imagem'] ?? null;

        if (
            isset($_FILES['imagem'])
            && $_FILES['imagem']['error'] === UPLOAD_ERR_OK
        ) {
            if ($_FILES['imagem']['size'] > 5 * 1024 * 1024) {
                throw new RuntimeException('A imagem deve ter no máximo 5 MB.');
            }

            $info = new finfo(FILEINFO_MIME_TYPE);
            $mime = $info->file($_FILES['imagem']['tmp_name']);
            $map = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ];

            if (!isset($map[$mime])) {
                throw new RuntimeException('Use imagem JPG, PNG ou WEBP.');
            }

            $dir = dirname(__DIR__, 2) . '/uploads/palpites';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $filename = date('YmdHis')
                . '-'
                . bin2hex(random_bytes(5))
                . '.'
                . $map[$mime];

            if (!move_uploaded_file(
                $_FILES['imagem']['tmp_name'],
                $dir . '/' . $filename
            )) {
                throw new RuntimeException('Falha ao salvar imagem.');
            }

            $image = 'uploads/palpites/' . $filename;
        }

        $values = preg_split(
            '/[,;\r\n]+/',
            (string)($_POST['valores_fixos'] ?? ''),
            -1,
            PREG_SPLIT_NO_EMPTY
        ) ?: [];

        $options = preg_split(
            '/\r\n|\r|\n/',
            (string)($_POST['opcoes_palpite'] ?? ''),
            -1,
            PREG_SPLIT_NO_EMPTY
        ) ?: [];

        $id = PalpiteRepository::save([
            'idEventoPalpite' => $id,
            'titulo' => $_POST['titulo'] ?? '',
            'descricao' => $_POST['descricao'] ?? '',
            'imagem' => $image,
            'equipe_casa' => $_POST['equipe_casa'] ?? '',
            'equipe_visitante' => $_POST['equipe_visitante'] ?? '',
            'data_jogo' => $_POST['data_jogo'] ?? null,
            'data_inicio' => $_POST['data_inicio'] ?? null,
            'data_fim' => $_POST['data_fim'] ?? null,
            'valor_minimo' => $_POST['valor_minimo'] ?? 10,
            'permitir_valor_livre' => $_POST['permitir_valor_livre'] ?? 0,
            'permitir_outro_palpite' => $_POST['permitir_outro_palpite'] ?? 0,
            'pix_ativo' => $_POST['pix_ativo'] ?? 0,
            'cartao_ativo' => $_POST['cartao_ativo'] ?? 0,
            'ativo' => $_POST['ativo'] ?? 0,
            'valores' => $values,
            'opcoes' => $options,
        ]);

        Support::flash('success', 'Formulário de palpite salvo com sucesso.');
        Support::redirect('/admin/palpites/form.php?id=' . $id);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$event = $id ? PalpiteRepository::find($id) : $event;
$values = $id
    ? array_column(PalpiteRepository::values($id), 'valor')
    : [];
$options = $id
    ? array_column(PalpiteRepository::options($id), 'rotulo')
    : [];

$shortUrl = $id
    ? ShortUrlService::urlFor(
        ShortUrlService::TYPE_PREDICTION,
        $id
    )
    : null;

require dirname(__DIR__) . '/_header.php';
?>

<?php if ($error): ?>
    <div class="alert error"><?= Support::e($error) ?></div>
<?php endif; ?>

<?php if ($shortUrl): ?>
    <div class="panel short-url-panel">
        <div>
            <span class="content-type-badge">Link curto</span>
            <h2>URL encurtada do Palpite</h2>
            <p>
                Este endereço é único e continuará funcionando mesmo
                se o título do Palpite for alterado.
            </p>
        </div>

        <div class="short-url-control">
            <input
                type="text"
                value="<?= Support::e($shortUrl) ?>"
                readonly
            >

            <button
                class="btn"
                type="button"
                data-copy-url="<?= Support::e($shortUrl) ?>"
            >
                Copiar link
            </button>

            <a
                class="btn primary"
                href="<?= Support::e($shortUrl) ?>"
                target="_blank"
            >
                Abrir
            </a>
        </div>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="panel">
    <input type="hidden" name="_csrf" value="<?= Support::csrf() ?>">
    <input type="hidden" name="idEventoPalpite" value="<?= $id ?>">

    <div class="grid2">
        <label class="full">
            Título*
            <input name="titulo"
                   value="<?= Support::e($event['titulo'] ?? '') ?>"
                   placeholder="Copa do Mundo da JEP - Brasil x Escócia"
                   required>
            <small>
                O endereço do palpite será gerado automaticamente pelo título.
                Ex.: copa-do-mundo-da-jep-brasil-x-escocia
            </small>
        </label>

        <label>
            Equipe 1*
            <input name="equipe_casa"
                   value="<?= Support::e($event['equipe_casa'] ?? '') ?>"
                   placeholder="Brasil"
                   required>
        </label>

        <label>
            Equipe 2*
            <input name="equipe_visitante"
                   value="<?= Support::e($event['equipe_visitante'] ?? '') ?>"
                   placeholder="Escócia"
                   required>
        </label>

        <label class="full">
            Data e hora do jogo*
            <input type="datetime-local"
                   name="data_jogo"
                   value="<?= !empty($event['data_jogo'])
                       ? Support::e(str_replace(' ', 'T', substr($event['data_jogo'], 0, 16)))
                       : '' ?>"
                   required>
        </label>

        <label>
            Início das participações
            <input type="datetime-local"
                   name="data_inicio"
                   value="<?= !empty($event['data_inicio'])
                       ? Support::e(str_replace(' ', 'T', substr($event['data_inicio'], 0, 16)))
                       : '' ?>">
        </label>

        <label>
            Encerramento das participações
            <input type="datetime-local"
                   name="data_fim"
                   value="<?= !empty($event['data_fim'])
                       ? Support::e(str_replace(' ', 'T', substr($event['data_fim'], 0, 16)))
                       : '' ?>">
            <small>Use este campo para impedir novos palpites antes do início do jogo.</small>
        </label>

        <label class="full">
            Descrição / regras
            <textarea name="descricao" rows="7"
                      placeholder="Dê o seu palpite e concorra!&#10;&#10;Escolha o placar..."><?= Support::e($event['descricao'] ?? '') ?></textarea>
        </label>

        <label class="full">
            Opções de palpite*
            <textarea name="opcoes_palpite" rows="12"
                      placeholder="Brasil 1 x 0 Escócia&#10;Brasil 2 x 0 Escócia&#10;Brasil 2 x 1 Escócia&#10;Escócia 1 x 0 Brasil&#10;Brasil 1 x 1 Escócia"
                      required><?= Support::e(implode("\n", $options)) ?></textarea>
            <small>Uma opção por linha. A opção “Outro” é controlada separadamente abaixo.</small>
        </label>

        <label>
            Valores fixos*
            <input name="valores_fixos"
                   value="<?= Support::e(implode(', ', array_map(
                       fn($v) => number_format((float)$v, 2, ',', ''),
                       $values
                   ))) ?>"
                   placeholder="10, 20, 30">
            <small>Separe por vírgula. O menor valor permitido é R$ 10,00.</small>
        </label>

        <label>
            Valor mínimo
            <input type="number"
                   step="0.01"
                   min="10"
                   name="valor_minimo"
                   value="<?= Support::e($event['valor_minimo'] ?? '10.00') ?>"
                   required>
        </label>

        <label class="full">
            Imagem
            <input type="file" name="imagem" accept="image/jpeg,image/png,image/webp">
        </label>
    </div>

    <div class="checks">
        <label>
            <input type="checkbox" name="permitir_outro_palpite" value="1"
                <?= !isset($event['permitir_outro_palpite']) || !empty($event['permitir_outro_palpite']) ? 'checked' : '' ?>>
            Permitir “Outro” palpite
        </label>

        <label>
            <input type="checkbox" name="permitir_valor_livre" value="1"
                <?= !empty($event['permitir_valor_livre']) ? 'checked' : '' ?>>
            Permitir outro valor
        </label>

        <label>
            <input type="checkbox" name="pix_ativo" value="1"
                <?= !isset($event['pix_ativo']) || !empty($event['pix_ativo']) ? 'checked' : '' ?>>
            PIX
        </label>

        <label>
            <input type="checkbox" name="cartao_ativo" value="1"
                <?= !isset($event['cartao_ativo']) || !empty($event['cartao_ativo']) ? 'checked' : '' ?>>
            Cartão de Crédito
        </label>

        <label>
            <input type="checkbox" name="ativo" value="1"
                <?= !isset($event['ativo']) || !empty($event['ativo']) ? 'checked' : '' ?>>
            Formulário ativo
        </label>
    </div>

    <button class="btn primary" type="submit">Salvar palpite</button>
</form>

<?php require dirname(__DIR__) . '/_footer.php'; ?>
