-- Checkout IECLB Parobé
-- Atualização v1.4.0 - Diagnóstico automático do PIX
--
-- Execute uma única vez em instalações v1.3.x.

SET NAMES utf8mb4;

ALTER TABLE configuracoes_asaas
    ADD COLUMN pix_disponivel_sandbox TINYINT(1) NULL DEFAULT NULL
        AFTER webhook_token_producao,
    ADD COLUMN pix_verificado_em_sandbox DATETIME NULL
        AFTER pix_disponivel_sandbox,
    ADD COLUMN pix_chave_sandbox VARCHAR(160) NULL
        AFTER pix_verificado_em_sandbox,
    ADD COLUMN pix_disponivel_producao TINYINT(1) NULL DEFAULT NULL
        AFTER pix_chave_sandbox,
    ADD COLUMN pix_verificado_em_producao DATETIME NULL
        AFTER pix_disponivel_producao,
    ADD COLUMN pix_chave_producao VARCHAR(160) NULL
        AFTER pix_verificado_em_producao;
