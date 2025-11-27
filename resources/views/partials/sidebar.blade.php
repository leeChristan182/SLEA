@php
use Illuminate\Support\Facades\Auth;

/** Current user + role (single users table) */
$user = Auth::user();
$role = $user?->role; // 'admin' | 'assessor' | 'student'
@endphp

<!-- Overlay for mobile only -->
<div class="sidebar-overlay"></div>

<!-- Burger Menu (mobile only) -->
<button id="mobileSidebarToggle" class="mobile-sidebar-toggle" aria-label="Toggle mobile sidebar">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar -->
<aside id="sidebar" class="sidebar">
    <div class="menu-profile">
        <div class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </div>

    </div>

    <ul>
        {{-- ===================== STUDENT MENU ===================== --}}
        @if ($role === 'student')
        <li class="{{ request()->routeIs('student.profile') ? 'active' : '' }}">
            <a href="{{ route('student.profile') }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;">
                <i class="fas fa-user"></i><span>Profile</span>
            </a>
        </li>
        <li class="{{ request()->routeIs('student.submit') ? 'active' : '' }}">
            <a href="{{ route('student.submit') }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;">
                <i class="fas fa-tasks"></i><span>Submit</span>
            </a>
        </li>
        <li class="{{ request()->routeIs('student.performance') ? 'active' : '' }}">
            <a href="{{ route('student.performance') }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;">
                <i class="fas fa-chart-line"></i><span>Performance</span>
            </a>
        </li>
        <li class="{{ request()->routeIs('student.history') ? 'active' : '' }}">
            <a href="{{ route('student.history') }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;">
                <i class="fas fa-clock-rotate-left"></i><span>History</span>
            </a>
        </li>
        {{-- Optional: criteria page if you keep it --}}
        {{-- <li class="{{ request()->routeIs('student.criteria') ? 'active' : '' }}">
        <a href="{{ route('student.criteria') }}"><i class="fas fa-list-check"></i><span>Criteria</span></a>
        </li> --}}
        @endif

        {{-- ===================== ASSESSOR MENU ===================== --}}
        @if ($role === 'assessor')
        <li class="{{ request()->routeIs('assessor.profile') ? 'active' : '' }}">
            <a href="{{ route('assessor.profile') }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;">
                <i class="fas fa-user"></i><span>Profile</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('assessor.submissions.pending-submissions') ? 'active' : '' }}">
            <a href="{{ route('assessor.submissions.pending-submissions') }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;">
                <i class="fas fa-clock"></i><span>Pending</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('assessor.students.submissions') ? 'active' : '' }}">
            <a href="{{ route('assessor.students.submissions') }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;">
                <i class="fas fa-file-alt"></i><span>Submissions</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('assessor.final-review*') ? 'active' : '' }}">
            <a href="{{ route('assessor.final-review.index') }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;">
                <i class="fas fa-clipboard-check"></i><span>Final Review</span>
            </a>
        </li>
        @endif



        {{-- ===================== ADMIN MENU ===================== --}}
        @if ($role === 'admin')
        <li class="{{ request()->routeIs('admin.profile') ? 'active' : '' }}">
            <a href="{{ route('admin.profile') }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;">
                <i class="fas fa-user"></i><span>Profile</span>
            </a>
        </li>

        <li class="has-submenu {{ request()->routeIs('admin.create_user') || request()->routeIs('admin.approve-reject') || request()->routeIs('admin.manage-account') ? 'open' : '' }}">
            <span class="submenu-title" style="display:flex;align-items:center;gap:10px;cursor:default;">
                <i class="fas fa-users-cog"></i><span>User Account Management</span>
            </span>
            <ul class="submenu">
                <li class="{{ request()->routeIs('admin.create_user') ? 'active' : '' }}">
                    <a href="{{ route('admin.create_user') }}">Create Assessor's Account</a>
                </li>
                <li class="{{ request()->routeIs('admin.approve-reject') ? 'active' : '' }}">
                    <a href="{{ route('admin.approve-reject') }}">Approve/Reject Account</a>
                </li>
                <li class="{{ request()->routeIs('admin.manage-account') ? 'active' : '' }}">
                    <a href="{{ route('admin.manage-account') }}">Manage Account</a>
                </li>
                {{-- Revalidation queue we added --}}
                <li class="{{ request()->routeIs('admin.revalidation') ? 'active' : '' }}">
                    <a href="{{ route('admin.revalidation') }}">Academic Revalidation</a>
                </li>
            </ul>
        </li>

        <li class="{{ request()->routeIs('admin.rubrics.index') ? 'active' : '' }}">
            <a href="{{ route('admin.rubrics.index') }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;">
                <i class="fas fa-tasks"></i><span>Scoring Rubric Configuration</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('admin.organizations.index') ? 'active' : '' }}">
            <a href="{{ route('admin.organizations.index') }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;">
                <i class="fas fa-building"></i><span>Organization Management</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('admin.final-review') ? 'active' : '' }}">
            <a href="{{ route('admin.final-review') }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;">
                <i class="fas fa-clipboard-check"></i><span>Final Review</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('admin.award-report') ? 'active' : '' }}">
            <a href="{{ route('admin.award-report') }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;">
                <i class="fas fa-trophy"></i><span>Award Report</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('admin.system-logs.*') ? 'active' : '' }}">
            <a href="{{ route('admin.system-logs.index') }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;">
                <i class="fas fa-server"></i><span>System Monitoring and Logs</span>
            </a>
        </li>
        @endif
    </ul>
</aside>

{{-- Mobile CSS --}}
<style>
    /* BURGER (mobile only) */
    .mobile-sidebar-toggle {
        position: fixed;
        left: 20px;
        top: 15px;
        width: 40px;
        height: 40px;
        background: #7b0000;
        color: #fff;
        border: none;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 1100;
    }

    @media (max-width: 768px) {
        .mobile-sidebar-toggle {
            display: flex;
        }

        .sidebar {
            left: -260px;
            top: 0;
            height: 100vh;
        }

        .sidebar.active {
            left: 0;
        }

        .main-content {
            margin-left: 0 !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const toggleBtn = document.getElementById('mobileSidebarToggle');
        const menuToggle = document.getElementById('menuToggle');
        const submenuItems = document.querySelectorAll('.has-submenu');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                if (window.innerWidth <= 768) {
                    overlay.classList.toggle('active');
                    document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
                }
            });
        }

        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            });
        }

        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                if (window.innerWidth > 768) document.body.classList.toggle('collapsed');
            });
        }

        submenuItems.forEach(item => {
            const title = item.querySelector('.submenu-title');
            title?.addEventListener('click', () => item.classList.toggle('open'));
        });
    });
</script>