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

    if (savedTheme === 'light') {
        setLightMode(true);
    } else if (savedTheme === 'dark') {
        setLightMode(false);
    } 
    else if (systemPrefersLight) {
         setLightMode(false); 
    } else {
        setLightMode(false); 
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

        // --- GESTIONE CLICK (Pulsante e Click Fuori) ---
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

            // Click fuori dal menu
            if (isMenuOpen && !clickTarget.closest('#main-navigation')) {
                chiudiMenu();
            }
        });

        // --- NUOVO: CHIUSURA ALLO SCROLL ---
        window.addEventListener('scroll', () => {
            // Controlla se il menu è aperto. Se sì, chiudilo.
            if (navMenu.classList.contains('is-open')) {
                chiudiMenu();
            }
        }, { passive: true }); // "passive: true" migliora le performance dello scroll
    }

    /* ==========================================
     * 3. UTILITIES (Back button, Scroll top, Links)
     * ========================================== */
    const backBtn = document.getElementById('back-link');
    
    if (backBtn) {
        const params = new URLSearchParams(window.location.search);
        
        // 1. GESTIONE CHIUSURA SCHEDA
        const action = params.get('action');

        if (action === 'close') {
            backBtn.innerHTML = '<i class="fas fa-times"></i> Chiudi e torna alla registrazione';
            backBtn.addEventListener('click', (e) => {
                e.preventDefault();
                window.close();
            });

        } else {
            // 2. GESTIONE NAVIGAZIONE NORMALE
            const returnUrl = params.get('return_to');

            if (returnUrl) {
                backBtn.href = returnUrl;
            } else {
                // 3. Fallback intelligente
                const isInSubfolder = window.location.pathname.includes('/html/') || window.location.pathname.split('/').length > 2;
                backBtn.href = isInSubfolder ? '../index.html' : 'index.html';

                backBtn.addEventListener('click', (e) => {
                    const referrer = document.referrer;
                    const currentDomain = window.location.hostname; 

                    if (referrer && referrer.includes(currentDomain)) {
                        e.preventDefault();
                        window.history.back();
                    }
                });
            }
        }
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


    /* ==========================================
     * 4. GESTIONE PASSWORD (Mostra/Nascondi)
     * ========================================== */
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');

    if (togglePasswordButtons.length > 0) {
        togglePasswordButtons.forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                
                if (input.type === "password") {
                    input.type = "text";
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                    this.setAttribute('aria-label', 'Nascondi password');
                } else {
                    input.type = "password";
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                    this.setAttribute('aria-label', 'Mostra password');
                }
            });
        });
    }

    /* ==========================================
     * 5. GESTIONE SLIDER / CAROSELLO INFINITO
     * ========================================== */
    // Cerchiamo l'elemento slider
    const slider = document.getElementById('imageSlider');

    // Eseguiamo questo codice SOLO se lo slider esiste nella pagina
    if (slider) {
        const nextBtn = document.querySelector('.next-btn');
        const prevBtn = document.querySelector('.prev-btn');
        
        let currentIndex = 0;
        const totalSlides = 5; // Numero di foto REALI (escluso il clone)
        let isTransitioning = false;
        let autoScroll; // Variabile per l'intervallo

        // Funzione per scorrere avanti
        const nextSlide = () => {
            if (isTransitioning) return;
            isTransitioning = true;
            currentIndex++;
            updateSlider('smooth');

            // Se siamo arrivati al clone (dopo l'ultima foto)
            if (currentIndex === totalSlides) {
                setTimeout(() => {
                    // Disattiviamo l'animazione e saltiamo istantaneamente alla prima foto vera
                    isTransitioning = false;
                    currentIndex = 0;
                    updateSlider('auto'); // 'auto' = salto istantaneo
                }, 600); // Aspettiamo fine animazione CSS
            } else {
                setTimeout(() => { isTransitioning = false; }, 600);
            }
        };

        // Funzione per scorrere indietro
        const prevSlide = () => {
            if (isTransitioning) return;
            isTransitioning = true;

            if (currentIndex === 0) {
                // Se siamo alla prima, saltiamo al clone in fondo
                currentIndex = totalSlides;
                updateSlider('auto');
                // E poi scorriamo indietro alla penultima
                setTimeout(() => {
                    currentIndex--;
                    updateSlider('smooth');
                    setTimeout(() => { isTransitioning = false; }, 600);
                }, 50); 
            } else {
                currentIndex--;
                updateSlider('smooth');
                setTimeout(() => { isTransitioning = false; }, 600);
            }
        };

        // Funzione helper per lo scroll
        const updateSlider = (behavior) => {
            const width = slider.clientWidth;
            slider.scrollTo({
                left: width * currentIndex,
                behavior: behavior
            });
        };

        // --- FUNZIONI START / STOP AUTOSCROLL ---
        const startAutoScroll = () => {
            // Pulisce eventuali intervalli esistenti per sicurezza
            clearInterval(autoScroll);
            autoScroll = setInterval(nextSlide, 3500);
        };

        const stopAutoScroll = () => {
            clearInterval(autoScroll);
        };

        // Event Listeners Frecce
        if(nextBtn) nextBtn.addEventListener('click', nextSlide);
        if(prevBtn) prevBtn.addEventListener('click', prevSlide);

        // --- PAUSA SU HOVER ---
        
        // 1. Avvio iniziale
        startAutoScroll();

        // 2. Quando il mouse entra o tocchi -> STOP
        slider.addEventListener('mouseenter', stopAutoScroll); 
        slider.addEventListener('touchstart', stopAutoScroll, { passive: true });

        // 3. Quando il mouse esce o alzi il dito -> RIPARTE
        slider.addEventListener('mouseleave', startAutoScroll);
        slider.addEventListener('touchend', startAutoScroll);

        // Gestione ridimensionamento finestra
        window.addEventListener('resize', () => updateSlider('auto'));
    }

});