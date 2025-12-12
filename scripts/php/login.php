<?php

require_once 'DBConnection.php';
use DB\DBConnection;


function ripristinoInput($htmlContent){
    
    $htmlContent = str_replace("[email]", htmlspecialchars(isset($_POST['email']) ? $_POST['email'] : ''), $htmlContent);
    
    
    if(strpos($htmlContent, "[err]") !== false) {
        $htmlContent = str_replace("[err]", "", $htmlContent);
    }
    return $htmlContent;
}

$loginHTML = file_get_contents('../../html/login.html');
$err = "";

session_start();

if (isset($_SESSION["utente"])) {
    
    if(isset($_SESSION["ruolo"]) && $_SESSION["ruolo"] === 'admin') {
        header("location: admin.php"); 
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
                
                $_SESSION['utente'] = $ris['email']; 
                $_SESSION['ruolo'] = $ris['ruolo'];  

                
                if(isset($_COOKIE['backToOrigin'])){
                    $url = $_COOKIE['backToOrigin'];
                    setcookie("backToOrigin", "", time() - 3600, "/"); 
                    header("location: " . $url);
                } 
                else {
                    
                    if ($ris['ruolo'] === 'admin') {
                         
                        header("location: admin.php");
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