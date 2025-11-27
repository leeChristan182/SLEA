@extends('layouts.app')

@section('title', 'Manage User Accounts')

@section('content')
<div class="container">
    @include('partials.sidebar')

    <main class="main-content">
        <div class="page-header">
            <h1>Manage User Accounts</h1>
        </div>

        {{-- Flash Messages --}}
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Controls Section --}}
        <div class="controls-section">
            <div class="filter-controls">
                <div class="filter-group">
                    <label for="roleFilter">Role</label>
                    <select id="roleFilter" name="role" class="form-select" onchange="applyFilters()">
                        <option value="">All</option>
                        <option value="{{ \App\Models\User::ROLE_ADMIN }}"
                            {{ request('role') === \App\Models\User::ROLE_ADMIN ? 'selected' : '' }}>
                            Admin
                        </option>
                        <option value="{{ \App\Models\User::ROLE_ASSESSOR }}"
                            {{ request('role') === \App\Models\User::ROLE_ASSESSOR ? 'selected' : '' }}>
                            Assessor
                        </option>
                        <option value="{{ \App\Models\User::ROLE_STUDENT }}"
                            {{ request('role') === \App\Models\User::ROLE_STUDENT ? 'selected' : '' }}>
                            Student
                        </option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="statusFilter">Status</label>
                    <select id="statusFilter" name="status" class="form-select" onchange="applyFilters()">
                        <option value="">All</option>
                        <option value="{{ \App\Models\User::STATUS_PENDING }}"
                            {{ request('status') === \App\Models\User::STATUS_PENDING ? 'selected' : '' }}>
                            Pending
                        </option>
                        <option value="{{ \App\Models\User::STATUS_APPROVED }}"
                            {{ request('status') === \App\Models\User::STATUS_APPROVED ? 'selected' : '' }}>
                            Approved
                        </option>
                        <option value="{{ \App\Models\User::STATUS_DISABLED }}"
                            {{ request('status') === \App\Models\User::STATUS_DISABLED ? 'selected' : '' }}>
                            Disabled
                        </option>
                        <option value="{{ \App\Models\User::STATUS_REJECTED }}"
                            {{ request('status') === \App\Models\User::STATUS_REJECTED ? 'selected' : '' }}>
                            Rejected
                        </option>
                    </select>
                </div>
            </div>

            <div class="search-controls">
                <div class="search-group">
                    <input
                        type="text"
                        id="searchInput"
                        name="q"
                        class="form-control"
                        placeholder="Search by name or email"
                        value="{{ request('q') }}"
                    >
                    <button type="button" id="searchBtn" class="btn-search-maroon search-btn-attached" title="Search" onclick="handleSearchClick(event)">
                        <i class="fas fa-search"></i>
                    </button>
                    <button type="button" id="clearBtn" class="btn-clear" title="Clear search" onclick="handleClearClick(event)">
                        Clear
                    </button>
                </div>
            </div>
        </div>

        {{-- Users Table --}}
        <div class="submissions-table-container">
            <table class="table submissions-table">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->full_name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ ucfirst($user->role) }}</td>
                            <td>
                                @php
                                    $badgeClass = match ($user->status) {
                                        \App\Models\User::STATUS_APPROVED => 'approved',
                                        \App\Models\User::STATUS_PENDING => 'pending',
                                        \App\Models\User::STATUS_DISABLED => 'disabled',
                                        \App\Models\User::STATUS_REJECTED => 'rejected',
                                        default => 'pending',
                                    };
                                @endphp
                                <span class="status-badge {{ $badgeClass }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td>{{ $user->created_at?->format('M d, Y h:i A') }}</td>
                            <td>
                                <div class="action-buttons-group">
                                    {{-- Toggle (approved <-> disabled) --}}
                                    <form action="{{ route('admin.manage.toggle', $user) }}"
                                          method="POST"
                                          class="d-inline toggle-form"
                                          data-user-name="{{ $user->full_name }}"
                                          data-user-status="{{ $user->status }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="button" class="btn-toggle" title="Enable / Disable">
                                            <i class="fas {{ $user->status === \App\Models\User::STATUS_DISABLED ? 'fa-user-check' : 'fa-user-slash' }}"></i>
                                        </button>
                                    </form>

                                    {{-- Delete --}}
                                    <form action="{{ route('admin.manage.destroy', $user) }}"
                                          method="POST"
                                          class="d-inline delete-form"
                                          data-user-name="{{ $user->full_name }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn-delete" title="Delete user">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted" style="padding: 40px;">
                                No user accounts found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
            <div class="pagination-container" data-pagination-container>
                <div class="pagination-info">
                    Showing {{ $users->firstItem() ?? 0 }} – {{ $users->lastItem() ?? 0 }}
                    of {{ $users->total() }} entries
                </div>

                <div class="unified-pagination">
                    @if($users->onFirstPage())
                        <button class="btn-nav" disabled>
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                    @else
                        <a href="{{ $users->previousPageUrl() }}" class="btn-nav">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    @endif

                    <span class="pagination-pages">
                        @php
                            $currentPage = $users->currentPage();
                            $lastPage = $users->lastPage();
                            $start = max(1, $currentPage - 2);
                            $end = min($lastPage, $currentPage + 2);
                        @endphp

                        @if($start > 1)
                            <a href="{{ $users->url(1) }}" class="page-btn">1</a>
                            @if($start > 2)
                                <span class="page-btn disabled">...</span>
                            @endif
                        @endif

                        @for($i = $start; $i <= $end; $i++)
                            @if($i == $currentPage)
                                <span class="page-btn active">{{ $i }}</span>
                            @else
                                <a href="{{ $users->url($i) }}" class="page-btn">{{ $i }}</a>
                            @endif
                        @endfor

                        @if($end < $lastPage)
                            @if($end < $lastPage - 1)
                                <span class="page-btn disabled">...</span>
                            @endif
                            <a href="{{ $users->url($lastPage) }}" class="page-btn">{{ $lastPage }}</a>
                        @endif
                    </span>

                    @if($users->hasMorePages())
                        <a href="{{ $users->nextPageUrl() }}" class="btn-nav">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <button class="btn-nav" disabled>
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </main>
</div>

