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
    $ruolo = isset($_SESSION['ruolo']) ? $_SESSION['ruolo'] : null;

    // ---------------------------------------------------
    // A. LOGICA ICONA PRIMARIA (CARRELLO o MATITA)
    // ---------------------------------------------------
    $cartIconHTML = "";

    // 1. CASO ADMIN: Vede la Matita
    if ($isLogged && $ruolo === 'admin') {
        
        if ($paginaCorrente === 'admin.php') {
            // Icona statica se sono già in modifica
            $cartIconHTML = '
            <span class="current-page-icon" aria-current="page" title="Sei in Modifica Vini">
                <i class="fas fa-pen" aria-hidden="true"></i>
                <span class="visually-hidden">Modifica Vini (Pagina corrente)</span>
            </span>';
        } else {
            // Link a Modifica
            $cartIconHTML = '
            <a href="admin.php" title="Modifica Vini">
                <i class="fas fa-pen" aria-hidden="true"></i>
                <span class="visually-hidden">Modifica Vini</span>
            </a>';
        }
    } 
    // 2. CASO staff: NON vede la Matita (né il carrello)
    elseif ($isLogged && $ruolo === 'staff') {
        $cartIconHTML = ""; // Vuoto intenzionale
    }
    // 3. CASO UTENTE STANDARD o OSPITE: Vede il Carrello
    else {
        
        if ($paginaCorrente === 'carrello.php' || $paginaCorrente === 'checkout.php' ) {
            $cartIconHTML = '
            <span class="current-page-icon" aria-current="page" title="Sei nel Carrello">
                <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                <span class="visually-hidden" lang="en">Shop (Pagina corrente)</span>
            </span>';
        } else {
            $cartIconHTML = '
            <a href="carrello.php" title="Vai al carrello">
                <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                <span class="visually-hidden" lang="en">Shop</span>
            </a>';
        }
    }

    // ---------------------------------------------------
    // B. LOGICA ICONA SECONDARIA (UTENTE o TABELLA)
    // ---------------------------------------------------
    $userIconHTML = "";

    if ($isLogged) {
        
        // --- ADMIN o staff (Vedono la Tabella) ---
        if ($ruolo === 'admin' || $ruolo === 'staff') {
            
            // Entrambi mandano a utente.php 
            if ($paginaCorrente === 'gestionale.php' || $paginaCorrente === 'staff.php') {
                $userIconHTML = '
                <span class="current-page-icon" aria-current="page" title="Sei nella Dashboard">
                    <i class="fas fa-table" aria-hidden="true"></i>
                    <span class="visually-hidden">Dashboard (Pagina corrente)</span>
                </span>';
            } else {
                $userIconHTML = '
                <a href="utente.php" title="Vai alla Dashboard">
                    <i class="fas fa-table" aria-hidden="true"></i>
                    <span class="visually-hidden">Dashboard</span>
                </a>';
            }
        } 
        // --- UTENTE STANDARD (Vede l'omino) ---
        else {
            if ($paginaCorrente === 'areaPersonale.php') {
                $userIconHTML = '
                <span class="current-page-icon" aria-current="page" title="Sei nella tua Area Riservata">
                    <i class="fas fa-user-circle" aria-hidden="true"></i>
                    <span class="visually-hidden">Area Riservata (Pagina corrente)</span>
                </span>';
            } else {
                $userIconHTML = '
                <a href="utente.php" title="Vai alla tua Area Riservata">
                    <i class="fas fa-user-circle" aria-hidden="true"></i>
                    <span class="visually-hidden">Area Riservata</span>
                </a>';
            }
        }

    } else {
        // --- OSPITE (NON LOGGATO) ---
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

    // Sostituisco il segnaposto del carrello/matita
    $htmlContent = str_replace("[cart_icon_link]", $cartIconHTML, $htmlContent);
    // Sostituisco il segnaposto utente/tabella
    $htmlContent = str_replace("[user_area_link]", $userIconHTML, $htmlContent);

    return $htmlContent;
}
?>