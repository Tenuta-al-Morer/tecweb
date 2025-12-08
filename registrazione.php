<?php

require_once 'config.php'; // Includo la classe DBConnection
use DB\DBConnection;

// Funzione per pulire e reinserire i dati inseriti dall'utente nel form in caso di errore
function ripristinoInput($htmlContent){
    $htmlContent = str_replace("[nome]", htmlspecialchars(isset($_POST['nome']) ? $_POST['nome'] : ''), $htmlContent);
    $htmlContent = str_replace("[cognome]", htmlspecialchars(isset($_POST['cognome']) ? $_POST['cognome'] : ''), $htmlContent);
    $htmlContent = str_replace("[email]", htmlspecialchars(isset($_POST['email']) ? $_POST['email'] : ''), $htmlContent);
    // Puliamo il placeholder [err] se non è stato già sostituito
    if(strpos($htmlContent, "[err]") !== false) {
        $htmlContent = str_replace("[err]", "", $htmlContent);
    }
    return $htmlContent;
}

// Carico il template HTML
$registrazioneHTML = file_get_contents('registrazione.html');
$err = "";

session_start();
// Se l'utente è già loggato, via da qui
if (isset($_SESSION["utente"])) {
    header("location: utente.php"); // Cambia con la tua pagina utente
    exit();
}

// Controllo se il form è stato inviato
if($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Controllo campi vuoti
    if(empty($_POST["nome"]) || empty($_POST["cognome"]) || empty($_POST["email"]) || empty($_POST["password"]) || empty($_POST["confirm-password"])){
        $err .= "<p>Compila tutti i campi obbligatori.</p>";
    } else {
        
        // 2. Validazione Input
        
        // Nome e Cognome: Solo lettere, spazi e apostrofi
        if (!preg_match("/^[a-zA-Z\s']+$/", $_POST["nome"])) {
            $err .= "<p>Nome non valido (solo lettere).</p>";
        }
        if (!preg_match("/^[a-zA-Z\s']+$/", $_POST["cognome"])) {
            $err .= "<p>Cognome non valido (solo lettere).</p>";
        }

        // Email
        if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
            $err .= "<p>Formato email non valido.</p>";
        }

        // Password: Minimo 8 chars, almeno 1 lettera e 1 numero
        if (strlen($_POST["password"]) < 8) {
            $err .= "<p>La password deve essere di almeno 8 caratteri.</p>";
        }
        // Decommenta questa riga se vuoi forzare numeri e lettere:
        // if (!preg_match("/\d/", $_POST["password"]) || !preg_match("/[a-zA-Z]/", $_POST["password"])) { $err .= "<p>La password deve contenere lettere e numeri.</p>"; }

        // Conferma Password
        if ($_POST["password"] !== $_POST["confirm-password"]) {
            $err .= "<p>Le password non coincidono.</p>";
        }

        // Privacy Checkbox
        if (!isset($_POST["privacy"])) {
            $err .= "<p>Devi accettare la Privacy Policy.</p>";
        }

        // 3. Se non ci sono errori locali, proviamo il DB
        if (empty($err)) {
            try {
                $db = new DBConnection();
                // Hash della password
                $passwordHash = password_hash($_POST["password"], PASSWORD_DEFAULT);
                
                $ris = $db->registerUser($_POST["nome"], $_POST["cognome"], $_POST["email"], $passwordHash);
                $db->closeConnection();

                if($ris == -1){
                    $err = "<p>Questa email è già registrata.</p>";
                } 
                else if($ris == 1){
                    // REGISTRAZIONE RIUSCITA
                    $_SESSION['utente'] = $_POST["email"];
                    
                    // Reindirizzamento
                    header("location: login.php?success=1"); 
                    // Oppure vai diretto all'area riservata: header("location: area_riservata.php");
                    exit();
                } 
                else {
                    $err = "<p>Errore generico nel database. Riprova più tardi.</p>";
                }

            } catch (Exception $e) {
                $err = "<p>Errore di sistema.</p>";
            }
        }
    }
}

// Se c'è un errore o è la prima visita, mostriamo la pagina
if (!empty($err)) {
    // Inietto l'errore nell'HTML
    $registrazioneHTML = str_replace("[err]", $err, $registrazioneHTML);
} else {
    // Pulisco il placeholder errore
    $registrazioneHTML = str_replace("[err]", "", $registrazioneHTML);
}

// Ripristino i valori inseriti nei campi value="..."
echo ripristinoInput($registrazioneHTML);

?>