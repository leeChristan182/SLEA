@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

$user = Auth::user();
$role = $user->role ?? null; // 'admin', 'assessor', 'student' or null

// Default landing route based on role
switch ($role) {
case 'admin':
$routeName = 'admin.profile';
break;
case 'assessor':
$routeName = 'assessor.profile';
break;
case 'student':
$routeName = 'student.profile';
break;
default:
$routeName = 'login.show';
break;
}

$link = Route::has($routeName) ? route($routeName) : route('login.show');
@endphp

<div class="header-container">
    <div class="header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <!-- Logo + SLEA linked to role-based landing -->
            <a href="{{ $link }}" class="d-flex align-items-center gap-2 text-decoration-none">
                <img src="{{ asset('images/osas-logo.png') }}" alt="OSAS Logo" height="60">
                <span class="fs-3 fw-bolder logo-text">SLEA</span>
            </a>

            <div style="width: 1px; height: 40px; background-color: #ccc; margin: 0 0.5rem;"></div>

            <!-- Tagline -->
            <div class="tagline ms-3">
                <span class="gold1">Empowering</span> <span class="maroon1">Leadership.</span><br>
                <span class="maroon1">Recognizing</span> <span class="gold1">Excellence.</span>
            </div>
        </div>

        <div class="header-right d-flex align-items-center gap-3">
            <div class="text-end d-none d-sm-block">
                <small>Having Trouble?</small><br>
                <a href="#">Send us a message</a>
            </div>

            <!-- Dark Mode Toggle -->
            <button id="darkModeToggle" class="dark-toggle-btn" title="Toggle Dark Mode">
                <i class="fas fa-moon"></i>
            </button>

            @if ($user)
            {{-- ✅ Single, canonical logout form using POST /logout --}}
            <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="logout-btn" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="d-none d-md-inline">Logout</span>
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

{{-- SweetAlert2 for logout confirmation --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const logoutForm = document.getElementById('logoutForm');
        if (!logoutForm) return;

        logoutForm.addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Confirm Logout',
                text: 'Are you sure you want to logout?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#a00',
                cancelButtonColor: '#555',
                confirmButtonText: 'Yes, logout',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    logoutForm.submit(); // 👈 actual POST /logout happens here
                }
            });
        });
    });
</script>

<style>
    .logout-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        border: 2px solid #a00;
        border-radius: 8px;
        padding: 6px 12px;
        background: #fff;
        color: #333;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .logout-btn:hover {
        background-color: #a00;
        color: #fff;
        border-color: #800;
    }

    .logout-btn i {
        font-size: 1.1rem;
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .swal2-popup {
        font-family: inherit;
    }

    .swal2-confirm {
        background-color: #a00 !important;
        border: none !important;
        border-radius: 8px !important;
    }

    .swal2-cancel {
        background-color: #ccc !important;
        color: #333 !important;
        border-radius: 8px !important;
    }
</style>