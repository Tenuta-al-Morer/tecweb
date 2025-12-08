<?php
session_start();

require_once 'common.php';


if (!isset($_SESSION['utente'])) {
    header("location: login.php");
    exit();
}

$htmlContent = caricaPagina('shop.html');

echo $htmlContent;
?>