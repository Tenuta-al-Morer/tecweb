<?php
require_once 'common.php';

// Valori di default 
$backLink = 'home.php';
$backText = 'Torna alla Home';
$iconClass = 'fa-arrow-left';
$extraAttr = ''; 

// Richiesta di chiusura finestra
if (isset($_GET['action']) && $_GET['action'] === 'close') {
    $backLink = '#';
    $backText = 'Chiudi finestra';
    $iconClass = 'fa-times';
    // Aggiungiamo l'onclick per chiudere la scheda
    $extraAttr = 'onclick="window.close(); return false;"';
} 
// Rilevamento automatico pagina precedente 
elseif (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    $host = $_SERVER['HTTP_HOST'];

    if (strpos($referer, $host) !== false && strpos($referer, 'policy.php') === false) {
        $backLink = $referer; 
        $backText = 'Pagina precedente';
    }
}

$pagina = caricaPagina('../../html/policy.html');

$sostituzioni = [
    '[BACK_LINK]'  => htmlspecialchars($backLink),
    '[BACK_ATTR]'  => $extraAttr,                 
    '[ICON_CLASS]' => $iconClass,                  
    '[BACK_TEXT]'  => $backText                    
];

foreach ($sostituzioni as $placeholder => $valore) {
    $pagina = str_replace($placeholder, $valore, $pagina);
}

echo $pagina;
?>