document.addEventListener('DOMContentLoaded', function () {
    // === DARK MODE ===
    const body = document.body;
    const toggleBtn = document.getElementById('darkModeToggle');
    const toggleBtnFloating = document.getElementById('darkModeToggleFloating');

    // Initial load
    if (localStorage.getItem('theme') === 'dark') {
        body.classList.add('dark-mode');
        toggleBtn?.querySelector('i')?.classList.replace('fa-moon', 'fa-sun');
        toggleBtnFloating?.querySelector('i')?.classList.replace('fa-moon', 'fa-sun');
    }

    function toggleTheme() {
        body.classList.toggle('dark-mode');
        const mode = body.classList.contains('dark-mode') ? 'dark' : 'light';
        localStorage.setItem('theme', mode);

        const icon = toggleBtn?.querySelector('i');
        const iconFloating = toggleBtnFloating?.querySelector('i');
        if (mode === 'dark') {
            icon?.classList.replace('fa-moon', 'fa-sun');
            iconFloating?.classList.replace('fa-moon', 'fa-sun');
        } else {
            icon?.classList.replace('fa-sun', 'fa-moon');
            iconFloating?.classList.replace('fa-sun', 'fa-moon');
        }
    }

    toggleBtn?.addEventListener('click', toggleTheme);
    toggleBtnFloating?.addEventListener('click', toggleTheme);

    // === PASSWORD TOGGLE ===
    // Use event delegation to prevent duplicate handlers
    const passwordInput = document.getElementById('passwordInput');
    const togglePasswordBtn = document.querySelector('.toggle-password');
    
    if (togglePasswordBtn && passwordInput) {
        // Remove any existing listeners by using a named function
        function handlePasswordToggle(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            const icon = togglePasswordBtn.querySelector('i');
            
            if (icon) {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                
                // Toggle icon classes
                if (isHidden) {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                    togglePasswordBtn.setAttribute('title', 'Hide password');
                } else {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                    togglePasswordBtn.setAttribute('title', 'Show password');
                }
            }
        }
        
        // Remove existing listener if any, then add new one
        togglePasswordBtn.removeEventListener('click', handlePasswordToggle);
        togglePasswordBtn.addEventListener('click', handlePasswordToggle);
        
        // Prevent browser's native password reveal on input
        passwordInput.addEventListener('input', function(e) {
            if (this.hasAttribute('data-custom-toggle')) {
                // Browser's native reveal is already hidden via CSS
            }
        });
    }

    // === PRIVACY MODAL ===
    setTimeout(() => {
        const modalEl = document.getElementById('privacyModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const privacyModal = new bootstrap.Modal(modalEl, {
                backdrop: 'static',
                keyboard: false
            });
            privacyModal.show();
        } else {
            console.error('Privacy modal not found or Bootstrap not loaded');
        }
    }, 100);
});
