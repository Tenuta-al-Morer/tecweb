<?php
session_start();
require_once 'common.php';
require_once 'DBConnection.php';

use DB\DBConnection;

// --- CONTROLLO ACCESSO E INIZIALIZZAZIONE ---
if (!isset($_SESSION['utente'])) {
    header("Location: login.php?return=checkout.php");
    exit();
}

$ruolo = $_SESSION['ruolo']; 

// Se l'utente è admin o staff, svuota carrello e reindirizza
if ($ruolo === 'admin' || $ruolo === 'staff') {
    $db = new DBConnection();
    $db->svuotaCarrelloUtente($_SESSION['utente_id']);
    $db->closeConnection();
    
    header("Location: gestionale.php");
    exit();
}

$db = new DBConnection();
$id_utente = $_SESSION['utente_id'];

// --- VERIFICA CARRELLO ---
$carrello = $db->getCarrelloUtente($id_utente);
$itemsAttivi = array_filter($carrello, function($i) { 
    return ($i['stato'] === 'attivo' || $i['stato'] === 'active') && $i['quantita_stock'] > 0; 
});

if (empty($itemsAttivi)) {
    header("Location: carrello.php");
    exit();
}

// --- GESTIONE INVIO ORDINE (POST) ---
$errorMsg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'crea_ordine') {
    
    $indirizzo = trim($_POST['indirizzo'] . ", " . $_POST['citta'] . " " . $_POST['cap'] . " (" . $_POST['provincia'] . ")");
    
    $prefisso = trim($_POST['prefisso']);
    $numero = trim($_POST['telefono']);
    $telefonoCompleto = $prefisso . " " . $numero;

    $metodo = $_POST['metodo_pagamento'];
    
    $totaleProdotti = 0;
    foreach ($itemsAttivi as $item) $totaleProdotti += $item['totale_riga'];
    
    $sogliaGratuita = 49.00;
    $costoSpedizione = ($totaleProdotti >= $sogliaGratuita) ? 0.00 : 10.00;

    $risultato = $db->creaOrdine($id_utente, $indirizzo, $metodo, $costoSpedizione);

    if ($risultato['success']) {
        $raw_indirizzo = trim($_POST['indirizzo']);
        $raw_citta     = trim($_POST['citta']);
        $raw_cap       = trim($_POST['cap']);
        $raw_provincia = trim($_POST['provincia']);
        $raw_prefisso  = trim($_POST['prefisso']);
        $raw_telefono  = trim($_POST['telefono']);

        $db->aggiornaDatiSpedizione(
            $id_utente, 
            $raw_indirizzo, 
            $raw_citta, 
            $raw_cap, 
            $raw_provincia, 
            $raw_prefisso,  
            $raw_telefono   
        );

        header("Location: areaPersonale.php#ordini"); 
        exit();
    } else {
        // Messaggio di errore accessibile
        $errorMsg = '<div class="alert error" role="alert"><i class="fas fa-exclamation-triangle"></i> Errore durante l\'ordine: ' . htmlspecialchars($risultato['error']) . '</div>';
    }
}

// --- PREPARAZIONE DATI TEMPLATE ---
$userInfo = $db->getUserInfo($id_utente);

$val_nome = $userInfo['nome'] ?? '';
$val_cognome = $userInfo['cognome'] ?? '';
$val_indirizzo = $userInfo['indirizzo'] ?? '';
$val_citta     = $userInfo['citta'] ?? ''; 
$val_cap       = $userInfo['cap'] ?? ''; 
$val_provincia = $userInfo['provincia'] ?? ''; 
$val_prefisso  = $userInfo['prefisso'] ?? '+39'; 
$val_telefono  = $userInfo['telefono'] ?? '';

// --- GENERAZIONE LISTA PRODOTTI HTML ---
$totaleProdotti = 0;
$listaProdottiHTML = "";

foreach ($itemsAttivi as $item) {
    $totaleProdotti += $item['totale_riga'];
    
    $imgSrc = htmlspecialchars($item['img']); 

    $listaProdottiHTML .= '
    <div class="summary-item-rich">
        <div class="mini-img-wrapper">
            <img src="' . $imgSrc . '" alt="' . htmlspecialchars($item['nome']) . '">
        </div>
        <div class="rich-details">
            <span class="rich-name">' . htmlspecialchars($item['nome']) . '</span>
            <span class="rich-qty">Q.tà: ' . $item['quantita'] . '</span>
        </div>
        <div class="rich-price">€ ' . number_format($item['totale_riga'], 2) . '</div>
    </div>';
}

$costoSpedizione = ($totaleProdotti >= 49.00) ? 0.00 : 10.00;
$totaleFinale = $totaleProdotti + $costoSpedizione;
$strSpedizione = ($costoSpedizione == 0) ? '<span class="text-green">Gratuita</span>' : '€ ' . number_format($costoSpedizione, 2);

// --- CARICAMENTO E RENDER PAGINA ---
$htmlPage = caricaPagina('../../html/checkout.html');

$htmlPage = str_replace("[alert_msg]", $errorMsg, $htmlPage);
$htmlPage = str_replace("[val_nome]", htmlspecialchars($val_nome), $htmlPage);
$htmlPage = str_replace("[val_cognome]", htmlspecialchars($val_cognome), $htmlPage);
$htmlPage = str_replace("[val_indirizzo]", htmlspecialchars($val_indirizzo), $htmlPage);
$htmlPage = str_replace("[val_citta]", htmlspecialchars($val_citta), $htmlPage);
$htmlPage = str_replace("[val_cap]", htmlspecialchars($val_cap), $htmlPage);
$htmlPage = str_replace("[val_provincia]", htmlspecialchars($val_provincia), $htmlPage);
$htmlPage = str_replace("[val_prefisso]", htmlspecialchars($val_prefisso), $htmlPage);
$htmlPage = str_replace("[val_telefono]", htmlspecialchars($val_telefono), $htmlPage);

$htmlPage = str_replace("[lista_prodotti]", $listaProdottiHTML, $htmlPage);
$htmlPage = str_replace("[subtotale]", number_format($totaleProdotti, 2), $htmlPage);
$htmlPage = str_replace("[spedizione]", $strSpedizione, $htmlPage);
$htmlPage = str_replace("[totale_finale]", number_format($totaleFinale, 2), $htmlPage);

echo $htmlPage;
?>