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

// 1) Leggo vini dal DB
$db = new DBConnection();
$viniArray = $db->getVini();
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

// 3) Replace placeholders
$htmlContent = str_replace("[email_utente]", $emailUtente, $htmlContent);
$htmlContent = str_replace("[riferimento]", $ruoloUtente, $htmlContent);
$htmlContent = str_replace("[riga_vini]", $vini, $htmlContent);

echo $htmlContent;
?>
