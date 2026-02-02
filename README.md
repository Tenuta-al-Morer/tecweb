<div align="center">

<img src="assets_relazione/logo.png" width="200"/><br/>

<h1 style="margin-bottom:0; padding-bottom:5px;">Relazione di progetto Tenuta al Morer</h1><h3 style="margin-top:0; padding-top:0; margin-bottom:10px;">Corso di Tecnologie Web A.A. 2025-26</h3>

<div><h3 style="margin-bottom: 0; margin-top: 15px; font-size: 1.17em; font-weight: bold;">Autori</h3>Luca Marcuzzo, matricola 2113198, luca.marcuzzo.1@studenti.unipd.it<br/>Michele Stevanin, matricola 2101741, michele.stevanin@studenti.unipd.it<br/>Giovanni Visentin, matricola 2101064, giovanni.visentin.7@studenti.unipd.it<br/>Alessandro Contarini, matricola 2101052, alessandro.contarini.1@studenti.unipd.it (referente)<br/><h3 style="margin-bottom: 0; margin-top: 15px; font-size: 1.17em; font-weight: bold;">Sito web</h3>https://tecweb.studenti.math.unipd.it/acontari<br/><h3 style="margin-bottom: 0; margin-top: 15px; font-size: 1.17em; font-weight: bold;">Repository GitHub</h3>https://github.com/Tenuta-al-Morer/tecweb<br/><h3 style="margin-bottom: 0; margin-top: 15px; font-size: 1.17em; font-weight: bold;">Credenziali</h3>Utente: user / user<br/>Staff: staff / staff<br/>Admin: admin / admin</div>

</div><br/><hr/><br/>

## Introduzione

La presente relazione ha come scopo quello di descrivere le metodologie e i ragionamenti che abbiamo applicato per la realizzazione del progetto per il corso di Tecnologie Web (Laurea in Informatica - L31) dell’anno accademico 2025-2026. Il progetto realizzato prevede la creazione di un sito web accessibile per la cantina vitivinicola "Tenuta al Morer".

Il sito offre diverse funzionalità agli utenti clienti della Tenuta:

- consultazione di contenuti informativi e descrittivi sulla Tenuta;

- visualizzazione del catalogo dei vini disponibili;

- acquisto di prodotti enologici, previa registrazione e creazione di un account personale;

- prenotazione di esperienze di degustazione;

- gestione dell’area personale con storico ordini e prenotazioni;

- invio di richieste di assistenza o segnalazioni tramite l’apposita sezione contatti.

Mentre, per quanto riguarda gli impiegati della Tenuta, la piattaforma offre:

- possibilità di gestione delle attività della cantina richieste dal cliente.

Infine, l’utente amministratore, oltre a quanto permesso allo staff, può:

- gestire il catalogo dei vini;

- gestire i permessi degli utenti registrati sulla piattaforma.

L’obiettivo principale è stato quello di sviluppare un’esperienza intuitiva e completa per gli utenti, semplificando l’interazione tra clienti e lavoratori della cantina e migliorando l’accesso alle informazioni ad essa relative.

## Analisi dei requisiti

Prima di avviare lo sviluppo del sito, abbiamo analizzato diversi siti web di cantine già esistenti per identificare le informazioni principali da includere. A partire da questi spunti, abbiamo integrato la nostra visione e i servizi aggiuntivi che intendiamo offrire, definendo così la struttura gerarchica del sito di Tenuta al Morer.

Le pagine sono state progettate per essere semplici da usare e visivamente accattivanti, con particolare attenzione all’ottimizzazione per dispositivi mobile. In questa fase abbiamo anche stabilito convenzioni interne per garantire un’esperienza utente coerente e intuitiva.

### Analisi utente

Tenuta al Morer si presenta come un sito fruibile da chiunque sia alla ricerca di vini di qualità o di informazioni enologiche. Il sito si rivolge a un pubblico eterogeneo, accogliendo sia utenti con conoscenze di base sul mondo del vino sia chi non ne sa nulla; adotta un linguaggio tecnico dove necessario, ma resta nel complesso facilmente comprensibile, così da coinvolgere e accompagnare anche l’utente meno esperto che desidera informarsi e apprendere nuove conoscenze.

