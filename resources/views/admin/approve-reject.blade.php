@extends('layouts.app')

@section('title', 'Student Account Approval')

@section('content')
<div class="container">
    @include('partials.sidebar')

    <main class="main-content">
        <div class="page-header">
            <h1>Student Account Approval</h1>
        </div>

        {{-- Alerts --}}
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
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

        {{-- Controls Section --}}
        <div class="controls-section">
            <div class="search-controls" style="flex: 0 1 auto; max-width: 500px;">
                <div class="search-group">
                    <input
                        type="text"
                        id="searchInput"
                        name="q"
                        class="form-control"
                        value="{{ request('q') }}"
                        style="max-width: 300px;"
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

        {{-- Table --}}
        <div class="submissions-table-container">
            <table class="table submissions-table">
                <thead>
                    <tr>
                        <th>Student No.</th>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Program</th>
                        <th>Year Level</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        @php
                            $acad = $student->studentAcademic;
                        @endphp
                        <tr>
                            <td>{{ $acad->student_number ?? '—' }}</td>
                            <td>
                                {{ $student->last_name }},
                                {{ $student->first_name }}
                                {{ $student->middle_name }}
                            </td>
                            <td>{{ $student->email }}</td>
                            <td>{{ $acad->program->name ?? '—' }}</td>
                            <td>{{ $acad->year_level ?? '—' }}</td>
                            <td>
                                <div class="action-buttons-group">
                                    <form action="{{ route('admin.approve', $student) }}"
                                          method="POST"
                                          class="d-inline approve-form"
                                          data-student-name="{{ $student->first_name }} {{ $student->last_name }}">
                                        @csrf
                                        <button type="button" class="btn-approve" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.reject', $student) }}"
                                          method="POST"
                                          class="d-inline reject-form"
                                          data-student-name="{{ $student->first_name }} {{ $student->last_name }}">
                                        @csrf
                                        <button type="button" class="btn-reject" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted" style="padding: 40px;">
                                No pending student accounts found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($students->hasPages())
            <div class="pagination-container" data-pagination-container>
                <div class="pagination-info">
                    Showing {{ $students->firstItem() ?? 0 }} – {{ $students->lastItem() ?? 0 }}
                    of {{ $students->total() }} entries
                </div>

                <div class="unified-pagination">
                    @if($students->onFirstPage())
                        <button class="btn-nav" disabled>
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                    @else
                        <a href="{{ $students->previousPageUrl() }}" class="btn-nav">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    @endif

                    <span class="pagination-pages">
                        @php
                            $currentPage = $students->currentPage();
                            $lastPage = $students->lastPage();
                            $start = max(1, $currentPage - 2);
                            $end = min($lastPage, $currentPage + 2);
                        @endphp

                        @if($start > 1)
                            <a href="{{ $students->url(1) }}" class="page-btn">1</a>
                            @if($start > 2)
                                <span class="page-btn disabled">...</span>
                            @endif
                        @endif

                        @for($i = $start; $i <= $end; $i++)
                            @if($i == $currentPage)
                                <span class="page-btn active">{{ $i }}</span>
                            @else
                                <a href="{{ $students->url($i) }}" class="page-btn">{{ $i }}</a>
                            @endif
                        @endfor

                        @if($end < $lastPage)
                            @if($end < $lastPage - 1)
                                <span class="page-btn disabled">...</span>
                            @endif
                            <a href="{{ $students->url($lastPage) }}" class="page-btn">{{ $lastPage }}</a>
                        @endif
                    </span>

                    @if($students->hasMorePages())
                        <a href="{{ $students->nextPageUrl() }}" class="btn-nav">
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
    /* Status badges for approve-reject page */
    .status-badge.pending {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffc107;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-badge.approved {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #28a745;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-badge.rejected {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #dc3545;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
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

    .btn-approve,
    .btn-reject {
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

    .btn-approve {
        background-color: #28a745;
        color: white;
    }

    .btn-approve:hover {
        background-color: #218838;
        transform: translateY(-1px);
    }

    .btn-reject {
        background-color: #dc3545;
        color: white;
    }

    .btn-reject:hover {
        background-color: #c82333;
        transform: translateY(-1px);
    }

    .btn-approve i,
    .btn-reject i {
        font-size: 0.9rem;
    }

    .no-action-text {
        color: #6c757d;
        font-style: italic;
        font-size: 0.85rem;
    }

    body.dark-mode .no-action-text {
        color: #999;
    }

    body.dark-mode .btn-approve {
        background-color: #28a745;
    }

    body.dark-mode .btn-approve:hover {
        background-color: #2d5a2d;
    }

    body.dark-mode .btn-reject {
        background-color: #dc3545;
    }

    body.dark-mode .btn-reject:hover {
        background-color: #8b0000;
    }

    /* Controls section - search only */
    .controls-section {
        justify-content: flex-end;
    }
</style>
<script src="{{ asset('js/admin_pagination.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Search and Clear functionality
    function handleSearchClick(event) {
        event.preventDefault();
        const searchInput = document.getElementById('searchInput');
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

        document.body.appendChild(form);
        form.submit();
    }

    function handleClearClick(event) {
        event.preventDefault();
        document.getElementById('searchInput').value = '';
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = window.location.pathname;
        document.body.appendChild(form);
        form.submit();
    }

    // Approve/Reject confirmation modals
    document.addEventListener('DOMContentLoaded', function() {
        // Approve button handlers
        document.querySelectorAll('.approve-form').forEach(function(form) {
            const button = form.querySelector('.btn-approve');
            const studentName = form.getAttribute('data-student-name');

            button.addEventListener('click', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Approve Student Account?',
                    html: `Are you sure you want to approve the account for <strong>${studentName}</strong>?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Approve',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        // Reject button handlers
        document.querySelectorAll('.reject-form').forEach(function(form) {
            const button = form.querySelector('.btn-reject');
            const studentName = form.getAttribute('data-student-name');

            button.addEventListener('click', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Reject Student Account?',
                    html: `Are you sure you want to reject the account for <strong>${studentName}</strong>?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Reject',
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
