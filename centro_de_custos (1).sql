-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 06/02/2025 às 15:53
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `centro_de_custos`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `cidades`
--

CREATE TABLE `cidades` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cidades`
--

INSERT INTO `cidades` (`id`, `nome`, `ativo`) VALUES
(1, 'Cidade A', 1),
(2, 'Cidade B', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `empresas`
--

CREATE TABLE `empresas` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cnpj_empresa` varchar(14) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `empresas`
--

INSERT INTO `empresas` (`id`, `nome`, `cnpj_empresa`, `ativo`) VALUES
(1, 'DTEL TELECOM LTDA', '09376370000100', 1),
(7, 'SOARES & SILVA TELECOMUNICACOES LTDA', '44401494000191', 1),
(8, 'H & A SERVICOS DE TELECOMUNICACOES LTDA', '34851868000117', 1),
(9, 'SOLON ARAUJO TELECOMUNICACOES LTDA', '24009579000464', 1),
(10, 'ORBIX TELECOM LTDA', '12627701000134', 1),
(11, 'VOICENET TELECOMUNICACOES LTDA', '42023055000167', 1),
(12, 'DX2 TECNOLOGIA E TELECOMUNICACAO LTDA', '46390328000162', 1),
(13, 'DMAIS SERVICOS DE TELECOMUNICACOES LTDA', '34746004000135', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `entrada_produto`
--

CREATE TABLE `entrada_produto` (
  `id_entrada` int(11) NOT NULL,
  `id_empresa` int(11) DEFAULT NULL,
  `id_produto` int(11) DEFAULT NULL,
  `id_fornecedor` int(11) DEFAULT NULL,
  `quantidade` int(11) NOT NULL,
  `valor_unitario` decimal(10,2) DEFAULT NULL,
  `frete` decimal(10,2) DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `data_entrada` date DEFAULT NULL,
  `nf` varchar(50) DEFAULT NULL,
  `observacao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `entrada_produto`
--

INSERT INTO `entrada_produto` (`id_entrada`, `id_empresa`, `id_produto`, `id_fornecedor`, `quantidade`, `valor_unitario`, `frete`, `valor_total`, `data_entrada`, `nf`, `observacao`) VALUES
(10, 1, 61, 9, 1, 419.00, 0.00, 419.00, '2024-02-26', 'S/N', ''),
(11, 8, 100, 10, 1, 198.86, 0.00, 198.00, '2024-11-05', 'S/N', ''),
(12, 1, 68, 12, 11, 7.89, 0.00, 86.00, '2024-10-02', NULL, ''),
(13, 1, 52, 5, 5, 8.70, 0.00, 43.00, '2022-10-31', NULL, ''),
(14, 1, 66, 10, 10, 33.67, 0.00, 336.00, '2024-09-12', '050.976', ''),
(15, 1, 53, 6, 3, 50.00, 0.00, 150.00, '2024-06-25', NULL, ''),
(16, 1, 59, 5, 1, 69.00, 0.00, 69.00, '2022-07-14', NULL, ''),
(17, 1, 76, 13, 2, 60.79, 0.00, 121.00, '2024-11-06', NULL, ''),
(18, 1, 55, 7, 2, 964.44, 0.00, 1928.00, '2024-08-22', '010.618.90', ''),
(19, 1, 55, 7, 2, 964.44, 0.00, 1928.00, '2024-08-22', '1062266', ''),
(20, 1, 54, 4, 4, 1598.23, 0.00, 6392.00, '2024-07-10', '000.103.526', ''),
(21, 1, 58, 8, 25, 50.00, 0.00, 1250.00, '2024-07-10', '000.007.030', ''),
(22, 1, 60, 9, 82, 8.60, 0.00, 705.00, '2022-12-26', NULL, ''),
(23, 1, 62, 10, 4, 1090.00, 0.00, 4360.00, '2024-04-17', NULL, ''),
(24, 1, 63, 10, 2, 5749.40, 0.00, 11498.00, '2024-08-06', NULL, ''),
(25, 1, 64, 5, 5, 47.71, 0.00, 238.00, '2024-08-14', '000.109.618', ''),
(26, 1, 65, 11, 15, 111.90, 0.00, 1678.00, '2024-08-20', '22.407', ''),
(27, 1, 67, 5, 1, 106.13, 0.00, 106.00, '2024-10-10', NULL, ''),
(28, 1, 72, 5, 1, 338.91, 0.00, 338.00, '2024-11-12', NULL, ''),
(29, 1, 74, 5, 1, 3885.85, 0.00, 3885.00, '2024-11-21', '000.112.794', ''),
(30, 1, 85, 14, 17, 26.91, 0.00, 457.00, '2024-11-29', '000.160.583', ''),
(31, 1, 101, 5, 10, 102.82, 0.00, 1028.00, '2025-01-17', '000.172.487', ''),
(32, 1, 103, 16, 6, 2015.00, 0.00, 12090.00, '2025-01-31', '000.000.041', '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `estados`
--

CREATE TABLE `estados` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `sigla` varchar(2) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `estados`
--

INSERT INTO `estados` (`id`, `nome`, `sigla`, `ativo`) VALUES
(1, 'Pernambuco', 'PE', 1),
(2, 'Alagoas', 'AL', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque`
--

CREATE TABLE `estoque` (
  `id_estoque` int(11) NOT NULL,
  `id_empresa` int(11) DEFAULT NULL,
  `id_produto` int(11) DEFAULT NULL,
  `quantidade` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `estoque`
--

INSERT INTO `estoque` (`id_estoque`, `id_empresa`, `id_produto`, `quantidade`) VALUES
(2, 1, 61, 1),
(3, 8, 100, 1),
(4, 1, 68, 11),
(5, 1, 52, 5),
(6, 1, 66, 10),
(7, 1, 53, 3),
(8, 1, 59, 1),
(9, 1, 76, 2),
(10, 1, 55, 4),
(11, 1, 54, 4),
(12, 1, 58, 25),
(13, 1, 60, 82),
(14, 1, 62, 4),
(15, 1, 63, 2),
(16, 1, 64, 5),
(17, 1, 65, 15),
(18, 1, 67, 1),
(19, 1, 72, 1),
(20, 1, 74, 1),
(21, 1, 85, 17),
(22, 1, 101, 10),
(23, 1, 103, 6);

-- --------------------------------------------------------

--
-- Estrutura para tabela `fornecedores`
--

CREATE TABLE `fornecedores` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `fornecedores`
--

INSERT INTO `fornecedores` (`id`, `nome`, `ativo`) VALUES
(4, 'NAGEM', 1),
(5, 'MICROOFFICE', 1),
(6, 'BLUE WHALE COMERCIO', 1),
(7, 'FUJIOKA ELETRO E IMAGEM S.A', 1),
(8, 'HREBOS', 1),
(9, 'SISTEMAQ', 1),
(10, 'MERCADO LIVRE', 1),
(11, 'DANKA', 1),
(12, 'LEMONE COMERCIO LTDA', 1),
(13, 'KGMLAN', 1),
(14, 'MEILING', 1),
(15, 'CIL COMERCIO DE INFORMATICA LTDA', 1),
(16, 'RENAN IPHONES E ACESSORIOS LTDA', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs_acesso`
--

CREATE TABLE `logs_acesso` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `data_acesso` timestamp NOT NULL DEFAULT current_timestamp(),
  `sucesso` tinyint(1) DEFAULT NULL,
  `ip_acesso` varchar(45) DEFAULT NULL,
  `navegador_acesso` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `logs_acesso`
--

INSERT INTO `logs_acesso` (`id`, `usuario_id`, `data_acesso`, `sucesso`, `ip_acesso`, `navegador_acesso`) VALUES
(3, 4, '2025-01-29 19:59:54', 1, NULL, NULL),
(4, 4, '2025-01-29 20:10:20', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36'),
(5, 4, '2025-01-30 18:55:58', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36'),
(6, 4, '2025-01-31 17:47:48', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36'),
(7, 4, '2025-02-03 17:52:47', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36'),
(8, 4, '2025-02-04 19:06:44', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36'),
(9, 4, '2025-02-05 18:02:30', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `marca` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `marca`) VALUES
(51, 'CABO HDMI/HDMI FHD 1,8 M', 'NWAY'),
(52, 'CABO DE FORÇA', 'HAYON'),
(53, 'CABO FULL HD UGREEN VGA 2 METROS', 'UGREEN'),
(54, 'GALAXY A35 5G', 'SAMSUNG'),
(55, 'GALAXY A15 5G', 'SAMSUNG'),
(56, 'MONITOR LED 21.5 AOC WIDESCREEN E2270S', 'AOC'),
(57, 'SSD KINGSTON A400 240GB', 'KINGSTON'),
(58, 'SUPORTE DE TABLET AEROSTAND SLIM', 'HREBOS'),
(59, 'CABO HDMI 1.4 S/FILTRO 10MTS', 'COMPUTER CABLE'),
(60, 'TUBO DE VOZ', 'INTELBRAS'),
(61, 'ATA 200 INTELBRAS', 'INTELBRAS'),
(62, 'MICROCOMPUTADOR LENOVO THINK CENTRE M900', 'LENOVO'),
(63, 'NOTEBOOK ACER ASPIRE 5 I7-12650H', 'ACER'),
(64, 'FILTRO PROTETOR ELETRONICO INTELBRAS EPE 1004', 'INTELBRAS'),
(65, 'MOCHILA - ACESS BP NEO STARK - BORDADO', 'DTEL'),
(66, 'CABO DE FORÇA TRIPOLAR 10A 3X0,75MM 3 METROS NOVO PADRAO 3M', 'COMPUTER CABLE'),
(67, 'FONE DE OUVIDO K-MEX HEADSET GAMER AR89 USB C/MICROFONE LED RGB', 'K-MEX'),
(68, 'CABO DE CARREGADOR TURBO KAIDI 3A USB-C 3.0 TIPO-C', 'KAIDI'),
(69, 'NOBREAK 1300VA UPS ONE 3.1 MCM', 'MCM'),
(70, 'TECLADO COM FIO INTELBRAS TCI10', 'INTELBRAS'),
(71, 'TECLADO E MOUSE SEM FIO INTELBRAS CSI50', 'INTELBRAS'),
(72, 'FONTE ATX 500W BLUECASE BILVOLT', 'BLUECASE'),
(73, 'DISPLAY MOTO G60', 'LUCIANO CELL'),
(74, 'NOTEBOOK DELL 15 3520 I3-1215U', 'DELL'),
(75, 'DISPLAY MOTO G8 PLAY', 'LUCIANO CELL'),
(76, 'CARTAO MICRO SD 64GB', 'WD PURPLE'),
(77, 'PROCESSADOR INTEL CORE I3-10105 LGA 1200', 'INTEL'),
(78, 'PLACA MAE ASUS PRIME, CHIPSET H510M-K, INTEL LGA 1200', 'GIGABYTE'),
(79, 'MEMORIA RAM 8GB DDR4 2666MHZ', 'KINGSTON'),
(80, 'GABINETE ATX C3TECH MT-C/C 200W', 'C3TECH'),
(81, 'PELÍCULAS HIDROGEL TABLET LENOVO M11', 'LUCIANO CELL'),
(82, 'KIT CAPA + PELÍCULA', 'LUCIANO CELL'),
(83, 'SMART TV TCL 50\" UHD 4K P755 GOOGLE TV', 'TCL'),
(84, 'SUPORTE UNIVERSAL UNI100', 'ELG'),
(85, 'SUPORTE APOIO NOTEBOOK UNIVERSAL - MODELO X', '9H'),
(86, 'ADAPTADOR DE REDE USB 3.0 GIGABIT LAN UL-1200', 'EXBOM'),
(87, 'TABLET LENOVO TAB M11 - TB33FU 4G 128GLG-BR', 'LENOVO'),
(88, 'DISPLAY SAMSUNG A15 5G', 'LUCIANO CELL'),
(89, 'DISPLAY SAMSUNG A22 4G OLED', 'LUCIANO CELL'),
(90, 'NOTEBOOK DELL INSPIRON 15 3520', 'DELL'),
(91, 'WINDOWS 10 PRO 32/64 BITS COA', 'MICROSOFT'),
(92, 'GABINETE ATX GOLDENTEC 1 BAIAS S/FONTE', 'C3TECH'),
(93, 'PROCESSADOR INTEL CORE I3-12100', 'INTEL'),
(94, 'PLACA MAE ASUS PRIME H610M-E D4, INTEL LGA 1700', 'ASUS'),
(95, 'MEMORIA RAM 8GB DDR3 2666MHZ KINGSTON', 'KINGSTON'),
(96, 'TECLADO E MOUSE USB INTELBRAS CCI20', 'INTELBRAS'),
(97, 'ESTABILIZADOR 500VA MCM BIVOLT', 'MCM'),
(98, 'MONITOR LED AOC 21,5 22B30HM2 GAMING HDMI', 'AOC'),
(99, 'HEADSET INTELBRAS CHS 40 USB', 'INTELBRAS'),
(100, 'BATERIA NOTEBOOK DELL 2 EN 1 YRDD6', 'Aliaance'),
(101, 'TECLADO E MOUSE LOGITECH USB MK120 PRETO', 'LOGITECH'),
(102, 'TELEFONE SEM FIO - TS 3110', 'INTELBRAS'),
(103, 'POCO X7 256GB 8GB RAM SILVER', 'XIAOMI');

-- --------------------------------------------------------

--
-- Estrutura para tabela `saida_produto`
--

CREATE TABLE `saida_produto` (
  `id_saida` int(11) NOT NULL,
  `id_empresa` int(11) DEFAULT NULL,
  `id_produto` int(11) DEFAULT NULL,
  `id_setor` int(11) DEFAULT NULL,
  `responsavel` varchar(255) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `data_saida` date DEFAULT NULL,
  `numero_ticket` varchar(50) DEFAULT NULL,
  `id_cidade` int(11) DEFAULT NULL,
  `id_estado` int(11) DEFAULT NULL,
  `observacao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `saida_produto`
--

INSERT INTO `saida_produto` (`id_saida`, `id_empresa`, `id_produto`, `id_setor`, `responsavel`, `quantidade`, `data_saida`, `numero_ticket`, `id_cidade`, `id_estado`, `observacao`) VALUES
(1, 1, 52, 10, '', 1, '2025-01-15', '202501000327', 1, NULL, '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `setores`
--

CREATE TABLE `setores` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `setores`
--

INSERT INTO `setores` (`id`, `nome`, `ativo`) VALUES
(10, 'TIC', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nivel_acesso` enum('ADMIN','USUARIO') NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `precisa_trocar_senha` tinyint(1) DEFAULT 1,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiracao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `nivel_acesso`, `ativo`, `criado_em`, `atualizado_em`, `precisa_trocar_senha`, `reset_token`, `reset_expiracao`) VALUES
(2, 'Filipe Gonçalves', 'filipe.silva@dteltelecom.psi.br', '$2y$10$F/4O5c/MGC46whcSnkkCi.v6ePZNMPwPKafkTZN0pIeiWdHaaWYj2', 'ADMIN', 1, '2024-12-27 18:38:38', '2025-02-05 19:23:59', 0, '07dc27a1e3f86d206b5b0183fdf57579d33d7999dbd3d7e966bfcf5bc35c39bb5442502b31b87c12c7fccd3ce26fac0d9364', '2025-02-05 21:23:59'),
(4, 'Matheus da Silva Alves', 'matheus.alves@dteltelecom.psi.br', '$2y$10$jeBhekeVNbSqO/YpUSUYQ.UX3SMyCb3PWmVVzaOfIF8YK52rpJEXm', 'ADMIN', 1, '2025-01-20 16:47:18', '2025-01-20 16:47:46', 0, NULL, NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `cidades`
--
ALTER TABLE `cidades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `entrada_produto`
--
ALTER TABLE `entrada_produto`
  ADD PRIMARY KEY (`id_entrada`),
  ADD KEY `id_empresa` (`id_empresa`),
  ADD KEY `id_produto` (`id_produto`),
  ADD KEY `id_fornecedor` (`id_fornecedor`);

--
-- Índices de tabela `estados`
--
ALTER TABLE `estados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`),
  ADD UNIQUE KEY `sigla` (`sigla`);

--
-- Índices de tabela `estoque`
--
ALTER TABLE `estoque`
  ADD PRIMARY KEY (`id_estoque`),
  ADD KEY `id_empresa` (`id_empresa`),
  ADD KEY `id_produto` (`id_produto`);

--
-- Índices de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `logs_acesso`
--
ALTER TABLE `logs_acesso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `saida_produto`
--
ALTER TABLE `saida_produto`
  ADD PRIMARY KEY (`id_saida`),
  ADD KEY `id_empresa` (`id_empresa`),
  ADD KEY `id_produto` (`id_produto`),
  ADD KEY `id_setor` (`id_setor`),
  ADD KEY `id_cidade` (`id_cidade`),
  ADD KEY `fk_id_estado` (`id_estado`);

--
-- Índices de tabela `setores`
--
ALTER TABLE `setores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `cidades`
--
ALTER TABLE `cidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `entrada_produto`
--
ALTER TABLE `entrada_produto`
  MODIFY `id_entrada` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de tabela `estados`
--
ALTER TABLE `estados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `estoque`
--
ALTER TABLE `estoque`
  MODIFY `id_estoque` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `logs_acesso`
--
ALTER TABLE `logs_acesso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT de tabela `saida_produto`
--
ALTER TABLE `saida_produto`
  MODIFY `id_saida` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `setores`
--
ALTER TABLE `setores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `entrada_produto`
--
ALTER TABLE `entrada_produto`
  ADD CONSTRAINT `entrada_produto_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `entrada_produto_ibfk_2` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id`),
  ADD CONSTRAINT `entrada_produto_ibfk_3` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id`);

--
-- Restrições para tabelas `estoque`
--
ALTER TABLE `estoque`
  ADD CONSTRAINT `estoque_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `estoque_ibfk_2` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id`);

--
-- Restrições para tabelas `logs_acesso`
--
ALTER TABLE `logs_acesso`
  ADD CONSTRAINT `logs_acesso_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `saida_produto`
--
ALTER TABLE `saida_produto`
  ADD CONSTRAINT `fk_id_estado` FOREIGN KEY (`id_estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `saida_produto_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `saida_produto_ibfk_2` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id`),
  ADD CONSTRAINT `saida_produto_ibfk_3` FOREIGN KEY (`id_setor`) REFERENCES `setores` (`id`),
  ADD CONSTRAINT `saida_produto_ibfk_4` FOREIGN KEY (`id_cidade`) REFERENCES `cidades` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