Struttura e layout sono semplici per permettere all’utente di familiarizzare facilmente con la navigazione del sito. Ci aspettiamo che la maggior parte degli utenti lo utilizzi con browser piuttosto recenti e che la tendenza agli accessi tramite mobile avvenga soprattutto per attività associate alla gestione delle proprie prenotazioni e alla ricerca veloce di vini.

Il target dell’età degli utenti si stima sia compreso tra i 25 e i 70 anni, dato il tipo di servizio offerto.

### SEO

Di seguito vengono riportate le ricerche alle quali il sito vuole rispondere:

- il nome del sito ("Tenuta al Morer");

- tutte le ricerche che contengano nomi di vini presenti nel sito;

- tutte le ricerche sull’argomento delle degustazioni in cantina;

- ricerche più generali come "cantina veneta", "vino", "acquisto vino online", "degustazione vini", ...

Le parole chiave selezionate sono state pensate per rivolgersi sia ad utenti che hanno già un’idea chiara di cosa stanno cercando, sia a nuovi utenti che cercano di apprendere maggiori informazioni durante la navigazione.

Operazioni svolte per migliorare il ranking del sito:

- in ogni pagina sono state definite parole chiave coerenti con il contenuto, utilizzate nei testi e nei meta tag rilevanti (come title e description);

- separazione tra struttura, presentazione e comportamento;

- ponendosi come obiettivo il miglioramento del ranking del sito, si è lavorato sull’ottimizzazione delle prestazioni, rendendo le pagine leggere attraverso la compressione delle immagini e l’utilizzo di formati adeguati, poiché una maggiore velocità di caricamento migliora il rendering e contribuisce indirettamente al posizionamento.

## Progettazione

### Schema organizzativo

Abbiamo definito una struttura chiara che consenta di navigare facilmente tra i prodotti disponibili e le relative opzioni di acquisto. Il catalogo dei vini è infatti strutturato secondo uno schema organizzativo esatto, ottenuto suddividendo i prodotti in categorie non sovrapponibili - “vini rossi”, “vini bianchi” e “La Selezione” - al fine di facilitare la consultazione da parte dell’utente. È inoltre presente una funzione di ricerca che permette di individuare rapidamente un vino all’interno del catalogo, agevolando l’utente che sa esattamente cosa cercare e desidera risparmiare tempo.

### Tipi di utente

Durante la fase di progettazione sono stati individuati i seguenti tipi di utente:

- **Utente non autenticato**: l’utente ospite ha accesso alle sezioni non private del sito, ossia le pagine "Home", "Tenuta", "Vini", "Esperienze" e "Contatti". Può prenotare degustazioni, inviare richieste di assistenza e ha la possibilità di inserire vini nel carrello. Tuttavia, non può effettuare acquisti né accedere all’area riservata, a meno che non completi la registrazione o effettui l’accesso.

- **Cliente**: ha accesso completo alle funzionalità del sito relative agli utenti clienti, come la possibilità di acquistare vini e gestire il proprio account all’interno della propria area riservata.

- **Staff**: l’utente staff può gestire gli ordini e le prenotazioni dei clienti, approvandole o rifiutandole. Inoltre, ha il controllo delle richieste di assistenza ricevute tramite il modulo contatti.

- **Admin**: l’utente admin può gestire tutti gli aspetti del sito, inclusi gli ordini degli utenti, le prenotazioni, i messaggi di assistenza, il catalogo dei vini e la gestione degli utenti registrati.

### Funzionalità

Elenco delle funzionalità del sito:

- registrazione utente;

- login cliente/staff/admin;

- visualizzazione catalogo vini;

- visualizzazione dei dettagli di uno specifico vino;

- ricerca testuale dei prodotti nel catalogo;

- acquisto vini (cliente);

- gestione carrello (cliente);

- checkout e creazione ordini (cliente);

- prenotazione esperienze;

- invio richieste di assistenza;

