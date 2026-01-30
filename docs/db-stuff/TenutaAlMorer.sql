-- phpMyAdmin SQL Dump
-- version 5.2.2deb1+deb13u1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Gen 05, 2026 alle 19:25
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
-- Struttura della tabella `carrello`
--

CREATE TABLE `carrello` (
  `id` int(11) NOT NULL,
  `id_utente` int(11) NOT NULL,
  `data_aggiornamento` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dump dei dati per la tabella `carrello`
--

INSERT INTO `carrello` (`id`, `id_utente`, `data_aggiornamento`) VALUES
(1, 6, '2025-12-22 22:39:38'),
(2, 5, '2025-12-27 14:09:07'),
(3, 7, '2025-12-28 12:19:45');

-- --------------------------------------------------------

--
-- Struttura della tabella `carrello_elemento`
--

CREATE TABLE `carrello_elemento` (
  `id` int(11) NOT NULL,
  `id_carrello` int(11) NOT NULL,
  `id_vino` int(11) NOT NULL,
  `quantita` int(11) NOT NULL DEFAULT 1,
  `data_inserimento` datetime DEFAULT current_timestamp(),
  `stato` enum('attivo','salvato') NOT NULL DEFAULT 'attivo'
) ;

--
-- Dump dei dati per la tabella `carrello_elemento`
--

INSERT INTO `carrello_elemento` (`id`, `id_carrello`, `id_vino`, `quantita`, `data_inserimento`, `stato`) VALUES
(5, 1, 8, 0, '2025-12-27 01:27:14', 'salvato'),
(25, 1, 2, 4, '2026-01-05 19:57:04', 'attivo');

-- --------------------------------------------------------

--
-- Struttura della tabella `contatto`
--

CREATE TABLE `contatto` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `tipo_supporto` varchar(128) NOT NULL,
  `prefisso` varchar(10) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `messaggio` text NOT NULL,
  `risposta` text DEFAULT NULL,
  `data_invio` datetime NOT NULL DEFAULT current_timestamp(),
  `stato` enum('aperto','risposto') NOT NULL
) ;

--
-- Dump dei dati per la tabella `contatto`
--

INSERT INTO `contatto` (`id`, `nome`, `cognome`, `email`, `tipo_supporto`, `prefisso`, `telefono`, `messaggio`, `risposta`, `data_invio`, `stato`) VALUES
(1, 'Test', 'User', 'user@test.com', 'Informazioni', '+39', '3331234567', 'Ho bisogno di informazioni sulle degustazioni.', ' - ', '2025-12-22 22:39:38', 'risposto'),
(2, 'Test', 'User', 'user@test.com', 'Ordine', '+39', '3331234567', 'Buongiorno, vorrei sapere quando verrà spedito il mio ordine.', '', '2025-12-22 22:39:38', 'aperto'),
(3, 'Michele', 'Stevanin', 'michele.stevanin@gmail.com', 'assistenza', '+39', '3510408301', 'vediamo se funziona tutto questo', '', '2025-12-27 00:58:06', 'aperto'),
(4, 'Luca', 'Toni', 'luca.toni@calcio.it', 'informazioni_vini', '+39', '3339998887', 'Salve, vorrei sapere se il Raboso è barricato. Grazie.', '', '2025-12-27 01:23:34', 'aperto'),
(5, 'Maria', 'Callas', 'divina@opera.com', 'visita_degustazione', '+39', '3337776665', 'Organizzate visite per gruppi numerosi (50 persone)?', '', '2025-12-27 01:23:34', 'aperto'),
(6, 'Sandro', 'Pertini', 'sandro@presidente.it', 'ordine_online', '+39', '3331111111', 'Ho sbagliato indirizzo nell\'ultimo ordine, come posso cambiare?', '', '2025-12-27 01:23:34', 'aperto'),
(7, 'Bebe', 'Vio', 'bebe@scherma.it', 'partnership', '+39', '3332222222', 'Vorrei proporre i vostri vini per un evento sportivo.', '', '2025-12-27 01:23:34', 'aperto'),
(8, 'Roberto', 'Baggio', 'codino@divino.it', 'assistenza', '+39', '3334444444', 'Il codice sconto non funziona nel carrello.', ' esempio di risposta fornita dal supporto.', '2025-12-27 01:23:34', 'risposto'),
(9, 'Paolo', 'Rossi', 'pablito@mondiale.it', 'altro', '+39', '3335555555', 'Avete distributori in Spagna?', '', '2025-12-27 01:23:34', 'aperto');

