-- ============================================================
-- CHECKOUT IECLB PAROBÉ
-- v1.6.3 - Google Analytics 4
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS configuracoes_analytics (
    idConfiguracao TINYINT UNSIGNED NOT NULL DEFAULT 1,
    ativo TINYINT(1) NOT NULL DEFAULT 0,
    measurement_id VARCHAR(30) NULL,
    criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (idConfiguracao)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO configuracoes_analytics (
    idConfiguracao,
    ativo,
    measurement_id
) VALUES (
    1,
    0,
    NULL
);
