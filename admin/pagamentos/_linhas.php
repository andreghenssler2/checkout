<?php
/** @var array $items */
?>
<?php foreach ($items as $payment): ?>
    <tr>
        <td>
            <?= Support::e(
                date(
                    'd/m/Y H:i',
                    strtotime(
                        $payment['dataPagamento']
                        ?: $payment['criadoEm']
                    )
                )
            ) ?>
        </td>

        <td>
            <span class="badge muted">
                <?= Support::e($payment['tipo']) ?>
            </span>
        </td>

        <td>
            <?= Support::e($payment['titulo'] ?? '—') ?>
        </td>

        <td>
            <strong><?= Support::e($payment['nome']) ?></strong><br>
            <small><?= Support::e($payment['cpf']) ?></small>
        </td>

        <td>
            <?= Support::e($payment['palpite'] ?? '—') ?>
        </td>

        <td>
            <?= Support::e(
                ReportRepository::paymentMethodLabel(
                    (string)$payment['formaPagamento']
                )
            ) ?>
        </td>

        <td>
            <?= Support::money((float)$payment['valor']) ?>
        </td>

        <td>
            <span class="badge <?= $payment['status'] === 'Pago'
                ? 'paid'
                : 'muted' ?>">
                <?= Support::e($payment['status']) ?>
            </span>

            <?php if (!empty($payment['asaasStatus'])): ?>
                <br>
                <small class="payment-asaas-status">
                    Asaas:
                    <?= Support::e($payment['asaasStatus']) ?>
                </small>
            <?php endif; ?>
        </td>

        <td>
            <?php if (!empty($payment['asaasPaymentId'])): ?>
                <small>
                    <?= Support::e($payment['asaasPaymentId']) ?>
                </small>
            <?php else: ?>
                <span>—</span>

                <form
                    method="post"
                    action="<?= APP_URL ?>/admin/pagamentos/reconciliar.php"
                    class="reconcile-form"
                >
                    <input
                        type="hidden"
                        name="_csrf"
                        value="<?= Support::csrf() ?>"
                    >

                    <input
                        type="hidden"
                        name="idPagamento"
                        value="<?= (int)$payment['idPagamento'] ?>"
                    >

                    <button
                        class="btn small"
                        type="submit"
                    >
                        Reconciliar
                    </button>
                </form>
            <?php endif; ?>
        </td>

        <td class="payment-error-cell">
            <?php if (
                !empty($payment['erro'])
                && str_contains(
                    (string)$payment['erro'],
                    '[SANDBOX_PIX_DISABLED]'
                )
            ): ?>
                <small>
                    Sandbox: cobrança criada no Asaas, mas esta conta
                    está com recebimentos Pix desabilitados.
                </small>
            <?php elseif (!empty($payment['erro'])): ?>
                <small>
                    <?= Support::e($payment['erro']) ?>
                </small>
            <?php else: ?>
                —
            <?php endif; ?>
        </td>

        <td>
            <?php if ($payment['status'] === 'Pago'): ?>
                <?php
                $receipt = ComprovanteService::ensureForPayment(
                    (int)$payment['idPagamento']
                );
                ?>

                <a
                    href="<?= Support::e(
                        ComprovanteService::url($receipt)
                    ) ?>"
                    target="_blank"
                >
                    Abrir
                </a>
            <?php else: ?>
                —
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>

<?php if (!$items): ?>
    <tr>
        <td colspan="11">
            Nenhum pagamento encontrado para os filtros selecionados.
        </td>
    </tr>
<?php endif; ?>