-- --------------------------------------------------------

--
-- Struttura della tabella `ordine`
--

CREATE TABLE `ordine` (
  `id` int(11) NOT NULL,
  `id_utente` int(11) DEFAULT NULL,
  `stato_ordine` enum('in_attesa','approvato','annullato') NOT NULL DEFAULT 'in_attesa',
  `totale_prodotti` decimal(10,2) NOT NULL,
  `costo_spedizione` decimal(10,2) NOT NULL,
  `totale_finale` decimal(10,2) NOT NULL,
  `indirizzo_spedizione` text NOT NULL,
  `data_creazione` datetime DEFAULT current_timestamp()
) ;

--
-- Dump dei dati per la tabella `ordine`
--

INSERT INTO `ordine` (`id`, `id_utente`, `stato_ordine`, `totale_prodotti`, `costo_spedizione`, `totale_finale`, `indirizzo_spedizione`, `data_creazione`) VALUES
(1, 6, 'approvato', 3.00, 10.50, 40.50, 'indirizzo', '2025-12-22 21:10:41'),
(4, 6, 'approvato', 47.00, 8.50, 55.50, 'Via Roma 12, 31020 Villorba (TV)', '2025-12-22 22:44:15'),
(5, 6, 'annullato', 32.50, 7.50, 40.00, 'Via Roma 12, 31020 Villorba (TV)', '2025-12-22 22:44:32'),
(6, 6, 'approvato', 37.00, 10.00, 47.00, 'Via Roma 1, Milano', '2025-10-15 10:00:00'),
(7, 6, 'in_attesa', 125.00, 0.00, 125.00, 'Via Roma 1, Milano', '2025-12-27 01:23:12'),
(8, 6, 'annullato', 28.00, 10.00, 38.00, 'Via Roma 1, Milano', '2025-11-20 14:30:00'),
(9, 6, 'approvato', 215.00, 0.00, 215.00, 'Ufficio Test, Roma', '2025-12-01 09:00:00'),
(10, 6, 'in_attesa', 60.00, 0.00, 60.00, 'Via Roma 1, Milano', '2025-12-27 01:23:13'),
(12, 6, 'in_attesa', 247.00, 0.00, 247.00, 'Via Roma 10, Conselve 12345 (pd)', '2025-12-27 14:34:20'),
(13, 6, 'in_attesa', 295.00, 0.00, 295.00, 'Via Appia nuova 10, Roma 00100 (RM)', '2025-12-27 20:49:26'),
(14, 6, 'in_attesa', 92.50, 0.00, 92.50, 'Via Appia nuova 10, Roma 12345 (RM)', '2025-12-27 20:54:18'),
(15, 6, 'in_attesa', 100.00, 0.00, 100.00, 'Via Appia nuova 10, Roma 12345 (RM)', '2025-12-27 21:02:16'),
(16, 6, 'in_attesa', 724.50, 0.00, 724.50, 'Via Appia nuova 10, Roma 12345 (RM)', '2025-12-27 21:04:12'),
(17, 6, 'in_attesa', 96.50, 0.00, 96.50, 'Via Appia nuova 10, Roma 12345 (RM)', '2025-12-28 02:19:04');

-- --------------------------------------------------------

--
-- Struttura della tabella `ordine_elemento`
--

CREATE TABLE `ordine_elemento` (
  `id` int(11) NOT NULL,
  `id_ordine` int(11) NOT NULL,
  `id_vino` int(11) NOT NULL,
  `nome_vino_storico` varchar(255) NOT NULL,
  `quantita` int(11) NOT NULL,
  `prezzo_acquisto` decimal(10,2) NOT NULL
) ;

--
-- Dump dei dati per la tabella `ordine_elemento`
--

