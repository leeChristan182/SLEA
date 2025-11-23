@auth
<div class="dropdown">
    <button class="btn btn-outline-light dropdown-toggle" type="button" id="userDropdown"
        data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-user me-1"></i>
        {{ auth()->user()->name }}
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
        <li>
            <h6 class="dropdown-header">
                <i class="fas fa-user-circle me-1"></i>
                {{ ucfirst(auth()->user()->user_role) }}
            </h6>
        </li>
        <li>
            <hr class="dropdown-divider">
        </li>
        <li>
            <a class="dropdown-item" href="#" id="logout-btn">
                <i class="fas fa-sign-out-alt me-2"></i>
                Logout
            </a>
        </li>
    </ul>
</div>

<!-- Hidden Laravel logout form (canonical) -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
</form>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    Confirm Logout
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to logout?</p>
                <p class="text-muted small">You will need to login again to access your account.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirm-logout">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    Logout
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const logoutBtn = document.getElementById('logout-btn');
        const logoutModalEl = document.getElementById('logoutModal');
        const confirmLogoutBtn = document.getElementById('confirm-logout');
        const logoutForm = document.getElementById('logout-form');

        if (!logoutModalEl) return;
        const logoutModal = new bootstrap.Modal(logoutModalEl);

        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                logoutModal.show();
            });
        }

        if (confirmLogoutBtn) {
            confirmLogoutBtn.addEventListener('click', function() {
                if (!logoutForm) {
                    console.error('logout-form not found');
                    window.location.href = '{{ route('
                    login.show ') }}';
                    return;
                }

                // Optional loading state
                confirmLogoutBtn.disabled = true;
                confirmLogoutBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Logging out...';

                logoutForm.submit(); // 👈 canonical POST /logout with CSRF
            });
        }
    });
</script>
@endauth