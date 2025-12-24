<?php
session_start();
require_once 'common.php';
require_once 'DBConnection.php';

use DB\DBConnection;

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
$nomeUtente = htmlspecialchars($_SESSION['nome']);

// 1) Leggo vini dal DB
$db = new DBConnection();
$ordiniArray = $db->getOrdini();
$prenotazioniArray = $db->getPrenotazioni();
$db->closeConnection();

// 2) Costruisco le righe HTML

$ordini = "";

foreach ($ordiniArray as $ordine) {
    $ordini .= "<tr>";
    $ordini .= '<th scope="row">' . (int)$ordine['id'] . '</th>';
    $ordini .= '<td data-title="ID Utente">' . (int)$ordine['id_utente'] . '</td>';
    $ordini .= '<td data-title="Costo Prodotti">' . number_format($ordine['totale_prodotti'], 2) . ' €</td>';
    $ordini .= '<td data-title="Costo Spedizione">' . number_format($ordine['costo_spedizione'], 2) . ' €</td>';
    $ordini .= '<td data-title="Totale Finale">' . number_format($ordine['totale_finale'], 2) . ' €</td>';
    $ordini .= '<td data-title="Indirizzo Spedizione">' . htmlspecialchars($ordine['indirizzo_spedizione']) . '</td>';
    $ordini .= '<td data-title="Metodo Pagamento">' . htmlspecialchars($ordine['metodo_pagamento']) . '</td>';
    $ordini .= '<td data-title="Data Creazione">' . htmlspecialchars($ordine['data_creazione']) . '</td>';
    $ordini .= '<td class="td_richiesta_degustazione" data-title="Gestione richiesta"> 
                    <form action="#" method="POST" class="standard-form">
                        <button type="submit" name="accetta" value="accetta" class="btn-secondary">Accetta</button>
                        <button type="submit" name="rifiuta" value="rifiuta" class="btn-secondary">Rifiuta</button>
                    </form>
                </td>';
    $ordini .= "</tr>";
}

$prenotazioni = "";
foreach ($prenotazioniArray as $prenotazione) {
    $prenotazioni .= "<tr>";
    $prenotazioni .= '<th scope="row">' . (int)$prenotazione['id'] . '</th>';
    $prenotazioni .= '<td data-title="Nome">' . htmlspecialchars($prenotazione['nome']) . '</td>';
    $prenotazioni .= '<td data-title="Cognome">' . htmlspecialchars($prenotazione['cognome']) . '</td>';
    $prenotazioni .= '<td data-title="Email">' . htmlspecialchars($prenotazione['email']) . '</td>';
    $prenotazioni .= '<td data-title="Telefono">' . htmlspecialchars($prenotazione['prefisso']) . htmlspecialchars($prenotazione['telefono']) . '</td>';
    $prenotazioni .= '<td data-title="Data visita">' . htmlspecialchars($prenotazione['data_visita']) . '</td>';
    $prenotazioni .= '<td data-title="Numero persone">' . (int)$prenotazione['numero_persone'] . '</td>';
    $prenotazioni .= '<td data-title="Data Invio">' . htmlspecialchars($prenotazione['data_invio']) . '</td>';
    $prenotazioni .= '<td class="td_richiesta_degustazione" data-title="Gestione richiesta"> 
                    <form action="#" method="POST" class="standard-form">
                        <button type="submit" name="accetta" value="accetta" class="btn-secondary">Accetta</button>
                        <button type="submit" name="rifiuta" value="rifiuta" class="btn-secondary">Rifiuta</button>
                    </form>
                </td>';
    $prenotazioni .= "</tr>";
}


// 3) Replace placeholders
$htmlContent = str_replace("[nome_utente]", $nomeUtente, $htmlContent);
$htmlContent = str_replace("[email_utente]", $emailUtente, $htmlContent);
$htmlContent = str_replace("[riferimento]", $ruoloUtente, $htmlContent);
$htmlContent = str_replace("[riga_ordini]", $ordini, $htmlContent);
$htmlContent = str_replace("[riga_prenotazioni]", $prenotazioni, $htmlContent);

echo $htmlContent;
?>