<link rel="stylesheet" href="{{ asset('css/pending-submissions.css') }}">
<style>
    /* Status badges for manage-account page */
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 1px solid;
    }

    .status-badge.pending {
        background-color: #fff3cd;
        color: #856404;
        border-color: #ffc107;
    }

    .status-badge.approved {
        background-color: #d4edda;
        color: #155724;
        border-color: #28a745;
    }

    .status-badge.disabled {
        background-color: #e2e3e5;
        color: #383d41;
        border-color: #6c757d;
    }

    .status-badge.rejected {
        background-color: #f8d7da;
        color: #721c24;
        border-color: #dc3545;
    }

    body.dark-mode .status-badge.pending {
        background-color: #744210;
        color: #f6e05e;
        border-color: #f6e05e;
    }

    body.dark-mode .status-badge.approved {
        background-color: #1e4d2b;
        color: #68d391;
        border-color: #68d391;
    }

    body.dark-mode .status-badge.disabled {
        background-color: #495057;
        color: #f0f0f0;
        border-color: #6c757d;
    }

    body.dark-mode .status-badge.rejected {
        background-color: #742a2a;
        color: #feb2b2;
        border-color: #feb2b2;
    }

    /* Action buttons group */
    .action-buttons-group {
        display: flex;
        gap: 8px;
        justify-content: center;
        align-items: center;
    }

    .btn-toggle,
    .btn-delete {
        width: 35px;
        height: 35px;
        border: none;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        padding: 0;
    }

    .btn-toggle {
        background-color: #ffc107;
        color: #212529;
    }

    .btn-toggle:hover {
        background-color: #e0a800;
        transform: translateY(-1px);
    }

    .btn-delete {
        background-color: #dc3545;
        color: white;
    }

    .btn-delete:hover {
        background-color: #c82333;
        transform: translateY(-1px);
    }

    .btn-toggle i,
    .btn-delete i {
        font-size: 0.9rem;
    }

    body.dark-mode .btn-toggle {
        background-color: #ffc107;
        color: #212529;
    }

    body.dark-mode .btn-toggle:hover {
        background-color: #e0a800;
    }

    body.dark-mode .btn-delete {
        background-color: #dc3545;
    }

    body.dark-mode .btn-delete:hover {
        background-color: #8b0000;
    }

    /* Filter group select width */
    .filter-group select {
        width: 200px;
        max-width: 200px;
        min-width: 150px;
    }
