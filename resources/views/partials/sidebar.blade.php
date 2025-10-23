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
        @endif

        {{-- ===================== ASSESSOR MENU ===================== --}}
        @if ($role === 'assessor')
        <li class="{{ request()->routeIs('assessor.dashboard') ? 'active' : '' }}">
            <a href="{{ route('assessor.dashboard') }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;">
                <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
            </a>
        </li>
        <li class="{{ request()->routeIs('assessor.profile') ? 'active' : '' }}">
            <a href="{{ route('assessor.profile') }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;">
                <i class="fas fa-user"></i><span>Profile</span>
            </a>
        </li>
        <li class="{{ request()->routeIs('assessor.pending-submissions') ? 'active' : '' }}">
            <a href="{{ route('assessor.pending-submissions') }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;">
                <i class="fas fa-clock"></i><span>Pending</span>
            </a>
        </li>
        <li class="{{ request()->routeIs('assessor.submissions') ? 'active' : '' }}">
            <a href="{{ route('assessor.submissions') }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;">
                <i class="fas fa-file-alt"></i><span>Submissions</span>
            </a>
        </li>
        <li class="{{ request()->routeIs('assessor.final-review') ? 'active' : '' }}">
            <a href="{{ route('assessor.final-review') }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;">
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

        <li class="has-submenu">
            <div style="display:flex;align-items:center;gap:10px;">
                <i class="fas fa-users-cog"></i><span>User Account Management</span>
            </div>
            <ul class="submenu">
                <li><a href="{{ route('admin.create_assessor') }}">Create Assessor's Account</a></li>
                <li><a href="{{ route('admin.approve-reject') }}">Approve/Reject Account</a></li>
                <li><a href="{{ route('admin.manage') }}">Manage Account</a></li>
            </ul>
        </li>
        <li class="{{ request()->routeIs('admin.rubrics.index') ? 'active' : '' }}">
            <a href="{{ route('admin.rubrics.index') }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;">
                <i class="fas fa-tasks"></i><span>Scoring Rubric Configuration</span>
            </a>
        </li>
        <li class="{{ request()->routeIs('admin.submission-oversight') ? 'active' : '' }}">
            <a href="{{ route('admin.submission-oversight') }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;">
                <i class="fas fa-file-alt"></i><span>Submission Oversight</span>
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

        <li class="{{ request()->routeIs('admin.system-monitoring') ? 'active' : '' }}">
            <a href="{{ route('admin.system-monitoring') }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;">
                <i class="fas fa-server"></i><span>System Monitoring and Logs</span>
            </a>
        </li>
        @endif


    </ul>
</aside>

{{-- ===== SIDEBAR TOGGLE SCRIPT ===== --}}
<script>
    document.getElementById('globalSidebarToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('active');
    });
</script>