<?php
session_start();
require_once 'common.php';
require_once 'DBConnection.php';

use DB\DBConnection;

// ============================================================
// 1. GESTIONE AZIONI (AJAX & STANDARD)
// ============================================================

if (isset($_REQUEST['action'])) {
    
    $action = $_REQUEST['action'];
    $id_vino = isset($_REQUEST['id_vino']) ? (int)$_REQUEST['id_vino'] : 0;
    $id_riga = isset($_REQUEST['id_riga']) ? (int)$_REQUEST['id_riga'] : 0;
    $current_qty = isset($_REQUEST['current_qty']) ? (int)$_REQUEST['current_qty'] : 1; 

    $db = new DBConnection();

    $new_qty = $current_qty;
    $qty_to_add = 1; 

    if ($action === 'piu') {
        $new_qty = $current_qty + 1;
    } elseif ($action === 'meno') {
        $new_qty = $current_qty - 1;
    } elseif ($action === 'aggiorna_quantita') {
        $new_qty = isset($_REQUEST['quantita']) ? (int)$_REQUEST['quantita'] : 1;
    } elseif ($action === 'aggiungi') {
        $qty_to_add = isset($_REQUEST['quantita']) ? (int)$_REQUEST['quantita'] : 1;
    }

    if ($action === 'piu') {
        $new_qty = $current_qty + 1;
    } elseif ($action === 'meno') {
        $new_qty = $current_qty - 1;
    } elseif ($action === 'aggiorna_quantita') {
        $new_qty = isset($_REQUEST['quantita']) ? (int)$_REQUEST['quantita'] : 1;
    } elseif ($action === 'aggiungi') {
        $qty_to_add = isset($_REQUEST['quantita']) ? (int)$_REQUEST['quantita'] : 1;
    }

    if ($new_qty > 100) {
        $new_qty = 100;
    }

    // --- LOGICA DB ---
    if (isset($_SESSION['utente'])) {
        $id_utente = $_SESSION['utente_id'];

        if ($action === 'aggiungi') {
            $db->aggiungiAlCarrello($id_utente, $id_vino, $qty_to_add);
        } 
        elseif ($action === 'rimuovi') {
            if($id_riga > 0) $db->rimuoviDaCarrello($id_riga);
            else $db->aggiornaQuantitaCarrello($id_utente, $id_vino, 0);
        }
        elseif ($action === 'salva_per_dopo') {
            if($id_riga > 0) $db->cambiaStatoElemento($id_riga, 'salvato');
        }
        elseif ($action === 'sposta_in_carrello') {
            if($id_riga > 0) $db->cambiaStatoElemento($id_riga, 'attivo');
        }
        else {
            $db->aggiornaQuantitaCarrello($id_utente, $id_vino, $new_qty);
        }
    } 
    else {
        if (!isset($_SESSION['guest_cart'])) {
            $_SESSION['guest_cart'] = [];
        }

        if ($action === 'aggiungi') {
            if (isset($_SESSION['guest_cart'][$id_vino])) {
                $_SESSION['guest_cart'][$id_vino] += $qty_to_add;
            } else {
                $_SESSION['guest_cart'][$id_vino] = $qty_to_add;
            }
        } 
        elseif ($new_qty <= 0 || $action === 'rimuovi') {
            if (isset($_SESSION['guest_cart'][$id_vino])) {
                unset($_SESSION['guest_cart'][$id_vino]);
            }
        } 
        else {
            $_SESSION['guest_cart'][$id_vino] = $new_qty;
        }
    }

    // --- CALCOLO TOTALI PER RISPOSTA AJAX ---
    $newTotalProd = 0;
    $newCount = 0;
    $finalTotal = 0;
    $shipMsg = "";

    if (isset($_SESSION['utente'])) {
        $cartData = $db->getCarrelloUtente($_SESSION['utente_id']);
        foreach($cartData as $c) {
            if($c['stato'] === 'attivo' || $c['stato'] === 'active'){
                $newTotalProd += $c['totale_riga'];
                $newCount += $c['quantita'];
            }
        }
    } else {
        if(isset($_SESSION['guest_cart'])) {
            foreach ($_SESSION['guest_cart'] as $idV => $q) {
                $v = $db->getVino($idV);
                if($v && $v['quantita_stock'] > 0){
                    $newTotalProd += $v['prezzo'] * $q;
                    $newCount += $q;
                }
            }
        }
    }
    
    $db->closeConnection();

    $soglia = 49.00;
    $costoStd = 10.00;
    $shipCost = ($newTotalProd == 0) ? 0 : (($newTotalProd >= $soglia) ? 0 : $costoStd);
    $finalTotal = $newTotalProd + $shipCost;
    $shipMsg = ($shipCost == 0 && $newTotalProd > 0) ? '<span style="color:#007600; font-weight:bold;">Gratuita</span>' : '€ ' . number_format($shipCost, 2);
    if($newTotalProd == 0) $shipMsg = '€ 0.00';

    // --- RISPOSTA JSON SE RICHIESTO ---
    if (isset($_REQUEST['ajax_mode']) && $_REQUEST['ajax_mode'] == '1') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'qty' => $new_qty,
            'id_riga' => $id_riga,
            'cart_count' => $newCount,
            'total_products' => number_format($newTotalProd, 2),
            'shipping' => $shipMsg,
            'total_final' => number_format($finalTotal, 2)
        ]);
        exit();
    }
    
    // Fallback standard
    header("Location: carrello.php");
    exit();
}

