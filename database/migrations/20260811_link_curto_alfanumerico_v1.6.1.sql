-- ============================================================
-- CHECKOUT IECLB PAROBÉ
-- v1.6.1 - Link curto alfanumérico com maiúsculas/minúsculas
-- ============================================================
-- Novos códigos usam:
--   A-Z
--   a-z
--   0-9
--
-- A coluna usa collation binária para preservar e diferenciar maiúsculas
-- e minúsculas durante busca e validação de unicidade.
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE links_curtos
    MODIFY COLUMN codigo
    VARCHAR(16)
    CHARACTER SET ascii
    COLLATE ascii_bin
    NOT NULL;

-- A UNIQUE KEY uq_link_curto_codigo já existente continua garantindo
-- que nenhum código curto seja repetido.
