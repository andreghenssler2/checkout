-- Atualização v1.1.0 - Módulo de Palpites
-- Execute este arquivo SOMENTE em uma instalação já existente da v1.0.2.
-- O valor mínimo continua sendo R$ 10,00 e os pagamentos usam a mesma configuração Asaas.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS palpites_eventos (
 idEventoPalpite INT UNSIGNED NOT NULL AUTO_INCREMENT,
 titulo VARCHAR(180) NOT NULL,
 slug VARCHAR(200) NOT NULL,
 descricao TEXT NULL,
 imagem VARCHAR(255) NULL,
 equipe_casa VARCHAR(120) NOT NULL,
 equipe_visitante VARCHAR(120) NOT NULL,
 data_jogo DATETIME NULL,
 data_inicio DATETIME NULL,
 data_fim DATETIME NULL,
 valor_minimo DECIMAL(10,2) NOT NULL DEFAULT 10.00,
 permitir_valor_livre TINYINT(1) NOT NULL DEFAULT 0,
 permitir_outro_palpite TINYINT(1) NOT NULL DEFAULT 1,
 pix_ativo TINYINT(1) NOT NULL DEFAULT 1,
 cartao_ativo TINYINT(1) NOT NULL DEFAULT 1,
 ativo TINYINT(1) NOT NULL DEFAULT 1,
 criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(idEventoPalpite),
 UNIQUE KEY uq_palpite_evento_slug(slug),
 KEY idx_palpite_evento_ativo(ativo,data_inicio,data_fim),
 KEY idx_palpite_evento_jogo(data_jogo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS palpites_valores (
 idValorPalpite INT UNSIGNED NOT NULL AUTO_INCREMENT,
 idEventoPalpite INT UNSIGNED NOT NULL,
 valor DECIMAL(10,2) NOT NULL,
 ordem INT UNSIGNED NOT NULL DEFAULT 0,
 ativo TINYINT(1) NOT NULL DEFAULT 1,
 criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(idValorPalpite),
 UNIQUE KEY uq_palpite_evento_valor(idEventoPalpite,valor),
 CONSTRAINT fk_palpite_valor_evento FOREIGN KEY(idEventoPalpite) REFERENCES palpites_eventos(idEventoPalpite) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS palpites_opcoes (
 idOpcao INT UNSIGNED NOT NULL AUTO_INCREMENT,
 idEventoPalpite INT UNSIGNED NOT NULL,
 rotulo VARCHAR(160) NOT NULL,
 ordem INT UNSIGNED NOT NULL DEFAULT 0,
 ativo TINYINT(1) NOT NULL DEFAULT 1,
 criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(idOpcao),
 KEY idx_palpite_opcoes_evento(idEventoPalpite,ativo,ordem),
 CONSTRAINT fk_palpite_opcao_evento FOREIGN KEY(idEventoPalpite) REFERENCES palpites_eventos(idEventoPalpite) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS palpites (
 idPalpite BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 idEventoPalpite INT UNSIGNED NOT NULL,
 idDoador BIGINT UNSIGNED NOT NULL,
 idOpcao INT UNSIGNED NULL,
 palpite VARCHAR(160) NOT NULL,
 statusPagamento VARCHAR(30) NOT NULL DEFAULT 'Pendente',
 criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(idPalpite),
 KEY idx_palpite_evento(idEventoPalpite),
 KEY idx_palpite_doador(idDoador),
 KEY idx_palpite_status(statusPagamento),
 CONSTRAINT fk_palpite_evento FOREIGN KEY(idEventoPalpite) REFERENCES palpites_eventos(idEventoPalpite) ON DELETE RESTRICT ON UPDATE CASCADE,
 CONSTRAINT fk_palpite_doador FOREIGN KEY(idDoador) REFERENCES doadores(idDoador) ON DELETE RESTRICT ON UPDATE CASCADE,
 CONSTRAINT fk_palpite_opcao FOREIGN KEY(idOpcao) REFERENCES palpites_opcoes(idOpcao) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE pagamentos
    MODIFY idOferta INT UNSIGNED NULL,
    ADD COLUMN idPalpite BIGINT UNSIGNED NULL AFTER idOferta,
    ADD KEY idx_pag_palpite (idPalpite),
    ADD CONSTRAINT fk_pag_palpite
        FOREIGN KEY (idPalpite)
        REFERENCES palpites(idPalpite)
        ON DELETE RESTRICT
        ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS=1;