- gestione account cliente (modifica dati, cambio password, eliminazione account);

- consultazione storico ordini e stato prenotazioni nell’area riservata (cliente);

- gestione ordini (staff/admin);

- gestione prenotazioni (staff/admin);

- gestione messaggi assistenza (staff/admin);

- gestione catalogo vini (admin);

- gestione utenti registrati (admin).

### Convenzioni interne

Elenco delle convenzioni interne del sito:

- l’adozione delle UI Cards in sostituzione degli elenchi testuali permette di raggruppare i concetti in unità visive distinte e intuitive, riducendo significativamente il carico cognitivo dell’utente necessario per elaborare le informazioni contenute in pagine come: "Home", "Contatti", "Area Personale";

- le pagine di autenticazione (*login.html* e *registrazione.html*) hanno un layout semplificato con header ridotto, al fine di concentrare l’esperienza utente alle sole funzioni di accesso;

- ad eccezione delle sezioni precedentemente citate, le restanti pagine presentano nell’area definita "above the fold" un menù di navigazione principale che permetta all’utente di orientarsi facilmente e identificare immediatamente i percorsi disponibili. Inoltre, per agevolare l’esperienza di navigazione, vengono distinti i link già visitati (*secondary-color*) da quelli non ancora consultati (in bianco);

### Schema database

Le principali tabelle del database sono:

- **utente**: memorizza gli account degli utenti con i relativi ruoli

- **vino**: memorizza il catalogo completo dei prodotti vinicoli

- **carrello** e **carrello_elemento**: servono per la gestione del carrello

- **ordine** e **ordine_elemento**: servono per la gestione degli ordini effettuati

- **prenotazione**: memorizza le prenotazioni delle esperienze

- **contatto**: memorizza i messaggi di assistenza

<figure data-latex-placement="H">
<img src="assets_relazione/schema_relazionale.png" style="width:90.0%" />
<figcaption>Schema del database "Tenuta al Morer"</figcaption>
</figure>

## Realizzazione

Per la realizzazione del sito abbiamo utilizzato dati relativi a vini realmente esistenti nella tradizione vitivinicola veneta. Le immagini dei prodotti e della tenuta sono state generate tramite strumenti di intelligenza artificiale con l’obbiettivo di ottenere una resa visiva coerente con il layout del sito, mantenendo comunque i riferimenti autentici al territorio e ai nomi originali dei prodotti.

### Struttura e contenuto

#### HTML

Il sito è stato sviluppato in HTML5. Abbiamo cercato di mantenere più struttura possibile nei file HTML ed eventualmente andare a lavorare e sostituire alcune parti con il PHP. Per sostituire singole parole, abbiamo utilizzato come segnaposto delle parole racchiuse tra parentesi quadre (\[segnaposto\]). Invece per sostituire intere sezioni è stato utilizzato il metodo preg_replace() di PHP, rispettando un certo pattern.

Un esempio è l’utilizzo nel file areaPersonale.html:

    <p>
        Sei loggato come: <span class="bold">[email_utente]</span>
    </p>

Questo approccio ci permette di impostare una struttura fissa nei file HTML e andare a modificare in maniera dinamica il contenuto tramite PHP.

#### Popolamento database

Per il popolamento del database abbiamo adottato un duplice approccio. La tabella dei vini è stata creata manualmente: trattandosi di un numero limitato di prodotti, abbiamo preferito inserire direttamente informazioni precise.

Al contrario, per le sezioni dedicate all’assistenza, agli ordini e alle prenotazioni delle esperienze, abbiamo scelto di generare i dati tramite Intelligenza Artificiale. Questa soluzione ci ha permesso di ottenere rapidamente un insieme completo di dati.

### Presentazione

#### CSS

Uno degli aspetti più importanti del nostro CSS è l’utilizzo delle variabili, impostate all’inizio del file e riutilizzate più volte in parti differenti. In questo modo siamo riusciti ad uniformare e tenere sotto controllo i contrasti e i colori utilizzati. Particolare attenzione è stata posta anche all’utilizzo di layout di tipo flex e grid. Data la loro pensantezza di renderizzazione per i browser, ne è stato fatto un uso consapevole, cercando di evitare di superare il secondo livello.

