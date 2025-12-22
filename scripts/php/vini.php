<?php
session_start();
require_once 'common.php';
require_once 'DBConnection.php';

use DB\DBConnection;

// 1. Gestione Link Utente (Header)
// In base alla sessione, decidiamo se mostrare l'icona Login o l'icona Utente/Admin
$userLinkHTML = '';
if (isset($_SESSION['utente'])) {
    // Utente loggato: Link alla dashboard corretta
    $targetPage = ($_SESSION['ruolo'] === 'admin') ? 'admin.php' : 'user.php'; // o utente.php
    $userLinkHTML = '<a href="' . $targetPage . '" aria-label="Area Personale"><i class="fas fa-user-check"></i></a>';
} else {
    // Ospite: Link al login
    $userLinkHTML = '<a href="login.php" aria-label="Accedi o Registrati"><i class="fas fa-user"></i></a>';
}

// 2. Recupero Dati dal DB
$db = new DBConnection();
try {
    // (Restituisce tutti i vini con stato='attivo')
    $tuttiIVini = $db->getVini();
} catch (Exception $e) {
    error_log("Errore recupero vini: " . $e->getMessage());
    $tuttiIVini = [];
}
$db->closeConnection();

// 3. Generazione HTML delle Card
$htmlRossi = "";
$htmlBianchi = "";
$htmlSelezione = "";

// Funzione helper locale per generare la singola card
function costruisciCardVino($vino) {
    // Sanificazione
    $id = (int)$vino['id'];
    $nome = htmlspecialchars($vino['nome']);
    $prezzo = number_format($vino['prezzo'], 2, ',', '.');
    $img = htmlspecialchars($vino['img']);
    $descBreve = htmlspecialchars($vino['descrizione_breve']);
    $descEstesa = htmlspecialchars($vino['descrizione_estesa']);
    
    // Dati tecnici
    $vitigno = htmlspecialchars($vino['vitigno'] ?? 'N/D');
    $annata = htmlspecialchars($vino['annata'] ?? 'N/D');
    $gradazione = htmlspecialchars($vino['gradazione'] ?? 'N/D');
    $temperatura = htmlspecialchars($vino['temperatura'] ?? 'N/D');
    $abbinamenti = htmlspecialchars($vino['abbinamenti'] ?? 'N/D');

    // Costruzione Blocco HTML
    return '
    <article class="wine-article" 
            data-nome="' . $nome . '" 
            data-descrizione="' . $descEstesa . '" 
            data-img="' . $img . '"
            data-prezzo="' . $prezzo . '">
        
        <div class="wine-item">
            <img src="' . $img . '" alt="Bottiglia di ' . $nome . '" class="wine-image" loading="lazy">
            <div class="content-wine-article">
                <h3>' . $nome . '</h3>
                <p>' . $descBreve . '</p> 
            </div>
        </div>
        
        <div class="actions">
            <div class="selettore-quantita">
                <button type="button" onclick="aggiornaQuantita(this, -1)" aria-label="Diminuisci quantità di ' . $nome . '">-</button>
                
                <label for="qty-' . $id . '" class="visually-hidden">Quantità per ' . $nome . '</label>
                <input type="number" id="qty-' . $id . '" class="input-qty" name="quantita" value="1" min="1" readonly>
                
                <button type="button" onclick="aggiornaQuantita(this, 1)" aria-label="Aumenta quantità di ' . $nome . '">+</button>
            </div>
            
            <button class="buy-button" aria-label="Aggiungi ' . $nome . ' al carrello">Acquista</button>
            <button class="details-button" onclick="apriDettagli(this)" aria-label="Vedi dettagli e scheda tecnica di ' . $nome . '">Info</button>
        </div>
        
        <div style="display:none;" class="hidden-data">
            <span data-key="vitigno">' . $vitigno . '</span>
            <span data-key="annata">' . $annata . '</span>
            <span data-key="gradazione">' . $gradazione . '</span>
            <span data-key="temperatura">' . $temperatura . '</span>
            <span data-key="abbinamenti">' . $abbinamenti . '</span>
        </div>
    </article>';
}

// Ciclo di smistamento
if (!empty($tuttiIVini)) {
    foreach ($tuttiIVini as $vino) {
        $card = costruisciCardVino($vino);
        
        switch (strtolower($vino['categoria'])) {
            case 'rossi':
                $htmlRossi .= $card;
                break;
            case 'bianchi':
                $htmlBianchi .= $card;
                break;
            case 'selezione':
                $htmlSelezione .= $card;
                break;
            default:
                break;
        }
    }
}

// Gestione categorie vuote
$msgVuoto = '<p class="no-wines">Al momento non ci sono vini disponibili in questa categoria.</p>';
if (empty($htmlRossi)) $htmlRossi = $msgVuoto;
if (empty($htmlBianchi)) $htmlBianchi = $msgVuoto;
if (empty($htmlSelezione)) $htmlSelezione = $msgVuoto;

// 4. Caricamento e Parsing Template
$htmlContent = caricaPagina('../../html/vini.html');

// Sostituzione Placeholders
$htmlContent = str_replace("[user_area_link]", $userLinkHTML, $htmlContent);
$htmlContent = str_replace("[vini_rossi]", $htmlRossi, $htmlContent);
$htmlContent = str_replace("[vini_bianchi]", $htmlBianchi, $htmlContent);
$htmlContent = str_replace("[vini_selezione]", $htmlSelezione, $htmlContent);

// 5. Output
echo $htmlContent;
?>