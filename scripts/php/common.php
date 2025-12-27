<?php
// common.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function caricaPagina($nomeFileHTML) {
    
    // Controllo esistenza file
    if (!file_exists($nomeFileHTML)) {
        return "Errore: Il template $nomeFileHTML non esiste.";
    }

    // Carico l'HTML
    $htmlContent = file_get_contents($nomeFileHTML);
    
    // Recupero dati sessione
    $paginaCorrente = basename($_SERVER['PHP_SELF']);
    $isLogged = isset($_SESSION['utente']);
    $ruolo = isset($_SESSION['ruolo']) ? $_SESSION['ruolo'] : null; // Recupero il ruolo se esiste

    // ---------------------------------------------------
    // A. LOGICA ICONA CARRELLO / MATITA
    // ---------------------------------------------------
    $cartIconHTML = "";

    // CASO ADMIN o AMMINISTRATORE (Mostra Matita)
    if ($isLogged && ($ruolo === 'admin' || $ruolo === 'amministratore')) {
        
        if ($paginaCorrente === 'editVini.php') {
            // Sono GIA' nella pagina di modifica -> Icona statica (colorata)
            $cartIconHTML = '
            <span class="current-page-icon" aria-current="page" title="Sei in Modifica Vini">
                <i class="fas fa-pen" aria-hidden="true"></i>
                <span class="visually-hidden">Modifica Vini (Pagina corrente)</span>
            </span>';
        } else {
            // Sono in altre pagine -> Link a Modifica
            $cartIconHTML = '
            <a href="editVini.php" title="Modifica Vini">
                <i class="fas fa-pen" aria-hidden="true"></i>
                <span class="visually-hidden">Modifica Vini</span>
            </a>';
        }

    } 
    // CASO UTENTE STANDARD o OSPITE (Mostra Carrello)
    else {
        
        if ($paginaCorrente === 'carrello.php' || $paginaCorrente === 'checkout.php' ) {
            $cartIconHTML = '
            <span class="current-page-icon" aria-current="page" title="Sei nel Carrello">
                <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                <span class="visually-hidden" lang="en">Shop (Pagina corrente)</span>
            </span>';
        } else {
            // Sono in altre pagine -> Link al Carrello
            $cartIconHTML = '
            <a href="carrello.php" title="Vai al carrello">
                <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                <span class="visually-hidden" lang="en">Shop</span>
            </a>';
        }
    }

    // ---------------------------------------------------
    // B. LOGICA ICONA UTENTE 
    // ---------------------------------------------------
    $userIconHTML = "";

    if ($isLogged) {
        // --- UTENTE LOGGATO ---
        if ($paginaCorrente === 'user.php' || $paginaCorrente === 'admin.php' || $paginaCorrente === 'amministratore.php') {
            // Se sono già nella pagina profilo
            $userIconHTML = '
            <span class="current-page-icon" aria-current="page" title="Sei nella tua Area Riservata">
                <i class="fas fa-user-circle" aria-hidden="true"></i>
                <span class="visually-hidden">Area Riservata (Pagina corrente)</span>
            </span>';
        } else {
            // Sono loggato ma altrove 
            $userIconHTML = '
            <a href="utente.php" title="Vai alla tua Area Riservata">
                <i class="fas fa-user-circle" aria-hidden="true"></i>
                <span class="visually-hidden">Area Riservata</span>
            </a>';
        }

    } else {
        // --- UTENTE NON LOGGATO ---
        if ($paginaCorrente === 'carrello.php') {
            $userIconHTML = '
            <a href="login.php?return=carrello.php" title="Accedi per completare l\'ordine">
                <i class="fas fa-key" aria-hidden="true"></i>
                <span class="visually-hidden">Accedi</span>
            </a>';
        } else {
            $userIconHTML = '
            <a href="login.php" title="Accedi">
                <i class="fas fa-key" aria-hidden="true"></i>
                <span class="visually-hidden">Accedi</span>
            </a>';
        }
    }

    // Sostituisco il nuovo segnaposto del carrello
    $htmlContent = str_replace("[cart_icon_link]", $cartIconHTML, $htmlContent);
    // Sostituisco il segnaposto utente
    $htmlContent = str_replace("[user_area_link]", $userIconHTML, $htmlContent);

    return $htmlContent;
}
?>