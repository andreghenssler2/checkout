-- ============================================================
-- CHECKOUT IECLB PAROBÉ
-- v1.8.1 - PagBank (PIX + Boleto + Webhook)
-- ============================================================
-- Execute somente em bancos que já receberam a v1.8.0.
-- Em instalação nova, use database/schema.sql da v1.8.1.
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS configuracoes_pagbank (
    idConfiguracao TINYINT UNSIGNED NOT NULL DEFAULT 1,
    ativo TINYINT(1) NOT NULL DEFAULT 0,
    ambiente ENUM('sandbox','producao') NOT NULL DEFAULT 'sandbox',
    token_sandbox TEXT NULL,
    token_producao TEXT NULL,
    public_key_sandbox LONGTEXT NULL,
    public_key_producao LONGTEXT NULL,
    ultimo_teste_sandbox DATETIME NULL,
    ultimo_teste_producao DATETIME NULL,
    ultimo_erro_sandbox TEXT NULL,
    ultimo_erro_producao TEXT NULL,
    criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (idConfiguracao)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO configuracoes_pagbank (
    idConfiguracao,
    ativo,
    ambiente
) VALUES (
    1,
    0,
    'sandbox'
);

CREATE TABLE IF NOT EXISTS pagbank_webhook_eventos (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    hashPayload CHAR(64) NOT NULL,
    orderId VARCHAR(80) NULL,
    chargeId VARCHAR(80) NULL,
    referencia VARCHAR(64) NULL,
    statusPagBank VARCHAR(80) NULL,
    payload LONGTEXT NOT NULL,
    processadoEm DATETIME NULL,
    erro TEXT NULL,
    criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pagbank_webhook_hash (hashPayload),
    KEY idx_pagbank_webhook_order (orderId),
    KEY idx_pagbank_webhook_referencia (referencia),
    KEY idx_pagbank_webhook_status (statusPagBank)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
