<?php

require_once dirname(__DIR__, 2)
    . '/bootstrap.php';

Auth::require();

$pageTitle = 'Configurações do site';
$error = '';

$current = SiteSettings::get();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!Support::checkCsrf($_POST['_csrf'] ?? null)) {
            throw new RuntimeException(
                'Sessão expirada.'
            );
        }

        $title = (string)(
            $_POST['titulo']
            ?? ''
        );

        $description = (string)(
            $_POST['descricao']
            ?? ''
        );

        $transparency = (string)(
            $_POST['transparencia_tipo']
            ?? 'Completa'
        );

        $favicon = SiteSettings::faviconPath();
        $changeFavicon = false;
        $oldFavicon = $favicon;

        if (!empty($_POST['remover_favicon'])) {
            $favicon = null;
            $changeFavicon = true;
        }

        if (
            isset($_FILES['favicon'])
            && ($_FILES['favicon']['error'] ?? UPLOAD_ERR_NO_FILE)
                !== UPLOAD_ERR_NO_FILE
        ) {
            if (
                ($_FILES['favicon']['error'] ?? UPLOAD_ERR_NO_FILE)
                !== UPLOAD_ERR_OK
            ) {
                throw new RuntimeException(
                    'Não foi possível receber o favicon.'
                );
            }

            $size = (int)(
                $_FILES['favicon']['size']
                ?? 0
            );

            if ($size <= 0 || $size > 1048576) {
                throw new RuntimeException(
                    'O favicon deve ter no máximo 1 MB.'
                );
            }

            $tmp = (string)(
                $_FILES['favicon']['tmp_name']
                ?? ''
            );

            $mime = (new finfo(
                FILEINFO_MIME_TYPE
            ))->file($tmp);

            $allowed = [
                'image/png' => 'png',
                'image/jpeg' => 'jpg',
                'image/webp' => 'webp',
                'image/x-icon' => 'ico',
                'image/vnd.microsoft.icon' => 'ico',
            ];

            if (
                !is_string($mime)
                || !isset($allowed[$mime])
            ) {
                throw new RuntimeException(
                    'Use um favicon PNG, JPG, WEBP ou ICO.'
                );
            }

            $directory =
                dirname(__DIR__, 2)
                . '/uploads/site';

            if (
                !is_dir($directory)
                && !mkdir(
                    $directory,
                    0755,
                    true
                )
                && !is_dir($directory)
            ) {
                throw new RuntimeException(
                    'Não foi possível criar a pasta do favicon.'
                );
            }

            $filename =
                'favicon-'
                . date('YmdHis')
                . '-'
                . bin2hex(
                    random_bytes(5)
                )
                . '.'
                . $allowed[$mime];

            $target =
                $directory
                . '/'
                . $filename;

            if (
                !move_uploaded_file(
                    $tmp,
                    $target
                )
            ) {
                throw new RuntimeException(
                    'Falha ao salvar o favicon.'
                );
            }

            $favicon =
                'uploads/site/'
                . $filename;

            $changeFavicon = true;
        }

        SiteSettings::save(
            $title,
            $description,
            $transparency,
            $favicon,
            $changeFavicon
        );

        if (
            $changeFavicon
            && $oldFavicon
            && $oldFavicon !== $favicon
            && str_starts_with(
                $oldFavicon,
                'uploads/site/'
            )
        ) {
            $oldFile =
                dirname(__DIR__, 2)
                . '/'
                . $oldFavicon;

            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        Support::flash(
            'success',
            'Configurações do site salvas.'
        );

        Support::redirect(
            '/admin/configuracoes/site.php'
        );
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$current = SiteSettings::get();

require dirname(__DIR__)
    . '/_header.php';
?>

<?php if ($error): ?>
    <div class="alert error">
        <?= Support::e($error) ?>
    </div>
<?php endif; ?>

<div class="panel">
    <span class="content-type-badge">
        Site
    </span>

    <h2>Informações gerais</h2>

    <p>
        Estas informações são usadas nas páginas públicas e nos
        metadados do Checkout.
    </p>

    <form
        method="post"
        enctype="multipart/form-data"
    >
        <input
            type="hidden"
            name="_csrf"
            value="<?= Support::csrf() ?>"
        >

        <label>
            Título do site*

            <input
                name="titulo"
                maxlength="160"
                value="<?= Support::e(
                    $current['titulo']
                    ?? ''
                ) ?>"
                required
            >

            <small>
                Exemplo: Checkout IECLB Parobé
            </small>
        </label>

        <label>
            Descrição do site*

            <textarea
                name="descricao"
                rows="3"
                maxlength="300"
                required
            ><?= Support::e(
                $current['descricao']
                ?? ''
            ) ?></textarea>

            <small>
                Utilizada principalmente na descrição das páginas
                para mecanismos de busca.
            </small>
        </label>

        <label>
            Favicon

            <input
                type="file"
                name="favicon"
                accept=".png,.jpg,.jpeg,.webp,.ico,image/png,image/jpeg,image/webp,image/x-icon,image/vnd.microsoft.icon"
            >

            <small>
                PNG, JPG, WEBP ou ICO. Máximo 1 MB.
                Recomenda-se imagem quadrada.
            </small>
        </label>

        <?php if (SiteSettings::faviconUrl()): ?>
            <div class="site-favicon-preview">
                <img
                    src="<?= Support::e(
                        SiteSettings::faviconUrl()
                    ) ?>"
                    alt="Favicon atual"
                >

                <div>
                    <strong>Favicon atual</strong>

                    <label class="site-remove-favicon">
                        <input
                            type="checkbox"
                            name="remover_favicon"
                            value="1"
                        >
                        Remover favicon
                    </label>
                </div>
            </div>
        <?php endif; ?>

        <label>
            Tipo de transparência

            <select name="transparencia_tipo">
                <?php
                $types = [
                    'Completa' =>
                        'Completa — mostra a explicação detalhada',
                    'Resumida' =>
                        'Resumida — mostra somente o aviso principal',
                    'Oculta' =>
                        'Oculta — não mostra o aviso de transparência',
                ];
                ?>

                <?php foreach ($types as $value => $label): ?>
                    <option
                        value="<?= Support::e($value) ?>"
                        <?= ($current['transparencia_tipo'] ?? 'Completa') === $value
                            ? 'selected'
                            : '' ?>
                    >
                        <?= Support::e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <div class="site-transparency-help">
            <strong>Completa</strong>
            <span>
                Mostra intermediador, parceria, tarifas e repasse
                integral do valor líquido.
            </span>

            <strong>Resumida</strong>
            <span>
                Mostra apenas que podem existir tarifas e que o valor
                líquido recebido é repassado integralmente.
            </span>

            <strong>Oculta</strong>
            <span>
                Não exibe a caixa de transparência nas páginas públicas,
                pagamento ou comprovante.
            </span>
        </div>

        <button
            class="btn primary"
            type="submit"
        >
            Salvar configurações
        </button>
    </form>
</div>

<?php
require dirname(__DIR__)
    . '/_footer.php';
?>
