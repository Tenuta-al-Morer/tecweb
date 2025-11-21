document.addEventListener('DOMContentLoaded', () => {
    const menuButton = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('#main-navigation');

    
    const apriMenu = () => {
        navMenu.classList.add('is-open');
        menuButton.setAttribute('aria-expanded', 'true');
    };

    const chiudiMenu = () => {
        navMenu.classList.remove('is-open');
        menuButton.setAttribute('aria-expanded', 'false');
    };

    
    document.addEventListener('click', (e) => {
        
        if (!menuButton || !navMenu) return;

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

    /* * ==========================================
     * 2. PULSANTE TORNA SU (Back to Top)
     * ==========================================
     */
    const backToTopBtn = document.getElementById('backToTopBtn');
    const mainNavBar = document.querySelector('.main-nav-bar');

    if (backToTopBtn && mainNavBar) {
        
        const toggleBackToTopButton = () => {
            const navBottomPosition = mainNavBar.offsetHeight;
            if (window.scrollY > navBottomPosition) {
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