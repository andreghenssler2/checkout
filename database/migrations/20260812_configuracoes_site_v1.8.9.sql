-- ============================================================
-- CHECKOUT IECLB PAROBÉ
-- v1.8.9 - Configurações gerais do site
-- ============================================================
-- Título, favicon, descrição e tipo de transparência.
-- Em instalação nova, use apenas database/schema.sql.
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS configuracoes_site (
    idConfiguracao TINYINT UNSIGNED NOT NULL DEFAULT 1,
    titulo VARCHAR(160) NOT NULL DEFAULT 'Checkout IECLB Parobé',
    descricao VARCHAR(300) NOT NULL DEFAULT
        'Campanhas de ofertas e palpites da IECLB Parobé',
    favicon VARCHAR(255) NULL,
    transparencia_tipo ENUM(
        'Completa',
        'Resumida',
        'Oculta'
    ) NOT NULL DEFAULT 'Completa',
    criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (idConfiguracao)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO configuracoes_site (
    idConfiguracao,
    titulo,
    descricao,
    favicon,
    transparencia_tipo
) VALUES (
    1,
    'Checkout IECLB Parobé',
    'Campanhas de ofertas e palpites da IECLB Parobé',
    NULL,
    'Completa'
);
