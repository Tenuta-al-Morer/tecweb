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

// LOGICA SEZIONE ATTIVA
$sezioneAttiva = $_GET['sezione'] ?? 'ordini';

// Classi CSS per le sezioni
$ordiniClass = ($sezioneAttiva === 'ordini') ? 'content-section is-visible' : 'content-section is-hidden';
$esperienzeClass = ($sezioneAttiva === 'esperienze') ? 'content-section is-visible' : 'content-section is-hidden';
$messaggiClass = ($sezioneAttiva === 'messaggi') ? 'content-section is-visible' : 'content-section is-hidden';

// Classi CSS per la Navigazione
$navOrdiniActive = ($sezioneAttiva === 'ordini') ? 'is-active' : '';
$navEsperienzeActive = ($sezioneAttiva === 'esperienze') ? 'is-active' : '';
$navMessaggiActive = ($sezioneAttiva === 'messaggi') ? 'is-active' : '';

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

            header("Location: " . $_SERVER['PHP_SELF'] . "?sezione=messaggi");
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

        header("Location: " . $_SERVER['PHP_SELF'] . "?sezione=ordini");
        exit;
    }

    // === PRENOTAZIONI ===
    $prenotazioneId = isset($_POST['prenotazione_id']) ? (int)$_POST['prenotazione_id'] : 0;
    if ($prenotazioneId > 0 && in_array($azione, ['accetta', 'rifiuta'], true)) {
        $stato = ($azione === 'accetta') ? 'approvato' : 'annullato';

        $db = new DBConnection();
        $ok = $db->aggiornaStatoPrenotazione($prenotazioneId, $stato);
        $db->closeConnection();

        header("Location: " . $_SERVER['PHP_SELF'] . "?sezione=esperienze");
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
$ordiniArchivioArray = $db->getOrdiniArchivio();
$prenotazioniArray = $db->getPrenotazioni();
$prenotazioniArchivioArray = $db->getPrenotazioniArchivio();
$messaggiArray = $db->getMessaggi();
$messaggiArchivioArray = $db->getMessaggiArchivio();
$db->closeConnection();

// 2) Costruzione righe tabelle

$ordini = "";

foreach ($ordiniArray as $ordine) {
    $ordineId = (int)$ordine['id'];
    $nomeCliente = trim(($ordine['cognome'] ?? '') . ' ' . ($ordine['nome'] ?? ''));
    if ($nomeCliente === '') {
        $nomeCliente = 'Utente #' . (int)$ordine['id_utente'];
    }

    $ordini .= '<tr data-order-id="' . $ordineId . '">';
    $ordini .= '<th scope="row">' . $ordineId . '</th>';
    $ordini .= '<td data-title="Cliente">' . htmlspecialchars($nomeCliente) . '</td>';
    $ordini .= '<td data-title="Totale Finale">' . number_format($ordine['totale_finale'], 2) . ' EUR</td>';
    $ordini .= '<td data-title="Data Creazione">' . htmlspecialchars($ordine['data_creazione']) . '</td>';
    
    // MODIFICA QUI: Rimosso <noscript>, aggiunto doppio bottone gestito via CSS
    $ordini .= '<td class="td_richiesta_degustazione" data-title="Dettagli">
                    <a href="?sezione=ordini#details-row-' . $ordineId . '" class="btn-secondary btn-fallback">Mostra Dettagli</a>
                    <button type="button" class="btn-secondary toggle-details-btn" data-order-id="' . $ordineId . '" aria-expanded="false" aria-controls="details-row-' . $ordineId . '">
                        Mostra <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                </td>';
                
    $ordini .= '<td class="td_richiesta_degustazione" data-title="Gestione richiesta"> 
                    <form action="" method="POST" class="standard-form">
                        <input type="hidden" name="ordine_id" value="' . $ordineId . '">
                        <button type="submit" name="azione" value="accetta" class="btn-secondary btn-accept">Accetta</button>
                        <button type="submit" name="azione" value="rifiuta" class="btn-secondary btn-reject">Rifiuta</button>
                    </form>
                </td>';
    $ordini .= '</tr>';

    $ordini .= '<tr class="order-details-row is-hidden" id="details-row-' . $ordineId . '"><td colspan="6" class="order-details-cell"><div class="details-content">';
    $ordini .= '<div class="details-section"><h4>Prodotti Ordinati:</h4><ul class="details-products-list">';
    if (!empty($ordine['elementi'])) {
        foreach ($ordine['elementi'] as $item) {
            $ordini .= '<li><span>' . (int)$item['quantita'] . 'x ' . htmlspecialchars($item['nome_vino_storico']) . '</span><span>' . number_format($item['prezzo_acquisto'], 2) . ' EUR</span></li>';
        }
    } else {
        $ordini .= '<li><span>Nessun prodotto associato.</span><span>0.00 EUR</span></li>';
    }
    $ordini .= '</ul></div>';
    $ordini .= '<div class="details-section"><h4>Riepilogo e Spedizione:</h4><p><strong>ID Utente:</strong> #' . (int)$ordine['id_utente'] . '</p><p><strong>Indirizzo Spedizione:</strong> ' . nl2br(htmlspecialchars($ordine['indirizzo_spedizione'])) . '</p><div class="details-summary details-summary-left"><h4>Prezzi:</h4><p>Prezzo Prodotti: ' . number_format($ordine['totale_prodotti'], 2) . ' EUR</p><p>Costo Spedizione: ' . number_format($ordine['costo_spedizione'], 2) . ' EUR</p><p><strong>Totale Finale:</strong> <span>' . number_format($ordine['totale_finale'], 2) . ' EUR</span></p></div></div></div></td></tr>';
}

$ordiniArchivio = "";

foreach ($ordiniArchivioArray as $ordine) {
    $ordineId = (int)$ordine['id'];
    $nomeCliente = trim(($ordine['cognome'] ?? '') . ' ' . ($ordine['nome'] ?? ''));
    if ($nomeCliente === '') {
        $nomeCliente = 'Utente #' . (int)$ordine['id_utente'];
    }

    $ordiniArchivio .= '<tr data-order-id="' . $ordineId . '">';
    $ordiniArchivio .= '<th scope="row">' . $ordineId . '</th>';
    $ordiniArchivio .= '<td data-title="Cliente">' . htmlspecialchars($nomeCliente) . '</td>';
    $ordiniArchivio .= '<td data-title="Totale Finale">' . number_format($ordine['totale_finale'], 2) . ' EUR</td>';
    $ordiniArchivio .= '<td data-title="Data Creazione">' . htmlspecialchars($ordine['data_creazione']) . '</td>';
    $ordiniArchivio .= '<td data-title="Stato">' . htmlspecialchars($ordine['stato_ordine']) . '</td>';
    
    // MODIFICA QUI
    $ordiniArchivio .= '<td class="td_richiesta_degustazione" data-title="Dettagli">
                            <a href="?sezione=ordini#details-row-' . $ordineId . '" class="btn-secondary btn-fallback">Mostra Dettagli</a>
                            <button type="button" class="btn-secondary toggle-details-btn" data-order-id="' . $ordineId . '" aria-expanded="false" aria-controls="details-row-' . $ordineId . '">
                                Mostra <i class="fas fa-chevron-down" aria-hidden="true"></i>
                            </button>
                        </td>';
    $ordiniArchivio .= '</tr>';

    $ordiniArchivio .= '<tr class="order-details-row is-hidden" id="details-row-' . $ordineId . '"><td colspan="6" class="order-details-cell"><div class="details-content">';
    $ordiniArchivio .= '<div class="details-section"><h4>Prodotti Ordinati:</h4><ul class="details-products-list">';
    if (!empty($ordine['elementi'])) {
        foreach ($ordine['elementi'] as $item) {
            $ordiniArchivio .= '<li><span>' . (int)$item['quantita'] . 'x ' . htmlspecialchars($item['nome_vino_storico']) . '</span><span>' . number_format($item['prezzo_acquisto'], 2) . ' EUR</span></li>';
        }
    } else {
        $ordiniArchivio .= '<li><span>Nessun prodotto associato.</span><span>0.00 EUR</span></li>';
    }
    $ordiniArchivio .= '</ul></div>';
    $ordiniArchivio .= '<div class="details-section"><h4>Riepilogo e Spedizione:</h4><p><strong>ID Utente:</strong> #' . (int)$ordine['id_utente'] . '</p><p><strong>Indirizzo Spedizione:</strong> ' . nl2br(htmlspecialchars($ordine['indirizzo_spedizione'])) . '</p><div class="details-summary details-summary-left"><h4>Prezzi:</h4><p>Prezzo Prodotti: ' . number_format($ordine['totale_prodotti'], 2) . ' EUR</p><p>Costo Spedizione: ' . number_format($ordine['costo_spedizione'], 2) . ' EUR</p><p><strong>Totale Finale:</strong> <span>' . number_format($ordine['totale_finale'], 2) . ' EUR</span></p></div></div></div></td></tr>';
}


$prenotazioni = "";
foreach ($prenotazioniArray as $prenotazione) {
    $prenotazioni .= "<tr>";
    $prenotazioni .= '<th scope="row">' . (int)$prenotazione['id'] . '</th>';
    $prenotazioni .= '<td data-title="Nome">' . htmlspecialchars($prenotazione['nome']) . '</td>';
    $prenotazioni .= '<td data-title="Cognome">' . htmlspecialchars($prenotazione['cognome']) . '</td>';
    $prenotazioni .= '<td data-title="Email">' . htmlspecialchars($prenotazione['email']) . '</td>';
    $prenotazioni .= '<td data-title="Tipo Degustazione">' . htmlspecialchars($prenotazione['tipo_degustazione']) . '</td>';
    $prenotazioni .= '<td data-title="Telefono">' . htmlspecialchars($prenotazione['prefisso']) . ' ' . htmlspecialchars($prenotazione['telefono']) . '</td>';
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
    $prenotazioniArchivio .= '<td data-title="Telefono">' . htmlspecialchars($prenotazione['prefisso']) . ' ' . htmlspecialchars($prenotazione['telefono']) . '</td>';
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
                            placeholder="Rispondi alle necessita del cliente">
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

// Nav Active
$htmlContent = str_replace("[NAV_ACTIVE_ORDINI]", $navOrdiniActive, $htmlContent);
$htmlContent = str_replace("[NAV_ACTIVE_ESPERIENZE]", $navEsperienzeActive, $htmlContent);
$htmlContent = str_replace("[NAV_ACTIVE_MESSAGGI]", $navMessaggiActive, $htmlContent);

// Section Visibility
$htmlContent = str_replace("[ORDINI_CLASS]", $ordiniClass, $htmlContent);
$htmlContent = str_replace("[ESPERIENZE_CLASS]", $esperienzeClass, $htmlContent);
$htmlContent = str_replace("[MESSAGGI_CLASS]", $messaggiClass, $htmlContent);

// Rows
$htmlContent = str_replace("[riga_ordini]", $ordini, $htmlContent);
$htmlContent = str_replace("[riga_ordini_archivio]", $ordiniArchivio, $htmlContent);
$htmlContent = str_replace("[riga_prenotazioni]", $prenotazioni, $htmlContent);
$htmlContent = str_replace("[riga_prenotazioni_archivio]", $prenotazioniArchivio, $htmlContent);
$htmlContent = str_replace("[riga_messaggi]", $messaggi, $htmlContent);
$htmlContent = str_replace("[riga_messaggi_archivio]", $messaggiArchivio, $htmlContent);

// Pulizia placeholder menu
$htmlContent = str_replace("[cart_icon_link]", "", $htmlContent);
$htmlContent = str_replace("[user_area_link]", "", $htmlContent);

echo $htmlContent;
?>
