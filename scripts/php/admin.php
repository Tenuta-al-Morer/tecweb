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
// Ricerca utenti (solo view=utenti)
$query = '';
if ($view === 'utenti') {
    $query = trim($_GET['q'] ?? '');
}

// 3. Gestione Azioni (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $azione = $_POST['azione'] ?? '';
    $db = new DBConnection();

    // Salva/Modifica
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

    // Creazione utente
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

    // Toggle Stato
    if ($azione === 'toggle_vino') {
        $nuovoStato = ($_POST['current_status'] == 'attivo') ? 'nascosto' : 'attivo';
        $db->toggleStatoVino($_POST['id'], $nuovoStato);
    }

    // Eliminazione
    if ($azione === 'elimina_vino') {
        $db->eliminaVino($_POST['id']);
    }

    // Cambio ruolo utente
    if ($azione === 'cambia_ruolo') {
        $idUtente = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $ruolo = $_POST['ruolo'] ?? '';

        if ($idUtente > 0 && $idUtente !== (int)$_SESSION['utente_id']) {
            $db->aggiornaRuoloUtente($idUtente, $ruolo);
        }
    }

    // Eliminazione utente
    if ($azione === 'elimina_utente') {
        $idUtente = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($idUtente > 0 && $idUtente !== (int)$_SESSION['utente_id']) {
            $db->eliminaUtenteAdmin($idUtente);
        }
    }

    // Ripristino vino
    if ($azione === 'ripristina_vino') {
        $db->ripristinaVino($_POST['id']);
    }
    
    $db->closeConnection();
    header("Location: admin.php?view=" . $view); 
    exit;
}

// 4. View
$htmlContent = caricaPagina('../../html/admin.html');
$nomeUtente = htmlspecialchars($_SESSION['nome']);

$db = new DBConnection();
$viniArray = ($view === 'vini') ? $db->getTuttiViniAdmin() : [];
$utentiArray = ($view === 'utenti') ? $db->getUtentiAdmin() : [];
$db->closeConnection();

// 5. Generazione Righe
$rigaVini = "";
foreach ($viniArray as $v) {
    // JSON sicuro per attributi HTML
    $json = json_encode($v, JSON_HEX_APOS | JSON_HEX_QUOT);
    
    // Controlliamo se è eliminato
    $isDeleted = ($v['stato'] === 'eliminato');
    
    // Classi e Stili per la riga
    // Se è eliminato, aggiungiamo una classe specifica e lo nascondiamo con CSS inline
    $rowClass = $isDeleted ? 'row-deleted' : '';
    $rowStyle = $isDeleted ? 'style="display:none"' : '';

    // Gestione Badge
    if ($isDeleted) {
        $badgeClass = 'badge-hidden'; // Usiamo grigio/scuro
        $txtStato = 'Eliminato';
    } else {
        $badgeClass = ($v['stato'] == 'attivo') ? 'badge-active' : 'badge-hidden';
        $txtStato = ucfirst($v['stato']);
    }
    $badge = "<span class='badge $badgeClass'>$txtStato</span>";
    
    // Alert Stock
    $colorStock = ($v['quantita_stock'] < 10 && !$isDeleted) ? 'color:var(--red-errors); font-weight:bold;' : '';
    $nomeSafe = htmlspecialchars($v['nome'], ENT_QUOTES);
    
    // Icona visibilita (Disabilitata se eliminato)
    $iconaVisibilita = ($v['stato'] == 'attivo') 
        ? '<i class="fas fa-eye" aria-hidden="true"></i>' 
        : '<i class="fas fa-eye-slash" aria-hidden="true" style="color:var(--gray-text);"></i>';
    
    // Se eliminato, non mostriamo i pulsanti di azione standard o li disabilitiamo
    $actions = "";
    if (!$isDeleted) {
        $actions = "
            <button type='button' class='btn-icon' onclick='apriModalModifica($json)' aria-label='Modifica $nomeSafe'>
                <i class='fas fa-edit'></i>
            </button>
            
            <form method='POST'>
                <input type='hidden' name='azione' value='toggle_vino'>
                <input type='hidden' name='view' value='{$view}'>
                <input type='hidden' name='id' value='{$v['id']}'>
                <input type='hidden' name='current_status' value='{$v['stato']}'>
                <button type='submit' class='btn-icon' aria-label='Visibilita $nomeSafe'>
                    $iconaVisibilita
                </button>
            </form>

            <form method='POST' onsubmit=\"return confirm('Sei sicuro di voler eliminare $nomeSafe?');\">
                <input type='hidden' name='azione' value='elimina_vino'>
                <input type='hidden' name='view' value='{$view}'>
                <input type='hidden' name='id' value='{$v['id']}'>
                <button type='submit' class='btn-icon btn-icon-delete' aria-label='Elimina $nomeSafe'>
                    <i class='fas fa-trash'></i>
                </button>
            </form>";
    } else {
        $actions = "
        <form method='POST' onsubmit=\"return confirm('Vuoi ripristinare $nomeSafe? Tornerà tra i vini nascosti.');\">
            <input type='hidden' name='azione' value='ripristina_vino'> <input type='hidden' name='view' value='{$view}'>
            <input type='hidden' name='id' value='{$v['id']}'>
            
            <button type='submit' class='btn-icon' style='color: var(--accent-color);' aria-label='Ripristina $nomeSafe' title='Ripristina Vino'>
                <i class='fas fa-trash-restore'></i> 
            </button>
        </form>";
    }

    $rigaVini .= "<tr class='{$rowClass}' {$rowStyle}>
        <td data-title='ID'><b>{$v['id']}</b></td>
        
        <td data-title='Anteprima'><img src='" . htmlspecialchars($v['img']) . "' class='admin-thumb' alt=''></td>
        
        <td data-title='Dettagli'>
            <div class='wine-title'>" . htmlspecialchars($v['nome']) . "</div>
            <div class='wine-cat'>" . ucfirst($v['categoria']) . "</div>
            <div class='truncate'>{$v['descrizione_breve']}</div>
        </td>
        
        <td data-title='Prezzo'>EUR " . number_format($v['prezzo'], 2) . "</td>
        <td data-title='Stock' style='$colorStock'>{$v['quantita_stock']}</td>
        <td data-title='Stato'>$badge</td>
        
        <td data-title='Azioni'>
            <div class='action-group'>
                $actions
            </div>
        </td>
    </tr>";
}

