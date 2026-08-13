-- Checkout IECLB Parobé
-- Atualização v1.2.0 - Boleto nas Ofertas
--
-- Execute uma única vez em instalações v1.1.x.
--
-- Regras:
-- - Boleto somente em Ofertas.
-- - Palpites continuam aceitando somente PIX e Cartão.
-- - O vencimento do boleto é definido pela aplicação como 1 dia útil.
-- - Sábado e domingo são pulados para o próximo dia útil.
-- - A aplicação não permite boleto quando o vencimento cair no mesmo dia
--   ou depois da data de encerramento da Oferta.

SET NAMES utf8mb4;

ALTER TABLE ofertas
    ADD COLUMN boleto_ativo TINYINT(1) NOT NULL DEFAULT 0
    AFTER cartao_ativo;

ALTER TABLE pagamentos
    MODIFY COLUMN formaPagamento
        ENUM('PIX','Cartao','Boleto') NOT NULL,
    ADD COLUMN bankSlipUrl VARCHAR(500) NULL
        AFTER pixExpiracao,
    ADD COLUMN boletoLinhaDigitavel VARCHAR(255) NULL
        AFTER bankSlipUrl,
    ADD COLUMN dataVencimento DATE NULL
        AFTER boletoLinhaDigitavel;