</style>
<script src="{{ asset('js/admin_pagination.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Search and Clear functionality
    function handleSearchClick(event) {
        event.preventDefault();
        const searchInput = document.getElementById('searchInput');
        const roleFilter = document.getElementById('roleFilter');
        const statusFilter = document.getElementById('statusFilter');
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = window.location.pathname;

        if (searchInput.value.trim()) {
            const qInput = document.createElement('input');
            qInput.type = 'hidden';
            qInput.name = 'q';
            qInput.value = searchInput.value.trim();
            form.appendChild(qInput);
        }

        if (roleFilter.value) {
            const roleInput = document.createElement('input');
            roleInput.type = 'hidden';
            roleInput.name = 'role';
            roleInput.value = roleFilter.value;
            form.appendChild(roleInput);
        }

        if (statusFilter.value) {
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = statusFilter.value;
            form.appendChild(statusInput);
        }

        document.body.appendChild(form);
        form.submit();
    }

    function handleClearClick(event) {
        event.preventDefault();
        document.getElementById('searchInput').value = '';
        const roleFilter = document.getElementById('roleFilter');
        const statusFilter = document.getElementById('statusFilter');
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = window.location.pathname;

        if (roleFilter.value) {
            const roleInput = document.createElement('input');
            roleInput.type = 'hidden';
            roleInput.name = 'role';
            roleInput.value = roleFilter.value;
            form.appendChild(roleInput);
        }

        if (statusFilter.value) {
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = statusFilter.value;
            form.appendChild(statusInput);
        }

        document.body.appendChild(form);
        form.submit();
    }

    function applyFilters() {
        const roleFilter = document.getElementById('roleFilter');
        const statusFilter = document.getElementById('statusFilter');
        const searchInput = document.getElementById('searchInput');
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = window.location.pathname;

        if (roleFilter.value) {
            const roleInput = document.createElement('input');
            roleInput.type = 'hidden';
            roleInput.name = 'role';
            roleInput.value = roleFilter.value;
            form.appendChild(roleInput);
        }

        if (statusFilter.value) {
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = statusFilter.value;
            form.appendChild(statusInput);
        }

        if (searchInput.value.trim()) {
            const qInput = document.createElement('input');
            qInput.type = 'hidden';
            qInput.name = 'q';
            qInput.value = searchInput.value.trim();
            form.appendChild(qInput);
        }

        document.body.appendChild(form);
        form.submit();
    }

    // Toggle/Delete confirmation modals
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle button handlers
        document.querySelectorAll('.toggle-form').forEach(function(form) {
            const button = form.querySelector('.btn-toggle');
            const userName = form.getAttribute('data-user-name');
            const userStatus = form.getAttribute('data-user-status');
            const action = userStatus === 'disabled' ? 'enable' : 'disable';

            button.addEventListener('click', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: `${action.charAt(0).toUpperCase() + action.slice(1)} User?`,
                    html: `Are you sure you want to ${action} the account for <strong>${userName}</strong>?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: `Yes, ${action.charAt(0).toUpperCase() + action.slice(1)}`,
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        // Delete button handlers
        document.querySelectorAll('.delete-form').forEach(function(form) {
            const button = form.querySelector('.btn-delete');
            const userName = form.getAttribute('data-user-name');

            button.addEventListener('click', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Delete User?',
                    html: `Are you sure you want to delete <strong>${userName}</strong>? This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endsection