INSERT INTO `ordine_elemento` (`id`, `id_ordine`, `id_vino`, `nome_vino_storico`, `quantita`, `prezzo_acquisto`) VALUES
(1, 1, 6, 'Manzoni Bianco', 2, 30.00),
(7, 6, 1, 'Raboso del Piave', 2, 18.50),
(8, 7, 9, 'Gran Morer', 5, 25.00),
(9, 8, 2, 'Merlot', 2, 14.00),
(10, 9, 11, 'Incanto', 5, 20.00),
(11, 9, 8, 'Prosecco', 6, 12.50),
(12, 9, 4, 'Refosco', 2, 15.50),
(13, 10, 6, 'Manzoni Bianco', 4, 15.00),
(17, 12, 12, 'Rosae Nobile', 13, 19.00),
(18, 13, 2, 'Merlot', 4, 14.00),
(19, 13, 9, 'Gran Morer', 5, 25.00),
(20, 13, 5, 'Chardonnay', 4, 13.50),
(21, 13, 6, 'Manzoni Bianco', 4, 15.00),
(22, 14, 1, 'Raboso del Piave', 5, 18.50),
(23, 15, 9, 'Gran Morer', 4, 25.00),
(24, 16, 1, 'Raboso del Piave', 33, 18.50),
(25, 16, 12, 'Rosae Nobile', 6, 19.00),
(26, 17, 2, 'Merlot', 4, 14.00),
(27, 17, 5, 'Chardonnay', 3, 13.50);

-- --------------------------------------------------------

--
-- Struttura della tabella `prenotazione`
--

CREATE TABLE `prenotazione` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `tipo_degustazione` varchar(50) NOT NULL,
  `prefisso` varchar(10) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `data_visita` date NOT NULL,
  `n_persone` int(11) NOT NULL,
  `data_invio` datetime NOT NULL DEFAULT current_timestamp(),
  `stato` enum('in_attesa','approvato','annullato') NOT NULL DEFAULT 'in_attesa'
) ;

--
-- Dump dei dati per la tabella `prenotazione`
--

INSERT INTO `prenotazione` (`id`, `nome`, `cognome`, `email`, `tipo_degustazione`, `prefisso`, `telefono`, `data_visita`, `n_persone`, `data_invio`, `stato`) VALUES
(1, 'Test', 'User', 'user@test.com', 'Degustazione Premium', '+39', '3331234567', '2025-10-10', 4, '2025-12-22 22:39:38', 'approvato'),
(3, 'Test', 'User', 'user@test.com', 'Degustazione Classica', '+39', '3331234567', '2026-01-15', 2, '2025-12-22 22:39:38', 'approvato'),
(5, 'Mario', 'Rossi', 'mario.rossi@fake.com', 'Linea Oro', '+39', '3331112223', '2026-02-14', 2, '2025-12-27 01:23:25', 'in_attesa'),
(6, 'Luigi', 'Verdi', 'luigi.v@provider.it', 'Piave', '+39', '3334445556', '2026-03-01', 4, '2025-12-27 01:23:25', 'in_attesa'),
(7, 'Anna', 'Bianchi', 'anna.b@testmail.com', 'Linea Oro', '+39', '3209876543', '2026-01-20', 10, '2025-12-20 10:00:00', 'approvato'),
(8, 'John', 'Doe', 'j.doe@international.com', 'Piave', '+1', '5550199', '2026-04-10', 2, '2025-12-27 01:23:25', 'annullato'),
(9, 'Elena', 'Neri', 'elena.n@fake.com', 'Linea Oro', '+39', '3471234567', '2026-02-28', 6, '2025-11-15 08:00:00', 'annullato'),
(10, 'Giulia', 'Gialli', 'giulia.g@posta.it', 'Piave', '+39', '3310000000', '2026-05-05', 3, '2025-12-27 01:23:25', 'in_attesa'),
(11, 'test', 'test', 'tes@test.test', 'Linea Oro', '+39', '43243243', '2026-03-12', 3, '2025-12-27 01:27:41', 'in_attesa');

-- --------------------------------------------------------

--
-- Struttura della tabella `utente`
--

CREATE TABLE `utente` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `data_registrazione` datetime NOT NULL DEFAULT current_timestamp(),
  `ruolo` enum('user','admin','staff') NOT NULL DEFAULT 'user',
  `indirizzo` varchar(255) DEFAULT NULL,
  `citta` varchar(100) DEFAULT NULL,
  `cap` varchar(10) DEFAULT NULL,
  `provincia` varchar(10) DEFAULT NULL,
  `prefisso` varchar(10) DEFAULT '+39',
  `telefono` varchar(20) DEFAULT NULL
) ;

--
-- Dump dei dati per la tabella `utente`
--

