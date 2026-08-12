<?php
require_once dirname(__DIR__, 2) . '/bootstrap.php';

Auth::require();

$pageTitle = 'Notificações Asaas';
$error = '';
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!Support::checkCsrf($_POST['_csrf'] ?? null)) {
            throw new RuntimeException('Sessão expirada.');
        }

        $asaas = new AsaasService();

        $items = Database::connection()->query(
            "SELECT idDoador,nome,cpf,asaasCustomerId
             FROM doadores
             WHERE asaasCustomerId IS NOT NULL
               AND asaasCustomerId <> ''
             ORDER BY idDoador"
        )->fetchAll();

        $total = count($items);
        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($items as $item) {
            try {
                $asaas->disableCustomerNotifications(
                    (string)$item['asaasCustomerId']
                );
                $success++;
            } catch (Throwable $e) {
                $failed++;
                $errors[] = [
                    'nome' => (string)$item['nome'],
                    'cpf' => (string)$item['cpf'],
                    'erro' => $e->getMessage(),
                ];
            }
        }

        $result = [
            'total' => $total,
            'sucesso' => $success,
            'falhas' => $failed,
            'erros' => $errors,
        ];
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

require dirname(__DIR__) . '/_header.php';
?>

<?php if ($error): ?>
    <div class="alert error"><?= Support::e($error) ?></div>
<?php endif; ?>

<div class="panel">
    <h2>Notificações automáticas do Asaas</h2>

    <p>
        O Checkout envia seus próprios e-mails quando a cobrança é criada
        e quando o pagamento é aprovado. Por isso, as notificações padrão
        de cobrança do Asaas ficam desabilitadas.
    </p>

    <div class="alert muted">
        <strong>Configuração:</strong>
        <code>notificationDisabled = true</code>
    </div>

    <p>
        Novos clientes já são criados dessa forma. Clientes existentes são
        atualizados automaticamente antes de uma nova cobrança.
    </p>

    <p>
        Para aplicar a configuração imediatamente a todos os clientes Asaas
        já vinculados ao Checkout, use o botão abaixo.
    </p>

    <form method="post">
        <input
            type="hidden"
            name="_csrf"
            value="<?= Support::csrf() ?>"
        >

        <button class="btn primary" type="submit">
            Desabilitar notificações dos clientes existentes
        </button>
    </form>
</div>

<?php if ($result): ?>
    <div class="panel">
        <h2>Resultado</h2>

        <div class="stats">
            <div class="stat">
                <small>Clientes</small>
                <strong><?= (int)$result['total'] ?></strong>
            </div>

            <div class="stat">
                <small>Atualizados</small>
                <strong><?= (int)$result['sucesso'] ?></strong>
            </div>

            <div class="stat">
                <small>Falhas</small>
                <strong><?= (int)$result['falhas'] ?></strong>
            </div>
        </div>

        <?php if ($result['erros']): ?>
            <div class="tablewrap">
                <table>
                    <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Erro</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($result['erros'] as $item): ?>
                        <tr>
                            <td><?= Support::e($item['nome']) ?></td>
                            <td><?= Support::e($item['cpf']) ?></td>
                            <td><?= Support::e($item['erro']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert success">
                Todas as notificações padrão de cobrança foram desabilitadas.
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require dirname(__DIR__) . '/_footer.php'; ?>