In secondo luogo, si evidenzia l’utilizzo di tre differenti fogli di stile: style.css, mini.css e print.css. Questa suddivisione è stata adottata per organizzare propriamente il codice CSS per gestire al meglio l’aspetto responsive del sito e garantire un adeguato layout di stampa in caso di occorrenza.

#### Gestione della classe no-js

Per garantire la corretta visualizzazione e fruibilità del sito, indipendentemente dal supporto JavaScript del browser, è stata adottata la classe `no-js`.

In particolare, il tag `<html>` viene inizializzato con la classe `no-js`; nella sezione `<head>` è incluso **script.js** che viene eseguito immediatamente al caricamento della pagina: se JavaScript è abilitato, la classe `no-js` viene rimossa.

Questo meccanismo permette di sfruttare i fogli di stile CSS per gestire due stati distinti dell’interfaccia:

- **Stato senza JavaScript**: grazie al selettore `.no-js`, vengono applicati stili di fallback. Ad esempio, elementi che richiedono interazione dinamica (come slider o menu complessi) vengono mostrati in una forma statica e accessibile, evitando che l’utente visualizzi controlli non funzionanti.

- **Stato con JavaScript**: la rimozione della classe permette l’applicazione degli stili dedicati alle funzionalità interattive avanzate.

Questa strategia assicura che il contenuto rimanga sempre usabile accessibile, delegando a JavaScript solo l’arricchimento dell’esperienza utente e non le funzionalità critiche.

#### CSS-Print

Per la versione di stampa è stata posta particolare attenzione alla leggibilità su carta e alla visualizzazione dei contenuti informativi. In particolare, è stato impostato un font con le grazie (*Times New Roman*) in sostituzione di quello web e sono stati rimossi elementi grafici ritenuti non necessari (decorativi) al fine di garantire un layout più pulito e un risparmio di inchiostro (*PrintFriendly*).

Nello specifico:

- sono stati nascosti tutti gli elementi interattivi e di navigazione (navbar, pulsanti di azione e carrello vuoto);

- sono state rimosse le immagini puramente decorative, mantenendo visibili solo il logo e i contenuti grafici informativi;

- la struttura della pagina è stata semplificata linearizzando i contenuti per adattarli al formato cartaceo verticale;

- Per garantire una stampa eco-compatibile (PrintFriendly), l’interfaccia "cartacea" è stata limitata alla sola modalità light-mode. Tale scelta mira a ridurre drasticamente il consumo di inchiostro, evitando la stampa di sfondi scuri.

#### Immagini e icone

La gestione delle risorse grafiche è stata diversificata in base alla tipologia di contenuto per bilanciare qualità e performance.

Le immagini relative ai prodotti (vini) e agli elementi grafici dell’interfaccia (come i loghi) sono stati salvati in formato **WebP**. Questa scelta, visibile nella sottocartella `tr`, è dettata sia dalla necessità di supportare lo sfondo trasparente, sia dal mantenere le rimensioni ridotte rispetto a formati come **PNG**. Le immagini della Tenuta e delle esperienze (come *vigneto.jpg* o *vendemmia.jpg*) utilizzano il formato **JPG**. Questa scelta è motivata dall’ottimo rapporto tra qualità e compressione del formato, ideale per gestire fotografie complesse che non richiedono trasparenza.

Tutte le immagini sono state ottimizzate mantenendo una dimensione ridotta (sotto 0.3MB) al fine di favorire la velocità di rendering.

Per quanto riguarda l’inserimento di nuovi prodotti tramite l’area riservata (admin), il sistema di upload permette all’amministratore di caricare immagini in diversi formati standard, senza restrizioni stringenti sul tipo di file in ingresso.

#### Font

Abbiamo usato il font **Atkinson Hyperlegible** per tutto il sito web poichè accessibile e senza grazie. Abbiamo applicato un’interlinea di 1.5 em per facilitare la lettura. Sono presenti inoltre dei font di fallback: Lexend e Roboto.

