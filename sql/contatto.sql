-- phpMyAdmin SQL Dump
-- version 5.2.2deb1+deb13u1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Dic 09, 2025 alle 14:57
-- Versione del server: 11.8.3-MariaDB-0+deb13u1 from Debian
-- Versione PHP: 8.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mstevani`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `contatto`
--

CREATE TABLE `contatto` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `tipo_supporto` varchar(123) NOT NULL,
  `prefisso` varchar(10) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `messaggio` text NOT NULL,
  `data_invio` datetime NOT NULL DEFAULT current_timestamp(),
  `stato` varchar(20) NOT NULL DEFAULT 'aperto'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `contatto`
--
ALTER TABLE `contatto`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `contatto`
--
ALTER TABLE `contatto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
