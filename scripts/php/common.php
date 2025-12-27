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
    $paginaCorrente = basename($_SERVER['PHP_SELF']);
    
    $isLogged = isset($_SESSION['utente']); 

    if ($isLogged) {
        // --- UTENTE LOGGATO ---


        if ($paginaCorrente === 'user.php' || $paginaCorrente === 'admin.php') {
            // Se sono già nella pagina profilo, mostro solo l'icona (senza link)
            $userIconHTML = '
            <span class="current-page-icon" aria-current="page" title="Sei nella tua Area Riservata">
                <i class="fas fa-user-circle" style="color: var(--primary-color);" aria-hidden="true"></i>
                <span class="visually-hidden">Area Riservata (Pagina corrente)</span>
            </span>';
        } else {
            // Sono loggato ma in un'altra pagina -> Link all'area riservata
            $userIconHTML = '
            <a href="utente.php" title="Vai alla tua Area Riservata">
                <i class="fas fa-user-circle" aria-hidden="true"></i>
                <span class="visually-hidden">Area Riservata</span>
            </a>';
        }

    } else {
        // --- UTENTE NON LOGGATO ---

        if ($paginaCorrente === 'carrello.php') {
            // Sono nel carrello e non loggato -> Link al login con redirect al carrello
            $userIconHTML = '
            <a href="login.php?return=carrello.php" title="Accedi per completare l\'ordine">
                <i class="fas fa-key" aria-hidden="true"></i>
                <span class="visually-hidden">Accedi</span>
            </a>';
        } else {
            // Non loggato, pagina generica -> Link al login standard
            $userIconHTML = '
            <a href="login.php" title="Accedi">
                <i class="fas fa-key" aria-hidden="true"></i>
                <span class="visually-hidden">Accedi</span>
            </a>';
        }
    }

    // 4. Sostituzione del placeholder
    $htmlContent = str_replace("[user_area_link]", $userIconHTML, $htmlContent);

    return $htmlContent;
}
?>