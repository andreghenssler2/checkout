-- ============================================================
-- CHECKOUT IECLB PAROBÉ
-- v1.6.0 - Encurtador de URLs para Ofertas e Palpites
-- ============================================================
-- Cada código curto é ÚNICO em todo o Checkout.
-- Cada Oferta/Palpite possui somente um link curto estável.
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS links_curtos (
    idLink BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(16) NOT NULL,
    tipo ENUM('Oferta','Palpite') NOT NULL,
    idReferencia BIGINT UNSIGNED NOT NULL,
    criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (idLink),
    UNIQUE KEY uq_link_curto_codigo (codigo),
    UNIQUE KEY uq_link_curto_origem (tipo,idReferencia),
    KEY idx_link_curto_tipo_ref (tipo,idReferencia)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Gera links estáveis para Ofertas já existentes.
-- Ex.: Oferta id 1 => o0000001
INSERT IGNORE INTO links_curtos (
    codigo,
    tipo,
    idReferencia
)
SELECT
    CONCAT(
        'o',
        LPAD(
            LOWER(CONV(idOferta,10,36)),
            7,
            '0'
        )
    ),
    'Oferta',
    idOferta
FROM ofertas;

-- Gera links estáveis para Palpites já existentes.
-- Ex.: Palpite id 1 => p0000001
INSERT IGNORE INTO links_curtos (
    codigo,
    tipo,
    idReferencia
)
SELECT
    CONCAT(
        'p',
        LPAD(
            LOWER(CONV(idEventoPalpite,10,36)),
            7,
            '0'
        )
    ),
    'Palpite',
    idEventoPalpite
FROM palpites_eventos;
