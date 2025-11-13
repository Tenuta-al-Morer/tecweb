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