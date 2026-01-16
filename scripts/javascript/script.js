const SafeStorage = {
    check: function() {
        try {
            return 'localStorage' in window && window.localStorage !== null;
        } catch (e) {
            return false;
        }
    },
    getItem: function(key) {
        if (this.check()) return localStorage.getItem(key);
        return null;
    },
    setItem: function(key, value) {
        if (this.check()) {
            try {
                localStorage.setItem(key, value);
            } catch (e) {
                console.warn('Storage full or unavailable');
            }
        }
    }
};

// Esegue i moduli in isolamento: se uno fallisce, gli altri continuano
const safeExecute = (moduleName, moduleFunction) => {
    try {
        document.querySelectorAll('.no-js').forEach(el => el.classList.remove('no-js'));
        moduleFunction();
    } catch (error) {
        console.error(`Error in module [${moduleName}]:`, error);
    }
};

document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.no-js').forEach(el => el.classList.remove('no-js'));

    /* ==========================================
     * 1. THEME MANAGEMENT (Default Dark)
     * ========================================== */
    safeExecute('Theme Manager', () => {
        const themeToggleBtn = document.getElementById('theme-toggle');
        
        
        const sunIconOld = `
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

        const moonIconOld = `
            <span class="visually-hidden">Passa alla modalità scura</span>
            <i class="fas fa-moon" aria-hidden="true"></i>
        `;


        
        const sunIcon = `
            <span class="visually-hidden">Passa alla modalita chiara</span>
            <svg viewBox="0 0 24 24" class="theme-icon" aria-hidden="true">
                <circle cx="12" cy="12" r="4.5"></circle>
                <line x1="12" y1="2" x2="12" y2="4"></line>
                <line x1="12" y1="20" x2="12" y2="22"></line>
                <line x1="4.2" y1="4.2" x2="5.8" y2="5.8"></line>
                <line x1="18.2" y1="18.2" x2="19.8" y2="19.8"></line>
                <line x1="2" y1="12" x2="4" y2="12"></line>
                <line x1="20" y1="12" x2="22" y2="12"></line>
                <line x1="4.2" y1="19.8" x2="5.8" y2="18.2"></line>
                <line x1="18.2" y1="5.8" x2="19.8" y2="4.2"></line>
            </svg>
        `;

        const moonIcon = `
            <span class="visually-hidden">Passa alla modalita scura</span>
            <svg viewBox="0 0 24 24" class="theme-icon" aria-hidden="true">
                <path d="M21 14.5A9 9 0 1 1 11.5 3a7.4 7.4 0 0 0 9.5 11.5z"></path>
            </svg>
        `;

        const setLightMode = (enableLight) => {
            if (enableLight) {
                
                document.body.classList.add('light-mode');
                SafeStorage.setItem('theme', 'light');
                
                if (themeToggleBtn) {
                    themeToggleBtn.innerHTML = moonIcon; 
                }
            } else {
                
                document.body.classList.remove('light-mode');
                SafeStorage.setItem('theme', 'dark');
                
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

        const savedTheme = SafeStorage.getItem('theme');
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
    });


    /* ==========================================
     * 2. MENU DI NAVIGAZIONE 
     * ========================================== */
    safeExecute('Navigation Menu', () => {
        const menuCheckbox = document.getElementById('menu-checkbox'); 
        const navMenu = document.querySelector('#main-navigation');
        const menuLabel = document.querySelector('.menu-toggle');

        if (menuCheckbox && navMenu && menuLabel) {
            
            const chiudiMenu = () => {
                menuCheckbox.checked = false; 
                menuLabel.setAttribute('aria-expanded', 'false'); 
                navMenu.classList.remove('is-open'); 
            };

            menuCheckbox.addEventListener('change', () => {
                const isOpen = menuCheckbox.checked;
                menuLabel.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                
                if(isOpen) {
                    navMenu.classList.add('is-open');
                } else {
                    navMenu.classList.remove('is-open');
                }
            });

            document.addEventListener('click', (e) => {
                
                if (menuCheckbox.checked && 
                    !e.target.closest('#main-navigation') && 
                    !e.target.closest('.menu-toggle') &&
                    e.target !== menuCheckbox) {
                    
                    chiudiMenu();
                }
            });
            window.addEventListener('scroll', () => {
                if (menuCheckbox.checked) {
                    chiudiMenu();
                }
            }, { passive: true });
        }
    });

    /* ==========================================
    * 3. UTILITIES (Scroll top, Links)
    * ========================================== */

    safeExecute('Utilities', () => {
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
    });


    /* ==========================================
    * 4. GESTIONE PASSWORD (Mostra/Nascondi)
    * ========================================== */
    safeExecute('Password Toggle', () => {
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
    });


    /* ==========================================
    * 5. GESTIONE SLIDER / CAROSELLO INFINITO
    * ========================================== */
    safeExecute('Infinite Slider', () => {
        const sliderContainer = document.getElementById('imageSlider');
        const slides = document.querySelectorAll('.slide');
        const nextBtn = document.querySelector('.next-btn');
        const prevBtn = document.querySelector('.prev-btn');
        const pauseBtn = document.getElementById('pauseBtn');

        if (sliderContainer && slides.length > 0) {

            const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');

            const clonesCount = 4;
            let currentIndex = clonesCount;
            let isTransitioning = false;
            let slideInterval;
            const intervalTime = 5000;
            let userPaused = false;

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
                
                if (mediaQuery.matches) {
                    sliderContainer.style.transition = 'none';
                } else {
                    sliderContainer.style.transition = 'transform 0.5s ease-in-out';
                }
                
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
                if (mediaQuery.matches || userPaused) return;
                slideInterval = setInterval(nextSlide, intervalTime);
            };

            const stopTimer = () => {
                clearInterval(slideInterval);
            };

            const resetTimer = () => {
                stopTimer();
                startTimer();
            };

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

            const pauseIcon = `
                <svg class="slider-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <line x1="9" y1="6" x2="9" y2="18"></line>
                    <line x1="15" y1="6" x2="15" y2="18"></line>
                </svg>
            `;

            const playIcon = `
                <svg class="slider-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M10 7 L17 12 L10 17 Z"></path>
                </svg>
            `;

            if (pauseBtn) {
                pauseBtn.addEventListener('click', () => {
                    userPaused = !userPaused;

                    if (userPaused) {
                        stopTimer();
                        pauseBtn.innerHTML = playIcon;
                        pauseBtn.setAttribute('aria-label', 'Riprendi lo scorrimento automatico');
                    } else {
                        startTimer();
                        pauseBtn.innerHTML = pauseIcon;
                        pauseBtn.setAttribute('aria-label', 'Metti in pausa lo scorrimento automatico');
                    }
                });
            }

            sliderContainer.addEventListener('mouseenter', stopTimer);
            sliderContainer.addEventListener('mouseleave', () => {
                startTimer(); 
            });

            window.addEventListener('resize', () => {
                const slideWidth = allSlides[0].offsetWidth;
                sliderContainer.style.transition = 'none';
                sliderContainer.style.transform = `translateX(${-slideWidth * currentIndex}px)`;
            });

            mediaQuery.addEventListener('change', () => {
                if (mediaQuery.matches) {
                    stopTimer();
                } else {
                    if (!userPaused) startTimer(); 
                }
            });

            let _printSaved = null;

            const enterPrintMode = () => {
                if (_printSaved) return;

                _printSaved = {
                    transform: sliderContainer.style.transform,
                    transition: sliderContainer.style.transition,
                    currentIndex: currentIndex,
                    isTransitioning: isTransitioning
                };

                stopTimer();

                sliderContainer.style.transition = 'none';
                sliderContainer.style.transform = 'none';
            };

            const exitPrintMode = () => {
                if (!_printSaved) return;

                sliderContainer.style.transition = _printSaved.transition;
                sliderContainer.style.transform = _printSaved.transform;

                currentIndex = _printSaved.currentIndex;
                isTransitioning = _printSaved.isTransitioning;

                updateInitialPosition();

                _printSaved = null;

                if (!mediaQuery.matches && !userPaused) startTimer();
            };

            window.addEventListener('beforeprint', enterPrintMode);
            window.addEventListener('afterprint', exitPrintMode);

            const mqlPrint = window.matchMedia('print');
            mqlPrint.addEventListener('change', (e) => {
                if (e.matches) enterPrintMode();
                else exitPrintMode();
            });

            startTimer();
        }
    });
   

    /* ==========================================
     * 6. ADMIN TABS 
     * ========================================== */
    safeExecute('Admin Tabs', () => {
        const map = {
            "#tab-vini": "#section-vini",
            "#tab-degustazioni": "#section-esperienze",
            "#tab-info": "#section-messaggi",
        };

        const tabs = document.querySelectorAll(".admin-tabs .admin-tab");

        if (tabs.length > 0) {
            const sections = Object.values(map).map(sel => document.querySelector(sel));

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

                    if (active === hash) {
                        showAll();
                    } else {
                        showOnly(hash);
                    }
                });
            });

            if (map[window.location.hash]) {
                showOnly(window.location.hash);
            } else {
                showAll();
            }
        }

        function bindArchiveToggle(toggleId, tableId, displayMode = "table") {
            const toggle = document.getElementById(toggleId);
            const table = document.getElementById(tableId);

            if (!toggle || !table) return;

            const container = table.closest(".table-container");
            const containerDisplay = container ? "block" : null;

            const apply = () => {
                table.style.display = toggle.checked ? displayMode : "none";
                if (container) {
                    container.style.display = toggle.checked ? containerDisplay : "none";
                }
            };

            apply();
            toggle.addEventListener("change", apply);
        }
        
        const toggleVini = document.getElementById('toggleViniEliminati');
        if (toggleVini) {
            toggleVini.addEventListener('change', function() {
                const deletedRows = document.querySelectorAll('.row-deleted');
                
                deletedRows.forEach(row => {
                    row.style.display = this.checked ? 'table-row' : 'none';
                });
            });
        }

        bindArchiveToggle("toggleArchivioMessaggi", "tab-info-archivio", "table");
        bindArchiveToggle("toggleArchivioPrenotazioni", "tab-degustazioni-archivio", "table");
        bindArchiveToggle("toggleArchivioVini", "tab-ordini-archivio", "table");
    });

    /* ==========================================
     * 7. USER DASHBOARD 
     * ========================================== */
    safeExecute('User Dashboard', () => {    
        const userNavLinks = document.querySelectorAll('.user-nav-link');
        const userSections = document.querySelectorAll('.content-section');

        const showUserSection = (sectionId) => {
            userSections.forEach(section => {
                section.classList.remove('is-visible');
                section.classList.add('is-hidden');
            });

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
                    
                    userNavLinks.forEach(nav => nav.classList.remove('is-active'));
                    link.classList.add('is-active');

                    history.pushState(null, '', '#' + targetId);

                    showUserSection(targetId);
                });
            });

            const currentHash = window.location.hash.replace('#', '');
            if (currentHash) {
                const activeLink = document.querySelector(`.user-nav-link[data-section="${currentHash}"]`);
                if (activeLink) {
                    userNavLinks.forEach(nav => nav.classList.remove('is-active'));
                    activeLink.classList.add('is-active');
                    showUserSection(currentHash);
                }
            }
        }

        const orderTables = document.querySelectorAll('.user-dashboard-container .table-data');
        
        if (orderTables.length > 0) {
            orderTables.forEach((orderTable) => {
                orderTable.addEventListener('click', (e) => {
                    const toggleButton = e.target.closest('.toggle-details-btn');
                    
                    if (toggleButton) {
                        e.preventDefault();
                        
                        const orderId = toggleButton.getAttribute('data-order-id');
                        const detailRow = document.getElementById('details-row-' + orderId);
                        const summaryRow = toggleButton.closest('tr');
                        const icon = toggleButton.querySelector('i');
                        
                        if (detailRow) {
                            const isHidden = detailRow.classList.contains('is-hidden');
                            
                            if (isHidden) {
                                detailRow.classList.remove('is-hidden');
                                if (summaryRow) summaryRow.classList.add('card-is-open'); 

                                toggleButton.setAttribute('aria-expanded', 'true');
                                if (icon) {
                                    icon.classList.remove('fa-chevron-down');
                                    icon.classList.add('fa-chevron-up');
                                }
                            } else {
                                detailRow.classList.add('is-hidden');
                                if (summaryRow) summaryRow.classList.remove('card-is-open');

                                toggleButton.setAttribute('aria-expanded', 'false');
                                if (icon) {
                                    icon.classList.remove('fa-chevron-up');
                                    icon.classList.add('fa-chevron-down');
                                }
                            }
                        }
                    }
                });
            });
        }
    });

    /* ==========================================
    * 8. VALIDAZIONE FORM GENERICA 
    * ========================================== */
    safeExecute('Form Validation', () => {
        const forms = document.querySelectorAll('form');

        const getErrorContainer = (input) => {
            const parent = input.parentElement;

            if (parent.classList.contains('password-wrapper')) {
                return parent.parentElement;
            }

            if (parent.classList.contains('phone-prefix') || parent.classList.contains('phone-number')) {
                return parent.closest('.phone-group');
            }
            
            if (input.type === 'checkbox') {
                return input.closest('.checkbox-container') || parent;
            }

            return parent; 
        };

        const createErrorElement = (message, isSpacer = false) => {
        const div = document.createElement('div');
        
        if (isSpacer) {
            div.className = 'error-spacer';
            div.setAttribute('aria-hidden', 'true');
            div.innerHTML = `<i class="fas fa-exclamation-circle"></i> &nbsp;`; 
        } else {
            div.className = 'error-message';
            div.setAttribute('role', 'alert');
            div.setAttribute('aria-live', 'polite'); 
            div.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        }
        return div;
    };

        const syncRowAlignment = (input) => {
            const container = getErrorContainer(input);
            const rowParent = container.closest('.row-two, .form-row');

            if (!rowParent) return;

            const cols = Array.from(rowParent.children);
            const sibling = cols.find(c => c !== container);

            if (!sibling) return; 

            const myError = container.querySelector('.error-message');
            const siblingError = sibling.querySelector('.error-message');
            const siblingSpacer = sibling.querySelector('.error-spacer');
            const mySpacer = container.querySelector('.error-spacer');

            if (myError && !siblingError && !siblingSpacer) {
                const label = sibling.querySelector('label');
                const spacer = createErrorElement('', true);
                
                if (label) label.after(spacer);
                else sibling.prepend(spacer);
            }

            if (myError && mySpacer) {
                mySpacer.remove();
            }

            if (siblingError && !myError && !mySpacer) {
                const label = container.querySelector('label');
                const spacer = createErrorElement('', true);
                
                if (label) label.after(spacer);
                else container.prepend(spacer);
            }

            if (!myError && !siblingError) {
                if (mySpacer) mySpacer.remove();
                if (siblingSpacer) siblingSpacer.remove();
            }
            
            if (myError && siblingError) {
                if (mySpacer) mySpacer.remove();
                if (siblingSpacer) siblingSpacer.remove();
            }
        };

        const showError = (input, message) => {
            const container = getErrorContainer(input);
            const currentError = container.querySelector('.error-message');
            if (currentError && currentError.textContent.includes(message)) {
                return; 
            }
            const errorId = 'error-' + input.id;

            if (input.type === 'checkbox') {
                const prev = container.previousElementSibling;
                if (prev && prev.classList.contains('error-message')) {
                    prev.remove();
                }
            } else {
                if (currentError) currentError.remove();
            }

            const existingSpacer = container.querySelector('.error-spacer');
            if (existingSpacer) existingSpacer.remove();

            input.classList.add('input-error');
            input.setAttribute('aria-invalid', 'true');

            const currentDescribedBy = input.getAttribute('aria-describedby') || '';
            const ids = currentDescribedBy.split(' ').filter(id => id !== errorId && id !== '');
            ids.push(errorId);
            input.setAttribute('aria-describedby', ids.join(' '));

            const errorDiv = createErrorElement(message, false);
            errorDiv.id = errorId;
            
            if (input.type === 'checkbox') {
                container.before(errorDiv);
            } else {
                const label = container.querySelector('label');
                if (label) {
                    label.after(errorDiv);
                } else {
                    container.prepend(errorDiv);
                }
                syncRowAlignment(input);
            }
        };

        const clearError = (input) => {
            const container = getErrorContainer(input);
            const errorId = 'error-' + input.id;

            if (input.type === 'checkbox') {
                const prev = container.previousElementSibling;
                if (prev && prev.classList.contains('error-message')) {
                    prev.remove();
                }
            } else {
                const existingError = container.querySelector('.error-message');
                if (existingError) existingError.remove();
                syncRowAlignment(input);
            }

            input.classList.remove('input-error');
            input.removeAttribute('aria-invalid');

            const currentDescribedBy = input.getAttribute('aria-describedby') || '';
            const ids = currentDescribedBy.split(' ').filter(id => id !== errorId && id !== '');

            if (ids.length > 0) {
                input.setAttribute('aria-describedby', ids.join(' '));
            } else {
                input.removeAttribute('aria-describedby');
            }
        };

        const validateField = (input) => {
            const value = input.value.trim();

            if (input.type === 'checkbox' && input.hasAttribute('required') && !input.checked) {
                showError(input, 'Devi accettare per proseguire');
                return false;
            }

            if (input.type !== 'checkbox' && input.hasAttribute('required') && value === '') {
                showError(input, 'Campo obbligatorio');
                return false;
            }

            if (input.type === 'email' && value !== '') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    showError(input, 'Formato email non valido');
                    return false;
                }
            }

            if (input.hasAttribute('pattern') && value !== '') {
                const regex = new RegExp('^' + input.getAttribute('pattern') + '$');
                const msg = input.getAttribute('title') || 'Formato errato';
                
                if (!regex.test(input.value)) { 
                    showError(input, msg);
                    return false;
                }
            }

            if (input.hasAttribute('minlength') && value !== '') {
                const min = input.getAttribute('minlength');
                if (value.length < min) {
                    showError(input, `Minimo ${min} caratteri`);
                    return false;
                }
            }

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

            clearError(input);
            return true;
        };

        forms.forEach(form => {
            form.setAttribute('novalidate', true);
            const inputs = form.querySelectorAll('input, select, textarea');

            form.addEventListener('submit', (e) => {
                let isValid = true;
                inputs.forEach(input => {
                    if (input.type === 'hidden' || input.type === 'submit') return;
                    if (!validateField(input)) isValid = false;
                });

                if (!isValid) {
                    e.preventDefault();
                    const firstError = document.querySelector('.error-message');
                    if(firstError) firstError.scrollIntoView({behavior: 'smooth', block: 'center'});
                }
            });

            inputs.forEach(input => {
                input.addEventListener('blur', () => validateField(input));
                
                input.addEventListener('input', () => {
                    if(input.classList.contains('input-error')) {
                        validateField(input);
                    }
                    if (input.name === 'password' || input.name === 'nuova_password') {
                        const form = input.closest('form');
                        const confirmName = input.name === 'password' ? 'confirm-password' : 'ripeti_password';
                        const confirmInput = form.querySelector(`input[name="${confirmName}"]`);
                        if (confirmInput && confirmInput.value !== '') {
                            validateField(confirmInput);
                        }
                    }
                });

                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.addEventListener('change', () => {
                        validateField(input);
                    });
                }
            });
        });
    });

    /* ==========================================
    * 9. GESTIONE CARRELLO 
    * ========================================== */
    safeExecute('Cart Logic', () => {    
        document.body.addEventListener('click', function(e) {
            
            const btn = e.target.closest('.cart-action-btn');
            
            if (btn) {
                e.preventDefault(); 

                let action = btn.getAttribute('data-action');
                const idRiga = btn.getAttribute('data-id-riga');
                const idVino = btn.getAttribute('data-id-vino');
                
                let inputQty = document.getElementById('qty_v_' + idVino);
                if (!inputQty) inputQty = document.getElementById('qty_' + idRiga);

                let currentQty = 1;
                let maxStock = 9999; 

                if (inputQty) {
                    currentQty = parseInt(inputQty.value);
                    let stockAttr = inputQty.getAttribute('data-stock');
                    if (stockAttr) {
                        maxStock = parseInt(stockAttr);
                    }
                }

                if (action === 'piu' && currentQty >= maxStock) {
                    return;
                }

                if (action === 'meno' && currentQty === 1) {
                    action = 'rimuovi'; 
                }

                btn.style.opacity = '0.5';
                
                const formData = new FormData();
                formData.append('action', action);
                formData.append('id_riga', idRiga);
                formData.append('id_vino', idVino);
                formData.append('current_qty', currentQty);

                formData.append('req_source', 'cart_update'); 

                inviaRichiestaCarrello(formData, btn, inputQty, action);
            }
        });

        document.body.addEventListener('change', function(e) {
            if (e.target.classList.contains('qty-input')) {
                const input = e.target;
                let newVal = parseInt(input.value); 
                const idRiga = input.getAttribute('data-id-riga');
                const idVino = input.getAttribute('data-id-vino');
                
                let maxStock = 9999;
                let stockAttr = input.getAttribute('data-stock');
                if (stockAttr) {
                    maxStock = parseInt(stockAttr);
                }

                let action = 'aggiorna_quantita';
                
                if (newVal > maxStock) {
                    input.value = maxStock;
                    newVal = maxStock;
                }

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
                formData.append('req_source', 'cart_update'); 

                inviaRichiestaCarrello(formData, input, input, action);
            }
        });

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
                        updateText('shipping-message-container', data.shipping_progress, true);

                        const badge = document.getElementById('global-cart-badge');
                        
                        if (badge) {
                            if (data.cart_count > 0) {
                                badge.innerText = data.cart_count > 99 ? '99+' : data.cart_count;
                                badge.style.display = 'flex'; 
                            } else {
                                badge.style.display = 'none'; 
                            }
                        } else if (data.cart_count > 0) {
                            window.location.reload();
                        }
                    }
                } else {
                    window.location.reload();
                }
            })
            .catch(err => {
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

    /* ==========================================
     * 10. CHECKOUT LOGIC
     * ========================================== */
    safeExecute('Checkout', () => {
        const checkoutForm = document.getElementById('checkout-form');
        
        if (checkoutForm) {
            const paymentRadios = document.querySelectorAll('input[name="metodo_pagamento"]');
            
            paymentRadios.forEach(radio => {
                radio.addEventListener('change', () => {
                });
                
                const card = radio.closest('.payment-card');
                if(card) {
                    card.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            radio.checked = true;
                            radio.dispatchEvent(new Event('change'));
                        }
                    });
                }
            });

            checkoutForm.addEventListener('submit', function(e) {
                const btn = this.querySelector('button[type="submit"]');
                
                if (this.checkValidity()) {
                    if (btn) {
                        btn.disabled = true;
                        const originalText = btn.innerText;
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Elaborazione...';
                        
                        setTimeout(() => {
                            btn.disabled = false;
                            btn.innerText = originalText;
                        }, 10000);
                    }
                }
            });
        }
    });
    
    /* ==========================================
     * 11. LOGICA PAGINA VINI
     * ========================================== */
    safeExecute('Pagina Vini', () => {

        document.body.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-plus, .btn-minus');

            if (btn) {
                e.preventDefault();

                const wrapper = btn.closest('.selettore-quantita');
                if (!wrapper) return;

                const input = wrapper.querySelector('input[type="number"]');
                if (!input) return;

                let currentVal = parseInt(input.value) || 1;
                let max = 9999;

                if (input.hasAttribute('max')) {
                    max = parseInt(input.getAttribute('max'));
                }

                let newVal = currentVal;
                if (btn.classList.contains('btn-plus')) {
                    if (currentVal < max) newVal = currentVal + 1;
                } else {
                    if (currentVal > 1) newVal = currentVal - 1;
                }

                input.value = newVal;
            }
        });

        document.body.addEventListener('keydown', function(e) {
            if (e.target.getAttribute('role') === 'button' && e.target.tagName === 'LABEL') {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    e.target.click();
                }
            }
        });

        const toast = document.getElementById('cart-toast');

        document.body.addEventListener('submit', function(e) {
            if (e.target.matches('.wine-form') || e.target.matches('.modal-wine-form')) {

                const submitBtn = e.submitter;

                if (submitBtn && (submitBtn.value === 'minus' || submitBtn.value === 'plus')) {
                    e.preventDefault();
                    return;
                }

                e.preventDefault();

                const form = e.target;
                const finalSubmitBtn = form.querySelector('button.buy-button');

                document.body.style.cursor = 'wait';
                if (finalSubmitBtn) finalSubmitBtn.disabled = true;

                const formData = new FormData(form);

                formData.append('req_source', 'vini_page');

                fetch('carrello.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) throw new Error("Network response was not ok");
                        return response.json();
                    })
                    .then(data => {
                        document.body.style.cursor = 'default';
                        if (finalSubmitBtn) finalSubmitBtn.disabled = false;

                        if (data.success) {
                            showToast("Prodotto aggiunto al carrello!");
                            resetFormQty(form);

                            const badge = document.getElementById('global-cart-badge');

                            if (badge) {
                                badge.innerText = data.cart_count > 99 ? '99+' : data.cart_count;
                                badge.style.display = 'flex';
                            } else {
                                if (data.cart_count > 0) location.reload();
                            }

                        } else {}
                    })
                    .catch(error => {
                        console.error('Errore:', error);
                        document.body.style.cursor = 'default';
                        if (finalSubmitBtn) finalSubmitBtn.disabled = false;
                    });
            }
        });

        function resetFormQty(form) {
            const input = form.querySelector('input[type="number"]');
            if (input) input.value = 1;
        }

        function showToast(message) {
            let localToast = document.getElementById('cart-toast');
            if (!localToast) {
                localToast = document.createElement('div');
                localToast.id = 'cart-toast';
                localToast.className = 'toast';
                document.body.appendChild(localToast);
            }

            localToast.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
            localToast.classList.add('show');

            setTimeout(() => {
                localToast.classList.remove('show');
            }, 3000);
        }

        const searchForm = document.getElementById('wine-search-form');
        const searchInput = document.getElementById('wine-search-input');

        if (searchForm && searchInput) {
            const mostraErroreSearch = (msg) => {
                const container = searchInput.parentElement;
                let errorDiv = container.querySelector('.search-error-message');
                
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'search-error-message error-message';
                    errorDiv.setAttribute('role', 'alert');
                    container.insertBefore(errorDiv, searchInput);
                }                
                errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${msg}`;
                searchInput.classList.add('input-error');
                searchInput.setAttribute('aria-invalid', 'true');
            };

            const rimuoviErroreSearch = () => {
                const container = searchInput.parentElement;
                const errorDiv = container.querySelector('.search-error-message');
                if (errorDiv) errorDiv.remove();
                
                searchInput.classList.remove('input-error');
                searchInput.removeAttribute('aria-invalid');
            };

            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();

                const query = searchInput.value.trim().toLowerCase();
    
                if (query.length < 3) {
                    mostraErroreSearch("Inserisci almeno 3 caratteri per la ricerca.");
                    return;
                }

                const items = Array.from(document.querySelectorAll('.wine-article'));
                
                const match = items.find((item) => {
                    const title = item.querySelector('h3');
                    return title && title.innerText.toLowerCase().includes(query);
                });

                if (match) {
                    rimuoviErroreSearch();
                    match.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    match.classList.add('is-highlighted');
                    setTimeout(() => {
                        bestMatch.classList.remove('is-highlighted');
                    }, 3000);
                } else {
                    mostraErroreSearch("Nessun vino trovato con questo nome.");
                }
            });
            searchInput.addEventListener('input', () => {
                rimuoviErroreSearch();
            });
        }

        const modalCheckboxes = document.querySelectorAll('.modal-toggle-checkbox');

        if (modalCheckboxes.length > 0) {
            let ultimoElementoFocusato = null;

            modalCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const modalOverlay = this.nextElementSibling;
                    const modalContent = modalOverlay ? modalOverlay.querySelector('.modal-content') : null;

                    if (!modalContent) return;

                    if (this.checked) {
                        ultimoElementoFocusato = document.activeElement;
                        attivaFocusTrap(modalContent, this);
                    } else {
                        if (ultimoElementoFocusato) {
                            ultimoElementoFocusato.focus();
                            ultimoElementoFocusato = null;
                        }
                    }
                });
            });

            function attivaFocusTrap(modal, checkboxController) {
                const focusableElementsString = 'a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, object, embed, [tabindex="0"], [contenteditable]';

                const getFocusables = () => {
                    let elements = Array.from(modal.querySelectorAll(focusableElementsString));
                    return elements.filter(el => el.offsetParent !== null);
                };

                let focusables = getFocusables();
                if (focusables.length === 0) return;

                let firstTabStop = focusables[0];
                let lastTabStop = focusables[focusables.length - 1];

                modal.focus();
                if (document.activeElement !== modal) {
                    firstTabStop.focus();
                }

                const keyHandler = function(e) {
                    if (e.key === 'Escape') {
                        checkboxController.checked = false;
                        checkboxController.dispatchEvent(new Event('change'));
                        return;
                    }

                    if (e.key === 'Tab') {
                        focusables = getFocusables();
                        firstTabStop = focusables[0];
                        lastTabStop = focusables[focusables.length - 1];

                        if (e.shiftKey) {
                            if (document.activeElement === firstTabStop || document.activeElement === modal) {
                                e.preventDefault();
                                lastTabStop.focus();
                            }
                        } else {
                            if (document.activeElement === lastTabStop) {
                                e.preventDefault();
                                firstTabStop.focus();
                            }
                        }
                    }
                };

                const globalFocusHandler = function(e) {
                    if (!checkboxController.checked) return;

                    if (!modal.contains(e.target)) {
                        e.stopPropagation();
                        e.preventDefault();
                        firstTabStop.focus();
                    }
                };

                const windowFocusHandler = function() {
                    if (checkboxController.checked && !modal.contains(document.activeElement)) {
                        firstTabStop.focus();
                    }
                };

                modal.addEventListener('keydown', keyHandler);

                document.addEventListener('focus', globalFocusHandler, true);

                window.addEventListener('focus', windowFocusHandler);

                const cleanupListener = function() {
                    if (!checkboxController.checked) {
                        modal.removeEventListener('keydown', keyHandler);
                        document.removeEventListener('focus', globalFocusHandler, true);
                        window.removeEventListener('focus', windowFocusHandler);
                        checkboxController.removeEventListener('change', cleanupListener);
                    }
                };
                checkboxController.addEventListener('change', cleanupListener);
            }
        }
    });
    
    /* ==========================================
    * GESTIONE MODALE 
    * ========================================== */
    safeExecute('Hybrid Modal', () => { 

        document.documentElement.classList.remove('no-js');
        
        const openButtons = document.querySelectorAll('button.js-visible[data-checkbox-id]');
        const closeButtons = document.querySelectorAll('button.js-close-modal');
        
        let lastFocusedElement = null;

        function openModal(btn) {
            const checkboxId = btn.getAttribute('data-checkbox-id');
            const checkbox = document.getElementById(checkboxId);
            const dialogId = btn.getAttribute('aria-controls');
            const dialog = document.getElementById(dialogId);

            if (checkbox && dialog) {
                lastFocusedElement = btn;
                checkbox.checked = true;
                
                document.body.style.overflow = 'hidden';

                const title = dialog.querySelector('h2');
                if (title) {
                    title.setAttribute('tabindex', '-1');
                    title.focus();
                } else {
                    dialog.focus();
                }

                dialog.addEventListener('keydown', trapFocus);
                document.addEventListener('keydown', handleEsc);
            }
        }

        function closeModal(btn) {
            const overlay = btn.closest('.modal-overlay');
            const checkbox = overlay.previousElementSibling; 
            
            if (checkbox) {
                checkbox.checked = false;
                document.body.style.overflow = '';
                
                const dialog = overlay.querySelector('.modal-content');
                dialog.removeEventListener('keydown', trapFocus);
                document.removeEventListener('keydown', handleEsc);

                if (lastFocusedElement) lastFocusedElement.focus();
            }
        }

        function trapFocus(e) {
            if (e.key !== 'Tab') return;
            const dialog = e.currentTarget;
            const focusables = dialog.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            const first = focusables[0];
            const last = focusables[focusables.length - 1];

            if (e.shiftKey) {
                if (document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                }
            } else { // Tab
                if (document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
        }

        function handleEsc(e) {
            if (e.key === 'Escape') {
                const checked = document.querySelector('.modal-toggle-checkbox:checked');
                if (checked) {
                    const overlay = checked.nextElementSibling;
                    const closeBtn = overlay.querySelector('.js-close-modal');
                    if (closeBtn) closeModal(closeBtn);
                }
            }
        }

        openButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                openModal(btn);
            });
        });

        closeButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                closeModal(btn);
            });
        });
    });

    /* ==========================================
     * 12. LOGICA PAGINA ADMIN 
     * ========================================== */
    safeExecute('Pagina Admin', () => {
        // Controllo esistenza elementi chiave
        const modal = document.getElementById('modalVino');
        if (!modal) return; 

        const modalTitle = document.getElementById('modalTitle');
        const firstInput = document.getElementById('nome');
        const modalUtente = document.getElementById('modalUtente');
        const modalUserTitle = document.getElementById('modalUserTitle');
        const firstUserInput = document.getElementById('utente_nome');

        window.apriModalNuovo = function() {
            if(modalTitle) modalTitle.innerText = "Aggiungi Nuovo Vino";
            const idCampo = document.getElementById('id_vino');
            if(idCampo) idCampo.value = ""; 
            
            const form = document.querySelector('#modalVino form');
            if(form) form.reset();
            
            modal.style.display = "flex";
            if(firstInput) firstInput.focus();
        };

        window.apriModalNuovoUtente = function() {
            if(modalUserTitle) modalUserTitle.innerText = "Aggiungi Nuovo Utente";

            const form = document.querySelector('#modalUtente form');
            if(form) form.reset();

            if (modalUtente) {
                modalUtente.style.display = "flex";
                if(firstUserInput) firstUserInput.focus();
            }
        };

        window.apriModalModifica = function(vino) {
            if(modalTitle) modalTitle.innerText = "Modifica: " + vino.nome;
            
            const setVal = (id, val) => {
                const el = document.getElementById(id);
                if(el) el.value = val;
            };

            setVal('id_vino', vino.id);
            setVal('nome', vino.nome);
            setVal('prezzo', vino.prezzo);
            setVal('quantita_stock', vino.quantita_stock);
            setVal('stato', vino.stato);
            setVal('categoria', vino.categoria);
            setVal('img', vino.img);
            setVal('descrizione_breve', vino.descrizione_breve);
            setVal('descrizione_estesa', vino.descrizione_estesa);
            
            setVal('vitigno', vino.vitigno || "");
            setVal('annata', vino.annata || "");
            setVal('gradazione', vino.gradazione || "");
            setVal('temperatura', vino.temperatura || "");
            setVal('abbinamenti', vino.abbinamenti || "");

            modal.style.display = "flex";
            if(firstInput) firstInput.focus();
        };

        window.chiudiModal = function() {
            modal.style.display = "none";
        };

        window.chiudiModalUtente = function() {
            if (modalUtente) modalUtente.style.display = "none";
        };
        
        window.onclick = function(event) {
            if (event.target == modal) window.chiudiModal();
            if (modalUtente && event.target == modalUtente) window.chiudiModalUtente();
        };
        
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                if (modal.style.display === "flex") window.chiudiModal();
                if (modalUtente && modalUtente.style.display === "flex") window.chiudiModalUtente();
            }
        });
    });
});
