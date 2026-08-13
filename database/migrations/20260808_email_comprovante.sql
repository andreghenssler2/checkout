-- Checkout IECLB Parobé
-- Atualização v1.3.0 - E-mails e Comprovante de Pagamento
--
-- Execute uma única vez em instalações v1.2.0.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS configuracoes_email (
 idConfiguracao TINYINT UNSIGNED NOT NULL DEFAULT 1,
 ativo TINYINT(1) NOT NULL DEFAULT 1,
 remetente_nome VARCHAR(150) NOT NULL DEFAULT 'IECLB Parobé',
 remetente_email VARCHAR(180) NOT NULL DEFAULT 'noreply@ieclbparobe.com.br',
 reply_to VARCHAR(180) NULL DEFAULT 'secretaria@ieclbparobe.com.br',
 criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (idConfiguracao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO configuracoes_email (
 idConfiguracao,
 ativo,
 remetente_nome,
 remetente_email,
 reply_to
) VALUES (
 1,
 1,
 'IECLB Parobé',
 'noreply@ieclbparobe.com.br',
 'secretaria@ieclbparobe.com.br'
);

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
 status ENUM('Pendente','Enviado','Erro') NOT NULL DEFAULT 'Pendente',
 tentativas INT UNSIGNED NOT NULL DEFAULT 0,
 ultimoErro TEXT NULL,
 criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 enviadoEm DATETIME NULL,
 PRIMARY KEY(idEmail),
 UNIQUE KEY uq_email_pagamento_tipo(idPagamento,tipo),
 KEY idx_email_status(status,criadoEm),
 CONSTRAINT fk_email_pagamento
   FOREIGN KEY(idPagamento)
   REFERENCES pagamentos(idPagamento)
   ON DELETE SET NULL
   ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
