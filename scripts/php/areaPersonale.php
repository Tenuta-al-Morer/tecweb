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

if ($ruoloUtente !== 'user' && $ruoloUtente !== 'admin' && $ruoloUtente !== 'staff') {
    header("location: 403.php");
    exit();
}

$db = new DBConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['azione_delete']) && $_POST['azione_delete'] == 'elimina_definitivamente') {
    if (isset($_POST['conferma_irreversibile'])) {
        if ($db->eliminaAccount($idUtente)) {
            session_unset();
            session_destroy();
            header("location: home.php?msg=account_deleted");
            exit();
        } else {
            $msgDelete = '<div class="alert error alert-msg">Errore durante l\'eliminazione. Riprova più tardi.</div>';
        }
    }
}

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

// LOGICA SEZIONE ATTIVA (GET parameter)
$sezioneAttiva = $_GET['sezione'] ?? 'dashboard';

// Classi CSS per le sezioni
$dashboardClass = ($sezioneAttiva === 'dashboard') ? 'content-section is-visible' : 'content-section is-hidden';
$ordiniClass = ($sezioneAttiva === 'ordini') ? 'content-section is-visible' : 'content-section is-hidden';
$datiClass = ($sezioneAttiva === 'dati') ? 'content-section is-visible' : 'content-section is-hidden';
$sicurezzaClass = ($sezioneAttiva === 'sicurezza') ? 'content-section is-visible' : 'content-section is-hidden';

// Classi CSS per la Navigazione
$navDashActive = ($sezioneAttiva === 'dashboard') ? 'is-active' : '';
$navOrdiniActive = ($sezioneAttiva === 'ordini') ? 'is-active' : '';
$navDatiActive = ($sezioneAttiva === 'dati') ? 'is-active' : '';
$navSicurezzaActive = ($sezioneAttiva === 'sicurezza') ? 'is-active' : '';

$ordini = $db->getOrdiniUtente($idUtente);
$stats = $db->getUserStats($idUtente);
$infoUtente = $db->getUserInfo($idUtente);

$db->closeConnection();

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

$dashboardHTML = '
<div class="page-intro">
    <h2>Bentornato, ' . htmlspecialchars($nomeUtenteSessione) . '!</h2>
    <p>Ecco il riepilogo della tua attività.</p>
</div>

<ul class="dashboard-grid">
    <li>
        <a href="?sezione=ordini" class="feature-card card-link border-gold">
            <div class="card-icon"><i class="fas fa-wallet" aria-hidden="true"></i></div>
            <h3>Il Tuo Valore</h3>
            <p class="stat-number">€ ' . number_format($stats['totale_speso'], 2, ',', '.') . '</p>
            <p class="stat-subtitle">su <strong>' . $stats['num_ordini'] . '</strong> ordini totali</p>
        </a>
    </li>
    <li>
        <a href="?sezione=ordini" class="feature-card card-link border-gold">
            <div class="card-icon"><i class="fas fa-shipping-fast" aria-hidden="true"></i></div>
            <h3>Ultimo Ordine</h3>
            ' . $ultimoOrdineHTML . '
        </a>
    </li>
    <li>
        <a href="?sezione=dati" class="feature-card card-link border-gold">
            <div class="card-icon"><i class="fas fa-user-circle" aria-hidden="true"></i></div>
            <h3>Il Tuo Profilo</h3>
            <p class="stat-number text-md">' . htmlspecialchars($infoUtente['nome'] ?? $nomeUtenteSessione) . '</p>
            <p class="profile-email">' . htmlspecialchars($infoUtente['email'] ?? $emailUtenteSessione) . '</p>
        </a>
    </li>
</ul>
';

