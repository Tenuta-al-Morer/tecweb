<?php
session_start();

require_once 'common.php';


if (!isset($_SESSION['utente'])) {
    header("location: login.php");
    exit();
}

$htmlContent = caricaPagina('../../html/shop.html');

$emailUtente = htmlspecialchars($_SESSION['utente']);
$htmlContent = str_replace("[email_utente]", $emailUtente, $htmlContent);

echo $htmlContent;
?>