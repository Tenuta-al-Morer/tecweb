<?php

session_start();

require_once 'common.php';


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
} else if ($ruoloUtente == 'user') {
    header("location: user.php");
    exit();
}
?>