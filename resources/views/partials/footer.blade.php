<footer id="page-footer" class="mt-auto text-center py-3 small">
    &copy; {{ date('Y') }} University of Southeastern Philippines. All rights reserved.
</footer>

<div class="floating-tools d-md-none">
    <button id="darkModeToggleFloating" class="floating-btn" title="Toggle Dark Mode"><i class="fas fa-moon"></i></button>
    <a href="#" class="floating-btn" title="Send us a message"><i class="fa-solid fa-envelope"></i></a>
</div>

<script>
    // Dark mode toggle for all pages
    const body = document.body;
    const toggleBtn = document.getElementById('darkModeToggle');
    const toggleBtnFloating = document.getElementById('darkModeToggleFloating');

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
    document.addEventListener("DOMContentLoaded", function() {
        const toggleBtn = document.querySelector(".menu-toggle");
        const sidebar = document.querySelector("aside.sidebar");
        const overlay = document.querySelector(".sidebar-overlay");

        if (toggleBtn && sidebar && overlay) {
            toggleBtn.addEventListener("click", function() {
                sidebar.classList.toggle("sidebar-open");
                overlay.classList.toggle("active");
            });

            overlay.addEventListener("click", function() {
                sidebar.classList.remove("sidebar-open");
                overlay.classList.remove("active");
            });
        }
    });
</script>
