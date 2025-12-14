@extends('layouts.app')

@section('title', 'Account Approval')

@section('content')
<div class="container">
    @include('partials.sidebar')

    <main class="main-content">
        <div class="page-header">
            <h1>Approve / Reject Accounts</h1>
            <p class="text-muted" style="margin-top:6px;">
                Showing <strong>Pending</strong> users with <strong>Unassigned</strong> role.
                Approving requires assigning a role.
                <br>
                <small><strong>Note:</strong> Approved students will be <strong>limited</strong> until Initial Validation (Academic + COR + Leadership).</small>
            </p>
        </div>

        {{-- Flash Messages --}}
        @if(session('status'))
            <div class="alert alert-success" id="successAlert">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Search --}}
        <div class="controls-section">
            <div class="search-controls">
                <div class="search-group">
                    <input
                        type="text"
                        id="searchInput"
                        name="q"
                        class="form-control"
                        value="{{ $search ?? request('q') }}"
                        placeholder="Search by Email / Name / User Code"
                    >
                    <button
                        type="button"
                        id="searchBtn"
                        class="btn-search-maroon search-btn-attached"
                        title="Search"
                        onclick="handleSearchClick(event)"
                    >
                        <i class="fas fa-search"></i>
                    </button>

                    <button
                        type="button"
                        id="clearBtn"
                        class="btn-clear"
                        title="Clear search"
                        onclick="handleClearClick(event)"
                    >
                        Clear
                    </button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="submissions-table-container">
            <table class="table submissions-table">
                <thead>
                    <tr>
                        <th>User Code</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Registered At</th>
                        <th style="min-width: 230px;">Approve (Assign Role)</th>
                        <th>Reject</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($users as $user)
                        @php
                            $fullName = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);
                        @endphp

                        <tr>
                            <td>
                                @if(!empty($user->user_code))
                                    {{ $user->user_code }}
                                @else
                                    <span class="text-muted">— <small>(Generated on approval)</small></span>
                                @endif
                            </td>

                            <td>{{ $fullName ?: '—' }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ optional($user->created_at)->format('M d, Y h:i A') ?? '—' }}</td>

                            {{-- Approve form (role required) --}}
                            <td>
                                <form
                                    action="{{ route('admin.approve', $user->id) }}"
                                    method="POST"
                                    class="approve-form d-inline"
                                    data-user-name="{{ $fullName }}"
                                >
                                    @csrf

                                    <div class="approve-inline">
                                        <select name="role" class="form-control role-select" required>
                                            <option value="" disabled selected>Select role</option>
                                            <option value="student">Student</option>
                                            <option value="assessor">Assessor</option>
                                        </select>

                                        <button type="button" class="btn-approve" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </div>

                                    <div class="hint-text">
                                        <small class="text-muted">
                                            Student → Limited until Initial Validation
                                        </small>
                                    </div>
                                </form>
                            </td>

                            {{-- Reject form (optional reason) --}}
                            <td>
                                <form
                                    action="{{ route('admin.reject', $user->id) }}"
                                    method="POST"
                                    class="reject-form d-inline"
                                    data-user-name="{{ $fullName }}"
                                >
                                    @csrf
                                    <input type="hidden" name="rejection_reason" value="">

                                    <button type="button" class="btn-reject" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted" style="padding: 40px;">
                                No pending accounts found.
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
<script src="{{ asset('js/admin_pagination.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .approve-inline {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }
    .role-select { min-width: 160px; }
    .hint-text { margin-top: 4px; }

    .btn-approve, .btn-reject {
        width: 35px; height: 35px;
        border: none; border-radius: 6px;
        display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.2s ease; padding: 0;
    }
    .btn-approve { background-color: #28a745; color: #fff; }
    .btn-approve:hover { background-color: #218838; transform: translateY(-1px); }
    .btn-reject { background-color: #dc3545; color: #fff; }
    .btn-reject:hover { background-color: #c82333; transform: translateY(-1px); }
</style>

<script>
    function handleSearchClick(event) {
        event.preventDefault();
        const searchInput = document.getElementById('searchInput');

        const form = document.createElement('form');
        form.method = 'GET';
        form.action = window.location.pathname;

        if (searchInput && searchInput.value.trim()) {
            const qInput = document.createElement('input');
            qInput.type = 'hidden';
            qInput.name = 'q';
            qInput.value = searchInput.value.trim();
            form.appendChild(qInput);
        }

        document.body.appendChild(form);
        form.submit();
    }

    function handleClearClick(event) {
        event.preventDefault();
        const searchInput = document.getElementById('searchInput');
        if (searchInput) searchInput.value = '';

        const form = document.createElement('form');
        form.method = 'GET';
        form.action = window.location.pathname;
        document.body.appendChild(form);
        form.submit();
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Approve handlers
        document.querySelectorAll('.approve-form').forEach(function (form) {
            const button = form.querySelector('.btn-approve');
            const roleSelect = form.querySelector('.role-select');
            const userName = form.getAttribute('data-user-name') || 'this user';

            button.addEventListener('click', function (e) {
                e.preventDefault();

                const role = roleSelect ? roleSelect.value : '';
                if (!role) {
                    Swal.fire({
                        title: 'Role required',
                        text: 'Please select a role before approving.',
                        icon: 'info'
                    });
                    return;
                }

                const note = role === 'student'
                    ? '<br><small>Student accounts will be limited until Initial Validation.</small>'
                    : '';

                Swal.fire({
                    title: 'Approve Account?',
                    html: `Approve <strong>${userName}</strong> as <strong>${role.toUpperCase()}</strong>?${note}`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Approve',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });

        // Reject handlers
        document.querySelectorAll('.reject-form').forEach(function (form) {
            const button = form.querySelector('.btn-reject');
            const userName = form.getAttribute('data-user-name') || 'this user';
            const reasonInput = form.querySelector('input[name="rejection_reason"]');

            button.addEventListener('click', function (e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Reject Account?',
                    html: `Reject <strong>${userName}</strong>? You may optionally provide a reason.`,
                    icon: 'warning',
                    input: 'textarea',
                    inputPlaceholder: 'Optional rejection reason (max 500 chars)…',
                    inputAttributes: { maxlength: 500 },
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Reject',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (reasonInput) {
                            reasonInput.value = (result.value || '').toString().slice(0, 500);
                        }
                        form.submit();
                    }
                });
            });
        });

        // Auto-fade success alert
        const successAlert = document.getElementById('successAlert');
        if (successAlert) {
            setTimeout(function () {
                successAlert.style.transition = 'opacity 0.5s ease-out';
                successAlert.style.opacity = '0';
                setTimeout(function () { successAlert.remove(); }, 500);
            }, 2500);
        }
    });
</script>
@endsection