#### Colori

Per garantire che il sito sia accessibile e che tutti gli utenti (inclusi coloro con difficoltà visive) possano navigarlo facilmente, abbiamo scelto una palette di colori con particolare attenzione al contrasto e alla leggibilità. Abbiamo selezionato i colori assicurandoci che il contrasto tra il testo e lo sfondo sia sufficientemente elevato, in conformità alle linee guida WCAG 2.1 di livello AA.

Dopo diversi tentativi di ottimizzazione dei colori, abbiamo definito e adottato la seguente palette:

| **Colore**        | **Codice HEX** |
|:------------------|:--------------:|
| Testo principale  |    \#E0E0E0    |
| Tema sfondo       |    \#1D1D1D    |
| Colore secondario |    \#C5A551    |
| Colore errori     |    \#FF2934    |
| Colore successo   |    \#176F3A    |
| Colore attenzione |    \#FFC107    |
| Colore bordo      |    \#B6B6B6    |

Palette colori modalità scura

| **Colore**        | **Codice HEX** |
|:------------------|:--------------:|
| Testo principale  |    \#121212    |
| Tema sfondo       |    \#F4F4F4    |
| Colore secondario |    \#7A5F1A    |
| Colore errori     |    \#990109    |
| Colore successo   |    \#02771D    |
| Colore attenzione |    \#856404    |
| Colore bordo      |    \#4B4B4B    |

Palette colori modalità chiara

### Comportamento

#### PHP

Nelle sezioni contenenti form abbiamo implementato, nei casi di errore, un algoritmo per il ripopolamento dei vari campi di input presenti tramite l’utilizzo della tecnica "segnaposto" descritta nella sezione 4.1.1. Questa soluzione evita che l’utente debba reinserire tutte le informazioni in caso di invio del form non andato a buon fine.

I file principali con cui viene gestita l’infrastruttura PHP sono:

- DBConnection.php: gestisce la connessione al database e contiene i vari metodi per effettuare le query SQL;

- common.php: gestisce parti comuni nelle varie pagine html come footer e icone mobili in alto a destra;

- un file .php associato ad ogni pagina .html. Unica eccezione riguarda il file logout.php che viene richiamato nelle pagine areaPersonale.html e gestionale.html.

#### JavaScript

Sono stati implementati script dedicati alla validazione lato client dei moduli, garantendo così una netta separazione tra la struttura semantica (HTML) e il livello comportamentale (JavaScript). L’uso di JavaScript è finalizzato al miglioramento della User Experience (UX) e della reattività dell’interfaccia. Inoltre, i controlli eseguiti lato client tramite JavaScript, e successivamente replicati lato server in PHP, permettono di intercettare gli errori in fase preliminare, riducendo il carico complessivo ed evitando la formazione di colli di bottiglia.

È fondamentale notare che l’intero progetto è stato sviluppato secondo il principio del Progressive Enhancement: tutte le funzionalità essenziali rimangono pienamente operative anche in assenza di JavaScript, il quale agisce esclusivamente come livello aggiuntivo di ottimizzazione.

#### Validazione dell’input

Abbiamo eseguito controlli sull’input sia lato client, utilizzando Javascript, che lato server tramite PHP. La maggior parte dei controlli è stata eseguita tramite funzioni che controllano con delle espressioni regolari il contenuto degli input. Inoltre, ogni messaggio di errore ritornato dai metodi di validazione sopra elencati è stato pensato per fornire all’utente una spiegazione chiara e concisa del problema.

#### Sicurezza

In ambito sicurezza sono state implementate le seguenti precauzioni:

- tutte le query in SQL vengono eseguite tramite librerie mysqli che vanno ad utilizzare i "prepared statements". Questo permette di vanificare tentativi di SQL Injection;

- le password presenti non vengono scritte in chiaro nel database, ma vengono prima cifrate tramite un algoritmo di hashing e solo successivamente salvate.

#### Errori di navigazione o del server

