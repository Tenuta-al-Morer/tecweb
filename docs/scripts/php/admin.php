<?php
session_start();
require_once 'common.php';
require_once 'DBConnection.php';

use DB\DBConnection;

// 1. Controllo Accesso
if (!isset($_SESSION['utente']) || $_SESSION['ruolo'] !== 'admin') {
    header("location: 403.php");
    exit();
}

// 2. View selection
$view = $_GET['view'] ?? ($_POST['view'] ?? 'vini');
if (!in_array($view, ['vini', 'utenti'], true)) {
    $view = 'vini';
}

$query = '';
if ($view === 'utenti') {
    $query = trim($_GET['q'] ?? '');
}

// 3. Gestione Azioni (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $azione = $_POST['azione'] ?? '';
    $db = new DBConnection();

    if ($azione === 'salva_vino') {
        $dati = [
            'nome' => $_POST['nome'],
            'prezzo' => $_POST['prezzo'],
            'quantita_stock' => $_POST['quantita_stock'],
            'stato' => $_POST['stato'],
            'img' => $_POST['img'],
            'categoria' => $_POST['categoria'],
            'descrizione_breve' => $_POST['descrizione_breve'],
            'descrizione_estesa' => $_POST['descrizione_estesa'],
            'vitigno' => $_POST['vitigno'] ?? '',
            'annata' => $_POST['annata'] ?? '',
            'gradazione' => $_POST['gradazione'] ?? '',
            'temperatura' => $_POST['temperatura'] ?? '',
            'abbinamenti' => $_POST['abbinamenti'] ?? ''
        ];

        if (!empty($_POST['id_vino'])) {
            $db->modificaVino($_POST['id_vino'], $dati);
        } else {
            $db->inserisciVino($dati);
        }
    }

    if ($azione === 'toggle_vino') {
        $nuovoStato = ($_POST['current_status'] == 'attivo') ? 'nascosto' : 'attivo';
        $db->toggleStatoVino($_POST['id'], $nuovoStato);
    }

    if ($azione === 'elimina_vino') {
        $db->eliminaVino($_POST['id']);
    }

    if ($azione === 'ripristina_vino') {
        $db->ripristinaVino($_POST['id']);
    }

    // --- AZIONI UTENTI (ORIGINALI - NON TOCCATE) ---
    if ($azione === 'salva_utente') {
        $nome = trim($_POST['utente_nome'] ?? '');
        $cognome = trim($_POST['utente_cognome'] ?? '');
        $email = trim($_POST['utente_email'] ?? '');
        $password = $_POST['utente_password'] ?? '';
        $ruolo = $_POST['utente_ruolo'] ?? 'user';

        if ($nome !== '' && $cognome !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($password) >= 8) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $db->creaUtenteAdmin($nome, $cognome, $email, $passwordHash, $ruolo);
        }
    }

    if ($azione === 'cambia_ruolo') {
        $idUtente = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $ruolo = $_POST['ruolo'] ?? '';

        if ($idUtente > 0 && $idUtente !== (int)$_SESSION['utente_id']) {
            $db->aggiornaRuoloUtente($idUtente, $ruolo);
        }
    }

    if ($azione === 'elimina_utente') {
        $idUtente = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($idUtente > 0 && $idUtente !== (int)$_SESSION['utente_id']) {
            $db->eliminaUtenteAdmin($idUtente);
        }
    }
    
    $db->closeConnection();
    // Redirect per pulire il POST
    if ($view === 'utenti' && $query !== '') {
         header("Location: admin.php?view=" . $view . "&q=" . urlencode($query));
    } else {
         header("Location: admin.php?view=" . $view); 
    }
    exit;
}

// 4. Caricamento Dati
$htmlContent = caricaPagina('../../html/admin.html');
$nomeUtente = htmlspecialchars($_SESSION['nome']);

