<?php

require_once 'DBConnection.php'; 
use DB\DBConnection;


function ripristinoInput($htmlContent){
    $htmlContent = str_replace("[nome]", htmlspecialchars(isset($_POST['nome']) ? $_POST['nome'] : ''), $htmlContent);
    $htmlContent = str_replace("[cognome]", htmlspecialchars(isset($_POST['cognome']) ? $_POST['cognome'] : ''), $htmlContent);
    $htmlContent = str_replace("[email]", htmlspecialchars(isset($_POST['email']) ? $_POST['email'] : ''), $htmlContent);
    
    if(strpos($htmlContent, "[err]") !== false) {
        $htmlContent = str_replace("[err]", "", $htmlContent);
    }
    return $htmlContent;
}


$registrazioneHTML = file_get_contents('../../html/registrazione.html');
$err = "";

session_start();

if (isset($_SESSION["utente"])) {
    header("location: utente.php"); 
    exit();
}


if($_SERVER["REQUEST_METHOD"] == "POST") {

    
    if(empty($_POST["nome"]) || empty($_POST["cognome"]) || empty($_POST["email"]) || empty($_POST["password"]) || empty($_POST["confirm-password"])){
        $err .= "<p>Compila tutti i campi obbligatori.</p>";
    } else {
        
        
        
        
        if (!preg_match("/^[a-zA-Z\s']+$/", $_POST["nome"])) {
            $err .= "<p>Nome non valido (solo lettere).</p>";
        }
        if (!preg_match("/^[a-zA-Z\s']+$/", $_POST["cognome"])) {
            $err .= "<p>Cognome non valido (solo lettere).</p>";
        }

        
        if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
            $err .= "<p>Formato email non valido.</p>";
        }

        
        if (strlen($_POST["password"]) < 8) {
            $err .= "<p>La password deve essere di almeno 8 caratteri.</p>";
        }
        
        

        
        if ($_POST["password"] !== $_POST["confirm-password"]) {
            $err .= "<p>Le password non coincidono.</p>";
        }

        
        if (!isset($_POST["privacy"])) {
            $err .= "<p>Devi accettare la Privacy Policy.</p>";
        }

        
        if (empty($err)) {
            try {
                $db = new DBConnection();
                
                $passwordHash = password_hash($_POST["password"], PASSWORD_DEFAULT);
                
                $ris = $db->registerUser($_POST["nome"], $_POST["cognome"], $_POST["email"], $passwordHash);
                $db->closeConnection();

                if($ris == -1){
                    $err = "<p>Questa email è già registrata.</p>";
                } 
                else if($ris == 1){
                    
                    $_SESSION['utente'] = $_POST["email"];
                    
                    
                    header("location: login.php?success=1"); 
                    
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


if (!empty($err)) {
    
    $registrazioneHTML = str_replace("[err]", $err, $registrazioneHTML);
} else {
    
    $registrazioneHTML = str_replace("[err]", "", $registrazioneHTML);
}


echo ripristinoInput($registrazioneHTML);

?>