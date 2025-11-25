document.addEventListener('DOMContentLoaded', function () {
    // === DARK MODE ===
    const body = document.body;
    const toggleBtn = document.getElementById('darkModeToggle');
    const toggleBtnFloating = document.getElementById('darkModeToggleFloating');

    const storedTheme = localStorage.getItem('theme');
    if (storedTheme === 'dark') {
        body.classList.add('dark-mode');
        toggleBtn?.querySelector('i')?.classList.replace('fa-moon', 'fa-sun');
        toggleBtnFloating?.querySelector('i')?.classList.replace('fa-moon', 'fa-sun');
    }

    function toggleTheme() {
        body.classList.toggle('dark-mode');
        const mode = body.classList.contains('dark-mode') ? 'dark' : 'light';
        localStorage.setItem('theme', mode);

        const iconMain = toggleBtn?.querySelector('i');
        const iconFloat = toggleBtnFloating?.querySelector('i');

        if (mode === 'dark') {
            iconMain?.classList.replace('fa-moon', 'fa-sun');
            iconFloat?.classList.replace('fa-moon', 'fa-sun');
        } else {
            iconMain?.classList.replace('fa-sun', 'fa-moon');
            iconFloat?.classList.replace('fa-sun', 'fa-moon');
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

    // === LOGIN FORM WIRING (visible -> hidden) ===
    const form          = document.getElementById('loginForm');
    const emailDisplay  = document.getElementById('email_display');
    const emailReal     = document.getElementById('email_real');
    const passwordReal  = document.getElementById('password_real');

    if (emailDisplay && emailReal) {
        // Show old('email') if present, otherwise blank
        emailDisplay.value = emailReal.value || '';
    }

    if (passwordInput && passwordReal) {
        // Never pre-fill password on load
        passwordInput.value = '';
        passwordReal.value = '';
    }

    if (form && emailDisplay && emailReal && passwordInput && passwordReal) {
        form.addEventListener('submit', () => {
            emailReal.value    = emailDisplay.value.trim();
            passwordReal.value = passwordInput.value;
        });
    }

    // === PASSWORD TOGGLE (eye button) - ROBUST VERSION ===
    const togglePasswordBtn = document.querySelector('.toggle-password');

    if (togglePasswordBtn && passwordInput) {
        function handlePasswordToggle(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            const icon = togglePasswordBtn.querySelector('i');
            const isHidden = passwordInput.type === 'password';

            passwordInput.type = isHidden ? 'text' : 'password';

            if (icon) {
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

        // In case this script gets loaded twice, clear any previous binding of this handler
        togglePasswordBtn.removeEventListener('click', handlePasswordToggle);
        togglePasswordBtn.addEventListener('click', handlePasswordToggle);

        // Hook for custom CSS to hide native reveal if you're using it
        passwordInput.addEventListener('input', function () {
            if (this.hasAttribute('data-custom-toggle')) {
                // no-op: native reveal is handled via CSS; this is just a hook
            }
        });
    }

    // === PREVENT PANIC CLICKS + SPINNER ===
    const submitBtn = form?.querySelector('button[type="submit"]');
    if (form && submitBtn) {
        let isSubmitting = false;

        form.addEventListener('submit', function (e) {
            if (isSubmitting) {
                e.preventDefault();
                return;
            }

            if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
                e.preventDefault();
                form.reportValidity?.();
                return;
            }

            isSubmitting = true;
            submitBtn.disabled = true;

            if (!submitBtn.dataset.originalHtml) {
                submitBtn.dataset.originalHtml = submitBtn.innerHTML;
            }

            submitBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Logging in...
            `;
        });
    }

    // === PRIVACY MODAL (with localStorage ack) ===
    setTimeout(() => {
        const modalEl = document.getElementById('privacyModal');
        if (!modalEl || typeof bootstrap === 'undefined') return;

        const STORAGE_KEY = 'slea_privacy_ack_v1';

        if (localStorage.getItem(STORAGE_KEY) === '1') {
            // already acknowledged → don't show again
            return;
        }

        const privacyModal = new bootstrap.Modal(modalEl, {
            backdrop: 'static',
            keyboard: false
        });

        // when user closes/continues, remember choice
        modalEl.addEventListener('hidden.bs.modal', () => {
            localStorage.setItem(STORAGE_KEY, '1');
        }, { once: true });

        privacyModal.show();
    }, 100);
});
