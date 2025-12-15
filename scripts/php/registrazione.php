<?php

session_start();
require_once 'common.php';
require_once 'DBConnection.php'; 
use DB\DBConnection;

if (!isset($_SESSION['utente_id'])) {
    $htmlContent = file_get_contents('../../html/registrazione.html');

    // pulizia placeholders (o ripristino valori se stai facendo POST)
    $htmlContent = str_replace("[err]", "", $htmlContent);
    $htmlContent = str_replace("[nome]", htmlspecialchars($_POST['nome'] ?? ''), $htmlContent);
    $htmlContent = str_replace("[cognome]", htmlspecialchars($_POST['cognome'] ?? ''), $htmlContent);
    $htmlContent = str_replace("[email]", htmlspecialchars($_POST['email'] ?? ''), $htmlContent);

    echo $htmlContent;
    exit();
}

$idUtente = $_SESSION['utente_id']; 
$ruoloUtente = $_SESSION['ruolo'];
$nomeUtenteSessione = isset($_SESSION['nome']) ? $_SESSION['nome'] : 'Utente';
$emailUtenteSessione = isset($_SESSION['utente']) ? $_SESSION['utente'] : '';

if ($ruoloUtente !== 'user') {
    header("location: 403.php");
    exit();
}


$db = new DBConnection();

// --- LOGICA CAMBIO PASSWORD ---
$msgPassword = ''; // Variabile per messaggi successo/errore

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['azione_pw']) && $_POST['azione_pw'] == 'cambia') {
    $vecchia = $_POST['vecchia_password'] ?? '';
    $nuova = $_POST['nuova_password'] ?? '';
    $ripeti = $_POST['ripeti_password'] ?? '';

    // FIX VALIDAZIONE: Allineato a 8 caratteri come in registrazione
    if ($nuova !== $ripeti) {
        $msgPassword = '<div class="alert error" style="margin-bottom:1rem;">Le nuove password non coincidono.</div>';
    } elseif (strlen($nuova) < 8) { 
        $msgPassword = '<div class="alert error" style="margin-bottom:1rem;">La password deve essere di almeno 8 caratteri.</div>';
    } else {
        if ($db->cambiaPassword($idUtente, $vecchia, $nuova)) {
            $msgPassword = '<div class="alert success" style="margin-bottom:1rem;">Password aggiornata con successo!</div>';
        } else {
            $msgPassword = '<div class="alert error" style="margin-bottom:1rem;">La vecchia password non è corretta.</div>';
        }
    }

    // FIX REDIRECT TAB: Script migliorato per garantire il click dopo il caricamento di script.js
    if (!empty($msgPassword)) {
        $msgPassword .= "
        <script>
            window.addEventListener('load', function() {
                setTimeout(function() {
                    var tabBtn = document.querySelector('[data-section=sicurezza]');
                    if(tabBtn) { tabBtn.click(); }
                }, 100);
            });
        </script>";
    }
}

// 1. Recupero Ordini
$ordini = $db->getOrdiniUtente($idUtente);
// 2. Recupero Statistiche
$stats = $db->getUserStats($idUtente);
// 3. Recupero Dati Personali Completi
$infoUtente = $db->getUserInfo($idUtente);

$db->closeConnection();

// --- HELPERS ---
function formatDate($dateString) {
    return (new DateTime($dateString))->format('d/m/Y H:i');
}

function getStatusBadge($stato) {
    $mappatura = [
        'in_attesa' => 'In attesa di pagamento',
        'pagato' => 'Pagato/In lavorazione',
        'in_preparazione' => 'In Preparazione',
        'spedito' => 'Spedito',
        'consegnato' => 'Consegnato',
        'annullato' => 'Annullato'
    ];
    $testo = $mappatura[$stato] ?? 'Sconosciuto';
    return '<span class="order-status-badge status-' . $stato . '">' . htmlspecialchars($testo) . '</span>';
}

// --- COSTRUZIONE DASHBOARD HTML ---

// A. Ultimo Ordine
$ultimoOrdineHTML = '<p class="text-muted">Nessun ordine recente.</p>';

if (!empty($ordini)) {
    $last = $ordini[0];
    $ultimoOrdineHTML = '
        <div class="last-order-info">
            ' . getStatusBadge($last['stato_ordine']) . '
            <p class="text-muted">
                del ' . (new DateTime($last['data_creazione']))->format('d/m/Y') . '
            </p>
        </div>
    ';
}