$rigaUtenti = "";
if ($view === 'utenti' && $query !== '') {
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
        ? "<span>Impossibile modificare</span>"
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
        <td data-title='ID'><b>{$idUtente}</b></td>
        <td data-title='Nome'>{$nome}</td>
        <td data-title='Cognome'>{$cognome}</td>
        <td data-title='Email'>{$email}</td>
        <td data-title='Ruolo'>{$ruolo}</td>
        <td data-title='Registrazione'>{$dataReg}</td>
        <td data-title='Azioni'>{$azioni}</td>
    </tr>";
}

$titoloAdmin = ($view === 'utenti') ? "Gestione Utenti" : "Gestione Catalogo Vini";
$tabViniClass = ($view === 'vini') ? "btn-primary" : "btn-secondary";
$tabUtentiClass = ($view === 'utenti') ? "btn-primary" : "btn-secondary";

$sezioneVini = "
        <div class='table-responsive'>
            <table class='compact-table'>
                <caption>Elenco vini nel database</caption>
                <thead>
                    <tr>
                        <th scope='col' style='width: 50px;'>ID</th>
                        <th scope='col' style='width: 80px;'>Img</th>
                        <th scope='col'>Dettagli Vino</th>
                        <th scope='col'>Prezzo</th>
                        <th scope='col'>Stock</th>
                        <th scope='col'>Stato</th>
                        <th scope='col' style='width: 120px;'>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    {$rigaVini}
                </tbody>
            </table>
        </div>";

$sezioneUtenti = "
        <div class='table-responsive'>
            <table class='compact-table'>
                <caption>Elenco utenti registrati</caption>
                <thead>
                    <tr>
                        <th scope='col' style='width: 60px;'>ID</th>
                        <th scope='col'>Nome</th>
                        <th scope='col'>Cognome</th>
                        <th scope='col'>Email</th>
                        <th scope='col' style='width: 120px;'>Ruolo</th>
                        <th scope='col' style='width: 140px;'>Registrazione</th>
                        <th scope='col' style='width: 180px;'>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    {$rigaUtenti}
                </tbody>
            </table>
        </div>";

$btnNuovoVino = ($view === 'vini')
    ? "<button class=\"btn-primary\" onclick=\"apriModalNuovo()\" aria-label=\"Aggiungi nuovo vino\">\n            <i class=\"fas fa-plus\" aria-hidden=\"true\"></i> Nuovo Vino\n       </button>"
    : "";

$btnNuovoUtente = ($view === 'utenti')
    ? "<button class=\"btn-primary\" onclick=\"apriModalNuovoUtente()\" aria-label=\"Aggiungi nuovo utente\">\n            <i class=\"fas fa-plus\" aria-hidden=\"true\"></i> Nuovo Utente\n       </button>"
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
// Pulizia placeholder menu
$htmlContent = str_replace("[cart_icon_link]", "", $htmlContent); 
$htmlContent = str_replace("[user_area_link]", "", $htmlContent);

echo $htmlContent;
?>
