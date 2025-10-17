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
    console.log('validatePassword function called!'); // Debug log
    
    // Get password value
    const passwordField = document.getElementById('newPassword');
    if (!passwordField) {
        console.error('Password field not found!');
        return;
    }
    
    const password = passwordField.value;
    console.log('Validating password:', password); // Debug log
    
    // Check requirements
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /\d/.test(password),
        special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
    };
    
    console.log('Requirements:', requirements); // Debug log
    
    let allRequirementsMet = true;
    
    // Update each requirement visually
    Object.keys(requirements).forEach(req => {
        const element = document.getElementById(req);
        console.log('Processing requirement:', req, 'Element:', element); // Debug log
        
        if (element) {
            // Remove all classes first
            element.classList.remove('valid', 'invalid');
            
            if (requirements[req]) {
                element.classList.add('valid');
                element.style.color = '#28a745'; // Force green color
                if (element.querySelector('i')) {
                    element.querySelector('i').style.color = '#28a745'; // Force icon green
                }
                console.log('Set', req, 'to valid'); // Debug log
            } else {
                element.classList.add('invalid');
                element.style.color = '#dc3545'; // Force red color
                if (element.querySelector('i')) {
                    element.querySelector('i').style.color = '#dc3545'; // Force icon red
                }
                console.log('Set', req, 'to invalid'); // Debug log
                allRequirementsMet = false;
            }
        } else {
            console.error('Element not found for requirement:', req); // Debug log
        }
    });
    
    console.log('All requirements met:', allRequirementsMet); // Debug log
    
    // Enable/disable the Change Password button based on requirements
    const changePasswordBtn = document.querySelector('#passwordEditMode .btn-save');
    console.log('Change password button:', changePasswordBtn); // Debug log
    
    if (changePasswordBtn) {
        if (allRequirementsMet && password.length > 0) {
            changePasswordBtn.disabled = false;
            changePasswordBtn.style.opacity = '1';
            changePasswordBtn.style.cursor = 'pointer';
            changePasswordBtn.style.backgroundColor = '#7b0000';
            console.log('Button enabled'); // Debug log
        } else {
            changePasswordBtn.disabled = true;
            changePasswordBtn.style.opacity = '0.5';
            changePasswordBtn.style.cursor = 'not-allowed';
            changePasswordBtn.style.backgroundColor = '#ccc';
            console.log('Button disabled'); // Debug log
        }
    } else {
        console.error('Change password button not found'); // Debug log
    }
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

// Logout function with custom confirmation modal
function logout() {
    showLogoutConfirmation();
}

