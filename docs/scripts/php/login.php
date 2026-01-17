<?php
require_once 'common.php';
require_once 'DBConnection.php';
use DB\DBConnection;

function ripristinoInput($htmlContent){
    
    $htmlContent = str_replace("[email]", htmlspecialchars(isset($_POST['email']) ? $_POST['email'] : ''), $htmlContent);
    
    if(strpos($htmlContent, "[err]") !== false) {
        $htmlContent = str_replace("[err]", "", $htmlContent);
    }
    $hiddenInput = "";
    if (isset($_GET['return'])) {
        $returnUrl = htmlspecialchars($_GET['return']);
        $hiddenInput = '<input type="hidden" name="redirect_to" value="' . $returnUrl . '">';
        
        $htmlContent = str_replace('</form>', $hiddenInput . '</form>', $htmlContent);
    }

    return $htmlContent;
}

$loginHTML = caricaPagina('../../html/login.html');
$err = "";

if (isset($_SESSION["utente"])) {
    
    if(isset($_SESSION["ruolo"]) && $_SESSION["ruolo"] === 'admin') {
        header("location: gestionale.php"); 
    } else {
        header("location: utente.php"); 
    }
    exit();
}


if(isset($_POST["email"]) && isset($_POST["password"])){

    if(empty($_POST["email"]) || empty($_POST["password"])){
        $err = "<p>Devi compilare tutti i campi.</p>";
        $loginHTML = str_replace("[err]", $err, $loginHTML);
        echo ripristinoInput($loginHTML);
        exit();
    }
    else {
        try {
            $db = new DBConnection();
            
            $ris = $db->loginUser($_POST["email"], $_POST["password"]);
            $db->closeConnection();
            
            if($ris === -1){
                $err = "<p>L'email inserita non è registrata.</p>";
                $loginHTML = str_replace("[err]", $err, $loginHTML);
                echo ripristinoInput($loginHTML);
            }
            else if($ris === 0){
                $err = "<p>Password errata.</p>";
                $loginHTML = str_replace("[err]", $err, $loginHTML);
                echo ripristinoInput($loginHTML);
            }
            else if(is_array($ris)){

                session_regenerate_id(true);                
                $_SESSION['utente'] = $ris['email']; 
                $_SESSION['utente_id'] = $ris['id']; 
                $_SESSION['ruolo'] = $ris['ruolo'];  
                $_SESSION['nome'] = $ris['nome']; 

                // --- MERGE CARRELLO ---
                if (isset($_SESSION['guest_cart']) && !empty($_SESSION['guest_cart'])) {
                    $dbMerge = new DB\DBConnection();
                    $carrelloUtenteDB = $dbMerge->getCarrelloUtente($_SESSION['utente_id']);
                    $mappaCarrelloUtente = [];
                    
                    foreach($carrelloUtenteDB as $riga) {
                        if($riga['stato'] === 'attivo' || $riga['stato'] === 'active') {
                            $mappaCarrelloUtente[$riga['id_vino']] = $riga['quantita'];
                        }
                    }

                    foreach ($_SESSION['guest_cart'] as $idVino => $qtyGuest) {
                        $qtyPresente = isset($mappaCarrelloUtente[$idVino]) ? $mappaCarrelloUtente[$idVino] : 0;
                        $totaleTeorico = $qtyPresente + $qtyGuest;
                        $qtyDaAggiungere = 0;

                        if ($totaleTeorico > 100) {
                            $qtyDaAggiungere = 100 - $qtyPresente;
                        } else {
                            $qtyDaAggiungere = $qtyGuest;
                        }

                        if ($qtyDaAggiungere > 0) {
                            $dbMerge->aggiungiAlCarrello($_SESSION['utente_id'], $idVino, $qtyDaAggiungere);
                        }
                    }
                    
                    $dbMerge->closeConnection();
                    unset($_SESSION['guest_cart']); 
                }
                
                if (isset($_POST['redirect_to']) && !empty($_POST['redirect_to'])) {
                    header("location: " . $_POST['redirect_to']);
                } else {
                    if ($ris['ruolo'] === 'admin') {
                        header("location: gestionale.php");
                    } else {
                        header("location: utente.php");
                    }
                }
                exit();
            }
            else {
                header("location: 500.html");
                exit();
            }
        }
        catch(Exception $e){
            header("location: 500.html");
            exit();
        }
    }
}
else {
    echo ripristinoInput($loginHTML);
}
?>