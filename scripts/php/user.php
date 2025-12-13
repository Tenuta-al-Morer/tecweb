<?php

if (!isset($_SESSION['utente'])) {
    header("location: login.php");
    exit();
}

$ruoloUtente = $_SESSION['ruolo'];

if ($ruoloUtente === 'admin') {
    header("location: admin.php");
    exit();
} else if ($ruoloUtente === 'moderatore') {
    header("location: moderatore.php");
    exit();
} else if ($ruoloUtente !== 'user') {
    header("location: 404.php");
    exit();
}

$htmlContent = caricaPagina('../../html/user.html');

$emailUtente = htmlspecialchars($_SESSION['utente']);

$htmlContent = str_replace("[email_utente]", $emailUtente, $htmlContent);
$htmlContent = str_replace("[riferimento]", $ruoloUtente, $htmlContent);

echo $htmlContent;

?>