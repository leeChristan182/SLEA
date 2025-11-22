// public/js/login.js

document.addEventListener('DOMContentLoaded', function () {
    // === ANTI-AUTOFILL: CLEAR EMAIL & PASSWORD ===
    const emailField = document.getElementById('email');
    const passwordField = document.getElementById('passwordInput');

    function wipeCredentials() {
        if (emailField) {
            emailField.value = '';
            emailField.setAttribute('autocomplete', 'off');
            emailField.setAttribute('autocapitalize', 'none');
            emailField.setAttribute('autocorrect', 'off');
        }

        if (passwordField) {
            passwordField.value = '';
            // For logins this can be 'off'; 'new-password' is helpful on register
            passwordField.setAttribute('autocomplete', 'off');
        }
    }

    // Run immediately
    wipeCredentials();
    // Run again shortly after load in case the browser fills *after* DOMContentLoaded
    setTimeout(wipeCredentials, 300);
    setTimeout(wipeCredentials, 1000);

    // === DARK MODE ===
    const body = document.body;
    const toggleBtn = document.getElementById('darkModeToggle');
    const toggleBtnFloating = document.getElementById('darkModeToggleFloating');

    // Initial theme from localStorage
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
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById('passwordInput');
            if (!input) return;

            const icon = btn.querySelector('i');
            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';

            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
    });

    // === PRIVACY MODAL ===
    setTimeout(() => {
        const modalEl = document.getElementById('privacyModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const privacyModal = new bootstrap.Modal(modalEl, {
                backdrop: 'static',
                keyboard: false
            });
            privacyModal.show();
        }
    }, 100);
});