$db = new DBConnection();
// Vini: prendiamo tutto per gestire il filtro eliminati via CSS
$viniArray = ($view === 'vini') ? $db->getTuttiViniAdmin() : [];
$utentiArray = ($view === 'utenti') ? $db->getUtentiAdmin() : [];
$db->closeConnection();

function getFormVinoHTML($v = null) {
    $isEdit = ($v !== null);
    $id = $isEdit ? $v['id'] : '';
    $nome = $isEdit ? htmlspecialchars($v['nome']) : '';
    $prezzo = $isEdit ? $v['prezzo'] : '';
    $stock = $isEdit ? $v['quantita_stock'] : '0';
    $img = $isEdit ? htmlspecialchars($v['img']) : '../../images/tr/placeholder.webp';
    $descBreve = $isEdit ? htmlspecialchars($v['descrizione_breve']) : '';
    $descEstesa = $isEdit ? htmlspecialchars($v['descrizione_estesa']) : '';
    $vitigno = $isEdit ? htmlspecialchars($v['vitigno']) : '';
    $annata = $isEdit ? htmlspecialchars($v['annata']) : '';
    $gradazione = $isEdit ? htmlspecialchars($v['gradazione']) : '';
    $temperatura = $isEdit ? htmlspecialchars($v['temperatura']) : '';
    $abbinamenti = $isEdit ? htmlspecialchars($v['abbinamenti']) : '';
    
    // Select Categoria
    $cats = ['rossi', 'bianchi', 'selezione'];
    $catOptions = "";
    $currentCat = $isEdit ? $v['categoria'] : 'rossi';
    foreach($cats as $c) {
        $sel = ($currentCat == $c) ? 'selected' : '';
        $catOptions .= "<option value='$c' $sel>" . ucfirst($c) . "</option>";
    }

    // Select Stato
    $stati = ['attivo', 'nascosto']; 
    $statoOptions = "";
    $currentStato = $isEdit ? $v['stato'] : 'attivo';
    // Se è eliminato, aggiungiamolo alle opzioni per completezza
    if ($currentStato === 'eliminato') $stati[] = 'eliminato'; 
    foreach($stati as $s) {
        $sel = ($currentStato == $s) ? 'selected' : '';
        $statoOptions .= "<option value='$s' $sel>" . ucfirst($s) . "</option>";
    }

    return "
    <form method='POST' enctype='multipart/form-data'>
        <input type='hidden' name='azione' value='salva_vino'>
        <input type='hidden' name='id_vino' value='$id'>
        
        <div class='form-grid'>
            <div class='admin-form-group'>
                <label>Nome Vino *</label>
                <input type='text' name='nome' class='admin-input' required value='$nome'>
                <small class='input-help'>Es: Pinot Nero Riserva</small>
            </div>
            <div class='admin-form-group'>
                <label>Categoria *</label>
                <select name='categoria' class='admin-input'>$catOptions</select>
                <small class='input-help'>Es: Rossi</small>
            </div>
            <div class='admin-form-group'>
                <label>Prezzo (€) *</label>
                <input type='number' step='0.01' name='prezzo' class='admin-input' required value='$prezzo'>
                <small class='input-help'>Es: 12.50</small>
            </div>
            <div class='admin-form-group'>
                <label>Stock *</label>
                <input type='number' name='quantita_stock' class='admin-input' required value='$stock'>
                <small class='input-help'>Es: 120</small>
            </div>
            <div class='admin-form-group'>
                <label>Stato</label>
                <select name='stato' class='admin-input'>$statoOptions</select>
                <small class='input-help'>Es: Attivo</small>
            </div>
            <div class='admin-form-group'>
                <label>Percorso Immagine</label>
                <input type='text' name='img' class='admin-input' value='$img'>
                <small class='input-help'>Es: ../../images/tr/placeholder.webp</small>
            </div>
        </div>

        <div class='admin-form-group'>
            <label>Descrizione Breve (Card)</label>
            <input type='text' name='descrizione_breve' class='admin-input' maxlength='100' value='$descBreve'>
            <small class='input-help'>Es: Rosso elegante con note di frutti rossi</small>
        </div>

        <div class='admin-form-group'>
            <label>Descrizione Estesa (Modale)</label>
            <textarea name='descrizione_estesa' class='admin-input' rows='3'>$descEstesa</textarea>
            <small class='input-help'>Es: Affinato in barrique per 12 mesi, struttura intensa e finale speziato.</small>
        </div>

        <fieldset class='admin-fieldset'>
            <legend>Scheda Tecnica</legend>
            <div class='form-grid'>
                <div class='admin-form-group'><label>Vitigno</label><input type='text' name='vitigno' class='admin-input' value='$vitigno'><small class='input-help'>Es: Cabernet Sauvignon</small></div>
                <div class='admin-form-group'><label>Annata</label><input type='text' name='annata' class='admin-input' value='$annata'><small class='input-help'>Es: 2021</small></div>
                <div class='admin-form-group'><label>Gradazione</label><input type='text' name='gradazione' class='admin-input' value='$gradazione'><small class='input-help'>Es: 13.5% vol</small></div>
                <div class='admin-form-group'><label>Temperatura</label><input type='text' name='temperatura' class='admin-input' value='$temperatura'><small class='input-help'>Es: 16-18 C</small></div>
                <div class='admin-form-group'><label>Abbinamenti</label><input type='text' name='abbinamenti' class='admin-input' value='$abbinamenti'><small class='input-help'>Es: Carni rosse, formaggi stagionati</small></div>
            </div>
        </fieldset>

        <div class='admin-form-actions'>
            <button type='submit' class='btn-primary'>" . ($isEdit ? 'Salva Modifiche' : 'Crea Vino') . "</button>
        </div>
    </form>";
}