// ============================================================
// 2. LOGICA VISUALIZZAZIONE
// ============================================================

$userLinkHTML = '';
if (isset($_SESSION['utente'])) {
    $targetPage = ($_SESSION['ruolo'] === 'admin') ? 'admin.php' : 'user.php';
    $userLinkHTML = '<a href="' . $targetPage . '" aria-label="Area Personale"><i class="fas fa-user-check"></i></a>';
} else {
    $userLinkHTML = '<a href="login.php" aria-label="Accedi o Registrati"><i class="fas fa-user"></i></a>';
}

$db = new DBConnection();
$allItems = [];
$activeItems = [];
$savedItems = []; 

$totaleProdotti = 0;
$numArticoli = 0;
$isLogged = isset($_SESSION['utente']);
$alertMsgHTML = ""; 

if ($isLogged) {
    $allItems = $db->getCarrelloUtente($_SESSION['utente_id']);
    foreach ($allItems as $item) {
        $stock = $item['quantita_stock'];
        $qty = $item['quantita'];
        $idR = $item['id_riga'];
        $stato = $item['stato'];

        if ($stato === 'active' || $stato === 'attivo') {
            if ($stock <= 0) {
                $db->cambiaStatoElemento($idR, 'salvato');
                $item['stato'] = 'salvato'; 
                $alertMsgHTML = '
                <div class="alert-bar">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Alcuni articoli non sono più disponibili e sono stati spostati in fondo alla lista.</span>
                </div>';
            }
        }

        if ($item['stato'] === 'salvato') {
            $savedItems[] = $item;
        } else {
            $activeItems[] = $item;
            $totaleProdotti += $item['totale_riga'];
            $numArticoli += $item['quantita'];
        }
    }
} else {
    if (isset($_SESSION['guest_cart']) && !empty($_SESSION['guest_cart'])) {
        foreach ($_SESSION['guest_cart'] as $idVino => $qty) {
            $vino = $db->getVino($idVino);
            if ($vino) {
                if ($vino['quantita_stock'] <= 0) {
                    $savedItems[] = array_merge($vino, [
                        'id_riga' => 0, 'id_vino' => $idVino, 'quantita' => $qty, 
                        'totale_riga' => 0, 'stato' => 'salvato'
                    ]);
                } else {
                    $totaleRiga = $vino['prezzo'] * $qty;
                    $totaleProdotti += $totaleRiga;
                    $numArticoli += $qty;
                    $activeItems[] = array_merge($vino, [
                        'id_riga' => 0, 'id_vino' => $idVino, 'quantita' => $qty, 
                        'totale_riga' => $totaleRiga, 'stato' => 'active'
                    ]);
                }
            }
        }
    }
}
$db->closeConnection();

$sogliaGratuita = 49.00;
$costoSpedizioneStandard = 10.00;

if ($totaleProdotti == 0) {
    $speseSpedizione = 0.00;
} else {
    $speseSpedizione = ($totaleProdotti >= $sogliaGratuita) ? 0.00 : $costoSpedizioneStandard;
}
$totaleFinale = $totaleProdotti + $speseSpedizione;

