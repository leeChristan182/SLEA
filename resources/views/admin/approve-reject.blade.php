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
                        <th>Actions</th>
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
                            <td>{{ optional($user->created_at)->setTimezone('Asia/Manila')->format('M d, Y h:i A') ?? '—' }}</td>

                            {{-- Actions column with view button --}}
                            <td>
                                <button 
                                    type="button" 
                                    class="btn-action-view" 
                                    title="View Actions"
                                    onclick="openActionModal({{ $user->id }}, '{{ addslashes($fullName) }}', '{{ $user->email }}')"
                                >
                                    <i class="fas fa-eye"></i> View
                                </button>
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

{{-- Action Modal --}}
<div id="actionModal" class="action-modal" style="display: none;">
    <div class="action-modal-dialog">
        <div class="action-modal-content">
            <div class="action-modal-header">
                <h5 class="action-modal-title">
                    <i class="fas fa-user-check me-2"></i>
                    Account Action
                </h5>
                <button type="button" class="action-modal-close" onclick="closeActionModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="action-modal-body">
                <div class="action-forms-section">
                    {{-- Approve Form --}}
                    <form id="approveForm" method="POST" class="action-form">
                        @csrf
                        <input type="hidden" name="user_id" id="approveUserId">
                        
                        <div class="form-group mb-3">
                            <label for="roleSelect" class="form-label">
                                <strong>Assign Role <span class="text-danger">*</span></strong>
                            </label>
                            <select name="role" id="roleSelect" class="form-control form-control-lg" required>
                                <option value="" disabled selected>-- Select Role --</option>
                                <option value="student">Student</option>
                                <option value="assessor">Assessor</option>
                            </select>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Both Student and Assessor accounts will be limited until they complete their details.
                            </small>
                        </div>

                        <button type="button" class="btn btn-success btn-action-submit w-100" onclick="handleApprove()">
                            <i class="fas fa-check me-2"></i> Approve Account
                        </button>
                    </form>

                    <div class="divider-section">
                        <span>OR</span>
                    </div>

                    {{-- Reject Form --}}
                    <form id="rejectForm" method="POST" class="action-form">
                        @csrf
                        <input type="hidden" name="user_id" id="rejectUserId">
                        <input type="hidden" name="rejection_reason" id="rejectionReason">
                        
                        <div class="form-group mb-3">
                            <label for="rejectionReasonText" class="form-label">
                                <strong>Rejection Reason <span class="text-muted">(Optional)</span></strong>
                            </label>
                            <textarea 
                                name="rejection_reason_text" 
                                id="rejectionReasonText" 
                                class="form-control" 
                                rows="3" 
                                placeholder="Enter rejection reason (optional)..."
                                maxlength="500"
                            ></textarea>
                            <small class="form-text text-muted">
                                <span id="charCount">0</span>/500 characters
                            </small>
                        </div>

                        <button type="button" class="btn btn-danger btn-action-submit w-100" onclick="handleReject()">
                            <i class="fas fa-times me-2"></i> Reject Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
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

    /* Action View Button */
    .btn-action-view {
        background: #7b0000;
        color: #fff;
        border: 1px solid #7b0000;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-action-view:hover {
        background: #5c0000;
        border-color: #5c0000;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(123, 0, 0, 0.3);
    }

    /* Action Modal Styles */
    .action-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        z-index: 1050;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .action-modal[style*="flex"] {
        display: flex !important;
    }

    .action-modal-dialog {
        width: 100%;
        max-width: 700px;
        margin: 0;
        position: relative;
        max-height: calc(100vh - 40px);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .action-modal-content {
        background: #fff;
        border-radius: 0;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        max-height: calc(100vh - 40px);
        display: flex;
        flex-direction: column;
        width: 100%;
    }

    .action-modal-header {
        background: #7b0000;
        color: #fff;
        padding: 18px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 2px solid #5c0000;
        flex-shrink: 0;
    }

    .action-modal-title {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        display: flex;
        align-items: center;
    }

    .action-modal-close {
        background: none;
        border: none;
        color: #fff;
        font-size: 24px;
        cursor: pointer;
        padding: 0;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0;
        transition: background 0.2s;
    }

    .action-modal-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .action-modal-body {
        padding: 24px;
        overflow-y: auto;
        flex: 1;
        min-height: 0;
        max-height: calc(100vh - 180px);
    }


    .action-forms-section {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .action-form {
        padding: 20px;
        background: #f8f9fa;
        border-radius: 0;
        border: 1px solid #dee2e6;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }

    .form-control-lg,
    .form-control {
        padding: 12px 16px;
        font-size: 16px;
        border: 1px solid #ced4da;
        border-radius: 0;
        width: 100%;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .form-control-lg:focus,
    .form-control:focus {
        outline: none;
        border-color: #7b0000;
        box-shadow: 0 0 0 3px rgba(123, 0, 0, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 70px;
        padding: 12px 16px;
        font-size: 16px;
    }

    .btn-action-submit {
        padding: 12px 24px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 0;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
    }

    .w-100 {
        width: 100% !important;
    }

    .btn-action-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .divider-section {
        text-align: center;
        position: relative;
        margin: 16px 0;
    }

    .divider-section::before,
    .divider-section::after {
        content: '';
        position: absolute;
        top: 50%;
        width: 45%;
        height: 1px;
        background: #dee2e6;
    }

    .divider-section::before {
        left: 0;
    }

    .divider-section::after {
        right: 0;
    }

    .divider-section span {
        background: #fff;
        padding: 0 12px;
        color: #6c757d;
        font-weight: 600;
        position: relative;
    }

    /* Dark mode support */
    body.dark-mode .action-modal-content {
        background: #2a2a2a;
        color: #f0f0f0;
    }


    body.dark-mode .action-form {
        background: #3a3a3a;
        border-color: #555;
    }

    body.dark-mode .form-label {
        color: #f0f0f0;
    }

    body.dark-mode .form-control-lg,
    body.dark-mode .form-control {
        background: #2a2a2a;
        border-color: #555;
        color: #f0f0f0;
    }

    body.dark-mode .form-control-lg:focus,
    body.dark-mode .form-control:focus {
        border-color: #7b0000;
        box-shadow: 0 0 0 3px rgba(123, 0, 0, 0.3);
    }

    body.dark-mode .divider-section span {
        background: #2a2a2a;
    }
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

    // Character counter for rejection reason
    document.addEventListener('DOMContentLoaded', function () {
        const reasonTextarea = document.getElementById('rejectionReasonText');
        const charCount = document.getElementById('charCount');
        
        if (reasonTextarea && charCount) {
            reasonTextarea.addEventListener('input', function() {
                charCount.textContent = this.value.length;
            });
        }

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

    // Open action modal
    function openActionModal(userId, userName, userEmail) {
        const modal = document.getElementById('actionModal');
        const approveUserId = document.getElementById('approveUserId');
        const rejectUserId = document.getElementById('rejectUserId');
        const roleSelect = document.getElementById('roleSelect');
        const rejectionReasonText = document.getElementById('rejectionReasonText');

        if (!modal) return;

        // Set user data
        if (approveUserId) approveUserId.value = userId;
        if (rejectUserId) rejectUserId.value = userId;
        
        // Reset forms
        if (roleSelect) roleSelect.value = '';
        if (rejectionReasonText) {
            rejectionReasonText.value = '';
            const charCount = document.getElementById('charCount');
            if (charCount) charCount.textContent = '0';
        }

        // Set form actions
        const approveForm = document.getElementById('approveForm');
        const rejectForm = document.getElementById('rejectForm');
        const baseUrl = '{{ url("") }}';
        if (approveForm) {
            approveForm.action = `${baseUrl}/admin/approve/${userId}`;
        }
        if (rejectForm) {
            rejectForm.action = `${baseUrl}/admin/reject/${userId}`;
        }

        // Show modal
        document.body.style.overflow = 'hidden';
        modal.style.display = 'flex';
    }

    // Close action modal
    function closeActionModal() {
        const modal = document.getElementById('actionModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    // Handle approve action
    function handleApprove() {
        const form = document.getElementById('approveForm');
        const roleSelect = document.getElementById('roleSelect');
        const approveUserId = document.getElementById('approveUserId');
        const userId = approveUserId ? approveUserId.value : '';
        
        // Get user name from the table row
        const viewButton = document.querySelector(`[onclick*="${userId}"]`);
        const userName = viewButton ? viewButton.closest('tr').querySelector('td:nth-child(2)')?.textContent.trim() || 'this user' : 'this user';

        if (!form || !roleSelect) return;

        const role = roleSelect.value;
        if (!role) {
            Swal.fire({
                title: 'Role Required',
                text: 'Please select a role before approving.',
                icon: 'info'
            });
            return;
        }

            const note = '<br><small>Both Student and Assessor accounts will be limited until they complete their details.</small>';

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
            if (result.isConfirmed) {
                closeActionModal();
                form.submit();
            }
        });
    }

    // Handle reject action
    function handleReject() {
        const form = document.getElementById('rejectForm');
        const rejectionReasonText = document.getElementById('rejectionReasonText');
        const rejectionReason = document.getElementById('rejectionReason');
        const rejectUserId = document.getElementById('rejectUserId');
        const userId = rejectUserId ? rejectUserId.value : '';
        
        // Get user name from the table row
        const viewButton = document.querySelector(`[onclick*="${userId}"]`);
        const userName = viewButton ? viewButton.closest('tr').querySelector('td:nth-child(2)')?.textContent.trim() || 'this user' : 'this user';

        if (!form) return;

        Swal.fire({
            title: 'Reject Account?',
            html: `Reject <strong>${userName}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Reject',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Set rejection reason
                if (rejectionReason && rejectionReasonText) {
                    rejectionReason.value = rejectionReasonText.value.slice(0, 500);
                }
                closeActionModal();
                form.submit();
            }
        });
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('actionModal');
        if (modal && e.target === modal) {
            closeActionModal();
        }
    });
</script>
@endsection
