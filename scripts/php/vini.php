<?php
session_start();
require_once 'common.php';
require_once 'DBConnection.php';

use DB\DBConnection;

// Recupero Dati dal DB
$db = new DBConnection();
try {
    // (Restituisce tutti i vini con stato='attivo')
    $tuttiIVini = $db->getVini();
} catch (Exception $e) {
    error_log("Errore recupero vini: " . $e->getMessage());
    $tuttiIVini = [];
}
$db->closeConnection();

// Generazione HTML delle Card
$htmlRossi = "";
$htmlBianchi = "";
$htmlSelezione = "";

// Funzione helper locale per generare la singola card
function costruisciCardVino($vino) {
    // --- RECUPERO DATI ---
    $id = (int)$vino['id'];
    $nome = htmlspecialchars($vino['nome']);
    $prezzo = number_format($vino['prezzo'], 2, ',', '.');
    $img = htmlspecialchars($vino['img']);
    $descBreve = htmlspecialchars($vino['descrizione_breve']);
    $descEstesa = htmlspecialchars($vino['descrizione_estesa']);
    $stock = (int)$vino['quantita_stock'];
    
    // Dati tecnici
    $vitigno = htmlspecialchars($vino['vitigno'] ?? '-');
    $annata = htmlspecialchars($vino['annata'] ?? '-');
    $gradazione = htmlspecialchars($vino['gradazione'] ?? '-');
    $temperatura = htmlspecialchars($vino['temperatura'] ?? '-');
    $abbinamenti = htmlspecialchars($vino['abbinamenti'] ?? '-');

    $modalId = "modal-vino-" . $id;

    // --- LOGICA VISIVA ---
    $htmlStock = "";
    $cardActionHTML = "";  // HTML per la card esterna
    $modalActionHTML = ""; // HTML per il popup interno
    $altText = "Bottiglia di " . $nome;

    if ($stock <= 0) {
        // --- CASO ESAURITO ---
        $altText .= " - Esaurito";
        
        // Esterno voglio solo bottone Info
        $cardActionHTML = '<div class=" card-actions actions-esaurito-wrapper">
                             <button class="badge-esaurito" disabled>Esaurito</button>
                             <label for="' . $modalId . '" class="details-button" aria-label="Maggiori informazioni su ' . $nome . '">Info</label>
                           </div>';
        
        // Interno voglio Badge Esaurito
        $modalActionHTML = '<div class="modal-actions-wrapper">
                              <span class="badge-esaurito" >Esaurito</span>
                            </div>';

    } else {
        // --- CASO DISPONIBILE ---
        $testoStock = ($stock <= 20) ? "Ultimi " . $stock . " pezzi!" : "Disponibile";
        $classeStock = ($stock <= 20) ? "stock-warning" : "stock-ok";
        $htmlStock = '<p class="stock-info ' . $classeStock . '"><i class="fas fa-check-circle"></i> ' . $testoStock . '</p>';

        // Versione NO-JS
        $qtyNoJS = '
        <div class="selettore-quantita selettore-nojs">
            <label for="qty-nojs-' . $id . '" class="visually-hidden">Quantità</label>
            <input type="number" id="qty-nojs-' . $id . '" name="quantita_nojs" value="1" min="1" max="' . $stock . '" class="input-qty-native">
        </div>';

        $qtyNoJSmodal = '
        <div class="selettore-quantita selettore-nojs modal-selettore-nojs">
            <label for="qty-nojs-' . $id . '" class="visually-hidden">Quantità</label>
            <input type="number" id="qty-nojs-' . $id . '" name="quantita_nojs" value="1" min="1" max="' . $stock . '" class="input-qty-native">
        </div>';

        // Versione JS
        $qtyJS = '
        <div class="selettore-quantita selettore-js">
            <button type="button" class="btn-minus" aria-label="Riduci quantità">-</button>
            <input type="text" value="1" readonly class="display-qty" aria-label="Quantità">
            <button type="button" class="btn-plus" data-max="' . $stock . '" aria-label="Aumenta quantità">+</button>
            
            <input type="hidden" name="quantita" value="1" class="qty-hidden">
        </div>';

        $qtyJSmodal = '
        <div class="selettore-quantita selettore-js modal-selettore-js">
            <button type="button" class="btn-minus" aria-label="Riduci quantità">-</button>
            <input type="text" value="1" readonly class="display-qty" aria-label="Quantità">
            <button type="button" class="btn-plus" data-max="' . $stock . '" aria-label="Aumenta quantità">+</button>
            
            <input type="hidden" name="quantita" value="1" class="qty-hidden">
        </div>';

        $cardActionHTML = '
        <form action="carrello.php" method="POST" class="wine-form">
            <input type="hidden" name="action" value="aggiungi">
            <input type="hidden" name="id_vino" value="' . $id . '">

            <div class="card-actions">
                <div class="card-buy-block">
                    ' . $qtyNoJS . '
                    ' . $qtyJS . '
                    <button type="submit" class="buy-button">Acquista</button>
                </div>
                <label for="' . $modalId . '" class="details-button" aria-label="Maggiori informazioni su ' . $nome . '">Info</label>
            </div>
        </form>';

        $modalActionHTML = '
        <form action="carrello.php" method="POST" class="modal-wine-form">
            <input type="hidden" name="action" value="aggiungi">
            <input type="hidden" name="id_vino" value="' . $id . '">
            
            <div class="modal-buy-block">
                ' . $qtyNoJSmodal . '
                ' . $qtyJSmodal . '
                <button type="submit" class="buy-button modal-btn-large">Aggiungi al Carrello</button>
            </div>
        </form>';
    }

    // --- OUTPUT HTML ---
    // da notare che uso tabindex="0" e role="button" per rendere le label accessibili via tastiera (serve JS apposito).
    // non l'ho usato per Info, che è label e deve esserlo per forza, perché apre il modal via checkbox hack.
    // dentro il modal invece non posso usare una checkbox hack per chiuderlo, quindi uso il role="button" e tabindex="0"
    
    return '
    <article class="wine-article">
        <div class="wine-item">
            <img src="' . $img . '" alt="' . $altText . '" class="wine-image" loading="lazy">
            <div class="content-wine-article">
                <h3>' . $nome . '</h3>
                <p>' . $descBreve . '</p> 
                ' . $htmlStock . '
            </div>
        </div>
        
        ' . $cardActionHTML . '

        <input type="checkbox" id="' . $modalId . '" class="modal-toggle-checkbox sr-only">
        
        <div class="modal-overlay">
            <div class="modal-content">
                <label for="' . $modalId . '" class="modal-close-btn" tabindex="0" role="button" aria-label="Chiudere informazioni di ' . $nome . '">&times;</label>
                
                <div class="modal-grid">
                    <div class="modal-img-col">
                        <img src="' . $img . '" alt="' . $altText . '">
                    </div>
                    <div class="modal-info-col">
                        <h2>' . $nome . '</h2>
                        <p class="modal-price">€ ' . $prezzo . '</p>
                        
                        <p class="modal-desc">' . $descEstesa . '</p>
                        
                        <div class="modal-specs">
                            <p><strong>Vitigno:</strong> ' . $vitigno . '</p>
                            <p><strong>Annata:</strong> ' . $annata . '</p>
                            <p><strong>Gradi:</strong> ' . $gradazione . '</p>
                            <p><strong>Temperatura:</strong> ' . $temperatura . '</p>
                            <p><strong>Abbinamenti:</strong> ' . $abbinamenti . '</p>
                        </div>
                        
                        ' . $modalActionHTML . '
                    </div>
                </div>
            </div>
            <label for="' . $modalId . '" class="modal-backdrop-close"></label>
        </div>
    </article>';
}

foreach ($tuttiIVini as $vino) {
    // genera l'HTML per questo specifico vino
    $card = costruisciCardVino($vino);
    
    switch ($vino['categoria']) {
        case 'rossi':
            $htmlRossi .= $card;
            break;
        case 'bianchi':
            $htmlBianchi .= $card;
            break;
        case 'selezione':
            $htmlSelezione .= $card;
            break;
    }
}

// Gestione categorie vuote
$msgVuoto = '<p class="no-wines">Al momento non ci sono vini disponibili in questa categoria.</p>';
if (empty($htmlRossi)) $htmlRossi = $msgVuoto;
if (empty($htmlBianchi)) $htmlBianchi = $msgVuoto;
if (empty($htmlSelezione)) $htmlSelezione = $msgVuoto;

// Caricamento e Parsing Template
$htmlContent = caricaPagina('../../html/vini.html');

// Sostituzione Placeholders
$htmlContent = str_replace("[vini_rossi]", $htmlRossi, $htmlContent);
$htmlContent = str_replace("[vini_bianchi]", $htmlBianchi, $htmlContent);
$htmlContent = str_replace("[vini_selezione]", $htmlSelezione, $htmlContent);

// Output
echo $htmlContent;
?>