$rigaVini = "";
$modaliViniHTML = "";

if ($view === 'vini') {
    foreach ($viniArray as $v) {
        $isDeleted = ($v['stato'] === 'eliminato');
        $rowClass = $isDeleted ? 'row-deleted' : '';
        // Il CSS gestirà la visibilità di row-deleted basandosi sul checkbox #toggle-deleted-visibility
        
        $badgeClass = $isDeleted ? 'badge-hidden' : (($v['stato'] == 'attivo') ? 'badge-active' : 'badge-hidden');
        $txtStato = ucfirst($v['stato']);
        $badge = "<span class='badge $badgeClass'>$txtStato</span>";
        
        $stockClass = ($v['quantita_stock'] < 10 && !$isDeleted) ? 'stock-low' : '';
        $nomeSafe = htmlspecialchars($v['nome'], ENT_QUOTES);
        
        // Icona Visibilità
        $iconaVisibilita = ($v['stato'] == 'attivo') 
            ? '<i class="fas fa-eye" aria-hidden="true"></i>' 
            : '<i class="fas fa-eye-slash icon-muted" aria-hidden="true"></i>';
        
        $actions = "";
        $modalToggleId = "modal-edit-" . $v['id'];

        if (!$isDeleted) {
            // 1. Modifica (LABEL per checkbox)
            $actions .= "
            <label for='$modalToggleId' class='btn-icon' aria-label='Modifica $nomeSafe' role='button' tabindex='0'>
                <i class='fas fa-edit'></i>
            </label>";
            
            // 2. Toggle Visibilità (Form)
            $actions .= "
            <form method='POST'>
                <input type='hidden' name='azione' value='toggle_vino'>
                <input type='hidden' name='view' value='{$view}'>
                <input type='hidden' name='id' value='{$v['id']}'>
                <input type='hidden' name='current_status' value='{$v['stato']}'>
                <button type='submit' class='btn-icon' aria-label='Cambia visibilità $nomeSafe'>
                    $iconaVisibilita
                </button>
            </form>";

            // 3. Elimina (Form)
            $actions .= "
            <form method='POST' onsubmit=\"return confirm('Sei sicuro di voler eliminare $nomeSafe?');\">
                <input type='hidden' name='azione' value='elimina_vino'>
                <input type='hidden' name='view' value='{$view}'>
                <input type='hidden' name='id' value='{$v['id']}'>
                <button type='submit' class='btn-icon btn-icon-delete' aria-label='Elimina $nomeSafe'>
                    <i class='fas fa-trash'></i>
                </button>
            </form>";
            
            // Genero la modale specifica per questo vino (se non eliminato)
            $formHTML = getFormVinoHTML($v);
            $modaliViniHTML .= "
            <input type='checkbox' id='$modalToggleId' class='state-toggle'>
            <div class='modal-wrapper-css'>
                <label for='$modalToggleId' class='modal-overlay-close' aria-label='Chiudi'></label>
                <div class='modal-box-css'>
                    <label for='$modalToggleId' class='modal-close-x' aria-label='Chiudi'>&times;</label>
                    <h2 class='modal-title'>Modifica Vino: $nomeSafe</h2>
                    $formHTML
                </div>
            </div>";

        } else {
            // Azioni per vini eliminati (solo ripristino)
            $actions = "
            <form method='POST' onsubmit=\"return confirm('Vuoi ripristinare $nomeSafe? Tornerà tra i vini nascosti.');\">
                <input type='hidden' name='azione' value='ripristina_vino'>
                <input type='hidden' name='view' value='{$view}'>
                <input type='hidden' name='id' value='{$v['id']}'>
                <button type='submit' class='btn-icon btn-icon-restore' aria-label='Ripristina $nomeSafe' title='Ripristina Vino'>
                    <i class='fas fa-trash-restore'></i> 
                </button>
            </form>";
        }

        $rigaVini .= "<tr class='{$rowClass}'>
            <th scope='row' data-title='ID'><b>{$v['id']}</b></th>
            <td data-title='Anteprima'><img src='" . htmlspecialchars($v['img']) . "' class='admin-thumb' alt=''></td>
            <td data-title='Dettagli'>
                <div class='wine-title'>" . htmlspecialchars($v['nome']) . "</div>
                <div class='wine-cat'>" . ucfirst($v['categoria']) . "</div>
                <div class='truncate'>{$v['descrizione_breve']}</div>
            </td>
            <td data-title='Prezzo'>€ " . number_format($v['prezzo'], 2) . "</td>
            <td data-title='Stock' class='$stockClass'>{$v['quantita_stock']}</td>
            <td data-title='Stato'>$badge</td>
            <td data-title='Azioni' class='admin-col-actions'><div class='action-group'>$actions</div></td>
        </tr>";
    }
}

