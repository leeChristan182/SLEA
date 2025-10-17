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
    document.querySelector('.toggle-password')?.addEventListener('click', function () {
        const input = document.getElementById('passwordInput');
        const icon = this.querySelector('i');
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        icon.className = isHidden ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
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
        } else {
            console.error('Privacy modal not found or Bootstrap not loaded');
        }
    }, 100);
});
