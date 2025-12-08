<?php
// common.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function caricaPagina($nomeFileHTML) {
    
    // 1. Controllo esistenza file
    if (!file_exists($nomeFileHTML)) {
        return "Errore: Il template $nomeFileHTML non esiste.";
    }

    // 2. Carico l'HTML
    $htmlContent = file_get_contents($nomeFileHTML);

    // 3. Logica ICONA UTENTE
    $userIconHTML = "";
    
    // Recupero il nome del file corrente (es. 'utente.php' o 'index.php')
    $paginaCorrente = basename($_SERVER['PHP_SELF']);

    if(isset($_SESSION['utente'])) {
        // UTENTE LOGGATO
        
        // Controllo se sono GIA' nella pagina utente o admin
        if ($paginaCorrente === 'utente.php' || $paginaCorrente === 'admin.php') {
            // Sono già qui: NIENTE LINK, solo icona visiva + aria-current
            $userIconHTML = '
            <span class="current-page-icon" aria-current="page" title="Sei nella tua Area Riservata">
                <i class="fas fa-user" style="color: var(--primary-color);" aria-hidden="true"></i>
                <span class="visually-hidden">Area Riservata (Pagina corrente)</span>
            </span>';
        } else {
            // Sono altrove: Mostro il LINK
            $userIconHTML = '
            <a href="utente.php" title="Vai alla tua Area Riservata">
                <i class="fas fa-user" aria-hidden="true"></i>
                <span class="visually-hidden">Area Riservata</span>
            </a>';
        }

    } else {
        // OSPITE (Non loggato): Link al Login
        // Nota: se sei già su login.php potresti voler fare lo stesso controllo, 
        // ma per ora lo lasciamo standard come da tua richiesta.
        $userIconHTML = '
        <a href="login.php" title="Accedi">
            <i class="fas fa-sign-in-alt contact-icon" aria-hidden="true"></i>
            <span class="visually-hidden">Accedi</span>
        </a>';
    }

    // 4. Sostituzione del placeholder
    $htmlContent = str_replace("[user_area_link]", $userIconHTML, $htmlContent);

    return $htmlContent;
}
?>