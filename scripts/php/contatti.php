<?php
require_once 'common.php';
require_once 'DBConnection.php';
use DB\DBConnection;

// 1. Carico il template HTML
$htmlContent = caricaPagina('../../html/contatti.html');

// Variabili per gestire errori e valori
$feedbackMessage = "";
$valori = [
    'nome' => '', 'cognome' => '', 'email' => '', 
    'tipo_supporto' => '', 'prefisso' => '+39', 
    'telefono' => '', 'messaggio' => ''
];

// 2. Controllo se il form è stato inviato
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Raccolta e pulizia dati
    $valori['nome'] = htmlspecialchars(trim($_POST['nome'] ?? ''));
    $valori['cognome'] = htmlspecialchars(trim($_POST['cognome'] ?? ''));
    $valori['email'] = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $valori['tipo_supporto'] = htmlspecialchars($_POST['tipo_supporto'] ?? '');
    $valori['prefisso'] = htmlspecialchars(trim($_POST['prefisso'] ?? '+39'));
    $valori['telefono'] = htmlspecialchars(trim($_POST['telefono'] ?? ''));
    $valori['messaggio'] = htmlspecialchars(trim($_POST['messaggio'] ?? ''));
    $privacy = isset($_POST['privacy']);

    // Validazione
    $errori = [];

    if (empty($valori['nome'])) $errori[] = "Il nome è obbligatorio.";
    if (empty($valori['cognome'])) $errori[] = "Il cognome è obbligatorio.";
    if (!filter_var($valori['email'], FILTER_VALIDATE_EMAIL)) $errori[] = "Email non valida.";
    if (empty($valori['tipo_supporto'])) $errori[] = "Seleziona un tipo di richiesta.";
    if (empty($valori['messaggio'])) $errori[] = "Il messaggio non può essere vuoto.";
    if (!$privacy) $errori[] = "Devi accettare la privacy policy.";

    // Se non ci sono errori, provo a salvare
    if (empty($errori)) {
        try {
            $db = new DBConnection();
            $risultato = $db->salvaMessaggio(
                $valori['nome'], 
                $valori['cognome'], 
                $valori['email'], 
                $valori['tipo_supporto'], 
                $valori['prefisso'], 
                $valori['telefono'], 
                $valori['messaggio']
            );
            $db->closeConnection();

        if ($risultato) {
            // Successo
            $feedbackMessage = '<div class="alert success" role="alert"><i class="fas fa-check-circle"></i> Messaggio inviato con successo! Ti risponderemo presto.</div>';
        } else {
            // Errore generico
            $feedbackMessage = '<div class="alert error" role="alert"><i class="fas fa-exclamation-triangle"></i> Errore nel salvataggio. Riprova più tardi.</div>';
        }
    } catch (Exception $e) {
        $feedbackMessage = '<div class="alert error" role="alert"><i class="fas fa-bomb"></i> Errore di sistema.</div>';
    }
    } else {
        // Ci sono errori di validazione PHP
        $feedbackMessage = '<div class="alert error" role="alert"><ul>';
        foreach ($errori as $err) {
            $feedbackMessage .= "<li>" . $err . "</li>";
        }
        $feedbackMessage .= '</ul></div>';
    }
}

// 3. Sostituzione Placeholders nell'HTML

// Inserisco il messaggio di feedback
$htmlContent = str_replace("[feedback_message]", $feedbackMessage, $htmlContent);

// Ripristino i valori nei campi (Sticky Form)
$htmlContent = str_replace("[val_nome]", $valori['nome'], $htmlContent);
$htmlContent = str_replace("[val_cognome]", $valori['cognome'], $htmlContent);
$htmlContent = str_replace("[val_email]", $valori['email'], $htmlContent);
$htmlContent = str_replace("[val_prefisso]", $valori['prefisso'], $htmlContent);
$htmlContent = str_replace("[val_telefono]", $valori['telefono'], $htmlContent);
$htmlContent = str_replace("[val_messaggio]", $valori['messaggio'], $htmlContent);

// Gestione Select (Opzione selezionata)
$options = [
    'informazioni_vini', 'visita_degustazione', 'ordine_online', 
    'partnership', 'events', 'assistenza', 'altro'
];
// Resetta tutti i placeholder delle select
$htmlContent = str_replace("[sel_default]", ($valori['tipo_supporto'] == '' ? 'selected' : ''), $htmlContent);
foreach ($options as $opt) {
    $placeholder = "[sel_" . $opt . "]";
    $selected = ($valori['tipo_supporto'] == $opt) ? 'selected' : '';
    $htmlContent = str_replace($placeholder, $selected, $htmlContent);
}

echo $htmlContent;
?>