// Modale Nuovo Vino (No-JS)
$modalNuovoVinoHTML = "";
if ($view === 'vini') {
    $formNuovo = getFormVinoHTML(null);
    $modalNuovoVinoHTML = "
    <input type='checkbox' id='toggle-modal-nuovo' class='state-toggle'>
    <div class='modal-wrapper-css'>
        <label for='toggle-modal-nuovo' class='modal-overlay-close' aria-label='Chiudi'></label>
        <div class='modal-box-css'>
            <label for='toggle-modal-nuovo' class='modal-close-x' aria-label='Chiudi'>&times;</label>
            <h2 class='modal-title'>Aggiungi Nuovo Vino</h2>
            $formNuovo
        </div>
    </div>";
}

$rigaUtenti = "";
if ($view === 'utenti') {
    // Logica filtro ricerca utenti
    if ($query !== '') {
        $utentiArray = array_filter($utentiArray, function ($u) use ($query) {
            $q = strtolower($query);
            return (stripos($u['nome'], $q) !== false)
                || (stripos($u['cognome'], $q) !== false)
                || (stripos($u['email'], $q) !== false)
                || (stripos($u['ruolo'], $q) !== false)
                || (stripos((string)$u['id'], $q) !== false);
        });
    }

    foreach ($utentiArray as $u) {
        $idUtente = (int)$u['id'];
        $isSelf = ($idUtente === (int)$_SESSION['utente_id']);
        $nome = htmlspecialchars($u['nome']);
        $cognome = htmlspecialchars($u['cognome']);
        $email = htmlspecialchars($u['email']);
        $ruolo = htmlspecialchars($u['ruolo']);
        $dataReg = htmlspecialchars($u['data_registrazione']);

        $ruoloOptions = "";
        foreach (['user', 'staff', 'admin'] as $r) {
            $selected = ($u['ruolo'] === $r) ? "selected" : "";
            $ruoloOptions .= "<option value='{$r}' {$selected}>{$r}</option>";
        }

        $azioni = $isSelf
            ? "<span>Impossibile sei tu;)</span>"
            : "<div class='action-group'>
                    <form method='POST'>
                        <input type='hidden' name='azione' value='cambia_ruolo'>
                        <input type='hidden' name='view' value='{$view}'>
                        <input type='hidden' name='id' value='{$idUtente}'>
                        <select name='ruolo' class='admin-input' aria-label='Ruolo per {$nome} {$cognome}'>
                            {$ruoloOptions}
                        </select>
                        <button type='submit' class='btn-secondary'>Aggiorna</button>
                    </form>
                    <form method='POST' onsubmit=\"return confirm('Eliminare l\\'utente {$nome} {$cognome}? Questa azione non si puo annullare.');\">
                        <input type='hidden' name='azione' value='elimina_utente'>
                        <input type='hidden' name='view' value='{$view}'>
                        <input type='hidden' name='id' value='{$idUtente}'>
                        <button type='submit' class='btn-icon btn-icon-delete' aria-label='Elimina {$nome} {$cognome}'>
                            <i class='fas fa-trash'></i>
                        </button>
                    </form>
                </div>";

        $rigaUtenti .= "<tr>
            <th scope='row' data-title='ID'><b>{$idUtente}</b></th>
            <td data-title='Nome'>{$nome}</td>
            <td data-title='Cognome'>{$cognome}</td>
            <td data-title='Email'>{$email}</td>
            <td data-title='Ruolo'>{$ruolo}</td>
            <td data-title='Registrazione'>{$dataReg}</td>
            <td data-title='Azioni'>{$azioni}</td>
        </tr>";
    }
}

