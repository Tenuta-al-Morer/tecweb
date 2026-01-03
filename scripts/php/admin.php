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

// 2. Gestione Azioni (POST)
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

    // Toggle Stato
    if ($azione === 'toggle_vino') {
        $nuovoStato = ($_POST['current_status'] == 'attivo') ? 'nascosto' : 'attivo';
        $db->toggleStatoVino($_POST['id'], $nuovoStato);
    }

    // Eliminazione (NUOVO)
    if ($azione === 'elimina_vino') {
        $db->eliminaVino($_POST['id']);
    }
    
    $db->closeConnection();
    header("Location: admin.php"); 
    exit;
}

// 3. View
$htmlContent = caricaPagina('../../html/admin.html');
$nomeUtente = htmlspecialchars($_SESSION['nome']);

$db = new DBConnection();
$viniArray = $db->getTuttiViniAdmin();
$db->closeConnection();

// 4. Generazione Righe
$rigaVini = "";
foreach ($viniArray as $v) {
    // JSON sicuro per attributi HTML
    $json = json_encode($v, JSON_HEX_APOS | JSON_HEX_QUOT);
    
    // Badge
    $badgeClass = ($v['stato'] == 'attivo') ? 'badge-active' : 'badge-hidden';
    $txtStato = ucfirst($v['stato']);
    $badge = "<span class='badge $badgeClass'>$txtStato</span>";
    
    // Alert Stock
    $colorStock = ($v['quantita_stock'] < 10) ? 'color:var(--red-errors); font-weight:bold;' : '';

    $nomeSafe = htmlspecialchars($v['nome'], ENT_QUOTES);
    
    // Icona visibilità
    $iconaVisibilita = ($v['stato'] == 'attivo') 
        ? '<i class="fas fa-eye" aria-hidden="true"></i>' 
        : '<i class="fas fa-eye-slash" aria-hidden="true" style="color:var(--gray-text);"></i>';

    $rigaVini .= "<tr>
        <td data-title='ID'><b>{$v['id']}</b></td>
        
        <td data-title='Anteprima'><img src='" . htmlspecialchars($v['img']) . "' class='admin-thumb' alt=''></td>
        
        <td data-title='Dettagli'>
            <div class='wine-title'>" . htmlspecialchars($v['nome']) . "</div>
            <div class='wine-cat'>" . ucfirst($v['categoria']) . "</div>
            <div class='truncate'>{$v['descrizione_breve']}</div>
        </td>
        
        <td data-title='Prezzo'>€ " . number_format($v['prezzo'], 2) . "</td>
        <td data-title='Stock' style='$colorStock'>{$v['quantita_stock']}</td>
        <td data-title='Stato'>$badge</td>
        
        <td data-title='Azioni'>
            <div class='action-group'>
                <button type='button' class='btn-icon' onclick='apriModalModifica($json)' aria-label='Modifica $nomeSafe'>
                    <i class='fas fa-edit'></i>
                </button>
                
                <form method='POST'>
                    <input type='hidden' name='azione' value='toggle_vino'>
                    <input type='hidden' name='id' value='{$v['id']}'>
                    <input type='hidden' name='current_status' value='{$v['stato']}'>
                    <button type='submit' class='btn-icon' aria-label='Visibilità $nomeSafe'>
                        $iconaVisibilita
                    </button>
                </form>

                <form method='POST' onsubmit=\"return confirm('Sei sicuro di voler eliminare $nomeSafe? Questa azione non si può annullare.');\">
                    <input type='hidden' name='azione' value='elimina_vino'>
                    <input type='hidden' name='id' value='{$v['id']}'>
                    <button type='submit' class='btn-icon btn-icon-delete' aria-label='Elimina $nomeSafe'>
                        <i class='fas fa-trash'></i>
                    </button>
                </form>
            </div>
        </td>
    </tr>";
}

$htmlContent = str_replace("[nome_utente]", $nomeUtente, $htmlContent);
$htmlContent = str_replace("[riga_vini]", $rigaVini, $htmlContent);
// Pulizia placeholder menu
$htmlContent = str_replace("[cart_icon_link]", "", $htmlContent); 
$htmlContent = str_replace("[user_area_link]", "", $htmlContent);

echo $htmlContent;
?>