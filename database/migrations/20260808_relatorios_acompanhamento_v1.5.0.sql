-- ============================================================
-- CHECKOUT IECLB PAROBÉ
-- v1.5.0 - Relatórios + acompanhamento de palpites
-- ============================================================
-- Pode ser executado em uma instalação v1.4.x.
-- O script verifica a existência das novas colunas/índices.
-- Faça backup antes de executar.
-- ============================================================

SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- PALPITES: estado e placar do jogo
-- ------------------------------------------------------------

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE palpites_eventos
         ADD COLUMN status_jogo ENUM('Agendado','EmAndamento','Finalizado')
         NOT NULL DEFAULT 'Agendado' AFTER data_jogo",
        "SELECT 1"
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'palpites_eventos'
      AND COLUMN_NAME = 'status_jogo'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE palpites_eventos
         ADD COLUMN placar_casa TINYINT UNSIGNED NULL AFTER status_jogo",
        "SELECT 1"
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'palpites_eventos'
      AND COLUMN_NAME = 'placar_casa'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE palpites_eventos
         ADD COLUMN placar_visitante TINYINT UNSIGNED NULL AFTER placar_casa",
        "SELECT 1"
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'palpites_eventos'
      AND COLUMN_NAME = 'placar_visitante'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE palpites_eventos
         ADD COLUMN finalizadoEm DATETIME NULL AFTER placar_visitante",
        "SELECT 1"
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'palpites_eventos'
      AND COLUMN_NAME = 'finalizadoEm'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE palpites_eventos
         ADD KEY idx_palpite_status_jogo (status_jogo, data_jogo)",
        "SELECT 1"
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'palpites_eventos'
      AND INDEX_NAME = 'idx_palpite_status_jogo'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------
-- PAGAMENTOS: valor líquido e tarifa informados pelo Asaas
-- ------------------------------------------------------------

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE pagamentos
         ADD COLUMN valorLiquido DECIMAL(10,2) NULL AFTER valor",
        "SELECT 1"
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'pagamentos'
      AND COLUMN_NAME = 'valorLiquido'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE pagamentos
         ADD COLUMN taxa DECIMAL(10,2) NULL AFTER valorLiquido",
        "SELECT 1"
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'pagamentos'
      AND COLUMN_NAME = 'taxa'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE pagamentos
         ADD KEY idx_pag_status_data_pagamento (status, dataPagamento)",
        "SELECT 1"
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'pagamentos'
      AND INDEX_NAME = 'idx_pag_status_data_pagamento'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- v1.5.0 concluída.
