-- phpMyAdmin SQL Dump
-- version 5.2.2deb1+deb13u1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Dic 15, 2025 alle 11:49
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dump dei dati per la tabella `carrello`
--

INSERT INTO `carrello` (`id`, `id_utente`, `data_aggiornamento`) VALUES
(1, 3, '2025-12-14 18:30:00'),
(2, 2, '2025-12-15 09:00:00');

-- --------------------------------------------------------

--
-- Struttura della tabella `carrello_elemento`
--

CREATE TABLE `carrello_elemento` (
  `id` int(11) NOT NULL,
  `id_carrello` int(11) NOT NULL,
  `id_vino` int(11) NOT NULL,
  `quantita` int(11) NOT NULL DEFAULT 1,
  `data_inserimento` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dump dei dati per la tabella `carrello_elemento`
--

INSERT INTO `carrello_elemento` (`id`, `id_carrello`, `id_vino`, `quantita`, `data_inserimento`) VALUES
(1, 1, 1, 2, '2025-12-14 18:25:00'),
(2, 1, 6, 6, '2025-12-14 18:30:00'),
(3, 2, 4, 1, '2025-12-15 09:00:00');

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
  `data_invio` datetime NOT NULL DEFAULT current_timestamp(),
  `stato` varchar(20) NOT NULL DEFAULT 'aperto'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dump dei dati per la tabella `contatto`
--

INSERT INTO `contatto` (`id`, `nome`, `cognome`, `email`, `tipo_supporto`, `prefisso`, `telefono`, `messaggio`, `data_invio`, `stato`) VALUES
(1, 'Anna', 'Neri', 'anna.neri@provider.it', 'Informazioni', '+39', '3311122334', 'Buongiorno, spedite anche all estero? Grazie.', '2025-12-01 09:00:00', 'chiuso'),
(2, 'Marco', 'Gialli', 'm.gialli@webmail.com', 'Collaborazione', '+39', '3388877665', 'Salve, sono un ristoratore di Bologna, vorrei la vostra lista prezzi ingrosso.', '2025-12-14 11:20:00', 'aperto'),
(3, 'Elena', 'Blu', 'elena.blu@studio.it', 'Problema Ordine', '+39', '3295544332', 'Il mio ordine n.123 non è ancora arrivato, potete verificare?', '2025-12-15 08:45:00', 'aperto');

-- --------------------------------------------------------

--
-- Struttura della tabella `contatto_archivio`
--

CREATE TABLE `contatto_archivio` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `tipo_supporto` varchar(128) NOT NULL,
  `prefisso` varchar(10) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `messaggio` text NOT NULL,
  `data_invio` datetime NOT NULL DEFAULT current_timestamp(),
  `stato` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ordine`
--

CREATE TABLE `ordine` (
  `id` int(11) NOT NULL,
  `id_utente` int(11) DEFAULT NULL,
  `stato_ordine` enum('in_attesa','pagato','in_preparazione','spedito','consegnato','annullato') NOT NULL DEFAULT 'in_attesa',
  `totale_prodotti` decimal(10,2) NOT NULL,
  `costo_spedizione` decimal(10,2) NOT NULL,
  `totale_finale` decimal(10,2) NOT NULL,
  `indirizzo_spedizione` text NOT NULL,
  `metodo_pagamento` varchar(50) NOT NULL,
  `id_transazione` varchar(255) DEFAULT NULL,
  `data_creazione` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dump dei dati per la tabella `ordine`
--

INSERT INTO `ordine` (`id`, `id_utente`, `stato_ordine`, `totale_prodotti`, `costo_spedizione`, `totale_finale`, `indirizzo_spedizione`, `metodo_pagamento`, `id_transazione`, `data_creazione`) VALUES
(1, 3, 'consegnato', 37.00, 10.00, 47.00, 'Via Roma 1, Milano', 'carta_credito', 'TRX_123456', '2025-10-10 14:00:00'),
(2, 3, 'spedito', 90.00, 0.00, 90.00, 'Via Roma 1, Milano', 'paypal', 'TRX_987654', '2025-12-01 09:00:00'),
(3, 3, 'in_attesa', 120.00, 0.00, 120.00, 'Sede Legale, Roma', 'bonifico', NULL, '2025-12-15 10:00:00');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dump dei dati per la tabella `ordine_elemento`
--

INSERT INTO `ordine_elemento` (`id`, `id_ordine`, `id_vino`, `nome_vino_storico`, `quantita`, `prezzo_acquisto`) VALUES
(1, 1, 2, 'Valpolicella Ripasso', 2, 18.50),
(2, 2, 1, 'Amarone della Valpolicella Classico', 2, 45.00),
(3, 3, 5, 'Brunello di Montalcino', 2, 60.00);

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
  `stato` varchar(20) NOT NULL DEFAULT 'In attesa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dump dei dati per la tabella `prenotazione`
--

INSERT INTO `prenotazione` (`id`, `nome`, `cognome`, `email`, `tipo_degustazione`, `prefisso`, `telefono`, `data_visita`, `n_persone`, `data_invio`, `stato`) VALUES
(1, 'Luca', 'Bianchi', 'luca.bianchi@mail.com', 'Degustazione Premium', '+39', '3331234567', '2025-12-20', 4, '2025-12-10 10:00:00', 'Confermata'),
(2, 'Giulia', 'Verdi', 'g.verdi@test.it', 'Tour Cantina', '+39', '3409876543', '2025-12-22', 2, '2025-12-12 15:30:00', 'In attesa'),
(3, 'John', 'Smith', 'john.smith@usa.com', 'Degustazione Classica', '+1', '5550199', '2026-01-05', 10, '2025-12-14 08:00:00', 'In attesa');

-- --------------------------------------------------------

--
-- Struttura della tabella `prenotazione_archivio`
--

CREATE TABLE `prenotazione_archivio` (
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
  `stato` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

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
  `ruolo` varchar(20) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dump dei dati per la tabella `utente`
--

INSERT INTO `utente` (`id`, `nome`, `cognome`, `email`, `password`, `data_registrazione`, `ruolo`) VALUES
(1, 'Super', 'Admin', 'admin@admin.it', '$2y$12$ulzgAmvf7bwCv5xU22kJ3uSv63WPO1aic2OzOpv/67RKWg2P4r/Ci', '2024-01-01 10:00:00', 'admin'),
(2, 'Capo', 'Amministratore', 'amministratore@amministratore.it', '$2y$12$ulzgAmvf7bwCv5xU22kJ3uSv63WPO1aic2OzOpv/67RKWg2P4r/Ci', '2024-01-02 11:30:00', 'admin'),
(3, 'Mario', 'Rossi', 'user@user.it', '$2y$12$.cvgluCIDsxjFm/6M7D.teqXXEcXnvEJXcbiAWDXCJdiMClbGJ16a', '2024-02-15 09:15:00', 'user');

-- --------------------------------------------------------

--
-- Struttura della tabella `vino`
--

CREATE TABLE `vino` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `prezzo` decimal(10,2) NOT NULL,
  `quantita_stock` int(11) NOT NULL DEFAULT 0,
  `stato` enum('attivo','nascosto','fuori_produzione') DEFAULT 'attivo',
  `img` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dump dei dati per la tabella `vino`
--

INSERT INTO `vino` (`id`, `nome`, `prezzo`, `quantita_stock`, `stato`, `img`) VALUES
(1, 'Amarone della Valpolicella Classico', 45.00, 50, 'attivo', 'amarone_classico.jpg'),
(2, 'Valpolicella Ripasso', 18.50, 120, 'attivo', 'ripasso.jpg'),
(3, 'Chianti Classico Riserva', 22.00, 80, 'attivo', 'chianti_riserva.jpg'),
(4, 'Barolo DOCG', 55.00, 30, 'attivo', 'barolo.jpg'),
(5, 'Brunello di Montalcino', 60.00, 25, 'attivo', 'brunello.jpg'),
(6, 'Prosecco Superiore DOCG', 12.00, 200, 'attivo', 'prosecco.jpg'),
(7, 'Franciacorta Saten', 28.00, 60, 'attivo', 'franciacorta.jpg'),
(8, 'Gewürztraminer Trentino', 16.50, 90, 'attivo', 'gewurztraminer.jpg'),
(9, 'Vermentino di Gallura', 14.00, 100, 'attivo', 'vermentino.jpg'),
(10, 'Primitivo di Manduria', 15.50, 110, 'attivo', 'primitivo.jpg'),
(11, 'Cannonau di Sardegna', 13.00, 95, 'attivo', 'cannonau.jpg'),
(12, 'Montepulciano d\'Abruzzo', 11.00, 150, 'attivo', 'montepulciano.jpg'),
(13, 'Falanghina del Sannio', 12.50, 130, 'attivo', 'falanghina.jpg'),
(14, 'Nero d\'Avola Sicilia', 10.50, 180, 'attivo', 'nero_avola.jpg'),
(15, 'Greco di Tufo', 17.00, 70, 'attivo', 'greco_tufo.jpg'),
(16, 'Nebbiolo Langhe', 20.00, 85, 'attivo', 'nebbiolo.jpg'),
(17, 'Barbaresco DOCG', 48.00, 40, 'attivo', 'barbaresco.jpg'),
(18, 'Lagrein Alto Adige', 19.00, 65, 'attivo', 'lagrein.jpg'),
(19, 'Sauvignon Blanc Collio', 21.00, 55, 'attivo', 'sauvignon.jpg'),
(20, 'Recioto della Valpolicella', 35.00, 45, 'nascosto', 'recioto.jpg');

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
-- Indici per le tabelle `contatto_archivio`
--
ALTER TABLE `contatto_archivio`
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
-- Indici per le tabelle `prenotazione_archivio`
--
ALTER TABLE `prenotazione_archivio`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `carrello_elemento`
--
ALTER TABLE `carrello_elemento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `contatto`
--
ALTER TABLE `contatto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `contatto_archivio`
--
ALTER TABLE `contatto_archivio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `ordine`
--
ALTER TABLE `ordine`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `ordine_elemento`
--
ALTER TABLE `ordine_elemento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `prenotazione`
--
ALTER TABLE `prenotazione`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `prenotazione_archivio`
--
ALTER TABLE `prenotazione_archivio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `utente`
--
ALTER TABLE `utente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `vino`
--
ALTER TABLE `vino`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

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
