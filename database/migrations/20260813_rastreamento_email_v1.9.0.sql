-- ============================================================
-- CHECKOUT IECLB PAROBÉ
-- v1.9.0 - Rastreamento de abertura de e-mails
-- ============================================================
-- Registra primeira abertura, última abertura e total de
-- carregamentos do pixel para cada e-mail enviado.
-- ============================================================

SET NAMES utf8mb4;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE configuracoes_email
         ADD COLUMN rastrear_abertura TINYINT(1)
         NOT NULL DEFAULT 1
         AFTER ativo",
        "SELECT 1"
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA=DATABASE()
      AND TABLE_NAME='configuracoes_email'
      AND COLUMN_NAME='rastrear_abertura'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE emails_envios
         ADD COLUMN rastreamento_token CHAR(64) NULL
         AFTER corpoHtml",
        "SELECT 1"
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA=DATABASE()
      AND TABLE_NAME='emails_envios'
      AND COLUMN_NAME='rastreamento_token'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE emails_envios
         ADD COLUMN abertoEm DATETIME NULL
         AFTER enviadoEm",
        "SELECT 1"
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA=DATABASE()
      AND TABLE_NAME='emails_envios'
      AND COLUMN_NAME='abertoEm'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE emails_envios
         ADD COLUMN ultimaAberturaEm DATETIME NULL
         AFTER abertoEm",
        "SELECT 1"
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA=DATABASE()
      AND TABLE_NAME='emails_envios'
      AND COLUMN_NAME='ultimaAberturaEm'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE emails_envios
         ADD COLUMN totalAberturas INT UNSIGNED
         NOT NULL DEFAULT 0
         AFTER ultimaAberturaEm",
        "SELECT 1"
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA=DATABASE()
      AND TABLE_NAME='emails_envios'
      AND COLUMN_NAME='totalAberturas'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE emails_envios
         ADD UNIQUE KEY uq_email_rastreamento_token
         (rastreamento_token)",
        "SELECT 1"
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA=DATABASE()
      AND TABLE_NAME='emails_envios'
      AND INDEX_NAME='uq_email_rastreamento_token'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE emails_envios
         ADD KEY idx_email_abertura
         (abertoEm,enviadoEm)",
        "SELECT 1"
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA=DATABASE()
      AND TABLE_NAME='emails_envios'
      AND INDEX_NAME='idx_email_abertura'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
