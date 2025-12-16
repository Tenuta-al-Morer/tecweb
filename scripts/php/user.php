<?php
session_start();
require_once 'common.php';
require_once 'DBConnection.php'; 
use DB\DBConnection;

if (!isset($_SESSION['utente_id'])) { 
    header("location: login.php");
    exit();
}

$idUtente = $_SESSION['utente_id']; 
$ruoloUtente = $_SESSION['ruolo'];
$nomeUtenteSessione = isset($_SESSION['nome']) ? $_SESSION['nome'] : 'Utente';
$emailUtenteSessione = isset($_SESSION['utente']) ? $_SESSION['utente'] : '';

if ($ruoloUtente !== 'user' and $ruoloUtente !== 'admin' and $ruoloUtente !== 'amministratore') {
    header("location: 403.php");
    exit();
}

$db = new DBConnection();

// --- LOGICA ELIMINAZIONE ACCOUNT ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['azione_delete']) && $_POST['azione_delete'] == 'elimina_definitivamente') {
    // Verifica ulteriore se la checkbox è stata spuntata (anche se c'è required in HTML)
    if (isset($_POST['conferma_irreversibile'])) {
        if ($db->eliminaAccount($idUtente)) {
            // Logout forzato e redirect
            session_unset();
            session_destroy();
            header("location: home.php?msg=account_deleted");
            exit();
        } else {
            $msgDelete = '<div class="alert error alert-msg">Errore durante l\'eliminazione. Riprova più tardi.</div>';
        }
    }
}

// --- LOGICA CAMBIO PASSWORD ---
$msgPassword = ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['azione_pw']) && $_POST['azione_pw'] == 'cambia') {
    $vecchia = $_POST['vecchia_password'] ?? '';
    $nuova = $_POST['nuova_password'] ?? '';
    $ripeti = $_POST['ripeti_password'] ?? '';

    if ($nuova !== $ripeti) {
        $msgPassword = '<div class="alert error alert-msg">Le nuove password non coincidono.</div>';
    } elseif (strlen($nuova) < 8) {
        $msgPassword = '<div class="alert error alert-msg">La password deve essere di almeno 8 caratteri.</div>';
    } else {
        if ($db->cambiaPassword($idUtente, $vecchia, $nuova)) {
            $msgPassword = '<div class="alert success alert-msg">Password aggiornata con successo!</div>';
        } else {
            $msgPassword = '<div class="alert error alert-msg">La vecchia password non è corretta.</div>';
        }
    }

    // Script per riaprire la tab sicurezza
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
        'in_attesa' => 'In attesa', 
        'approvato' => 'Ordine Approvato',
        'annullato' => 'Ordine Annullato'
    ];
    $testo = $mappatura[$stato] ?? $stato;
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
<div class="page-intro">
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
    $tabellaOrdini = '<div class="alert-box"><p><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> Non hai ancora effettuato ordini. Visita la sezione <a href="vini.php">Vini!</a></p></div>';
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

// --- COSTRUZIONE DATI PERSONALI (Nuovo Layout + Danger Zone) ---
$inizialeNome = strtoupper(substr($infoUtente['nome'], 0, 1));
$inizialeCognome = strtoupper(substr($infoUtente['cognome'], 0, 1));

$datiPersonaliHTML = '
<div class="profile-layout">
    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar">' . $inizialeNome . $inizialeCognome . '</div>
            <div class="profile-title">
                <h3>' . htmlspecialchars($infoUtente['nome'] . ' ' . $infoUtente['cognome']) . '</h3>
                <span class="role-badge">' . htmlspecialchars($ruoloUtente) . '</span>
            </div>
        </div>
        <div class="profile-body">
            <div class="profile-info-item">
                <span class="info-label"><i class="fas fa-envelope"></i> Email</span>
                <span class="info-value">' . htmlspecialchars($infoUtente['email']) . '</span>
            </div>
            <div class="profile-info-item">
                <span class="info-label"><i class="fas fa-id-badge"></i> ID Utente</span>
                <span class="info-value">#' . $idUtente . '</span>
            </div>
        </div>
    </div>

    <div class="danger-zone">
        <h3><i class="fas fa-exclamation-triangle"></i> Zona Pericolosa</h3>
        <p>L\'eliminazione dell\'account è <strong>irreversibile</strong>. Perderai l\'accesso all\'area riservata.</p>
        <p class="small-text">Nota: I tuoi ordini già effettuati rimarranno nei nostri registri per fini fiscali e legali, ma non saranno più associati a questo account.</p>
        
        <form action="user.php" method="POST" class="delete-account-form">
            <input type="hidden" name="azione_delete" value="elimina_definitivamente">
            
            <div class="checkbox-wrapper-delete">
                <input type="checkbox" id="conferma_irreversibile" name="conferma_irreversibile" required>
                <label for="conferma_irreversibile">Confermo di voler eliminare definitivamente il mio account.</label>
            </div>

            <button type="submit" class="btn-danger-delete">
                Elimina il mio account
            </button>
        </form>
    </div>
</div>';

// --- COSTRUZIONE FORM PASSWORD ---
$formPasswordHTML = '
<div class="password-form-container">
    ' . $msgPassword . '
    
    <form action="user.php#sicurezza" method="POST" class="auth-form">
        <input type="hidden" name="azione_pw" value="cambia">
        
        <div class="form-group">
            <label for="vecchia_password">Vecchia Password</label>
            <div class="password-wrapper">
                <input type="password" id="vecchia_password" name="vecchia_password" required placeholder="Inserisci la password attuale">
                <button type="button" class="toggle-password" aria-pressed="false">
                    <i class="fas fa-eye" aria-hidden="true"></i>
                    <span class="visually-hidden">Mostra password</span>
                </button>
            </div>
        </div>

        <div class="form-group">
            <label for="nuova_password">Nuova Password</label>
            <div class="password-wrapper">
                <input type="password" id="nuova_password" name="nuova_password" required placeholder="Inserisci la nuova password">
                <button type="button" class="toggle-password" aria-pressed="false">
                    <i class="fas fa-eye" aria-hidden="true"></i>
                    <span class="visually-hidden">Mostra password</span>
                </button>
            </div>
        </div>

        <div class="form-group">
            <label for="ripeti_password">Ripeti Nuova Password</label>
            <div class="password-wrapper">
                <input type="password" id="ripeti_password" name="ripeti_password" required placeholder="Ripeti la nuova password">
                <button type="button" class="toggle-password" aria-pressed="false">
                    <i class="fas fa-eye" aria-hidden="true"></i>
                    <span class="visually-hidden">Mostra password</span>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-primary btn-submit">Aggiorna Password</button>
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