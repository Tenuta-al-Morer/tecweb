<?php
// common.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/DBConnection.php';
use DB\DBConnection;

// Variabile per il conteggio carrello
$cartQty = 0;

$db = new DBConnection();

if (isset($_SESSION['utente_id'])) {
    
    // Controllo stato utente
    $userFresco = $db->checkUserStatus($_SESSION['utente_id']);

    if ($userFresco) {
        $_SESSION['ruolo'] = $userFresco['ruolo'];
        $_SESSION['nome'] = $userFresco['nome'];
        $_SESSION['cognome'] = $userFresco['cognome'];

        // --- CALCOLO QUANTITÀ CARRELLO (LOGGATO) ---
        if ($_SESSION['ruolo'] !== 'staff') {
            $carrelloItems = $db->getCarrelloUtente($_SESSION['utente_id']);
            foreach ($carrelloItems as $item) {
                if ($item['stato'] === 'attivo' || $item['stato'] === 'active') {
                    $cartQty += (int)$item['quantita'];
                }
            }
        }
        
    } else {
        $db->closeConnection(); // Chiudo prima di redirect
        session_unset();
        session_destroy();
        header("Location: login.php?error=session_expired");
        exit();
    }
} else {
    // --- CALCOLO QUANTITÀ CARRELLO (OSPITE) ---
    if (isset($_SESSION['guest_cart']) && is_array($_SESSION['guest_cart'])) {
        $cartQty = array_sum($_SESSION['guest_cart']);
    }
}

$db->closeConnection();


