// Sidebar toggle (desktop collapse/expand)
document.querySelector('.menu-toggle')?.addEventListener('click', () => {
    document.body.classList.toggle('collapsed');
});

// Dark mode toggle for sidebar
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
}

// Avatar preview
function previewAvatar(e) {
    const avatar = document.getElementById('avatarPreview');
    avatar.src = URL.createObjectURL(e.target.files[0]);
}

// Show/Hide Password
function togglePassword() {
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    const type = newPassword.type === 'password' ? 'text' : 'password';
    newPassword.type = type;
    confirmPassword.type = type;
}

// Password validation
function validatePassword() {
    const p = document.getElementById('newPassword').value;
    document.getElementById('length').className    = p.length >= 8 ? 'valid' : 'invalid';
    document.getElementById('uppercase').className = /[A-Z]/.test(p) ? 'valid' : 'invalid';
    document.getElementById('lowercase').className = /[a-z]/.test(p) ? 'valid' : 'invalid';
    document.getElementById('number').className    = /\d/.test(p) ? 'valid' : 'invalid';
    document.getElementById('special').className   = /[^A-Za-z0-9]/.test(p) ? 'valid' : 'invalid';
}

// Expose functions globally (needed for inline onClick handlers in Blade)
window.toggleDarkMode = toggleDarkMode;
window.previewAvatar = previewAvatar;
window.togglePassword = togglePassword;
window.validatePassword = validatePassword;

// Mobile sidebar slide-in/out toggle
const hamburger = document.querySelector('.menu-toggle');
const overlay = document.querySelector('.sidebar-overlay');

// Open/close sidebar on hamburger click
hamburger?.addEventListener('click', function() {
    document.body.classList.toggle('sidebar-open');
});

// Close sidebar when clicking the overlay
overlay?.addEventListener('click', function() {
    document.body.classList.remove('sidebar-open');
});

// Close sidebar when a menu item is clicked
document.querySelectorAll('.sidebar ul li').forEach(item => {
    item.addEventListener('click', function() {
        document.body.classList.remove('sidebar-open');
    });
});
    // Avatar preview
    function previewAvatar(e) {
        const file = e.target.files?.[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        document.getElementById('avatarPreview').src = url;
    }
    document.getElementById('avatarUpload').addEventListener('change', previewAvatar);

    // Show/hide password
    document.getElementById('showPass').addEventListener('change', (e) => {
        const show = e.target.checked;
        ['password', 'password_confirmation'].forEach(id => {
            const el = document.getElementById(id);
            el.type = show ? 'text' : 'password';
        });
    });

    // Simple checklist validation (front‑end demo)
    const newPass = document.getElementById('password');
    const rules = {
        length: v => v.length >= 8,
        uppercase: v => /[A-Z]/.test(v),
        lowercase: v => /[a-z]/.test(v),
        number: v => /\d/.test(v),
        special: v => /[^A-Za-z0-9]/.test(v),
    };
    newPass.addEventListener('input', () => {
        const v = newPass.value || '';
        document.querySelectorAll('#passwordChecklist li').forEach(li => {
            const ok = rules[li.dataset.rule](v);
            li.classList.toggle('valid', ok);
            li.classList.toggle('invalid', !ok);
        });
    });

    // Leadership filter
    const typeSelect = document.getElementById('leadershipType');
    const tableRows = document.querySelectorAll('#leadershipTable tbody tr');
    typeSelect.addEventListener('change', () => {
        const val = typeSelect.value;
        tableRows.forEach(tr => {
            tr.style.display = !val || tr.dataset.type === val ? '' : 'none';
        });
    });