function showLogoutConfirmation() {
    // Create confirmation modal overlay
    const overlay = document.createElement('div');
    overlay.id = 'logoutConfirmationOverlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10000;
        font-family: 'Quicksand', sans-serif;
        backdrop-filter: blur(3px);
    `;
    
    const modal = document.createElement('div');
    modal.style.cssText = `
        background: white;
        padding: 0;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        max-width: 450px;
        width: 90%;
        overflow: hidden;
        transform: scale(0.9);
        transition: transform 0.3s ease;
    `;
    
    modal.innerHTML = `
        <div style="background: linear-gradient(135deg, #dc3545, #c82333); padding: 25px; color: white;">
            <div style="font-size: 48px; margin-bottom: 15px;">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <h3 style="margin: 0; font-size: 22px; font-weight: 600;">Confirm Logout</h3>
        </div>
        <div style="padding: 30px;">
            <p style="color: #555; margin-bottom: 25px; font-size: 16px; line-height: 1.5;">
                Are you sure you want to log out?<br>
                <small style="color: #888; font-size: 14px;">You will be redirected to the login page.</small>
            </p>
            <div style="display: flex; gap: 12px; justify-content: center;">
                <button id="cancelLogout" style="
                    background: #6c757d;
                    color: white;
                    border: none;
                    padding: 12px 24px;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    min-width: 100px;
                " onmouseover="this.style.background='#5a6268'" onmouseout="this.style.background='#6c757d'">
                    Cancel
                </button>
                <button id="confirmLogout" style="
                    background: #dc3545;
                    color: white;
                    border: none;
                    padding: 12px 24px;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    min-width: 100px;
                " onmouseover="this.style.background='#c82333'" onmouseout="this.style.background='#dc3545'">
                    Log Out
                </button>
            </div>
        </div>
    `;
    
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // Animate modal appearance
    setTimeout(() => {
        modal.style.transform = 'scale(1)';
    }, 10);
    
    // Add event listeners
    document.getElementById('cancelLogout').addEventListener('click', () => {
        closeLogoutConfirmation();
    });
    
    document.getElementById('confirmLogout').addEventListener('click', () => {
        closeLogoutConfirmation();
        showLogoutSuccess();
        
        // Auto logout after showing success message
        setTimeout(() => {
            console.log('Starting logout process...');
            
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            console.log('CSRF Token found:', csrfToken);
            
            if (csrfToken) {
                const token = csrfToken.getAttribute('content');
                console.log('CSRF token value:', token);
                
                // Create a form to submit POST request to logout
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/logout';
                form.style.display = 'none';
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = token;
                form.appendChild(csrfInput);
                
                document.body.appendChild(form);
                console.log('Form created and submitted');
                form.submit();
            } else {
                console.error('CSRF token not found!');
                // Fallback without CSRF token
                window.location.href = '/';
            }
        }, 2000);
    });
    
    // Close on overlay click
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            closeLogoutConfirmation();
        }
    });
    
    // Close on Escape key
    const handleEscape = (e) => {
        if (e.key === 'Escape') {
            closeLogoutConfirmation();
            document.removeEventListener('keydown', handleEscape);
        }
    };
    document.addEventListener('keydown', handleEscape);
}

function closeLogoutConfirmation() {
    const overlay = document.getElementById('logoutConfirmationOverlay');
    if (overlay) {
        const modal = overlay.querySelector('div');
        modal.style.transform = 'scale(0.9)';
        setTimeout(() => {
            if (overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
        }, 300);
    }
}

function showLogoutSuccess() {
    // Create success message overlay
    const overlay = document.createElement('div');
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10000;
        font-family: 'Quicksand', sans-serif;
    `;
    
    const messageBox = document.createElement('div');
    messageBox.style.cssText = `
        background: white;
        padding: 30px 40px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        max-width: 400px;
        width: 90%;
    `;
    
    messageBox.innerHTML = `
        <div style="color: #28a745; font-size: 48px; margin-bottom: 15px;">
            <i class="fas fa-check-circle"></i>
        </div>
        <h3 style="color: #333; margin-bottom: 10px; font-size: 20px;">Logout Successful!</h3>
        <p style="color: #666; margin-bottom: 20px; font-size: 14px;">You have been successfully logged out. Redirecting to login page...</p>
        <div style="display: flex; justify-content: center; align-items: center; gap: 10px;">
            <div style="width: 20px; height: 20px; border: 2px solid #28a745; border-top: 2px solid transparent; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <span style="color: #666; font-size: 12px;">Please wait...</span>
        </div>
    `;
    
    // Add CSS animation for spinner
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
    
    overlay.appendChild(messageBox);
    document.body.appendChild(overlay);
    
    // Remove overlay after 2 seconds (just before redirect)
    setTimeout(() => {
        if (overlay.parentNode) {
            overlay.parentNode.removeChild(overlay);
        }
        if (style.parentNode) {
            style.parentNode.removeChild(style);
        }
    }, 2000);
}

// Expose logout functions globally
window.logout = logout;
window.showLogoutConfirmation = showLogoutConfirmation;
window.closeLogoutConfirmation = closeLogoutConfirmation;
window.showLogoutSuccess = showLogoutSuccess;