<?php
require_once dirname(__DIR__, 2) . '/bootstrap.php';

Auth::require();

$pageTitle = 'Configuração de E-mail';
$error = '';
$settings = EmailSettings::get();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!Support::checkCsrf($_POST['_csrf'] ?? null)) {
            throw new RuntimeException('Sessão expirada.');
        }

        $action = (string)($_POST['acao'] ?? 'salvar');

        if ($action === 'salvar') {
            $fromName = trim(
                (string)($_POST['remetente_nome'] ?? '')
            );

            $fromEmail = strtolower(
                trim((string)($_POST['remetente_email'] ?? ''))
            );

            $replyTo = strtolower(
                trim((string)($_POST['reply_to'] ?? ''))
            );

            if ($fromName === '') {
                throw new RuntimeException(
                    'Informe o nome do remetente.'
                );
            }

            if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException(
                    'Informe um e-mail de remetente válido.'
                );
            }

            if (
                $replyTo !== ''
                && !filter_var($replyTo, FILTER_VALIDATE_EMAIL)
            ) {
                throw new RuntimeException(
                    'Informe um Reply-To válido.'
                );
            }

            Database::connection()->prepare(
                'UPDATE configuracoes_email SET
                    ativo=:a,
                    rastrear_abertura=:ra,
                    remetente_nome=:n,
                    remetente_email=:e,
                    reply_to=:r
                 WHERE idConfiguracao=1'
            )->execute([
                ':a' => !empty($_POST['ativo']) ? 1 : 0,
                ':ra' =>
                    !empty($_POST['rastrear_abertura'])
                        ? 1
                        : 0,
                ':n' => $fromName,
                ':e' => $fromEmail,
                ':r' => $replyTo !== '' ? $replyTo : null,
            ]);

            Support::flash(
                'success',
                'Configuração de e-mail salva.'
            );

            Support::redirect(
                '/admin/configuracoes/email.php'
            );
        }

        if ($action === 'teste') {
            $to = strtolower(
                trim((string)($_POST['email_teste'] ?? ''))
            );

            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException(
                    'Informe um e-mail válido para o teste.'
                );
            }

            EmailService::queueUnique(
                null,
                'Teste',
                $to,
                'Teste de e-mail - Checkout IECLB Parobé',
                '<!doctype html><html><body style="font-family:Arial,sans-serif">'
                . '<h2>Teste de e-mail</h2>'
                . '<p>O envio de e-mail do Checkout IECLB Parobé está funcionando.</p>'
                . '<p><strong>Data:</strong> '
                . Support::e(date('d/m/Y H:i:s'))
                . '</p></body></html>'
            );

            Support::flash(
                'success',
                'E-mail de teste colocado na fila e processado.'
            );

            Support::redirect(
                '/admin/configuracoes/email.php'
            );
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$settings = EmailSettings::get();

$logs = Database::connection()->query(
    "SELECT *
     FROM emails_envios
     ORDER BY criadoEm DESC
     LIMIT 50"
)->fetchAll();

$emailStats = Database::connection()->query(
    "SELECT
        SUM(
            status='Enviado'
            AND rastreamento_token IS NOT NULL
        ) AS rastreaveis,
        SUM(
            status='Enviado'
            AND rastreamento_token IS NOT NULL
            AND abertoEm IS NOT NULL
        ) AS abertos
     FROM emails_envios"
)->fetch() ?: [];

$trackable = (int)(
    $emailStats['rastreaveis']
    ?? 0
);

$opened = (int)(
    $emailStats['abertos']
    ?? 0
);

$openRate = $trackable > 0
    ? round(
        ($opened / $trackable) * 100,
        1
    )
    : 0.0;

require dirname(__DIR__) . '/_header.php';
?>

<?php if ($error): ?>
    <div class="alert error"><?= Support::e($error) ?></div>
<?php endif; ?>

