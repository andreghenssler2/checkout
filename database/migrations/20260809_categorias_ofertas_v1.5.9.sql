-- ============================================================
-- CHECKOUT IECLB PAROBÉ
-- v1.5.9 - Categorias de Ofertas
-- Categorias: Local, Sinodal, Nacional e Especial
-- ============================================================

SET NAMES utf8mb4;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE ofertas
         ADD COLUMN categoria
         ENUM('Local','Sinodal','Nacional','Especial')
         NOT NULL DEFAULT 'Local'
         AFTER slug",
        "SELECT 1"
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'ofertas'
      AND COLUMN_NAME = 'categoria'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE ofertas
         ADD KEY idx_oferta_categoria (categoria)",
        "SELECT 1"
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'ofertas'
      AND INDEX_NAME = 'idx_oferta_categoria'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Ofertas já existentes ficam classificadas como Local.
UPDATE ofertas
SET categoria='Local'
WHERE categoria IS NULL
   OR categoria NOT IN ('Local','Sinodal','Nacional','Especial');
