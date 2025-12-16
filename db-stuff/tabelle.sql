-- phpMyAdmin SQL Dump
-- version 5.2.2deb1+deb13u1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Dic 11, 2025 alle 23:39
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
-- Database: `acontari`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `carrello`
--

CREATE TABLE `carrello` (
  `id` int(11) NOT NULL,
  `id_utente` int(11) NOT NULL,
  `data_aggiornamento` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
);

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
);

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
);

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
);

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
);

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
);

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
);

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
);

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
);

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
  `img` varchar(255) NOT NULL,
  `categoria` enum('rossi','bianchi','selezione') NOT NULL,
  `descrizione_breve` varchar(255) NOT NULL, -- visiblile nella card
  `descrizione_estesa` text NOT NULL, -- nel popup
  
  -- dati tecnici presenti sul popup
  `vitigno` varchar(100) DEFAULT 'N/D',
  `annata` varchar(20) DEFAULT 'N/D',
  `gradazione` varchar(20) DEFAULT 'N/D',
  `temperatura` varchar(20) DEFAULT 'N/D',
  `abbinamenti` varchar(255) DEFAULT 'N/D',
);

--
-- Indici per le tabelle scaricate
--

ALTER TABLE `carrello`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_carrello_utente` (`id_utente`);

ALTER TABLE `carrello_elemento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_elemento_carrello_cart` (`id_carrello`),
  ADD KEY `fk_elemento_carrello_vino` (`id_vino`);

ALTER TABLE `contatto`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `contatto_archivio`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ordine`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ordine_utente_smart` (`id_utente`);

ALTER TABLE `ordine_elemento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dettaglio_ordine_ord` (`id_ordine`),
  ADD KEY `fk_dettaglio_ordine_vino` (`id_vino`);

ALTER TABLE `prenotazione`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `prenotazione_archivio`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `utente`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `vino`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

ALTER TABLE `carrello`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `carrello_elemento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `contatto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `contatto_archivio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `ordine`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `ordine_elemento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `prenotazione`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `prenotazione_archivio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `utente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `vino`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

ALTER TABLE `carrello`
  ADD CONSTRAINT `fk_carrello_utente` FOREIGN KEY (`id_utente`) REFERENCES `utente` (`id`) ON DELETE CASCADE;

ALTER TABLE `carrello_elemento`
  ADD CONSTRAINT `fk_elemento_carrello_cart` FOREIGN KEY (`id_carrello`) REFERENCES `carrello` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_elemento_carrello_vino` FOREIGN KEY (`id_vino`) REFERENCES `vino` (`id`) ON DELETE CASCADE;

ALTER TABLE `ordine`
  ADD CONSTRAINT `fk_ordine_utente_smart` FOREIGN KEY (`id_utente`) REFERENCES `utente` (`id`) ON DELETE SET NULL;

