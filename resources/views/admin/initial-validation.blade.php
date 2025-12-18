@extends('layouts.app')

@section('title', 'Initial Validation')

@section('content')
    <div class="container admin-initial-validation-container">
        @include('partials.sidebar')

        <main class="main-content">
            <div class="page-header">
                <h1>Initial Validation</h1>
            </div>

            {{-- Flash Messages (hidden, will show as modal) --}}
            @if (session('status'))
                <div class="alert alert-success" style="display:none;">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
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
                            <option value="{{ \App\Models\User::ROLE_STUDENT }}"
                                {{ request('role') === \App\Models\User::ROLE_STUDENT ? 'selected' : '' }}>
                                Student (Limited)
                            </option>
                            <option value="{{ \App\Models\User::ROLE_ASSESSOR }}"
                                {{ request('role') === \App\Models\User::ROLE_ASSESSOR ? 'selected' : '' }}>
                                Assessor (Incomplete)
                            </option>
                        </select>
                    </div>
                </div>

                <div class="search-controls">
                    <div class="search-group">
                        <input type="text" id="searchInput" name="q" class="form-control"
                            placeholder="Search by name, email, contact"
                            value="{{ request('q') }}">
                        <button type="button" id="searchBtn" class="btn-search-maroon search-btn-attached" title="Search"
                            onclick="handleSearchClick(event)">
                            <i class="fas fa-search"></i>
                        </button>
                        <button type="button" id="clearBtn" class="btn-clear" title="Clear search"
                            onclick="handleClearClick(event)">
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
                            {{-- Unified info = register fields (minus password/privacy) --}}
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Birth Date</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($users as $user)
                            @php
                                $isStudent = $user->role === \App\Models\User::ROLE_STUDENT;

                                // Relations (make sure controller eager-loads these)
                                $acad = $user->studentAcademic;
                                $lead = $isStudent ? $user->studentLeaderships->sortByDesc('created_at')->first() : null;
                                $ass  = $user->assessorInfo;

                                $corUrl = ($isStudent && $acad && !empty($acad->certificate_of_registration_path))
                                    ? route('admin.students.cor', $user)
                                    : null;

                                // Build modal payload
                                $details = [
                                    'id' => $user->id,
                                    'name' => $user->full_name,
                                    'email' => $user->email,
                                    'contact' => $user->contact ?? null,
                                    'birth_date' => $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format('F d, Y') : null,
                                    'role' => $user->role,

                                    'student' => [
                                        'student_number' => $acad->student_number ?? null,
                                        'program'        => $acad?->program?->code ?? $acad?->program?->name ?? null,
                                        'college'        => $acad?->program?->college?->code ?? $acad?->program?->college?->name ?? null,
                                        'year_level'     => $acad->year_level ?? null,
                                        'cor_url'        => $corUrl,
                                        'leadership'     => $lead ? [
                                            'type'         => $lead?->leadershipType?->name ?? null,
                                            'cluster'      => $lead?->cluster?->name ?? null,
                                            'organization' => $lead?->organization?->name ?? null,
                                            'position'     => $lead?->position?->name ?? null,
                                            'term'         => $lead->term ?? null,
                                        ] : null,
                                    ],

                                    'assessor' => [
                                        'office_unit' => $ass->office_unit ?? null,
                                        'position'    => $ass->position ?? null,
                                        'designation' => $ass->designation ?? null,
                                    ],
                                ];
                            @endphp

                            <tr>
                                <td>{{ $user->full_name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->contact ?? '—' }}</td>
                                <td>
                                    {{ $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format('F d, Y') : '—' }}
                                </td>
                                <td>{{ ucfirst($user->role) }}</td>

                                <td>
                                    <div class="action-buttons-group">
                                        {{-- VIEW DETAILS --}}
                                        <button type="button"
                                            class="btn btn-sm btn-outline-maroon view-details-btn"
                                            data-details='@json($details)'
                                            data-approve-url="{{ route('admin.initial-validation.approve', $user) }}"
                                            data-reject-url="{{ route('admin.initial-validation.reject', $user) }}"
                                            data-user-name="{{ $user->full_name }}"
                                            title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted" style="padding: 40px;">
                                    No users found for initial validation.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($users->hasPages())
                <div class="pagination-container" data-pagination-container>
                    <div class="pagination-info">
                        Showing {{ $users->firstItem() ?? 0 }} – {{ $users->lastItem() ?? 0 }}
                        of {{ $users->total() }} entries
                    </div>

                    <div class="unified-pagination">
                        @if ($users->onFirstPage())
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

                            @if ($start > 1)
                                <a href="{{ $users->url(1) }}" class="page-btn">1</a>
                                @if ($start > 2)
                                    <span class="page-btn disabled">...</span>
                                @endif
                            @endif

                            @for ($i = $start; $i <= $end; $i++)
                                @if ($i == $currentPage)
                                    <span class="page-btn active">{{ $i }}</span>
                                @else
                                    <a href="{{ $users->url($i) }}" class="page-btn">{{ $i }}</a>
                                @endif
                            @endfor

                            @if ($end < $lastPage)
                                @if ($end < $lastPage - 1)
                                    <span class="page-btn disabled">...</span>
                                @endif
                                <a href="{{ $users->url($lastPage) }}" class="page-btn">{{ $lastPage }}</a>
                            @endif
                        </span>

                        @if ($users->hasMorePages())
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Match other admin sections’ wider table feel (no horizontal scrollbar; wrap content instead) */
        .admin-initial-validation-container {
            max-width: 1400px;
            width: min(95vw, 1400px);
        }

        .admin-initial-validation-container .submissions-table-container {
            width: 100%;
            overflow-x: hidden; /* keep single viewport */
        }

        .admin-initial-validation-container .submissions-table {
            width: 100%;
            table-layout: fixed; /* prevents overflow */
        }

        .admin-initial-validation-container .submissions-table th,
        .admin-initial-validation-container .submissions-table td {
            overflow-wrap: anywhere;
            word-break: break-word;
            white-space: normal;
        }

        /* Column sizing */
        .admin-initial-validation-container .submissions-table th:nth-child(1),
        .admin-initial-validation-container .submissions-table td:nth-child(1) { width: 22%; } /* Full Name */
        .admin-initial-validation-container .submissions-table th:nth-child(2),
        .admin-initial-validation-container .submissions-table td:nth-child(2) { width: 26%; } /* Email */
        .admin-initial-validation-container .submissions-table th:nth-child(3),
        .admin-initial-validation-container .submissions-table td:nth-child(3) { width: 16%; } /* Contact */
        .admin-initial-validation-container .submissions-table th:nth-child(4),
        .admin-initial-validation-container .submissions-table td:nth-child(4) { width: 16%; } /* Birth Date */
        .admin-initial-validation-container .submissions-table th:nth-child(5),
        .admin-initial-validation-container .submissions-table td:nth-child(5) { width: 10%; } /* Role */
        .admin-initial-validation-container .submissions-table th:nth-child(6),
        .admin-initial-validation-container .submissions-table td:nth-child(6) {
            width: 10%;
            white-space: nowrap;
        } /* Actions */

        /* Maroon View Button - Icon Only */
        .btn-outline-maroon.view-details-btn {
            color: #7E0308;
            border-color: #7E0308;
            background-color: transparent;
            padding: 6px 10px;
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
        }

        .btn-outline-maroon.view-details-btn:hover {
            color: #fff;
            background-color: #7E0308;
            border-color: #7E0308;
        }

        .btn-outline-maroon.view-details-btn:focus {
            color: #fff;
            background-color: #7E0308;
            border-color: #7E0308;
            box-shadow: 0 0 0 0.2rem rgba(126, 3, 8, 0.25);
        }

        .btn-outline-maroon.view-details-btn:active {
            color: #fff;
            background-color: #5a0205;
            border-color: #5a0205;
        }

        .btn-outline-maroon.view-details-btn:active:focus {
            box-shadow: 0 0 0 0.2rem rgba(126, 3, 8, 0.5);
        }

        .btn-outline-maroon.view-details-btn i {
            font-size: 14px;
            margin: 0;
        }
        /* Validation Modal Styles */
        .validation-modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            align-items: center;
            justify-content: center;
        }

        .validation-modal[style*="flex"] {
            display: flex !important;
        }

        .validation-modal-content {
            background-color: #fff;
            margin: 0;
            padding: 0;
            border-radius: 0 !important;
            width: 90%;
            max-width: 720px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .validation-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            border-bottom: 1px solid #dee2e6;
            background: #f8f9fa;
            border-radius: 0 !important;
        }

        .validation-modal-header h3 {
            margin: 0;
            color: #7b0000;
            font-size: 20px;
            font-weight: 600;
        }

        .close-modal {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
            line-height: 1;
            padding: 0;
            background: none;
            border: none;
        }

        .close-modal:hover {
            color: #7b0000;
        }

        .validation-modal-body {
            padding: 25px;
            text-align: left;
        }

        .validation-modal-footer {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            padding: 20px 25px;
            border-top: 1px solid #dee2e6;
            background: #f8f9fa;
            border-radius: 0 !important;
        }

        .btn-approve-modal,
        .btn-reject-modal {
            min-width: 120px;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-approve-modal {
            background-color: #28a745;
            color: white;
        }

        .btn-approve-modal:hover {
            background-color: #218838;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        }

        .btn-reject-modal {
            background-color: #dc3545;
            color: white;
        }

        .btn-reject-modal:hover {
            background-color: #c82333;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }

        body.dark-mode .validation-modal-content {
            background-color: #3a3a3a;
            color: #f0f0f0;
        }

        body.dark-mode .validation-modal-header {
            background: #2a2a2a;
            border-color: #555;
        }

        body.dark-mode .validation-modal-header h3 {
            color: #F9BD3D;
        }

        body.dark-mode .close-modal:hover {
            color: #F9BD3D;
        }

        body.dark-mode .validation-modal-footer {
            background: #2a2a2a;
            border-color: #555;
        }

        /* SweetAlert2 backdrop blur - applies when confirmation modal is shown */
        .swal2-container.swal2-backdrop-show {
            backdrop-filter: blur(5px) !important;
            -webkit-backdrop-filter: blur(5px) !important;
        }

        /* Ensure the backdrop overlay itself is blurred */
        .swal2-container.swal2-backdrop-show .swal2-backdrop-show {
            backdrop-filter: blur(5px) !important;
            -webkit-backdrop-filter: blur(5px) !important;
        }

        /* Remove corner radius from SweetAlert2 modals for formality */
        .swal2-popup {
            border-radius: 0 !important;
        }

        .swal2-container .swal2-popup {
            border-radius: 0 !important;
        }
    </style>

    <script>
        function handleSearchClick(event) {
            event.preventDefault();
            const q = document.getElementById('searchInput').value.trim();
            const role = document.getElementById('roleFilter').value;

            const form = document.createElement('form');
            form.method = 'GET';
            form.action = window.location.pathname;

            if (q) addHidden(form, 'q', q);
            if (role) addHidden(form, 'role', role);

            document.body.appendChild(form);
            form.submit();
        }

        function handleClearClick(event) {
            event.preventDefault();
            document.getElementById('searchInput').value = '';
            applyFilters();
        }

        function applyFilters() {
            const q = document.getElementById('searchInput').value.trim();
            const role = document.getElementById('roleFilter').value;

            const form = document.createElement('form');
            form.method = 'GET';
            form.action = window.location.pathname;

            if (q) addHidden(form, 'q', q);
            if (role) addHidden(form, 'role', role);

            document.body.appendChild(form);
            form.submit();
        }

        function addHidden(form, name, value) {
            const i = document.createElement('input');
            i.type = 'hidden';
            i.name = name;
            i.value = value;
            form.appendChild(i);
        }

        // Show success modal after approve/reject
        document.addEventListener('DOMContentLoaded', function () {
            const statusAlert = document.querySelector('.alert-success');
            if (statusAlert) {
                const statusText = statusAlert.textContent.trim();
                const isSuccess = statusText.toLowerCase().includes('successfully') ||
                                 statusText.toLowerCase().includes('approved') ||
                                 statusText.toLowerCase().includes('rejected');

                if (isSuccess) {
                    // Determine icon and title based on message
                    let icon = 'success';
                    let title = 'Success';

                    if (statusText.toLowerCase().includes('rejected')) {
                        icon = 'info';
                        title = 'Validation Rejected';
                    } else if (statusText.toLowerCase().includes('approved')) {
                        icon = 'success';
                        title = 'Validation Approved';
                    }

                    Swal.fire({
                        icon: icon,
                        title: title,
                        text: statusText,
                        confirmButtonColor: icon === 'success' ? '#28a745' : '#0d6efd',
                        confirmButtonText: 'OK',
                        timer: 3000,
                        timerProgressBar: true,
                        allowOutsideClick: true,
                        allowEscapeKey: true
                    });
                }
            }
        });

        // View Details modal with Approve/Reject buttons
        document.addEventListener('DOMContentLoaded', function () {
            const esc = (v) => {
                if (v === null || v === undefined || v === '') return '—';
                return String(v)
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            };

            document.querySelectorAll('.view-details-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const data = JSON.parse(this.getAttribute('data-details'));
                    const approveUrl = this.getAttribute('data-approve-url');
                    const rejectUrl = this.getAttribute('data-reject-url');
                    const userName = this.getAttribute('data-user-name');
                    const isStudent = (data.role === 'student');

                    let bodyHtml = `
                        <div style="text-align:left;">
                            <div><strong>Name:</strong> ${esc(data.name)}</div>
                            <div><strong>Email:</strong> ${esc(data.email)}</div>
                            <div><strong>Contact:</strong> ${esc(data.contact)}</div>
                            <div><strong>Birth Date:</strong> ${esc(data.birth_date)}</div>
                            <div><strong>Role:</strong> ${esc(data.role)}</div>
                            <hr style="margin:12px 0;">
                    `;

                    if (isStudent) {
                        const s = data.student || {};
                        const l = s.leadership || null;

                        bodyHtml += `
                            <h6 style="margin:0 0 8px;"><strong>Student Academic Info</strong></h6>
                            <div><strong>Student id:</strong> ${esc(s.student_number)}</div>
                            <div><strong>Program:</strong> ${esc(s.program)}</div>
                            <div><strong>College:</strong> ${esc(s.college)}</div>
                            <div><strong>Year Level:</strong> ${esc(s.year_level)}</div>
                            <div style="margin-top:8px;">
                                <strong>COR:</strong>
                                ${s.cor_url ? `<a href="${s.cor_url}" target="_blank" rel="noopener">View COR</a>` : '—'}
                            </div>

                            <hr style="margin:12px 0;">
                            <h6 style="margin:0 0 8px;"><strong>Leadership (Latest)</strong></h6>
                        `;

                        if (l) {
                            bodyHtml += `
                                <div><strong>Type:</strong> ${esc(l.type)}</div>
                                <div><strong>Cluster:</strong> ${esc(l.cluster)}</div>
                                <div><strong>Organization:</strong> ${esc(l.organization)}</div>
                                <div><strong>Position:</strong> ${esc(l.position)}</div>
                                <div><strong>Term:</strong> ${esc(l.term)}</div>
                            `;
                        } else {
                            bodyHtml += `<div class="text-muted">No leadership submitted</div>`;
                        }
                    } else {
                        const a = data.assessor || {};
                        bodyHtml += `
                            <h6 style="margin:0 0 8px;"><strong>Assessor Info</strong></h6>
                            <div><strong>Office/Unit:</strong> ${esc(a.office_unit)}</div>
                            <div><strong>Position:</strong> ${esc(a.position)}</div>
                            <div><strong>Designation:</strong> ${esc(a.designation)}</div>
                        `;
                    }

                    bodyHtml += `</div>`;

                    // Get CSRF token
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                    // Create modal HTML
                    const modalHtml = `
                        <div class="validation-modal" id="validationModal" style="display: flex;">
                            <div class="validation-modal-content" style="border-radius: 0;">
                                <div class="validation-modal-header">
                                    <h3>Submitted Information</h3>
                                    <span class="close-modal" onclick="closeValidationModal()">&times;</span>
                                </div>
                                <div class="validation-modal-body">
                                    ${bodyHtml}
                                </div>
                                <div class="validation-modal-footer">
                                    <form action="${approveUrl}" method="POST" class="d-inline" id="approveFormModal">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <button type="button" class="btn btn-approve-modal" onclick="confirmApprove('${userName}')">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    <form action="${rejectUrl}" method="POST" class="d-inline" id="rejectFormModal">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <button type="button" class="btn btn-reject-modal" onclick="confirmReject('${userName}')">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    `;

                    // Remove existing modal if any
                    const existingModal = document.getElementById('validationModal');
                    if (existingModal) {
                        existingModal.remove();
                    }

                    // Add modal to body
                    document.body.insertAdjacentHTML('beforeend', modalHtml);
                });
            });
        });

        function closeValidationModal() {
            const modal = document.getElementById('validationModal');
            if (modal) {
                modal.remove();
            }
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('validationModal');
            if (modal && e.target === modal) {
                closeValidationModal();
            }
        });

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeValidationModal();
            }
        });

        function confirmApprove(userName) {
            const approveForm = document.getElementById('approveFormModal');
            if (!approveForm) return;

            // Store form action and CSRF token before closing modal
            const formAction = approveForm.action;
            const csrfToken = approveForm.querySelector('input[name="_token"]')?.value;

            // Close the view modal first
            closeValidationModal();

            // Small delay to ensure modal is closed before showing confirmation
            setTimeout(() => {
            Swal.fire({
                title: 'Approve Validation?',
                html: `Approve initial validation for <strong>${userName}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Approve',
                cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    allowOutsideClick: false,
                    allowEscapeKey: true
            }).then((r) => {
                if (r.isConfirmed) {
                        // Create and submit form programmatically
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = formAction;

                        const tokenInput = document.createElement('input');
                        tokenInput.type = 'hidden';
                        tokenInput.name = '_token';
                        tokenInput.value = csrfToken;
                        form.appendChild(tokenInput);

                        document.body.appendChild(form);
                        form.submit();
                }
            });
            }, 100);
        }

        function confirmReject(userName) {
            const rejectForm = document.getElementById('rejectFormModal');
            if (!rejectForm) return;

            // Store form action and CSRF token before closing modal
            const formAction = rejectForm.action;
            const csrfToken = rejectForm.querySelector('input[name="_token"]')?.value;

            // Close the view modal first
            closeValidationModal();

            // Small delay to ensure modal is closed before showing confirmation
            setTimeout(() => {
            Swal.fire({
                title: 'Reject Validation?',
                html: `Reject initial validation for <strong>${userName}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Reject',
                cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    allowOutsideClick: false,
                    allowEscapeKey: true
            }).then((r) => {
                if (r.isConfirmed) {
                        // Create and submit form programmatically
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = formAction;

                        const tokenInput = document.createElement('input');
                        tokenInput.type = 'hidden';
                        tokenInput.name = '_token';
                        tokenInput.value = csrfToken;
                        form.appendChild(tokenInput);

                        document.body.appendChild(form);
                        form.submit();
                }
            });
            }, 100);
        }
    </script>
@endsection
