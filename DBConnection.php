<?php
namespace DB;

use Exception;
use mysqli; 

// Importiamo il file di configurazione.
// __DIR__ assicura che cerchi il file nella stessa cartella di questo script.
require_once __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

class DBConnection {
    // Rimosse le costanti private con le credenziali esplicite
    
    private $connection;

    public function __construct() {
        try {
            // Utilizziamo le costanti globali definite in config.php
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $this->connection->set_charset("utf8mb4");
        } catch (Exception $e) {
            die("Errore di connessione al database.");
        }
    }

    public function closeConnection() {
        if ($this->connection && !$this->connection->connect_errno) {
            $this->connection->close();
        }
    }

    // --- DA QUI IN GIÙ LE TUE FUNZIONI RIMANGONO IDENTICHE ---

    // FUNZIONE DI REGISTRAZIONE
    public function registerUser($nome, $cognome, $email, $password) {
        $queryControllo = "SELECT id FROM utenti WHERE email = ?"; 
        
        $stmt = $this->connection->prepare($queryControllo);
        if (!$stmt) { die("Errore prepare controllo: " . $this->connection->error); }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return -1; // Email già presente
        }
        $stmt->close();

        $query = "INSERT INTO utenti (nome, cognome, email, password) VALUES (?, ?, ?, ?)";

        $stmt = $this->connection->prepare($query);
        if (!$stmt) { die("Errore prepare insert: " . $this->connection->error); }

        $stmt->bind_param("ssss", $nome, $cognome, $email, $password);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return 1; 
        } else {
            return 0; 
        }
    }

    // FUNZIONE DI LOGIN
    public function loginUser($email, $password) {
        $query = "SELECT * FROM utenti WHERE email = ?"; 
        
        $stmt = $this->connection->prepare($query);
        if (!$stmt) { die("Errore prepare login: " . $this->connection->error); }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            return -1; 
        }

        if (password_verify($password, $user['password'])) {
            return $user; 
        } else {
            return 0; 
        }
    }

    // FUNZIONE PER SALVARE UN MESSAGGIO
    public function salvaMessaggio($nome, $cognome, $email, $tipo_supporto, $prefisso, $telefono, $messaggio) {
        $query = "INSERT INTO contatti (nome, cognome, email, tipo_supporto, prefisso, telefono, messaggio, data_invio, stato) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'aperto')";
        
        $stmt = $this->connection->prepare($query);
        if (!$stmt) { return false; }

        $stmt->bind_param("sssssss", $nome, $cognome, $email, $tipo_supporto, $prefisso, $telefono, $messaggio);
        
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    // FUNZIONE ARCHIVIA MESSAGGIO
    public function archiviaMessaggio($id) {
        $queryCopia = "INSERT INTO contatti_archivio (id, nome, cognome, email, tipo_supporto, prefisso, telefono, messaggio, data_invio, stato)
                       SELECT id, nome, cognome, email, tipo_supporto, prefisso, telefono, messaggio, data_invio, 'chiuso'
                       FROM contatti WHERE id = ?";
        
        $stmt = $this->connection->prepare($queryCopia);
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $esitoCopia = $stmt->execute();
        $stmt->close();

        if ($esitoCopia) {
            $queryCancella = "DELETE FROM contatti WHERE id = ?";
            $stmtDel = $this->connection->prepare($queryCancella);
            $stmtDel->bind_param("i", $id);
            $stmtDel->execute();
            $stmtDel->close();
            return true;
        }
        return false;
    }

    // FUNZIONE PER SALVARE UNA PRENOTAZIONE
    public function salvaPrenotazione($nome, $cognome, $email, $tipo_degustazione, $prefisso, $telefono, $data_visita, $n_persone) {
        $query = "INSERT INTO prenotazioni (nome, cognome, email, tipo_degustazione, prefisso, telefono, data_visita, n_persone, data_invio, stato) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'In attesa')";
        
        $stmt = $this->connection->prepare($query);
        if (!$stmt) { return false; }

        $stmt->bind_param("sssssssi", $nome, $cognome, $email, $tipo_degustazione, $prefisso, $telefono, $data_visita, $n_persone);
        
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    // FUNZIONE ARCHIVIA PRENOTAZIONE
    public function archiviaPrenotazione($id) {
        $queryCopia = "INSERT INTO prenotazioni_archivio (id, nome, cognome, email, tipo_degustazione, prefisso, telefono, data_visita, n_persone, data_invio, stato)
                       SELECT id, nome, cognome, email, tipo_degustazione, prefisso, telefono, data_visita, n_persone, data_invio, 'Completato'
                       FROM prenotazioni WHERE id = ?";
        
        $stmt = $this->connection->prepare($queryCopia);
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $esitoCopia = $stmt->execute();
        $stmt->close();

        if ($esitoCopia) {
            $queryCancella = "DELETE FROM prenotazioni WHERE id = ?";
            $stmtDel = $this->connection->prepare($queryCancella);
            $stmtDel->bind_param("i", $id);
            $stmtDel->execute();
            $stmtDel->close();
            return true;
        }
        return false;
    }
}
?>