Le direttive che si occupano degli errori di navigazione sono state inserite nell’opportuno file .htaccess. Se l’utente visita un link errato o che non esiste, viene mostrata una pagina di tipo 404 personalizzata. Inoltre, per errori lato server, come per esempio problemi di collegamento con il database, viene mostrata una pagina personalizzata di tipo 500. Infine, se l’utente visita pagine per cui non ha i permessi, viene mostrata una pagina personalizzata di tipo 403. Queste pagine hanno principalmente scopo informativo e di aiuto per l’utente.

### Accessibilità

Di seguito sono elencate tutte le scelte effettuate per migliorare l’accessibilità del sito. Ognuna mira almeno al soddisfacimento del livello di conformità AA delle WCAG 2.1 come stabilito dalla legge italiana (ed europea):

- navigazione da tastiera completa e accessibile che rispetta l’ordine visivo degli elementi;

- tabelle accessibili;

- form accessibili con label associate correttamente agli input (feedback e feedforward chiari e utili a tutte le categorie di utenti);

- per garantire l’orientamento dell’utente abbiamo verificato che nel "above the fold" di ogni pagina fosse semplice e immediato rispondere alle domande: "Dove sono? - Dove posso andare? - Di che cosa si tratta?";

- i contrasti tra i colori di testo e relativo sfondo sono accessibili;

- è stata adottata una gerarchia del sito ampia e poco profonda;

- sono stati utilizzati i tag *\<abbr\>* per esplicitare le abbreviazioni;

- i tag di headings (h1, h2, ...) sono stati utilizzati in maniera corretta e semanticamente coerente, rispettando la gerarchia;

- tutte le immagini decorative possiedono un attributo *alt* vuoto (`alt=""`). Al contrario, le immagini informative sono corredate da un attributo *alt* con un contenuto adeguato alle informazioni che veicolano;

- sono state utilizzate opportune tecniche di image replacement per l’immagine presente nella sezione home contenente testo, garantendo così che, qualora l’immagine non venga renderizzata, il testo rimanga leggibile e presenti un adeguato contrasto rispetto al relativo sfondo;

- nel caso di una compilazione errata di un form i valori inseriti non vengono eliminati;

- per agevolare l’utente nella compilazione dei form in modo più semplice e veloce, sono stati utilizzati gli attributi `autocomplete`;

- in ogni pagina sono stati implementati gli aiuti alla navigazione (vai al contenuto) per aiutare e velocizzare la navigazione da tastiera;

- sono state fornite alternative testuali per tutti i contenuti visivi/grafici;

- l’animazione nella pagina home è stata progettata con una velocità conforme alle linee guida WCAG, garantendo un’esperienza visiva confortevole. L’utente può interromperla in qualsiasi momento tramite apposito pulsante; inoltre, se il browser è impostato su "prefers-reduced-motion: reduce", l’animazione non viene mostrata, pur rimanendo usabile attraverso i controlli manuali (bottoni freccette).

#### Aiuti per lo screen reader

Per agevolare gli utenti che utilizzano lo screen reader per navigare sul sito, sono stati adottati i seguenti accorgimenti:

- all’inizio di ogni pagina sono presenti aiuti alla navigazione (*Vai al contenuto*) che consentono all’utente di risparmiare tempo qualora non fosse interessato all’ascolto dell’intestazione di inizio pagina;

- quando necessario, vengono utilizzati gli attributi `aria` per migliorare l’accessibilità dei contenuti;

- le tabelle rese accessibili includono accorgimenti come `aria-describedby` e l’attributo `abbr` per facilitare la comprensione dei dati e velocizzarne l’esplorazione;

- se il contenuto della pagina supera la porzione visibile (*above the fold*), viene fornito un pulsante che permette di tornare rapidamente in cima alla pagina;

- quando presenti termini in lingue diverse da quella principale della pagina, vengono utilizzati gli attributi `lang` per garantire una corretta lettura da parte dello screen reader;

- in generale, si è cercato di utilizzare il più possibile tag HTML5 semantici, in modo da facilitare l’interpretazione dei contenuti da parte degli screen reader;

- sono state fornite alternative testuali per tutti i contenuti visivi/grafici che veicolano informazioni.

