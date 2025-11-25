document.addEventListener('DOMContentLoaded', () => {

    /* ==========================================
     * 1. THEME MANAGEMENT (Default Dark)
     * ========================================== */
    const themeToggleBtn = document.getElementById('theme-toggle');
    
    // Icona SOLE (per passare alla Light Mode)
    const sunIcon = `
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

    // Icona LUNA (per passare alla Dark Mode)
    const moonIcon = '<i class="fas fa-moon"></i>';

    // Funzione che applica il tema LIGHT (se false, rimane Dark che è default)
    const setLightMode = (enableLight) => {
        if (enableLight) {
            // ATTIVA LIGHT MODE
            document.body.classList.add('light-mode');
            localStorage.setItem('theme', 'light');
            
            if (themeToggleBtn) {
                themeToggleBtn.innerHTML = moonIcon; // Mostra luna per tornare al buio
            }
        } else {
            // ATTIVA DARK MODE (Default - Rimuove classe)
            document.body.classList.remove('light-mode');
            localStorage.setItem('theme', 'dark');
            
            if (themeToggleBtn) {
                themeToggleBtn.innerHTML = sunIcon; // Mostra sole per andare alla luce
            }
        }
    };

    // A. Controllo Click sul pulsante
    if (themeToggleBtn) {
        // Imposta icona iniziale corretta
        const currentIsLight = document.body.classList.contains('light-mode');
        themeToggleBtn.innerHTML = currentIsLight ? moonIcon : sunIcon;

        themeToggleBtn.addEventListener('click', () => {
            const isLightNow = document.body.classList.contains('light-mode');
            setLightMode(!isLightNow); // Inverti
        });
    }

    // B. LOGICA ALL'AVVIO
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersLight = window.matchMedia('(prefers-color-scheme: light)').matches;

    // Se l'utente ha salvato "light", attiviamo light mode.
    // Altrimenti (se è "dark" o nullo), lasciamo il default CSS (che ora è Dark).
    if (savedTheme === 'light') {
        setLightMode(true);
    } else if (savedTheme === 'dark') {
        setLightMode(false);
    } 
    // Opzionale: Se vuoi rispettare la preferenza di sistema SOLO se l'utente preferisce chiaro
    // altrimenti ignora e usa Dark.
    else if (systemPrefersLight) {
         // setLightMode(true); // De-commenta se vuoi che chi ha il sistema chiaro veda chiaro
         setLightMode(false); // Commenta questa e usa quella sopra se vuoi il comportamento di sistema
    } else {
        setLightMode(false); // Default assoluto
    }


    /* ==========================================
     * 2. MENU DI NAVIGAZIONE
     * ========================================== */
    const menuButton = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('#main-navigation');

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

            if (clickTarget.closest('.menu-toggle')) {
                e.preventDefault(); 
                if (isMenuOpen) {
                    chiudiMenu();
                } else {
                    apriMenu();
                }
                return; 
            }

            if (isMenuOpen && !clickTarget.closest('#main-navigation')) {
                chiudiMenu();
            }
        });
    }


    /* ==========================================
     * 3. UTILITIES (Back button, Scroll top, Links)
     * ========================================== */
    const backBtn = document.getElementById('back-link');
    if (backBtn) {
        const params = new URLSearchParams(window.location.search);
        const returnUrl = params.get('return_to');
        if (returnUrl) backBtn.href = decodeURIComponent(returnUrl);
        else backBtn.href = 'index.html';
    }

    const backToTopBtn = document.getElementById('backToTopBtn');
    const mainNavBar = document.querySelector('.main-nav-bar');

    if (backToTopBtn) {
        const toggleBackToTopButton = () => {
            const threshold = mainNavBar ? mainNavBar.offsetHeight : 200;
            if (window.scrollY > threshold) backToTopBtn.classList.add('show');
            else backToTopBtn.classList.remove('show');
        };

        const smoothScrollToTop = () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        window.addEventListener('scroll', toggleBackToTopButton);
        backToTopBtn.addEventListener('click', smoothScrollToTop);
        toggleBackToTopButton();
    }
    
    const trackVisits = (selector) => {
        document.querySelectorAll(selector).forEach(link => {
            const href = link.getAttribute('href');
            if (localStorage.getItem('visited_' + href)) {
                link.classList.add('is-visited');
            }
            link.addEventListener('click', () => {
                localStorage.setItem('visited_' + href, 'true');
                link.classList.add('is-visited');
            });
        });
    }

    trackVisits('.primary-navigation a[href]');
    trackVisits('.mobile-icons a[href]');
});