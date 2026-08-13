SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS configuracoes_pagamentos (
    idConfiguracao TINYINT UNSIGNED NOT NULL DEFAULT 1,
    provedor_pix VARCHAR(30) NOT NULL DEFAULT 'Asaas',
    provedor_cartao VARCHAR(30) NOT NULL DEFAULT 'Asaas',
    provedor_boleto VARCHAR(30) NOT NULL DEFAULT 'Asaas',
    criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (idConfiguracao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO configuracoes_pagamentos (
    idConfiguracao,provedor_pix,provedor_cartao,provedor_boleto
) VALUES (1,'Asaas','Asaas','Asaas');

CREATE TABLE IF NOT EXISTS doadores_provedores (
    idDoador BIGINT UNSIGNED NOT NULL,
    provedor VARCHAR(30) NOT NULL,
    customerId VARCHAR(160) NOT NULL,
    criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (idDoador,provedor),
    UNIQUE KEY uq_doador_provedor_customer (provedor,customerId),
    CONSTRAINT fk_doador_provedor_doador
        FOREIGN KEY (idDoador)
        REFERENCES doadores(idDoador)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE pagamentos
         ADD COLUMN provedor VARCHAR(30)
         NOT NULL DEFAULT 'Asaas'
         AFTER formaPagamento",
        "SELECT 1"
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA=DATABASE()
      AND TABLE_NAME='pagamentos'
      AND COLUMN_NAME='provedor'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE pagamentos
         ADD COLUMN provedorPaymentId VARCHAR(160) NULL
         AFTER provedor",
        "SELECT 1"
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA=DATABASE()
      AND TABLE_NAME='pagamentos'
      AND COLUMN_NAME='provedorPaymentId'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE pagamentos
         ADD COLUMN provedorStatus VARCHAR(80) NULL
         AFTER provedorPaymentId",
        "SELECT 1"
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA=DATABASE()
      AND TABLE_NAME='pagamentos'
      AND COLUMN_NAME='provedorStatus'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE pagamentos
         ADD KEY idx_pag_provedor_status
         (provedor,provedorStatus)",
        "SELECT 1"
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA=DATABASE()
      AND TABLE_NAME='pagamentos'
      AND INDEX_NAME='idx_pag_provedor_status'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE pagamentos
         ADD UNIQUE KEY uq_pag_provedor_payment
         (provedor,provedorPaymentId)",
        "SELECT 1"
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA=DATABASE()
      AND TABLE_NAME='pagamentos'
      AND INDEX_NAME='uq_pag_provedor_payment'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE pagamentos
SET
    provedor='Asaas',
    provedorPaymentId=COALESCE(provedorPaymentId,asaasPaymentId),
    provedorStatus=COALESCE(provedorStatus,asaasStatus)
WHERE
    provedor IS NULL
    OR provedor=''
    OR provedor='Asaas';

INSERT INTO doadores_provedores (
    idDoador,provedor,customerId
)
SELECT
    idDoador,'Asaas',asaasCustomerId
FROM doadores
WHERE asaasCustomerId IS NOT NULL
  AND TRIM(asaasCustomerId)<>''
ON DUPLICATE KEY UPDATE
    customerId=VALUES(customerId),
    atualizadoEm=NOW();
