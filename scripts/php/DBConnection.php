<?php
namespace DB;

use Exception;
use mysqli; 

require_once __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

class DBConnection {
    
    private $connection;

    public function __construct() {
        try {
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

    // CREAZIONE UTENTE (Admin)
    public function creaUtenteAdmin($nome, $cognome, $email, $password, $ruolo) {
        $ruoli_permessi = ['user', 'staff', 'admin'];
        if (!in_array($ruolo, $ruoli_permessi, true)) {
            return 0;
        }

        $queryControllo = "SELECT id FROM utente WHERE email = ?"; 
        $stmt = $this->connection->prepare($queryControllo);
        if (!$stmt) { return 0; }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $stmt->close();
            return -1;
        }
        $stmt->close();

        $query = "INSERT INTO utente (nome, cognome, email, password, ruolo) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->connection->prepare($query);
        if (!$stmt) { return 0; }
        $stmt->bind_param("sssss", $nome, $cognome, $email, $password, $ruolo);
        $result = $stmt->execute();
        $stmt->close();

        return $result ? 1 : 0;
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
        $stmt = $this->connection->prepare("SELECT nome, cognome, email, indirizzo, citta, cap, provincia, prefisso, telefono FROM utente WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // AGGIORNAMENTO SESSIONE
    public function checkUserStatus($id_utente) {
        $query = "SELECT id, nome, cognome, ruolo, email FROM utente WHERE id = ?";
        
        $stmt = $this->connection->prepare($query);
        if (!$stmt) return null;
        
        $stmt->bind_param("i", $id_utente);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        return $user; 
    }


    public function salvaMessaggio($nome, $cognome, $email, $tipo_supporto, $prefisso, $telefono, $messaggio) {
        
        // Query di inserimento nella nuova tabella
        $query = "INSERT INTO contatto_archivio 
                 (nome, cognome, email, tipo_supporto, prefisso, telefono, messaggio, data_invio, stato) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'aperto')";

        $stmt = $this->connection->prepare($query);
        
        if (!$stmt) {
            return false; 
        }

        // "sssssss" indica che sono 7 stringhe
        $stmt->bind_param("sssssss", 
            $nome, 
            $cognome, 
            $email, 
            $tipo_supporto, 
            $prefisso, 
            $telefono, 
            $messaggio
        );

        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }


    // FUNZIONE PER SALVARE UNA PRENOTAZIONE
    public function salvaPrenotazione($nome, $cognome, $email, $tipo_degustazione, $prefisso, $telefono, $data_visita, $n_persone) {
        $query = "INSERT INTO prenotazione_archivio (nome, cognome, email, tipo_degustazione, prefisso, telefono, data_visita, n_persone, data_invio, stato) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'in_attesa')";
        
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
                       FROM prenotazione_archivio WHERE id = ?";
        
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

    // FUNZIONE ARCHIVIA MESSAGGIO
    public function archiviaMessaggio($id, $risposta) {
        $sql = "UPDATE contatto_archivio
                SET risposta = ?, stato = 'risposto'
                WHERE id = ?";

        $stmt = $this->connection->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("si", $risposta, $id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    // ============================================================
    // SEZIONE E-COMMERCE
    // ============================================================

    // RECUPERO LISTA VINI
    public function getVini() {
        $query = "SELECT * FROM vino WHERE stato = 'attivo'";
        $result = $this->connection->query($query);
        
        $vini = [];
        while ($row = $result->fetch_assoc()) {
            $vini[] = $row;
        }
        return $vini;
    }

    // RECUPERO UN SINGOLO VINO
    public function getVino($id) {
        $stmt = $this->connection->prepare("SELECT * FROM vino WHERE id = ? AND stato = 'attivo'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc(); 
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
            $nuovaQuantita = $row['quantita'] + $quantita;
            $stmtUpdate = $this->connection->prepare("UPDATE carrello_elemento SET quantita = ?, stato = 'attivo' WHERE id = ?");
            $stmtUpdate->bind_param("ii", $nuovaQuantita, $row['id']);
            return $stmtUpdate->execute();
        } else {
            $stmtInsert = $this->connection->prepare("INSERT INTO carrello_elemento (id_carrello, id_vino, quantita, stato) VALUES (?, ?, ?, 'attivo')");
            $stmtInsert->bind_param("iii", $id_carrello, $id_vino, $quantita);
            return $stmtInsert->execute();
        }
    }

    // VISUALIZZA CARRELLO COMPLETO
    public function getCarrelloUtente($id_utente) {
        $id_carrello = $this->getCarrelloId($id_utente);
        
        $query = "SELECT ec.id as id_riga, ec.stato, v.id as id_vino, v.stato as stato_vino, v.nome, v.descrizione_breve, v.img, v.prezzo, v.quantita_stock, ec.quantita, (v.prezzo * ec.quantita) as totale_riga 
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

    // RECUPERA VINO ANCHE SE NON ATTIVO (Per il carrello ospiti)
    public function getVinoPerCarrello($id) {
        // A differenza di getVino(), qui NON filtriamo per stato='attivo'
        $stmt = $this->connection->prepare("SELECT * FROM vino WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc(); 
    }

    // CAMBIA STATO ELEMENTO CARRELLO
    public function cambiaStatoElemento($id_riga, $nuovo_stato) {
        $stmt = $this->connection->prepare("UPDATE carrello_elemento SET stato = ? WHERE id = ?");
        $stmt->bind_param("si", $nuovo_stato, $id_riga);
        return $stmt->execute();
    }

    // AGGIORNA QUANTITÀ ESATTA
    public function aggiornaQuantitaCarrello($id_utente, $id_vino, $nuova_quantita) {
        $id_carrello = $this->getCarrelloId($id_utente);
        
        if ($nuova_quantita <= 0) {
            $stmt = $this->connection->prepare("DELETE FROM carrello_elemento WHERE id_carrello = ? AND id_vino = ?");
            $stmt->bind_param("ii", $id_carrello, $id_vino);
            return $stmt->execute();
        }

        $stmt = $this->connection->prepare("UPDATE carrello_elemento SET quantita = ? WHERE id_carrello = ? AND id_vino = ?");
        $stmt->bind_param("iii", $nuova_quantita, $id_carrello, $id_vino);
        return $stmt->execute();
    }

    // RIMUOVI ELEMENTO DAL CARRELLO
    public function rimuoviDaCarrello($id_elemento_carrello) {
        $stmt = $this->connection->prepare("DELETE FROM carrello_elemento WHERE id = ?");
        $stmt->bind_param("i", $id_elemento_carrello);
        return $stmt->execute();
    }

    // CHECKOUT: CREAZIONE ORDINE
    public function creaOrdine($id_utente, $indirizzo_spedizione, $costo_spedizione = 10.00) {
        $allItems = $this->getCarrelloUtente($id_utente);
        $items = array_filter($allItems, function($i) { return $i['stato'] === 'attivo'; });
        
        if (empty($items)) {
            return ["success" => false, "error" => "Il carrello attivo è vuoto"];
        }

        $totale_prodotti = 0;
        foreach ($items as $item) {
            if($item['quantita'] > $item['quantita_stock']){
                 return ["success" => false, "error" => "Attenzione: " . $item['nome'] . " è terminato."];
            }
            $totale_prodotti += $item['totale_riga'];
        }
        $totale_finale = $totale_prodotti + $costo_spedizione;

        $this->connection->begin_transaction();

        try {
            $stmtOrd = $this->connection->prepare("INSERT INTO ordine (id_utente, totale_prodotti, costo_spedizione, totale_finale, indirizzo_spedizione, stato_ordine) VALUES (?, ?, ?, ?, ?, 'in_attesa')");
            $stmtOrd->bind_param("iddds", $id_utente, $totale_prodotti, $costo_spedizione, $totale_finale, $indirizzo_spedizione);
            $stmtOrd->execute();
            $id_ordine = $stmtOrd->insert_id;

            $stmtDett = $this->connection->prepare("INSERT INTO ordine_elemento (id_ordine, id_vino, nome_vino_storico, quantita, prezzo_acquisto) VALUES (?, ?, ?, ?, ?)");
            $stmtUpdateStock = $this->connection->prepare("UPDATE vino SET quantita_stock = quantita_stock - ? WHERE id = ?");

            foreach ($items as $item) {
                $stmtDett->bind_param("iisid", 
                    $id_ordine, 
                    $item['id_vino'], 
                    $item['nome'], 
                    $item['quantita'], 
                    $item['prezzo']
                );
                $stmtDett->execute();
                
                $stmtUpdateStock->bind_param("ii", $item['quantita'], $item['id_vino']);
                $stmtUpdateStock->execute();
            }

            $id_carrello = $this->getCarrelloId($id_utente);
            $stmtDelCart = $this->connection->prepare("DELETE FROM carrello_elemento WHERE id_carrello = ? AND stato = 'attivo'");
            $stmtDelCart->bind_param("i", $id_carrello);
            $stmtDelCart->execute();

            $this->connection->commit();
            return ["success" => true, "id_ordine" => $id_ordine];

        } catch (Exception $e) {
            $this->connection->rollback();
            return ["success" => false, "error" => $e->getMessage()];
        }
    }

    // ELIMINAZIONE ACCOUNT
    public function eliminaAccount($id_utente) {
        $stmt = $this->connection->prepare("DELETE FROM utente WHERE id = ?");
        $stmt->bind_param("i", $id_utente);
        
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    
    // ============================================================
    // SEZIONE ADMIN - GESTIONE VINI
    // ============================================================

    // 1. RECUPERA TUTTI I VINI (Anche nascosti)
    public function getTuttiViniAdmin() {
        $result = $this->connection->query("SELECT * FROM vino ORDER BY id DESC"); 
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // 2. INSERISCI NUOVO VINO
    public function inserisciVino($dati) {
        $query = "INSERT INTO vino (nome, prezzo, quantita_stock, stato, img, categoria, descrizione_breve, descrizione_estesa, vitigno, annata, gradazione, temperatura, abbinamenti) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("sdissssssssss", $dati['nome'], $dati['prezzo'], $dati['quantita_stock'], $dati['stato'], $dati['img'], $dati['categoria'], $dati['descrizione_breve'], $dati['descrizione_estesa'], $dati['vitigno'], $dati['annata'], $dati['gradazione'], $dati['temperatura'], $dati['abbinamenti']);
        return $stmt->execute();
    }

    // 3. MODIFICA VINO
    public function modificaVino($id, $dati) {
        $query = "UPDATE vino SET nome=?, prezzo=?, quantita_stock=?, stato=?, img=?, categoria=?, descrizione_breve=?, descrizione_estesa=?, vitigno=?, annata=?, gradazione=?, temperatura=?, abbinamenti=? WHERE id=?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("sdissssssssssi", $dati['nome'], $dati['prezzo'], $dati['quantita_stock'], $dati['stato'], $dati['img'], $dati['categoria'], $dati['descrizione_breve'], $dati['descrizione_estesa'], $dati['vitigno'], $dati['annata'], $dati['gradazione'], $dati['temperatura'], $dati['abbinamenti'], $id);
        return $stmt->execute();
    }

    // 4. CAMBIO STATO RAPIDO
    public function toggleStatoVino($id, $nuovoStato) {
        $stmt = $this->connection->prepare("UPDATE vino SET stato = ? WHERE id = ?");
        $stmt->bind_param("si", $nuovoStato, $id);
        return $stmt->execute();
    }

    // 5. ELIMINA VINO
    public function eliminaVino($id) {
        // Invece di DELETE FROM, facciamo un UPDATE
        $stmt = $this->connection->prepare("UPDATE vino SET stato = 'eliminato' WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // RIPRISTINA VINO (Da eliminato a nascosto)
    public function ripristinaVino($id) {
        $stmt = $this->connection->prepare("UPDATE vino SET stato = 'nascosto' WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }


    // 6. RECUPERA TUTTI GLI UTENTI (Admin)
    public function getUtentiAdmin() {
        $result = $this->connection->query("SELECT id, nome, cognome, email, ruolo, data_registrazione FROM utente ORDER BY id DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // 7. AGGIORNA RUOLO UTENTE (Admin)
    public function aggiornaRuoloUtente($id, $ruolo) {
        $ruoli_permessi = ['user', 'staff', 'admin'];
        if (!in_array($ruolo, $ruoli_permessi, true)) {
            return false;
        }

        $stmt = $this->connection->prepare("UPDATE utente SET ruolo = ? WHERE id = ?");
        $stmt->bind_param("si", $ruolo, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // 8. ELIMINA UTENTE (Admin)
    public function eliminaUtenteAdmin($id) {
        $stmt = $this->connection->prepare("DELETE FROM utente WHERE id = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // AGGIORNA STATO ORDINE (ADMIN)
    public function aggiornaStatoOrdine($id_ordine, $nuovo_stato) {
        $stati_permessi = ['in_attesa', 'approvato', 'annullato'];
        if (!in_array($nuovo_stato, $stati_permessi)) {
            return false;
        }

        $stmt = $this->connection->prepare("UPDATE ordine SET stato_ordine = ? WHERE id = ?");
        $stmt->bind_param("si", $nuovo_stato, $id_ordine);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // AGGIORNA STATO PRENOTAZIONE (ADMIN)
    public function aggiornaStatoPrenotazione($id_prenotazione, $nuovo_stato) {
        $stati_permessi = ['in_attesa', 'approvato', 'annullato'];
        if (!in_array($nuovo_stato, $stati_permessi)) {
            return false;
        }
        $stmt = $this->connection->prepare("UPDATE prenotazione_archivio SET stato = ? WHERE id = ?");
        $stmt->bind_param("si", $nuovo_stato, $id_prenotazione);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function aggiornaDatiSpedizione($id_utente, $indirizzo, $citta, $cap, $provincia, $prefisso, $telefono) {
        
        $query = "UPDATE utente 
                  SET indirizzo = ?, citta = ?, cap = ?, provincia = ?, prefisso = ?, telefono = ? 
                  WHERE id = ?";
        
        $stmt = $this->connection->prepare($query);
        if (!$stmt) { return false; }

        // "ssssssi" = 6 stringhe + 1 intero
        $stmt->bind_param("ssssssi", $indirizzo, $citta, $cap, $provincia, $prefisso, $telefono, $id_utente);
        
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    // SVUOTA COMPLETAMENTE IL CARRELLO (Per Admin/Staff)
    public function svuotaCarrelloUtente($id_utente) {
        $stmtCheck = $this->connection->prepare("SELECT id FROM carrello WHERE id_utente = ?");
        $stmtCheck->bind_param("i", $id_utente);
        $stmtCheck->execute();
        $res = $stmtCheck->get_result();

        if ($row = $res->fetch_assoc()) {
            $id_carrello = $row['id'];
            
            $stmt = $this->connection->prepare("DELETE FROM carrello_elemento WHERE id_carrello = ?");
            if (!$stmt) return false;
            
            $stmt->bind_param("i", $id_carrello);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }

        return true;
    }

    // RECUPERO ORDINI UTENTE
    public function getOrdiniUtente($id_utente) {
        $ordini = [];
        
        $queryOrdini = "SELECT * FROM ordine WHERE (id_utente = ?) ORDER BY data_creazione DESC";
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

    // RECUPERO PRENOTAZIONI UTENTE
    public function getPrenotazioniUtente($email) {
        $prenotazioni = [];

        $queryPrenotazioni = "SELECT * FROM prenotazione_archivio WHERE email = ? ORDER BY data_invio DESC";
        $stmtPren = $this->connection->prepare($queryPrenotazioni);
        if (!$stmtPren) { return []; }

        $stmtPren->bind_param("s", $email);
        $stmtPren->execute();
        $resultPren = $stmtPren->get_result();

        while ($prenotazione = $resultPren->fetch_assoc()) {
            $prenotazioni[] = $prenotazione;
        }

        $stmtPren->close();
        return $prenotazioni;
    }

    // RECUPERO TUTTI GLI ORDINI (GESTIONALE)
    public function getOrdini() {
        $ordini = [];
        
        $queryOrdini = "SELECT * FROM ordine WHERE stato_ordine='in_attesa' ORDER BY data_creazione DESC";
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

    public function getOrdiniArchivio() {
        $ordini = [];
        
        $queryOrdini = "SELECT * FROM ordine WHERE stato_ordine!='in_attesa' ORDER BY data_creazione DESC";
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


    // RECUPERO PRENOTAZIONI (GESTIONALE)
    public function getPrenotazioni() {
        $prenotazioni = [];
        
        $queryPrenotazioni = "SELECT * FROM prenotazione_archivio WHERE stato='in_attesa' ORDER BY data_invio DESC";
        $stmtPren = $this->connection->prepare($queryPrenotazioni);
        if (!$stmtPren) { return []; }

        $stmtPren->execute();
        $resultPren = $stmtPren->get_result();

        while ($prenotazione = $resultPren->fetch_assoc()) {
            $prenotazioni[] = $prenotazione;
        }
        
        $stmtPren->close();
        return $prenotazioni;
    }

    public function getPrenotazioniArchivio() {
        $prenotazioni = [];
        
        $queryPrenotazioni = "SELECT * FROM prenotazione_archivio WHERE stato!='in_attesa' ORDER BY data_invio DESC";
        $stmtPren = $this->connection->prepare($queryPrenotazioni);
        if (!$stmtPren) { return []; }

        $stmtPren->execute();
        $resultPren = $stmtPren->get_result();

        while ($prenotazione = $resultPren->fetch_assoc()) {
            $prenotazioni[] = $prenotazione;
        }
        
        $stmtPren->close();
        return $prenotazioni;
    }

    // RECUPERO MESSAGGI (GESTIONALE)
    public function getMessaggi() {
        $messaggi = [];
        $queryMessaggi = "SELECT * FROM contatto_archivio WHERE stato = 'aperto' ORDER BY data_invio DESC";
        $stmtMess = $this->connection->prepare($queryMessaggi);
        if (!$stmtMess) { return []; } 
        
        $stmtMess->execute();
        $resultMess = $stmtMess->get_result();

        while ($messaggio = $resultMess->fetch_assoc()) {
            $messaggi[] = $messaggio;
        }
        $stmtMess->close();
        return $messaggi;
    }

    // RECUPERO MESSAGGI ARCHIVIO (GESTIONALE)
    public function getMessaggiArchivio() {
        $messaggi = [];
        $queryMessaggi = "SELECT * FROM contatto_archivio WHERE stato != 'aperto' ORDER BY data_invio DESC";
        $stmtMess = $this->connection->prepare($queryMessaggi);
        if (!$stmtMess) { return []; } 
        
        $stmtMess->execute();
        $resultMess = $stmtMess->get_result();

        while ($messaggio = $resultMess->fetch_assoc()) {
            $messaggi[] = $messaggio;
        }
        $stmtMess->close();
        return $messaggi;
    }


    // AGGIORNA STATO MESSAGGIO (ADMIN)
    public function aggiornaStatoMessaggio(int $id, string $stato): bool {
        $sql = "UPDATE contatto SET stato = ? WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("si", $stato, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    
    // RECUPERO DETTAGLI ORDINE
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

    // STATISTICHE UTENTE
    public function getUserStats($id_utente) {
        $query = "SELECT COUNT(*) as num_ordini, SUM(totale_finale) as totale_speso 
                  FROM ordine WHERE id_utente = ?";
        
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $id_utente);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        if ($data['totale_speso'] === null) {
            $data['totale_speso'] = 0.00;
        }
        
        $stmt->close();
        return $data;
    }

    // CAMBIA PASSWORD
    public function cambiaPassword($id_utente, $vecchia_password, $nuova_password) {
        $stmt = $this->connection->prepare("SELECT password FROM utente WHERE id = ?");
        $stmt->bind_param("i", $id_utente);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$res) { return false; }

        if (!password_verify($vecchia_password, $res['password'])) {
            return false;
        }

        $nuovoHash = password_hash($nuova_password, PASSWORD_DEFAULT);
        $stmtUpd = $this->connection->prepare("UPDATE utente SET password = ? WHERE id = ?");
        $stmtUpd->bind_param("si", $nuovoHash, $id_utente);
        $result = $stmtUpd->execute();
        $stmtUpd->close();

        return $result;
    }
}
?>
