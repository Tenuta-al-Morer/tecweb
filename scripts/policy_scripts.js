document.addEventListener('DOMContentLoaded', () => {

    /* ==========================================
     * 1. GESTIONE PULSANTE "PAGINA PRECEDENTE"
     * ==========================================
     */
    const backBtn = document.getElementById('back-link');
    
    if (backBtn) {
        // Cerca il parametro 'return_to' nell'URL
        const params = new URLSearchParams(window.location.search);
        const returnUrl = params.get('return_to');

        if (returnUrl) {
            // Se c'è il parametro, decodificalo e usalo
            backBtn.href = decodeURIComponent(returnUrl);
        } else {
            // Altrimenti torna alla Home
            backBtn.href = 'index.html';
        }
    }

    /* ==========================================
     * 2. PULSANTE TORNA SU (Back to Top) - Versione Policy
     * ==========================================
     */
    const backToTopBtn = document.getElementById('backToTopBtn');

    // Nota: Qui NON cerchiamo .main-nav-bar perché in questa pagina non c'è.
    
    if (backToTopBtn) {
        
        const toggleBackToTopButton = () => {
            // Invece di misurare la navbar, usiamo un valore fisso (es. 200px)
            if (window.scrollY > 200) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        };

        const smoothScrollToTop = () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        };

        window.addEventListener('scroll', toggleBackToTopButton);
        backToTopBtn.addEventListener('click', smoothScrollToTop);
        
        // Controllo iniziale nel caso la pagina venga ricaricata già scrollata
        toggleBackToTopButton();
    }
});