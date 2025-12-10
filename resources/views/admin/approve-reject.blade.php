@extends('layouts.app')

@section('title', 'Pending Account Approval')

@section('content')
    <div class="container">
        @include('partials.sidebar')

        <main class="main-content">
            <div class="page-header">
                <h1>Pending Account Approval</h1>
                <p class="text-muted">Review and assign roles to newly registered users</p>
            </div>

            {{-- Alerts --}}
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

            {{-- Search Section --}}
            <div class="controls-section">
                <div class="search-controls">
                    <div class="search-group">
                        <form method="GET" action="{{ route('admin.approve-reject') }}" class="d-flex gap-2 align-items-center" style="width: 100%;">
                            <input type="text" id="searchInput" name="q" class="form-control" value="{{ request('q') }}"
                                placeholder="Search by User ID, Name, or Email">
                            <button type="submit" id="searchBtn" class="btn-search-maroon search-btn-attached" title="Search">
                                <i class="fas fa-search"></i>
                            </button>
                            @if(request('q'))
                                <a href="{{ route('admin.approve-reject') }}" class="btn-clear" title="Clear search">
                                    Clear
                                </a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="submissions-table-container">
                <table class="table submissions-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Date Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->user_code ?? '—' }}</td>
                                <td>
                                    {{ $user->last_name }},
                                    {{ $user->first_name }}
                                    {{ $user->middle_name ? ' ' . $user->middle_name : '' }}
                                </td>
                                <td class="email-cell">{{ $user->email }}</td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="action-buttons-group">
                                        {{-- Assign as Student --}}
                                        <form action="{{ route('admin.approve', $user->id) }}" method="POST"
                                            class="d-inline approve-student-form"
                                            data-user-name="{{ $user->first_name }} {{ $user->last_name }}">
                                            @csrf
                                            <input type="hidden" name="role" value="student">
                                            <button type="button" class="btn-assign-student" title="Assign as Student">
                                                <i class="fas fa-user-graduate"></i> Student
                                            </button>
                                        </form>

                                        {{-- Assign as Assessor --}}
                                        <form action="{{ route('admin.approve', $user->id) }}" method="POST"
                                            class="d-inline approve-assessor-form"
                                            data-user-name="{{ $user->first_name }} {{ $user->last_name }}">
                                            @csrf
                                            <input type="hidden" name="role" value="assessor">
                                            <button type="button" class="btn-assign-assessor" title="Assign as Assessor">
                                                <i class="fas fa-user-tie"></i> Assessor
                                            </button>
                                        </form>

                                        {{-- Reject --}}
                                        <button type="button" class="btn-reject" 
                                            data-user-id="{{ $user->id }}"
                                            data-user-name="{{ $user->first_name }} {{ $user->last_name }}"
                                            data-user-email="{{ $user->email }}"
                                            title="Reject">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted" style="padding: 40px;">
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

    {{-- Rejection Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Reject Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to reject the account for <strong id="rejectUserName"></strong>?</p>
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">Rejection Reason (Optional)</label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" 
                                placeholder="Enter reason for rejection (will be sent via email)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="{{ asset('css/pending-submissions.css') }}">
    <style>
        .action-buttons-group {
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        .btn-assign-student,
        .btn-assign-assessor,
        .btn-reject {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .btn-assign-student {
            background-color: #007bff;
            color: white;
        }

        .btn-assign-student:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
        }

        .btn-assign-assessor {
            background-color: #28a745;
            color: white;
        }

        .btn-assign-assessor:hover {
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

        .controls-section {
            margin-bottom: 20px;
        }

        .search-controls {
            display: flex;
            justify-content: flex-start;
            width: 100%;
        }

        .search-group {
            display: flex;
            gap: 8px;
            align-items: center;
            width: 100%;
        }

        .search-group .form-control {
            flex: 1;
            max-width: 400px;
        }

        /* User ID column - make it smaller */
        .submissions-table th:nth-child(1),
        .submissions-table td:nth-child(1) {
            width: 8%;
            min-width: 80px;
            max-width: 100px;
        }

        /* Email column styling - make it longer */
        .submissions-table .email-cell {
            min-width: 300px;
            max-width: 400px;
            width: 35%;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        /* Date Registered column - make it smaller but ensure it fits on one line */
        .submissions-table th:nth-child(4),
        .submissions-table td:nth-child(4) {
            width: 15%;
            min-width: 140px;
            max-width: 160px;
            white-space: nowrap;
        }

        @media (max-width: 768px) {
            .submissions-table .email-cell {
                min-width: 200px;
                max-width: 250px;
                width: auto;
                font-size: 0.875rem;
            }

            .submissions-table th:nth-child(4),
            .submissions-table td:nth-child(4) {
                width: auto;
                min-width: 100px;
                max-width: 120px;
                font-size: 0.875rem;
            }
        }
    </style>
    <script src="{{ asset('js/admin_pagination.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Assign as Student
            document.querySelectorAll('.approve-student-form').forEach(function (form) {
                const button = form.querySelector('.btn-assign-student');
                const userName = form.getAttribute('data-user-name');

                button.addEventListener('click', function (e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Assign as Student?',
                        html: `Are you sure you want to approve and assign <strong>${userName}</strong> as a <strong>Student</strong>?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#007bff',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, Assign as Student',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            // Assign as Assessor
            document.querySelectorAll('.approve-assessor-form').forEach(function (form) {
                const button = form.querySelector('.btn-assign-assessor');
                const userName = form.getAttribute('data-user-name');

                button.addEventListener('click', function (e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Assign as Assessor?',
                        html: `Are you sure you want to approve and assign <strong>${userName}</strong> as an <strong>Assessor</strong>?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, Assign as Assessor',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            // Reject button
            const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
            const rejectForm = document.getElementById('rejectForm');

            document.querySelectorAll('.btn-reject').forEach(function (button) {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    const userId = this.getAttribute('data-user-id');
                    const userName = this.getAttribute('data-user-name');

                    document.getElementById('rejectUserName').textContent = userName;
                    rejectForm.action = "{{ route('admin.reject', ':id') }}".replace(':id', userId);
                    rejectModal.show();
                });
            });

            // Auto-fade success alert
            const successAlert = document.getElementById('successAlert');
            if (successAlert) {
                setTimeout(function() {
                    successAlert.style.transition = 'opacity 0.5s ease-out';
                    successAlert.style.opacity = '0';
                    setTimeout(function() {
                        successAlert.remove();
                    }, 500);
                }, 3000);
            }
        });
    </script>
@endsection
