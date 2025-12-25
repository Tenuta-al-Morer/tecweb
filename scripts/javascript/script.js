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
     * 3. UTILITIES (Scroll top, Links)
     * ========================================== */

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

    /* ==========================================
     * 7. USER DASHBOARD (Area Riservata)
     * ========================================== */
    
    // A. Gestione Navigazione Sidebar (Schede)
    const userNavLinks = document.querySelectorAll('.user-nav-link');
    const userSections = document.querySelectorAll('.content-section');

    // Funzione per mostrare la sezione corretta
    const showUserSection = (sectionId) => {
        // 1. Nascondi tutte le sezioni
        userSections.forEach(section => {
            section.classList.remove('is-visible');
            section.classList.add('is-hidden');
        });

        // 2. Mostra la sezione target
        const targetSection = document.getElementById(sectionId);
        if (targetSection) {
            targetSection.classList.remove('is-hidden');
            targetSection.classList.add('is-visible');
        }
    };

    if (userNavLinks.length > 0) {
        userNavLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                
                const targetId = link.getAttribute('data-section');
                
                // Aggiorna classi menu attivo
                userNavLinks.forEach(nav => nav.classList.remove('is-active'));
                link.classList.add('is-active');

                // Cambia URL senza ricaricare (utile se l'utente aggiorna la pagina)
                history.pushState(null, '', '#' + targetId);

                // Mostra il contenuto
                showUserSection(targetId);
            });
        });

        // Gestione Reload pagina: se c'è un #hash nell'URL (es. user.php#ordini), apri quella tab
        const currentHash = window.location.hash.replace('#', '');
        if (currentHash) {
            const activeLink = document.querySelector(`.user-nav-link[data-section="${currentHash}"]`);
            if (activeLink) {
                // Simula il click o attiva manualmente
                userNavLinks.forEach(nav => nav.classList.remove('is-active'));
                activeLink.classList.add('is-active');
                showUserSection(currentHash);
            }
        }
    }

    // B. Gestione Espansione Ordini (Mostra/Nascondi Dettagli)
    const orderTable = document.querySelector('.order-summary-table');
    
    if (orderTable) {
        orderTable.addEventListener('click', (e) => {
            // Cerchiamo se il click è avvenuto dentro un bottone toggle
            const toggleButton = e.target.closest('.toggle-details-btn');
            
            if (toggleButton) {
                e.preventDefault();
                
                // Recuperiamo l'ID dell'ordine dal bottone
                const orderId = toggleButton.getAttribute('data-order-id');
                // Troviamo la riga dei dettagli corrispondente
                const detailRow = document.getElementById('details-row-' + orderId);
                // Troviamo la riga "padre" (la card superiore)
                const summaryRow = toggleButton.closest('tr');

                const icon = toggleButton.querySelector('i');
                
                if (detailRow) {
                    // Toggle visibilità
                    const isHidden = detailRow.classList.contains('is-hidden');
                    
                    if (isHidden) {
                        // APRI
                        detailRow.classList.remove('is-hidden');
                        // AGGIUNTA: Aggiungiamo classe per lo stile unito
                        if (summaryRow) summaryRow.classList.add('card-is-open'); 

                        toggleButton.setAttribute('aria-expanded', 'true');
                        // Cambia icona
                        if(icon) {
                            icon.classList.remove('fa-chevron-down');
                            icon.classList.add('fa-chevron-up');
                        }
                    } else {
                        // CHIUDI
                        detailRow.classList.add('is-hidden');
                        if (summaryRow) summaryRow.classList.remove('card-is-open');

                        toggleButton.setAttribute('aria-expanded', 'false');
                        // Cambia icona
                        if(icon) {
                            icon.classList.remove('fa-chevron-up');
                            icon.classList.add('fa-chevron-down');
                        }
                    }
                }
            }
        });
    }

    /* ==========================================
     * 8. VALIDAZIONE FORM GENERICA (Client-Side)
     * ========================================== */
    const forms = document.querySelectorAll('form');

    // Funzione per trovare dove mettere l'errore
    const getErrorContainer = (input) => {
        const parent = input.parentElement;

        // CASO 1: Password (c'è il wrapper con l'occhio) -> L'errore va fuori dal wrapper, nel form-group
        if (parent.classList.contains('password-wrapper')) {
            return parent.closest('.form-group');
        }

        // CASO 2: Telefono (prefisso o numero) -> L'errore va sotto tutto il gruppo telefono
        if (parent.classList.contains('phone-prefix') || parent.classList.contains('phone-number')) {
            return parent.closest('.form-group');
        }

        // CASO 3: Standard e Colonne Affiancate (row-two)
        return parent; 
    };

    // Funzione per mostrare errore
    const showError = (input, message) => {
        const container = getErrorContainer(input);
        
        // Rimuovi eventuali errori precedenti nello stesso container
        const existingError = container.querySelector('.error-message');
        if (existingError) existingError.remove();

        // Aggiungi classe errore all'input
        input.classList.add('input-error');

        // Crea il messaggio
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.color = 'var(--accent-color)'; 
        errorDiv.style.fontSize = '0.85rem';
        errorDiv.style.marginTop = '0.25rem';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        
        container.appendChild(errorDiv);
    };

    // Funzione per rimuovere errore
    const clearError = (input) => {
        const container = getErrorContainer(input);
        const existingError = container.querySelector('.error-message');
        if (existingError) existingError.remove();
        input.classList.remove('input-error');
    };

    // Logica di controllo singolo campo
    const validateField = (input) => {
        const value = input.value.trim();

        // 0. Controllo Checkbox
        if (input.type === 'checkbox' && input.hasAttribute('required') && !input.checked) {
            showError(input, 'Devi accettare per proseguire');
            return false;
        }

        // 1. Controllo Required (Testo/Select)
        if (input.type !== 'checkbox' && input.hasAttribute('required') && value === '') {
            showError(input, 'Campo obbligatorio');
            return false;
        }

        // 2. Controllo Email
        if (input.type === 'email' && value !== '') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                showError(input, 'Formato email non valido');
                return false;
            }
        }

        // 3. Controllo Pattern
        if (input.hasAttribute('pattern') && value !== '') {
            const regex = new RegExp('^' + input.getAttribute('pattern') + '$');
            const msg = input.getAttribute('title') || 'Formato errato';
            
            if (!regex.test(input.value)) { 
                showError(input, msg);
                return false;
            }
        }

        // 4. Controllo MinLength
        if (input.hasAttribute('minlength') && value !== '') {
            const min = input.getAttribute('minlength');
            if (value.length < min) {
                showError(input, `Minimo ${min} caratteri`);
                return false;
            }
        }

        // 5. Controllo Uguaglianza Password
        const passwordMap = {
            'confirm-password': 'password',
            'ripeti_password': 'nuova_password'
        };

        if (passwordMap[input.name]) {
            const form = input.closest('form');
            const primaryFieldName = passwordMap[input.name];
            const primaryInput = form.querySelector(`input[name="${primaryFieldName}"]`);
            
            if (primaryInput && value !== primaryInput.value) {
                showError(input, 'Le password non coincidono');
                return false;
            }
        }

        // Se tutto ok
        clearError(input);
        return true;
    };

    // Attivazione su tutti i form
    forms.forEach(form => {
        form.setAttribute('novalidate', true);

        const inputs = form.querySelectorAll('input, select, textarea');

        // A. Controllo al Submit
        form.addEventListener('submit', (e) => {
            let isValid = true;
            inputs.forEach(input => {
                if (input.type === 'hidden' || input.type === 'submit') return;
                if (!validateField(input)) isValid = false;
            });

            if (!isValid) {
                e.preventDefault();
                const firstError = form.querySelector('.input-error');
                if(firstError) firstError.scrollIntoView({behavior: 'smooth', block: 'center'});
            }
        });

        // B. Controllo "Live"
        inputs.forEach(input => {
            // Quando esci dal campo
            input.addEventListener('blur', () => validateField(input));
            
            // Mentre scrivi (per testo)
            input.addEventListener('input', () => {
                if(input.classList.contains('input-error')) {
                    validateField(input);
                }
                // Logica Password Live
                if (input.name === 'password' || input.name === 'nuova_password') {
                    const form = input.closest('form');
                    const confirmName = input.name === 'password' ? 'confirm-password' : 'ripeti_password';
                    const confirmInput = form.querySelector(`input[name="${confirmName}"]`);
                    if (confirmInput && confirmInput.value !== '') {
                        validateField(confirmInput);
                    }
                }
            });

            // Mentre clicchi (per checkbox e radio)
            if (input.type === 'checkbox' || input.type === 'radio') {
                input.addEventListener('change', () => {
                    validateField(input);
                });
            }
        });
    });


    /* ==========================================
    * 9. GESTIONE CARRELLO (AJAX + LIMITI)
    * ========================================== */
    
    // A. GESTIONE DEI PULSANTI (+, -, ELIMINA, ETC.)
    document.body.addEventListener('click', function(e) {
        
        const btn = e.target.closest('.ajax-cmd');
        
        if (btn) {
            e.preventDefault(); 

            let action = btn.getAttribute('data-action');
            const idRiga = btn.getAttribute('data-id-riga');
            const idVino = btn.getAttribute('data-id-vino');
            
            let inputQty = document.getElementById('qty_v_' + idVino);
            if (!inputQty) inputQty = document.getElementById('qty_' + idRiga);

            let currentQty = 1;
            if (inputQty) currentQty = parseInt(inputQty.value);

            // --- CONTROLLO LIMITE MASSIMO (Button +) ---
            // Se premo PIU e sono già a 100 (o più), mi fermo.
            if (action === 'piu' && currentQty >= 100) {
                return; // Non fa nulla
            }

            // --- CONTROLLO LIMITE MINIMO (Button -) ---
            if (action === 'meno' && currentQty === 1) {
                action = 'rimuovi'; 
            }

            btn.style.opacity = '0.5';
            
            const formData = new FormData();
            formData.append('action', action);
            formData.append('id_riga', idRiga);
            formData.append('id_vino', idVino);
            formData.append('current_qty', currentQty);
            formData.append('ajax_mode', '1');

            inviaRichiestaCarrello(formData, btn, inputQty, action);
        }
    });

    // B. GESTIONE INPUT MANUALE
    document.body.addEventListener('change', function(e) {
        if (e.target.classList.contains('qty-input')) {
            const input = e.target;
            let newVal = parseInt(input.value); // Uso let per poterlo modificare
            const idRiga = input.getAttribute('data-id-riga');
            const idVino = input.getAttribute('data-id-vino');

            let action = 'aggiorna_quantita';
            
            // --- CONTROLLO LIMITE MASSIMO (Input manuale) ---
            if (newVal > 100) {
                input.value = 100;
                newVal = 100;
                // Procedo con l'aggiornamento a 100
            }

            // --- CONTROLLO LIMITE MINIMO (Input manuale) ---
            if (newVal <= 0) {
                if(confirm("Vuoi rimuovere questo vino dal carrello?")) {
                    action = 'rimuovi';
                } else {
                    input.value = 1;
                    return;
                }
            }

            input.style.opacity = '0.5';

            const formData = new FormData();
            formData.append('action', action);
            formData.append('id_riga', idRiga);
            formData.append('id_vino', idVino);
            formData.append('quantita', newVal);
            formData.append('ajax_mode', '1');

            inviaRichiestaCarrello(formData, input, input, action);
        }
    });

    // C. FUNZIONE UNICA 
    function inviaRichiestaCarrello(formData, triggerElement, inputQty, actionUsed) {
        fetch('carrello.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json()) 
        .then(data => {
            triggerElement.style.opacity = '1';
            
            if (data.success) {
                if (actionUsed === 'rimuovi' || actionUsed === 'salva_per_dopo' || actionUsed === 'sposta_in_carrello') {
                    window.location.reload();
                } else {
                    if (inputQty) inputQty.value = data.qty;
                    updateText('summary-subtotal', data.total_products);
                    updateText('summary-shipping', data.shipping, true);
                    updateText('summary-total', data.total_final);
                    updateText('cart-list-total', data.total_products);
                    updateText('cart-count-display', data.cart_count);
                }
            } else {
                window.location.reload();
            }
        })
        .catch(err => {
            console.error('Errore:', err);
            window.location.reload();
        });
    }

    function updateText(id, value, isHtml = false) {
        const el = document.getElementById(id);
        if (el) {
            if (isHtml) el.innerHTML = value;
            else el.innerText = value;
        }
    }
});
