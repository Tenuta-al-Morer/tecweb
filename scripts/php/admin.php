<?php

session_start();
require_once 'common.php';

if (!isset($_SESSION['utente'])) {
    header("location: login.php");
    exit();
}

$ruoloUtente = $_SESSION['ruolo'];


if ($ruoloUtente !== 'admin') {
    header("location: 403.php");
    exit();
}

$htmlContent = caricaPagina('../../html/admin.html');

$emailUtente = htmlspecialchars($_SESSION['utente']);

$htmlContent = str_replace("[email_utente]", $emailUtente, $htmlContent);
$htmlContent = str_replace("[riferimento]", $ruoloUtente, $htmlContent);

echo $htmlContent;

?>