function caricaPagina($nomeFileHTML, $extraReplacements = []) {
    // Uso global per accedere alla variabile calcolata sopra
    global $cartQty;

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

    $badgeHTML = "";
    if ($cartQty > 0) {
        // Se il numero è > 99 mostra "99+" per non rompere il layout
        $displayQty = ($cartQty > 99) ? '99+' : $cartQty;
        $badgeHTML = '<span class="cart-badge" id="global-cart-badge">' . $displayQty . '</span>';
    }

    // ---------------------------------------------------
    // A. LOGICA ICONA PRIMARIA (CARRELLO o MATITA)
    // ---------------------------------------------------
    $cartIconHTML = "";

    // 1. CASO ADMIN (Amministrazione)
    if ($isLogged && $ruolo === 'admin') {
        if ($paginaCorrente === 'admin.php') {
            $cartIconHTML = '
            <span class="current-page-icon" aria-current="page" title="Sei in Amministrazione">
                <i class="fas fa-edit" aria-hidden="true"></i>
                <span class="visually-hidden">Amministrazione (Pagina corrente)</span>
            </span>';
        } else {
            $cartIconHTML = '
            <a href="admin.php" title="Vai a Amministrazione">
                <i class="fas fa-edit" aria-hidden="true"></i>
                <span class="visually-hidden">Amministrazione</span>
            </a>';
        }
    } 
    // 2. CASO STAFF: Non vede niente
    elseif ($isLogged && $ruolo === 'staff') {
        $cartIconHTML = ""; 
    }
    // 3. CASO UTENTE STANDARD o OSPITE: Vede il Carrello CON BADGE
    else {
        if ($paginaCorrente === 'carrello.php' || $paginaCorrente === 'checkout.php' ) {
            $cartIconHTML = '
            <span class="current-page-icon cart-icon-container" aria-current="page" title="Sei nel Carrello">
                <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                ' . $badgeHTML . '
                <span class="visually-hidden" lang="en">Shop (Pagina corrente)</span>
            </span>';
        } else {
            $cartIconHTML = '
            <a href="carrello.php" title="Vai al carrello" class="cart-icon-container">
                <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                ' . $badgeHTML . '
                <span class="visually-hidden" lang="en">Shop</span>
            </a>';
        }
    }
    
    // ---------------------------------------------------
    // B. LOGICA ICONA SECONDARIA (UTENTE o TABELLA)
    // ---------------------------------------------------
    $userIconHTML = "";

    if ($isLogged) {
        
        // --- ADMIN o staff (Vedono gestionale) ---
        if ($ruolo === 'admin' || $ruolo === 'staff') {
            
            // Entrambi mandano a utente.php 
            if ($paginaCorrente === 'gestionale.php' || $paginaCorrente === 'staff.php') {
                $userIconHTML = '
                <span class="current-page-icon" aria-current="page" title="Sei in Gestionale">
                    <i class="fas fa-sliders-h" aria-hidden="true"></i>
                    <span class="visually-hidden">Gestionale (Pagina corrente)</span>
                </span>';
            } else {
                $userIconHTML = '
                <a href="utente.php" title="Vai a Gestionale">
                    <i class="fas fa-sliders-h" aria-hidden="true"></i>
                    <span class="visually-hidden">Gestionale</span>
                </a>';
            }
        } 
        // --- UTENTE STANDARD (Vede l'omino) ---
        else {
            if ($paginaCorrente === 'areaPersonale.php') {
                $userIconHTML = '
                <span class="current-page-icon" aria-current="page" title="Sei nella tua Area Riservata">
                    <i class="fas fa-user" aria-hidden="true"></i>
                    <span class="visually-hidden">Area Riservata (Pagina corrente)</span>
                </span>';
            } else {
                $userIconHTML = '
                <a href="utente.php" title="Vai alla tua Area Riservata">
                    <i class="fas fa-user" aria-hidden="true"></i>
                    <span class="visually-hidden">Area Riservata</span>
                </a>';
            }
        }

    } else {
        // --- OSPITE (NON LOGGATO) ---
        if ($paginaCorrente === 'carrello.php') {
            $userIconHTML = '
            <a href="login.php?return=carrello.php" title="Accedi per completare l\'ordine">
                <i class="fas fa-user-alt-slash" aria-hidden="true"></i>
                <span class="visually-hidden">Accedi</span>
            </a>';
        } else {
            $userIconHTML = '
            <a href="login.php" title="Vai alla pagina di login">
                <i class="fas fa-user-alt-slash" aria-hidden="true"></i>
                <span class="visually-hidden">Accedi</span>
            </a>';
        }
    }


    $footer = '
        <footer>
            <div class="footer-content">
                <small>
                    &copy; 2025 Tenuta al Morer - <abbr title="Partita">P.</abbr> <abbr title="Imposta sul Valore Aggiunto">IVA</abbr> 00000000000 <br>
                    <a href="policy.php">Note legali</a> |
                    <a href="policy.php#privacy-policy"><span lang="en">  Privacy Policy</span></a> | 
                    <a href="policy.php#accessibility">  Accessibilità</a> |
                    <a href="mappa.php">Mappa del sito</a>  
                </small>
            </div>
        </footer>';

    $backToTopBtn = '<button id="backToTopBtn" aria-label="Torna all\'inizio della pagina">
            <svg viewBox="0 0 24 24" class="arrow-icon" aria-hidden="true">
                <path d="M12 5 L12 19 M5 12 L12 5 L19 12"/>
            </svg>
        </button>';

    $menuTogle = '<input type="checkbox" id="menu-checkbox" class="menu-checkbox visually-hidden">

            <label for="menu-checkbox" class="menu-toggle" aria-controls="main-navigation" aria-expanded="false">
                <i class="fas fa-bars" aria-hidden="true"></i>
                <span class="visually-hidden">Apri il menu di navigazione</span>
            </label>';


    // Sostituisco il segnaposto del carrello/matita
    $htmlContent = str_replace("[cart_icon_link]", $cartIconHTML, $htmlContent);
    // Sostituisco il segnaposto utente/tabella
    $htmlContent = str_replace("[user_area_link]", $userIconHTML, $htmlContent);
    // Sostituisco footer
    $htmlContent = str_replace("[footer]", $footer, $htmlContent);
    // Sostituisco back to top button
    $htmlContent = str_replace("[back_to_top_button]", $backToTopBtn, $htmlContent);

    $htmlContent = str_replace("[menu_toggle]", $menuTogle, $htmlContent);

    if (!empty($extraReplacements)) {
        foreach ($extraReplacements as $placeholder => $replacement) {
            $htmlContent = str_replace($placeholder, $replacement, $htmlContent);
        }
    }

    return $htmlContent;
}
?>
