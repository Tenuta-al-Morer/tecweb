<?php

require_once 'config.php';
use DB\DBConnection;

// Funzione per ripristinare l'email nel form in caso di errore
function ripristinoInput($htmlContent){
    // Inserisce l'email inviata via POST nel value dell'input
    $htmlContent = str_replace("[email]", htmlspecialchars(isset($_POST['email']) ? $_POST['email'] : ''), $htmlContent);
    
    // Pulisce il placeholder [err] se non è stato usato
    if(strpos($htmlContent, "[err]") !== false) {
        $htmlContent = str_replace("[err]", "", $htmlContent);
    }
    return $htmlContent;
}

$loginHTML = file_get_contents('login.html');
$err = "";

session_start();
// Se l'utente è già loggato, lo mando alla sua area
if (isset($_SESSION["utente"])) {
    // Se è admin va all'admin panel, altrimenti area utente
    if(isset($_SESSION["ruolo"]) && $_SESSION["ruolo"] === 'admin') {
        header("location: admin.php"); // O .php se diventerà dinamica
    } else {
        header("location: utente.php"); // Cambia con la tua pagina utente
    }
    exit();
}

// Controllo se il form è stato inviato
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
            // loginUser ora restituisce l'array utente o un codice di errore
            $ris = $db->loginUser($_POST["email"], $_POST["password"]);
            $db->closeConnection();
            
            if($ris === -1){
                // Email non trovata
                $err = "<p>L'email inserita non è registrata.</p>";
                $loginHTML = str_replace("[err]", $err, $loginHTML);
                echo ripristinoInput($loginHTML);
            }
            else if($ris === 0){
                // Password errata
                $err = "<p>Password errata.</p>";
                $loginHTML = str_replace("[err]", $err, $loginHTML);
                echo ripristinoInput($loginHTML);
            }
            else if(is_array($ris)){
                // LOGIN RIUSCITO ($ris contiene i dati dell'utente)
                $_SESSION['utente'] = $ris['email']; // O $ris['username'] se preferisci
                $_SESSION['ruolo'] = $ris['ruolo'];  // Salviamo il ruolo in sessione

                // Gestione del "Torna dove eri prima" (Cookie backToOrigin)
                if(isset($_COOKIE['backToOrigin'])){
                    $url = $_COOKIE['backToOrigin'];
                    setcookie("backToOrigin", "", time() - 3600, "/"); // Cancella cookie
                    header("location: " . $url);
                } 
                else {
                    // Reindirizzamento basato sul ruolo
                    if ($ris['ruolo'] === 'admin') {
                         // Nota: Assicurati che questo percorso esista
                        header("location: users/admin.html");
                    } else {
                        header("location: utente.php");
                    }
                }
                exit();
            }
            else {
                // Errore imprevisto
                header("location: 500.html");
                exit();
            }
        }
        catch(Exception $e){
            // In produzione logga l'errore, qui rimandiamo al 500
            header("location: 500.html");
            exit();
        }
    }
}
else {
    // Prima visita alla pagina (nessun POST)
    echo ripristinoInput($loginHTML);
}
?>