#### Compatibilità

Le tabelle del sito sono state rese completamente responsive, utilizzando, ad esempio, gli attributi `data-title`, in modo da facilitarne la lettura anche su dispositivi con schermi di dimensioni ridotte. Questo permette agli utenti di visualizzare le informazioni in maniera chiara senza dover scorrere orizzontalmente o perdere dettagli importanti. La navbar è stata progettata per adattarsi automaticamente: sui piccoli schermi si trasforma in un menù a “hamburger”, semplificando l’accesso alle voci principali e riducendo l’ingombro visivo.

In generale, tutte le pagine del sito implementano layout fluidi, cioè strutture che si adattano dinamicamente alle diverse dimensioni e risoluzioni degli schermi. Ciò garantisce che i contenuti siano sempre leggibili e facilmente usabili, sia su desktop, tablet o smartphone. Per ottenere questo comportamento, sono state create due diverse media query, ciascuna studiata per specifici intervalli di larghezza dello schermo, entrambe in grado di mantenere la fluidità dei layout e assicurare un’esperienza di navigazione uniforme su tutti i dispositivi.

## Test effettuati

### Accessibilità e validazione

Per verificare l’accessibilità, la correttezza e l’ottimizzazione del sito sono stati utilizzati sia strumenti automatici che test manuali.

Strumenti automatici utilizzati:

- Silktide;

- WAVE by WebAIM;

- Total Validator;

- W3C Validator (HTML e CSS);

- Lighthouse (per il calcolo delle prestazioni del sito).

L’impiego di strumenti automatici di analisi ha consentito di individuare rapidamente eventuali errori di sintassi, problemi di contrasto e la presenza di tag mancanti o non correttamente strutturati. I controlli effettuati hanno confermato che il sito rispetta i requisiti di conformità WCAG 2.1 livello AA in tutti i suoi aspetti. I validatori HTML e CSS non hanno segnalato errori significativi, ad eccezione di alcuni falsi positivi, che sono stati opportunamente analizzati e discussi nella sezione 5.2.

Test manuali effettuati:

- controllo della corretta struttura degli headings;

- verifica dell’uso appropriato dei tag semantici;

- controllo della presenza e correttezza degli attributi `alt` delle immagini;

- controllo della navigabilità da tastiera di tutte le pagine del sito;

- verifica del corretto funzionamento dei form e della gestione degli errori;

- verifica del corretto funzionamento delle funzionalità con JavaScript disabilitato;

- controllo del layout responsive su dispositivi mobili e tablet;

- verifica della leggibilità e del contrasto dei colori utilizzati;

- test di stampa delle pagine per verificarne la formattazione e la leggibilità su carta;

- test di accessibilità con screen reader (NVDA).

- compatibilità del sito con diversi browser: Microsoft Edge, Google Chrome, Mozilla Firefox, Apple Safari e Opera;

- compatibilità con diversi sistemi operativi: Microsoft Windows 10, Ubuntu 23.10, Android 9, Android 15 e iOS 18.3.

Questa combinazione di test automatici e manuali ha garantito che il sito sia accessibile, leggibile e navigabile correttamente da tutti gli utenti, comprese le persone che utilizzano screen reader o altre tecnologie assistive.

### Analisi dei Falsi Positivi

L’analisi dei falsi positivi è stata effettuata dopo la correzione di tutte le problematiche effettivamente riscontrate; di seguito sono riportate le segnalazioni risultate non critiche:

- **W3C Validator - HTML**: segnalazione di tag non chiusi in alcune pagine. Dopo un’attenta verifica del codice sorgente, si è constatato che si trattava di un falso positivo dovuto a particolari costrutti PHP che generano codice HTML dinamicamente. In tutti i casi, il codice generato è risultato valido e conforme agli standard HTML5.

- **W3C Validator - CSS**: segnalazione di proprietà CSS non riconosciute. Queste segnalazioni sono state analizzate e si è riscontrato che si trattava di proprietà CSS3 ancora non pienamente supportate da tutti i validatori, ma ampiamente accettate nei browser moderni.

