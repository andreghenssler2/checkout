-- ============================================================
-- CHECKOUT IECLB PAROBÉ
-- v1.8.7 - Taxas e prazo de recebimento PagBank
-- ============================================================
-- Execute somente em banco existente.
-- Em instalação nova, use o database/schema.sql da v1.8.7.
-- ============================================================

SET NAMES utf8mb4;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE configuracoes_pagbank
         ADD COLUMN cartao_prazo_recebimento
         SMALLINT UNSIGNED NOT NULL DEFAULT 30
         AFTER ambiente",
        "SELECT 1"
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA=DATABASE()
      AND TABLE_NAME='configuracoes_pagbank'
      AND COLUMN_NAME='cartao_prazo_recebimento'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE configuracoes_pagbank
SET cartao_prazo_recebimento=30
WHERE cartao_prazo_recebimento NOT IN (14,30)
   OR cartao_prazo_recebimento IS NULL;