// B. Cards Dashboard
$dashboardHTML = '
<div class="page-intro" style="margin-bottom: 2rem;">
    <h2>Bentornato, ' . htmlspecialchars($nomeUtenteSessione) . '!</h2>
    <p>Ecco il riepilogo della tua attività.</p>
</div>

<div class="dashboard-grid">
    
    <a href="#ordini" onclick="document.querySelector(\'[data-section=ordini]\').click()" class="feature-card card-link border-gold">
        <div class="card-icon"><i class="fas fa-wallet"></i></div>
        <h3>Il Tuo Valore</h3>
        <p class="stat-number">€ ' . number_format($stats['totale_speso'], 2, ',', '.') . '</p>
        <p class="stat-subtitle">su <strong>' . $stats['num_ordini'] . '</strong> ordini totali</p>
    </a>

    <a href="#ordini" onclick="document.querySelector(\'[data-section=ordini]\').click()" class="feature-card card-link border-gold">
        <div class="card-icon"><i class="fas fa-shipping-fast"></i></div>
        <h3>Ultimo Ordine</h3>
        ' . $ultimoOrdineHTML . '
    </a>

    <a href="#dati" onclick="document.querySelector(\'[data-section=dati]\').click()" class="feature-card card-link border-gold">
        <div class="card-icon"><i class="fas fa-user-circle"></i></div>
        <h3>Il Tuo Profilo</h3>
        <p class="stat-number text-md">' . htmlspecialchars($infoUtente['nome'] ?? $nomeUtenteSessione) . '</p>
        <p class="profile-email">' . htmlspecialchars($infoUtente['email'] ?? $emailUtenteSessione) . '</p>
    </a>

</div>
';

// --- COSTRUZIONE TABELLA ORDINI ---
$tabellaOrdini = '';
if (empty($ordini)) {
    $tabellaOrdini = '<div class="alert-box"><p><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> Non hai ancora effettuato ordini. Visita la sezione <a href="vini.php">Vini</a>!</p></div>';
} else {
    $tabellaOrdini .= '<div class="table-container"><table class="table-data order-summary-table"><caption>Storico dei tuoi ordini</caption><thead><tr><th>N. Ordine</th><th>Data</th><th>Stato</th><th class="td_richiesta_degustazione">Totale</th><th class="td_richiesta_degustazione">Dettagli</th></tr></thead><tbody>';

    foreach ($ordini as $ordine) {
        $id_ordine = htmlspecialchars($ordine['id']);
        $tabellaOrdini .= '<tr data-order-id="' . $id_ordine . '">';
        $tabellaOrdini .= '<td data-title="N. Ordine">#' . $id_ordine . '</td>';
        $tabellaOrdini .= '<td data-title="Data">' . formatDate($ordine['data_creazione']) . '</td>';
        $tabellaOrdini .= '<td data-title="Stato">' . getStatusBadge($ordine['stato_ordine']) . '</td>';
        $tabellaOrdini .= '<td data-title="Totale" class="td_richiesta_degustazione">€ ' . number_format($ordine['totale_finale'], 2, ',', '.') . '</td>';
        $tabellaOrdini .= '<td class="td_richiesta_degustazione"><button type="button" class="btn-secondary toggle-details-btn" data-order-id="' . $id_ordine . '" aria-expanded="false" aria-controls="details-row-' . $id_ordine . '">Mostra <i class="fas fa-chevron-down" aria-hidden="true"></i></button></td></tr>';
        
        $tabellaOrdini .= '<tr class="order-details-row is-hidden" id="details-row-' . $id_ordine . '"><td colspan="5" class="order-details-cell"><div class="details-content">';
        $tabellaOrdini .= '<div class="details-section"><h4>Prodotti Ordinati:</h4><ul class="details-products-list">';
        foreach ($ordine['elementi'] as $item) {
            $tabellaOrdini .= '<li><span>' . htmlspecialchars($item['quantita']) . 'x ' . htmlspecialchars($item['nome_vino_storico']) . '</span><span>€ ' . number_format($item['prezzo_acquisto'], 2, ',', '.') . ' (cad.)</span></li>';
        }
        $tabellaOrdini .= '</ul></div>'; 
        $tabellaOrdini .= '<div class="details-section"><h4>Riepilogo e Spedizione:</h4><p><strong>Pagamento:</strong> ' . htmlspecialchars($ordine['metodo_pagamento']) . '</p><p><strong>Indirizzo Spedizione:</strong> ' . nl2br(htmlspecialchars($ordine['indirizzo_spedizione'])) . '</p><div class="details-summary"><p>Totale Prodotti: € ' . number_format($ordine['totale_prodotti'], 2, ',', '.') . '</p><p>Costo Spedizione: € ' . number_format($ordine['costo_spedizione'], 2, ',', '.') . '</p><p><strong>Totale Finale: <span>€ ' . number_format($ordine['totale_finale'], 2, ',', '.') . '</span></strong></p></div></div></div></td></tr>';
    }
    $tabellaOrdini .= '</tbody></table></div>';
}