- **Lighthouse**: segnalazione di immagini non ottimizzate. Dopo aver esaminato le immagini in questione, si è constatato che erano già state ottimizzate per il web, ma il tool non riconosceva alcune tecniche di compressione avanzate utilizzate.

- **Silktide e WAVE by WebAIM**: segnalazioni riguardanti il contrasto dei colori in alcune sezioni. Dopo un’analisi approfondita, si è verificato che i contrasti rispettavano comunque le linee guida WCAG 2.1 livello AA, e le segnalazioni erano dovute a particolari combinazioni di colori che, pur essendo accessibili, risultavano borderline secondo gli algoritmi dei tool.

Silktide e WAVE by WebAIM hanno segnalato presunti problemi di contrasto cromatico che, a seguito di verifica, sono stati classificati come falsi positivi. In particolare, nella homepage è stato segnalato un falso positivo relativo al contrasto tra il testo e l’immagine hero, mentre nella sezione dedicata ai vini la segnalazione riguarda il contrasto delle informazioni relative alle quantità dei prodotti. Un ulteriore falso positivo è stato rilevato da Silktide nella pagina di amministrazione, all’interno della sezione di gestione utenti, dove il contrasto segnalato risulta comunque conforme ai requisiti WCAG 2.1 livello AA. Tali segnalazioni sono riconducibili alle limitazioni degli strumenti automatici nell’analisi di elementi visivi complessi o dinamici.

### Screen reader

L’accessibilità del sito è stata testata utilizzando lo screen reader NVDA, verificando la corretta lettura e interpretazione di tutti i contenuti e delle principali componenti interattive. I test hanno incluso il controllo dell’ordine di navigazione da tastiera, della corretta associazione tra etichette e campi di input, dell’interpretazione dei ruoli e degli attributi ARIA, nonché della lettura strutturata di tabelle, form e altri elementi complessi. Le verifiche non hanno evidenziato criticità rilevanti.

## Organizzazione del gruppo

Il lavoro è stato organizzato suddividendo le attività in base alle diverse pagine e funzionalità del sito da sviluppare. A ciascun membro del gruppo sono stati assegnati compiti specifici, così da ottimizzare i tempi di sviluppo e valorizzare al meglio le competenze individuali.

### Divisione dei compiti

- **Michele Stevanin**:

  - HTML/CSS: pagine Home, Area Personale, Carrello-Checkout, Policy

  - PHP/JavaScript: funzionalità inerenti alle pagine sviluppate

  - DB: progettazione e implementazione

  - Testing e validazione: Total Validator, Lighthouse, Silktide

  - Relazione tecnica

- **Alessandro Contarini**:

  - HTML/CSS: pagine Esperienze, Contatti, Gestionale, Policy

  - PHP/JavaScript: funzionalità inerenti alle pagine sviluppate

  - DB: progettazione e implementazione

  - Testing e validazione: Total Validator, Lighthouse, Silktide

  - Relazione tecnica

- **Luca Marcuzzo**:

  - HTML/CSS: pagine Tenuta, Admin, Mappa, Login

  - PHP/JavaScript: funzionalità inerenti alle pagine sviluppate

  - DB: popolamento e backup

  - Testing e validazione: W3C Validator, Silktide, WAVE by WebAIM, NVDA

  - Relazione tecnica

- **Giovanni Visentin**:

  - HTML/CSS: pagine Vini, Admin, Registrazione

  - PHP/JavaScript: funzionalità inerenti alle pagine sviluppate

  - DB: ottimizzazione query

  - Testing e validazione: W3C Validator, Silktide, WAVE by WebAIM, NVDA

  - Relazione tecnica

## Note

1.  Il sito è stato sviluppato con particolare attenzione alle linee guida per l’accessibilità WCAG 2.1 livello AA.

2.  Sono state adottate misure di sicurezza di base per la protezione delle sessioni utente, in particolare la prevenzione di accessi non autorizzati alle aree riservate del sito.

3.  Il database viene fornito già popolato con dati dimostrativi per facilitare il testing delle funzionalità.
