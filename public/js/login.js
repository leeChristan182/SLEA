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

    // === LOGIN FORM WIRING (visible -> hidden) ===
    const form          = document.getElementById('loginForm');
    const passwordInput = document.getElementById('passwordInput');
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

    // === PASSWORD TOGGLE (eye button) ===
    // === PASSWORD TOGGLE (eye button) ===
    // Use more specific selector to avoid duplicates - select only the one in the password input group
    const passwordInputGroup = passwordInput?.closest('.input-group');
    const togglePasswordBtn = passwordInputGroup?.querySelector('.toggle-password');

    if (togglePasswordBtn && passwordInput) {
        // Ensure button is clickable and visible
        togglePasswordBtn.style.pointerEvents = 'auto';
        togglePasswordBtn.style.cursor = 'pointer';
        togglePasswordBtn.style.zIndex = '10';
        togglePasswordBtn.setAttribute('tabindex', '0');

        // Simple toggle function - directly uses the button and input
        const handlePasswordToggle = function(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
            }

            // Get icon directly from the button
            const icon = togglePasswordBtn.querySelector('i');

            if (!icon || !passwordInput) {
                console.warn('Password toggle elements not found');
                return false;
            }

            // Save current state BEFORE toggling
            const isHidden = passwordInput.type === 'password';
            const wasFocused = document.activeElement === passwordInput;
            const cursorPosition = passwordInput.selectionStart || passwordInput.value.length;

            // Toggle password visibility
            passwordInput.type = isHidden ? 'text' : 'password';

            // Update icon
            if (isHidden) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                togglePasswordBtn.setAttribute('title', 'Hide password');
                togglePasswordBtn.setAttribute('aria-label', 'Hide password');
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                togglePasswordBtn.setAttribute('title', 'Show password');
                togglePasswordBtn.setAttribute('aria-label', 'Show password');
            }

            // Only restore focus if input was focused before, and preserve cursor position
            if (wasFocused) {
                setTimeout(() => {
                    passwordInput.focus();
                    // Restore cursor to where it was
                    const newPos = Math.min(cursorPosition, passwordInput.value.length);
                    passwordInput.setSelectionRange(newPos, newPos);
                }, 0);
            }

            return false;
        };

        // Direct event handlers - no cloning needed
        // Prevent button from receiving focus on mousedown
        togglePasswordBtn.addEventListener('mousedown', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.blur();
        }, true);

        // Main click handler - use capture phase to ensure it fires first
        togglePasswordBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            handlePasswordToggle(e);
            return false;
        }, true);

        // Backup onclick handler (fires after addEventListener)
        togglePasswordBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            handlePasswordToggle(e);
            return false;
        };

        // Keyboard support (Enter/Space)
        togglePasswordBtn.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                handlePasswordToggle(e);
            }
        });
    } else {
        if (!togglePasswordBtn) {
            console.warn('Password toggle button not found in DOM');
        }
        if (!passwordInput) {
            console.warn('Password input not found in DOM');
        }
    }

    // Copy values from visible fields to hidden fields BEFORE form validation
    if (form && emailDisplay && emailReal && passwordInput && passwordReal) {
        // Use 'submit' event with capture phase to run BEFORE validation
        form.addEventListener('submit', function(e) {
            // Copy values BEFORE any validation happens
            emailReal.value    = emailDisplay.value.trim();
            passwordReal.value = passwordInput.value;
        }, true); // Use capture phase to run early
    }

    // === PREVENT PANIC CLICKS + SPINNER ===
    const submitBtn = form?.querySelector('button[type="submit"]');
    if (form && submitBtn) {
        let isSubmitting = false;

        form.addEventListener('submit', function (e) {
            // Ensure values are copied to hidden fields first (in case capture phase didn't work)
            if (emailDisplay && emailReal && passwordInput && passwordReal) {
                emailReal.value    = emailDisplay.value.trim();
                passwordReal.value = passwordInput.value;
            }

            if (isSubmitting) {
                e.preventDefault();
                return;
            }

            // Check if hidden fields have values (they should after copying)
            if (!emailReal.value || !passwordReal.value) {
                e.preventDefault();
                // Show validation errors
                if (!emailReal.value) {
                    emailDisplay.setCustomValidity('The email field is required.');
                } else {
                    emailDisplay.setCustomValidity('');
                }
                if (!passwordReal.value) {
                    passwordInput.setCustomValidity('The password field is required.');
                } else {
                    passwordInput.setCustomValidity('');
                }
                form.reportValidity?.();
                return;
            }

            // Clear any custom validity messages
            emailDisplay.setCustomValidity('');
            passwordInput.setCustomValidity('');

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