$titoloAdmin = ($view === 'utenti') ? "Gestione Utenti" : "Gestione Catalogo Vini";
$tabViniClass = ($view === 'vini') ? "btn-primary" : "btn-secondary";
$tabUtentiClass = ($view === 'utenti') ? "btn-primary" : "btn-secondary";

// Checkbox per mostrare eliminati (solo vista vini)
$toggleDeletedHTML = "
<input type='checkbox' id='toggle-deleted-visibility' class='state-toggle'>
<label for='toggle-deleted-visibility' class='toggle-label-wrapper'>
    <div class='toggle-switch-graphic'></div>
    <span>Mostra vini eliminati</span>
</label>";

// Sezione Vini HTML
$sezioneVini = "
        <p id='descrizione-tab-vini' class='sum'>
            La tabella riassume i vini presenti nel catalogo. Ogni riga rappresenta un vino.
            Le colonne riportano: ID, immagine, dettagli, prezzo, stock, stato e azioni disponibili.
        </p>
        $toggleDeletedHTML
        <div class='table-responsive'>
            <table class='compact-table' aria-describedby='descrizione-tab-vini'>
                <caption>Elenco vini nel database</caption>
                <thead>
                    <tr>
                        <th scope='col' abbr='ID' class='admin-col-id'>ID</th>
                        <th scope='col' abbr='Immagine' class='admin-col-img'>Img</th>
                        <th scope='col' abbr='Dettagli'>Dettagli Vino</th>
                        <th scope='col' abbr='Prezzo'>Prezzo</th>
                        <th scope='col' abbr='Stock'>Stock</th>
                        <th scope='col' abbr='Stato'>Stato</th>
                        <th scope='col' abbr='Azioni' class='admin-col-actions'>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    {$rigaVini}
                </tbody>
            </table>
        </div>
        $modalNuovoVinoHTML
        $modaliViniHTML
        ";

