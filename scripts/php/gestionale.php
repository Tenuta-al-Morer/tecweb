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

if ($ruoloUtente !== 'admin' && $ruoloUtente !== 'staff') {
    header("location: 403.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $azione = $_POST['azione'] ?? '';

    // === MESSAGGI ===
    if ($azione === 'msg_risposta') {
        $messaggioId = isset($_POST['messaggio_id']) ? (int)$_POST['messaggio_id'] : 0;
        $risposta    = trim($_POST['richiesta1'] ?? '');

        if ($messaggioId > 0 && $risposta !== '') {
            $db = new DBConnection();
            $ok = $db->archiviaMessaggio($messaggioId, $risposta);
            $db->closeConnection();

            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        header("location: 404.php");
        exit;
    }

    // === ORDINI ===
    $ordineId = isset($_POST['ordine_id']) ? (int)$_POST['ordine_id'] : 0;
    if ($ordineId > 0 && in_array($azione, ['accetta', 'rifiuta'], true)) {
        $stato = ($azione === 'accetta') ? 'approvato' : 'annullato';

        $db = new DBConnection();
        $ok = $db->aggiornaStatoOrdine($ordineId, $stato);
        $db->closeConnection();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // === PRENOTAZIONI ===
    $prenotazioneId = isset($_POST['prenotazione_id']) ? (int)$_POST['prenotazione_id'] : 0;
    if ($prenotazioneId > 0 && in_array($azione, ['accetta', 'rifiuta'], true)) {
        $stato = ($azione === 'accetta') ? 'approvato' : 'annullato';

        $db = new DBConnection();
        $ok = $db->aggiornaStatoPrenotazione($prenotazioneId, $stato);
        $db->closeConnection();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // POST non valido
    header("location: 404.php");
    exit;
}


$htmlContent = caricaPagina('../../html/gestionale.html');
$emailUtente = htmlspecialchars($_SESSION['utente']);
$nomeUtente = htmlspecialchars($_SESSION['nome']);

// 1) Lettura da DB
$db = new DBConnection();
$ordiniArray = $db->getOrdini();
$prenotazioniArray = $db->getPrenotazioni();
$prenotazioniArchivioArray = $db->getPrenotazioniArchivio();
$messaggiArray = $db->getMessaggi();
$messaggiArchivioArray = $db->getMessaggiArchivio();
$db->closeConnection();

// 2) Costruzione righe tabelle

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
    $prenotazioni .= '<td data-title="Tipo Degustazione">' . htmlspecialchars($prenotazione['tipo_degustazione']) . '</td>';
    $prenotazioni .= '<td data-title="Telefono">' . htmlspecialchars($prenotazione['prefisso']) . ' '. htmlspecialchars($prenotazione['telefono']) . '</td>';
    $prenotazioni .= '<td data-title="Data visita">' . htmlspecialchars($prenotazione['data_visita']) . '</td>';
    $prenotazioni .= '<td data-title="Numero persone">' . (int)$prenotazione['n_persone'] . '</td>';
    $prenotazioni .= '<td data-title="Data Invio">' . htmlspecialchars($prenotazione['data_invio']) . '</td>';
    $prenotazioni .= '<td class="td_richiesta_degustazione" data-title="Gestione richiesta"> 
                        <form action="" method="POST" class="standard-form">
                            <input type="hidden" name="prenotazione_id" value="' . (int)$prenotazione['id'] . '">
                            <button type="submit" name="azione" value="accetta" class="btn-secondary">Accetta</button>
                            <button type="submit" name="azione" value="rifiuta" class="btn-secondary">Rifiuta</button>
                        </form>
                    </td>';
    $prenotazioni .= "</tr>";
}

$prenotazioniArchivio = "";
foreach ($prenotazioniArchivioArray as $prenotazione) {
    $prenotazioniArchivio .= "<tr>";
    $prenotazioniArchivio .= '<th scope="row">' . (int)$prenotazione['id'] . '</th>';
    $prenotazioniArchivio .= '<td data-title="Nome">' . htmlspecialchars($prenotazione['nome']) . '</td>';
    $prenotazioniArchivio .= '<td data-title="Cognome">' . htmlspecialchars($prenotazione['cognome']) . '</td>';
    $prenotazioniArchivio .= '<td data-title="Email">' . htmlspecialchars($prenotazione['email']) . '</td>';
    $prenotazioniArchivio .= '<td data-title="Tipo Degustazione">' . htmlspecialchars($prenotazione['tipo_degustazione']) . '</td>';
    $prenotazioniArchivio .= '<td data-title="Telefono">' . htmlspecialchars($prenotazione['prefisso']) . ' '. htmlspecialchars($prenotazione['telefono']) . '</td>';
    $prenotazioniArchivio .= '<td data-title="Data visita">' . htmlspecialchars($prenotazione['data_visita']) . '</td>';
    $prenotazioniArchivio .= '<td data-title="Numero persone">' . (int)$prenotazione['n_persone'] . '</td>';
    $prenotazioniArchivio .= '<td data-title="Data Invio">' . htmlspecialchars($prenotazione['data_invio']) . '</td>';
    $prenotazioniArchivio .= '<td data-title="Stato">' . htmlspecialchars($prenotazione['stato']) . '</td>';
    $prenotazioniArchivio .= "</tr>";
}


$messaggi = "";
foreach ($messaggiArray as $messaggio) {
    $idMsg = (int)$messaggio['id'];

    $messaggi .= "<tr>";
    $messaggi .= '<th scope="row">' . $idMsg . '</th>';
    $messaggi .= '<td data-title="Nome">' . htmlspecialchars($messaggio['nome']) . '</td>';
    $messaggi .= '<td data-title="Cognome">' . htmlspecialchars($messaggio['cognome']) . '</td>';
    $messaggi .= '<td data-title="Email">' . htmlspecialchars($messaggio['email']) . '</td>';
    $messaggi .= '<td data-title="Tipo supporto">' . htmlspecialchars($messaggio['tipo_supporto']) . '</td>';
    $messaggi .= '<td data-title="Telefono">' . htmlspecialchars($messaggio['prefisso']) . ' ' . htmlspecialchars($messaggio['telefono']) . '</td>';
    $messaggi .= '<td data-title="Messaggio">' . htmlspecialchars($messaggio['messaggio']) . '</td>';
    $messaggi .= '<td data-title="Data invio">' . htmlspecialchars($messaggio['data_invio']) . '</td>';

    $messaggi .= '<td class="td_richiesta_msg" data-title="Gestione richiesta">
                    <form id="form_msg_' . $idMsg . '" action="" method="POST" class="standard-form">
                        <input type="hidden" name="messaggio_id" value="' . $idMsg . '">
                        <input type="hidden" name="azione" value="msg_risposta">

                        <label for="richiesta1_' . $idMsg . '">Risposta<span aria-hidden="true">*</span></label>
                        <input type="text" id="richiesta1_' . $idMsg . '" name="richiesta1" required
                            placeholder="Rispondi alle necessità del cliente">
                    </form>
                    
                    <button type="submit" form="form_msg_' . $idMsg . '" class="btn-secondary">Invia</button>
                </td>';
    $messaggi .= "</tr>";
}

$messaggiArchivio = "";
foreach ($messaggiArchivioArray as $messaggio) {
    $idMsg = (int)$messaggio['id'];
    $messaggiArchivio .= "<tr>";
    $messaggiArchivio .= '<th scope="row">' . $idMsg . '</th>';
    $messaggiArchivio .= '<td data-title="Nome">' . htmlspecialchars($messaggio['nome']) . '</td>';
    $messaggiArchivio .= '<td data-title="Cognome">' . htmlspecialchars($messaggio['cognome']) . '</td>';
    $messaggiArchivio .= '<td data-title="Email">' . htmlspecialchars($messaggio['email']) . '</td>';
    $messaggiArchivio .= '<td data-title="Tipo supporto">' . htmlspecialchars($messaggio['tipo_supporto']) . '</td>';
    $messaggiArchivio .= '<td data-title="Telefono">' . htmlspecialchars($messaggio['prefisso']) . ' ' . htmlspecialchars($messaggio['telefono']) . '</td>';
    $messaggiArchivio .= '<td data-title="Messaggio">' . htmlspecialchars($messaggio['messaggio']) . '</td>';
    $messaggiArchivio .= '<td data-title="Data invio">' . htmlspecialchars($messaggio['data_invio']) . '</td>';
    $messaggiArchivio .= '<td data-title="Risposta">' . htmlspecialchars($messaggio['risposta']) . '</td>';
    $messaggiArchivio .= "</tr>";
}


// 3) Replace placeholders
$htmlContent = str_replace("[nome_utente]", $nomeUtente, $htmlContent);
$htmlContent = str_replace("[email_utente]", $emailUtente, $htmlContent);
$htmlContent = str_replace("[riferimento]", $ruoloUtente, $htmlContent);
$htmlContent = str_replace("[riga_ordini]", $ordini, $htmlContent);
$htmlContent = str_replace("[riga_prenotazioni]", $prenotazioni, $htmlContent);
$htmlContent = str_replace("[riga_prenotazioni_archivio]", $prenotazioniArchivio, $htmlContent);
$htmlContent = str_replace("[riga_messaggi]", $messaggi, $htmlContent);
$htmlContent = str_replace("[riga_messaggi_archivio]", $messaggiArchivio, $htmlContent);

echo $htmlContent;
?>