INSERT INTO `utente` (`id`, `nome`, `cognome`, `email`, `password`, `data_registrazione`, `ruolo`, `indirizzo`, `citta`, `cap`, `provincia`, `prefisso`, `telefono`) VALUES
(5, 'TestAdmin', 'Admin', 'admin', '$2y$10$C4nvcMK.3tnfALXz9sUmmeT.bJZxczgp.A3L1okyu0Zf6NjWw561m', '2025-12-08 17:34:01', 'admin', NULL, NULL, NULL, NULL, '+39', NULL),
(6, 'TestUser', 'User', 'user', '$2y$10$CubhFtqfSPXVCYsGn4Y5B.MOwk80YcjYZz3hS8fAtb4xdPygFNy', '2025-12-08 17:34:01', 'user', 'Via Appia nuova 10', 'Roma', '12345', 'RM', '+39', '35101555408'),
(7, 'TestStaff', 'Staff', 'staff', '$2y$12$StxkEY8E/fxSzhcqa8iGt.L1p0YpZITI62BAAerYTVDCy5blypgLW', '2025-12-28 12:17:04', 'staff', NULL, NULL, NULL, NULL, '+39', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `vino`
--

CREATE TABLE `vino` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `prezzo` decimal(10,2) NOT NULL,
  `quantita_stock` int(11) NOT NULL DEFAULT 0,
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
) ;

--
-- Dump dei dati per la tabella `vino`
--

INSERT INTO `vino` (`id`, `nome`, `prezzo`, `quantita_stock`, `stato`, `img`, `categoria`, `descrizione_breve`, `descrizione_estesa`, `vitigno`, `annata`, `gradazione`, `temperatura`, `abbinamenti`) VALUES
(1, 'Raboso del Piave', 18.50, 0, 'attivo', '../../images/tr/Raboso del Piave.webp', 'rossi', 'Un vino rosso corposo e avvolgente.', 'Un vino rosso corposo e avvolgente, perfetto per carni rosse e formaggi stagionati. Note di marasca e prugna.', 'Raboso 100%', '2019', '13.5% Vol', '18-20°C', 'Carni rosse, Selvaggina'),
(2, 'Merlot', 14.00, 96, 'attivo', '../../images/tr/Merlot.webp', 'rossi', 'Morbido e vellutato.', 'Il classico Merlot: morbido, vellutato e versatile. Ideale per ogni occasione.', 'Merlot 100%', '2022', '12.5% Vol', '16-18°C', 'Arrosti, Formaggi media stagionatura'),
(3, 'Cabernet Franc', 16.00, 97, 'nascosto', '../../images/tr/Cabernet Franc 1.webp', 'rossi', 'Deciso e persistente.', 'Note erbacee caratteristiche, gusto deciso e persistente. Un vino di carattere.', 'Cabernet Franc', '2021', '13.0% Vol', '16-18°C', 'Salumi, Grigliate'),
(4, 'Refosco', 15.50, 158, 'attivo', '../../images/tr/Refosco.webp', 'rossi', 'Carattere forte e intenso.', 'Autoctono dal carattere forte, colore rosso rubino intenso con riflessi violacei.', 'Refosco p.r.', '2021', '13.0% Vol', '16-18°C', 'Piatti tipici veneti, Carni grasse'),
(5, 'Chardonnay', 13.50, 222, 'attivo', '../../images/tr/Chardonnay.webp', 'bianchi', 'Elegante e fruttato.', 'Elegante, fruttato con sentori di mela golden e crosta di pane.', 'Chardonnay', '2023', '12.0% Vol', '8-10°C', 'Antipasti magri, Pesce'),
(6, 'Manzoni Bianco', 15.00, 58, 'attivo', '../../images/tr/Manzoni Bianco.webp', 'bianchi', 'Aromatico e strutturato.', 'Incrocio Riesling e Pinot Bianco. Aromatico, strutturato e di grande eleganza.', 'Incrocio Manzoni', '2023', '13.0% Vol', '10-12°C', 'Risotti, Crostacei'),
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `carrello_elemento`
--
ALTER TABLE `carrello_elemento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT per la tabella `contatto`
--
ALTER TABLE `contatto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT per la tabella `ordine`
--
ALTER TABLE `ordine`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT per la tabella `ordine_elemento`
--
ALTER TABLE `ordine_elemento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT per la tabella `prenotazione`
--
ALTER TABLE `prenotazione`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT per la tabella `utente`
--
ALTER TABLE `utente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT per la tabella `vino`
--
ALTER TABLE `vino`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
