<?php

declare(strict_types=1);

final class TransparencyNotice
{
    public static function render(): void
    {
        ?>
        <section class="transparency-box" aria-labelledby="transparency-title">
            <div class="transparency-icon" aria-hidden="true">i</div>

            <div class="transparency-content">
                <h3 id="transparency-title">Transparência sobre os pagamentos</h3>

                <p>
                    Os pagamentos realizados por esta plataforma são processados
                    em nome de <strong>André Gustavo Henssler</strong>,
                    responsável pela conta utilizada para o recebimento das
                    cobranças, em parceria com a
                    <strong>Paróquia Evangélica de Confissão Luterana de Parobé –
                    IECLB Parobé</strong>.
                </p>

                <p>
                    <strong>André atua somente como intermediador do recebimento.</strong>
                </p>

                <p>
                    As instituições e meios de pagamento utilizados pela
                    plataforma podem cobrar <strong>tarifas de processamento</strong>.
                    Essas tarifas são descontadas pelo próprio prestador do meio
                    de pagamento antes da disponibilização do valor recebido.
                </p>

                <p>
                    O <strong>valor líquido efetivamente recebido, após o desconto
                    das tarifas cobradas pelo meio de pagamento, é repassado
                    integralmente (100%) à IECLB Parobé</strong>.
                </p>

                <p class="transparency-last">
                    Esta parceria tem como objetivo viabilizar o recebimento
                    eletrônico de inscrições, ofertas e demais contribuições
                    destinadas às atividades da IECLB Parobé.
                </p>
            </div>
        </section>
        <?php
    }

    public static function shortText(): string
    {
        return 'Os recebimentos são processados em nome de André Gustavo '
            . 'Henssler, como intermediador. O valor líquido efetivamente '
            . 'recebido, após as tarifas cobradas pelo meio de pagamento, '
            . 'é repassado integralmente (100%) à IECLB Parobé.';
    }

    public static function receiptText(): string
    {
        return 'O pagamento foi processado em nome de André Gustavo Henssler, '
            . 'responsável pela conta de recebimento, atuando somente como '
            . 'intermediador em parceria com a IECLB Parobé. Eventuais tarifas '
            . 'de processamento são descontadas pelo próprio meio de pagamento. '
            . 'O valor líquido efetivamente recebido após esses descontos é '
            . 'repassado integralmente (100%) à IECLB Parobé.';
    }
}
