-- ============================================================
-- CHECKOUT IECLB PAROBÉ
-- v1.6.2 - Regeneração dos links curtos já existentes
-- ============================================================
-- ATENÇÃO:
-- Este script TROCA todos os códigos curtos atuais.
-- Portanto, os links curtos antigos deixarão de funcionar.
--
-- Os novos códigos:
-- - misturam maiúsculas, minúsculas e números;
-- - possuem 10 caracteres;
-- - não se repetem;
-- - continuam protegidos pela UNIQUE KEY uq_link_curto_codigo.
--
-- Exemplo de formato:
--   Oq2f8k3m7x
--   Pr5a1z9c4
-- ============================================================

SET NAMES utf8mb4;

START TRANSACTION;

-- Garante comparação case-sensitive antes de recriar os códigos.
ALTER TABLE links_curtos
    MODIFY COLUMN codigo
    VARCHAR(16)
    CHARACTER SET ascii
    COLLATE ascii_bin
    NOT NULL;

-- ------------------------------------------------------------
-- Regenera TODOS os links existentes.
--
-- Estrutura do novo código:
--   1º caractere: tipo (O = Oferta / P = Palpite) - MAIÚSCULO
--   2º caractere: letra minúscula derivada do id
--   3º caractere: número derivado do id
--   4º ao 10º: identificador embaralhado em base 36
--
-- A multiplicação por 2654435761 funciona como uma permutação
-- dos valores INT UNSIGNED (módulo 2^32), deixando o código menos
-- sequencial sem perder a unicidade por tipo/id.
-- ------------------------------------------------------------

UPDATE links_curtos
SET codigo = CONCAT(
    CASE
        WHEN tipo = 'Oferta' THEN 'O'
        ELSE 'P'
    END,
    CHAR(
        97 + MOD(idReferencia, 26)
    ),
    MOD(idReferencia, 10),
    LPAD(
        LOWER(
            CONV(
                MOD(
                    idReferencia * 2654435761,
                    4294967296
                ),
                10,
                36
            )
        ),
        7,
        '0'
    )
);

COMMIT;

-- Conferência opcional:
-- SELECT codigo,tipo,idReferencia
-- FROM links_curtos
-- ORDER BY tipo,idReferencia;
