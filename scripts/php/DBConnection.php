<?php
namespace DB;

use Exception;
use mysqli; 

// Importiamo il file di configurazione.
// __DIR__ assicura che cerchi il file nella stessa cartella di questo script.
require_once __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

class DBConnection {
    
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

    

    // FUNZIONE DI REGISTRAZIONE
    public function registerUser($nome, $cognome, $email, $password) {
        $queryControllo = "SELECT id FROM utente WHERE email = ?"; 
        
        $stmt = $this->connection->prepare($queryControllo);
        if (!$stmt) { die("Errore prepare controllo: " . $this->connection->error); }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return -1; // Email già presente
        }
        $stmt->close();

        $query = "INSERT INTO utente (nome, cognome, email, password) VALUES (?, ?, ?, ?)";

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
        $query = "SELECT * FROM utente WHERE email = ?"; 
        
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

    // RECUPERO DATI ANAGRAFICI UTENTE
    public function getUserInfo($id) {
        $stmt = $this->connection->prepare("SELECT nome, cognome, email FROM utente WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // FUNZIONE PER SALVARE UN MESSAGGIO
    public function salvaMessaggio($nome, $cognome, $email, $tipo_supporto, $prefisso, $telefono, $messaggio) {
        $query = "INSERT INTO contatto (nome, cognome, email, tipo_supporto, prefisso, telefono, messaggio, data_invio, stato) 
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
        $queryCopia = "INSERT INTO contatto_archivio (id, nome, cognome, email, tipo_supporto, prefisso, telefono, messaggio, data_invio, stato)
                       SELECT id, nome, cognome, email, tipo_supporto, prefisso, telefono, messaggio, data_invio, 'chiuso'
                       FROM contatto WHERE id = ?";
        
        $stmt = $this->connection->prepare($queryCopia);
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $esitoCopia = $stmt->execute();
        $stmt->close();

        if ($esitoCopia) {
            $queryCancella = "DELETE FROM contatto WHERE id = ?";
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
        $query = "INSERT INTO prenotazione (nome, cognome, email, tipo_degustazione, prefisso, telefono, data_visita, n_persone, data_invio, stato) 
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
        $queryCopia = "INSERT INTO prenotazione_archivio (id, nome, cognome, email, tipo_degustazione, prefisso, telefono, data_visita, n_persone, data_invio, stato)
                       SELECT id, nome, cognome, email, tipo_degustazione, prefisso, telefono, data_visita, n_persone, data_invio, 'Completato'
                       FROM prenotazione WHERE id = ?";
        
        $stmt = $this->connection->prepare($queryCopia);
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $esitoCopia = $stmt->execute();
        $stmt->close();

        if ($esitoCopia) {
            $queryCancella = "DELETE FROM prenotazione WHERE id = ?";
            $stmtDel = $this->connection->prepare($queryCancella);
            $stmtDel->bind_param("i", $id);
            $stmtDel->execute();
            $stmtDel->close();
            return true;
        }
        return false;
    }

    // ============================================================
    // SEZIONE E-COMMERCE (VINI, CARRELLO, ORDINI)
    // ============================================================

    // RECUPERO LISTA VINI (Solo quelli attivi)
    public function getVini() {
        // Selezioniamo solo i vini attivi per la vendita
        $query = "SELECT * FROM vino WHERE stato = 'attivo'";
        $result = $this->connection->query($query);
        
        $vini = [];
        while ($row = $result->fetch_assoc()) {
            $vini[] = $row;
        }
        return $vini;
    }

    // RECUPERO UN SINGOLO VINO (Per pagina dettaglio)
    public function getVino($id) {
        $stmt = $this->connection->prepare("SELECT * FROM vino WHERE id = ? AND stato = 'attivo'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc(); // Ritorna null se non esiste
    }

    // GESTIONE CARRELLO: OTTIENI O CREA ID CARRELLO
    private function getCarrelloId($id_utente) {
        $stmt = $this->connection->prepare("SELECT id FROM carrello WHERE id_utente = ?");
        $stmt->bind_param("i", $id_utente);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            return $row['id'];
        } else {
            $stmtInsert = $this->connection->prepare("INSERT INTO carrello (id_utente) VALUES (?)");
            $stmtInsert->bind_param("i", $id_utente);
            $stmtInsert->execute();
            return $stmtInsert->insert_id;
        }
    }

    // AGGIUNGI AL CARRELLO
    public function aggiungiAlCarrello($id_utente, $id_vino, $quantita) {
        $id_carrello = $this->getCarrelloId($id_utente);

        $stmtCheck = $this->connection->prepare("SELECT id, quantita FROM carrello_elemento WHERE id_carrello = ? AND id_vino = ?");
        $stmtCheck->bind_param("ii", $id_carrello, $id_vino);
        $stmtCheck->execute();
        $res = $stmtCheck->get_result();

        if ($row = $res->fetch_assoc()) {
            // UPDATE
            $nuovaQuantita = $row['quantita'] + $quantita;
            $stmtUpdate = $this->connection->prepare("UPDATE carrello_elemento SET quantita = ? WHERE id = ?");
            $stmtUpdate->bind_param("ii", $nuovaQuantita, $row['id']);
            return $stmtUpdate->execute();
        } else {
            // INSERT
            $stmtInsert = $this->connection->prepare("INSERT INTO carrello_elemento (id_carrello, id_vino, quantita) VALUES (?, ?, ?)");
            $stmtInsert->bind_param("iii", $id_carrello, $id_vino, $quantita);
            return $stmtInsert->execute();
        }
    }

    // VISUALIZZA CARRELLO
    public function getCarrelloUtente($id_utente) {
        $id_carrello = $this->getCarrelloId($id_utente);
        
        $query = "SELECT ec.id as id_riga, v.id as id_vino, v.nome, v.prezzo, ec.quantita, (v.prezzo * ec.quantita) as totale_riga 
                  FROM carrello_elemento ec
                  JOIN vino v ON ec.id_vino = v.id
                  WHERE ec.id_carrello = ?";
                  
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $id_carrello);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        return $items;
    }

    // RIMUOVI ELEMENTO DAL CARRELLO
    public function rimuoviDaCarrello($id_elemento_carrello) {
        $stmt = $this->connection->prepare("DELETE FROM carrello_elemento WHERE id = ?");
        $stmt->bind_param("i", $id_elemento_carrello);
        return $stmt->execute();
    }

    // CHECKOUT: CREAZIONE ORDINE
    public function creaOrdine($id_utente, $indirizzo_spedizione, $metodo_pagamento, $costo_spedizione = 10.00) {
        $items = $this->getCarrelloUtente($id_utente);
        
        if (empty($items)) {
            return ["success" => false, "error" => "Il carrello è vuoto"];
        }

        $totale_prodotti = 0;
        foreach ($items as $item) {
            $totale_prodotti += $item['totale_riga'];
        }
        $totale_finale = $totale_prodotti + $costo_spedizione;

        $this->connection->begin_transaction();

        try {
            // A. Creo ordine
            $stmtOrd = $this->connection->prepare("INSERT INTO ordine (id_utente, totale_prodotti, costo_spedizione, totale_finale, indirizzo_spedizione, metodo_pagamento, stato_ordine) VALUES (?, ?, ?, ?, ?, ?, 'pagato')");
            $stmtOrd->bind_param("idddss", $id_utente, $totale_prodotti, $costo_spedizione, $totale_finale, $indirizzo_spedizione, $metodo_pagamento);
            $stmtOrd->execute();
            $id_ordine = $stmtOrd->insert_id;

            // B. Sposto le righe
            $stmtDett = $this->connection->prepare("INSERT INTO ordine_elemento (id_ordine, id_vino, nome_vino_storico, quantita, prezzo_acquisto) VALUES (?, ?, ?, ?, ?)");

            foreach ($items as $item) {
                $stmtDett->bind_param("iisid", 
                    $id_ordine, 
                    $item['id_vino'], 
                    $item['nome'], 
                    $item['quantita'], 
                    $item['prezzo']
                );
                $stmtDett->execute();
            }

            // C. Svuoto il carrello
            $id_carrello = $this->getCarrelloId($id_utente);
            $stmtDelCart = $this->connection->prepare("DELETE FROM carrello_elemento WHERE id_carrello = ?");
            $stmtDelCart->bind_param("i", $id_carrello);
            $stmtDelCart->execute();

            $this->connection->commit();
            return ["success" => true, "id_ordine" => $id_ordine];

        } catch (Exception $e) {
            $this->connection->rollback();
            return ["success" => false, "error" => $e->getMessage()];
        }
    }

    public function eliminaAccount($id_utente) {
        // quando cancelliamo l'utente, gli ordini non vengono cancellati.
        // Il loro campo 'id_utente' diventerà NULL automaticamente.
        // I dati storici di vendita rimangono salvi.

        $stmt = $this->connection->prepare("DELETE FROM utente WHERE id = ?");
        $stmt->bind_param("i", $id_utente);
        
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // RECUPERO GLI ORDINI DI UN UTENTE
    public function getOrdiniUtente($id_utente) {
        $ordini = [];
        
        // 1. Query per gli ordini principali (dal più recente)
        $queryOrdini = "SELECT * FROM ordine WHERE id_utente = ? ORDER BY data_creazione DESC";
        $stmtOrd = $this->connection->prepare($queryOrdini);
        if (!$stmtOrd) { return []; }

        $stmtOrd->bind_param("i", $id_utente);
        $stmtOrd->execute();
        $resultOrd = $stmtOrd->get_result();

        while ($ordine = $resultOrd->fetch_assoc()) {
            $ordine['elementi'] = $this->getDettagliOrdine($ordine['id']);
            $ordini[] = $ordine;
        }
        
        $stmtOrd->close();
        return $ordini;
    }


        // RECUPERO GLI ORDINI DI TUTTI GLI UTENTI (Per Admin)
    public function getOrdini() {
        $ordini = [];
        
        // 1. Query per gli ordini (dal più recente)
        $queryOrdini = "SELECT * FROM ordine ORDER BY data_creazione DESC";
        $stmtOrd = $this->connection->prepare($queryOrdini);
        if (!$stmtOrd) { return []; }

        $stmtOrd->execute();
        $resultOrd = $stmtOrd->get_result();

        while ($ordine = $resultOrd->fetch_assoc()) {
            $ordine['elementi'] = $this->getDettagliOrdine($ordine['id']);
            $ordini[] = $ordine;
        }
        
        $stmtOrd->close();
        return $ordini;
    }

    // RECUPERO I DETTAGLI (ELEMENTI) DI UN SINGOLO ORDINE
    private function getDettagliOrdine($id_ordine) {
        $elementi = [];
        
        $queryElementi = "SELECT * FROM ordine_elemento WHERE id_ordine = ?";
        $stmtElem = $this->connection->prepare($queryElementi);
        if (!$stmtElem) { return []; }

        $stmtElem->bind_param("i", $id_ordine);
        $stmtElem->execute();
        $resultElem = $stmtElem->get_result();

        while ($elemento = $resultElem->fetch_assoc()) {
            $elementi[] = $elemento;
        }

        $stmtElem->close();
        return $elementi;
    }

    // STATISTICHE UTENTE (Per Dashboard)
    public function getUserStats($id_utente) {
        // Calcola totale speso e numero ordini
        $query = "SELECT COUNT(*) as num_ordini, SUM(totale_finale) as totale_speso 
                  FROM ordine WHERE id_utente = ?";
        
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $id_utente);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        // Se non ci sono ordini, SUM restituisce NULL, convertiamo in 0
        if ($data['totale_speso'] === null) {
            $data['totale_speso'] = 0.00;
        }
        
        $stmt->close();
        return $data;
    }

    // CAMBIA PASSWORD UTENTE
    public function cambiaPassword($id_utente, $vecchia_password, $nuova_password) {
        // 1. Prendo la password attuale (hash)
        $stmt = $this->connection->prepare("SELECT password FROM utente WHERE id = ?");
        $stmt->bind_param("i", $id_utente);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$res) { return false; }

        // 2. Verifico se la vecchia password coincide
        if (!password_verify($vecchia_password, $res['password'])) {
            return false; // Vecchia password errata
        }

        // 3. Hash della nuova password e aggiornamento
        $nuovoHash = password_hash($nuova_password, PASSWORD_DEFAULT);
        $stmtUpd = $this->connection->prepare("UPDATE utente SET password = ? WHERE id = ?");
        $stmtUpd->bind_param("si", $nuovoHash, $id_utente);
        $result = $stmtUpd->execute();
        $stmtUpd->close();

        return $result;
    }
}
?>