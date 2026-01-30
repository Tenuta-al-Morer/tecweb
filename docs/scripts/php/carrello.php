<?php
session_start();
require_once 'common.php';
require_once 'DBConnection.php';

use DB\DBConnection;

// 1. GESTIONE ACCESSO ADMIN/STAFF
if (isset($_SESSION['utente']) && isset($_SESSION['ruolo'])) {
    $ruolo = $_SESSION['ruolo']; 
    
    if ($ruolo === 'admin' || $ruolo === 'staff') {
        $db = new DBConnection();
        $db->svuotaCarrelloUtente($_SESSION['utente_id']);
        $db->closeConnection();
        header("Location: gestionale.php");
        exit();
    }
}

// 2. GESTIONE RICHIESTE (POST/GET)
if (isset($_REQUEST['action'])) {
    
    $action = $_REQUEST['action'];
    $id_vino = isset($_REQUEST['id_vino']) ? (int)$_REQUEST['id_vino'] : 0;
    $id_riga = isset($_REQUEST['id_riga']) ? (int)$_REQUEST['id_riga'] : 0;
    $current_qty = isset($_REQUEST['current_qty']) ? (int)$_REQUEST['current_qty'] : 1; 

    $db = new DBConnection();

    $new_qty = $current_qty;
    $qty_to_add = 1; 

    // Calcolo nuove quantità in base all'azione
    if ($action === 'piu') {
        if (isset($_REQUEST['quantita'])) {
            $current_qty = (int)$_REQUEST['quantita'];
        }
        $new_qty = $current_qty + 1;
    } elseif ($action === 'meno') {
        if (isset($_REQUEST['quantita'])) {
            $current_qty = (int)$_REQUEST['quantita'];
        }
        $new_qty = $current_qty - 1;
    } elseif ($action === 'aggiorna_quantita') {
        $new_qty = isset($_REQUEST['quantita']) ? (int)$_REQUEST['quantita'] : 1;
    } elseif ($action === 'aggiungi') {
        $qty_to_add = isset($_REQUEST['quantita']) ? (int)$_REQUEST['quantita'] : 1;
    }

    $vinoInfo = $db->getVinoPerCarrello($id_vino); 
    
    // Controllo esistenza vino
    if (!$vinoInfo) {
         if (isset($_REQUEST['req_source'])) {
             echo json_encode(['success' => false, 'error' => 'Vino non trovato']);
             exit();
         }
         header("Location: carrello.php"); exit();
    }

    $realStock = (int)$vinoInfo['quantita_stock'];

    // Validazione Stock
    if ($new_qty > $realStock) {
        $new_qty = $realStock;
    }
    
    if ($action === 'aggiungi') {
        if ($qty_to_add > $realStock) $qty_to_add = $realStock;
    }

    // Esecuzione modifiche DB o Sessione
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
        // Logica Guest (Sessione)
        if (!isset($_SESSION['guest_cart'])) {
            $_SESSION['guest_cart'] = [];
        }

        if ($action === 'aggiungi') {
            if (isset($_SESSION['guest_cart'][$id_vino])) {
                $_SESSION['guest_cart'][$id_vino] += $qty_to_add;
            } else {
                $_SESSION['guest_cart'][$id_vino] = $qty_to_add;
            }
            if ($_SESSION['guest_cart'][$id_vino] > $realStock) {
                $_SESSION['guest_cart'][$id_vino] = $realStock;
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

    // Ricalcolo Totali per risposta JSON
    $newTotalProd = 0;
    $newCount = 0;
    
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

    // Logica Spedizione
    $soglia = 49.00;
    $costoStd = 10.00;
    $shipCost = ($newTotalProd == 0) ? 0 : (($newTotalProd >= $soglia) ? 0 : $costoStd);
    $finalTotal = $newTotalProd + $shipCost;
    $shipMsg = ($shipCost == 0 && $newTotalProd > 0) ? '<span class="free-shipping-text">Gratuita</span>' : '€ ' . number_format($shipCost, 2);
    if($newTotalProd == 0) $shipMsg = '€ 0.00';

    $progressHTML = "";
    if ($newTotalProd == 0) {
        $progressHTML = '<p class="shipping-info"><i class="fas fa-shopping-basket"></i> Aggiungi prodotti al carrello.</p>';
    } elseif ($shipCost == 0) {
        $progressHTML = '<p class="shipping-info success"><i class="fas fa-check-circle"></i> Hai diritto alla spedizione GRATUITA!</p>';
    } else {
        $mancante = number_format($soglia - $newTotalProd, 2);
        $progressHTML = '<p class="shipping-info warning"><i class="fas fa-info-circle"></i> Aggiungi altri <span class="bold">€ ' . $mancante . '</span> per la spedizione GRATIS.</p>';
    }

    if (isset($_REQUEST['req_source'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'qty' => $new_qty,
            'id_riga' => $id_riga,
            'cart_count' => $newCount,
            'total_products' => number_format($newTotalProd, 2),
            'shipping' => $shipMsg,
            'shipping_progress' => $progressHTML,
            'total_final' => number_format($finalTotal, 2)
        ]);
        exit();
    }
    
    $redirectTarget = isset($_REQUEST['return_url']) ? $_REQUEST['return_url'] : 'carrello.php';
    
    header("Location: " . $redirectTarget);
    exit();
}

// 3. GENERAZIONE PAGINA HTML
$userLinkHTML = '';
if (isset($_SESSION['utente'])) {
    $targetPage = ($_SESSION['ruolo'] === 'admin') ? 'gestionale.php' : 'areaPersonale.php';
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
$quantitaRidottaMsg = []; 

// Recupero e pulizia carrello
if ($isLogged) {
    $allItems = $db->getCarrelloUtente($_SESSION['utente_id']);
    
    foreach ($allItems as &$item) {
        $stock = (int)$item['quantita_stock'];
        $qty = (int)$item['quantita'];
        $idR = $item['id_riga'];
        $idV = $item['id_vino'];
        $statoCarrello = $item['stato']; 
        $statoVino = isset($item['stato_vino']) ? $item['stato_vino'] : 'attivo';

        // Check Stock vs Quantità
        if (($statoCarrello === 'active' || $statoCarrello === 'attivo') && $stock > 0 && $qty > $stock) {
            $db->aggiornaQuantitaCarrello($_SESSION['utente_id'], $idV, $stock);
            $item['quantita'] = $stock;
            $qty = $stock; 
            $item['totale_riga'] = $item['prezzo'] * $stock; 
            $quantitaRidottaMsg[] = htmlspecialchars($item['nome']);
        }

        // Check Disponibilità
        if (($statoCarrello === 'active' || $statoCarrello === 'attivo')) {
            if ($stock <= 0 || $statoVino !== 'attivo') {
                $db->cambiaStatoElemento($idR, 'salvato');
                $item['stato'] = 'salvato'; 
                $statoCarrello = 'salvato';
            }
        }

        if ($statoCarrello === 'salvato') {
            $savedItems[] = $item;
        } else {
            $activeItems[] = $item;
            $totaleProdotti += $item['totale_riga'];
            $numArticoli += $item['quantita'];
        }
    }
    unset($item); 
} else {
    // Gestione Guest
    if (isset($_SESSION['guest_cart']) && !empty($_SESSION['guest_cart'])) {
        foreach ($_SESSION['guest_cart'] as $idVino => $qty) {
            $vino = $db->getVinoPerCarrello($idVino);
            
            if ($vino) {
                $statoVino = $vino['stato'];
                $stock = (int)$vino['quantita_stock'];

                if ($stock > 0 && $qty > $stock) {
                    $_SESSION['guest_cart'][$idVino] = $stock;
                    $qty = $stock;
                    $quantitaRidottaMsg[] = htmlspecialchars($vino['nome']);
                }

                if ($stock <= 0 || $statoVino !== 'attivo') {
                    $savedItems[] = array_merge($vino, [
                        'id_riga' => 0, 
                        'id_vino' => $idVino, 
                        'quantita' => $qty, 
                        'totale_riga' => 0, 
                        'stato' => 'salvato',
                        'stato_vino' => $statoVino
                    ]);
                } else {
                    $totaleRiga = $vino['prezzo'] * $qty;
                    $totaleProdotti += $totaleRiga;
                    $numArticoli += $qty;
                    $activeItems[] = array_merge($vino, [
                        'id_riga' => 0, 
                        'id_vino' => $idVino, 
                        'quantita' => $qty, 
                        'totale_riga' => $totaleRiga, 
                        'stato' => 'active',
                        'stato_vino' => $statoVino
                    ]);
                }
            }
        }
    }
}
$db->closeConnection();

// Alert quantità ridotte
if (!empty($quantitaRidottaMsg)) {
    $listaVini = implode(", ", $quantitaRidottaMsg);
    $alertMsgHTML .= '
    <div class="alert-bar">
        <i class="fas fa-info-circle"></i>
        <span>La quantità di alcuni articoli (' . $listaVini . ') è stata aggiornata in base alla disponibilità attuale.</span>
    </div>';
}

// Calcolo Spedizione UI
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
    $msgSpedizione = '<span class="free-shipping-text">Gratuita</span>';
    $progressHTML = '<p class="shipping-info success"><i class="fas fa-check-circle"></i> Hai diritto alla spedizione GRATUITA!</p>';
} else {
    $mancante = number_format($sogliaGratuita - $totaleProdotti, 2);
    $msgSpedizione = '€ ' . number_format($costoSpedizioneStandard, 2);
    $progressHTML = '<p class="shipping-info warning"><i class="fas fa-info-circle"></i> Aggiungi altri <span class="bold">€ ' . $mancante . '</span> per la spedizione GRATIS.</p>';
}

// 4. FUNZIONE RENDER ITEM 
function renderCartItem($item, $isLogged, $type = 'active') {
    $idR = isset($item['id_riga']) ? $item['id_riga'] : 0;
    $idV = isset($item['id_vino']) ? $item['id_vino'] : $item['id'];
    $qty = $item['quantita'];
    $stock = (int)$item['quantita_stock']; 
    
    $statoVino = isset($item['stato_vino']) ? $item['stato_vino'] : (isset($item['stato']) ? $item['stato'] : 'attivo');

    $imgSrc = htmlspecialchars($item['img']);
    $nome = htmlspecialchars($item['nome']);
    $desc = htmlspecialchars($item['descrizione_breve']);
    $prezzoSingolo = number_format($item['prezzo'], 2);
    
    $availText = "";
    $availClass = "availability"; 
    
    if ($statoVino === 'nascosto' || $statoVino !== 'attivo') {
        $availText = "Non disponibile";
        $availClass .= " text-red";
    } elseif ($stock <= 0) {
        $availText = "Esaurito";
        $availClass .= " text-red";
    } elseif ($stock < 100) { 
        $availText = "Rimanenti solo $stock bottiglie";
        $availClass .= " text-orange"; 
    } else {
        $availText = "Spedizione in 24/48h";
        $availClass .= " text-green";
    }

    $actionsHTML = "";
    
    $linkDelete = "carrello.php?action=rimuovi&id_riga=$idR&id_vino=$idV";
    $btnElimina = "<a href='$linkDelete' class='action-btn-text btn-delete cart-action-btn' data-action='rimuovi' data-id-riga='$idR' data-id-vino='$idV'>Elimina</a>";

    if ($type === 'active') {
        $btnSalva = "";
        if ($isLogged) {
            $linkSave = "carrello.php?action=salva_per_dopo&id_riga=$idR&id_vino=$idV";
            $btnSalva = "<span class='separator'>|</span> <a href='$linkSave' class='action-btn-text btn-save cart-action-btn' data-action='salva_per_dopo' data-id-riga='$idR' data-id-vino='$idV'>Salva per dopo</a>";
        }

        $limitMax = $stock; 

        $actionsHTML = "
            <form class='qty-selector' action='carrello.php' method='POST'>
                <input type='hidden' name='id_riga' value='$idR'>
                <input type='hidden' name='id_vino' value='$idV'>
                
                <button type='submit' name='action' value='aggiorna_quantita' class='hidden-submit' tabindex='-1' aria-hidden='true'>Aggiorna quantità</button>

                <button type='submit' name='action' value='meno' class='qty-btn cart-action-btn' aria-label='Riduci quantità di $nome' data-action='meno' data-id-riga='$idR' data-id-vino='$idV'>-</button>
                
                <label for='qty_v_$idV' class='visually-hidden'>Quantità per $nome</label>

                <input type='number' name='quantita' id='qty_v_$idV' value='$qty' class='qty-input' min='1' max='$limitMax' data-stock='$stock' data-id-riga='$idR' data-id-vino='$idV'>
                
                <button type='submit' name='action' value='piu' class='qty-btn cart-action-btn' aria-label='Aumenta quantità di $nome' data-action='piu' data-id-riga='$idR' data-id-vino='$idV'>+</button>
            </form>
            <span class='separator'>|</span>
            $btnElimina
            $btnSalva
        ";
    } else {
        $btnSposta = "";
        if ($stock > 0 && $statoVino === 'attivo') {
            $linkMove = "carrello.php?action=sposta_in_carrello&id_riga=$idR&id_vino=$idV";
            $btnSposta = "<a href='$linkMove' class='action-btn-text btn-move cart-action-btn' data-action='sposta_in_carrello' data-id-riga='$idR' data-id-vino='$idV'>Sposta nel carrello</a>";
        } else {
            $btnSposta = "<span class='disabled-btn'>Non acquistabile</span>";
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
        </div>
        <div class='item-actions-row'>$actionsHTML</div>
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
        <a href='vini.php' class='btn-primary btn-shop-empty'>Vai allo Shop</a>
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
        <div class='empty-cart-message small-empty'>
            <i class='fas fa-wine-bottle'></i>
            <h3>Il carrello attivo è vuoto</h3>
            <a href='vini.php' class='btn-primary btn-shop-empty'>Vai allo Shop</a>
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
            $checkoutHTML = '<a href="login.php?return=carrello.php" class="btn-primary btn-full text-center">Accedi per acquistare</a>';
        }
    } else {
        $checkoutHTML = '<button disabled class="btn-primary btn-full btn-disabled text-center">Carrello Vuoto</button>';
    }

    $mainContentHTML = "
    <div class='cart-layout'>
        <div class='cart-list-container'>
            <h1 class='cart-title-main'>Carrello</h1>
            $activeProductsHTML
            " . (!empty($activeItems) ? "<div class='cart-list-total-row'>Totale prodotti (<span id='cart-count-display'>$numArticoli</span>): <span class='bold'>€ <span id='cart-list-total'>" . number_format($totaleProdotti, 2) . "</span></span></div>" : "") . "
            $savedProductsHTML
        </div>

        <div class='cart-summary-box'>
            <div id='shipping-message-container'>
                $progressHTML
            </div>

            <div class='summary-row'>
                <span>Prodotti:</span>
                <span>€ <span id='summary-subtotal'>" . number_format($totaleProdotti, 2) . "</span></span>
            </div>
            <div class='summary-row'>
                <span>Spedizione:</span>
                <span id='summary-shipping'>$msgSpedizione</span>
            </div>
            <div class='summary-total' aria-live='polite' aria-atomic='true'>
                <span>Totale:</span>
                <span>€ <span id='summary-total'>" . number_format($totaleFinale, 2) . "</span></span>
            </div>
            <p class='vat-text'><abbr title=\"Imposta sul Valore Aggiunto\">IVA</abbr> inclusa</p>
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