$tabellaOrdini = '';
if (empty($ordini)) {
    $tabellaOrdini = '<div class="alert-box"><p><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> Non hai ancora effettuato ordini. Visita la sezione <a href="vini.php">Vini!</a></p></div>';
} else {
    $tabellaOrdini .= '<div class="table-container">
        <table class="table-data order-summary-table">
            <caption>Storico dei tuoi ordini</caption>
            <thead>
                <tr>
                    <th scope="col">N. Ordine</th>
                    <th scope="col">Data</th>
                    <th scope="col">Stato</th>
                    <th scope="col" class="td_richiesta_degustazione">Totale</th>
                    <th scope="col" class="td_richiesta_degustazione">Dettagli</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($ordini as $ordine) {
        $id_ordine = htmlspecialchars($ordine['id']);
        $tabellaOrdini .= '<tr data-order-id="' . $id_ordine . '">';
        $tabellaOrdini .= '<td data-title="N. Ordine">#' . $id_ordine . '</td>';
        $tabellaOrdini .= '<td data-title="Data">' . formatDate($ordine['data_creazione']) . '</td>';
        $tabellaOrdini .= '<td data-title="Stato">' . getStatusBadge($ordine['stato_ordine']) . '</td>';
        $tabellaOrdini .= '<td data-title="Totale" class="td_richiesta_degustazione">€ ' . number_format($ordine['totale_finale'], 2, ',', '.') . '</td>';
        
        // MODIFICA QUI: Bottone con fallback NOSCRIPT
        $tabellaOrdini .= '<td class="td_richiesta_degustazione">
            <noscript>
                <form method="get" action="#details-row-' . $id_ordine . '">
                    <input type="hidden" name="sezione" value="ordini">
                    <button type="submit" class="btn-secondary">Mostra Dettagli</button>
                </form>
            </noscript>
            <button type="button" class="btn-secondary toggle-details-btn" data-order-id="' . $id_ordine . '" aria-expanded="false" aria-controls="details-row-' . $id_ordine . '">Mostra <i class="fas fa-chevron-down" aria-hidden="true"></i></button>
        </td></tr>';
        
        $tabellaOrdini .= '<tr class="order-details-row is-hidden" id="details-row-' . $id_ordine . '"><td colspan="5" class="order-details-cell"><div class="details-content">';
        $tabellaOrdini .= '<div class="details-section"><h4>Prodotti Ordinati:</h4><ul class="details-products-list">';
        foreach ($ordine['elementi'] as $item) {
            $tabellaOrdini .= '<li><span>' . htmlspecialchars($item['quantita']) . 'x ' . htmlspecialchars($item['nome_vino_storico']) . '</span><span>€ ' . number_format($item['prezzo_acquisto'], 2, ',', '.') . ' (cad.)</span></li>';
        }
        $tabellaOrdini .= '</ul></div>'; 
        $tabellaOrdini .= '<div class="details-section"><h4>Riepilogo e Spedizione:</h4><p><strong>Indirizzo Spedizione:</strong> ' . nl2br(htmlspecialchars($ordine['indirizzo_spedizione'])) . '</p><div class="details-summary"><p>Totale Prodotti: € ' . number_format($ordine['totale_prodotti'], 2, ',', '.') . '</p><p>Costo Spedizione: € ' . number_format($ordine['costo_spedizione'], 2, ',', '.') . '</p><p><strong>Totale Finale: <span>€ ' . number_format($ordine['totale_finale'], 2, ',', '.') . '</span></strong></p></div></div></div></td></tr>';
    }
    $tabellaOrdini .= '</tbody></table></div>';
}

$inizialeNome = strtoupper(substr($infoUtente['nome'], 0, 1));
$inizialeCognome = strtoupper(substr($infoUtente['cognome'], 0, 1));

$datiPersonaliHTML = '
<div class="profile-layout">
    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar" aria-hidden="true">' . $inizialeNome . $inizialeCognome . '</div>
            <div class="profile-title">
                <h3>' . htmlspecialchars($infoUtente['nome'] . ' ' . $infoUtente['cognome']) . '</h3>
                <span class="role-badge">' . htmlspecialchars($ruoloUtente) . '</span>
            </div>
        </div>
        <div class="profile-body">
            <div class="profile-info-item">
                <span class="info-label"><i class="fas fa-envelope" aria-hidden="true"></i> Email</span>
                <span class="info-value">' . htmlspecialchars($infoUtente['email']) . '</span>
            </div>
            <div class="profile-info-item">
                <span class="info-label"><i class="fas fa-id-badge" aria-hidden="true"></i> ID Utente</span>
                <span class="info-value">#' . $idUtente . '</span>
            </div>
        </div>
    </div>

    <div class="danger-zone">
        <h3><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> Zona Pericolosa</h3>
        <p>L\'eliminazione dell\'account è <strong>irreversibile</strong>. Perderai l\'accesso all\'area riservata.</p>
        
        <form action="areaPersonale.php" method="POST" class="delete-account-form">
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