// --- COSTRUZIONE DATI PERSONALI ---
$datiPersonaliHTML = '
<div class="info-list" style="margin-top: 1.5rem;">
    <p>
        <i class="fas fa-user" aria-hidden="true"></i>
        <strong>Nome:</strong> &nbsp; ' . htmlspecialchars($infoUtente['nome']) . '
    </p>
    <p>
        <i class="fas fa-user" aria-hidden="true"></i>
        <strong>Cognome:</strong> &nbsp; ' . htmlspecialchars($infoUtente['cognome']) . '
    </p>
    <p>
        <i class="fas fa-envelope" aria-hidden="true"></i>
        <strong>Email:</strong> &nbsp; ' . htmlspecialchars($infoUtente['email']) . '
    </p>
    <p>
        <i class="fas fa-id-badge" aria-hidden="true"></i>
        <strong>Ruolo:</strong> &nbsp; <span style="text-transform: capitalize;">' . htmlspecialchars($ruoloUtente) . '</span>
    </p>
</div>';

// --- COSTRUZIONE FORM PASSWORD ---
// Aggiunto action="#sicurezza" per mantenere lo scroll e prevenire il salto a inizio pagina
$formPasswordHTML = '
<div style="max-width: 500px; margin-top: 1rem;">
    ' . $msgPassword . '
    
    <form action="user.php#sicurezza" method="POST" class="auth-form" style="text-align: left;">
        <input type="hidden" name="azione_pw" value="cambia">
        
        <div class="form-group">
            <label for="vecchia_password">Vecchia Password</label>
            <input type="password" id="vecchia_password" name="vecchia_password" required placeholder="Inserisci la password attuale">
        </div>

        <div class="form-group">
            <label for="nuova_password">Nuova Password</label>
            <input type="password" id="nuova_password" name="nuova_password" required placeholder="Inserisci la nuova password">
        </div>

        <div class="form-group">
            <label for="ripeti_password">Ripeti Nuova Password</label>
            <input type="password" id="ripeti_password" name="ripeti_password" required placeholder="Ripeti la nuova password">
        </div>

        <button type="submit" class="btn-primary" style="margin-top: 1rem;">Aggiorna Password</button>
    </form>
</div>';

// --- OUTPUT FINALE ---
$htmlContent = caricaPagina('../../html/user.html');

$htmlContent = str_replace("[email_utente]", htmlspecialchars($infoUtente['email']), $htmlContent);
$htmlContent = str_replace("[riferimento]", $ruoloUtente, $htmlContent);

// Sostituzione Dashboard
$dashboardDefaultContent = '<h2>Riepilogo Attività</h2>
                    <div class="alert-box">
                        <p><i class="fas fa-info-circle" aria-hidden="true"></i> Qui troverai una panoramica dei tuoi ultimi ordini, prodotti preferiti e dati principali. (In sviluppo...)</p>
                    </div>';

$htmlContent = str_replace($dashboardDefaultContent, $dashboardHTML, $htmlContent);
$htmlContent = str_replace("[TABELLA_ORDINI]", $tabellaOrdini, $htmlContent); 
$htmlContent = str_replace("[DATI_PERSONALI]", $datiPersonaliHTML, $htmlContent); 
$htmlContent = str_replace("[FORM_PASSWORD]", $formPasswordHTML, $htmlContent);

echo $htmlContent;
?>