ALTER TABLE `ordine_elemento`
  ADD CONSTRAINT `fk_dettaglio_ordine_ord` FOREIGN KEY (`id_ordine`) REFERENCES `ordine` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_dettaglio_ordine_vino` FOREIGN KEY (`id_vino`) REFERENCES `vino` (`id`);

--
-- Dati
--

INSERT INTO `utente` (`id`, `nome`, `cognome`, `email`, `password`, `data_registrazione`, `ruolo`) VALUES
(4, 'Michele', 'Stevanin', 'michele.stevanin@gmail.com', '$2y$12$AUKWqbIW7VPzufNsQjq7c.glvZMbs4h32/NPuDjuRJtZSLf5vN8fu', '2025-12-08 17:34:01', 'user'),
(5, 'TestAdmin', 'Admin', 'admin@test.com', '$2y$10$C4nvcMK.3tnfALXz9sUmmeT.bJZxczgp.A3L1okyuOZf6NjWw561m', '2025-12-08 17:34:01', 'admin'),
(6, 'TestUser', 'User', 'user@test.com', '$2y$10$CubhFtqfSPXVCYsGn4Y5B.MOwk80YcjYZz3hS8fAtb4xdPygFNy/G', '2025-12-08 17:34:01', 'user');

INSERT INTO `vino` 
(`id` `nome`, `prezzo`, `img`, `categoria`, `descrizione_breve`, `descrizione_estesa`, `vitigno`, `annata`, `gradazione`, `temperatura`, `abbinamenti`) 
VALUES 
-- ROSSI
(1, 'Raboso del Piave', 18.50, '../images/tr/Raboso del Piave', 'rossi', 
 'Un vino rosso corposo e avvolgente.', 
 'Un vino rosso corposo e avvolgente, perfetto per carni rosse e formaggi stagionati. Note di marasca e prugna.', 
 'Raboso 100%', '2019', '13.5% Vol', '18-20°C', 'Carni rosse, Selvaggina'),

(2, 'Merlot', 14.00, '../images/tr/Merlot.webp', 'rossi', 
 'Morbido e vellutato.', 
 'Il classico Merlot: morbido, vellutato e versatile. Ideale per ogni occasione.', 
 'Merlot 100%', '2022', '12.5% Vol', '16-18°C', 'Arrosti, Formaggi media stagionatura'),

(3, 'Cabernet Franc', 16.00, '../images/tr/Cabernet Franc 1', 'rossi', 
 'Deciso e persistente.', 
 'Note erbacee caratteristiche, gusto deciso e persistente. Un vino di carattere.', 
 'Cabernet Franc', '2021', '13.0% Vol', '16-18°C', 'Salumi, Grigliate'),

(4, 'Refosco', 15.50, '../images/tr/Refosco.webp', 'rossi', 
 'Carattere forte e intenso.', 
 'Autoctono dal carattere forte, colore rosso rubino intenso con riflessi violacei.', 
 'Refosco p.r.', '2021', '13.0% Vol', '16-18°C', 'Piatti tipici veneti, Carni grasse'),

-- BIANCHI
(5, 'Chardonnay', 13.50, '../images/tr/Chardonnay.webp', 'bianchi', 
 'Elegante e fruttato.', 
 'Elegante, fruttato con sentori di mela golden e crosta di pane.', 
 'Chardonnay', '2023', '12.0% Vol', '8-10°C', 'Antipasti magri, Pesce'),

(6, 'Manzoni Bianco', 15.00, '../images/tr/Manzoni Bianco.webp', 'bianchi', 
 'Aromatico e strutturato.', 
 'Incrocio Riesling e Pinot Bianco. Aromatico, strutturato e di grande eleganza.', 
 'Incrocio Manzoni', '2023', '13.0% Vol', '10-12°C', 'Risotti, Crostacei'),

(7, 'Pinot Grigio', 13.00, '../images/tr/Pinot Grigio.webp', 'bianchi', 
 'Fresco e sapido.', 
 'Fresco, sapido e piacevole. Ottimo come aperitivo o tutto pasto leggero.', 
 'Pinot Grigio', '2023', '12.0% Vol', '8-10°C', 'Aperitivi, Carni bianche'),

(8, 'Prosecco', 12.50, '../images/tr/Prosecco.webp', 'bianchi', 
 'Le bollicine venete.', 
 'Le bollicine venete per eccellenza. Fresco, vivace e floreale.', 
 'Glera 100%', '2024', '11.0% Vol', '6-8°C', 'Brindisi, Aperitivi, Dolci secchi'),

-- SELEZIONE
(9, 'Gran Morer', 25.00, '../images/tr/Gran Morer.webp', 'selezione', 
 'Riserva speciale.', 
 'La nostra riserva speciale. Invecchiato in botte, complesso e speziato.', 
 'Uvaggio Segreto', '2018', '14.5% Vol', '18-20°C', 'Meditazione, Carni importanti'),

(10, 'Vigna Dorata', 22.00, '../images/tr/Vigna Dorata.webp', 'selezione', 
 'Dolce e avvolgente.', 
 'Selezione di uve passite, dolce, avvolgente e dai riflessi dorati.', 
 'Verduzzo', '2020', '14.0% Vol', '10-12°C', 'Pasticceria secca, Formaggi erborinati'),

(11, 'Incanto', 20.00, '../images/tr/Incanto.webp', 'selezione', 
 'Profumi floreali.', 
 'Un vino che incanta per i suoi profumi floreali intensi e la persistenza.', 
 'Vitigni aromatici', '2022', '12.5% Vol', '8-10°C', 'Piatti speziati, Formaggi freschi'),

(12, 'Rosae Nobile', 19.00, '../images/tr/Rosae Nobile.webp', 'selezione', 
 'Rosato di alta classe.', 
 'Rosato di alta classe, note di frutti di bosco e rosa canina.', 
 'Raboso vinif. in bianco', '2023', '12.0% Vol', '8-10°C', 'Antipasti di pesce, Sushi');

INSERT INTO `ordine`
(`id`,`id_utente`, `stato_ordine`, `totale_prodotti`, `costo_spedizione`, `totale_finale`, `indirizzo_spedizione`, `metodo_pagamento`, `id_transazione`, `data_creazione`)
VALUES
(1, 4, 'in_attesa', 3.00, 10.50, 40.50, 'indirizzo', 'GooglePay', '1', NOW());

INSERT INTO `ordine_elemento`
(`id`, `id_ordine`, `id_vino`, `nome_vino_storico`, `quantita`, `prezzo_acquisto`)
VALUES
(1, 1, 6, 'Manzoni Bianco', 2, 30.00);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
