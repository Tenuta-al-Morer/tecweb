document.addEventListener('DOMContentLoaded', () => {

    /* ==========================================
     * 1. DARK MODE (Con Rilevamento Automatico di Sistema)
     * ========================================== */
    const themeToggleBtn = document.getElementById('theme-toggle');
    
    // Funzione che applica il tema e cambia l'icona
    const setDarkMode = (isDark) => {
        if (isDark) {
            document.body.classList.add('dark-mode');
            localStorage.setItem('theme', 'dark'); // Memorizza la scelta
            
            // Se c'è il pulsante, cambia l'icona in SOLE (per tornare al giorno)
            if (themeToggleBtn) {
                themeToggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
            }
        } else {
            document.body.classList.remove('dark-mode');
            localStorage.setItem('theme', 'light'); // Memorizza la scelta
            
            // Se c'è il pulsante, cambia l'icona in LUNA (per andare alla notte)
            if (themeToggleBtn) {
                themeToggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
            }
        }
    };

    // A. Controllo Click sul pulsante
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            const isDarkNow = document.body.classList.contains('dark-mode');
            setDarkMode(!isDarkNow); // Inverti lo stato attuale
        });
    }

    // B. LOGICA INTELLIGENTE ALL'AVVIO
    const savedTheme = localStorage.getItem('theme'); // 1. Cerca preferenza salvata
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches; // 2. Cerca preferenza di sistema

    if (savedTheme === 'dark') {
        // Se l'utente aveva scelto DARK in passato, usalo
        setDarkMode(true);
    } else if (savedTheme === 'light') {
        // Se l'utente aveva scelto LIGHT in passato, usalo
        setDarkMode(false);
    } else {
        // Se è la PRIMA VISITA (nessun salvataggio), usa la preferenza del sistema
        if (systemPrefersDark) {
            setDarkMode(true);
        } else {
            setDarkMode(false);
        }
    }


    /* ==========================================
     * 2. MENU DI NAVIGAZIONE (Solo se presente)
     * ========================================== */
    const menuButton = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('#main-navigation');

    // Eseguiamo questo blocco solo se il menu esiste nella pagina
    if (menuButton && navMenu) {
        
        const apriMenu = () => {
            navMenu.classList.add('is-open');
            menuButton.setAttribute('aria-expanded', 'true');
        };

        const chiudiMenu = () => {
            navMenu.classList.remove('is-open');
            menuButton.setAttribute('aria-expanded', 'false');
        };

        document.addEventListener('click', (e) => {
            const clickTarget = e.target;
            const isMenuOpen = navMenu.classList.contains('is-open');

            // Click sul pulsante hamburger
            if (clickTarget.closest('.menu-toggle')) {
                e.preventDefault(); 
                if (isMenuOpen) {
                    chiudiMenu();
                } else {
                    apriMenu();
                }
                return; 
            }

            // Click fuori dal menu per chiudere
            if (isMenuOpen && !clickTarget.closest('#main-navigation')) {
                chiudiMenu();
            }
        });
    }


    /* ==========================================
     * 3. PULSANTE "TORNA INDIETRO" (Specifico Policy)
     * ========================================== */
    const backBtn = document.getElementById('back-link');
    
    if (backBtn) {
        const params = new URLSearchParams(window.location.search);
        const returnUrl = params.get('return_to');

        if (returnUrl) {
            backBtn.href = decodeURIComponent(returnUrl);
        } else {
            backBtn.href = 'index.html';
        }
    }


    /* ==========================================
     * 4. PULSANTE TORNA SU (Universale Adattivo)
     * ========================================== */
    const backToTopBtn = document.getElementById('backToTopBtn');
    const mainNavBar = document.querySelector('.main-nav-bar');

    if (backToTopBtn) {
        
        const toggleBackToTopButton = () => {
            // Logica intelligente: 
            // Se c'è la navbar (Home), usa la sua altezza come punto di attivazione.
            // Se non c'è (Policy), usa 200px fissi.
            const threshold = mainNavBar ? mainNavBar.offsetHeight : 200;

            if (window.scrollY > threshold) {
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
        
        toggleBackToTopButton();
    }

});