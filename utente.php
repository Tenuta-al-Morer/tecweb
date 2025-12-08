<?php
// utenti.php (o utente.php, assicurati che il nome file coincida)
session_start();

require_once 'common.php';

// CONTROLLO SICUREZZA
if (!isset($_SESSION['utente'])) {
    header("location: login.php");
    exit();
}

// 1. Uso la funzione comune per caricare la pagina E gestire l'icona (che ora sarà statica)
$htmlContent = caricaPagina('utente.html');

// 2. Gestisco i placeholder specifici di QUESTA pagina
$emailUtente = htmlspecialchars($_SESSION['utente']);
$htmlContent = str_replace("[email_utente]", $emailUtente, $htmlContent);

// 3. Stampo il risultato finale
echo $htmlContent;
?>