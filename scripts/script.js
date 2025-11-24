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
                //immagine del sole SVG
                themeToggleBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                `;
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

    
    const navLinks = document.querySelectorAll('.primary-navigation a[href]');

    navLinks.forEach(link => {
        const href = link.getAttribute('href');

        // Se in passato è già stato visitato, rimetto la classe
        if (localStorage.getItem('visited_' + href)) {
            link.classList.add('is-visited');
        }

        // Quando ci clicco, lo segno come visitato
        link.addEventListener('click', () => {
            localStorage.setItem('visited_' + href, 'true');
            link.classList.add('is-visited');
        });
    });

    const iconLinks = document.querySelectorAll('.mobile-icons a[href]');

    iconLinks.forEach(link => {
        const href = link.getAttribute('href');

        // Se in passato è già stato visitato, rimetto la classe
        if (localStorage.getItem('visited_' + href)) {
            link.classList.add('is-visited');
        }

        // Quando ci clicco, lo segno come visitato
        link.addEventListener('click', () => {
            localStorage.setItem('visited_' + href, 'true');
            link.classList.add('is-visited');
        });
    });



});
