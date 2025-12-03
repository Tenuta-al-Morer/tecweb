document.addEventListener('DOMContentLoaded', () => {

    /* ==========================================
     * 1. THEME MANAGEMENT (Default Dark)
     * ========================================== */
    const themeToggleBtn = document.getElementById('theme-toggle');
    
    // Icona SOLE (per passare alla Light Mode)
    const sunIcon = `
        <span class="visually-hidden">Passa alla modalità chiara</span>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
            viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true">
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

    const moonIcon = `
        <span class="visually-hidden">Passa alla modalità scura</span>
        <i class="fas fa-moon" aria-hidden="true"></i>
    `;


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
            const href = new URL(link.href).pathname;

            if (localStorage.getItem('visited_' + href)) {
                link.classList.add('is-visited');
            }

            link.addEventListener('click', () => {
                localStorage.setItem('visited_' + href, 'true');
            });
        });
    };

// attivazione
trackVisits(".navbar a");


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
                const srText = this.querySelector('.visually-hidden'); // testo per screen reader

                const isHidden = input.type === "password";

                // toggle tipo input
                input.type = isHidden ? "text" : "password";

                // toggle icona
                icon.classList.toggle('fa-eye', !isHidden);
                icon.classList.toggle('fa-eye-slash', isHidden);

                // aggiorna stato ARIA
                this.setAttribute('aria-pressed', isHidden ? 'true' : 'false');

                // aggiorna testo per screen reader
                if (srText) {
                    srText.textContent = isHidden ? 'Nascondi password' : 'Mostra password';
                }
            });
        });
    }


    /* ==========================================
     * 5. GESTIONE SLIDER / CAROSELLO INFINITO (CORRETTO PER MULTI-SLIDE)
     * ========================================== */
    const sliderContainer = document.getElementById('imageSlider');
    const slides = document.querySelectorAll('.slide');
    const nextBtn = document.querySelector('.next-btn');
    const prevBtn = document.querySelector('.prev-btn');

    if (sliderContainer && slides.length > 0) {
        
        // Numero di cloni di sicurezza (uguale al massimo numero di foto visibili nel CSS)
        const clonesCount = 4; 
        
        // Start index deve compensare i cloni iniziali
        let currentIndex = clonesCount; 
        let isTransitioning = false;
        let slideInterval;
        const intervalTime = 5000; 

        // 1. CLONAZIONE AVANZATA (4 copie a destra e 4 a sinistra)
        
        // Clona gli ultimi 4 per metterli all'inizio (Prepend)
        for (let i = 0; i < clonesCount; i++) {
            // Prendiamo le slide partendo dalla fine
            const slideToClone = slides[slides.length - 1 - i]; 
            const clone = slideToClone.cloneNode(true);
            clone.classList.add('clone-slide'); // Classe utile per debug
            sliderContainer.prepend(clone);
        }

        // Clona i primi 4 per metterli alla fine (Append)
        for (let i = 0; i < clonesCount; i++) {
            const slideToClone = slides[i];
            const clone = slideToClone.cloneNode(true);
            clone.classList.add('clone-slide');
            sliderContainer.append(clone);
        }

        // Riselezioniamo tutte le slide (Originali + Cloni)
        const allSlides = document.querySelectorAll('.slide');

        // 2. POSIZIONAMENTO INIZIALE
        // Spostiamo il contenitore per mostrare la prima immagine REALE
        const updateInitialPosition = () => {
             const slideWidth = allSlides[0].offsetWidth; // Usa offsetWidth per precisione
             sliderContainer.style.transition = 'none'; // Nessuna animazione all'avvio
             sliderContainer.style.transform = `translateX(${-slideWidth * currentIndex}px)`;
        };
        
        // Chiamiamo subito la funzione
        updateInitialPosition();

        // 3. FUNZIONE DI SCORRIMENTO
        const moveSlide = () => {
            const slideWidth = allSlides[0].offsetWidth; 
            sliderContainer.style.transition = 'transform 0.5s ease-in-out';
            sliderContainer.style.transform = `translateX(${-slideWidth * currentIndex}px)`;
        };

        const nextSlide = () => {
            if (isTransitioning) return;
            // Se siamo oltre i cloni finali, fermati (il transitionend gestirà il reset)
            if (currentIndex >= allSlides.length - 1) return;

            isTransitioning = true;
            currentIndex++;
            moveSlide();
        };

        const prevSlide = () => {
            if (isTransitioning) return;
            if (currentIndex <= 0) return;

            isTransitioning = true;
            currentIndex--;
            moveSlide();
        };

        // 4. GESTIONE DEL "SALTO" INFINITO (Transition End)
        sliderContainer.addEventListener('transitionend', () => {
            isTransitioning = false;
            const slideWidth = allSlides[0].offsetWidth;

            // CASO A: Siamo andati troppo AVANTI (siamo sui cloni finali)
            // Logica: Se l'indice è arrivato alla fine delle slide originali + cloni
            if (currentIndex >= slides.length + clonesCount) {
                sliderContainer.style.transition = 'none'; // Togli animazione
                // Calcoliamo la nuova posizione: togliamo la lunghezza delle slide originali
                currentIndex = currentIndex - slides.length; 
                sliderContainer.style.transform = `translateX(${-slideWidth * currentIndex}px)`;
            }

            // CASO B: Siamo andati troppo INDIETRO (siamo sui cloni iniziali)
            if (currentIndex < clonesCount) {
                sliderContainer.style.transition = 'none'; // Togli animazione
                // Calcoliamo la nuova posizione: aggiungiamo la lunghezza delle slide originali
                currentIndex = currentIndex + slides.length;
                sliderContainer.style.transform = `translateX(${-slideWidth * currentIndex}px)`;
            }
        });

        // 5. EVENTI BOTTONI
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                nextSlide();
                resetTimer();
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                prevSlide();
                resetTimer();
            });
        }

        // 6. GESTIONE AUTOPLAY
        const startTimer = () => {
            slideInterval = setInterval(nextSlide, intervalTime);
        };

        const stopTimer = () => {
            clearInterval(slideInterval);
        };

        const resetTimer = () => {
            stopTimer();
            startTimer();
        };

        // Pausa hover
        sliderContainer.addEventListener('mouseenter', stopTimer);
        sliderContainer.addEventListener('mouseleave', startTimer);

        // 7. GESTIONE RESIZE
        // Se si ridimensiona la finestra, bisogna ricalcolare la posizione precisa
        window.addEventListener('resize', () => {
            const slideWidth = allSlides[0].offsetWidth;
            sliderContainer.style.transition = 'none';
            sliderContainer.style.transform = `translateX(${-slideWidth * currentIndex}px)`;
        });

        startTimer();
    }

});