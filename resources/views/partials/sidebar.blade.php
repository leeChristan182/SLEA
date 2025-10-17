@php
use Illuminate\Support\Facades\Auth;
$user = Auth::user();
$role = null;

if ($user) {
if ($user instanceof \App\Models\AdminAccount) {
$role = 'admin';
} elseif ($user instanceof \App\Models\AssessorAccount) {
$role = 'assessor';
} else {
$role = 'student';
}
}
@endphp

{{-- ===== BURGER MENU (always visible on mobile) ===== --}}
<button id="globalSidebarToggle" class="sidebar-toggle-btn" aria-label="Toggle sidebar">
    <i class="fas fa-bars"></i>
</button>

{{-- ===== SIDEBAR ===== --}}
<aside id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="sidebar-logo">
        <h3>SLEA</h3>
    </div>

    <ul>
        {{-- STUDENT MENU --}}
        @if ($role === 'student')
        <li class="{{ request()->routeIs('student.profile') ? 'active' : '' }}">
            <a href="{{ route('student.profile') }}"><i class="fas fa-user"></i><span>Profile</span></a>
        </li>
        <li class="{{ request()->routeIs('student.submit') ? 'active' : '' }}">
            <a href="{{ route('student.submit') }}"><i class="fas fa-tasks"></i><span>Submit</span></a>
        </li>
        <li class="{{ request()->routeIs('student.performance') ? 'active' : '' }}">
            <a href="{{ route('student.performance') }}"><i class="fas fa-chart-line"></i><span>Performance</span></a>
        </li>
        <li class="{{ request()->routeIs('student.history') ? 'active' : '' }}">
            <a href="{{ route('student.history') }}"><i class="fas fa-clock-rotate-left"></i><span>History</span></a>
        </li>
        @endif

        {{-- ASSESSOR MENU --}}
        @if ($role === 'assessor')
        <li class="{{ request()->routeIs('assessor.dashboard') ? 'active' : '' }}">
            <a href="{{ route('assessor.dashboard') }}"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
        </li>
        <li class="{{ request()->routeIs('assessor.profile') ? 'active' : '' }}">
            <a href="{{ route('assessor.profile') }}"><i class="fas fa-user"></i><span>Profile</span></a>
        </li>
        <li><a href="{{ route('assessor.pending-submissions') }}"><i class="fas fa-clock"></i><span>Pending</span></a></li>
        <li><a href="{{ route('assessor.submissions') }}"><i class="fas fa-file-alt"></i><span>Submissions</span></a></li>
        <li><a href="{{ route('assessor.final-review') }}"><i class="fas fa-clipboard-check"></i><span>Final Review</span></a></li>
        @endif

        {{-- ADMIN MENU --}}
        @if ($role === 'admin')
        <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard') }}"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
        </li>
        <li class="{{ request()->routeIs('admin.profile') ? 'active' : '' }}">
            <a href="{{ route('admin.profile') }}"><i class="fas fa-user"></i><span>Profile</span></a>
        </li>
        <li><a href="{{ route('admin.manage') }}"><i class="fas fa-users-cog"></i><span>Manage Accounts</span></a></li>
        <li><a href="{{ route('admin.approve-reject') }}"><i class="fas fa-user-check"></i><span>Approve/Reject</span></a></li>
        <li><a href="{{ route('admin.submission-oversight') }}"><i class="fas fa-file-alt"></i><span>Submissions</span></a></li>
        <li><a href="{{ route('admin.final-review') }}"><i class="fas fa-clipboard-check"></i><span>Final Review</span></a></li>
        <li><a href="{{ route('admin.award-report') }}"><i class="fas fa-trophy"></i><span>Award Report</span></a></li>
        <li><a href="{{ route('admin.system-monitoring') }}"><i class="fas fa-server"></i><span>System Monitoring</span></a></li>
        @endif

        {{-- LOGOUT --}}
        @if ($user)
        <li class="logout-item">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i><span>Logout</span>
                </button>
            </form>
        </li>
        @endif
    </ul>
</aside>

{{-- ===== SIDEBAR TOGGLE SCRIPT ===== --}}
<script>
    document.getElementById('globalSidebarToggle').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('active');
    });
</script>