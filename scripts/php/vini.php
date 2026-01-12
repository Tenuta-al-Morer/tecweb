<?php
session_start();
require_once 'common.php';
require_once 'DBConnection.php';

use DB\DBConnection;

if (!isset($_SESSION['vini_qty'])) {
    $_SESSION['vini_qty'] = [];
}

$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$isPostUpdate = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_temp_qty']));

if (!$isPostUpdate && strpos($referer, 'vini.php') === false) {
    $_SESSION['vini_qty'] = [];
}

if ($isPostUpdate) {
    $id = (int)$_POST['id_vino'];
    $dir = $_POST['direction'];
    $stock = (int)$_POST['stock_max'];
    
    $valoreInserito = isset($_POST['quantita']) ? (int)$_POST['quantita'] : 1;
    if ($valoreInserito < 1) $valoreInserito = 1;
    
    if ($dir === 'minus') {
        $current = $valoreInserito - 1;
    } elseif ($dir === 'plus') {
        $current = $valoreInserito + 1;
    } else {
        $current = $valoreInserito;
    }

    if ($current < 1) $current = 1;
    if ($stock > 0 && $current > $stock) $current = $stock;
    
    $_SESSION['vini_qty'][$id] = $current;
    
    header("Location: vini.php#vino-$id");
    exit();
}

$db = new DBConnection();
try {
    $tuttiIVini = $db->getVini();
} catch (Exception $e) {
    error_log("Errore recupero vini: " . $e->getMessage());
    $tuttiIVini = [];
}
$db->closeConnection();

$htmlRossi = "";
$htmlBianchi = "";
$htmlSelezione = "";

function costruisciCardVino($vino) {
    $id = (int)$vino['id'];
    $nome = htmlspecialchars($vino['nome']);
    $prezzo = number_format($vino['prezzo'], 2, ',', '.');
    $img = htmlspecialchars($vino['img']);
    $descBreve = htmlspecialchars($vino['descrizione_breve']);
    $descEstesa = htmlspecialchars($vino['descrizione_estesa']);
    $stock = (int)$vino['quantita_stock'];
    
    $vitigno = htmlspecialchars($vino['vitigno'] ?? '-');
    $annata = htmlspecialchars($vino['annata'] ?? '-');
    $gradazione = htmlspecialchars($vino['gradazione'] ?? '-');
    $temperatura = htmlspecialchars($vino['temperatura'] ?? '-');
    $abbinamenti = htmlspecialchars($vino['abbinamenti'] ?? '-');

    $modalId = "modal-vino-" . $id;
    $anchorId = "vino-" . $id;

    $qtySession = isset($_SESSION['vini_qty'][$id]) ? $_SESSION['vini_qty'][$id] : 1;
    if ($qtySession > $stock && $stock > 0) $qtySession = $stock;

    $htmlStock = "";
    $cardActionHTML = "";
    $modalActionHTML = "";
    $altText = "Bottiglia di " . $nome;

    if ($stock <= 0) {
        $altText .= " - Esaurito";
        
        $cardActionHTML = '
        <div class="card-actions card-actions-wrapper">
             <div class="card-buy-block">
                <button class="badge-esaurito" disabled>Esaurito</button>
             </div>
             <label for="' . $modalId . '" class="details-button" aria-label="Maggiori informazioni su ' . $nome . '">Info</label>
        </div>';
        
        $modalActionHTML = '
        <div class="modal-buy-block">
              <span class="badge-esaurito">Esaurito</span>
        </div>';

    } else {
        $testoStock = ($stock <= 20) ? "Ultimi " . $stock . " pezzi!" : "Disponibile";
        $classeStock = ($stock <= 20) ? "stock-warning" : "stock-ok";
        $htmlStock = '<p class="stock-info ' . $classeStock . '"><i class="fas fa-check-circle"></i> ' . $testoStock . '</p>';

        $selectorHTML = '
        <div class="selettore-quantita">
            <button type="submit" name="direction" value="minus" formaction="vini.php" class="btn-minus" aria-label="Riduci quantità">-</button>
            <input type="number" name="quantita" value="' . $qtySession . '" class="display-qty" min="1" max="' . $stock . '" aria-label="Quantità">
            <button type="submit" name="direction" value="plus" formaction="vini.php" class="btn-plus" aria-label="Aumenta quantità">+</button>
        </div>';

        $cardActionHTML = '
        <form action="vini.php" method="POST" class="wine-form">
            <input type="hidden" name="action" value="aggiungi">
            <input type="hidden" name="id_vino" value="' . $id . '">
            <input type="hidden" name="stock_max" value="' . $stock . '">
            <input type="hidden" name="update_temp_qty" value="1">

            <div class="card-actions">
                <div class="card-buy-block">
                    ' . $selectorHTML . '
                    <button type="submit" class="buy-button">Acquista</button>
                </div>
                <label for="' . $modalId . '" class="details-button" aria-label="Maggiori informazioni su ' . $nome . '">Info</label>
            </div>
        </form>';

        $modalActionHTML = '
        <form action="vini.php" method="POST" class="modal-wine-form">
            <input type="hidden" name="action" value="aggiungi">
            <input type="hidden" name="id_vino" value="' . $id . '">
            <input type="hidden" name="stock_max" value="' . $stock . '">
            <input type="hidden" name="update_temp_qty" value="1">
            
            <div class="modal-buy-block">
                ' . $selectorHTML . '
                <button type="submit" class="buy-button modal-btn-large">Aquista</button>
            </div>
        </form>';
    }

    return '
    <article class="wine-article" id="' . $anchorId . '">
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

$msgVuoto = '<p class="no-wines">Al momento non ci sono vini disponibili in questa categoria.</p>';
if (empty($htmlRossi)) $htmlRossi = $msgVuoto;
if (empty($htmlBianchi)) $htmlBianchi = $msgVuoto;
if (empty($htmlSelezione)) $htmlSelezione = $msgVuoto;

$htmlContent = caricaPagina('../../html/vini.html');
$htmlContent = str_replace("[vini_rossi]", $htmlRossi, $htmlContent);
$htmlContent = str_replace("[vini_bianchi]", $htmlBianchi, $htmlContent);
$htmlContent = str_replace("[vini_selezione]", $htmlSelezione, $htmlContent);

echo $htmlContent;
?>
