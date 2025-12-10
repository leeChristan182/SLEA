// Dark Mode Toggle for Registration Page
(function() {
    'use strict';
    
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDarkMode);
    } else {
        initDarkMode();
    }
    
    function initDarkMode() {
        const body = document.body;
        const registerContainer = document.querySelector('.register-container');
        const headerContainer = document.querySelector('.header-container');
        const toggleBtn = document.getElementById('darkModeToggle');

        if (!toggleBtn) {
            console.warn('Dark mode toggle button not found');
            return;
        }

        // Function to apply theme
        function applyTheme(mode) {
            const isDark = mode === 'dark';
            
            // Toggle dark-mode class on body
            if (isDark) {
                body.classList.add('dark-mode');
            } else {
                body.classList.remove('dark-mode');
            }
            
            // Also apply to register-container and header-container
            if (registerContainer) {
                if (isDark) {
                    registerContainer.classList.add('dark-mode');
                } else {
                    registerContainer.classList.remove('dark-mode');
                }
            }
            
            if (headerContainer) {
                if (isDark) {
                    headerContainer.classList.add('dark-mode');
                } else {
                    headerContainer.classList.remove('dark-mode');
                }
            }
            
            // Update icon
            const icon = toggleBtn.querySelector('i');
            if (icon) {
                if (isDark) {
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                } else {
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                }
            }
            
            // Save to localStorage
            try {
                localStorage.setItem('theme', mode);
            } catch (e) {
                console.warn('localStorage not available:', e);
            }
        }

        // Function to toggle theme
        function toggleTheme(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            const currentMode = body.classList.contains('dark-mode') ? 'dark' : 'light';
            const newMode = currentMode === 'dark' ? 'light' : 'dark';
            applyTheme(newMode);
        }

        // Apply saved theme on load
        try {
            const savedTheme = localStorage.getItem('theme') || 'light';
            applyTheme(savedTheme);
        } catch (e) {
            console.warn('localStorage not available:', e);
            applyTheme('light');
        }

        // Add click event listener to toggle button
        toggleBtn.addEventListener('click', toggleTheme);
        
        // Also handle mousedown for better responsiveness
        toggleBtn.addEventListener('mousedown', function(e) {
            e.preventDefault();
        });
    }
})();

