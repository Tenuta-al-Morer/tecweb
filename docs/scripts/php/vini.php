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
    $img = str_replace(' ', '%20', htmlspecialchars($vino['img']));
    $descBreve = htmlspecialchars($vino['descrizione_breve']);
    $descEstesa = htmlspecialchars($vino['descrizione_estesa']);
    $stock = (int)$vino['quantita_stock'];
    
    $vitigno = htmlspecialchars($vino['vitigno'] ?? '-');
    $annata = htmlspecialchars($vino['annata'] ?? '-');
    $gradazione = htmlspecialchars($vino['gradazione'] ?? '-');
    $temperatura = htmlspecialchars($vino['temperatura'] ?? '-');
    $abbinamenti = htmlspecialchars($vino['abbinamenti'] ?? '-');

    $anchorId = "vino-" . $id;
    $modalId = "modal-vino-" . $id;
    $modalId = preg_replace('/[^A-Za-z0-9\-_:.]/', '_', $modalId);
    $dialogId = "dialog-" . $id;
    $titleId = "modal-title-" . $id;
    $priceId = "modal-price-" . $id; 
    $descId = "modal-desc-" . $id;
    $descrModaleId = "descr-modale-" . $id;
    $descrTabellaId = "descr-tabella-" . $id;
    $specsCaptionId = "modal-specs-cap-" . $id;

    $qtySession = isset($_SESSION['vini_qty'][$id]) ? $_SESSION['vini_qty'][$id] : 1;
    if ($qtySession > $stock && $stock > 0) $qtySession = $stock;

    $htmlStock = "";
    $cardActionHTML = "";
    $modalActionHTML = "";
    
    $triggerHTML = '
    <label for="' . $modalId . '" class="details-button" tabindex="0">
        Info <span class="visually-hidden">: scheda di ' . $nome . '</span>
    </label>';

    $closeButtonsHTML = '
        <label for="' . $modalId . '" class="modal-close-btn" tabindex="0">
            <span aria-hidden="true">&times;</span>
            <span class="visually-hidden">Chiudi scheda ' . $nome . '</span>
        </label>
    ';

    if ($stock <= 0) {
        $cardActionHTML = '
        <div class="card-actions esaurito-wrapper">
             <span class="badge-esaurito card-action">Esaurito</span>
             ' . $triggerHTML . '
        </div>';
        
        $modalActionHTML = '<div class="modal-buy-block"><span class="badge-esaurito">Esaurito</span></div>';

    } else {
        $testoStock = ($stock <= 20) ? "Ultimi " . $stock . " pezzi!" : "Disponibile";
        $classeStock = ($stock <= 20) ? "stock-warning" : "stock-ok";
        $htmlStock = '<p class="stock-info ' . $classeStock . '"><span class="fas fa-check-circle"></span> ' . $testoStock . '</p>';

        $selectorHTML = '
        <div class="selettore-quantita card-action">
            <button type="submit" name="direction" value="minus" formaction="vini.php" class="btn-minus">
                - <span class="visually-hidden">Riduci quantità di ' . $nome . ' (scheda)</span>
            </button>
            <input type="number" name="quantita" value="' . $qtySession . '" class="display-qty" min="1" max="' . $stock . '" aria-label="Quantità da acquistare di ' . $nome . ' (scheda)">
            <button type="submit" name="direction" value="plus" formaction="vini.php" class="btn-plus">
                + <span class="visually-hidden">Aumenta quantità di ' . $nome . ' (scheda)</span>
            </button>
        </div>';
        $selectorModal = '
        <div class="selettore-quantita card-action">
            <button type="submit" name="direction" value="minus" formaction="vini.php" class="btn-minus">
                - <span class="visually-hidden">Riduci quantità di ' . $nome . ' (modale)</span>
            </button>
            <input type="number" name="quantita" value="' . $qtySession . '" class="display-qty" min="1" max="' . $stock . '" aria-label="Quantità da acquistare di ' . $nome . ' (modale)">
            <button type="submit" name="direction" value="plus" formaction="vini.php" class="btn-plus">
                + <span class="visually-hidden">Aumenta quantità di ' . $nome . ' (modale)</span>
            </button>
        </div>';

        $cardActionHTML = '
        <form action="carrello.php" method="POST" class="wine-form">
            <input type="hidden" name="action" value="aggiungi">
            <input type="hidden" name="return_url" value="vini.php"> 
            <input type="hidden" name="id_vino" value="' . $id . '">
            <input type="hidden" name="stock_max" value="' . $stock . '">
            <input type="hidden" name="update_temp_qty" value="1">

            <div class="card-actions">
                <div class="card-buy-block">
                    ' . $selectorHTML . '
                    <button type="submit" class="buy-button card-action">
                        Acquista <span class="visually-hidden">' . $nome . ' (scheda)</span>
                    </button>
                </div>
                ' . $triggerHTML . '
            </div>
        </form>';

        $modalActionHTML = '
        <form action="carrello.php" method="POST" class="modal-wine-form">
            <input type="hidden" name="action" value="aggiungi">
            <input type="hidden" name="return_url" value="vini.php">
            <input type="hidden" name="id_vino" value="' . $id . '">
            <input type="hidden" name="stock_max" value="' . $stock . '">
            <input type="hidden" name="update_temp_qty" value="1">
            
            <div class="modal-buy-block">
                ' . $selectorModal . '
                <button type="submit" class="buy-button modal-btn-large">
                    Acquista <span class="visually-hidden">' . $nome . ' (modale)</span>
                </button>
            </div>
        </form>';
    }

    return '
    <article class="wine-article" id="' . $anchorId . '">
        <div class="wine-item">
            <img src="' . $img . '" alt="" class="wine-image" loading="lazy">
            <div class="content-wine-article">
                <h3>' . $nome . '</h3>
                <p>' . $descBreve . '</p> 
                ' . $htmlStock . '
            </div>
        </div>
        
        ' . $cardActionHTML . '

        <input type="checkbox" id="' . $modalId . '" class="modal-toggle-checkbox visually-hidden" aria-label="Mostra dettagli e specifiche per ' . $nome . '">
        
        <div class="modal-overlay">
            
            <div id="' . $dialogId . '" 
                 class="modal-content"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="' . $titleId . '"
                 aria-describedby="' . $descrModaleId . '" tabindex="0">

                <p id="' . $descrModaleId . '" class="sum visually-hidden">
                    Scheda completa di ' . $nome . ', inclusi prezzi, descrizione estesa e tabella tecnica.
                </p>

                <div class="modal-grid">
                    <div class="modal-img-col">
                        <img src="' . $img . '" alt="">
                    </div>

                    <div class="modal-info-col"> 
                        <h2 id="' . $titleId . '">' . $nome . '</h2>

                        <p class="modal-price" id="' . $priceId . '"><span class="bold">Prezzo:</span> € ' . $prezzo . '</p>

                        <p class="modal-desc" id="' . $descId . '">' . $descEstesa . '</p>

                        <p id="' . $descrTabellaId . '" class="sum">
                            La tabella riassume le specifiche tecniche del vino: vitigno, annata, gradazione, temperatura, abbinamenti.
                        </p>

                        <div class="modal-specs">
                            <table class="modal-specs-table" aria-describedby="'. $descrTabellaId . '">
                                <caption class="sr-only" id="' . $specsCaptionId . '">Specifiche tecniche di ' . $nome . '</caption>
                                <tbody>
                                    <tr><th scope="row">Vitigno</th><td>' . $vitigno . '</td></tr>
                                    <tr><th scope="row">Annata</th><td>' . $annata . '</td></tr>
                                    <tr><th scope="row">Gradi</th><td>' . $gradazione . '</td></tr>
                                    <tr><th scope="row">Temperatura</th><td>' . $temperatura . '</td></tr>
                                    <tr><th scope="row">Abbinamenti</th><td>' . $abbinamenti . '</td></tr>
                                </tbody>
                            </table>
                        </div>

                        ' . $modalActionHTML . '
                    </div>
                </div>
                ' . $closeButtonsHTML . '
            </div>
            
            <label for="' . $modalId . '" class="modal-backdrop-close">
                <span class="visually-hidden">Chiudi finestra modale di ' . $nome . '</span>
            </label>
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

$htmlDatalist = '<datalist id="suggerimenti-vini">';
foreach ($tuttiIVini as $vino) {
    $nomeVino = htmlspecialchars($vino['nome']); 
    $htmlDatalist .= '<option value="' . $nomeVino . '">';
}
$htmlDatalist .= '</datalist>';

$htmlContent = str_replace("</body>", $htmlDatalist . "</body>", $htmlContent);

echo $htmlContent;
?>