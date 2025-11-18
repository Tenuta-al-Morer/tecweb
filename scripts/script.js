// Attendi che tutto l'HTML sia stato caricato prima di eseguire lo script
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Trova il bottone
    const menuToggle = document.querySelector('.menu-toggle');
    
    // 2. Trova il menu (usando l'ID che hai appena aggiunto)
    const mainMenu = document.querySelector('#main-navigation');

    // Assicurati che entrambi esistano prima di continuare
    if (menuToggle && mainMenu) {
        
        // 3. Aggiungi un "listener" per il click sul bottone
        menuToggle.addEventListener('click', function() {
            
            // 4. Aggiungi/rimuovi la classe '.is-open' dal menu
            mainMenu.classList.toggle('is-open');

            // 5. Aggiorna l'attributo 'aria-expanded' per l'accessibilità
            // Controlla se il menu è ora aperto (ha la classe 'is-open')
            const isExpanded = mainMenu.classList.contains('is-open');
            menuToggle.setAttribute('aria-expanded', isExpanded);
        });
    }

});

// Ottieni riferimenti agli elementi
const backToTopBtn = document.getElementById('backToTopBtn');
const mainNavBar = document.querySelector('.main-nav-bar'); // Il container della tua navbar

// Funzione per mostrare/nascondere il pulsante
function toggleBackToTopButton() {
    // La posizione Y della fine della navbar rispetto alla parte superiore della viewport
    const navBottomPosition = mainNavBar.offsetHeight; 

    // Se lo scroll verticale è maggiore dell'altezza della navbar, mostra il pulsante
    if (window.scrollY > navBottomPosition) {
        backToTopBtn.classList.add('show');
    } else {
        backToTopBtn.classList.remove('show');
    }
}

// Funzione per lo scorrimento fluido
function smoothScrollToTop() {
    window.scrollTo({
        top: 0, // Vai all'inizio della pagina
        behavior: 'smooth' // Questo è l'effetto di scorrimento fluido
    });
}

// Aggiungi un listener per l'evento scroll
window.addEventListener('scroll', toggleBackToTopButton);

// Aggiungi un listener per l'evento click
backToTopBtn.addEventListener('click', smoothScrollToTop);

// Assicurati che la funzione di controllo venga eseguita all'avvio della pagina
toggleBackToTopButton();