// Sezione Utenti HTML (Codice Originale)
$sezioneUtenti = "
        <p id='descrizione-tab-utenti' class='sum'>
            La tabella riassume gli utenti registrati. Ogni riga rappresenta un utente.
            Le colonne riportano: ID, nome, cognome, email, ruolo, data registrazione e azioni disponibili.
        </p>
        <div class='table-responsive'>
            <table class='compact-table' aria-describedby='descrizione-tab-utenti'>
                <caption>Elenco utenti registrati</caption>
                <thead>
                    <tr>
                        <th scope='col' abbr='ID' class='admin-user-col-id'>ID</th>
                        <th scope='col' abbr='Nome'>Nome</th>
                        <th scope='col' abbr='Cognome'>Cognome</th>
                        <th scope='col' abbr='Email'>Email</th>
                        <th scope='col' abbr='Ruolo' class='admin-user-col-role'>Ruolo</th>
                        <th scope='col' abbr='Registrazione' class='admin-user-col-registered'>Registrazione</th>
                        <th scope='col' abbr='Azioni' class='admin-user-col-actions'>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    {$rigaUtenti}
                </tbody>
            </table>
        </div>";

// Pulsanti Nuovi Elementi
// Vini: usa label per checkbox (No-JS)
$btnNuovoVino = ($view === 'vini')
    ? "<label for='toggle-modal-nuovo' class=\"btn-primary admin-btn-inline\">
            <i class=\"fas fa-plus\" aria-hidden=\"true\"></i>&nbsp;Nuovo Vino
       </label>"
    : "";

// Utenti: usa button onclick (JS originale) - NON TOCCATO
$btnNuovoUtente = ($view === 'utenti')
    ? "<label for=\"toggle-modal-utente\" class=\"btn-primary\" role=\"button\" aria-label=\"Aggiungi nuovo utente\">
            <i class=\"fas fa-plus\" aria-hidden=\"true\"></i> Nuovo Utente
       </label>"
    : "";

// Form Ricerca Utenti (Originale)
$userSearchForm = "";
if ($view === 'utenti') {
    $safeQuery = htmlspecialchars($query, ENT_QUOTES);
    $userSearchForm = "<form method=\"GET\" action=\"admin.php\" class=\"admin-search-form\" role=\"search\">
            <input type=\"hidden\" name=\"view\" value=\"utenti\">
            <label for=\"admin-search\" class=\"admin-search-label\">Cerca utente</label>
            <input type=\"search\" id=\"admin-search\" name=\"q\" value=\"{$safeQuery}\" placeholder=\"Nome, email o ruolo\" class=\"admin-search-input\">
            <button type=\"submit\" class=\"btn-secondary admin-search-button\">Cerca</button>
        </form>";
}

// Replace
$htmlContent = str_replace("[nome_utente]", $nomeUtente, $htmlContent);
$htmlContent = str_replace("[titolo_admin]", $titoloAdmin, $htmlContent);
$htmlContent = str_replace("[tab_vini_class]", $tabViniClass, $htmlContent);
$htmlContent = str_replace("[tab_utenti_class]", $tabUtentiClass, $htmlContent);
$htmlContent = str_replace("[user_search_form]", $userSearchForm, $htmlContent);
$htmlContent = str_replace("[btn_nuovo_vino]", $btnNuovoVino, $htmlContent);
$htmlContent = str_replace("[btn_nuovo_utente]", $btnNuovoUtente, $htmlContent);
$htmlContent = str_replace("[sezione_vini]", ($view === 'vini') ? $sezioneVini : "", $htmlContent);
$htmlContent = str_replace("[sezione_utenti]", ($view === 'utenti') ? $sezioneUtenti : "", $htmlContent);

// Pulizia menu
$htmlContent = str_replace("[cart_icon_link]", "", $htmlContent); 
$htmlContent = str_replace("[user_area_link]", "", $htmlContent);

echo $htmlContent;
?>