$formPasswordHTML = '
<div class="password-form-container">
    ' . $msgPassword . '
    
    <form action="areaPersonale.php?sezione=sicurezza" method="POST" class="auth-form">
        <input type="hidden" name="azione_pw" value="cambia">
        
        <div class="form-group">
            <label for="vecchia_password">Vecchia Password</label>
            <div class="password-wrapper">
                <input type="password" id="vecchia_password" name="vecchia_password" required placeholder="Inserisci la password attuale" autocomplete="current-password">
                <button type="button" class="toggle-password" aria-pressed="false">
                    <i class="fas fa-eye" aria-hidden="true"></i>
                    <span class="visually-hidden">Mostra password</span>
                </button>
            </div>
        </div>

        <div class="form-group">
            <label for="nuova_password">Nuova Password</label>
            <div class="password-wrapper">
                <input type="password" id="nuova_password" name="nuova_password" required placeholder="Inserisci la nuova password" autocomplete="new-password">
                <button type="button" class="toggle-password" aria-pressed="false">
                    <i class="fas fa-eye" aria-hidden="true"></i>
                    <span class="visually-hidden">Mostra password</span>
                </button>
            </div>
        </div>

        <div class="form-group">
            <label for="ripeti_password">Ripeti Nuova Password</label>
            <div class="password-wrapper">
                <input type="password" id="ripeti_password" name="ripeti_password" required placeholder="Ripeti la nuova password" autocomplete="new-password">
                <button type="button" class="toggle-password" aria-pressed="false">
                    <i class="fas fa-eye" aria-hidden="true"></i>
                    <span class="visually-hidden">Mostra password</span>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-primary btn-submit">Aggiorna Password</button>
    </form>
</div>';

$htmlContent = caricaPagina('../../html/areaPersonale.html'); 

$htmlContent = str_replace("[email_utente]", htmlspecialchars($infoUtente['email']), $htmlContent);
$htmlContent = str_replace("[riferimento]", $ruoloUtente, $htmlContent);

// Sostituzione Classi Navigazione
$htmlContent = str_replace("[NAV_ACTIVE_DASHBOARD]", $navDashActive, $htmlContent);
$htmlContent = str_replace("[NAV_ACTIVE_ORDINI]", $navOrdiniActive, $htmlContent);
$htmlContent = str_replace("[NAV_ACTIVE_DATI]", $navDatiActive, $htmlContent);
$htmlContent = str_replace("[NAV_ACTIVE_SICUREZZA]", $navSicurezzaActive, $htmlContent);

// Sostituzione Classi Sezioni (Visibilità)
$htmlContent = str_replace("[DASHBOARD_CLASS]", $dashboardClass, $htmlContent);
$htmlContent = str_replace("[ORDINI_CLASS]", $ordiniClass, $htmlContent);
$htmlContent = str_replace("[DATI_CLASS]", $datiClass, $htmlContent);
$htmlContent = str_replace("[SICUREZZA_CLASS]", $sicurezzaClass, $htmlContent);

$htmlContent = str_replace("[DASHBOARD_CONTENT]", $dashboardHTML, $htmlContent);
$htmlContent = str_replace("[TABELLA_ORDINI]", $tabellaOrdini, $htmlContent); 
$htmlContent = str_replace("[DATI_PERSONALI]", $datiPersonaliHTML, $htmlContent); 
$htmlContent = str_replace("[FORM_PASSWORD]", $formPasswordHTML, $htmlContent);

echo $htmlContent;
?>