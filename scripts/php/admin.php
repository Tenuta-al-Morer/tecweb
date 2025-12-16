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
$viniArray = $db->getVini();
$ordiniArray = $db->getOrdini();
$db->closeConnection();

// 2) Costruisco le righe HTML
$vini = "";

foreach ($viniArray as $vino) {
    $vini .= "<tr>";
    $vini .= '<th scope="row">' . (int)$vino['id'] . "</th>";
    $vini .= '<td data-title="Nome Prodotto">' . htmlspecialchars($vino['nome']) . "</td>";
    $vini .= '<td data-title="Prezzo">' . htmlspecialchars($vino['prezzo']) . " €</td>";
    $vini .= '<td data-title="Quantità Richiesta">' . (int)$vino['quantita_stock'] . "</td>";
    $vini .= '<td data-title="Stato">' . htmlspecialchars($vino['stato']) . "</td>";
    $vini .= '<td class="td_richiesta_degustazione" data-title="Gestione richiesta"> 
                                <form action="#" method="POST" class="standard-form">
                                    <button type="submit" name="accetta" value="accetta" class="btn-secondary">Accetta</button>
                                    <button type="submit" name="rifiuta" value="rifiuta" class="btn-secondary">Rifiuta</button>
                                </form>
                            </td>';
    $vini .= "</tr>";

}

$ordini = "";

foreach ($ordiniArray as $ordine) {
    $ordini .= "<tr>";
    $ordini .= '<th scope="row">' . (int)$ordine['id'] . '</th>';
    $ordini .= '<td data-title="ID Utente">' . (int)$ordine['id_utente'] . '</td>';
    $ordini .= '<td data-title="Stato Ordine">' . htmlspecialchars($ordine['stato_ordine']) . '</td>';
    $ordini .= '<td data-title="Totale Prodotti">' . number_format($ordine['totale_prodotti'], 2) . ' €</td>';
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


// 3) Replace placeholders
$htmlContent = str_replace("[nome_utente]", $nomeUtente, $htmlContent);
$htmlContent = str_replace("[email_utente]", $emailUtente, $htmlContent);
$htmlContent = str_replace("[riferimento]", $ruoloUtente, $htmlContent);
$htmlContent = str_replace("[riga_ordini]", $ordini, $htmlContent);

echo $htmlContent;
?>
