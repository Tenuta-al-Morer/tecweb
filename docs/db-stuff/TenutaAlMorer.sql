-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Gen 31, 2026 alle 16:28
-- Versione del server: 8.0.44-0ubuntu0.24.04.2
-- Versione PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `acontari`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `carrello`
--

CREATE TABLE `carrello` (
  `id` int NOT NULL,
  `id_utente` int NOT NULL,
  `data_aggiornamento` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `carrello`
--

INSERT INTO `carrello` (`id`, `id_utente`, `data_aggiornamento`) VALUES
(1, 6, '2026-01-31 16:39:47'),
(2, 9, '2026-01-31 16:43:42'),
(3, 10, '2026-01-31 16:44:59'),
(4, 12, '2026-01-31 16:46:55'),
(5, 13, '2026-01-31 16:48:23');

-- --------------------------------------------------------

--
-- Struttura della tabella `carrello_elemento`
--

CREATE TABLE `carrello_elemento` (
  `id` int NOT NULL,
  `id_carrello` int NOT NULL,
  `id_vino` int NOT NULL,
  `quantita` int NOT NULL DEFAULT '1',
  `data_inserimento` datetime DEFAULT CURRENT_TIMESTAMP,
  `stato` enum('attivo','salvato') NOT NULL DEFAULT 'attivo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `carrello_elemento`
--

INSERT INTO `carrello_elemento` (`id`, `id_carrello`, `id_vino`, `quantita`, `data_inserimento`, `stato`) VALUES
(1, 1, 2, 2, '2026-01-31 16:40:38', 'attivo'),
(3, 1, 10, 3, '2026-01-31 16:40:45', 'salvato'),
(5, 2, 6, 1, '2026-01-31 16:44:04', 'attivo'),
(7, 2, 10, 1, '2026-01-31 16:44:09', 'salvato'),
(8, 3, 11, 1, '2026-01-31 16:45:06', 'attivo'),
(10, 5, 4, 1, '2026-01-31 16:48:27', 'attivo');

-- --------------------------------------------------------

--
-- Struttura della tabella `contatto`
--

CREATE TABLE `contatto` (
  `id` int NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `tipo_supporto` varchar(128) NOT NULL,
  `messaggio` text NOT NULL,
  `risposta` text,
  `data_invio` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stato` enum('aperto','risposto') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `contatto`
--

INSERT INTO `contatto` (`id`, `nome`, `cognome`, `email`, `tipo_supporto`, `messaggio`, `risposta`, `data_invio`, `stato`) VALUES
(1, 'Test', 'User', 'user@test.com', 'Informazioni', 'Ho bisogno di informazioni sulle degustazioni.', ' - ', '2025-12-22 22:39:38', 'risposto'),
(10, 'Alessandro', 'Contarini', 'alessandro.contarini@test.com', 'ordine_online', 'Non mi è ancora arrivato l&#039;ordine online', NULL, '2026-01-31 17:22:55', 'aperto'),
(11, 'Luca', 'Marcuzzo', 'luca.marcuzzo@test.com', 'informazioni_vini', 'Tra quanto è disponibile il Raboso del Piave?', NULL, '2026-01-31 17:24:39', 'aperto'),
(12, 'Alessandro', 'Contarini', 'alessandro.contarini@test.com', 'ordine_online', 'Qual è il tempo media di consegna?', NULL, '2026-01-31 17:25:27', 'aperto'),
(13, 'Giovanni', 'Visentin', 'giovanni.visentin@test.com', 'visita_degustazione', 'Esistono altri tipi di degustazioni? O cambiano a periodi?', NULL, '2026-01-31 17:25:59', 'aperto'),
(14, 'Michele', 'Stevanin', 'michele.stevanin@test.com', 'assistenza', 'Come accedo alla mia area personale?', NULL, '2026-01-31 17:26:28', 'aperto'),
(15, 'Michele', 'Stevanin', 'michele.stevanin@test.com', 'assistenza', 'Come accedo alla mia area personale?', NULL, '2026-01-31 17:27:57', 'aperto');

-- --------------------------------------------------------

--
-- Struttura della tabella `ordine`
--

CREATE TABLE `ordine` (
  `id` int NOT NULL,
  `id_utente` int DEFAULT NULL,
  `stato_ordine` enum('in_attesa','approvato','annullato') NOT NULL DEFAULT 'in_attesa',
  `totale_prodotti` decimal(10,2) NOT NULL,
  `costo_spedizione` decimal(10,2) NOT NULL,
  `totale_finale` decimal(10,2) NOT NULL,
  `indirizzo_spedizione` text NOT NULL,
  `data_creazione` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `ordine`
--

INSERT INTO `ordine` (`id`, `id_utente`, `stato_ordine`, `totale_prodotti`, `costo_spedizione`, `totale_finale`, `indirizzo_spedizione`, `data_creazione`) VALUES
(1, 6, 'in_attesa', 31.00, 10.00, 41.00, 'Via Appia nuova 10, Roma 12345 (RM)', '2026-01-31 16:41:32'),
(2, 6, 'in_attesa', 15.00, 10.00, 25.00, 'Via Appia nuova 10, Roma 12345 (RM)', '2026-01-31 16:42:27'),
(3, 9, 'in_attesa', 27.00, 10.00, 37.00, 'Via Appia nuova 10, Roma 12345 (RM)', '2026-01-31 16:44:29'),
(4, 12, 'in_attesa', 15.50, 10.00, 25.50, 'Via delle vigne 10, Vicenza 12345 (VI)', '2026-01-31 16:47:40');

-- --------------------------------------------------------

--
-- Struttura della tabella `ordine_elemento`
--

CREATE TABLE `ordine_elemento` (
  `id` int NOT NULL,
  `id_ordine` int NOT NULL,
  `id_vino` int NOT NULL,
  `nome_vino_storico` varchar(255) NOT NULL,
  `quantita` int NOT NULL,
  `prezzo_acquisto` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `ordine_elemento`
--

INSERT INTO `ordine_elemento` (`id`, `id_ordine`, `id_vino`, `nome_vino_storico`, `quantita`, `prezzo_acquisto`) VALUES
(1, 1, 4, 'Refosco', 2, 15.50),
(2, 2, 6, 'Manzoni Bianco', 1, 15.00),
(3, 3, 5, 'Chardonnay', 2, 13.50),
(4, 4, 4, 'Refosco', 1, 15.50);

-- --------------------------------------------------------

--
-- Struttura della tabella `prenotazione`
--

CREATE TABLE `prenotazione` (
  `id` int NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `tipo_degustazione` varchar(50) NOT NULL,
  `data_visita` date NOT NULL,
  `n_persone` int NOT NULL,
  `data_invio` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stato` enum('in_attesa','approvato','annullato') NOT NULL DEFAULT 'in_attesa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `prenotazione`
--

INSERT INTO `prenotazione` (`id`, `nome`, `cognome`, `email`, `tipo_degustazione`, `data_visita`, `n_persone`, `data_invio`, `stato`) VALUES
(1, 'Test', 'User', 'user', 'Piave', '2026-03-10', 4, '2025-12-22 22:39:38', 'approvato'),
(2, 'Mario', 'Rossi', 'mario.rossi@test.com', 'Linea Oro', '2026-03-15', 2, '2025-12-22 22:39:38', 'approvato'),
(3, 'Luca', 'Marcuzzo', 'luca.marcuzzo@test.com', 'Linea Oro', '2026-04-14', 2, '2025-12-27 01:23:25', 'in_attesa'),
(4, 'Guido', 'Bianchi', 'guido.bianchi@test.com', 'Piave', '2026-05-01', 4, '2025-12-27 01:23:25', 'in_attesa'),
(5, 'Alessandro', 'Contarini', 'alessandro.contarini@test.com', 'Linea Oro', '2026-05-20', 10, '2025-12-20 10:00:00', 'approvato'),
(6, 'Matteo', 'Bartolini', 'matteo.bartolini@test.com', 'Piave', '2026-06-10', 2, '2025-12-27 01:23:25', 'annullato'),
(7, 'Michele', 'Stevanin', 'michele.stevanin@test.com', 'Linea Oro', '2026-06-28', 6, '2025-11-15 08:00:00', 'annullato'),
(8, 'Giovanni', 'Visentin', 'giovanni.visentin@test.com', 'Piave', '2026-07-05', 3, '2025-12-27 01:23:25', 'in_attesa'),
(9, 'Alessandro', 'Contarini', 'alessandro.contarini@test.com', 'Linea Oro', '2026-03-21', 3, '2026-01-31 17:03:34', 'in_attesa');

-- --------------------------------------------------------

--
-- Struttura della tabella `utente`
--

CREATE TABLE `utente` (
  `id` int NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `data_registrazione` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ruolo` enum('user','admin','staff') NOT NULL DEFAULT 'user',
  `indirizzo` varchar(255) DEFAULT NULL,
  `citta` varchar(100) DEFAULT NULL,
  `cap` varchar(10) DEFAULT NULL,
  `provincia` varchar(10) DEFAULT NULL,
  `prefisso` varchar(10) DEFAULT '+39',
  `telefono` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `utente`
--

INSERT INTO `utente` (`id`, `nome`, `cognome`, `email`, `password`, `data_registrazione`, `ruolo`, `indirizzo`, `citta`, `cap`, `provincia`, `prefisso`, `telefono`) VALUES
(5, 'TestAdmin', 'Admin', 'admin', '$2y$12$wj1eqzV9r3pncPFvrdDt8ueXjxh/ED/pDd05nNHtkU6DPG/UWLneS', '2025-12-08 17:34:01', 'admin', NULL, NULL, NULL, NULL, '+39', NULL),
(6, 'TestUser', 'User', 'user', '$2y$12$oCzda95839k1P7.fGe2VvOVcK1obwGSGMPvM0AXEzbXrEfGdzwCwq', '2025-12-08 17:34:01', 'user', 'Via Appia nuova 10', 'Roma', '12345', 'RM', '+39', '3391234567'),
(7, 'TestStaff', 'Staff', 'staff', '$2y$12$b/v7bMOxqkGmRRFl4QppS.15isE25osp7c.HBD6T4IC/sH0toIwva', '2025-12-28 12:17:04', 'staff', NULL, NULL, NULL, NULL, '+39', NULL),
(9, 'Alessandro', 'Contarini', 'alessandro.contarini@test.com', '$2y$10$G0IutuDGijZDbXKc7MkT1OA4d525hF0PZg/0bWkpcYyDy7Rp6yPTu', '2025-12-28 12:28:04', 'user', 'Via Appia nuova 10', 'Roma', '12345', 'RM', '+39', '3337654321'),
(10, 'Michele', 'Stevanin', 'michele.stevanin@test.com', '$2y$10$pyTyItZKUOtZZUjMZokUAOw3onq0jHLu0I7bM.zy7.yvT9hxdz1Am', '2025-12-28 12:29:04', 'user', NULL, NULL, NULL, NULL, '+39', NULL),
(12, 'Luca', 'Marcuzzo', 'luca.marcuzzo@test.com', '$2y$10$KAB/W440DhM5yIQAyF7CB.HRi56B7O3kQjjmXJLwQP00AT8jw3zjO', '2026-01-31 16:46:48', 'user', 'Via delle vigne 10', 'Vicenza', '12345', 'VI', '+39', '3357654123'),
(13, 'Giovanni', 'Visentin', 'giovanni.visentin@test.com', '$2y$10$c/jCqWAumhPsXm4LsQJkCeGaDUCBiXJsH/T19k9HbPsXc53ncTfzm', '2026-01-31 16:48:15', 'user', NULL, NULL, NULL, NULL, '+39', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `vino`
--

CREATE TABLE `vino` (
  `id` int NOT NULL,
  `nome` varchar(255) NOT NULL,
  `prezzo` decimal(10,2) NOT NULL,
  `quantita_stock` int NOT NULL DEFAULT '0',
  `stato` enum('attivo','nascosto','eliminato') DEFAULT 'attivo',
  `img` varchar(255) NOT NULL,
  `categoria` enum('rossi','bianchi','selezione') NOT NULL,
  `descrizione_breve` varchar(255) NOT NULL,
  `descrizione_estesa` text NOT NULL,
  `vitigno` varchar(100) DEFAULT 'N/D',
  `annata` varchar(20) DEFAULT 'N/D',
  `gradazione` varchar(20) DEFAULT 'N/D',
  `temperatura` varchar(20) DEFAULT 'N/D',
  `abbinamenti` varchar(255) DEFAULT 'N/D'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `vino`
--

INSERT INTO `vino` (`id`, `nome`, `prezzo`, `quantita_stock`, `stato`, `img`, `categoria`, `descrizione_breve`, `descrizione_estesa`, `vitigno`, `annata`, `gradazione`, `temperatura`, `abbinamenti`) VALUES
(1, 'Raboso del Piave', 18.50, 0, 'attivo', '../../images/tr/Raboso del Piave.webp', 'rossi', 'Un vino rosso corposo e avvolgente.', 'Un vino rosso corposo e avvolgente, perfetto per carni rosse e formaggi stagionati. Note di marasca e prugna.', 'Raboso 100%', '2019', '13.5% Vol', '18-20°C', 'Carni rosse, Selvaggina'),
(2, 'Merlot', 14.00, 96, 'attivo', '../../images/tr/Merlot.webp', 'rossi', 'Morbido e vellutato.', 'Il classico Merlot: morbido, vellutato e versatile. Ideale per ogni occasione.', 'Merlot 100%', '2022', '12.5% Vol', '16-18°C', 'Arrosti, Formaggi media stagionatura'),
(3, 'Cabernet Franc', 16.00, 97, 'nascosto', '../../images/tr/Cabernet Franc 1.webp', 'rossi', 'Deciso e persistente.', 'Note erbacee caratteristiche, gusto deciso e persistente. Un vino di carattere.', 'Cabernet Franc', '2021', '13.0% Vol', '16-18°C', 'Salumi, Grigliate'),
(4, 'Refosco', 15.50, 155, 'attivo', '../../images/tr/Refosco.webp', 'rossi', 'Carattere forte e intenso.', 'Autoctono dal carattere forte, colore rosso rubino intenso con riflessi violacei.', 'Refosco p.r.', '2021', '13.0% Vol', '16-18°C', 'Piatti tipici veneti, Carni grasse'),
(5, 'Chardonnay', 13.50, 220, 'attivo', '../../images/tr/Chardonnay.webp', 'bianchi', 'Elegante e fruttato.', 'Elegante, fruttato con sentori di mela golden e crosta di pane.', 'Chardonnay', '2023', '12.0% Vol', '8-10°C', 'Antipasti magri, Pesce'),
(6, 'Manzoni Bianco', 15.00, 57, 'attivo', '../../images/tr/Manzoni Bianco.webp', 'bianchi', 'Aromatico e strutturato.', 'Incrocio Riesling e Pinot Bianco. Aromatico, strutturato e di grande eleganza.', 'Incrocio Manzoni', '2023', '13.0% Vol', '10-12°C', 'Risotti, Crostacei'),
(7, 'Pinot Grigio', 13.00, 196, 'nascosto', '../../images/tr/Pinot Grigio.webp', 'bianchi', 'Fresco e sapido.', 'Fresco, sapido e piacevole. Ottimo come aperitivo o tutto pasto leggero.', 'Pinot Grigio', '2023', '12.0% Vol', '8-10°C', 'Aperitivi, Carni bianche'),
(8, 'Prosecco', 12.50, 0, 'attivo', '../../images/tr/Prosecco.webp', 'bianchi', 'Le bollicine venete.', 'Le bollicine venete per eccellenza. Fresco, vivace e floreale.', 'Glera 100%', '2024', '11.0% Vol', '6-8°C', 'Brindisi, Aperitivi, Dolci secchi'),
(9, 'Gran Morer', 25.00, 118, 'attivo', '../../images/tr/Gran Morer.webp', 'selezione', 'Riserva speciale.', 'La nostra riserva speciale. Invecchiato in botte, complesso e speziato.', 'Uvaggio Segreto', '2018', '14.5% Vol', '18-20°C', 'Meditazione, Carni importanti'),
(10, 'Vigna Dorata', 22.00, 19, 'attivo', '../../images/tr/Vigna Dorata.webp', 'selezione', 'Dolce e avvolgente.', 'Selezione di uve passite, dolce, avvolgente e dai riflessi dorati.', 'Verduzzo', '2020', '14.0% Vol', '10-12°C', 'Pasticceria secca, Formaggi erborinati'),
(11, 'Incanto', 20.00, 277, 'attivo', '../../images/tr/Incanto.webp', 'selezione', 'Profumi floreali.', 'Un vino che incanta per i suoi profumi floreali intensi e la persistenza.', 'Vitigni aromatici', '2022', '12.5% Vol', '8-10°C', 'Piatti speziati, Formaggi freschi'),
(12, 'Rosae Nobile', 19.00, 0, 'attivo', '../../images/tr/Rosae Nobile.webp', 'selezione', 'Rosato di alta classe.', 'Rosato di alta classe, note di frutti di bosco e rosa canina.', 'Raboso vinif. in bianco', '2023', '12.0% Vol', '8-10°C', 'Antipasti di pesce, Sushi');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `carrello`
--
ALTER TABLE `carrello`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_carrello_utente` (`id_utente`);

--
-- Indici per le tabelle `carrello_elemento`
--
ALTER TABLE `carrello_elemento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_elemento_carrello_cart` (`id_carrello`),
  ADD KEY `fk_elemento_carrello_vino` (`id_vino`);

--
-- Indici per le tabelle `contatto`
--
ALTER TABLE `contatto`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `ordine`
--
ALTER TABLE `ordine`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ordine_utente_smart` (`id_utente`);

--
-- Indici per le tabelle `ordine_elemento`
--
ALTER TABLE `ordine_elemento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dettaglio_ordine_ord` (`id_ordine`),
  ADD KEY `fk_dettaglio_ordine_vino` (`id_vino`);

--
-- Indici per le tabelle `prenotazione`
--
ALTER TABLE `prenotazione`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `utente`
--
ALTER TABLE `utente`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indici per le tabelle `vino`
--
ALTER TABLE `vino`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `carrello`
--
ALTER TABLE `carrello`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `carrello_elemento`
--
ALTER TABLE `carrello_elemento`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT per la tabella `contatto`
--
ALTER TABLE `contatto`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT per la tabella `ordine`
--
ALTER TABLE `ordine`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `ordine_elemento`
--
ALTER TABLE `ordine_elemento`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `prenotazione`
--
ALTER TABLE `prenotazione`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT per la tabella `utente`
--
ALTER TABLE `utente`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT per la tabella `vino`
--
ALTER TABLE `vino`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `carrello`
--
ALTER TABLE `carrello`
  ADD CONSTRAINT `fk_carrello_utente` FOREIGN KEY (`id_utente`) REFERENCES `utente` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `carrello_elemento`
--
ALTER TABLE `carrello_elemento`
  ADD CONSTRAINT `fk_elemento_carrello_cart` FOREIGN KEY (`id_carrello`) REFERENCES `carrello` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_elemento_carrello_vino` FOREIGN KEY (`id_vino`) REFERENCES `vino` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `ordine`
--
ALTER TABLE `ordine`
  ADD CONSTRAINT `fk_ordine_utente_smart` FOREIGN KEY (`id_utente`) REFERENCES `utente` (`id`) ON DELETE SET NULL;

--
-- Limiti per la tabella `ordine_elemento`
--
ALTER TABLE `ordine_elemento`
  ADD CONSTRAINT `fk_dettaglio_ordine_ord` FOREIGN KEY (`id_ordine`) REFERENCES `ordine` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_dettaglio_ordine_vino` FOREIGN KEY (`id_vino`) REFERENCES `vino` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;