<div class="panel">
    <h2>Envio de e-mails</h2>

    <p>
        O Checkout envia um e-mail ao criar a oferta/palpite e outro
        quando o pagamento é aprovado. O envio utiliza o serviço
        <code>mail()</code> do PHP da hospedagem.
    </p>

    <form method="post">
        <input
            type="hidden"
            name="_csrf"
            value="<?= Support::csrf() ?>"
        >
        <input type="hidden" name="acao" value="salvar">

        <div class="checks">
            <label>
                <input
                    type="checkbox"
                    name="ativo"
                    value="1"
                    <?= !empty($settings['ativo']) ? 'checked' : '' ?>
                >
                Envio de e-mail ativo
            </label>

            <label>
                <input
                    type="checkbox"
                    name="rastrear_abertura"
                    value="1"
                    <?= !empty($settings['rastrear_abertura']) ? 'checked' : '' ?>
                >
                Rastrear abertura dos e-mails enviados
            </label>
        </div>

        <div class="email-tracking-note">
            <strong>Como funciona o rastreamento</strong>

            <p>
                Cada e-mail recebe uma imagem transparente exclusiva.
                Quando essa imagem é carregada, o Checkout registra a
                primeira abertura, a última abertura e a quantidade de
                carregamentos. Não são armazenados IP nem User-Agent.
            </p>

            <p>
                O resultado é indicativo: bloqueio de imagens, Apple Mail
                Privacy Protection e proxies de Gmail/Outlook podem impedir
                ou antecipar o registro de uma abertura.
            </p>
        </div>

        <div class="grid2">
            <label>
                Nome do remetente
                <input
                    name="remetente_nome"
                    value="<?= Support::e($settings['remetente_nome'] ?? '') ?>"
                    required
                >
            </label>

            <label>
                E-mail do remetente
                <input
                    type="email"
                    name="remetente_email"
                    value="<?= Support::e($settings['remetente_email'] ?? '') ?>"
                    required
                >
            </label>

            <label class="full">
                Responder para (Reply-To)
                <input
                    type="email"
                    name="reply_to"
                    value="<?= Support::e($settings['reply_to'] ?? '') ?>"
                >
            </label>
        </div>

        <button class="btn primary" type="submit">
            Salvar configuração
        </button>
    </form>

    <hr>

    <h3>Enviar e-mail de teste</h3>

    <form method="post" class="actions">
        <input
            type="hidden"
            name="_csrf"
            value="<?= Support::csrf() ?>"
        >
        <input type="hidden" name="acao" value="teste">

        <input
            type="email"
            name="email_teste"
            placeholder="seu@email.com"
            required
        >

        <button class="btn" type="submit">
            Enviar teste
        </button>
    </form>
</div>

<div class="email-open-stats">
    <div class="panel email-open-stat">
        <small>E-mails rastreáveis enviados</small>
        <strong><?= $trackable ?></strong>
    </div>

    <div class="panel email-open-stat">
        <small>Com abertura registrada</small>
        <strong><?= $opened ?></strong>
    </div>

    <div class="panel email-open-stat">
        <small>Taxa de abertura registrada</small>
        <strong>
            <?= Support::e(
                number_format(
                    $openRate,
                    1,
                    ',',
                    '.'
                )
            ) ?>%
        </strong>
    </div>
</div>

<div class="panel tablewrap">
    <h2>Últimos envios</h2>

    <table>
        <thead>
        <tr>
            <th>Data</th>
            <th>Tipo</th>
            <th>Destinatário</th>
            <th>Assunto</th>
            <th>Status</th>
            <th>Abertura</th>
            <th>Tentativas</th>
            <th>Erro</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= Support::e($log['criadoEm']) ?></td>
                <td><?= Support::e($log['tipo']) ?></td>
                <td><?= Support::e($log['destinatario']) ?></td>
                <td><?= Support::e($log['assunto']) ?></td>
                <td>
                    <span class="badge <?= $log['status'] === 'Enviado' ? 'paid' : 'muted' ?>">
                        <?= Support::e($log['status']) ?>
                    </span>
                </td>

                <td>
                    <?php if (!empty($log['abertoEm'])): ?>
                        <span class="badge paid">
                            Aberto
                        </span>

                        <br>

                        <small>
                            1ª:
                            <?= Support::e(
                                $log['abertoEm']
                            ) ?>
                        </small>

                        <?php if ((int)($log['totalAberturas'] ?? 0) > 1): ?>
                            <br>
                            <small>
                                Última:
                                <?= Support::e(
                                    $log['ultimaAberturaEm']
                                    ?? $log['abertoEm']
                                ) ?>
                            </small>
                        <?php endif; ?>

                        <br>

                        <small>
                            Carregamentos:
                            <?= (int)(
                                $log['totalAberturas']
                                ?? 0
                            ) ?>
                        </small>
                    <?php elseif (!empty($log['rastreamento_token'])): ?>
                        <span class="badge muted">
                            Não aberto
                        </span>
                    <?php else: ?>
                        <span class="badge muted">
                            Sem rastreamento
                        </span>
                    <?php endif; ?>
                </td>

                <td><?= (int)$log['tentativas'] ?></td>
                <td>
                    <small><?= Support::e($log['ultimoErro'] ?? '') ?></small>
                </td>
            </tr>
        <?php endforeach; ?>

        <?php if (!$logs): ?>
            <tr>
                <td colspan="8">Nenhum envio registrado.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require dirname(__DIR__) . '/_footer.php'; ?>
