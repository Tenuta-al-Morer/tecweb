<?php

session_start();

require_once 'common.php';


if (!isset($_SESSION['utente'])) {
    header("location: login.php");
    exit();
}


$htmlContent = caricaPagina('../../html/utente.html');


$emailUtente = htmlspecialchars($_SESSION['utente']);
$ruoloUtente = $_SESSION['ruolo'];
$htmlContent = str_replace("[email_utente]", $emailUtente, $htmlContent);
$htmlContent = str_replace("[riferimento]", $ruoloUtente, $htmlContent);



echo $htmlContent;
?>