$msgSpedizione = "";
$progressHTML = ""; 

if ($totaleProdotti == 0) {
    $msgSpedizione = "€ 0.00";
    $progressHTML = '<p class="shipping-info"><i class="fas fa-shopping-basket"></i> Aggiungi prodotti al carrello.</p>';
} elseif ($speseSpedizione == 0) {
    $msgSpedizione = '<span style="color:#007600; font-weight:bold;">Gratuita</span>';
    $progressHTML = '<p class="shipping-info success"><i class="fas fa-check-circle"></i> Hai diritto alla spedizione GRATUITA!</p>';
} else {
    $mancante = number_format($sogliaGratuita - $totaleProdotti, 2);
    $msgSpedizione = '€ ' . number_format($costoSpedizioneStandard, 2);
    $progressHTML = '<p class="shipping-info warning"><i class="fas fa-info-circle"></i> Aggiungi altri <strong>€ ' . $mancante . '</strong> per la spedizione GRATIS.</p>';
}

function renderCartItem($item, $isLogged, $type = 'active') {
    $idR = isset($item['id_riga']) ? $item['id_riga'] : 0;
    $idV = isset($item['id_vino']) ? $item['id_vino'] : $item['id'];
    $qty = $item['quantita'];
    $stock = $item['quantita_stock'];
    
    $imgSrc = htmlspecialchars($item['img']);
    $nome = htmlspecialchars($item['nome']);
    $desc = htmlspecialchars($item['descrizione_breve']);
    $prezzoSingolo = number_format($item['prezzo'], 2);
    
    $availText = "";
    $availClass = "availability"; 
    
    if ($stock <= 0) {
        $availText = "Non disponibile";
        $availClass .= " text-red";
    } elseif ($stock < 6) {
        $availText = "Solo $stock rimaste - Ordina subito!";
        $availClass .= " text-orange";
    } else {
        $availText = "Spedizione in 24/48h";
        $availClass .= " text-green";
    }

    $actionsHTML = "";

    // Bottone ELIMINA (Button reale, non link)
    $btnElimina = "<button type='button' class='action-btn-text btn-delete ajax-cmd' data-action='rimuovi' data-id-riga='$idR' data-id-vino='$idV'>Elimina</button>";

    if ($type === 'active') {
        
        $btnSalva = "";
        if ($isLogged) {
            // Bottone SALVA PER DOPO
            $btnSalva = "<span class='separator'>|</span> <button type='button' class='action-btn-text btn-save ajax-cmd' data-action='salva_per_dopo' data-id-riga='$idR' data-id-vino='$idV'>Salva per dopo</button>";
        }

        $limitMax = ($stock > 100) ? 100 : $stock;

        $actionsHTML = "
            <div class='qty-selector'>
                <button type='button' class='qty-btn ajax-cmd' data-action='meno' data-id-riga='$idR' data-id-vino='$idV'>-</button>
                
                <input type='number' id='qty_v_$idV' value='$qty' class='qty-input' min='1' max='$limitMax' data-id-riga='$idR' data-id-vino='$idV'>
                
                <button type='button' class='qty-btn ajax-cmd' data-action='piu' data-id-riga='$idR' data-id-vino='$idV'>+</button>
            </div>
            <span class='separator'>|</span>
            $btnElimina
            $btnSalva
        ";
    } else {
        // Sezione Salvati
        $btnSposta = "";
        if ($stock > 0) {
            $btnSposta = "<button type='button' class='action-btn-text btn-move ajax-cmd' data-action='sposta_in_carrello' data-id-riga='$idR' data-id-vino='$idV'>Sposta nel carrello</button>";
        } else {
            $btnSposta = "<span class='disabled-btn'>Non disponibile</span>";
        }

        $actionsHTML = "
            $btnSposta
            <span class='separator'>|</span>
            $btnElimina
        ";
    }


    return "
    <div class='cart-item " . ($type == 'saved' ? 'item-saved' : '') . "'>
        <div class='cart-item-img'>
            <a href='vini.php'><img src='$imgSrc' alt='$nome'></a>
        </div>
        <div class='cart-item-details'>
            <div class='item-header'>
                <h3><a href='vini.php'>$nome</a></h3>
                <p class='price-mobile'>€ $prezzoSingolo</p>
            </div>
            <p class='item-desc'>$desc</p>
            <p class='$availClass'>$availText</p>
            <div class='item-actions-row'>$actionsHTML</div>
        </div>
        <div class='cart-item-price-col'>
            <p class='price-large'>€ $prezzoSingolo</p>
        </div>
    </div>";
}

