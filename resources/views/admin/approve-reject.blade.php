@extends('layouts.app')

@section('title', 'Approval of Accounts')

@section('content')
<div class="container">
    @include('partials.sidebar')

    <main class="main-content">
        <div class="page-with-back-button">
            <div class="page-content">
                <!-- Back Button -->
                <div class="rubric-header-nav">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-back">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <h2 class="approval-title">Approval of Accounts</h2>

                {{-- Search & Filter Form --}}
                <form class="controls" method="GET" action="{{ route('admin.approve-reject') }}">
                    <div class="dropdowns">
                        <label>
                            Filter
                            <select name="filter">
                                <option value="">None</option>
                                <option value="student" @selected(request('filter')==='student' )>Student</option>
                                <option value="admin" @selected(request('filter')==='admin' )>Admin</option>
                                <option value="assessor" @selected(request('filter')==='assessor' )>Assessor</option>
                            </select>
                        </label>

                        <label>
                            Sort by
                            <select name="sort">
                                <option value="">None</option>
                                <option value="name" @selected(request('sort')==='name' )>Name</option>
                                <option value="date" @selected(request('sort')==='date' )>Date Created</option>
                                <option value="status" @selected(request('sort')==='status' )>Status</option>
                            </select>
                        </label>
                    </div>

                    <div class="search-box">
                        <input type="text" name="q" placeholder="Search by name, email, or student ID..."
                            value="{{ request('q') }}">
                        <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                    </div>
                </form>

                <table class="approval-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Organization Name</th>
                            <th>Organization Role</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr id="user-row-{{ $user->id }}">
                            <td>{{ $user->student_id ?? 'N/A' }}</td>
                            <td>{{ $user->name ?? 'N/A' }}</td>
                            <td>{{ $user->leadershipInformation->first()->organization_name ?? 'N/A' }}</td>
                            <td>{{ $user->leadershipInformation->first()->organization_role ?? 'N/A' }}</td>
                            <td><span class="status-badge status-pending">Pending</span></td>
                            <td class="action-buttons">
                                <button type="button" class="btn-action btn-reject"
                                    data-id="{{ $user->id }}" data-name="{{ $user->name }}"
                                    onclick="openModal('reject', this)">
                                    <i class="fas fa-times"></i>
                                </button>
                                <button type="button" class="btn-action btn-approve"
                                    data-id="{{ $user->id }}" data-name="{{ $user->name }}"
                                    onclick="openModal('approve', this)">
                                    <i class="fas fa-user-check"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No pending accounts found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </main>
</div>

{{-- Confirmation Modal --}}
<div id="confirmationModal" class="modal" style="display:none;">
    <div class="modal-content confirmation-modal">
        <div class="modal-header">
            <h3 id="modalTitle">Confirm Action</h3>
            <span class="close" onclick="closeModal('confirmationModal')">&times;</span>
        </div>
        <div class="modal-body">
            <p id="modalMessage"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('confirmationModal')">Cancel</button>
            <button type="button" class="btn btn-primary" id="confirmButton"></button>
        </div>
    </div>
</div>

<script>
    let currentAction = '';
    let currentUserId = '';

    function openModal(action, el) {
        currentAction = action;
        currentUserId = el.getAttribute('data-id');

        document.getElementById('modalTitle').textContent =
            action === 'approve' ? 'Approve Account' : 'Reject Account';
        document.getElementById('modalMessage').textContent =
            `Are you sure you want to ${action} this account (${el.getAttribute('data-name')})?`;

        const btn = document.getElementById('confirmButton');
        btn.textContent = action.charAt(0).toUpperCase() + action.slice(1);
        btn.className = `btn btn-${action === 'approve' ? 'success' : 'danger'}`;
        btn.onclick = confirmAction;

        document.getElementById('confirmationModal').style.display = 'flex';
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function confirmAction() {
        const url = currentAction === 'approve' ?
            `{{ url('/admin/approve') }}/${currentUserId}` :
            `{{ url('/admin/reject') }}/${currentUserId}`;

        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'approved' || data.status === 'rejected') {
                    document.getElementById(`user-row-${currentUserId}`).remove();
                }
                closeModal('confirmationModal');
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Failed to process request.');
                closeModal('confirmationModal');
            });
    }
</script>
@endsection