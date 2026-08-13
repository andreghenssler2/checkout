SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS administradores (
 idAdministrador INT UNSIGNED NOT NULL AUTO_INCREMENT,
 nome VARCHAR(150) NOT NULL,
 email VARCHAR(180) NOT NULL,
 senha VARCHAR(255) NOT NULL,
 ativo TINYINT(1) NOT NULL DEFAULT 1,
 ultimo_login DATETIME NULL,
 criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (idAdministrador), UNIQUE KEY uq_admin_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS configuracoes_site (
 idConfiguracao TINYINT UNSIGNED NOT NULL DEFAULT 1,
 titulo VARCHAR(160) NOT NULL DEFAULT 'Checkout IECLB Parobé',
 descricao VARCHAR(300) NOT NULL DEFAULT 'Campanhas de ofertas e palpites da IECLB Parobé',
 favicon VARCHAR(255) NULL,
 transparencia_tipo ENUM('Completa','Resumida','Oculta') NOT NULL DEFAULT 'Completa',
 criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(idConfiguracao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO configuracoes_site (
 idConfiguracao,titulo,descricao,favicon,transparencia_tipo
) VALUES (
 1,
 'Checkout IECLB Parobé',
 'Campanhas de ofertas e palpites da IECLB Parobé',
 NULL,
 'Completa'
);

CREATE TABLE IF NOT EXISTS configuracoes_pagamentos (
 idConfiguracao TINYINT UNSIGNED NOT NULL DEFAULT 1,
 provedor_pix VARCHAR(30) NOT NULL DEFAULT 'Asaas',
 provedor_cartao VARCHAR(30) NOT NULL DEFAULT 'Asaas',
 provedor_boleto VARCHAR(30) NOT NULL DEFAULT 'Asaas',
 criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(idConfiguracao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO configuracoes_pagamentos (
 idConfiguracao,provedor_pix,provedor_cartao,provedor_boleto
) VALUES (1,'Asaas','Asaas','Asaas');

CREATE TABLE IF NOT EXISTS configuracoes_pagbank (
 idConfiguracao TINYINT UNSIGNED NOT NULL DEFAULT 1,
 ativo TINYINT(1) NOT NULL DEFAULT 0,
 ambiente ENUM('sandbox','producao') NOT NULL DEFAULT 'sandbox',
 cartao_prazo_recebimento SMALLINT UNSIGNED NOT NULL DEFAULT 30,
 token_sandbox TEXT NULL,
 token_producao TEXT NULL,
 public_key_sandbox LONGTEXT NULL,
 public_key_producao LONGTEXT NULL,
 ultimo_teste_sandbox DATETIME NULL,
 ultimo_teste_producao DATETIME NULL,
 ultimo_erro_sandbox TEXT NULL,
 ultimo_erro_producao TEXT NULL,
 criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(idConfiguracao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO configuracoes_pagbank (
 idConfiguracao,ativo,ambiente
) VALUES (1,0,'sandbox');

CREATE TABLE IF NOT EXISTS configuracoes_asaas (
 idConfiguracao TINYINT UNSIGNED NOT NULL DEFAULT 1,
 ativo TINYINT(1) NOT NULL DEFAULT 0,
 ambiente ENUM('sandbox','producao') NOT NULL DEFAULT 'sandbox',
 api_key_sandbox TEXT NULL, api_key_producao TEXT NULL,
 webhook_token_sandbox TEXT NULL, webhook_token_producao TEXT NULL,
 pix_disponivel_sandbox TINYINT(1) NULL DEFAULT NULL,
 pix_verificado_em_sandbox DATETIME NULL,
 pix_chave_sandbox VARCHAR(160) NULL,
 pix_disponivel_producao TINYINT(1) NULL DEFAULT NULL,
 pix_verificado_em_producao DATETIME NULL,
 pix_chave_producao VARCHAR(160) NULL,
 criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (idConfiguracao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO configuracoes_asaas (idConfiguracao,ativo,ambiente) VALUES (1,0,'sandbox');



CREATE TABLE IF NOT EXISTS configuracoes_analytics (
 idConfiguracao TINYINT UNSIGNED NOT NULL DEFAULT 1,
 ativo TINYINT(1) NOT NULL DEFAULT 0,
 measurement_id VARCHAR(30) NULL,
 criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (idConfiguracao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO configuracoes_analytics (
 idConfiguracao,ativo,measurement_id
) VALUES (
 1,0,NULL
);

CREATE TABLE IF NOT EXISTS configuracoes_email (
 idConfiguracao TINYINT UNSIGNED NOT NULL DEFAULT 1,
 ativo TINYINT(1) NOT NULL DEFAULT 1,
 rastrear_abertura TINYINT(1) NOT NULL DEFAULT 1,
 remetente_nome VARCHAR(150) NOT NULL DEFAULT 'IECLB Parobé',
 remetente_email VARCHAR(180) NOT NULL DEFAULT 'noreply@ieclbparobe.com.br',
 reply_to VARCHAR(180) NULL DEFAULT 'secretaria@ieclbparobe.com.br',
 criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (idConfiguracao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO configuracoes_email (
 idConfiguracao,ativo,rastrear_abertura,remetente_nome,remetente_email,reply_to
) VALUES (
 1,1,1,'IECLB Parobé','noreply@ieclbparobe.com.br','secretaria@ieclbparobe.com.br'
);

CREATE TABLE IF NOT EXISTS doadores (
 idDoador BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 nome VARCHAR(180) NOT NULL,
 cpf VARCHAR(11) NOT NULL,
 email VARCHAR(180) NOT NULL,
 telefone VARCHAR(30) NOT NULL,
 asaasCustomerId VARCHAR(100) NULL,
 criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(idDoador),
 UNIQUE KEY uq_doador_cpf(cpf),
 KEY idx_doador_asaas(asaasCustomerId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS doadores_provedores (
 idDoador BIGINT UNSIGNED NOT NULL,
 provedor VARCHAR(30) NOT NULL,
 customerId VARCHAR(160) NOT NULL,
 criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(idDoador,provedor),
 UNIQUE KEY uq_doador_provedor_customer(provedor,customerId),
 CONSTRAINT fk_doador_provedor_doador FOREIGN KEY(idDoador)
   REFERENCES doadores(idDoador) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS links_curtos (
 idLink BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 codigo VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 tipo ENUM('Oferta','Palpite') NOT NULL,
 idReferencia BIGINT UNSIGNED NOT NULL,
 criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(idLink),
 UNIQUE KEY uq_link_curto_codigo(codigo),
 UNIQUE KEY uq_link_curto_origem(tipo,idReferencia),
 KEY idx_link_curto_tipo_ref(tipo,idReferencia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ofertas (
 idOferta INT UNSIGNED NOT NULL AUTO_INCREMENT,
 titulo VARCHAR(180) NOT NULL,
 slug VARCHAR(200) NOT NULL,
 categoria ENUM('Local','Sinodal','Nacional','Especial') NOT NULL DEFAULT 'Local',
 descricao TEXT NULL,
 imagem VARCHAR(255) NULL,
 data_inicio DATETIME NULL,
 data_fim DATETIME NULL,
 valor_minimo DECIMAL(10,2) NOT NULL DEFAULT 10.00,
 permitir_valor_livre TINYINT(1) NOT NULL DEFAULT 1,
 pix_ativo TINYINT(1) NOT NULL DEFAULT 1,
 cartao_ativo TINYINT(1) NOT NULL DEFAULT 1,
 boleto_ativo TINYINT(1) NOT NULL DEFAULT 0,
 ativo TINYINT(1) NOT NULL DEFAULT 1,
 criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (idOferta),
 UNIQUE KEY uq_oferta_slug(slug),
 KEY idx_oferta_categoria(categoria),
 KEY idx_oferta_ativa(ativo,data_inicio,data_fim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ofertas_valores (
 idValor INT UNSIGNED NOT NULL AUTO_INCREMENT,
 idOferta INT UNSIGNED NOT NULL,
 valor DECIMAL(10,2) NOT NULL,
 ordem INT UNSIGNED NOT NULL DEFAULT 0,
 ativo TINYINT(1) NOT NULL DEFAULT 1,
 criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(idValor),
 UNIQUE KEY uq_oferta_valor(idOferta,valor),
 CONSTRAINT fk_valor_oferta FOREIGN KEY(idOferta) REFERENCES ofertas(idOferta) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS palpites_eventos (
 idEventoPalpite INT UNSIGNED NOT NULL AUTO_INCREMENT,
 titulo VARCHAR(180) NOT NULL,
 slug VARCHAR(200) NOT NULL,
 descricao TEXT NULL,
 imagem VARCHAR(255) NULL,
 equipe_casa VARCHAR(120) NOT NULL,
 equipe_visitante VARCHAR(120) NOT NULL,
 data_jogo DATETIME NULL,
 status_jogo ENUM('Agendado','EmAndamento','Finalizado') NOT NULL DEFAULT 'Agendado',
 placar_casa TINYINT UNSIGNED NULL,
 placar_visitante TINYINT UNSIGNED NULL,
 finalizadoEm DATETIME NULL,
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
 KEY idx_palpite_evento_jogo(data_jogo),
 KEY idx_palpite_status_jogo(status_jogo,data_jogo)
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

CREATE TABLE IF NOT EXISTS pagamentos (
 idPagamento BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 idOferta INT UNSIGNED NULL,
 idPalpite BIGINT UNSIGNED NULL,
 idDoador BIGINT UNSIGNED NOT NULL,
 codigo VARCHAR(64) NOT NULL,
 valor DECIMAL(10,2) NOT NULL,
 valorLiquido DECIMAL(10,2) NULL,
 taxa DECIMAL(10,2) NULL,
 formaPagamento ENUM('PIX','Cartao','Boleto') NOT NULL,
 provedor VARCHAR(30) NOT NULL DEFAULT 'Asaas',
 provedorPaymentId VARCHAR(160) NULL,
 provedorStatus VARCHAR(80) NULL,
 status VARCHAR(30) NOT NULL DEFAULT 'Pendente',
 asaasPaymentId VARCHAR(100) NULL,
 asaasStatus VARCHAR(60) NULL,
 invoiceUrl VARCHAR(500) NULL,
 pixQrCode LONGTEXT NULL,
 pixCopiaCola TEXT NULL,
 pixExpiracao DATETIME NULL,
 bankSlipUrl VARCHAR(500) NULL,
 boletoLinhaDigitavel VARCHAR(255) NULL,
 dataVencimento DATE NULL,
 dataPagamento DATETIME NULL,
 erro TEXT NULL,
 criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(idPagamento),
 UNIQUE KEY uq_pag_codigo(codigo),
 UNIQUE KEY uq_pag_asaas(asaasPaymentId),
 KEY idx_pag_oferta(idOferta),
 KEY idx_pag_palpite(idPalpite),
 KEY idx_pag_doador(idDoador),
 KEY idx_pag_status(status),
 KEY idx_pag_provedor_status(provedor,provedorStatus),
 UNIQUE KEY uq_pag_provedor_payment(provedor,provedorPaymentId),
 KEY idx_pag_data(criadoEm),
 KEY idx_pag_status_data_pagamento(status,dataPagamento),
 CONSTRAINT fk_pag_oferta FOREIGN KEY(idOferta) REFERENCES ofertas(idOferta) ON DELETE RESTRICT ON UPDATE CASCADE,
 CONSTRAINT fk_pag_palpite FOREIGN KEY(idPalpite) REFERENCES palpites(idPalpite) ON DELETE RESTRICT ON UPDATE CASCADE,
 CONSTRAINT fk_pag_doador FOREIGN KEY(idDoador) REFERENCES doadores(idDoador) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS comprovantes (
 idComprovante BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 idPagamento BIGINT UNSIGNED NOT NULL,
 numero VARCHAR(40) NOT NULL,
 token VARCHAR(80) NOT NULL,
 emitidoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(idComprovante),
 UNIQUE KEY uq_comprovante_pagamento(idPagamento),
 UNIQUE KEY uq_comprovante_numero(numero),
 UNIQUE KEY uq_comprovante_token(token),
 CONSTRAINT fk_comprovante_pagamento
   FOREIGN KEY(idPagamento)
   REFERENCES pagamentos(idPagamento)
   ON DELETE RESTRICT
   ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS emails_envios (
 idEmail BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 idPagamento BIGINT UNSIGNED NULL,
 tipo VARCHAR(40) NOT NULL,
 destinatario VARCHAR(180) NOT NULL,
 assunto VARCHAR(220) NOT NULL,
 corpoHtml LONGTEXT NOT NULL,
 rastreamento_token CHAR(64) NULL,
 status ENUM('Pendente','Enviado','Erro') NOT NULL DEFAULT 'Pendente',
 tentativas INT UNSIGNED NOT NULL DEFAULT 0,
 ultimoErro TEXT NULL,
 criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 enviadoEm DATETIME NULL,
 abertoEm DATETIME NULL,
 ultimaAberturaEm DATETIME NULL,
 totalAberturas INT UNSIGNED NOT NULL DEFAULT 0,
 PRIMARY KEY(idEmail),
 UNIQUE KEY uq_email_pagamento_tipo(idPagamento,tipo),
 UNIQUE KEY uq_email_rastreamento_token(rastreamento_token),
 KEY idx_email_status(status,criadoEm),
 KEY idx_email_abertura(abertoEm,enviadoEm),
 CONSTRAINT fk_email_pagamento
   FOREIGN KEY(idPagamento)
   REFERENCES pagamentos(idPagamento)
   ON DELETE SET NULL
   ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pagbank_webhook_eventos (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 hashPayload CHAR(64) NOT NULL,
 orderId VARCHAR(80) NULL,
 chargeId VARCHAR(80) NULL,
 referencia VARCHAR(64) NULL,
 statusPagBank VARCHAR(80) NULL,
 payload LONGTEXT NOT NULL,
 processadoEm DATETIME NULL,
 erro TEXT NULL,
 criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id),
 UNIQUE KEY uq_pagbank_webhook_hash(hashPayload),
 KEY idx_pagbank_webhook_order(orderId),
 KEY idx_pagbank_webhook_referencia(referencia),
 KEY idx_pagbank_webhook_status(statusPagBank)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS asaas_webhook_eventos (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 eventoId VARCHAR(160) NOT NULL,
 evento VARCHAR(120) NOT NULL,
 asaasPaymentId VARCHAR(100) NULL,
 payload LONGTEXT NULL,
 processadoEm DATETIME NULL,
 erro TEXT NULL,
 criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id),
 UNIQUE KEY uq_webhook_evento(eventoId),
 KEY idx_webhook_payment(asaasPaymentId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS checkout_limites (
 chave CHAR(64) NOT NULL,
 tipo VARCHAR(30) NOT NULL,
 janelaInicio DATETIME NOT NULL,
 tentativas INT UNSIGNED NOT NULL DEFAULT 0,
 atualizadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(chave,tipo),
 KEY idx_limite_janela(janelaInicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
