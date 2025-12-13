document.addEventListener('DOMContentLoaded', () => {

    /* ==========================================
     * 1. THEME MANAGEMENT (Default Dark)
     * ========================================== */
    const themeToggleBtn = document.getElementById('theme-toggle');
    
    
    const sunIcon = `
        <span class="visually-hidden">Passa alla modalità chiara</span>
        <svg xmlns="http:
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


    
    const setLightMode = (enableLight) => {
        if (enableLight) {
            
            document.body.classList.add('light-mode');
            localStorage.setItem('theme', 'light');
            
            if (themeToggleBtn) {
                themeToggleBtn.innerHTML = moonIcon; 
            }
        } else {
            
            document.body.classList.remove('light-mode');
            localStorage.setItem('theme', 'dark');
            
            if (themeToggleBtn) {
                themeToggleBtn.innerHTML = sunIcon; 
            }
        }
    };

    
    if (themeToggleBtn) {
        
        const currentIsLight = document.body.classList.contains('light-mode');
        themeToggleBtn.innerHTML = currentIsLight ? moonIcon : sunIcon;

        themeToggleBtn.addEventListener('click', () => {
            const isLightNow = document.body.classList.contains('light-mode');
            setLightMode(!isLightNow); 
        });
    }

    
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

        
        window.addEventListener('scroll', () => {
            
            if (navMenu.classList.contains('is-open')) {
                chiudiMenu();
            }
        }, { passive: true }); 
    }

    /* ==========================================
     * 3. UTILITIES (Back button, Scroll top, Links)
     * ========================================== */
    const backBtn = document.getElementById('back-link');
    
    if (backBtn) {
        const params = new URLSearchParams(window.location.search);
        
        
        const action = params.get('action');

        if (action === 'close') {
            backBtn.innerHTML = '<i class="fas fa-times"></i> Chiudi e torna alla registrazione';
            backBtn.addEventListener('click', (e) => {
                e.preventDefault();
                window.close();
            });

        } else {
            
            const returnUrl = params.get('return_to');

            if (returnUrl) {
                backBtn.href = returnUrl;
            } else {
                
                const isInSubfolder = window.location.pathname.includes('/html/') || window.location.pathname.split('/').length > 2;
                backBtn.href = isInSubfolder ? 'index.php' : 'index.php';

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
            
            if (!link.href) return;

            const href = new URL(link.href).pathname;

            if (localStorage.getItem('visited_' + href)) {
                link.classList.add('is-visited');
            }

            link.addEventListener('click', () => {
                localStorage.setItem('visited_' + href, 'true');
            });
        });
    };

    
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
                const srText = this.querySelector('.visually-hidden'); 

                const isHidden = input.type === "password";

                
                input.type = isHidden ? "text" : "password";

                
                icon.classList.toggle('fa-eye', !isHidden);
                icon.classList.toggle('fa-eye-slash', isHidden);

                
                this.setAttribute('aria-pressed', isHidden ? 'true' : 'false');

                
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
        
        
        const clonesCount = 4; 
        
        
        let currentIndex = clonesCount; 
        let isTransitioning = false;
        let slideInterval;
        const intervalTime = 5000; 

        
        
        
        for (let i = 0; i < clonesCount; i++) {
            
            const slideToClone = slides[slides.length - 1 - i]; 
            const clone = slideToClone.cloneNode(true);
            clone.classList.add('clone-slide'); 
            sliderContainer.prepend(clone);
        }

        
        for (let i = 0; i < clonesCount; i++) {
            const slideToClone = slides[i];
            const clone = slideToClone.cloneNode(true);
            clone.classList.add('clone-slide');
            sliderContainer.append(clone);
        }

        
        const allSlides = document.querySelectorAll('.slide');

        
        
        const updateInitialPosition = () => {
             const slideWidth = allSlides[0].offsetWidth; 
             sliderContainer.style.transition = 'none'; 
             sliderContainer.style.transform = `translateX(${-slideWidth * currentIndex}px)`;
        };
        
        
        updateInitialPosition();

        
        const moveSlide = () => {
            const slideWidth = allSlides[0].offsetWidth; 
            sliderContainer.style.transition = 'transform 0.5s ease-in-out';
            sliderContainer.style.transform = `translateX(${-slideWidth * currentIndex}px)`;
        };

        const nextSlide = () => {
            if (isTransitioning) return;
            
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

        
        sliderContainer.addEventListener('transitionend', () => {
            isTransitioning = false;
            const slideWidth = allSlides[0].offsetWidth;

            
            
            if (currentIndex >= slides.length + clonesCount) {
                sliderContainer.style.transition = 'none'; 
                
                currentIndex = currentIndex - slides.length; 
                sliderContainer.style.transform = `translateX(${-slideWidth * currentIndex}px)`;
            }

            
            if (currentIndex < clonesCount) {
                sliderContainer.style.transition = 'none'; 
                
                currentIndex = currentIndex + slides.length;
                sliderContainer.style.transform = `translateX(${-slideWidth * currentIndex}px)`;
            }
        });

        
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

        
        sliderContainer.addEventListener('mouseenter', stopTimer);
        sliderContainer.addEventListener('mouseleave', startTimer);

        
        
        window.addEventListener('resize', () => {
            const slideWidth = allSlides[0].offsetWidth;
            sliderContainer.style.transition = 'none';
            sliderContainer.style.transform = `translateX(${-slideWidth * currentIndex}px)`;
        });

        startTimer();
    }

    /* ==========================================
     * 6. ADMIN TABS (mostra una tabella alla volta)
     * ========================================== */
    const map = {
        "#tab-vini": "#section-vini",
        "#tab-degustazioni": "#section-esperienze",
        "#tab-info": "#section-messaggi",
    };

    const tabs = document.querySelectorAll(".admin-tabs .admin-tab");

    // Se non siamo in admin, non fare nulla (così non rompe le altre pagine)
    if (tabs.length > 0) {
        const sections = Object.values(map).map(sel => document.querySelector(sel));

        // stato: null = vista collettiva, altrimenti una delle chiavi "#tab-..."
        let active = null;

        function showAll() {
            sections.forEach(s => s && s.classList.remove("is-hidden"));
            tabs.forEach(t => t.classList.remove("is-active"));
            active = null;
            history.replaceState(null, "", window.location.pathname + window.location.search);
        }

        function showOnly(hash) {
            const sectionSel = map[hash];
            sections.forEach(s => s && s.classList.add("is-hidden"));

            const target = document.querySelector(sectionSel);
            if (target) target.classList.remove("is-hidden");

            tabs.forEach(t => {
                const isThis = t.getAttribute("href") === hash;
                t.classList.toggle("is-active", isThis);
            });

            active = hash;
            history.replaceState(null, "", hash);

            if (target) target.scrollIntoView({ behavior: "smooth", block: "start" });
        }

        tabs.forEach(tab => {
            tab.addEventListener("click", (e) => {
                e.preventDefault();
                const hash = tab.getAttribute("href");

                // se riclicco la tab già attiva -> torna alla vista collettiva
                if (active === hash) {
                    showAll();
                } else {
                    showOnly(hash);
                }
            });
        });

        // Se apro la pagina con un hash valido, parto filtrato
        if (map[window.location.hash]) {
            showOnly(window.location.hash);
        } else {
            showAll();
        }
    }

});