$mainContentHTML = "";

if (empty($activeItems) && empty($savedItems)) {
    $mainContentHTML = "
    <div class='empty-cart-message'>
        <i class='fas fa-wine-bottle'></i>
        <h2>Il tuo carrello è vuoto</h2>
        <p>Non hai ancora aggiunto prodotti.</p>
        <a href='vini.php' class='btn-primary' style='margin-top:1.5rem; display:inline-block; width:auto; padding:1rem 2rem;'>Vai allo Shop</a>
    </div>";
} 
else {
    $activeProductsHTML = "";
    if (!empty($activeItems)) {
        foreach ($activeItems as $item) {
            $activeProductsHTML .= renderCartItem($item, $isLogged, 'active');
        }
    } else {
        $activeProductsHTML = "
        <div class='empty-cart-message' style='padding: 2rem;'>
            <i class='fas fa-wine-bottle' style='font-size:2rem'></i>
            <h3>Il carrello attivo è vuoto</h3>
            <a href='vini.php' class='btn-primary' style='margin-top:1rem;'>Vai allo Shop</a>
        </div>";
    }

    $savedProductsHTML = "";
    if (!empty($savedItems)) {
        $savedProductsHTML .= "<div class='saved-items-section'><h3>Salvati per dopo / Non disponibili</h3>";
        foreach ($savedItems as $item) {
            $savedProductsHTML .= renderCartItem($item, $isLogged, 'saved');
        }
        $savedProductsHTML .= "</div>";
    }

    if (!empty($activeItems)) {
        if ($isLogged) {
            $checkoutHTML = '<a href="checkout.php" class="btn-primary btn-full text-center">Procedi all\'ordine</a>';
        } else {
            $checkoutHTML = '<a href="login.php" class="btn-primary btn-full text-center">Accedi per acquistare</a>';
        }
    } else {
        $checkoutHTML = '<button disabled class="btn-primary btn-full btn-disabled text-center">Carrello Vuoto</button>';
    }

    // Nota: Ho aggiunto IDs ai campi prezzo per il JS
    $mainContentHTML = "
    <div class='cart-layout'>
        <div class='cart-list-container'>
            <h1 class='cart-title-main'>Carrello</h1>
            $activeProductsHTML
            " . (!empty($activeItems) ? "<div style='text-align:right; margin-top:1.5rem; font-size:1.2rem;'>Totale prodotti (<span id='cart-count-display'>$numArticoli</span>): <strong>€ <span id='cart-list-total'>" . number_format($totaleProdotti, 2) . "</span></strong></div>" : "") . "
            $savedProductsHTML
        </div>

        <div class='cart-summary-box'>
            $progressHTML
            <div class='summary-row'>
                <span>Prodotti:</span>
                <span>€ <span id='summary-subtotal'>" . number_format($totaleProdotti, 2) . "</span></span>
            </div>
            <div class='summary-row'>
                <span>Spedizione:</span>
                <span id='summary-shipping'>$msgSpedizione</span>
            </div>
            <div class='summary-total'>
                <span>Totale:</span>
                <span>€ <span id='summary-total'>" . number_format($totaleFinale, 2) . "</span></span>
            </div>
            <p style='font-size:0.8rem; text-align:right; color:var(--diversity-color); margin-top:0.5rem;'>IVA inclusa</p>
            $checkoutHTML
        </div>
    </div>";
}

$htmlPage = caricaPagina('../../html/carrello.html');
$htmlPage = str_replace("[user_area_link]", $userLinkHTML, $htmlPage);
$htmlPage = str_replace("[alert_msg]", $alertMsgHTML, $htmlPage);
$htmlPage = str_replace("[content_carrello]", $mainContentHTML, $htmlPage);

echo $htmlPage;
?>