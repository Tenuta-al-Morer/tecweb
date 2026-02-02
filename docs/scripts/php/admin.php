<?php
session_start();
require_once 'common.php';
require_once 'DBConnection.php';

use DB\DBConnection;

if (!isset($_SESSION['utente']) || $_SESSION['ruolo'] !== 'admin') {
    header("location: 403.php");
    exit();
}

$view = $_GET['view'] ?? ($_POST['view'] ?? 'vini');
if (!in_array($view, ['vini', 'utenti'], true)) {
    $view = 'vini';
}

$query = '';
if ($view === 'utenti') {
    $query = trim($_GET['q'] ?? '');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $azione = $_POST['azione'] ?? '';
    $db = new DBConnection();

    if ($azione === 'salva_vino') {
        
        $percorsoImmagine = $_POST['img_old'] ?? '../../images/tr/placeholder.webp';

        if (isset($_FILES['img_file']) && $_FILES['img_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES['img_file']['tmp_name'];
            $fileName = $_FILES['img_file']['name'];
            
            $check = getimagesize($fileTmp);
            if ($check !== false) {
                $cleanName = preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($fileName));
                $nuovoNomeFile = time() . "_" . $cleanName;
                
                $targetDir = "../../images/tr/";
                $targetFile = $targetDir . $nuovoNomeFile;

                $dbPath = "images/tr/" . $nuovoNomeFile;

                if (move_uploaded_file($fileTmp, $targetFile)) {
                    $percorsoImmagine = $dbPath; 
                }
            }
        }

        $dati = [
            'nome' => $_POST['nome'],
            'prezzo' => $_POST['prezzo'],
            'quantita_stock' => $_POST['quantita_stock'],
            'stato' => $_POST['stato'],
            'img' => $percorsoImmagine,
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
    
    if ($view === 'utenti' && $query !== '') {
         header("Location: admin.php?view=" . $view . "&q=" . urlencode($query));
    } else {
         header("Location: admin.php?view=" . $view); 
    }
    exit;
}

$htmlContent = caricaPagina('../../html/admin.html');
$nomeUtente = htmlspecialchars($_SESSION['nome']);

$db = new DBConnection();
$viniArray = ($view === 'vini') ? $db->getTuttiViniAdmin() : [];
$utentiArray = ($view === 'utenti') ? $db->getUtentiAdmin() : [];
$db->closeConnection();

function getFormVinoHTML($v = null) {
    $isEdit = ($v !== null);
    $id = $isEdit ? $v['id'] : 'new'; 
    $suffix = "_" . $id; 
    
    $nome = $isEdit ? htmlspecialchars($v['nome']) : '';
    $prezzo = $isEdit ? $v['prezzo'] : '0.00';
    $stock = $isEdit ? $v['quantita_stock'] : '0';

    $imgPathDB = $isEdit ? htmlspecialchars($v['img']) : '../../images/tr/placeholder.webp';
    $imgSrcPreview = str_replace(' ', '%20', $imgPathDB);

    $descBreve = $isEdit ? htmlspecialchars($v['descrizione_breve']) : '';
    $descEstesa = $isEdit ? htmlspecialchars($v['descrizione_estesa']) : '';
    $vitigno = $isEdit ? htmlspecialchars($v['vitigno']) : '';
    $annata = $isEdit ? htmlspecialchars($v['annata']) : '';
    $gradazione = $isEdit ? htmlspecialchars($v['gradazione']) : '';
    $temperatura = $isEdit ? htmlspecialchars($v['temperatura']) : '';
    $abbinamenti = $isEdit ? htmlspecialchars($v['abbinamenti']) : '';
    
    $cats = ['rossi', 'bianchi', 'selezione'];
    $catOptions = "";
    $currentCat = $isEdit ? $v['categoria'] : 'rossi';
    foreach($cats as $c) {
        $sel = ($currentCat == $c) ? 'selected' : '';
        $catOptions .= "<option value='$c' $sel>" . ucfirst($c) . "</option>";
    }

    $stati = ['attivo', 'nascosto']; 
    $statoOptions = "";
    $currentStato = $isEdit ? $v['stato'] : 'attivo';
    if ($currentStato === 'eliminato') $stati[] = 'eliminato'; 
    foreach($stati as $s) {
        $sel = ($currentStato == $s) ? 'selected' : '';
        $statoOptions .= "<option value='$s' $sel>" . ucfirst($s) . "</option>";
    }

    $btnText = $isEdit ? 'Salva Modifiche' : 'Crea Vino';
    $idVinoField = $isEdit ? $v['id'] : '';

    return "
    <form method='POST' enctype='multipart/form-data'>
        <input type='hidden' name='azione' value='salva_vino'>
        <input type='hidden' name='id_vino' value='$idVinoField'>
        <input type='hidden' name='img_old' value='$imgPathDB'>
        
        <div class='form-grid'>
            <div class='admin-form-group'>
                <label for='nome$suffix'>Nome Vino *</label>
                <input type='text' id='nome$suffix' name='nome' class='admin-input' required value='$nome'>
                <span class='input-help little'>Esempio: Pinot Nero Riserva</span>
            </div>
            <div class='admin-form-group'>
                <label for='categoria$suffix'>Categoria *</label>
                <select id='categoria$suffix' name='categoria' class='admin-input'>$catOptions</select>
                <span class='input-help little'>Esempio: Rossi</span>
            </div>
            <div class='admin-form-group'>
                <label for='prezzo$suffix'>Prezzo (€) *</label>
                <input type='number' step='0.01' id='prezzo$suffix' name='prezzo' class='admin-input' required value='$prezzo'>
                <span class='input-help little'>Esempio: 12.50</span>
            </div>
            <div class='admin-form-group'>
                <label for='stock$suffix'>Stock *</label>
                <input type='number' id='stock$suffix' name='quantita_stock' class='admin-input' required value='$stock'>
                <span class='input-help little'>Esempio: 120</span>
            </div>
            <div class='admin-form-group'>
                <label for='stato$suffix'>Stato</label>
                <select id='stato$suffix' name='stato' class='admin-input'>$statoOptions</select>
                <span class='input-help little'>Esempio: Attivo</span>
            </div>
            
            <div class='admin-form-group'>
                <label for='img_file$suffix'>Immagine (JPG, PNG, WEBP)</label>
                <div class='image-upload-wrapper'>
                    <img src='$imgSrcPreview' alt='Anteprima attuale' class='admin-img-preview'>
                    <input type='file' id='img_file$suffix' name='img_file' class='admin-input' accept='.jpg, .jpeg, .png, .webp'>
                </div>
                <span class='input-help little'>Lascia vuoto per mantenere l'immagine attuale.</span>
            </div>
        </div>

        <div class='admin-form-group'>
            <label for='desc_breve$suffix'>Descrizione Breve (Card)</label>
            <input type='text' id='desc_breve$suffix' name='descrizione_breve' class='admin-input' maxlength='100' value='$descBreve'>
            <span class='input-help little'>Esempio: Rosso elegante con note di frutti rossi</span>
        </div>

        <div class='admin-form-group'>
            <label for='desc_estesa$suffix'>Descrizione Estesa (Modale)</label>
            <textarea id='desc_estesa$suffix' name='descrizione_estesa' class='admin-input' rows='3'>$descEstesa</textarea>
            <span class='input-help little'>Esempio: Affinato in barrique per 12 mesi...</span>
        </div>

        <fieldset class='admin-fieldset'>
            <legend>Scheda Tecnica</legend>
            <div class='form-grid'>
                <div class='admin-form-group'><label for='vitigno$suffix'>Vitigno</label><input type='text' id='vitigno$suffix' name='vitigno' class='admin-input' value='$vitigno'></div>
                <div class='admin-form-group'><label for='annata$suffix'>Annata</label><input type='text' id='annata$suffix' name='annata' class='admin-input' value='$annata'></div>
                <div class='admin-form-group'><label for='grad$suffix'>Gradazione</label><input type='text' id='grad$suffix' name='gradazione' class='admin-input' value='$gradazione'></div>
                <div class='admin-form-group'><label for='temp$suffix'>Temperatura</label><input type='text' id='temp$suffix' name='temperatura' class='admin-input' value='$temperatura'></div>
                <div class='admin-form-group'><label for='abb$suffix'>Abbinamenti</label><input type='text' id='abb$suffix' name='abbinamenti' class='admin-input' value='$abbinamenti'></div>
            </div>
        </fieldset>

        <div class='admin-form-actions'>
            <button type='submit' class='btn-primary'>$btnText</button>
        </div>
    </form>";
}

$rigaVini = "";
$modaliViniHTML = "";

if ($view === 'vini') {
    foreach ($viniArray as $v) {
        $isDeleted = ($v['stato'] === 'eliminato');
        $rowClass = $isDeleted ? 'row-deleted' : '';
        
        $badgeClass = $isDeleted ? 'badge-hidden' : (($v['stato'] == 'attivo') ? 'badge-active' : 'badge-hidden');
        $txtStato = ucfirst($v['stato']);
        $badge = "<span class='badge $badgeClass'>$txtStato</span>";
        
        $stockClass = ($v['quantita_stock'] < 10 && !$isDeleted) ? 'stock-low' : '';
        $nomeSafe = htmlspecialchars($v['nome'], ENT_QUOTES);
        
        $imgSrc = str_replace(' ', '%20', htmlspecialchars($v['img']));
        
        $iconaVisibilita = ($v['stato'] == 'attivo') 
            ? '<span class="fas fa-eye" aria-hidden="true"></span>' 
            : '<span class="fas fa-eye-slash icon-muted" aria-hidden="true"></span>';
        
        $actions = "";
        $modalToggleId = "modal-edit-" . $v['id'];

        if (!$isDeleted) {
            $actions .= "
            <label for='$modalToggleId' class='btn-icon' title='Modifica $nomeSafe' tabindex='0'>
                <span class='visually-hidden'>Modifica $nomeSafe</span>
                <span class='fas fa-edit' aria-hidden='true'></span>
            </label>";
            
            $actions .= "
            <form method='POST'>
                <input type='hidden' name='azione' value='toggle_vino'>
                <input type='hidden' name='view' value='{$view}'>
                <input type='hidden' name='id' value='{$v['id']}'>
                <input type='hidden' name='current_status' value='{$v['stato']}'>
                <button type='submit' class='btn-icon' title='Cambia visibilità $nomeSafe' aria-label='Cambia visibilità $nomeSafe'>
                    $iconaVisibilita
                </button>
            </form>";

            $actions .= "
            <form method='POST'>
                <input type='hidden' name='azione' value='elimina_vino'>
                <input type='hidden' name='view' value='{$view}'>
                <input type='hidden' name='id' value='{$v['id']}'>
                <button type='submit' class='btn-icon btn-icon-delete' title='Elimina $nomeSafe' aria-label='Elimina $nomeSafe'>
                    <span class='fas fa-trash' aria-hidden='true'></span>
                </button>
            </form>";
            
            $formHTML = getFormVinoHTML($v);
            $modaliViniHTML .= "
            <input type='checkbox' id='$modalToggleId' class='state-toggle' aria-label='Pannello $nomeSafe'>
            <div class='modal-wrapper-css'>
                <label for='$modalToggleId' class='modal-overlay-close' title='Chiudi'><span class='visually-hidden'>Chiudi</span></label>
                <div class='modal-box-css'>
                    <h2 class='modal-title'>Modifica Vino: $nomeSafe</h2>
                    $formHTML
                    <label for='$modalToggleId' class='modal-close-x' title='Chiudi' tabindex='0'>&times;</label>
                </div>
            </div>
            <label for='$modalToggleId' class='visually-hidden'>Pannello $nomeSafe</label>";
        
        } else {
            $actions = "
            <form method='POST'>
                <input type='hidden' name='azione' value='ripristina_vino'>
                <input type='hidden' name='view' value='{$view}'>
                <input type='hidden' name='id' value='{$v['id']}'>
                <button type='submit' class='btn-icon btn-icon-restore' aria-label='Ripristina $nomeSafe' title='Ripristina Vino'>
                    <span class='fas fa-trash-restore' aria-hidden='true'></span>
                </button>
            </form>";
        }

        $rigaVini .= "<tr class='{$rowClass}'>
            <th scope='row'>{$v['id']}</th>
            <td data-title='Anteprima'><img src='$imgSrc' class='admin-thumb' alt='Anteprima $nomeSafe'></td>
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

$modalNuovoVinoHTML = "";
if ($view === 'vini') {
    $formNuovo = getFormVinoHTML(null);
    $modalNuovoVinoHTML = "
    <input type='checkbox' id='toggle-modal-nuovo' class='state-toggle' aria-label='Pannello Nuovo Vino'>
    <div class='modal-wrapper-css'>
        <label for='toggle-modal-nuovo' class='modal-overlay-close' title='Chiudi'><span class='visually-hidden'>Chiudi</span></label>
        <div class='modal-box-css'>
            <h2 class='modal-title'>Aggiungi Nuovo Vino</h2>
            $formNuovo
            <label for='toggle-modal-nuovo' class='modal-close-x' title='Chiudi' tabindex='0'>&times;</label>
        </div>
    </div>
    <label for='toggle-modal-nuovo' class='visually-hidden'>Pannello Nuovo Vino</label>";
}

$rigaUtenti = "";
$modalNuovoUtenteHTML = ""; 

if ($view === 'utenti') {
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
                        <button type='submit' class='btn-secondary'>
                            Aggiorna
                            <span class='visually-hidden'> ruolo di {$nome} {$cognome}</span>
                        </button>
                    </form>
                    <form method='POST'>
                        <input type='hidden' name='azione' value='elimina_utente'>
                        <input type='hidden' name='view' value='{$view}'>
                        <input type='hidden' name='id' value='{$idUtente}'>
                        <button type='submit' class='btn-icon btn-icon-delete' title='Elimina {$nome} {$cognome}' aria-label='Elimina {$nome} {$cognome}'>
                            <span class='fas fa-trash' aria-hidden='true'></span>
                        </button>
                    </form>
                </div>";

        $rigaUtenti .= "<tr>
            <th scope='row' data-title='ID'>{$idUtente}</th>
            <td data-title='Nome'>{$nome}</td>
            <td data-title='Cognome'>{$cognome}</td>
            <td data-title='Email'>{$email}</td>
            <td data-title='Ruolo'>{$ruolo}</td>
            <td data-title='Registrazione'>{$dataReg}</td>
            <td data-title='Azioni' class='Richieste_admin'>{$azioni}</td>
        </tr>";
    }

    $modalNuovoUtenteHTML = "
    <input type='checkbox' id='toggle-modal-utente' class='state-toggle' aria-label='Pannello Nuovo Utente'>
    <div class='modal-wrapper-css'>
        <label for='toggle-modal-utente' class='modal-overlay-close' title='Chiudi'><span class='visually-hidden'>Chiudi</span></label>
        <div class='modal-box-css'>
            <h2 class='modal-title'>Aggiungi Nuovo Utente</h2>
            <form method='POST'>
                <input type='hidden' name='azione' value='salva_utente'>
                <div class='admin-form-group'>
                    <label for='new_u_nome'>Nome *</label>
                    <input type='text' id='new_u_nome' name='utente_nome' class='admin-input' required>
                </div>
                <div class='admin-form-group'>
                    <label for='new_u_cognome'>Cognome *</label>
                    <input type='text' id='new_u_cognome' name='utente_cognome' class='admin-input' required>
                </div>
                <div class='admin-form-group'>
                    <label for='new_u_email'>Email *</label>
                    <input type='email' id='new_u_email' name='utente_email' class='admin-input' required>
                </div>
                <div class='admin-form-group'>
                    <label for='new_u_pass'>Password * (min. 8 caratteri)</label>
                    <input type='password' id='new_u_pass' name='utente_password' class='admin-input' minlength='8' required>
                </div>
                <div class='admin-form-group'>
                    <label for='new_u_ruolo'>Ruolo</label>
                    <select id='new_u_ruolo' name='utente_ruolo' class='admin-input'>
                        <option value='user'>User</option>
                        <option value='staff'>Staff</option>
                        <option value='admin'>Admin</option>
                    </select>
                </div>
                <div class='admin-form-actions'>
                    <button type='submit' class='btn-primary'>Crea Utente</button>
                </div>
            </form>
            <label for='toggle-modal-utente' class='modal-close-x' title='Chiudi' tabindex='0'>&times;</label>
        </div>
    </div>
    <label for='toggle-modal-utente' class='visually-hidden'>Pannello Nuovo Utente</label>";
}

$titoloAdmin = ($view === 'utenti') ? "Gestione Utenti" : "Gestione Catalogo Vini";
$tabViniClass = ($view === 'vini') ? "btn-primary" : "btn-secondary";
$tabUtentiClass = ($view === 'utenti') ? "btn-primary" : "btn-secondary";

$toggleDeletedHTML = "
<input type='checkbox' id='toggle-deleted-visibility' class='state-toggle'>
<label for='toggle-deleted-visibility' class='toggle-label-wrapper'>
    <span class='toggle-switch-graphic'></span>
    <span>Mostra vini eliminati</span>
</label>";

$sezioneVini = "
        <p id='descrizione-tab-vini' class='sum'>
            La tabella riassume i vini presenti nel catalogo. Ogni riga rappresenta un vino.
            Le colonne riportano: ID, immagine, dettagli, prezzo, stock, stato e azioni disponibili.
        </p>
        $toggleDeletedHTML
        <div class='table-responsive'>
            <table class='table-data' aria-describedby='descrizione-tab-vini'>
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

$sezioneUtenti = "
        <p id='descrizione-tab-utenti' class='sum'>
            La tabella riassume gli utenti registrati. Ogni riga rappresenta un utente.
            Le colonne riportano: ID, nome, cognome, email, ruolo, data registrazione e azioni disponibili.
        </p>
        <div class='table-responsive'>
            <table class='table-data' aria-describedby='descrizione-tab-utenti'>
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
        </div>
        $modalNuovoUtenteHTML
        ";

$btnNuovoVino = ($view === 'vini')
    ? "<label for='toggle-modal-nuovo' class=\"btn-primary admin-btn-inline\" tabindex=\"0\">
            <span class=\"fas fa-plus\" aria-hidden=\"true\"></span>&nbsp;Nuovo Vino
       </label>"
    : "";

$btnNuovoUtente = ($view === 'utenti')
    ? "<label for=\"toggle-modal-utente\" class=\"btn-primary\" tabindex=\"0\">
            <span class=\"fas fa-plus\" aria-hidden=\"true\"></span>&nbsp;Nuovo Utente
       </label>"
    : "";

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

$htmlContent = str_replace("[nome_utente]", $nomeUtente, $htmlContent);
$htmlContent = str_replace("[titolo_admin]", $titoloAdmin, $htmlContent);
$htmlContent = str_replace("[tab_vini_class]", $tabViniClass, $htmlContent);
$htmlContent = str_replace("[tab_utenti_class]", $tabUtentiClass, $htmlContent);
$htmlContent = str_replace("[user_search_form]", $userSearchForm, $htmlContent);
$htmlContent = str_replace("[btn_nuovo_vino]", $btnNuovoVino, $htmlContent);
$htmlContent = str_replace("[btn_nuovo_utente]", $btnNuovoUtente, $htmlContent);
$htmlContent = str_replace("[sezione_vini]", ($view === 'vini') ? $sezioneVini : "", $htmlContent);
$htmlContent = str_replace("[sezione_utenti]", ($view === 'utenti') ? $sezioneUtenti : "", $htmlContent);

$htmlContent = str_replace("[cart_icon_link]", "", $htmlContent); 
$htmlContent = str_replace("[user_area_link]", "", $htmlContent);

echo $htmlContent;
?>