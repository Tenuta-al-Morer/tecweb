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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ordineId = isset($_POST['ordine_id']) ? (int)$_POST['ordine_id'] : 0;
    $azione   = $_POST['azione'] ?? '';

    if ($ordineId > 0 && in_array($azione, ['accetta', 'rifiuta'], true)) {

        $stato = ($azione === 'accetta') ? 'approvato' : 'annullato';

        $db = new DBConnection();
        $ok = $db->aggiornaStatoOrdine($ordineId, $stato);
        $db->closeConnection();

        // (debug veloce) se vuoi verificare che la query abbia modificato:
        // var_dump($ok); exit;

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // se arrivi qui, POST non valido
    header("location: 404.php");
    exit;
}





$htmlContent = caricaPagina('../../html/admin.html');
$emailUtente = htmlspecialchars($_SESSION['utente']);
$nomeUtente = htmlspecialchars($_SESSION['nome']);

// 1) Leggo vini dal DB
$db = new DBConnection();
$ordiniArray = $db->getOrdini();
$prenotazioniArray = $db->getPrenotazioni();
$messaggiArray = $db->getMessaggi();
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
                    <form action="" method="POST" class="standard-form">
                        <input type="hidden" name="ordine_id" value="' . (int)$ordine['id'] . '">
                        <button type="submit" name="azione" value="accetta" class="btn-secondary">Accetta</button>
                        <button type="submit" name="azione" value="rifiuta" class="btn-secondary">Rifiuta</button>
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
    $prenotazioni .= '<td data-title="Telefono">' . htmlspecialchars($prenotazione['prefisso']) . ' '. htmlspecialchars($prenotazione['telefono']) . '</td>';
    $prenotazioni .= '<td data-title="Data visita">' . htmlspecialchars($prenotazione['data_visita']) . '</td>';
    $prenotazioni .= '<td data-title="Numero persone">' . (int)$prenotazione['n_persone'] . '</td>';
    $prenotazioni .= '<td data-title="Data Invio">' . htmlspecialchars($prenotazione['data_invio']) . '</td>';
    $prenotazioni .= '<td class="td_richiesta_degustazione" data-title="Gestione richiesta"> 
                    <form action="#" method="POST" class="standard-form">
                        <button type="submit" name="accetta" value="accetta" class="btn-secondary">Accetta</button>
                        <button type="submit" name="rifiuta" value="rifiuta" class="btn-secondary">Rifiuta</button>
                    </form>
                </td>';
    $prenotazioni .= "</tr>";
}

$messaggi = "";
foreach ($messaggiArray as $messaggio) {
    $messaggi .= "<tr>";
    $messaggi .= '<th scope="row">' . (int)$messaggio['id'] . '</th>';
    $messaggi .= '<td data-title="Nome">' . htmlspecialchars($messaggio['nome']) . '</td>';
    $messaggi .= '<td data-title="Cognome">' . htmlspecialchars($messaggio['cognome']) . '</td>';
    $messaggi .= '<td data-title="Email">' . htmlspecialchars($messaggio['email']) . '</td>';
    $messaggi .= '<td data-title="Tipo supporto">' . htmlspecialchars($messaggio['tipo_supporto']) . '</td>';
    $messaggi .= '<td data-title="Telefono">' . htmlspecialchars($messaggio['prefisso']) .' '. htmlspecialchars($messaggio['telefono']) . '</td>';
    $messaggi .= '<td data-title="Messaggio">' . htmlspecialchars($messaggio['messaggio']) . '</td>';
    $messaggi .= '<td data-title="Data invio">' . htmlspecialchars($messaggio['data_invio']) . '</td>';
    $messaggi .= '<td class="td_richiesta_msg" data-title="Gestione richiesta"> 
                                <form action="#" method="POST" class="standard-form">
                                    <label for="richiesta1">Risposta<span aria-hidden="true">*</span></label>
                                    <input type="text" id="richiesta1" name="richiesta1" required placeholder="Rispondi alle necessità del cliente">
                                </form>
                                <form action="#" method="POST" class="standard-form">
                                    <button type="submit" name="msg_risposta" value="msg_risposta" class="btn-secondary">Invia</button>
                                </form>
                            </td>';
    $messaggi .= "</tr>";
}

// 3) Replace placeholders
$htmlContent = str_replace("[nome_utente]", $nomeUtente, $htmlContent);
$htmlContent = str_replace("[email_utente]", $emailUtente, $htmlContent);
$htmlContent = str_replace("[riferimento]", $ruoloUtente, $htmlContent);
$htmlContent = str_replace("[riga_ordini]", $ordini, $htmlContent);
$htmlContent = str_replace("[riga_prenotazioni]", $prenotazioni, $htmlContent);
$htmlContent = str_replace("[riga_messaggi]", $messaggi, $htmlContent);

echo $htmlContent;
?>
