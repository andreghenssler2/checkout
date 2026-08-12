-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 12-Ago-2026 às 19:25
-- Versão do servidor: 5.7.44-48
-- versão do PHP: 8.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de dados: `ieclbp28_teste`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `acesso`
--

CREATE TABLE `acesso` (
  `idAcess` int(11) NOT NULL,
  `tipo_acess` char(20) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `acesso`
--

INSERT INTO `acesso` (`idAcess`, `tipo_acess`) VALUES
(1, 'Administrador'),
(2, 'Coloborador'),
(3, 'Participante');

-- --------------------------------------------------------

--
-- Estrutura da tabela `administradores`
--

CREATE TABLE `administradores` (
  `idAdministrador` int(10) UNSIGNED NOT NULL,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `ultimo_login` datetime DEFAULT NULL,
  `criadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `administradores`
--

INSERT INTO `administradores` (`idAdministrador`, `nome`, `email`, `senha`, `ativo`, `ultimo_login`, `criadoEm`, `atualizadoEm`) VALUES
(1, 'Andre', 'andreghenssler@gmail.com', '$2y$10$CNT0bt3Zr5fFQA4TTjWQPOi9QH6iqgV9mmgzUT4DQMwDgaaJKkdBe', 1, '2026-08-12 19:07:38', '2026-08-08 20:27:39', '2026-08-12 19:07:38');

-- --------------------------------------------------------

--
-- Estrutura da tabela `asaas_webhook_eventos`
--

CREATE TABLE `asaas_webhook_eventos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `eventoId` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `evento` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asaasPaymentId` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci,
  `processadoEm` datetime DEFAULT NULL,
  `erro` text COLLATE utf8mb4_unicode_ci,
  `criadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `asaas_webhook_eventos`
--

INSERT INTO `asaas_webhook_eventos` (`id`, `eventoId`, `evento`, `asaasPaymentId`, `payload`, `processadoEm`, `erro`, `criadoEm`) VALUES
(1, 'evt_848100dd833f724812f6d2c02262b1b1&17877323', 'ACCESS_TOKEN_CREATED', NULL, '{\"id\":\"evt_848100dd833f724812f6d2c02262b1b1&17877323\",\"event\":\"ACCESS_TOKEN_CREATED\",\"dateCreated\":\"2026-08-08 20:31:41\",\"account\":{\"id\":\"9b4ee057-ddc5-47d4-8ad2-e9f7a89ff404\",\"ownerId\":null},\"accessToken\":{\"id\":\"97882a3b-3a67-4457-86f2-c8e9a36346be\",\"name\":\"ieclboferta2\",\"enabled\":true,\"dateCreated\":\"2026-08-08 20:31:41\",\"disabledReason\":null,\"expirationDate\":null,\"projectedExpirationDateByLackOfUse\":null}}', '2026-08-08 20:33:14', NULL, '2026-08-08 20:33:14'),
(2, 'evt_05b708f961d739ea7eba7e4db318f621&17877537', 'PAYMENT_CREATED', 'pay_adk3l5fb1tjmtikh', '{\"id\":\"evt_05b708f961d739ea7eba7e4db318f621&17877537\",\"event\":\"PAYMENT_CREATED\",\"dateCreated\":\"2026-08-08 20:45:13\",\"account\":{\"id\":\"9b4ee057-ddc5-47d4-8ad2-e9f7a89ff404\",\"ownerId\":null},\"payment\":{\"object\":\"payment\",\"id\":\"pay_adk3l5fb1tjmtikh\",\"dateCreated\":\"2026-08-08\",\"customer\":\"cus_000008565347\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":10.0,\"netValue\":8.01,\"originalValue\":null,\"interestValue\":null,\"description\":\"Palpite - copa - sjkdh\",\"billingType\":\"UNDEFINED\",\"pixTransaction\":null,\"status\":\"PENDING\",\"dueDate\":\"2026-08-08\",\"originalDueDate\":\"2026-08-08\",\"paymentDate\":null,\"clientPaymentDate\":null,\"installmentNumber\":null,\"invoiceUrl\":\"https://sandbox.asaas.com/i/adk3l5fb1tjmtikh\",\"invoiceNumber\":\"16479553\",\"externalReference\":\"PLP-Ly7AyekerOO1h5P4okjJmQqT\",\"deleted\":false,\"anticipated\":false,\"anticipable\":false,\"creditDate\":null,\"estimatedCreditDate\":null,\"transactionReceiptUrl\":null,\"nossoNumero\":\"12899792\",\"bankSlipUrl\":\"https://sandbox.asaas.com/b/pdf/adk3l5fb1tjmtikh\",\"lastInvoiceViewedDate\":null,\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0.00,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0.00,\"type\":\"FIXED\"},\"interest\":{\"value\":0.00,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}}', '2026-08-08 20:45:14', NULL, '2026-08-08 20:45:14'),
(3, 'evt_d26e303b238e509335ac9ba210e51b0f&17877768', 'PAYMENT_RECEIVED', 'pay_adk3l5fb1tjmtikh', '{\"id\":\"evt_d26e303b238e509335ac9ba210e51b0f&17877768\",\"event\":\"PAYMENT_RECEIVED\",\"dateCreated\":\"2026-08-08 20:50:36\",\"account\":{\"id\":\"9b4ee057-ddc5-47d4-8ad2-e9f7a89ff404\",\"ownerId\":null},\"payment\":{\"object\":\"payment\",\"id\":\"pay_adk3l5fb1tjmtikh\",\"dateCreated\":\"2026-08-08\",\"customer\":\"cus_000008565347\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":10.0,\"netValue\":8.01,\"originalValue\":null,\"interestValue\":null,\"description\":\"Palpite - copa - sjkdh\",\"billingType\":\"BOLETO\",\"canBePaidAfterDueDate\":true,\"confirmedDate\":\"2026-08-08\",\"pixTransaction\":null,\"status\":\"RECEIVED\",\"dueDate\":\"2026-08-08\",\"originalDueDate\":\"2026-08-08\",\"paymentDate\":\"2026-08-08\",\"clientPaymentDate\":\"2026-08-08\",\"installmentNumber\":null,\"invoiceUrl\":\"https://sandbox.asaas.com/i/adk3l5fb1tjmtikh\",\"invoiceNumber\":\"16479553\",\"externalReference\":\"PLP-Ly7AyekerOO1h5P4okjJmQqT\",\"deleted\":false,\"anticipated\":false,\"anticipable\":false,\"creditDate\":\"2026-08-08\",\"estimatedCreditDate\":\"2026-08-08\",\"transactionReceiptUrl\":\"https://sandbox.asaas.com/comprovantes/h/UEFZTUVOVF9SRUNFSVZFRDpwYXlfYWRrM2w1ZmIxdGptdGlraA%3D%3D\",\"nossoNumero\":\"12899792\",\"bankSlipUrl\":\"https://sandbox.asaas.com/b/pdf/adk3l5fb1tjmtikh\",\"lastInvoiceViewedDate\":null,\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0.00,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0.00,\"type\":\"FIXED\"},\"interest\":{\"value\":0.00,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}}', '2026-08-08 20:50:37', NULL, '2026-08-08 20:50:37'),
(4, 'evt_05b708f961d739ea7eba7e4db318f621&17877793', 'PAYMENT_CREATED', 'pay_3h08j28ezvwv7s4h', '{\"id\":\"evt_05b708f961d739ea7eba7e4db318f621&17877793\",\"event\":\"PAYMENT_CREATED\",\"dateCreated\":\"2026-08-08 20:53:39\",\"account\":{\"id\":\"9b4ee057-ddc5-47d4-8ad2-e9f7a89ff404\",\"ownerId\":null},\"payment\":{\"object\":\"payment\",\"id\":\"pay_3h08j28ezvwv7s4h\",\"dateCreated\":\"2026-08-08\",\"customer\":\"cus_000008622308\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":10.0,\"netValue\":8.01,\"originalValue\":null,\"interestValue\":null,\"description\":\"Palpite - copa - sjkdh\",\"billingType\":\"PIX\",\"pixTransaction\":null,\"status\":\"PENDING\",\"dueDate\":\"2026-08-08\",\"originalDueDate\":\"2026-08-08\",\"paymentDate\":null,\"clientPaymentDate\":null,\"installmentNumber\":null,\"invoiceUrl\":\"https://sandbox.asaas.com/i/3h08j28ezvwv7s4h\",\"invoiceNumber\":\"16479671\",\"externalReference\":\"PLP-A26fjczzrpVZEGFTDlX39kmW\",\"deleted\":false,\"anticipated\":false,\"anticipable\":false,\"creditDate\":null,\"estimatedCreditDate\":null,\"transactionReceiptUrl\":null,\"nossoNumero\":null,\"bankSlipUrl\":null,\"lastInvoiceViewedDate\":null,\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0.00,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0.00,\"type\":\"FIXED\"},\"interest\":{\"value\":0.00,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}}', '2026-08-08 20:53:39', NULL, '2026-08-08 20:53:39'),
(5, 'evt_d26e303b238e509335ac9ba210e51b0f&17877800', 'PAYMENT_RECEIVED', 'pay_3kiognjc3j25qv2s', '{\"id\":\"evt_d26e303b238e509335ac9ba210e51b0f&17877800\",\"event\":\"PAYMENT_RECEIVED\",\"dateCreated\":\"2026-08-08 20:54:33\",\"account\":{\"id\":\"9b4ee057-ddc5-47d4-8ad2-e9f7a89ff404\",\"ownerId\":null},\"payment\":{\"object\":\"payment\",\"id\":\"pay_3kiognjc3j25qv2s\",\"dateCreated\":\"2026-08-08\",\"customer\":\"cus_000008622165\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":10.0,\"netValue\":8.01,\"originalValue\":null,\"interestValue\":null,\"description\":\"Palpite - Copa do Mundo da JEP - Final - Brasil 1 x 0 Esc\\u00f3cia\",\"billingType\":\"BOLETO\",\"canBePaidAfterDueDate\":true,\"confirmedDate\":\"2026-08-08\",\"pixTransaction\":null,\"status\":\"RECEIVED\",\"dueDate\":\"2026-08-08\",\"originalDueDate\":\"2026-08-08\",\"paymentDate\":\"2026-08-08\",\"clientPaymentDate\":\"2026-08-08\",\"installmentNumber\":null,\"invoiceUrl\":\"https://sandbox.asaas.com/i/3kiognjc3j25qv2s\",\"invoiceNumber\":\"16479192\",\"externalReference\":\"PLP-yRGhxvEeEbhosPHDo8BNixv4\",\"deleted\":false,\"anticipated\":false,\"anticipable\":false,\"creditDate\":\"2026-08-08\",\"estimatedCreditDate\":\"2026-08-08\",\"transactionReceiptUrl\":\"https://sandbox.asaas.com/comprovantes/h/UEFZTUVOVF9SRUNFSVZFRDpwYXlfM2tpb2duamMzajI1cXYycw%3D%3D\",\"nossoNumero\":\"12899666\",\"bankSlipUrl\":\"https://sandbox.asaas.com/b/pdf/3kiognjc3j25qv2s\",\"lastInvoiceViewedDate\":null,\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0.00,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0.00,\"type\":\"FIXED\"},\"interest\":{\"value\":0.00,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}}', '2026-08-08 20:54:33', NULL, '2026-08-08 20:54:33'),
(6, 'evt_d26e303b238e509335ac9ba210e51b0f&17877802', 'PAYMENT_RECEIVED', 'pay_3h08j28ezvwv7s4h', '{\"id\":\"evt_d26e303b238e509335ac9ba210e51b0f&17877802\",\"event\":\"PAYMENT_RECEIVED\",\"dateCreated\":\"2026-08-08 20:54:57\",\"account\":{\"id\":\"9b4ee057-ddc5-47d4-8ad2-e9f7a89ff404\",\"ownerId\":null},\"payment\":{\"object\":\"payment\",\"id\":\"pay_3h08j28ezvwv7s4h\",\"dateCreated\":\"2026-08-08\",\"customer\":\"cus_000008622308\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":10.0,\"netValue\":8.01,\"originalValue\":null,\"interestValue\":null,\"description\":\"Palpite - copa - sjkdh\",\"billingType\":\"PIX\",\"confirmedDate\":\"2026-08-08\",\"pixTransaction\":\"e327c521-09e2-4b75-a292-eda264aa1cb4\",\"pixQrCodeId\":\"c24978e4-d97a-4efd-90eb-3547c9a04a1b\",\"status\":\"RECEIVED\",\"dueDate\":\"2026-08-08\",\"originalDueDate\":\"2026-08-08\",\"paymentDate\":\"2026-08-08\",\"clientPaymentDate\":\"2026-08-08\",\"installmentNumber\":null,\"invoiceUrl\":\"https://sandbox.asaas.com/i/3h08j28ezvwv7s4h\",\"invoiceNumber\":\"16479671\",\"externalReference\":\"PLP-A26fjczzrpVZEGFTDlX39kmW\",\"deleted\":false,\"anticipated\":false,\"anticipable\":false,\"creditDate\":\"2026-08-08\",\"estimatedCreditDate\":\"2026-08-08\",\"transactionReceiptUrl\":\"https://sandbox.asaas.com/comprovantes/h/UEFZTUVOVF9SRUNFSVZFRDpwYXlfM2gwOGoyOGV6dnd2N3M0aA%3D%3D\",\"nossoNumero\":null,\"bankSlipUrl\":null,\"lastInvoiceViewedDate\":null,\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0.00,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0.00,\"type\":\"FIXED\"},\"interest\":{\"value\":0.00,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}}', '2026-08-08 20:54:58', NULL, '2026-08-08 20:54:58'),
(7, 'evt_d545b50fbb17a226c5f4f4fd193922b7&17877825', 'PAYMENT_DELETED', 'pay_6bhbekqncag5d2ku', '{\"id\":\"evt_d545b50fbb17a226c5f4f4fd193922b7&17877825\",\"event\":\"PAYMENT_DELETED\",\"dateCreated\":\"2026-08-08 20:58:36\",\"account\":{\"id\":\"9b4ee057-ddc5-47d4-8ad2-e9f7a89ff404\",\"ownerId\":null},\"payment\":{\"object\":\"payment\",\"id\":\"pay_6bhbekqncag5d2ku\",\"dateCreated\":\"2026-08-08\",\"customer\":\"cus_000008622165\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":10.0,\"netValue\":8.01,\"originalValue\":null,\"interestValue\":null,\"description\":\"Palpite - Copa do Mundo da JEP - Final - Brasil 1 x 0 Esc\\u00f3cia\",\"billingType\":\"UNDEFINED\",\"pixTransaction\":null,\"status\":\"PENDING\",\"dueDate\":\"2026-08-08\",\"originalDueDate\":\"2026-08-08\",\"paymentDate\":null,\"clientPaymentDate\":null,\"installmentNumber\":null,\"invoiceUrl\":\"https://sandbox.asaas.com/i/6bhbekqncag5d2ku\",\"invoiceNumber\":\"16479226\",\"externalReference\":\"PLP-Xd-uZCZQF3PuAZYw78hqcyrw\",\"deleted\":true,\"anticipated\":false,\"anticipable\":false,\"creditDate\":null,\"estimatedCreditDate\":null,\"transactionReceiptUrl\":\"https://sandbox.asaas.com/comprovantes/h/UEFZTUVOVF9ERUxFVEVEOnBheV82YmhiZWtxbmNhZzVkMmt1\",\"nossoNumero\":\"12899675\",\"bankSlipUrl\":\"https://sandbox.asaas.com/b/pdf/6bhbekqncag5d2ku\",\"lastInvoiceViewedDate\":null,\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0.00,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0.00,\"type\":\"FIXED\"},\"interest\":{\"value\":0.00,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}}', '2026-08-08 20:58:36', NULL, '2026-08-08 20:58:36'),
(8, 'evt_05b708f961d739ea7eba7e4db318f621&17877899', 'PAYMENT_CREATED', 'pay_jmfczxfnihtaup6k', '{\"id\":\"evt_05b708f961d739ea7eba7e4db318f621&17877899\",\"event\":\"PAYMENT_CREATED\",\"dateCreated\":\"2026-08-08 21:01:15\",\"account\":{\"id\":\"9b4ee057-ddc5-47d4-8ad2-e9f7a89ff404\",\"ownerId\":null},\"payment\":{\"object\":\"payment\",\"id\":\"pay_jmfczxfnihtaup6k\",\"dateCreated\":\"2026-08-08\",\"customer\":\"cus_000008622454\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":80.0,\"netValue\":78.01,\"originalValue\":null,\"interestValue\":null,\"description\":\"Inscri\\u00e7\\u00e3o - Retiro Paroquial 2026\",\"billingType\":\"PIX\",\"pixTransaction\":null,\"status\":\"PENDING\",\"dueDate\":\"2026-11-20\",\"originalDueDate\":\"2026-11-20\",\"paymentDate\":null,\"clientPaymentDate\":null,\"installmentNumber\":null,\"invoiceUrl\":\"https://sandbox.asaas.com/i/jmfczxfnihtaup6k\",\"invoiceNumber\":\"16479733\",\"externalReference\":\"2026sistemaevento-pag-pg-20260808-0c9dbb\",\"deleted\":false,\"anticipated\":false,\"anticipable\":false,\"creditDate\":null,\"estimatedCreditDate\":null,\"transactionReceiptUrl\":null,\"nossoNumero\":null,\"bankSlipUrl\":null,\"lastInvoiceViewedDate\":null,\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0.00,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0.00,\"type\":\"FIXED\"},\"interest\":{\"value\":0.00,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}}', '2026-08-08 21:01:15', NULL, '2026-08-08 21:01:15'),
(9, 'evt_d545b50fbb17a226c5f4f4fd193922b7&17877904', 'PAYMENT_DELETED', 'pay_cotoplj61ld7vbe0', '{\"id\":\"evt_d545b50fbb17a226c5f4f4fd193922b7&17877904\",\"event\":\"PAYMENT_DELETED\",\"dateCreated\":\"2026-08-08 21:01:48\",\"account\":{\"id\":\"9b4ee057-ddc5-47d4-8ad2-e9f7a89ff404\",\"ownerId\":null},\"payment\":{\"object\":\"payment\",\"id\":\"pay_cotoplj61ld7vbe0\",\"dateCreated\":\"2026-08-08\",\"customer\":\"cus_000008565347\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":10.0,\"netValue\":8.01,\"originalValue\":null,\"interestValue\":null,\"description\":\"Oferta - Oferta Teste 1\",\"billingType\":\"PIX\",\"pixTransaction\":null,\"status\":\"PENDING\",\"dueDate\":\"2026-08-08\",\"originalDueDate\":\"2026-08-08\",\"paymentDate\":null,\"clientPaymentDate\":null,\"installmentNumber\":null,\"invoiceUrl\":\"https://sandbox.asaas.com/i/cotoplj61ld7vbe0\",\"invoiceNumber\":\"16477176\",\"externalReference\":\"sistemaevento-oferta-1\",\"deleted\":true,\"anticipated\":false,\"anticipable\":false,\"creditDate\":null,\"estimatedCreditDate\":null,\"transactionReceiptUrl\":\"https://sandbox.asaas.com/comprovantes/h/UEFZTUVOVF9ERUxFVEVEOnBheV9jb3RvcGxqNjFsZDd2YmUw\",\"nossoNumero\":null,\"bankSlipUrl\":null,\"lastInvoiceViewedDate\":\"2026-08-08T20:35:18Z\",\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0.00,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0.00,\"type\":\"FIXED\"},\"interest\":{\"value\":0.00,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}}', '2026-08-08 21:01:47', NULL, '2026-08-08 21:01:47'),
(10, 'evt_d545b50fbb17a226c5f4f4fd193922b7&17877907', 'PAYMENT_DELETED', 'pay_eb7vyq2w8h5nhpe2', '{\"id\":\"evt_d545b50fbb17a226c5f4f4fd193922b7&17877907\",\"event\":\"PAYMENT_DELETED\",\"dateCreated\":\"2026-08-08 21:02:43\",\"account\":{\"id\":\"9b4ee057-ddc5-47d4-8ad2-e9f7a89ff404\",\"ownerId\":null},\"payment\":{\"object\":\"payment\",\"id\":\"pay_eb7vyq2w8h5nhpe2\",\"dateCreated\":\"2026-08-08\",\"customer\":\"cus_000008583165\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":81.99,\"netValue\":80.0,\"originalValue\":null,\"interestValue\":null,\"description\":\"Inscri\\u00e7\\u00e3o - Retiro Paroquial 2026\",\"billingType\":\"BOLETO\",\"canBePaidAfterDueDate\":true,\"pixTransaction\":null,\"status\":\"PENDING\",\"dueDate\":\"2026-11-20\",\"originalDueDate\":\"2026-11-20\",\"paymentDate\":null,\"clientPaymentDate\":null,\"installmentNumber\":null,\"invoiceUrl\":\"https://sandbox.asaas.com/i/eb7vyq2w8h5nhpe2\",\"invoiceNumber\":\"16475802\",\"externalReference\":\"2026sistemaevento-pag-pg-20260808-0f2e08\",\"deleted\":true,\"anticipated\":false,\"anticipable\":false,\"creditDate\":null,\"estimatedCreditDate\":null,\"transactionReceiptUrl\":\"https://sandbox.asaas.com/comprovantes/h/UEFZTUVOVF9ERUxFVEVEOnBheV9lYjd2eXEydzhoNW5ocGUy\",\"nossoNumero\":\"12898576\",\"bankSlipUrl\":\"https://sandbox.asaas.com/b/pdf/eb7vyq2w8h5nhpe2\",\"lastInvoiceViewedDate\":\"2026-08-08T18:10:12Z\",\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0.00,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0.00,\"type\":\"FIXED\"},\"interest\":{\"value\":0.00,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"daysAfterDueDateToRegistrationCancellation\":5,\"escrow\":null,\"refunds\":null}}', '2026-08-08 21:02:43', NULL, '2026-08-08 21:02:43'),
(11, 'evt_05b708f961d739ea7eba7e4db318f621&17877920', 'PAYMENT_CREATED', 'pay_gl4u3ulsun34st9e', '{\"id\":\"evt_05b708f961d739ea7eba7e4db318f621&17877920\",\"event\":\"PAYMENT_CREATED\",\"dateCreated\":\"2026-08-08 21:05:39\",\"account\":{\"id\":\"9b4ee057-ddc5-47d4-8ad2-e9f7a89ff404\",\"ownerId\":null},\"payment\":{\"object\":\"payment\",\"id\":\"pay_gl4u3ulsun34st9e\",\"dateCreated\":\"2026-08-08\",\"customer\":\"cus_000008622460\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":10.0,\"netValue\":8.01,\"originalValue\":null,\"interestValue\":null,\"description\":\"Palpite - copa - A 0 x 0 B\",\"billingType\":\"PIX\",\"pixTransaction\":null,\"status\":\"PENDING\",\"dueDate\":\"2026-08-08\",\"originalDueDate\":\"2026-08-08\",\"paymentDate\":null,\"clientPaymentDate\":null,\"installmentNumber\":null,\"invoiceUrl\":\"https://sandbox.asaas.com/i/gl4u3ulsun34st9e\",\"invoiceNumber\":\"16479765\",\"externalReference\":\"PLP-UqjZGP1qI-a5pBvi0_qdhxfR\",\"deleted\":false,\"anticipated\":false,\"anticipable\":false,\"creditDate\":null,\"estimatedCreditDate\":null,\"transactionReceiptUrl\":null,\"nossoNumero\":null,\"bankSlipUrl\":null,\"lastInvoiceViewedDate\":null,\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0.00,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0.00,\"type\":\"FIXED\"},\"interest\":{\"value\":0.00,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}}', '2026-08-08 21:05:39', NULL, '2026-08-08 21:05:39'),
(12, 'evt_d545b50fbb17a226c5f4f4fd193922b7&17878077', 'PAYMENT_DELETED', 'pay_jmfczxfnihtaup6k', '{\"id\":\"evt_d545b50fbb17a226c5f4f4fd193922b7&17878077\",\"event\":\"PAYMENT_DELETED\",\"dateCreated\":\"2026-08-08 21:12:39\",\"account\":{\"id\":\"9b4ee057-ddc5-47d4-8ad2-e9f7a89ff404\",\"ownerId\":null},\"payment\":{\"object\":\"payment\",\"id\":\"pay_jmfczxfnihtaup6k\",\"dateCreated\":\"2026-08-08\",\"customer\":\"cus_000008622454\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":80.0,\"netValue\":78.01,\"originalValue\":null,\"interestValue\":null,\"description\":\"Inscri\\u00e7\\u00e3o - Retiro Paroquial 2026\",\"billingType\":\"PIX\",\"pixTransaction\":null,\"status\":\"PENDING\",\"dueDate\":\"2026-11-20\",\"originalDueDate\":\"2026-11-20\",\"paymentDate\":null,\"clientPaymentDate\":null,\"installmentNumber\":null,\"invoiceUrl\":\"https://sandbox.asaas.com/i/jmfczxfnihtaup6k\",\"invoiceNumber\":\"16479733\",\"externalReference\":\"2026sistemaevento-pag-pg-20260808-0c9dbb\",\"deleted\":true,\"anticipated\":false,\"anticipable\":false,\"creditDate\":null,\"estimatedCreditDate\":null,\"transactionReceiptUrl\":\"https://sandbox.asaas.com/comprovantes/h/UEFZTUVOVF9ERUxFVEVEOnBheV9qbWZjenhmbmlodGF1cDZr\",\"nossoNumero\":null,\"bankSlipUrl\":null,\"lastInvoiceViewedDate\":\"2026-08-09T00:01:42Z\",\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0.00,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0.00,\"type\":\"FIXED\"},\"interest\":{\"value\":0.00,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}}', '2026-08-08 21:12:39', NULL, '2026-08-08 21:12:39'),
(13, 'evt_d26e303b238e509335ac9ba210e51b0f&17878086', 'PAYMENT_RECEIVED', 'pay_gl4u3ulsun34st9e', '{\"id\":\"evt_d26e303b238e509335ac9ba210e51b0f&17878086\",\"event\":\"PAYMENT_RECEIVED\",\"dateCreated\":\"2026-08-08 21:14:51\",\"account\":{\"id\":\"9b4ee057-ddc5-47d4-8ad2-e9f7a89ff404\",\"ownerId\":null},\"payment\":{\"object\":\"payment\",\"id\":\"pay_gl4u3ulsun34st9e\",\"dateCreated\":\"2026-08-08\",\"customer\":\"cus_000008622460\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":10.0,\"netValue\":8.01,\"originalValue\":null,\"interestValue\":null,\"description\":\"Palpite - copa - A 0 x 0 B\",\"billingType\":\"PIX\",\"confirmedDate\":\"2026-08-08\",\"pixTransaction\":\"70255f60-25d4-43df-a022-fcbf83323370\",\"pixQrCodeId\":\"c4266b92-b34c-409e-8fc5-aa24cde63198\",\"status\":\"RECEIVED\",\"dueDate\":\"2026-08-08\",\"originalDueDate\":\"2026-08-08\",\"paymentDate\":\"2026-08-08\",\"clientPaymentDate\":\"2026-08-08\",\"installmentNumber\":null,\"invoiceUrl\":\"https://sandbox.asaas.com/i/gl4u3ulsun34st9e\",\"invoiceNumber\":\"16479765\",\"externalReference\":\"PLP-UqjZGP1qI-a5pBvi0_qdhxfR\",\"deleted\":false,\"anticipated\":false,\"anticipable\":false,\"creditDate\":\"2026-08-08\",\"estimatedCreditDate\":\"2026-08-08\",\"transactionReceiptUrl\":\"https://sandbox.asaas.com/comprovantes/h/UEFZTUVOVF9SRUNFSVZFRDpwYXlfZ2w0dTN1bHN1bjM0c3Q5ZQ%3D%3D\",\"nossoNumero\":null,\"bankSlipUrl\":null,\"lastInvoiceViewedDate\":null,\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0.00,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0.00,\"type\":\"FIXED\"},\"interest\":{\"value\":0.00,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}}', '2026-08-08 21:14:51', NULL, '2026-08-08 21:14:51'),
(14, 'evt_05b708f961d739ea7eba7e4db318f621&17882001', 'PAYMENT_CREATED', 'pay_7w0nl0esxkodu8wn', '{\"id\":\"evt_05b708f961d739ea7eba7e4db318f621&17882001\",\"event\":\"PAYMENT_CREATED\",\"dateCreated\":\"2026-08-09 08:07:55\",\"account\":{\"id\":\"9b4ee057-ddc5-47d4-8ad2-e9f7a89ff404\",\"ownerId\":null},\"payment\":{\"object\":\"payment\",\"id\":\"pay_7w0nl0esxkodu8wn\",\"dateCreated\":\"2026-08-09\",\"customer\":\"cus_000008625685\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":10.0,\"netValue\":8.01,\"originalValue\":null,\"interestValue\":null,\"description\":\"Oferta - Oferta Local - Par\\u00f3quia\",\"billingType\":\"PIX\",\"pixTransaction\":null,\"status\":\"PENDING\",\"dueDate\":\"2026-08-09\",\"originalDueDate\":\"2026-08-09\",\"paymentDate\":null,\"clientPaymentDate\":null,\"installmentNumber\":null,\"invoiceUrl\":\"https://sandbox.asaas.com/i/7w0nl0esxkodu8wn\",\"invoiceNumber\":\"16487683\",\"externalReference\":\"OFR-4JtUQZj5RLpaK-OsP-hjlHm7\",\"deleted\":false,\"anticipated\":false,\"anticipable\":false,\"creditDate\":null,\"estimatedCreditDate\":null,\"transactionReceiptUrl\":null,\"nossoNumero\":null,\"bankSlipUrl\":null,\"lastInvoiceViewedDate\":null,\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0.00,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0.00,\"type\":\"FIXED\"},\"interest\":{\"value\":0.00,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}}', '2026-08-09 08:07:55', NULL, '2026-08-09 08:07:55'),
(15, 'evt_05b708f961d739ea7eba7e4db318f621&17882003', 'PAYMENT_CREATED', 'pay_1sltkb71mr9rkpo0', '{\"id\":\"evt_05b708f961d739ea7eba7e4db318f621&17882003\",\"event\":\"PAYMENT_CREATED\",\"dateCreated\":\"2026-08-09 08:10:04\",\"account\":{\"id\":\"9b4ee057-ddc5-47d4-8ad2-e9f7a89ff404\",\"ownerId\":null},\"payment\":{\"object\":\"payment\",\"id\":\"pay_1sltkb71mr9rkpo0\",\"dateCreated\":\"2026-08-09\",\"customer\":\"cus_000008625695\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":80.0,\"netValue\":78.01,\"originalValue\":null,\"interestValue\":null,\"description\":\"Inscri\\u00e7\\u00e3o - Retiro Paroquial 2026\",\"billingType\":\"PIX\",\"pixTransaction\":null,\"status\":\"PENDING\",\"dueDate\":\"2026-11-20\",\"originalDueDate\":\"2026-11-20\",\"paymentDate\":null,\"clientPaymentDate\":null,\"installmentNumber\":null,\"invoiceUrl\":\"https://sandbox.asaas.com/i/1sltkb71mr9rkpo0\",\"invoiceNumber\":\"16487684\",\"externalReference\":\"2026sistemaevento-pag-pg-20260809-9cdf3f\",\"deleted\":false,\"anticipated\":false,\"anticipable\":false,\"creditDate\":null,\"estimatedCreditDate\":null,\"transactionReceiptUrl\":null,\"nossoNumero\":null,\"bankSlipUrl\":null,\"lastInvoiceViewedDate\":null,\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0.00,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0.00,\"type\":\"FIXED\"},\"interest\":{\"value\":0.00,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}}', '2026-08-09 08:10:04', NULL, '2026-08-09 08:10:04'),
(16, 'evt_d26e303b238e509335ac9ba210e51b0f&17882010', 'PAYMENT_RECEIVED', 'pay_7w0nl0esxkodu8wn', '{\"id\":\"evt_d26e303b238e509335ac9ba210e51b0f&17882010\",\"event\":\"PAYMENT_RECEIVED\",\"dateCreated\":\"2026-08-09 08:17:40\",\"account\":{\"id\":\"9b4ee057-ddc5-47d4-8ad2-e9f7a89ff404\",\"ownerId\":null},\"payment\":{\"object\":\"payment\",\"id\":\"pay_7w0nl0esxkodu8wn\",\"dateCreated\":\"2026-08-09\",\"customer\":\"cus_000008625685\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":10.0,\"netValue\":8.01,\"originalValue\":null,\"interestValue\":null,\"description\":\"Oferta - Oferta Local - Par\\u00f3quia\",\"billingType\":\"PIX\",\"confirmedDate\":\"2026-08-09\",\"pixTransaction\":\"d29ee157-21df-49eb-9136-035ace982fb9\",\"pixQrCodeId\":\"53577a67-f340-4848-9b51-2f22c0c11553\",\"status\":\"RECEIVED\",\"dueDate\":\"2026-08-09\",\"originalDueDate\":\"2026-08-09\",\"paymentDate\":\"2026-08-09\",\"clientPaymentDate\":\"2026-08-09\",\"installmentNumber\":null,\"invoiceUrl\":\"https://sandbox.asaas.com/i/7w0nl0esxkodu8wn\",\"invoiceNumber\":\"16487683\",\"externalReference\":\"OFR-4JtUQZj5RLpaK-OsP-hjlHm7\",\"deleted\":false,\"anticipated\":false,\"anticipable\":false,\"creditDate\":\"2026-08-09\",\"estimatedCreditDate\":\"2026-08-09\",\"transactionReceiptUrl\":\"https://sandbox.asaas.com/comprovantes/h/UEFZTUVOVF9SRUNFSVZFRDpwYXlfN3cwbmwwZXN4a29kdTh3bg%3D%3D\",\"nossoNumero\":null,\"bankSlipUrl\":null,\"lastInvoiceViewedDate\":null,\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0.00,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0.00,\"type\":\"FIXED\"},\"interest\":{\"value\":0.00,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}}', '2026-08-09 08:17:40', NULL, '2026-08-09 08:17:40'),
(17, 'evt_d26e303b238e509335ac9ba210e51b0f&17882018', 'PAYMENT_RECEIVED', 'pay_1sltkb71mr9rkpo0', '{\"id\":\"evt_d26e303b238e509335ac9ba210e51b0f&17882018\",\"event\":\"PAYMENT_RECEIVED\",\"dateCreated\":\"2026-08-09 08:20:15\",\"account\":{\"id\":\"9b4ee057-ddc5-47d4-8ad2-e9f7a89ff404\",\"ownerId\":null},\"payment\":{\"object\":\"payment\",\"id\":\"pay_1sltkb71mr9rkpo0\",\"dateCreated\":\"2026-08-09\",\"customer\":\"cus_000008625695\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":80.0,\"netValue\":78.01,\"originalValue\":null,\"interestValue\":null,\"description\":\"Inscri\\u00e7\\u00e3o - Retiro Paroquial 2026\",\"billingType\":\"PIX\",\"confirmedDate\":\"2026-08-09\",\"pixTransaction\":\"a6e9a9ef-ec18-4886-8f41-3ba039f18c05\",\"pixQrCodeId\":\"743cd990-a7db-4334-a4bc-b93068420dde\",\"status\":\"RECEIVED\",\"dueDate\":\"2026-11-20\",\"originalDueDate\":\"2026-11-20\",\"paymentDate\":\"2026-08-09\",\"clientPaymentDate\":\"2026-08-09\",\"installmentNumber\":null,\"invoiceUrl\":\"https://sandbox.asaas.com/i/1sltkb71mr9rkpo0\",\"invoiceNumber\":\"16487684\",\"externalReference\":\"2026sistemaevento-pag-pg-20260809-9cdf3f\",\"deleted\":false,\"anticipated\":false,\"anticipable\":false,\"creditDate\":\"2026-08-09\",\"estimatedCreditDate\":\"2026-08-09\",\"transactionReceiptUrl\":\"https://sandbox.asaas.com/comprovantes/h/UEFZTUVOVF9SRUNFSVZFRDpwYXlfMXNsdGtiNzFtcjlya3BvMA%3D%3D\",\"nossoNumero\":null,\"bankSlipUrl\":null,\"lastInvoiceViewedDate\":\"2026-08-09T11:10:27Z\",\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0.00,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0.00,\"type\":\"FIXED\"},\"interest\":{\"value\":0.00,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}}', '2026-08-09 08:20:14', NULL, '2026-08-09 08:20:14'),
(18, 'evt_05b708f961d739ea7eba7e4db318f621&17882276', 'PAYMENT_CREATED', 'pay_c9wnjxljnr6305cl', '{\"id\":\"evt_05b708f961d739ea7eba7e4db318f621&17882276\",\"event\":\"PAYMENT_CREATED\",\"dateCreated\":\"2026-08-09 09:33:19\",\"account\":{\"id\":\"9b4ee057-ddc5-47d4-8ad2-e9f7a89ff404\",\"ownerId\":null},\"payment\":{\"object\":\"payment\",\"id\":\"pay_c9wnjxljnr6305cl\",\"dateCreated\":\"2026-08-09\",\"customer\":\"cus_000008626167\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":10.0,\"netValue\":8.01,\"originalValue\":null,\"interestValue\":null,\"description\":\"Oferta - Oferta Local - Par\\u00f3quia\",\"billingType\":\"BOLETO\",\"canBePaidAfterDueDate\":true,\"pixTransaction\":null,\"status\":\"PENDING\",\"dueDate\":\"2026-08-10\",\"originalDueDate\":\"2026-08-10\",\"paymentDate\":null,\"clientPaymentDate\":null,\"installmentNumber\":null,\"invoiceUrl\":\"https://sandbox.asaas.com/i/c9wnjxljnr6305cl\",\"invoiceNumber\":\"16487765\",\"externalReference\":\"OFR-2Fo7zf0FQFU32USI5TGHCqLa\",\"deleted\":false,\"anticipated\":false,\"anticipable\":false,\"creditDate\":null,\"estimatedCreditDate\":null,\"transactionReceiptUrl\":null,\"nossoNumero\":\"12901803\",\"bankSlipUrl\":\"https://sandbox.asaas.com/b/pdf/c9wnjxljnr6305cl\",\"lastInvoiceViewedDate\":null,\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0.00,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0.00,\"type\":\"FIXED\"},\"interest\":{\"value\":0.00,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}}', '2026-08-09 09:33:19', NULL, '2026-08-09 09:33:19'),
(19, 'evt_05b708f961d739ea7eba7e4db318f621&1457509351', 'PAYMENT_CREATED', 'pay_jad2btsdwk9cmtsy', '{\"id\":\"evt_05b708f961d739ea7eba7e4db318f621&1457509351\",\"event\":\"PAYMENT_CREATED\",\"dateCreated\":\"2026-08-09 11:52:32\",\"account\":{\"id\":\"762d7255-cdaf-45ba-a35e-d56fd3fa6cd0\",\"ownerId\":\"0b81ca92-df70-4386-9368-d93e255b778c\"},\"payment\":{\"object\":\"payment\",\"id\":\"pay_jad2btsdwk9cmtsy\",\"dateCreated\":\"2026-08-09\",\"customer\":\"cus_000192684406\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":10.0,\"netValue\":8.11,\"originalValue\":null,\"interestValue\":null,\"description\":\"Oferta - Ofertas Locais Agosto 2026 - Par\\u00f3quia\",\"billingType\":\"PIX\",\"pixTransaction\":null,\"status\":\"PENDING\",\"dueDate\":\"2026-08-09\",\"originalDueDate\":\"2026-08-09\",\"paymentDate\":null,\"clientPaymentDate\":null,\"installmentNumber\":null,\"invoiceUrl\":\"https://www.asaas.com/i/jad2btsdwk9cmtsy\",\"invoiceNumber\":\"879101951\",\"externalReference\":\"OFR-oKjvp6SRdCw2diq4Q31jMfnd\",\"deleted\":false,\"anticipated\":false,\"anticipable\":false,\"creditDate\":null,\"estimatedCreditDate\":null,\"transactionReceiptUrl\":null,\"nossoNumero\":null,\"bankSlipUrl\":null,\"lastInvoiceViewedDate\":null,\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0.00,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0.00,\"type\":\"FIXED\"},\"interest\":{\"value\":0.00,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}}', '2026-08-09 11:52:33', NULL, '2026-08-09 11:52:33'),
(20, 'evt_d26e303b238e509335ac9ba210e51b0f&1457510768', 'PAYMENT_RECEIVED', 'pay_jad2btsdwk9cmtsy', '{\"id\":\"evt_d26e303b238e509335ac9ba210e51b0f&1457510768\",\"event\":\"PAYMENT_RECEIVED\",\"dateCreated\":\"2026-08-09 11:55:03\",\"account\":{\"id\":\"762d7255-cdaf-45ba-a35e-d56fd3fa6cd0\",\"ownerId\":\"0b81ca92-df70-4386-9368-d93e255b778c\"},\"payment\":{\"object\":\"payment\",\"id\":\"pay_jad2btsdwk9cmtsy\",\"dateCreated\":\"2026-08-09\",\"customer\":\"cus_000192684406\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":10.0,\"netValue\":8.11,\"originalValue\":null,\"interestValue\":null,\"description\":\"Oferta - Ofertas Locais Agosto 2026 - Par\\u00f3quia\",\"billingType\":\"PIX\",\"confirmedDate\":\"2026-08-09\",\"pixTransaction\":\"1604ca25-ee61-4667-8ca2-32d3f0f2d721\",\"pixQrCodeId\":\"ANDREGUSTAVOHENSSL00000684450226ASA\",\"status\":\"RECEIVED\",\"dueDate\":\"2026-08-09\",\"originalDueDate\":\"2026-08-09\",\"paymentDate\":\"2026-08-09\",\"clientPaymentDate\":\"2026-08-09\",\"installmentNumber\":null,\"invoiceUrl\":\"https://www.asaas.com/i/jad2btsdwk9cmtsy\",\"invoiceNumber\":\"879101951\",\"externalReference\":\"OFR-oKjvp6SRdCw2diq4Q31jMfnd\",\"deleted\":false,\"anticipated\":false,\"anticipable\":false,\"creditDate\":\"2026-08-09\",\"estimatedCreditDate\":\"2026-08-09\",\"transactionReceiptUrl\":\"https://www.asaas.com/comprovantes/h/UEFZTUVOVF9SRUNFSVZFRDpwYXlfamFkMmJ0c2R3azljbXRzeQ%3D%3D\",\"nossoNumero\":null,\"bankSlipUrl\":null,\"lastInvoiceViewedDate\":null,\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0.00,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0.00,\"type\":\"FIXED\"},\"interest\":{\"value\":0.00,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}}', '2026-08-09 11:55:03', NULL, '2026-08-09 11:55:03'),
(21, 'evt_05b708f961d739ea7eba7e4db318f621&17883637', 'PAYMENT_CREATED', 'pay_zrr90il5m4pbwg06', '{\"id\":\"evt_05b708f961d739ea7eba7e4db318f621&17883637\",\"event\":\"PAYMENT_CREATED\",\"dateCreated\":\"2026-08-09 12:51:24\",\"account\":{\"id\":\"9b4ee057-ddc5-47d4-8ad2-e9f7a89ff404\",\"ownerId\":null},\"payment\":{\"object\":\"payment\",\"id\":\"pay_zrr90il5m4pbwg06\",\"dateCreated\":\"2026-08-09\",\"customer\":\"cus_000008627459\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":12.0,\"netValue\":10.01,\"originalValue\":null,\"interestValue\":null,\"description\":\"Palpite - Copa do Mundo Brasil x Jap\\u00e3o - Brasil 1 x 0 Jap\\u00e3o\",\"billingType\":\"PIX\",\"pixTransaction\":null,\"status\":\"PENDING\",\"dueDate\":\"2026-08-09\",\"originalDueDate\":\"2026-08-09\",\"paymentDate\":null,\"clientPaymentDate\":null,\"installmentNumber\":null,\"invoiceUrl\":\"https://sandbox.asaas.com/i/zrr90il5m4pbwg06\",\"invoiceNumber\":\"16488577\",\"externalReference\":\"PLP-JbBPvpgh-JqVHquOuuk0Du8K\",\"deleted\":false,\"anticipated\":false,\"anticipable\":false,\"creditDate\":null,\"estimatedCreditDate\":null,\"transactionReceiptUrl\":null,\"nossoNumero\":null,\"bankSlipUrl\":null,\"lastInvoiceViewedDate\":null,\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0.00,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0.00,\"type\":\"FIXED\"},\"interest\":{\"value\":0.00,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}}', '2026-08-09 12:51:25', NULL, '2026-08-09 12:51:25'),
(22, 'evt_d26e303b238e509335ac9ba210e51b0f&17883672', 'PAYMENT_RECEIVED', 'pay_c9wnjxljnr6305cl', '{\"id\":\"evt_d26e303b238e509335ac9ba210e51b0f&17883672\",\"event\":\"PAYMENT_RECEIVED\",\"dateCreated\":\"2026-08-09 12:59:35\",\"account\":{\"id\":\"9b4ee057-ddc5-47d4-8ad2-e9f7a89ff404\",\"ownerId\":null},\"payment\":{\"object\":\"payment\",\"id\":\"pay_c9wnjxljnr6305cl\",\"dateCreated\":\"2026-08-09\",\"customer\":\"cus_000008626167\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":10.0,\"netValue\":8.01,\"originalValue\":null,\"interestValue\":null,\"description\":\"Oferta - Oferta Local - Par\\u00f3quia\",\"billingType\":\"BOLETO\",\"canBePaidAfterDueDate\":true,\"confirmedDate\":\"2026-08-09\",\"pixTransaction\":null,\"status\":\"RECEIVED\",\"dueDate\":\"2026-08-10\",\"originalDueDate\":\"2026-08-10\",\"paymentDate\":\"2026-08-09\",\"clientPaymentDate\":\"2026-08-09\",\"installmentNumber\":null,\"invoiceUrl\":\"https://sandbox.asaas.com/i/c9wnjxljnr6305cl\",\"invoiceNumber\":\"16487765\",\"externalReference\":\"OFR-2Fo7zf0FQFU32USI5TGHCqLa\",\"deleted\":false,\"anticipated\":false,\"anticipable\":false,\"creditDate\":\"2026-08-09\",\"estimatedCreditDate\":\"2026-08-09\",\"transactionReceiptUrl\":\"https://sandbox.asaas.com/comprovantes/h/UEFZTUVOVF9SRUNFSVZFRDpwYXlfYzl3bmp4bGpucjYzMDVjbA%3D%3D\",\"nossoNumero\":\"12901803\",\"bankSlipUrl\":\"https://sandbox.asaas.com/b/pdf/c9wnjxljnr6305cl\",\"lastInvoiceViewedDate\":null,\"lastBankSlipViewedDate\":\"2026-08-09T12:33:29Z\",\"discount\":{\"value\":0.00,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0.00,\"type\":\"FIXED\"},\"interest\":{\"value\":0.00,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}}', '2026-08-09 12:59:35', NULL, '2026-08-09 12:59:35'),
(23, 'evt_d26e303b238e509335ac9ba210e51b0f&17883706', 'PAYMENT_RECEIVED', 'pay_zrr90il5m4pbwg06', '{\"id\":\"evt_d26e303b238e509335ac9ba210e51b0f&17883706\",\"event\":\"PAYMENT_RECEIVED\",\"dateCreated\":\"2026-08-09 13:09:30\",\"account\":{\"id\":\"9b4ee057-ddc5-47d4-8ad2-e9f7a89ff404\",\"ownerId\":null},\"payment\":{\"object\":\"payment\",\"id\":\"pay_zrr90il5m4pbwg06\",\"dateCreated\":\"2026-08-09\",\"customer\":\"cus_000008627459\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":12.0,\"netValue\":10.01,\"originalValue\":null,\"interestValue\":null,\"description\":\"Palpite - Copa do Mundo Brasil x Jap\\u00e3o - Brasil 1 x 0 Jap\\u00e3o\",\"billingType\":\"PIX\",\"confirmedDate\":\"2026-08-09\",\"pixTransaction\":\"a0ea2016-ddfb-4df7-8c6e-236d491ea209\",\"pixQrCodeId\":\"517a8c82-bc18-480d-9380-4b7345df9ef3\",\"status\":\"RECEIVED\",\"dueDate\":\"2026-08-09\",\"originalDueDate\":\"2026-08-09\",\"paymentDate\":\"2026-08-09\",\"clientPaymentDate\":\"2026-08-09\",\"installmentNumber\":null,\"invoiceUrl\":\"https://sandbox.asaas.com/i/zrr90il5m4pbwg06\",\"invoiceNumber\":\"16488577\",\"externalReference\":\"PLP-JbBPvpgh-JqVHquOuuk0Du8K\",\"deleted\":false,\"anticipated\":false,\"anticipable\":false,\"creditDate\":\"2026-08-09\",\"estimatedCreditDate\":\"2026-08-09\",\"transactionReceiptUrl\":\"https://sandbox.asaas.com/comprovantes/h/UEFZTUVOVF9SRUNFSVZFRDpwYXlfenJyOTBpbDVtNHBid2cwNg%3D%3D\",\"nossoNumero\":null,\"bankSlipUrl\":null,\"lastInvoiceViewedDate\":null,\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0.00,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0.00,\"type\":\"FIXED\"},\"interest\":{\"value\":0.00,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}}', '2026-08-09 13:09:31', NULL, '2026-08-09 13:09:31');

-- --------------------------------------------------------

--
-- Estrutura da tabela `checkout_limites`
--

CREATE TABLE `checkout_limites` (
  `chave` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `janelaInicio` datetime NOT NULL,
  `tentativas` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `atualizadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `checkout_limites`
--

INSERT INTO `checkout_limites` (`chave`, `tipo`, `janelaInicio`, `tentativas`, `atualizadoEm`) VALUES
('c0eac48d159a75ff057c0c9803e8fcce64d351fc91181f082713728330f0d30b', 'checkout_ip', '2026-08-09 09:33:18', 1, '2026-08-09 09:33:18'),
('dac4a8b23648ad9a902a3f1377a25f7dde521bf284e97e9a11b6244255928b25', 'checkout_ip', '2026-08-09 12:51:22', 1, '2026-08-09 12:51:22');

-- --------------------------------------------------------

--
-- Estrutura da tabela `cidade`
--

CREATE TABLE `cidade` (
  `idCidade` int(11) NOT NULL,
  `nomeCidade` varchar(67) COLLATE utf8_unicode_ci NOT NULL,
  `UF` char(6) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `cidade`
--

INSERT INTO `cidade` (`idCidade`, `nomeCidade`, `UF`) VALUES
(1, 'Parobé', 'RS'),
(2, 'Taquara', 'RS');

-- --------------------------------------------------------

--
-- Estrutura da tabela `citbiblicas`
--

CREATE TABLE `citbiblicas` (
  `codCitBiblicas` int(11) NOT NULL,
  `texto` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `versiculo` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `citbiblicas`
--

INSERT INTO `citbiblicas` (`codCitBiblicas`, `texto`, `versiculo`) VALUES
(1, 'Um coração puro é aquele que observa e pondera o que Deus diz e substitui os seus próprios pensamentos pela Palavra de Deus.', 'Martim Lutero'),
(2, 'Que cada um use o seu próprio dom para o bem dos outros!\r\n', '1Pedro 4.10b'),
(3, 'Jesus Cristo diz: Eu sou o caminho, a verdade e a vida. Ninguém vem ao Pai senão por mim.', 'João 14.6'),
(4, 'Importa, acima de tudo, que homem e mulher convivam em amor e concórdia, para que um queira ao outro de coração e com fidelidade integral.', 'Martim Lutero'),
(5, 'Jesus Cristo diz: Eu sou o caminho, a verdade e a vida. Ninguém vem ao Pai senão por mim.Deus, ao atender uma oração, atende-a de modo maravilhoso e rico, assim que o coração humano é por demais apertado para poder compreendê-lo.', 'Martim Lutero'),
(6, 'A Deus, aos pais e aos mestres, nunca se poderá agradecer e recompensar de modo suficiente.', 'Martim Lutero'),
(7, 'As pessoas podem fazer seus planos, porém é o Senhor Deus quem dá a última palavra.', 'Provérbios 16.1'),
(8, 'A Palavra de Deus é a relíquia das relíquias, a única, na verdade, que nós cristãos reconhecemos e temos.', 'Martim Lutero'),
(9, 'O Senhor é o Deus verdadeiro. Ele é o Deus vivo, o Rei eterno.', 'Jeremias 10.10'),
(10, 'Quem está unido com Cristo é uma nova pessoa.\r\n', ' 2Coríntios 5.1'),
(11, 'A fé não pode aderir ou agarrar-se a qualquer coisa que tem valor nesta vida, mas rompe os seus limites e se agarra ao que se encontra acima e fora desta vida, ao próprio Deus.', ' Martim Lutero'),
(12, 'A intenção real de Deus é, portanto, que não permitamos venha qualquer pessoa sofrer dano e que, ao contrário, demonstremos todo o bem e o amor.', 'Martim Lutero'),
(13, 'Jesus Cristo diz: Passarão o céu e a terra, porém as minhas palavras não passarão.', 'Lucas 21.33'),
(14, 'Em todo o universo, não há nada que possa nos separar do amor de Deus, que é nosso por meio de Cristo Jesus, o nosso Senhor.', 'Romanos 8.39'),
(15, 'O ponto principal do Evangelho, o seu fundamento, é que, antes de tomares Cristo como exemplo, o acolhas e o reconheças como presente que foi dado a ti, pessoalmente, por Deus.', 'Martim Lutero'),
(16, 'Que ninguém procure somente os seus próprios interesses, mas também os dos outros.', 'Filipenses 2.4'),
(17, 'Assim como o fogo sempre produz calor e fumaça, também a fé sempre vem acompanhada do amor.', 'Martim Lutero'),
(18, 'Ó Senhor, tu somente és o Deus de todos os reinos da terra; tu fizeste os céus e a terra.', '2Reis 19.15'),
(19, 'Deus diz: Não fiquem com medo, pois estou com vocês. Eu lhes dou forças e os ajudo.', 'Isaias 41.10'),
(20, 'Vivam como pessoas que pertencem à luz, pois a luz produz uma grande colheita de todo tipo de bondade, honestidade e verdade.', 'Efésios 5.8-9'),
(21, 'Ainda não somos o que devemos ser, mas em tal seremos transformados. Nem tudo já aconteceu e nem tudo já foi feito, mas está em andamento. A vida cristã não é o fim, mas o caminho. Ainda nem tudo está luzindo e brilhando, mas tudo está melhorando.', 'Martim Lutero'),
(22, 'Dêem graças a Deus, o Senhor, porque Ele é bom e porque o seu amor dura para sempre.', 'Salmo 118.1'),
(23, 'O verdadeiro cristão não vive na terra para si próprio, mas para o próximo e lhe serve.', 'Martim Lutero'),
(24, 'A fé é um contínuo e persistente olhar para Cristo.', 'Martim Lutero'),
(25, 'O amor só é verdadeiro quando também a fé é verdadeira. É o amor que não busca o seu bem, mas o bem do próximo', 'Martim Lutero'),
(26, 'O Senhor guardará você. Ele está sempre ao seu lado para protegê-lo. Ele o guardará quando você for e quando voltar, agora e sempre.', 'Salmo 121.5 e 8'),
(27, 'Tornai-vos, pois, praticantes da Palavra e não somente ouvintes', 'Tiago 1.22'),
(28, 'Fé e amor perfazem a natureza do cristão. A fé recebe, o amor dá. A fé leva a pessoa a Deus e o amor a aproxima das demais. Por meio da fé, ela aceita os benefícios de Deus. Por meio do amor, ela beneficia os seus semelhantes', 'Martim Lutero'),
(29, 'Arrisco e coloco a minha confiança somente no único Deus, invisível e incompreensível, o que criou o céu e a terra.', 'Martim Lutero'),
(30, 'Pela graça sois salvos, mediante a fé, e isto não vem de vós: é dom de Deus', 'Efésios 2.8'),
(31, 'Hoje, tenho muito a fazer, portanto, hoje, vou precisar orar muito', 'Martim Lutero'),
(32, 'A lei inteira se resume em um mandamento só: ame os outros como você ama a você mesmo.', 'Gálatas 5.14');

-- --------------------------------------------------------

--
-- Estrutura da tabela `comprovantes`
--

CREATE TABLE `comprovantes` (
  `idComprovante` bigint(20) UNSIGNED NOT NULL,
  `idPagamento` bigint(20) UNSIGNED NOT NULL,
  `numero` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `emitidoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `comprovantes`
--

INSERT INTO `comprovantes` (`idComprovante`, `idPagamento`, `numero`, `token`, `emitidoEm`) VALUES
(1, 4, 'CMP-20260808-00000004', '43WB9RW_17C6X3xKIKxnOmw_V3HqGf3iLN9EGA7ra8s', '2026-08-08 20:50:37'),
(2, 5, 'CMP-20260808-00000005', 'amDwLRSYRRZcSWObnWyZ-93oPX9kgjrYxOVbJPJvb-4', '2026-08-08 20:54:58'),
(3, 6, 'CMP-20260808-00000006', 'DJaPb0HUUN6lH9ci4qxHumcgUPsFLoAV_xiSYUF6w5Q', '2026-08-08 21:14:51'),
(4, 7, 'CMP-20260809-00000007', '3FWXG7SDDubLWPVwJteZJ74720rMSlRhvbpffF0QExc', '2026-08-09 08:17:40'),
(5, 9, 'CMP-20260809-00000009', 'vEqU2h1UJ5uWsZ_UiC5Q1LS2BrlkuYh1IwH1gcEveHA', '2026-08-09 11:55:03'),
(6, 8, 'CMP-20260809-00000008', 'daieeDl8j_MDbTiYxsnQHT2Cn1CFVXAnNWB0hmhXlrk', '2026-08-09 12:59:36'),
(7, 10, 'CMP-20260809-00000010', 'FSF5I-PlXRm2tnDh1PvxvVciGodwK6rXmoAwrQtmMr8', '2026-08-09 13:09:31');

-- --------------------------------------------------------

--
-- Estrutura da tabela `configuracoes_analytics`
--

CREATE TABLE `configuracoes_analytics` (
  `idConfiguracao` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `ativo` tinyint(1) NOT NULL DEFAULT '0',
  `measurement_id` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `criadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `configuracoes_analytics`
--

INSERT INTO `configuracoes_analytics` (`idConfiguracao`, `ativo`, `measurement_id`, `criadoEm`, `atualizadoEm`) VALUES
(1, 1, 'G-NY7PZP7TB3', '2026-08-11 20:12:50', '2026-08-11 20:20:48');

-- --------------------------------------------------------

--
-- Estrutura da tabela `configuracoes_asaas`
--

CREATE TABLE `configuracoes_asaas` (
  `idConfiguracao` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `ativo` tinyint(1) NOT NULL DEFAULT '0',
  `ambiente` enum('sandbox','producao') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sandbox',
  `api_key_sandbox` text COLLATE utf8mb4_unicode_ci,
  `api_key_producao` text COLLATE utf8mb4_unicode_ci,
  `webhook_token_sandbox` text COLLATE utf8mb4_unicode_ci,
  `webhook_token_producao` text COLLATE utf8mb4_unicode_ci,
  `pix_disponivel_sandbox` tinyint(1) DEFAULT NULL,
  `pix_verificado_em_sandbox` datetime DEFAULT NULL,
  `pix_chave_sandbox` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pix_disponivel_producao` tinyint(1) DEFAULT NULL,
  `pix_verificado_em_producao` datetime DEFAULT NULL,
  `pix_chave_producao` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `criadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `configuracoes_asaas`
--

INSERT INTO `configuracoes_asaas` (`idConfiguracao`, `ativo`, `ambiente`, `api_key_sandbox`, `api_key_producao`, `webhook_token_sandbox`, `webhook_token_producao`, `pix_disponivel_sandbox`, `pix_verificado_em_sandbox`, `pix_chave_sandbox`, `pix_disponivel_producao`, `pix_verificado_em_producao`, `pix_chave_producao`, `criadoEm`, `atualizadoEm`) VALUES
(1, 1, 'producao', 'bqW2N5ybhcHuVGaL3MzXA/WWwphALru4sNeNGylZ6zPgra+BmzRqKRBdV0loDwqDQZ4+ZVB591MsiqdBzUV08E4+vqP2VTqQulXpgnVNAMV58s595b/j+zq3kHNkbVpdjqzK9aTx/x87e8XouO3TXpSSg9MAQSwMsBND1Ka0kIqK+c2VV07FNgzTwVbHmWS+xRTTq5Gr7BouL6NT7OfF3QqfkprwwteMZ9oG12AnnCGufeflMo3tz6US2lEh5fEnN1g=', 'E1CcYAMwvJqYzS5WqMjV5SH/ssueAhODdihAYuL+4XYmDjcgu8BfFTAgRWVSk7yPKJVVmdhzbjqdhclGWPpRtVdHPVH4N4vhvc80xoeHRGhpm3jYffSV/u8/5ZNf1DEIKMRljktjIuj/An/6cH96jC1CAiFjba5kkzUAePEmhhurGVy6lO89TuE9/SOOfABCmQs8JcVd+AxzuHRa112q1FoCrrbk9cGxHW4Wj5vvVcfpnZ204nqT3+5uh0jK347RnmY=', 'Gh+K4k7DJstQguaCYlZsuE/H8jS10b1sV5zIZBWMdobDaPDlPoEBdERQ+gA4BzpR/iYqtfOedELUKEzm1ME33JZs8nsgjj+zHTdiKmg=', 'MupTZ5t3iTkc/OY6+HfSqDmLddPFRVDHwBaeNLuJiPmJ2Ne4a25x0eFh89gTO+moRSuZmsFa6UFMI1Z3UGibTsBsPDX1e7GG2+7IfXk=', 1, '2026-08-12 09:43:46', '66cc44c8-a2ab-45da-8e8f-fce95184090e', 1, '2026-08-12 19:07:18', '4f9ad5ae-9824-4b09-85ad-ac308e4d8223', '2026-08-08 20:27:39', '2026-08-12 19:07:18');

-- --------------------------------------------------------

--
-- Estrutura da tabela `configuracoes_email`
--

CREATE TABLE `configuracoes_email` (
  `idConfiguracao` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `remetente_nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'IECLB Parobé',
  `remetente_email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'noreply@ieclbparobe.com.br',
  `reply_to` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT 'secretaria@ieclbparobe.com.br',
  `criadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `configuracoes_email`
--

INSERT INTO `configuracoes_email` (`idConfiguracao`, `ativo`, `remetente_nome`, `remetente_email`, `reply_to`, `criadoEm`, `atualizadoEm`) VALUES
(1, 1, 'IECLB Parobé', 'noreply@ieclbparobe.com.br', 'secretaria@ieclbparobe.com.br', '2026-08-08 20:46:35', '2026-08-08 20:46:35');

-- --------------------------------------------------------

--
-- Estrutura da tabela `doadores`
--

CREATE TABLE `doadores` (
  `idDoador` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cpf` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefone` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asaasCustomerId` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `criadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `doadores`
--

INSERT INTO `doadores` (`idDoador`, `nome`, `cpf`, `email`, `telefone`, `asaasCustomerId`, `criadoEm`, `atualizadoEm`) VALUES
(1, 'Andre Gustavo Henssler', '37577505005', 'andrehenssler10@gmail.com', '51995262803', 'cus_000008621148', '2026-08-08 18:05:27', '2026-08-08 18:05:27'),
(2, 'Juraci Ivany Henssler', '82905629002', 'andrehenssler@sou.faccat.br', '51995262803', 'cus_000008621432', '2026-08-08 18:39:23', '2026-08-08 18:39:23'),
(3, 'Andressa Henssler', '14756092055', 'admin@ieclbparobe.com.br', '51995262803', 'cus_000008621561', '2026-08-08 18:57:00', '2026-08-08 18:57:00'),
(4, 'Samuel Gabriel Arthur Bernardes', '77677258042', 'samuel_gabriel_bernardes@sinsesp.com.br', '51995262803', 'cus_000008621649', '2026-08-08 19:09:08', '2026-08-08 19:09:58'),
(5, 'Leandro Alvarino Henssker', '46429948072', 'juracihenssler94@gmail.com', '51998393713', 'cus_000192684406', '2026-08-08 19:35:05', '2026-08-09 11:52:30'),
(6, 'Juraci Ivany Ev', '98341787008', 'andrehenssler@gmail.com', '51995262003', 'cus_000008621919', '2026-08-08 19:41:42', '2026-08-08 19:41:43'),
(7, 'Adriava He Ev', '38064477050', 'andrehenssler3@gmail.com', '51995262803', 'cus_000008621929', '2026-08-08 19:43:25', '2026-08-08 19:44:13'),
(8, 'Isabel Adriana Emilly da Mata', '43851941900', 'ian-martins80@teravida.com.br', '51995262803', 'cus_000008622005', '2026-08-08 19:53:05', '2026-08-08 19:53:06'),
(9, 'Ian Thomas Kaique da Rocha', '21966363125', 'ian-darocha78@murosterrae.com.br', '51995262803', 'cus_000008622018', '2026-08-08 19:55:07', '2026-08-08 19:58:41'),
(10, 'Cristiane Isabelle Isabel Ramos', '66958836880', 'cristiane_isabelle_ramos@destawco.com', '(51) 99526-2803', 'cus_000008622165', '2026-08-08 20:14:34', '2026-08-08 20:18:40'),
(11, 'Benício Bryan da Cruz', '24713001899', 'benicio-dacruz86@henrimar.com.br', '51995262803', 'cus_000008622308', '2026-08-08 20:30:09', '2026-08-08 20:34:02'),
(12, 'Andre Henssler', '03025715073', 'andrehenssle10@gmail.com', '51995262803', 'cus_000008565347', '2026-08-08 20:45:11', '2026-08-08 20:45:11'),
(13, 'Gustavo', '65327520021', 'andrehenssler10@gmail.com', '51995262803', 'cus_000008622460', '2026-08-08 21:05:36', '2026-08-08 21:05:37'),
(14, 'Beatriz Lúcia Eduarda Alves', '78867457284', 'beatriz_lucia_alves@grupoamericaville1.com.br', '51995262803', 'cus_000008625685', '2026-08-09 08:07:53', '2026-08-09 08:07:54'),
(15, 'Heloise Vitória Marcela dos Santos', '97220310234', 'heloise_dossantos@lnaa.com.br', '51995262803', 'cus_000008626167', '2026-08-09 09:33:18', '2026-08-09 09:33:18'),
(16, 'Liz Aurora Cláudia da Rosa', '31002856701', 'oferta@ieclbparobe.com.br', '51995262803', 'cus_000008627459', '2026-08-09 12:51:22', '2026-08-09 12:51:22');

-- --------------------------------------------------------

--
-- Estrutura da tabela `emails_envios`
--

CREATE TABLE `emails_envios` (
  `idEmail` bigint(20) UNSIGNED NOT NULL,
  `idPagamento` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `destinatario` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `assunto` varchar(220) COLLATE utf8mb4_unicode_ci NOT NULL,
  `corpoHtml` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Pendente','Enviado','Erro') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendente',
  `tentativas` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ultimoErro` text COLLATE utf8mb4_unicode_ci,
  `criadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `enviadoEm` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `emails_envios`
--

INSERT INTO `emails_envios` (`idEmail`, `idPagamento`, `tipo`, `destinatario`, `assunto`, `corpoHtml`, `status`, `tentativas`, `ultimoErro`, `criadoEm`, `enviadoEm`) VALUES
(1, 4, 'Aprovado', 'andrehenssle10@gmail.com', 'Pagamento aprovado - copa', '<!doctype html><html lang=\"pt-BR\"><head><meta charset=\"utf-8\"></head><body style=\"margin:0;background:#f3f5f3;font-family:Arial,sans-serif;color:#273029\"><div style=\"max-width:640px;margin:0 auto;padding:28px 16px\"><div style=\"background:#526c58;color:#fff;padding:18px 22px;border-radius:12px 12px 0 0\"><strong style=\"font-size:20px\">Checkout IECLB Parobé</strong></div><div style=\"background:#fff;padding:26px 22px;border:1px solid #dfe4e0;border-top:0;border-radius:0 0 12px 12px\"><h1 style=\"font-size:26px;margin:0 0 18px\">Pagamento aprovado</h1><p>Seu pagamento foi confirmado com sucesso.</p><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Formulário</span><strong style=\"display:block;margin-top:3px\">copa</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Palpite</span><strong style=\"display:block;margin-top:3px\">sjkdh</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Valor pago</span><strong style=\"display:block;margin-top:3px\">R$ 10,00</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Forma de pagamento</span><strong style=\"display:block;margin-top:3px\">PIX</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Comprovante</span><strong style=\"display:block;margin-top:3px\">CMP-20260808-00000004</strong></div><p style=\"margin:24px 0 8px\"><a href=\"https://checkout.ieclbparobe.com.br/comprovante/43WB9RW_17C6X3xKIKxnOmw_V3HqGf3iLN9EGA7ra8s\" style=\"display:inline-block;background:#526c58;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:700\">Abrir comprovante</a></p><p style=\"color:#6b746e;font-size:13px\">O comprovante pode ser impresso ou salvo em PDF pelo navegador.</p><hr style=\"border:0;border-top:1px solid #e8ece9;margin:28px 0\"><p style=\"font-size:12px;color:#7a817d;margin:0\">Mensagem automática do Checkout IECLB Parobé.</p></div></div></body></html>', 'Enviado', 1, NULL, '2026-08-08 20:50:37', '2026-08-08 20:50:37'),
(2, 5, 'Criacao', 'benicio-dacruz86@henrimar.com.br', 'Recebemos seu palpite - copa', '<!doctype html><html lang=\"pt-BR\"><head><meta charset=\"utf-8\"></head><body style=\"margin:0;background:#f3f5f3;font-family:Arial,sans-serif;color:#273029\"><div style=\"max-width:640px;margin:0 auto;padding:28px 16px\"><div style=\"background:#526c58;color:#fff;padding:18px 22px;border-radius:12px 12px 0 0\"><strong style=\"font-size:20px\">Checkout IECLB Parobé</strong></div><div style=\"background:#fff;padding:26px 22px;border:1px solid #dfe4e0;border-top:0;border-radius:0 0 12px 12px\"><h1 style=\"font-size:26px;margin:0 0 18px\">Recebemos sua solicitação</h1><p>Seu palpite foi registrado e o pagamento foi criado.</p><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Formulário</span><strong style=\"display:block;margin-top:3px\">copa</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Palpite</span><strong style=\"display:block;margin-top:3px\">sjkdh</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Valor</span><strong style=\"display:block;margin-top:3px\">R$ 10,00</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Forma de pagamento</span><strong style=\"display:block;margin-top:3px\">PIX</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Status</span><strong style=\"display:block;margin-top:3px\">Pendente</strong></div><p style=\"margin:24px 0 8px\"><a href=\"https://checkout.ieclbparobe.com.br/pagamento/PLP-A26fjczzrpVZEGFTDlX39kmW\" style=\"display:inline-block;background:#526c58;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:700\">Acompanhar pagamento</a></p><p style=\"color:#6b746e;font-size:13px\">PIX, boleto e atualizações do pagamento podem ser consultados por esse link.</p><hr style=\"border:0;border-top:1px solid #e8ece9;margin:28px 0\"><p style=\"font-size:12px;color:#7a817d;margin:0\">Mensagem automática do Checkout IECLB Parobé.</p></div></div></body></html>', 'Enviado', 1, NULL, '2026-08-08 20:53:38', '2026-08-08 20:53:38'),
(3, 5, 'Aprovado', 'benicio-dacruz86@henrimar.com.br', 'Pagamento aprovado - copa', '<!doctype html><html lang=\"pt-BR\"><head><meta charset=\"utf-8\"></head><body style=\"margin:0;background:#f3f5f3;font-family:Arial,sans-serif;color:#273029\"><div style=\"max-width:640px;margin:0 auto;padding:28px 16px\"><div style=\"background:#526c58;color:#fff;padding:18px 22px;border-radius:12px 12px 0 0\"><strong style=\"font-size:20px\">Checkout IECLB Parobé</strong></div><div style=\"background:#fff;padding:26px 22px;border:1px solid #dfe4e0;border-top:0;border-radius:0 0 12px 12px\"><h1 style=\"font-size:26px;margin:0 0 18px\">Pagamento aprovado</h1><p>Seu pagamento foi confirmado com sucesso.</p><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Formulário</span><strong style=\"display:block;margin-top:3px\">copa</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Palpite</span><strong style=\"display:block;margin-top:3px\">sjkdh</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Valor pago</span><strong style=\"display:block;margin-top:3px\">R$ 10,00</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Forma de pagamento</span><strong style=\"display:block;margin-top:3px\">PIX</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Comprovante</span><strong style=\"display:block;margin-top:3px\">CMP-20260808-00000005</strong></div><p style=\"margin:24px 0 8px\"><a href=\"https://checkout.ieclbparobe.com.br/comprovante/amDwLRSYRRZcSWObnWyZ-93oPX9kgjrYxOVbJPJvb-4\" style=\"display:inline-block;background:#526c58;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:700\">Abrir comprovante</a></p><p style=\"color:#6b746e;font-size:13px\">O comprovante pode ser impresso ou salvo em PDF pelo navegador.</p><hr style=\"border:0;border-top:1px solid #e8ece9;margin:28px 0\"><p style=\"font-size:12px;color:#7a817d;margin:0\">Mensagem automática do Checkout IECLB Parobé.</p></div></div></body></html>', 'Enviado', 1, NULL, '2026-08-08 20:54:58', '2026-08-08 20:54:58'),
(4, 6, 'Criacao', 'andrehenssler10@gmail.com', 'Recebemos seu palpite - copa', '<!doctype html><html lang=\"pt-BR\"><head><meta charset=\"utf-8\"></head><body style=\"margin:0;background:#f3f5f3;font-family:Arial,sans-serif;color:#273029\"><div style=\"max-width:640px;margin:0 auto;padding:28px 16px\"><div style=\"background:#526c58;color:#fff;padding:18px 22px;border-radius:12px 12px 0 0\"><strong style=\"font-size:20px\">Checkout IECLB Parobé</strong></div><div style=\"background:#fff;padding:26px 22px;border:1px solid #dfe4e0;border-top:0;border-radius:0 0 12px 12px\"><h1 style=\"font-size:26px;margin:0 0 18px\">Recebemos sua solicitação</h1><p>Seu palpite foi registrado e o pagamento foi criado.</p><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Formulário</span><strong style=\"display:block;margin-top:3px\">copa</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Palpite</span><strong style=\"display:block;margin-top:3px\">A 0 x 0 B</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Valor</span><strong style=\"display:block;margin-top:3px\">R$ 10,00</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Forma de pagamento</span><strong style=\"display:block;margin-top:3px\">PIX</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Status</span><strong style=\"display:block;margin-top:3px\">Pendente</strong></div><p style=\"margin:24px 0 8px\"><a href=\"https://checkout.ieclbparobe.com.br/pagamento/PLP-UqjZGP1qI-a5pBvi0_qdhxfR\" style=\"display:inline-block;background:#526c58;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:700\">Acompanhar pagamento</a></p><p style=\"color:#6b746e;font-size:13px\">PIX, boleto e atualizações do pagamento podem ser consultados por esse link.</p><hr style=\"border:0;border-top:1px solid #e8ece9;margin:28px 0\"><p style=\"font-size:12px;color:#7a817d;margin:0\">Mensagem automática do Checkout IECLB Parobé.</p></div></div></body></html>', 'Enviado', 1, NULL, '2026-08-08 21:05:37', '2026-08-08 21:05:38'),
(5, 6, 'Aprovado', 'andrehenssler10@gmail.com', 'Pagamento aprovado - copa', '<!doctype html><html lang=\"pt-BR\"><head><meta charset=\"utf-8\"></head><body style=\"margin:0;background:#f3f5f3;font-family:Arial,sans-serif;color:#273029\"><div style=\"max-width:640px;margin:0 auto;padding:28px 16px\"><div style=\"background:#526c58;color:#fff;padding:18px 22px;border-radius:12px 12px 0 0\"><strong style=\"font-size:20px\">Checkout IECLB Parobé</strong></div><div style=\"background:#fff;padding:26px 22px;border:1px solid #dfe4e0;border-top:0;border-radius:0 0 12px 12px\"><h1 style=\"font-size:26px;margin:0 0 18px\">Pagamento aprovado</h1><p>Seu pagamento foi confirmado com sucesso.</p><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Formulário</span><strong style=\"display:block;margin-top:3px\">copa</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Palpite</span><strong style=\"display:block;margin-top:3px\">A 0 x 0 B</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Valor pago</span><strong style=\"display:block;margin-top:3px\">R$ 10,00</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Forma de pagamento</span><strong style=\"display:block;margin-top:3px\">PIX</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Comprovante</span><strong style=\"display:block;margin-top:3px\">CMP-20260808-00000006</strong></div><p style=\"margin:24px 0 8px\"><a href=\"https://checkout.ieclbparobe.com.br/comprovante/DJaPb0HUUN6lH9ci4qxHumcgUPsFLoAV_xiSYUF6w5Q\" style=\"display:inline-block;background:#526c58;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:700\">Abrir comprovante</a></p><p style=\"color:#6b746e;font-size:13px\">O comprovante pode ser impresso ou salvo em PDF pelo navegador.</p><hr style=\"border:0;border-top:1px solid #e8ece9;margin:28px 0\"><p style=\"font-size:12px;color:#7a817d;margin:0\">Mensagem automática do Checkout IECLB Parobé.</p></div></div></body></html>', 'Enviado', 1, NULL, '2026-08-08 21:14:51', '2026-08-08 21:14:51'),
(6, 7, 'Criacao', 'beatriz_lucia_alves@grupoamericaville1.com.br', 'Recebemos sua oferta - Oferta Local - Paróquia', '<!doctype html><html lang=\"pt-BR\"><head><meta charset=\"utf-8\"></head><body style=\"margin:0;background:#f3f5f3;font-family:Arial,sans-serif;color:#273029\"><div style=\"max-width:640px;margin:0 auto;padding:28px 16px\"><div style=\"background:#526c58;color:#fff;padding:18px 22px;border-radius:12px 12px 0 0\"><strong style=\"font-size:20px\">Checkout IECLB Parobé</strong></div><div style=\"background:#fff;padding:26px 22px;border:1px solid #dfe4e0;border-top:0;border-radius:0 0 12px 12px\"><h1 style=\"font-size:26px;margin:0 0 18px\">Recebemos sua solicitação</h1><p>Sua oferta foi registrada e o pagamento foi criado.</p><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Oferta</span><strong style=\"display:block;margin-top:3px\">Oferta Local - Paróquia</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Valor</span><strong style=\"display:block;margin-top:3px\">R$ 10,00</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Forma de pagamento</span><strong style=\"display:block;margin-top:3px\">PIX</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Status</span><strong style=\"display:block;margin-top:3px\">Pendente</strong></div><p style=\"margin:24px 0 8px\"><a href=\"https://checkout.ieclbparobe.com.br/pagamento/OFR-4JtUQZj5RLpaK-OsP-hjlHm7\" style=\"display:inline-block;background:#526c58;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:700\">Acompanhar pagamento</a></p><p style=\"color:#6b746e;font-size:13px\">PIX, boleto e atualizações do pagamento podem ser consultados por esse link.</p><hr style=\"border:0;border-top:1px solid #e8ece9;margin:28px 0\"><p style=\"font-size:12px;color:#7a817d;margin:0\">Mensagem automática do Checkout IECLB Parobé.</p></div></div></body></html>', 'Enviado', 1, NULL, '2026-08-09 08:07:54', '2026-08-09 08:07:54'),
(7, 7, 'Aprovado', 'beatriz_lucia_alves@grupoamericaville1.com.br', 'Pagamento aprovado - Oferta Local - Paróquia', '<!doctype html><html lang=\"pt-BR\"><head><meta charset=\"utf-8\"></head><body style=\"margin:0;background:#f3f5f3;font-family:Arial,sans-serif;color:#273029\"><div style=\"max-width:640px;margin:0 auto;padding:28px 16px\"><div style=\"background:#526c58;color:#fff;padding:18px 22px;border-radius:12px 12px 0 0\"><strong style=\"font-size:20px\">Checkout IECLB Parobé</strong></div><div style=\"background:#fff;padding:26px 22px;border:1px solid #dfe4e0;border-top:0;border-radius:0 0 12px 12px\"><h1 style=\"font-size:26px;margin:0 0 18px\">Pagamento aprovado</h1><p>Seu pagamento foi confirmado com sucesso.</p><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Oferta</span><strong style=\"display:block;margin-top:3px\">Oferta Local - Paróquia</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Valor pago</span><strong style=\"display:block;margin-top:3px\">R$ 10,00</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Forma de pagamento</span><strong style=\"display:block;margin-top:3px\">PIX</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Comprovante</span><strong style=\"display:block;margin-top:3px\">CMP-20260809-00000007</strong></div><p style=\"margin:24px 0 8px\"><a href=\"https://checkout.ieclbparobe.com.br/comprovante/3FWXG7SDDubLWPVwJteZJ74720rMSlRhvbpffF0QExc\" style=\"display:inline-block;background:#526c58;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:700\">Abrir comprovante</a></p><p style=\"color:#6b746e;font-size:13px\">O comprovante pode ser impresso ou salvo em PDF pelo navegador.</p><hr style=\"border:0;border-top:1px solid #e8ece9;margin:28px 0\"><p style=\"font-size:12px;color:#7a817d;margin:0\">Mensagem automática do Checkout IECLB Parobé.</p></div></div></body></html>', 'Enviado', 1, NULL, '2026-08-09 08:17:40', '2026-08-09 08:17:40'),
(8, 8, 'Criacao', 'heloise_dossantos@lnaa.com.br', 'Recebemos sua oferta - Oferta Local - Paróquia', '<!doctype html><html lang=\"pt-BR\"><head><meta charset=\"utf-8\"></head><body style=\"margin:0;background:#f3f5f3;font-family:Arial,sans-serif;color:#273029\"><div style=\"max-width:640px;margin:0 auto;padding:28px 16px\"><div style=\"background:#526c58;color:#fff;padding:18px 22px;border-radius:12px 12px 0 0\"><strong style=\"font-size:20px\">Checkout IECLB Parobé</strong></div><div style=\"background:#fff;padding:26px 22px;border:1px solid #dfe4e0;border-top:0;border-radius:0 0 12px 12px\"><h1 style=\"font-size:26px;margin:0 0 18px\">Recebemos sua solicitação</h1><p>Sua oferta foi registrada e o pagamento foi criado.</p><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Oferta</span><strong style=\"display:block;margin-top:3px\">Oferta Local - Paróquia</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Valor</span><strong style=\"display:block;margin-top:3px\">R$ 10,00</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Forma de pagamento</span><strong style=\"display:block;margin-top:3px\">Boleto</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Status</span><strong style=\"display:block;margin-top:3px\">Pendente</strong></div><p style=\"margin:24px 0 8px\"><a href=\"https://checkout.ieclbparobe.com.br/pagamento/OFR-2Fo7zf0FQFU32USI5TGHCqLa\" style=\"display:inline-block;background:#526c58;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:700\">Acompanhar pagamento</a></p><p style=\"color:#6b746e;font-size:13px\">PIX, boleto e atualizações do pagamento podem ser consultados por esse link.</p><hr style=\"border:0;border-top:1px solid #e8ece9;margin:28px 0\"><p style=\"font-size:12px;color:#7a817d;margin:0\">Mensagem automática do Checkout IECLB Parobé.</p></div></div></body></html>', 'Enviado', 1, NULL, '2026-08-09 09:33:19', '2026-08-09 09:33:19'),
(9, 9, 'Criacao', 'juracihenssler94@gmail.com', 'Recebemos sua oferta - Ofertas Locais Agosto 2026 - Paróquia', '<!doctype html><html lang=\"pt-BR\"><head><meta charset=\"utf-8\"></head><body style=\"margin:0;background:#f3f5f3;font-family:Arial,sans-serif;color:#273029\"><div style=\"max-width:640px;margin:0 auto;padding:28px 16px\"><div style=\"background:#526c58;color:#fff;padding:18px 22px;border-radius:12px 12px 0 0\"><strong style=\"font-size:20px\">Checkout IECLB Parobé</strong></div><div style=\"background:#fff;padding:26px 22px;border:1px solid #dfe4e0;border-top:0;border-radius:0 0 12px 12px\"><h1 style=\"font-size:26px;margin:0 0 18px\">Recebemos sua solicitação</h1><p>Sua oferta foi registrada e o pagamento foi criado.</p><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Oferta</span><strong style=\"display:block;margin-top:3px\">Ofertas Locais Agosto 2026 - Paróquia</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Valor</span><strong style=\"display:block;margin-top:3px\">R$ 10,00</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Forma de pagamento</span><strong style=\"display:block;margin-top:3px\">PIX</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Status</span><strong style=\"display:block;margin-top:3px\">Pendente</strong></div><p style=\"margin:24px 0 8px\"><a href=\"https://checkout.ieclbparobe.com.br/pagamento/OFR-oKjvp6SRdCw2diq4Q31jMfnd\" style=\"display:inline-block;background:#526c58;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:700\">Acompanhar pagamento</a></p><p style=\"color:#6b746e;font-size:13px\">PIX, boleto e atualizações do pagamento podem ser consultados por esse link.</p><hr style=\"border:0;border-top:1px solid #e8ece9;margin:28px 0\"><p style=\"font-size:12px;color:#7a817d;margin:0\">Mensagem automática do Checkout IECLB Parobé.</p></div></div></body></html>', 'Enviado', 1, NULL, '2026-08-09 11:52:31', '2026-08-09 11:52:31'),
(10, 9, 'Aprovado', 'juracihenssler94@gmail.com', 'Pagamento aprovado - Ofertas Locais Agosto 2026 - Paróquia', '<!doctype html><html lang=\"pt-BR\"><head><meta charset=\"utf-8\"></head><body style=\"margin:0;background:#f3f5f3;font-family:Arial,sans-serif;color:#273029\"><div style=\"max-width:640px;margin:0 auto;padding:28px 16px\"><div style=\"background:#526c58;color:#fff;padding:18px 22px;border-radius:12px 12px 0 0\"><strong style=\"font-size:20px\">Checkout IECLB Parobé</strong></div><div style=\"background:#fff;padding:26px 22px;border:1px solid #dfe4e0;border-top:0;border-radius:0 0 12px 12px\"><h1 style=\"font-size:26px;margin:0 0 18px\">Pagamento aprovado</h1><p>Seu pagamento foi confirmado com sucesso.</p><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Oferta</span><strong style=\"display:block;margin-top:3px\">Ofertas Locais Agosto 2026 - Paróquia</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Valor pago</span><strong style=\"display:block;margin-top:3px\">R$ 10,00</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Forma de pagamento</span><strong style=\"display:block;margin-top:3px\">PIX</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Comprovante</span><strong style=\"display:block;margin-top:3px\">CMP-20260809-00000009</strong></div><p style=\"margin:24px 0 8px\"><a href=\"https://checkout.ieclbparobe.com.br/comprovante/vEqU2h1UJ5uWsZ_UiC5Q1LS2BrlkuYh1IwH1gcEveHA\" style=\"display:inline-block;background:#526c58;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:700\">Abrir comprovante</a></p><p style=\"color:#6b746e;font-size:13px\">O comprovante pode ser impresso ou salvo em PDF pelo navegador.</p><hr style=\"border:0;border-top:1px solid #e8ece9;margin:28px 0\"><p style=\"font-size:12px;color:#7a817d;margin:0\">Mensagem automática do Checkout IECLB Parobé.</p></div></div></body></html>', 'Enviado', 1, NULL, '2026-08-09 11:55:03', '2026-08-09 11:55:03'),
(11, 10, 'Criacao', 'oferta@ieclbparobe.com.br', 'Recebemos seu palpite - Copa do Mundo Brasil x Japão', '<!doctype html><html lang=\"pt-BR\"><head><meta charset=\"utf-8\"></head><body style=\"margin:0;background:#f3f5f3;font-family:Arial,sans-serif;color:#273029\"><div style=\"max-width:640px;margin:0 auto;padding:28px 16px\"><div style=\"background:#526c58;color:#fff;padding:18px 22px;border-radius:12px 12px 0 0\"><strong style=\"font-size:20px\">Checkout IECLB Parobé</strong></div><div style=\"background:#fff;padding:26px 22px;border:1px solid #dfe4e0;border-top:0;border-radius:0 0 12px 12px\"><h1 style=\"font-size:26px;margin:0 0 18px\">Recebemos sua solicitação</h1><p>Seu palpite foi registrado e o pagamento foi criado.</p><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Formulário</span><strong style=\"display:block;margin-top:3px\">Copa do Mundo Brasil x Japão</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Palpite</span><strong style=\"display:block;margin-top:3px\">Brasil 1 x 0 Japão</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Valor</span><strong style=\"display:block;margin-top:3px\">R$ 12,00</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Forma de pagamento</span><strong style=\"display:block;margin-top:3px\">PIX</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Status</span><strong style=\"display:block;margin-top:3px\">Pendente</strong></div><p style=\"margin:24px 0 8px\"><a href=\"https://checkout.ieclbparobe.com.br/pagamento/PLP-JbBPvpgh-JqVHquOuuk0Du8K\" style=\"display:inline-block;background:#526c58;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:700\">Acompanhar pagamento</a></p><p style=\"color:#6b746e;font-size:13px\">PIX, boleto e atualizações do pagamento podem ser consultados por esse link.</p><hr style=\"border:0;border-top:1px solid #e8ece9;margin:28px 0\"><p style=\"font-size:12px;color:#7a817d;margin:0\">Mensagem automática do Checkout IECLB Parobé.</p></div></div></body></html>', 'Enviado', 1, NULL, '2026-08-09 12:51:23', '2026-08-09 12:51:23'),
(12, 8, 'Aprovado', 'heloise_dossantos@lnaa.com.br', 'Pagamento aprovado - Ofertas Locais Agosto 2026 - Paróquia', '<!doctype html><html lang=\"pt-BR\"><head><meta charset=\"utf-8\"></head><body style=\"margin:0;background:#f3f5f3;font-family:Arial,sans-serif;color:#273029\"><div style=\"max-width:640px;margin:0 auto;padding:28px 16px\"><div style=\"background:#526c58;color:#fff;padding:18px 22px;border-radius:12px 12px 0 0\"><strong style=\"font-size:20px\">Checkout IECLB Parobé</strong></div><div style=\"background:#fff;padding:26px 22px;border:1px solid #dfe4e0;border-top:0;border-radius:0 0 12px 12px\"><h1 style=\"font-size:26px;margin:0 0 18px\">Pagamento aprovado</h1><p>Seu pagamento foi confirmado com sucesso.</p><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Oferta</span><strong style=\"display:block;margin-top:3px\">Ofertas Locais Agosto 2026 - Paróquia</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Valor pago</span><strong style=\"display:block;margin-top:3px\">R$ 10,00</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Forma de pagamento</span><strong style=\"display:block;margin-top:3px\">Boleto</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Comprovante</span><strong style=\"display:block;margin-top:3px\">CMP-20260809-00000008</strong></div><p style=\"margin:24px 0 8px\"><a href=\"https://checkout.ieclbparobe.com.br/comprovante/daieeDl8j_MDbTiYxsnQHT2Cn1CFVXAnNWB0hmhXlrk\" style=\"display:inline-block;background:#526c58;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:700\">Abrir comprovante</a></p><p style=\"color:#6b746e;font-size:13px\">O comprovante pode ser impresso ou salvo em PDF pelo navegador.</p><hr style=\"border:0;border-top:1px solid #e8ece9;margin:28px 0\"><p style=\"font-size:12px;color:#7a817d;margin:0\">Mensagem automática do Checkout IECLB Parobé.</p></div></div></body></html>', 'Enviado', 1, NULL, '2026-08-09 12:59:36', '2026-08-09 12:59:36'),
(13, 10, 'Aprovado', 'oferta@ieclbparobe.com.br', 'Pagamento aprovado - Copa do Mundo Brasil x Japão', '<!doctype html><html lang=\"pt-BR\"><head><meta charset=\"utf-8\"></head><body style=\"margin:0;background:#f3f5f3;font-family:Arial,sans-serif;color:#273029\"><div style=\"max-width:640px;margin:0 auto;padding:28px 16px\"><div style=\"background:#526c58;color:#fff;padding:18px 22px;border-radius:12px 12px 0 0\"><strong style=\"font-size:20px\">Checkout IECLB Parobé</strong></div><div style=\"background:#fff;padding:26px 22px;border:1px solid #dfe4e0;border-top:0;border-radius:0 0 12px 12px\"><h1 style=\"font-size:26px;margin:0 0 18px\">Pagamento aprovado</h1><p>Seu pagamento foi confirmado com sucesso.</p><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Formulário</span><strong style=\"display:block;margin-top:3px\">Copa do Mundo Brasil x Japão</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Palpite</span><strong style=\"display:block;margin-top:3px\">Brasil 1 x 0 Japão</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Valor pago</span><strong style=\"display:block;margin-top:3px\">R$ 12,00</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Forma de pagamento</span><strong style=\"display:block;margin-top:3px\">PIX</strong></div><div style=\"margin:10px 0\"><span style=\"display:block;font-size:12px;color:#6d756f\">Comprovante</span><strong style=\"display:block;margin-top:3px\">CMP-20260809-00000010</strong></div><p style=\"margin:24px 0 8px\"><a href=\"https://checkout.ieclbparobe.com.br/comprovante/FSF5I-PlXRm2tnDh1PvxvVciGodwK6rXmoAwrQtmMr8\" style=\"display:inline-block;background:#526c58;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:700\">Abrir comprovante</a></p><p style=\"color:#6b746e;font-size:13px\">O comprovante pode ser impresso ou salvo em PDF pelo navegador.</p><hr style=\"border:0;border-top:1px solid #e8ece9;margin:28px 0\"><p style=\"font-size:12px;color:#7a817d;margin:0\">Mensagem automática do Checkout IECLB Parobé.</p></div></div></body></html>', 'Enviado', 1, NULL, '2026-08-09 13:09:31', '2026-08-09 13:09:31');

-- --------------------------------------------------------

--
-- Estrutura da tabela `evento`
--

CREATE TABLE `evento` (
  `idEvento` int(11) NOT NULL,
  `nomeEvento` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `dataInicio` date NOT NULL,
  `horaInicio` time DEFAULT NULL,
  `dataFinal` date NOT NULL,
  `horaFinal` time DEFAULT NULL,
  `descricao` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imagem` varchar(120) COLLATE utf8_unicode_ci DEFAULT NULL,
  `codImpot` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `importancia`
--

CREATE TABLE `importancia` (
  `idImportancia` int(11) NOT NULL,
  `tipo` char(50) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `importancia`
--

INSERT INTO `importancia` (`idImportancia`, `tipo`) VALUES
(1, 'Baixa'),
(2, 'Média'),
(3, 'Alta'),
(4, 'Baixa'),
(5, 'Média'),
(6, 'Alta'),
(7, 'Baixa'),
(8, 'Média'),
(9, 'Alta'),
(10, 'Baixa'),
(11, 'Média'),
(12, 'Alta'),
(13, 'Baixa'),
(14, 'Média'),
(15, 'Alta'),
(16, 'Baixa'),
(17, 'Média'),
(18, 'Alta'),
(19, 'Baixa'),
(20, 'Média'),
(21, 'Alta');

-- --------------------------------------------------------

--
-- Estrutura da tabela `links_curtos`
--

CREATE TABLE `links_curtos` (
  `idLink` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `tipo` enum('Oferta','Palpite') COLLATE utf8mb4_unicode_ci NOT NULL,
  `idReferencia` bigint(20) UNSIGNED NOT NULL,
  `criadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `links_curtos`
--

INSERT INTO `links_curtos` (`idLink`, `codigo`, `tipo`, `idReferencia`, `criadoEm`, `atualizadoEm`) VALUES
(1, 'Oc20grnhia', 'Oferta', 2, '2026-08-11 20:02:05', '2026-08-11 20:09:19'),
(2, 'Og61eaygiu', 'Oferta', 6, '2026-08-11 20:02:05', '2026-08-11 20:09:19'),
(3, 'Oe40xjaz0k', 'Oferta', 4, '2026-08-11 20:02:05', '2026-08-11 20:09:19'),
(4, 'Oh70n686af', 'Oferta', 7, '2026-08-11 20:02:05', '2026-08-11 20:09:19'),
(5, 'Ob117wdrqp', 'Oferta', 1, '2026-08-11 20:02:05', '2026-08-11 20:09:19'),
(6, 'Of506ekos5', 'Oferta', 5, '2026-08-11 20:02:05', '2026-08-11 20:09:19'),
(7, 'Oi81v2ly14', 'Oferta', 8, '2026-08-11 20:02:05', '2026-08-11 20:09:19'),
(8, 'Od31oo198z', 'Oferta', 3, '2026-08-11 20:02:05', '2026-08-11 20:09:19'),
(16, 'Pb117wdrqp', 'Palpite', 1, '2026-08-11 20:02:05', '2026-08-11 20:09:19'),
(17, 'Pc20grnhia', 'Palpite', 2, '2026-08-11 20:02:05', '2026-08-11 20:09:19');

-- --------------------------------------------------------

--
-- Estrutura da tabela `local`
--

CREATE TABLE `local` (
  `idLocal` int(11) NOT NULL,
  `codEvento` int(11) NOT NULL,
  `endereceo` varchar(120) COLLATE utf8_unicode_ci DEFAULT NULL,
  `numero` char(11) COLLATE utf8_unicode_ci DEFAULT NULL,
  `complemento` char(11) COLLATE utf8_unicode_ci DEFAULT NULL,
  `codCidade` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `ofertas`
--

CREATE TABLE `ofertas` (
  `idOferta` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria` enum('Local','Sinodal','Nacional','Especial') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Local',
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `imagem` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_inicio` datetime DEFAULT NULL,
  `data_fim` datetime DEFAULT NULL,
  `valor_minimo` decimal(10,2) NOT NULL DEFAULT '10.00',
  `permitir_valor_livre` tinyint(1) NOT NULL DEFAULT '1',
  `pix_ativo` tinyint(1) NOT NULL DEFAULT '1',
  `cartao_ativo` tinyint(1) NOT NULL DEFAULT '1',
  `boleto_ativo` tinyint(1) NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `criadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `ofertas`
--

INSERT INTO `ofertas` (`idOferta`, `titulo`, `slug`, `categoria`, `descricao`, `imagem`, `data_inicio`, `data_fim`, `valor_minimo`, `permitir_valor_livre`, `pix_ativo`, `cartao_ativo`, `boleto_ativo`, `ativo`, `criadoEm`, `atualizadoEm`) VALUES
(1, 'Missão no Sínodo Mato Grosso', 'missao-no-sinodo-mato-grosso', 'Nacional', 'A missão no Sínodo Mato Grosso precisa da sua contribuição! Somos 22 paróquias que abrangem uma área de 2.400 km de extensão de Norte a Sul e 1.600 km de Leste a Oeste. Somos uma igreja em dispersão, e por isso, precisamos das suas orações, assim como da sua generosidade e solidariedade. Sua oferta será investida em projetos missionários, como os da Comunidade de Santarém, Paróquia Transamazônica, Matupá, Porto dos Gaúchos e Vila Rica. Além disso, esses recursos permitirão o auxílio emergencial às comunidades em dificuldade e viabilizarão encontros de formação para líderes e ministros/as. Que Deus abençoe as dádivas e todas as pessoas doadoras!', 'uploads/ofertas/20260809133852-0af6318985.webp', '2026-08-01 00:00:00', '2027-08-01 00:00:00', 10.00, 1, 1, 1, 1, 1, '2026-08-08 18:03:45', '2026-08-11 20:46:29'),
(2, 'Oferta Local 16 de Agosto 2026 - Paróquia', 'oferta-local-16-de-agosto-2026-paroquia', 'Local', 'O desino desta oferta é para trabalho com jovens, cursos, programa de rádio há hora evangelica', 'uploads/ofertas/20260809133915-c3792567c6.jpg', '2026-08-08 19:00:00', '2027-08-18 23:59:00', 10.00, 1, 1, 1, 1, 1, '2026-08-08 18:15:32', '2026-08-10 06:44:52'),
(3, 'Trabalho de Inclusão e Acessibilidade – Pessoas com Deficiência', 'trabalho-de-inclusao-e-acessibilidade-pessoas-com-deficiencia', 'Especial', 'Inclusão, amor que acolhe! A oferta deste culto é destinada para o trabalho de inclusão e acessibilidade na IECLB. Inclusão acontece quando há respeito, acolhimento e acessibilidade. Para sermos comunidades cada vez mais inclusivas é necessário formação, materiais de estudo e disponibilizar espaços acessíveis. Nossa gratidão pela sua oferta que permite, por exemplo, editar vídeos com libras e legendas, disponibilizar materiais em braile e realizar o curso de Libras!', 'uploads/ofertas/20260809134104-7800a2da97.png', '2026-08-08 13:40:00', '2027-08-20 23:59:00', 10.00, 1, 1, 1, 1, 1, '2026-08-09 13:41:04', '2026-08-10 06:37:24'),
(4, 'Investimento na Missão - Ponto de Pregação São Francisco de Paula', 'investimento-na-missao-ponto-de-pregacao-sao-francisco-de-paula', 'Sinodal', 'Missão de Deus, nossa paixão!O ponto de pregação de São Francisco de Paula precisa de você! Neste ano de 2026 começamos um novo trabalho, uma nova missão, inclusive com um ministro atuando e morando na comunidade. Desta maneira, estamos desenvolvendo vários trabalhos. Por exemplo: trabalho com música na escola, trabalho com crianças, trabalho com mulheres, trabalho diaconal com famílias em situação de vulnerabilidade, cultos todos os finais de semana, evangelizações, entre outros. Mas, para que este trabalho possa continuar gerando frutos, precisamos da sua ajuda, da sua oração e da sua contribuição. Qualquer auxílio é muito bem-vindo e fará toda a diferença. Nosso sonho é continuar crescendo para abençoar a cidade de São Francisco de Paula através da pregação do Evangelho e da vivência do Reino de Deus. Venha junto com a gente realizar este sonho!', 'uploads/ofertas/20260810063402-1738616487.png', '2026-08-10 08:00:00', '2027-08-08 23:59:00', 10.00, 1, 1, 1, 1, 1, '2026-08-10 06:34:02', '2026-08-10 06:34:02'),
(5, 'Missão com Literatura Evangelística', 'missao-com-literatura-evangelistica', 'Nacional', 'Você já leu um folheto evangelístico? Certa vez, eu estava cansado/a, sentad/oa na sala de espera do hospital, quando li um folheto deixado ali. As palavras falaram direto ao meu coração. Senti-me acolhido/a por Deus. Um simples folheto mudou o meu dia ? e minha vida. Com sua oferta, você pode ser, hoje, instrumento dessa esperança. Doe para a missão com folhetos evangelísticos! Deus te abençoe', 'uploads/ofertas/20260810063604-aed0686e77.png', '2026-09-01 00:00:00', '2026-09-12 23:59:00', 10.00, 1, 1, 1, 1, 1, '2026-08-10 06:36:04', '2026-08-11 20:55:56'),
(6, 'Ofertas Local Setembro 2026 - Paróquia', 'ofertas-local-setembro-2026-paroquia', 'Local', 'O desino desta oferta é para trabalho com jovens, cursos, programa de rádio há hora evangelica', 'uploads/ofertas/20260810064113-da0eef26f9.png', '2026-09-01 00:00:00', '2027-09-12 23:59:00', 10.00, 1, 1, 1, 1, 1, '2026-08-10 06:41:13', '2026-08-11 20:56:33'),
(7, 'Construção e Reformas - Comunidade Alto Feliz', 'construcao-e-reformas-comunidade-alto-feliz', 'Sinodal', 'Queridos irmãos e irmãs em Cristo. É com o coração cheio de gratidão e esperança que nos dirigimos a vocês hoje. O templo da Comunidade Evangélica de Confissão Luterana em Alto Feliz, pertencente à Paróquia Martim Lutero em São Vendelino, tem 102 anos de história que continua viva através das famílias que ali se reúnem e vivem sua fé de confissão Luterana, a exemplo das pessoas que as antecederam. Precisamos restaurar sua beleza para que continue a ser um lugar sagrado para nós e para as futuras gerações. Cada oferta, por menor que pareça, é um tijolo nessa obra de amor e gratidão ao Senhor. Unidos em fé e propósito, vamos honrar o passado e edificar o futuro. Sua generosidade é o alicerce para que nosso templo continue a ressoar com o louvor a Deus por muitos e muitos anos. Contamos com seu valioso apoio e desde já agradecemos.', 'uploads/ofertas/20260810064337-89e505c1f8.png', '2026-09-01 00:00:00', '2027-09-12 23:59:00', 10.00, 1, 1, 1, 1, 1, '2026-08-10 06:43:37', '2026-08-11 20:57:03'),
(8, 'Trabalho com Mulheres e Coordenação de Gênero', 'trabalho-com-mulheres-e-coordenacao-de-genero', 'Nacional', 'O que o Senhor requer de ti? Que pratiques a justiça, ames a misericórdia e andes humildemente com teu Deus.? (Mq 6.8) Somos chamadas e chamados a viver uma fé que transforma. As ofertas de hoje apoiam ações que fortalecem o compromisso da IECLB com a dignidade, o respeito e a inclusão. Com elas, tornamos nossas comunidades mais justas e acolhedoras para todas as pessoas. Ofertar é partilhar o amor que transforma e faz florescer a justiça no chão da fé.', 'uploads/ofertas/20260810064856-06a388e419.png', '2026-09-01 00:00:00', '2027-09-12 23:59:00', 10.00, 1, 1, 1, 0, 1, '2026-08-10 06:46:23', '2026-08-11 20:56:15');

-- --------------------------------------------------------

--
-- Estrutura da tabela `ofertas_valores`
--

CREATE TABLE `ofertas_valores` (
  `idValor` int(10) UNSIGNED NOT NULL,
  `idOferta` int(10) UNSIGNED NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `ordem` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `criadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `ofertas_valores`
--

INSERT INTO `ofertas_valores` (`idValor`, `idOferta`, `valor`, `ordem`, `ativo`, `criadoEm`) VALUES
(48, 4, 10.00, 0, 1, '2026-08-10 06:34:02'),
(49, 4, 20.00, 1, 1, '2026-08-10 06:34:02'),
(50, 4, 30.00, 2, 1, '2026-08-10 06:34:02'),
(51, 4, 50.00, 3, 1, '2026-08-10 06:34:02'),
(52, 4, 100.00, 4, 1, '2026-08-10 06:34:02'),
(58, 3, 10.00, 0, 1, '2026-08-10 06:37:24'),
(59, 3, 20.00, 1, 1, '2026-08-10 06:37:24'),
(60, 3, 30.00, 2, 1, '2026-08-10 06:37:24'),
(61, 3, 50.00, 3, 1, '2026-08-10 06:37:24'),
(62, 3, 100.00, 4, 1, '2026-08-10 06:37:24'),
(98, 2, 10.50, 0, 1, '2026-08-10 06:46:51'),
(99, 2, 20.00, 1, 1, '2026-08-10 06:46:51'),
(100, 2, 25.00, 2, 1, '2026-08-10 06:46:51'),
(101, 2, 50.00, 3, 1, '2026-08-10 06:46:51'),
(102, 2, 100.00, 4, 1, '2026-08-10 06:46:51'),
(123, 1, 10.00, 0, 1, '2026-08-11 20:46:29'),
(124, 1, 20.00, 1, 1, '2026-08-11 20:46:29'),
(125, 1, 30.00, 2, 1, '2026-08-11 20:46:29'),
(126, 1, 50.00, 3, 1, '2026-08-11 20:46:29'),
(127, 1, 100.00, 4, 1, '2026-08-11 20:46:29'),
(128, 5, 10.00, 0, 1, '2026-08-11 20:55:56'),
(129, 5, 20.00, 1, 1, '2026-08-11 20:55:56'),
(130, 5, 30.00, 2, 1, '2026-08-11 20:55:56'),
(131, 5, 50.00, 3, 1, '2026-08-11 20:55:56'),
(132, 5, 100.00, 4, 1, '2026-08-11 20:55:56'),
(133, 8, 10.00, 0, 1, '2026-08-11 20:56:15'),
(134, 8, 20.00, 1, 1, '2026-08-11 20:56:15'),
(135, 8, 25.00, 2, 1, '2026-08-11 20:56:15'),
(136, 8, 50.00, 3, 1, '2026-08-11 20:56:15'),
(137, 8, 100.00, 4, 1, '2026-08-11 20:56:15'),
(138, 6, 10.00, 0, 1, '2026-08-11 20:56:33'),
(139, 6, 20.00, 1, 1, '2026-08-11 20:56:33'),
(140, 6, 30.00, 2, 1, '2026-08-11 20:56:33'),
(141, 6, 50.00, 3, 1, '2026-08-11 20:56:33'),
(142, 6, 100.00, 4, 1, '2026-08-11 20:56:33'),
(143, 7, 10.00, 0, 1, '2026-08-11 20:57:03'),
(144, 7, 20.00, 1, 1, '2026-08-11 20:57:03'),
(145, 7, 30.00, 2, 1, '2026-08-11 20:57:03'),
(146, 7, 50.00, 3, 1, '2026-08-11 20:57:03'),
(147, 7, 100.00, 4, 1, '2026-08-11 20:57:03');

-- --------------------------------------------------------

--
-- Estrutura da tabela `pagamentos`
--

CREATE TABLE `pagamentos` (
  `idPagamento` bigint(20) UNSIGNED NOT NULL,
  `idOferta` int(10) UNSIGNED DEFAULT NULL,
  `idPalpite` bigint(20) UNSIGNED DEFAULT NULL,
  `idDoador` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `valorLiquido` decimal(10,2) DEFAULT NULL,
  `taxa` decimal(10,2) DEFAULT NULL,
  `formaPagamento` enum('PIX','Cartao','Boleto') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendente',
  `asaasPaymentId` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asaasStatus` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoiceUrl` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pixQrCode` longtext COLLATE utf8mb4_unicode_ci,
  `pixCopiaCola` text COLLATE utf8mb4_unicode_ci,
  `pixExpiracao` datetime DEFAULT NULL,
  `bankSlipUrl` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `boletoLinhaDigitavel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dataVencimento` date DEFAULT NULL,
  `dataPagamento` datetime DEFAULT NULL,
  `erro` text COLLATE utf8mb4_unicode_ci,
  `criadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `pagamentos`
--

INSERT INTO `pagamentos` (`idPagamento`, `idOferta`, `idPalpite`, `idDoador`, `codigo`, `valor`, `valorLiquido`, `taxa`, `formaPagamento`, `status`, `asaasPaymentId`, `asaasStatus`, `invoiceUrl`, `pixQrCode`, `pixCopiaCola`, `pixExpiracao`, `bankSlipUrl`, `boletoLinhaDigitavel`, `dataVencimento`, `dataPagamento`, `erro`, `criadoEm`, `atualizadoEm`) VALUES
(4, NULL, 3, 12, 'PLP-Ly7AyekerOO1h5P4okjJmQqT', 10.00, NULL, NULL, 'PIX', 'Pago', 'pay_adk3l5fb1tjmtikh', 'RECEIVED', 'https://sandbox.asaas.com/i/adk3l5fb1tjmtikh', NULL, NULL, NULL, 'https://sandbox.asaas.com/b/pdf/adk3l5fb1tjmtikh', NULL, '2026-08-08', '2026-08-08 00:00:00', NULL, '2026-08-08 20:45:11', '2026-08-09 13:20:09'),
(5, NULL, 4, 11, 'PLP-A26fjczzrpVZEGFTDlX39kmW', 10.00, NULL, NULL, 'PIX', 'Pago', 'pay_3h08j28ezvwv7s4h', 'RECEIVED', 'https://sandbox.asaas.com/i/3h08j28ezvwv7s4h', 'iVBORw0KGgoAAAANSUhEUgAAAcIAAAHCAQAAAABUY/ToAAADGklEQVR4Xu2UwY7bMBBDffP//1E/y7c0fKTk7QKFi710AlDeyCMOH3PQZI/XD9ev47vyr6vk0yr5tEo+rZJPq+TT+m/kdbDO13VSn+/3u9bjno60t7PkZFIVVjtkhxHMORSO+EsOJqNi1Dtzwdtd2VZd8jNIwHWWku0UtaNLfgrpflrArBx3Zsnx5MuqHIaUYr/MbJzjLDma3Df++MRZ8q/PAHKvS+Keh8wEIl2/skqOJS/PQaYh/pxfGgA2/8B5So4m314d5CCADig4qNWElBxN2m9gtSH1d7dNMxAlJ5N2a8PuKgZrZt5x9xeUHEv6LK91vCIEi3dQINSSk0mUO4LW/qAsAxGcSo4l6aYtTP1Mghr2y5Q0nCXHkjmopUlQZTChzvRXeFRKTiZxBtyFUHLEot1zUXI6KVUchedig/hBdFCETiXHkrpbNDq2B+egejOcSo4mff/iaC0uzN10MMeSc8m0xbH7gGsR0hiElV9yMGnAGf44bXWsai7Iol9yLHndN6++HJ4JQ3sOIhktOZbElSu+aOVj/FyolZLzSZ30UMtoQkqivgTYV3IwKc1QDGhu6O3wpSCUnE1+uXtCXij4SCRTO82Sg8krBu4cONqdZvvKLDmbDGcn5yDgCXILV8nxpCRDvG+PSKuY/DUlZ5MyHhJCJ+Y7mAZoybHk5ihtAEnQaiSi5HQS78U/Y3tVb8hZeziASw4mI+XyYe40+6z4z0LJsSR2e+SHtG3HEiIH36BGybmkwZuPFneighguOZj09V/cs1ncjthywldSybmkbcu+CEOnzKfjVUhSv+RYEm907UlaiU7IjOzokoNJzYD2QxQ2Cl6UcuUbUpccSwbElaC9FkMGQtCSY8m1FhFcaYd+7Gz+EEG/5FgyxClVDhk4/LHhAAMtOZdUpX3LhOnAS3HOkgJccjT5dvjKA4tgyaTrFxDF01DyA0gMq8WmWeBNDe1uyQ8gcbk2lYLftj6YHVNyMGnp0g97dRajROWB4nCr5Fzy/h/MbeM+NBgajVShcJccTf5olXxaJZ9WyadV8mmVfFofRv4GREoYIwMWLU8AAAAASUVORK5CYII=', '00020101021226820014br.gov.bcb.pix2560pix-h.asaas.com/qr/cobv/cb703a24-2998-4a15-b440-d5287a999c545204000053039865802BR5922Andre Gustavo Henssler6006Parobe61089563000062070503***63047761', '2027-08-08 23:59:59', NULL, NULL, '2026-08-08', '2026-08-08 00:00:00', NULL, '2026-08-08 20:53:37', '2026-08-08 20:54:58'),
(6, NULL, 5, 13, 'PLP-UqjZGP1qI-a5pBvi0_qdhxfR', 10.00, NULL, NULL, 'PIX', 'Pago', 'pay_gl4u3ulsun34st9e', 'RECEIVED', 'https://sandbox.asaas.com/i/gl4u3ulsun34st9e', 'iVBORw0KGgoAAAANSUhEUgAAAcIAAAHCAQAAAABUY/ToAAADFUlEQVR4Xu2UQW7cUAxDvfP9b9RjeZeGj5QnKVC4yKYagHLqL1F8moW+e3z8MH4dfyr/GiWfouRTlHyKkk9R8in+G3kdxPmZncrPy6lOeio/X+66LrmYVBarzDJw6PHAiOMsuZuMOmZv25kFea/JS74Hqa1LDRwQ7DW65NuQysajM5FS9pLvQX5EFaimE/zIJl7OkqvJe+OPT5wl//osIO94XYNs/dI8O+caTJRcS8ptxlv+snUNufzyB85TcjWpGzB2AfSSMfPkSsiYSSU3k07pDyqfqvv0lFTyl1xLBlKWU0Jqz4LhQsRXci95sVz+5LfDN8ITBOcieFbJ3SSmSDRt4DxMp+FfKbmbHAcDSDKK5Y/mSRlUcjF5wGJmhkdAiVFC2FlyNwnzUkkY44F5qWaWR5ZcSyqDEPhqD4hgW4aqKrmX9JrjGzvzKDxwJFUlV5PKLtFnhtgRJrXIA15lycXk4V2fuM3adhMeKwKh5GrydJluDPLjy0RaSPRLriXlOxSSBo5iu2eOCbTkbvIEwvpFMR3USsk3IEV9tgC9cHJXlu4BHl5yLYmVm+CcuzB756R3Kwgld5PECT1GFGW6I0q//ELJveRH1uuv2yhaUJw2YMJSci1pQrXFVD4iMSS/waSSe0maL8wV37ZezPCTXP2Sa0l3WfrtUDvyC0wDtORy8tYwoFGjIWdEyf2kr8H8dyyDb4URlffliFxyM+n1jseZXgEIFHdKriaHZtE6x+YkN4Up8yMlF5P2xD3o7T4QBjFccjNp+Uj489bhcS7pyJhJJdeSKuWT5tsAYeiky4R03S+5lsTrBXuIVu7CJ1oux4wuuZj8RmEj4SCVK7+QvORacsB4zCSGubUzaMm15ITZ4/VZ87KSf36VXE1es2OvHk6X4vvLt+DKhJKLSWVfANHMyIGVlhSfJTeTnw5v+eYT8qgBYMW+km9AToMWL90FTvJxqCi5n5SoIl6t36k/do9Ks+Ri0pK508ZE5snjQf4ruZk8CF+B4LIhAuSDBsm3XnIt+aMo+RQln6LkU5R8ipJP8Wbkb7UpZ9OZMmf6AAAAAElFTkSuQmCC', '00020101021226820014br.gov.bcb.pix2560pix-h.asaas.com/qr/cobv/1cc13205-2a3a-40be-98a5-f8785495a1e15204000053039865802BR5922Andre Gustavo Henssler6006Parobe61089563000062070503***63048B19', '2027-08-08 23:59:59', NULL, NULL, '2026-08-08', '2026-08-08 00:00:00', NULL, '2026-08-08 21:05:37', '2026-08-08 21:14:51'),
(7, 2, NULL, 14, 'OFR-4JtUQZj5RLpaK-OsP-hjlHm7', 10.00, 8.01, 1.99, 'PIX', 'Pago', 'pay_7w0nl0esxkodu8wn', 'RECEIVED', 'https://sandbox.asaas.com/i/7w0nl0esxkodu8wn', 'iVBORw0KGgoAAAANSUhEUgAAAcIAAAHCAQAAAABUY/ToAAADEklEQVR4Xu2TQW7rQAxDvfP9b9RjeZeWj9LYCVD4o5svA1SSsYbiYxYz3l5/rK/tU/nXCnlXIe8q5F2FvKuQd/XfyGOj9tehLwvDQ5In7vflDDmZxCgdzENZrWGA0rj9IQeTpZYRuMzSNUWtPuQzSBnl6c6gpTM65FNIde3hNrhqK3vIZ5Avq4vdtNovM4vviJ0hR5PrxG8/5Qz562cAuYrRrstQp87Wzr4GXSHHkofvgR1w69S9saJMf0KOJuX+cKA6ju17bMjZpH3CdfQcv5UWHQDdwSHHkkfZPQa6LLobZsrifwo5lpRhHTQ/OXTiMpdQc2eFnEwylHGB7g6iNtP+AyLYhRxLXlBRvdNdEGv/JRs05FiS7nzorZaLPYwHNQs5nzxk9eFjZn9urZ33IuRosranUaNzjx+PNorQLuRwskQf/CUGYo29CzmalMFQuX6ey8Veng1ca8jRJAZzmvnhtgmiFGYh5GRS3MuU1DKwyieMOek9CTmYhJGlfJisYBf2ER1yLKnOP2Z9BaRUXoEoIeeTh+bmZPSCXbCvxQqQGnIwCXs9d4f53HlK0MYKQsi5pK+AC9mIg3ZinOk5aMixpIeStu5Odi+BOT/fjZBjSXT2BYkhg+OHI8R5eELOJZd8HamBd4Y/1YecTWoOW74as7mCQpmGnEwarp8/xpZclpqFnE3qDT4u7zHvdIOVhoU8B4ccTKrXlJFWiyTYZ8VfCyHHktY5/OLbWSsBmnvMIORcUoKxspVit/aSjXR2yMkkFHZ5O6hSLfumrKSQc8me7xWCQoJlKd1I0jzkXJIJZ+8r4JaogjTgjqzokINJ+TEAw6vXg5+s/Q/VhxxLFuj5SVDNLEWRIUeTXcx2ErgQTiPXjRWjIceSEGJA9DX5vthRnpCTSXVeD419/D53PSQ4SwrmkKNJgHXKde4q0uo2lCJvyEeQDCqCjBdXQ0/6dmgT8gFkmWu4Gt7nnlZQyMGkpZ+n8TcjujwO8jfkZHKjuA+eEIBsoF5okHrXQ44l/1Qh7yrkXYW8q5B3FfKuHkZ+Aysjp5OnlRbpAAAAAElFTkSuQmCC', '00020101021226820014br.gov.bcb.pix2560pix-h.asaas.com/qr/cobv/bd8eb33f-d3f0-492c-a89a-d6af7a6cdf595204000053039865802BR5922Andre Gustavo Henssler6006Parobe61089563000062070503***63048289', '2027-08-09 23:59:59', NULL, NULL, '2026-08-09', '2026-08-09 00:00:00', NULL, '2026-08-09 08:07:54', '2026-08-09 08:17:40'),
(8, 2, NULL, 15, 'OFR-2Fo7zf0FQFU32USI5TGHCqLa', 10.00, 8.01, 1.99, 'Boleto', 'Pago', 'pay_c9wnjxljnr6305cl', 'RECEIVED', 'https://sandbox.asaas.com/i/c9wnjxljnr6305cl', NULL, NULL, NULL, 'https://sandbox.asaas.com/b/pdf/c9wnjxljnr6305cl', '46191110000000000000012901803010515340000001000', '2026-08-10', '2026-08-09 00:00:00', NULL, '2026-08-09 09:33:18', '2026-08-09 12:59:35'),
(9, 2, NULL, 5, 'OFR-oKjvp6SRdCw2diq4Q31jMfnd', 10.00, 8.11, 1.89, 'PIX', 'Pago', 'pay_jad2btsdwk9cmtsy', 'RECEIVED', 'https://www.asaas.com/i/jad2btsdwk9cmtsy', 'iVBORw0KGgoAAAANSUhEUgAAAcIAAAHCAQAAAABUY/ToAAADHElEQVR4Xu2TQa7bMBBDtcv9b9RjefdrPlJKWqBw0U3nA5Rje4bDxywkr69/XD/W78rfrpJPq+TTKvm0Sj6tkk/rv5HXYr1cve7nfb+u9OvlOdpxlhxMqnrrGSFYc6uMDErOJq3KqP0OliNxRKrDlxxP3jO9U75TsuhLfh9SlxBJSjif+cko+T3IqOKw8uaFhHffeZacS8rO/PGKs+QfrwHkXpowPvu9P/PFxHpWybEkfrv8+WbDcz721B/+Dig5l0TEspzg6S9xIp1LX3IyCQfB3rsgxxN1of0/JYeTNmGM/cQJ2bBNWiXnkmw6Y1FitOcuUJNwvCUnkxbOQZAb2CMfiHemQkuOJi9hGD6LpQWOVQP/CWLJ0aTvfMPpP1qlRUhAycHkAuOpg6Dhh+t08qyIJceSyMKo/LaDDC/SiXFVciy5R7GfnQ+s3iB10kqOJVHP0AmQNiZTmkvcJceSYuTzjhsiBVQ/O2xQUXIySaM933PHWEkoISaJKjmXpFvsfipn+Dxo/hY4ISWHk2tvsmo14DSx7pwrYSXnkudTXjoKfqKiG38rnpacS6r0xCH4ZMkRESGXFaeUnEtCrSz7d+8M94RuT8nRpGAVQLJQcQYCcnMymJQcTEqSWd69/9uMFPfmSw4nGd2Cd56cJZc4dRmTm5SSo8mvg4axCb8SeDvZWsmxJIICXuC5liQQYujUUJYcTMp0eeOPkRSNOBimiM6g5FzSOgRm5UhQDHdEwUkvOZj09vog2JGnDoEjMye65HCS0rcMeTG3a7+VtQcl55KZMWDCjt+CI5CV6KmPQ8nBJO0hXB5PInfHtORkUqX23HYuaeExC5WHsuRw0s1t470DArD3H8dA/1NyMGnQW56hIW+/S9RIJUeTe0HkdT9U0tucfGWqLTmWvLLDeF9ypNKFLRH0npYcTHqoj9hofgYiHKXkfHL3ervFbJUfVw6MEkrOJ8PrpwBsjHcksRZKzie/WLwvB8E6wnLCSs4mTcQilyk4YuWRjbrkcDIuW/ygIEZNAOHWSg4m/2mVfFoln1bJp1XyaZV8Wt+M/Al7T1nF3bAu8gAAAABJRU5ErkJggg==', '00020101021226800014br.gov.bcb.pix2558pix.asaas.com/qr/cobv/7adab78e-f6c2-4810-8491-00f816d595dc5204000053039865802BR5922Andre Gustavo Henssler6006Parobe61089563000062070503***63049370', '2027-08-09 23:59:59', NULL, NULL, '2026-08-09', '2026-08-09 00:00:00', NULL, '2026-08-09 11:52:30', '2026-08-09 11:55:03'),
(10, NULL, 6, 16, 'PLP-JbBPvpgh-JqVHquOuuk0Du8K', 12.00, 10.01, 1.99, 'PIX', 'Pago', 'pay_zrr90il5m4pbwg06', 'RECEIVED', 'https://sandbox.asaas.com/i/zrr90il5m4pbwg06', 'iVBORw0KGgoAAAANSUhEUgAAAcIAAAHCAQAAAABUY/ToAAADDklEQVR4Xu2US27kQAxDvfP9bzTH6l1P+CiV3QMMKsgmMkA5rg/Fx15UOcf7h/Xn+Ff5boXcVchdhdxVyF2F3NWvka+DOrU4ec8vkZV72motqfYhB5NaSWd2FD49YEWVgzHkYLJUw2yNanaXvFqHfAb5iRwFgl3RIR9D2qCtl1W1lT3kM8h3qfrzwtb6mBnYlzPkaHKd+PYpZ8j/PgPIj/IN6Fd5dvY16Ao5lnz5HizX7dT5zj34A+cJOZoUq517CqBXUWwd+a6QkLPJQuynKaNUacxOqZ2MIceSMq+OWsaVoxRzbXN+yNmkHCLUuAVVAs0KJivkZLI35euoep3lcgS7kINJzllaZ/SGw/dcTMeHnEzeHWobdKidFPkhp5NLK1wlFElsRXV+yNEksmGIFWXGiUKcb0PIsWSJJ2ftg29zbXw9nP/5ZYccSNZp0y/+crG3x7eDbcjBJBYkw8u2CKIUtn4m5FjSSntYS2QNLwOgJSwhB5Pqc/BqmbFUdmHtkiHkZNI9jth/pfkeHAoyiBLyGaQeWcvILSiFhBXAr4ScS9LnJvCUoc6d+eZRV0LIuaT6LsS7JJ9ifBXsAA05lsRJg3exUoXi1NCvKuRgEulmckhPTibzMoUcS7IlQM5ecxE08Gn7qbX6IceSZnTUasFcxjvI7dA+5GjSN+AWYN4DC6+NhxxOIhw+9v6DN9RtYpYcci4pWd80ZbumC7gUcrQLOZc88WC6eT1pdDQd/QKNkINJNSWrUXfich8IjVRsyLlks8uLuxO8pXRR+IGQk8nl8cDshTGsvSgw5FwSrw7ft4EkSzXD+46s6JCDSfk7wDbOnYmlXGWodcixpEEBqGaqmlmabkPI0WTXa33aCiJNG5R6iQANOZaUvc4YlRnLfbCDUJlCziW1YsTGRr6e0KqlIeR08svhI198lTyvug2lyBvyISSuk6Ou+2Ef5UR3Qz6B9Mn3SJIZ3RFHlBJyMmlJrJeelnricZD/Qk4m+39wiZWBbOCj6/sSciz5owq5q5C7CrmrkLsKuauHkX8BJ7v3Qw9w7PoAAAAASUVORK5CYII=', '00020101021226820014br.gov.bcb.pix2560pix-h.asaas.com/qr/cobv/9ba596f9-03d3-40f3-97ce-c136bce03d225204000053039865802BR5922Andre Gustavo Henssler6006Parobe61089563000062070503***6304ED0F', '2027-08-09 23:59:59', NULL, NULL, '2026-08-09', '2026-08-09 00:00:00', NULL, '2026-08-09 12:51:22', '2026-08-09 13:09:31');

-- --------------------------------------------------------

--
-- Estrutura da tabela `palpites`
--

CREATE TABLE `palpites` (
  `idPalpite` bigint(20) UNSIGNED NOT NULL,
  `idEventoPalpite` int(10) UNSIGNED NOT NULL,
  `idDoador` bigint(20) UNSIGNED NOT NULL,
  `idOpcao` int(10) UNSIGNED DEFAULT NULL,
  `palpite` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `statusPagamento` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendente',
  `criadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `palpites_eventos`
--

CREATE TABLE `palpites_eventos` (
  `idEventoPalpite` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `imagem` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `equipe_casa` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `equipe_visitante` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_jogo` datetime DEFAULT NULL,
  `status_jogo` enum('Agendado','EmAndamento','Finalizado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Agendado',
  `placar_casa` tinyint(3) UNSIGNED DEFAULT NULL,
  `placar_visitante` tinyint(3) UNSIGNED DEFAULT NULL,
  `finalizadoEm` datetime DEFAULT NULL,
  `data_inicio` datetime DEFAULT NULL,
  `data_fim` datetime DEFAULT NULL,
  `valor_minimo` decimal(10,2) NOT NULL DEFAULT '10.00',
  `permitir_valor_livre` tinyint(1) NOT NULL DEFAULT '0',
  `permitir_outro_palpite` tinyint(1) NOT NULL DEFAULT '1',
  `pix_ativo` tinyint(1) NOT NULL DEFAULT '1',
  `cartao_ativo` tinyint(1) NOT NULL DEFAULT '1',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `criadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `palpites_eventos`
--

INSERT INTO `palpites_eventos` (`idEventoPalpite`, `titulo`, `slug`, `descricao`, `imagem`, `equipe_casa`, `equipe_visitante`, `data_jogo`, `status_jogo`, `placar_casa`, `placar_visitante`, `finalizadoEm`, `data_inicio`, `data_fim`, `valor_minimo`, `permitir_valor_livre`, `permitir_outro_palpite`, `pix_ativo`, `cartao_ativo`, `ativo`, `criadoEm`, `atualizadoEm`) VALUES
(1, 'copa', 'copa', 'jksahd', NULL, 'A', 'B', '2026-08-11 05:00:00', 'Finalizado', 0, 0, '2026-08-08 21:28:21', '2026-08-08 00:00:00', '2026-08-11 05:00:00', 10.00, 1, 1, 1, 1, 1, '2026-08-08 20:29:32', '2026-08-09 12:32:35'),
(2, 'Copa do Mundo Brasil x Japão', 'copa-do-mundo-brasil-x-japao', 'Dê o seu palpite e concorra! ⚽🏆\r\n\r\nEscolha o placar de Argentina x Espanha e participe da brincadeira da Copa do Mundo da JEP.\r\n\r\n💰 Valor da aposta: minima é de R$ 5,00\r\n\r\n🎯 Acertou o placar exato? Caso tenha mais que um ganhador, os 50% serão dividos entre os ganhadores.\r\n\r\n📅 Faça seu palpite antes do início da partida e boa sorte!\r\n\r\nQuanto mais participantes, maior será o prêmio! 🙌⚽🏆\r\n\r\n\r\n\r\nUma copia será encaminhada para o seu email', 'uploads/palpites/20260809124702-770f64aa67.jpg', 'Brasil', 'Japão', '2026-08-13 16:00:00', 'Agendado', NULL, NULL, NULL, '2026-08-08 00:59:00', '2026-08-12 09:00:00', 10.00, 1, 1, 1, 1, 1, '2026-08-09 12:47:02', '2026-08-12 09:45:32');

-- --------------------------------------------------------

--
-- Estrutura da tabela `palpites_opcoes`
--

CREATE TABLE `palpites_opcoes` (
  `idOpcao` int(10) UNSIGNED NOT NULL,
  `idEventoPalpite` int(10) UNSIGNED NOT NULL,
  `rotulo` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ordem` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `criadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `palpites_opcoes`
--

INSERT INTO `palpites_opcoes` (`idOpcao`, `idEventoPalpite`, `rotulo`, `ordem`, `ativo`, `criadoEm`) VALUES
(2, 1, 'A 1 x 2 B', 0, 1, '2026-08-08 21:05:07'),
(3, 1, 'A 0 x 0 B', 1, 1, '2026-08-08 21:05:07'),
(17, 2, '🇧🇷 Brasil 2 x 1 Japão 🇯🇵', 0, 1, '2026-08-12 09:44:16'),
(18, 2, '🇧🇷 Brasil 2 x 0 Japão 🇯🇵', 1, 1, '2026-08-12 09:44:16'),
(19, 2, '🇧🇷 Brasil 1 x 0 Japão 🇯🇵', 2, 1, '2026-08-12 09:44:16'),
(20, 2, '🇧🇷 Brasil 3 x 1 Japão 🇯🇵', 3, 1, '2026-08-12 09:44:16'),
(21, 2, '🇧🇷 Brasil 3 x 0 Japão 🇯🇵', 4, 1, '2026-08-12 09:44:16'),
(22, 2, '🇧🇷 Brasil 4 x 1 Japão 🇯🇵', 5, 1, '2026-08-12 09:44:16'),
(23, 2, '🇧🇷 Brasil 4 x 2 Japão 🇯🇵', 6, 1, '2026-08-12 09:44:16'),
(24, 2, '🇧🇷 Brasil 1 x 2 Japão 🇯🇵', 7, 1, '2026-08-12 09:44:16'),
(25, 2, '🇧🇷 Brasil 0 x 1 Japão 🇯🇵', 8, 1, '2026-08-12 09:44:16'),
(26, 2, '🇧🇷 Brasil 2 x 3 Japão 🇯🇵', 9, 1, '2026-08-12 09:44:16');

-- --------------------------------------------------------

--
-- Estrutura da tabela `palpites_valores`
--

CREATE TABLE `palpites_valores` (
  `idValorPalpite` int(10) UNSIGNED NOT NULL,
  `idEventoPalpite` int(10) UNSIGNED NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `ordem` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `criadoEm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `palpites_valores`
--

INSERT INTO `palpites_valores` (`idValorPalpite`, `idEventoPalpite`, `valor`, `ordem`, `ativo`, `criadoEm`) VALUES
(2, 1, 10.00, 0, 1, '2026-08-08 21:05:07'),
(9, 2, 10.00, 0, 1, '2026-08-12 09:44:16'),
(10, 2, 12.00, 1, 1, '2026-08-12 09:44:16'),
(11, 2, 15.00, 2, 1, '2026-08-12 09:44:16');

-- --------------------------------------------------------

--
-- Estrutura da tabela `titulo`
--

CREATE TABLE `titulo` (
  `idTitulo` int(11) NOT NULL,
  `nome` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `descricao` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `keyword` text COLLATE utf8_unicode_ci,
  `favicon` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imagem` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `titulo`
--

INSERT INTO `titulo` (`idTitulo`, `nome`, `descricao`, `keyword`, `favicon`, `imagem`) VALUES
(1, 'Teste2', NULL, NULL, NULL, NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `acesso`
--
ALTER TABLE `acesso`
  ADD PRIMARY KEY (`idAcess`);

--
-- Índices para tabela `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`idAdministrador`),
  ADD UNIQUE KEY `uq_admin_email` (`email`);

--
-- Índices para tabela `asaas_webhook_eventos`
--
ALTER TABLE `asaas_webhook_eventos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_webhook_evento` (`eventoId`),
  ADD KEY `idx_webhook_payment` (`asaasPaymentId`);

--
-- Índices para tabela `checkout_limites`
--
ALTER TABLE `checkout_limites`
  ADD PRIMARY KEY (`chave`,`tipo`),
  ADD KEY `idx_limite_janela` (`janelaInicio`);

--
-- Índices para tabela `cidade`
--
ALTER TABLE `cidade`
  ADD PRIMARY KEY (`idCidade`);

--
-- Índices para tabela `citbiblicas`
--
ALTER TABLE `citbiblicas`
  ADD PRIMARY KEY (`codCitBiblicas`);

--
-- Índices para tabela `comprovantes`
--
ALTER TABLE `comprovantes`
  ADD PRIMARY KEY (`idComprovante`),
  ADD UNIQUE KEY `uq_comprovante_pagamento` (`idPagamento`),
  ADD UNIQUE KEY `uq_comprovante_numero` (`numero`),
  ADD UNIQUE KEY `uq_comprovante_token` (`token`);

--
-- Índices para tabela `configuracoes_analytics`
--
ALTER TABLE `configuracoes_analytics`
  ADD PRIMARY KEY (`idConfiguracao`);

--
-- Índices para tabela `configuracoes_asaas`
--
ALTER TABLE `configuracoes_asaas`
  ADD PRIMARY KEY (`idConfiguracao`);

--
-- Índices para tabela `configuracoes_email`
--
ALTER TABLE `configuracoes_email`
  ADD PRIMARY KEY (`idConfiguracao`);

--
-- Índices para tabela `doadores`
--
ALTER TABLE `doadores`
  ADD PRIMARY KEY (`idDoador`),
  ADD UNIQUE KEY `uq_doador_cpf` (`cpf`),
  ADD KEY `idx_doador_asaas` (`asaasCustomerId`);

--
-- Índices para tabela `emails_envios`
--
ALTER TABLE `emails_envios`
  ADD PRIMARY KEY (`idEmail`),
  ADD UNIQUE KEY `uq_email_pagamento_tipo` (`idPagamento`,`tipo`),
  ADD KEY `idx_email_status` (`status`,`criadoEm`);

--
-- Índices para tabela `evento`
--
ALTER TABLE `evento`
  ADD PRIMARY KEY (`idEvento`);

--
-- Índices para tabela `importancia`
--
ALTER TABLE `importancia`
  ADD PRIMARY KEY (`idImportancia`);

--
-- Índices para tabela `links_curtos`
--
ALTER TABLE `links_curtos`
  ADD PRIMARY KEY (`idLink`),
  ADD UNIQUE KEY `uq_link_curto_codigo` (`codigo`),
  ADD UNIQUE KEY `uq_link_curto_origem` (`tipo`,`idReferencia`),
  ADD KEY `idx_link_curto_tipo_ref` (`tipo`,`idReferencia`);

--
-- Índices para tabela `local`
--
ALTER TABLE `local`
  ADD PRIMARY KEY (`idLocal`),
  ADD KEY `fk_cidade` (`codCidade`),
  ADD KEY `fk_evento` (`codEvento`);

--
-- Índices para tabela `ofertas`
--
ALTER TABLE `ofertas`
  ADD PRIMARY KEY (`idOferta`),
  ADD UNIQUE KEY `uq_oferta_slug` (`slug`),
  ADD KEY `idx_oferta_ativa` (`ativo`,`data_inicio`,`data_fim`),
  ADD KEY `idx_oferta_categoria` (`categoria`);

--
-- Índices para tabela `ofertas_valores`
--
ALTER TABLE `ofertas_valores`
  ADD PRIMARY KEY (`idValor`),
  ADD UNIQUE KEY `uq_oferta_valor` (`idOferta`,`valor`);

--
-- Índices para tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD PRIMARY KEY (`idPagamento`),
  ADD UNIQUE KEY `uq_pag_codigo` (`codigo`),
  ADD UNIQUE KEY `uq_pag_asaas` (`asaasPaymentId`),
  ADD KEY `idx_pag_oferta` (`idOferta`),
  ADD KEY `idx_pag_palpite` (`idPalpite`),
  ADD KEY `idx_pag_doador` (`idDoador`),
  ADD KEY `idx_pag_status` (`status`),
  ADD KEY `idx_pag_data` (`criadoEm`),
  ADD KEY `idx_pag_status_data_pagamento` (`status`,`dataPagamento`);

--
-- Índices para tabela `palpites`
--
ALTER TABLE `palpites`
  ADD PRIMARY KEY (`idPalpite`),
  ADD KEY `idx_palpite_evento` (`idEventoPalpite`),
  ADD KEY `idx_palpite_doador` (`idDoador`),
  ADD KEY `idx_palpite_status` (`statusPagamento`),
  ADD KEY `fk_palpite_opcao` (`idOpcao`);

--
-- Índices para tabela `palpites_eventos`
--
ALTER TABLE `palpites_eventos`
  ADD PRIMARY KEY (`idEventoPalpite`),
  ADD UNIQUE KEY `uq_palpite_evento_slug` (`slug`),
  ADD KEY `idx_palpite_evento_ativo` (`ativo`,`data_inicio`,`data_fim`),
  ADD KEY `idx_palpite_evento_jogo` (`data_jogo`),
  ADD KEY `idx_palpite_status_jogo` (`status_jogo`,`data_jogo`);

--
-- Índices para tabela `palpites_opcoes`
--
ALTER TABLE `palpites_opcoes`
  ADD PRIMARY KEY (`idOpcao`),
  ADD KEY `idx_palpite_opcoes_evento` (`idEventoPalpite`,`ativo`,`ordem`);

--
-- Índices para tabela `palpites_valores`
--
ALTER TABLE `palpites_valores`
  ADD PRIMARY KEY (`idValorPalpite`),
  ADD UNIQUE KEY `uq_palpite_evento_valor` (`idEventoPalpite`,`valor`);

--
-- Índices para tabela `titulo`
--
ALTER TABLE `titulo`
  ADD PRIMARY KEY (`idTitulo`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `acesso`
--
ALTER TABLE `acesso`
  MODIFY `idAcess` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `administradores`
--
ALTER TABLE `administradores`
  MODIFY `idAdministrador` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `asaas_webhook_eventos`
--
ALTER TABLE `asaas_webhook_eventos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de tabela `cidade`
--
ALTER TABLE `cidade`
  MODIFY `idCidade` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `citbiblicas`
--
ALTER TABLE `citbiblicas`
  MODIFY `codCitBiblicas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de tabela `comprovantes`
--
ALTER TABLE `comprovantes`
  MODIFY `idComprovante` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `doadores`
--
ALTER TABLE `doadores`
  MODIFY `idDoador` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `emails_envios`
--
ALTER TABLE `emails_envios`
  MODIFY `idEmail` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `evento`
--
ALTER TABLE `evento`
  MODIFY `idEvento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `importancia`
--
ALTER TABLE `importancia`
  MODIFY `idImportancia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de tabela `links_curtos`
--
ALTER TABLE `links_curtos`
  MODIFY `idLink` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `local`
--
ALTER TABLE `local`
  MODIFY `idLocal` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ofertas`
--
ALTER TABLE `ofertas`
  MODIFY `idOferta` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `ofertas_valores`
--
ALTER TABLE `ofertas_valores`
  MODIFY `idValor` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  MODIFY `idPagamento` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `palpites`
--
ALTER TABLE `palpites`
  MODIFY `idPalpite` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `palpites_eventos`
--
ALTER TABLE `palpites_eventos`
  MODIFY `idEventoPalpite` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `palpites_opcoes`
--
ALTER TABLE `palpites_opcoes`
  MODIFY `idOpcao` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de tabela `palpites_valores`
--
ALTER TABLE `palpites_valores`
  MODIFY `idValorPalpite` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `titulo`
--
ALTER TABLE `titulo`
  MODIFY `idTitulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `comprovantes`
--
ALTER TABLE `comprovantes`
  ADD CONSTRAINT `fk_comprovante_pagamento` FOREIGN KEY (`idPagamento`) REFERENCES `pagamentos` (`idPagamento`) ON UPDATE CASCADE;

--
-- Limitadores para a tabela `emails_envios`
--
ALTER TABLE `emails_envios`
  ADD CONSTRAINT `fk_email_pagamento` FOREIGN KEY (`idPagamento`) REFERENCES `pagamentos` (`idPagamento`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limitadores para a tabela `evento`
--
ALTER TABLE `evento`
  ADD CONSTRAINT `fk_importancia_e` FOREIGN KEY (`idEvento`) REFERENCES `importancia` (`idImportancia`);

--
-- Limitadores para a tabela `local`
--
ALTER TABLE `local`
  ADD CONSTRAINT `fk_cidade` FOREIGN KEY (`codCidade`) REFERENCES `cidade` (`idCidade`),
  ADD CONSTRAINT `fk_evento` FOREIGN KEY (`codEvento`) REFERENCES `evento` (`idEvento`);

--
-- Limitadores para a tabela `ofertas_valores`
--
ALTER TABLE `ofertas_valores`
  ADD CONSTRAINT `fk_valor_oferta` FOREIGN KEY (`idOferta`) REFERENCES `ofertas` (`idOferta`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD CONSTRAINT `fk_pag_doador` FOREIGN KEY (`idDoador`) REFERENCES `doadores` (`idDoador`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pag_oferta` FOREIGN KEY (`idOferta`) REFERENCES `ofertas` (`idOferta`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pag_palpite` FOREIGN KEY (`idPalpite`) REFERENCES `palpites` (`idPalpite`) ON UPDATE CASCADE;

--
-- Limitadores para a tabela `palpites`
--
ALTER TABLE `palpites`
  ADD CONSTRAINT `fk_palpite_doador` FOREIGN KEY (`idDoador`) REFERENCES `doadores` (`idDoador`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_palpite_evento` FOREIGN KEY (`idEventoPalpite`) REFERENCES `palpites_eventos` (`idEventoPalpite`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_palpite_opcao` FOREIGN KEY (`idOpcao`) REFERENCES `palpites_opcoes` (`idOpcao`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limitadores para a tabela `palpites_opcoes`
--
ALTER TABLE `palpites_opcoes`
  ADD CONSTRAINT `fk_palpite_opcao_evento` FOREIGN KEY (`idEventoPalpite`) REFERENCES `palpites_eventos` (`idEventoPalpite`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `palpites_valores`
--
ALTER TABLE `palpites_valores`
  ADD CONSTRAINT `fk_palpite_valor_evento` FOREIGN KEY (`idEventoPalpite`) REFERENCES `palpites_eventos` (`idEventoPalpite`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
