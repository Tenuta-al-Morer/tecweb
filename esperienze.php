<?php
require_once 'common.php';
require_once 'config.php';
use DB\DBConnection;

// 1. Carico il template HTML
$htmlContent = caricaPagina('esperienze.html');

// Variabili per gestire errori e valori
$feedbackMessage = "";
$valori = [
    'nome' => '', 'cognome' => '', 'email' => '', 
    'tipo_degustazione' => '', 'prefisso' => '+39', 
    'telefono' => '', 'data' => '', 'persone' => ''
];

// 2. Controllo se il form è stato inviato
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Raccolta e pulizia dati
    $valori['nome'] = htmlspecialchars(trim($_POST['nome'] ?? ''));
    $valori['cognome'] = htmlspecialchars(trim($_POST['cognome'] ?? ''));
    $valori['email'] = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $valori['tipo_degustazione'] = htmlspecialchars($_POST['tipo_degustazione'] ?? '');
    $valori['prefisso'] = htmlspecialchars(trim($_POST['prefisso'] ?? '+39'));
    $valori['telefono'] = htmlspecialchars(trim($_POST['telefono'] ?? ''));
    $valori['data'] = htmlspecialchars(trim($_POST['data'] ?? ''));
    $valori['persone'] = (int)($_POST['persone'] ?? 0);
    $privacy = isset($_POST['privacy']);

    // Validazione
    $errori = [];

    if (empty($valori['nome'])) $errori[] = "Il nome è obbligatorio.";
    if (empty($valori['cognome'])) $errori[] = "Il cognome è obbligatorio.";
    if (!filter_var($valori['email'], FILTER_VALIDATE_EMAIL)) $errori[] = "Email non valida.";
    if (empty($valori['tipo_degustazione'])) $errori[] = "Seleziona un tipo di degustazione.";
    
    // Validazione Data
    if (empty($valori['data'])) {
        $errori[] = "Seleziona una data.";
    } else {
        $oggi = date("Y-m-d");
        if ($valori['data'] < $oggi) {
            $errori[] = "La data non può essere nel passato.";
        }
    }

    if ($valori['persone'] < 1 || $valori['persone'] > 50) $errori[] = "Numero persone non valido (1-50).";
    if (!$privacy) $errori[] = "Devi accettare la privacy policy.";

    // Se non ci sono errori, provo a salvare
    if (empty($errori)) {
        try {
            $db = new DBConnection();
            $risultato = $db->salvaPrenotazione(
                $valori['nome'], 
                $valori['cognome'], 
                $valori['email'], 
                $valori['tipo_degustazione'], 
                $valori['prefisso'], 
                $valori['telefono'], 
                $valori['data'],     // data_visita
                $valori['persone']   // n_persone
            );
            $db->closeConnection();

            if ($risultato) {
                // Successo: Messaggio verde e PULIZIA campi
                $feedbackMessage = '<div class="alert success"><i class="fas fa-check-circle"></i> Richiesta inviata! Ti contatteremo per confermare.</div>';
                // Resetto i valori
                $valori = array_fill_keys(array_keys($valori), ''); 
                $valori['prefisso'] = '+39'; 
            } else {
                $feedbackMessage = '<div class="alert error"><i class="fas fa-exclamation-triangle"></i> Errore nel salvataggio. Riprova più tardi.</div>';
            }
        } catch (Exception $e) {
            $feedbackMessage = '<div class="alert error"><i class="fas fa-bomb"></i> Errore di sistema.</div>';
        }
    } else {
        // Ci sono errori: Messaggio rosso
        $feedbackMessage = '<div class="alert error"><ul>';
        foreach ($errori as $err) {
            $feedbackMessage .= "<li>" . $err . "</li>";
        }
        $feedbackMessage .= '</ul></div>';
    }
}

// 3. Sostituzione Placeholders nell'HTML

// Feedback
$htmlContent = str_replace("[feedback_message]", $feedbackMessage, $htmlContent);

// Valori Input (Sticky form)
$htmlContent = str_replace("[val_nome]", $valori['nome'], $htmlContent);
$htmlContent = str_replace("[val_cognome]", $valori['cognome'], $htmlContent);
$htmlContent = str_replace("[val_email]", $valori['email'], $htmlContent);
$htmlContent = str_replace("[val_prefisso]", $valori['prefisso'], $htmlContent);
$htmlContent = str_replace("[val_telefono]", $valori['telefono'], $htmlContent);
$htmlContent = str_replace("[val_data]", $valori['data'], $htmlContent);
// Gestione placeholder numerico per persone (se 0 mettiamo vuoto)
$htmlContent = str_replace("[val_persone]", ($valori['persone'] > 0 ? $valori['persone'] : ''), $htmlContent);

// Gestione Select
$options = ['Linea Oro', 'Piave'];

// Resetta il default
$htmlContent = str_replace("[sel_default]", ($valori['tipo_degustazione'] == '' ? 'selected' : ''), $htmlContent);

foreach ($options as $opt) {
    // Nota: qui uso str_replace diretto perché i valori contengono spazi (Linea Oro)
    $placeholder = "[sel_" . $opt . "]";
    $selected = ($valori['tipo_degustazione'] == $opt) ? 'selected' : '';
    $htmlContent = str_replace($placeholder, $selected, $htmlContent);
}

echo $htmlContent;
?>