{{-- resources/views/admin/manage_accounts.blade.php --}}
@extends('layouts.app')

@section('title', 'Manage Assessor Accounts')

@section('content')
<div class="container">

    <main class="main-content">
        <div class="page-with-back-button">
            <div class="page-content">
                <!-- Back Button -->
                <div class="rubric-header-nav mb-3">
                    <a href="{{ route('admin.profile') }}" class="btn btn-back">
                        <i class="fas fa-arrow-left"></i> Back to Profile
                    </a>
                </div>

                <h2 class="manage-title">Manage Assessor Accounts</h2>

                {{-- Flash Messages --}}
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="m-0 ps-3">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Controls (filter/search) --}}
                <form class="controls d-flex justify-content-between align-items-center mb-3" method="GET" action="{{ route('admin.manage.assessors') }}">
                    <div class="d-flex gap-2 align-items-center">
                        <label class="me-2">
                            <span class="small text-muted d-block">Filter</span>
                            <select name="filter" class="form-select">
                                <option value="">None</option>
                                <option value="new" @selected(request('filter')==='new' )>New (Last 7 days)</option>
                            </select>
                        </label>

                        <label class="me-2">
                            <span class="small text-muted d-block">Sort by</span>
                            <select name="sort" class="form-select">
                                <option value="">None</option>
                                <option value="name" @selected(request('sort')==='name' )>Name</option>
                                <option value="date" @selected(request('sort')==='date' )>Date Created</option>
                            </select>
                        </label>
                    </div>

                    <div class="d-flex gap-2 align-items-end">
                        <div>
                            <input type="text" name="q" class="form-control" placeholder="Search by email or name..." value="{{ request('q') }}">
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                        </div>
                        <div>
                            <a href="{{ route('admin.assessors.create') }}" class="btn btn-success ms-2">
                                <i class="fas fa-user-plus me-1"></i> Add Assessor
                            </a>
                        </div>
                    </div>
                </form>

                {{-- Assessor Accounts Table --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Full Name</th>
                                <th>Email Address</th>
                                <th>Position</th>
                                <th>Created By (Admin)</th>
                                <th>Date Created</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($assessors as $assessor)
                            <tr id="row-{{ \Illuminate\Support\Str::slug($assessor->email_address) }}">
                                <td>{{ $assessor->first_name }} {{ $assessor->last_name }}</td>
                                <td>{{ $assessor->email_address }}</td>
                                <td>{{ $assessor->position }}</td>
                                <td>{{ optional($assessor->admin)->email_address ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($assessor->dateacc_created)->format('M d, Y h:i A') }}</td>
                                <td class="text-center">
                                    <div class="btn-group" role="group" aria-label="actions">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-warning"
                                            title="Disable / Enable"
                                            onclick="confirmToggleAssessor('{{ addslashes($assessor->email_address) }}', '{{ addslashes(optional($assessor->admin)->email_address ?? '') }}')">
                                            <i class="fas fa-user-slash"></i>
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-danger"
                                            title="Delete"
                                            onclick="confirmDeleteAssessor('{{ addslashes($assessor->email_address) }}', '{{ addslashes($assessor->first_name . ' ' . $assessor->last_name) }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No assessor accounts found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination + showing info --}}
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <div class="text-muted small">
                        Showing
                        @if($assessors->total() > 0)
                        {{ $assessors->firstItem() }} - {{ $assessors->lastItem() }}
                        @else
                        0
                        @endif
                        of {{ $assessors->total() }} entries
                    </div>

                    <div>
                        {{ $assessors->appends(request()->query())->links() }}
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>

{{-- Toggle Assessor Modal --}}
<div id="toggleModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="toggleModalTitle">Toggle Assessor</h3>
            <button class="close" onclick="closeToggleModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p id="toggleModalMessage">Are you sure you want to toggle this assessor's status?</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeToggleModal()">Cancel</button>
            <button id="toggleConfirmBtn" class="btn btn-warning" onclick="submitToggleForm()">Confirm</button>

            {{-- Hidden form used for AJAX submit (has CSRF token) --}}
            <form id="toggleForm" method="POST" style="display:none;">
                @csrf
                @method('PATCH')
            </form>
        </div>
    </div>
</div>

{{-- Delete Assessor Modal --}}
<div id="deleteModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="deleteModalTitle">Delete Assessor</h3>
            <button class="close" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p id="deleteModalMessage">Are you sure you want to delete this assessor? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <button class="btn btn-danger" onclick="submitDeleteForm()">Delete</button>

            <form id="deleteForm" method="POST" style="display:none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>

{{-- Simple success toast/modal (non-blocking) --}}
<div id="successToast" style="display:none; position: fixed; right: 20px; bottom: 20px; z-index: 9999;">
    <div class="toast bg-success text-white p-3 rounded">
        <div id="successToastMessage">Action completed successfully</div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    /* Utility to safely build URL segments for emails — encodeURIComponent will be used. */

    /* Toggle Assessor functions */
    function confirmToggleAssessor(email, adminEmail) {
        const modal = document.getElementById('toggleModal');
        const title = document.getElementById('toggleModalTitle');
        const message = document.getElementById('toggleModalMessage');

        title.textContent = 'Toggle Assessor Status';
        message.textContent = `Are you sure you want to toggle the status of "${email}" (created by ${adminEmail || 'N/A'})?`;

        // Set the form action; route pattern assumed: /admin/assessors/{email}/toggle
        const form = document.getElementById('toggleForm');
        form.action = '/admin/assessors/' + encodeURIComponent(email) + '/toggle';

        modal.style.display = 'block';
    }

    function closeToggleModal() {
        document.getElementById('toggleModal').style.display = 'none';
    }

    function submitToggleForm() {
        const form = document.getElementById('toggleForm');
        const formData = new FormData(form);

        fetch(form.action, {
                method: 'POST', // method spoofed via _method PATCH in the form
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
            })
            .then(async (res) => {
                const data = await res.json().catch(() => ({}));
                closeToggleModal();
                showSuccess(data.message || 'Status updated');
                setTimeout(() => location.reload(), 1000);
            })
            .catch(err => {
                console.error(err);
                closeToggleModal();
                showSuccess('Error updating status. Check console.');
            });
    }

    /* Delete functions */
    function confirmDeleteAssessor(email, fullName) {
        const modal = document.getElementById('deleteModal');
        const message = document.getElementById('deleteModalMessage');

        message.textContent = `Are you sure you want to permanently delete "${fullName}" (${email})? This action cannot be undone.`;

        const form = document.getElementById('deleteForm');
        form.action = '/admin/assessors/' + encodeURIComponent(email);

        modal.style.display = 'block';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }

    function submitDeleteForm() {
        const form = document.getElementById('deleteForm');
        const formData = new FormData(form);

        fetch(form.action, {
                method: 'POST', // method spoofed via _method DELETE
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
            })
            .then(async res => {
                const data = await res.json().catch(() => ({}));
                closeDeleteModal();
                showSuccess(data.message || 'Assessor deleted');
                setTimeout(() => location.reload(), 1000);
            })
            .catch(err => {
                console.error(err);
                closeDeleteModal();
                showSuccess('Error deleting assessor. Check console.');
            });
    }

    /* Small success helper */
    function showSuccess(message) {
        const toast = document.getElementById('successToast');
        const msg = document.getElementById('successToastMessage');
        msg.textContent = message;
        toast.style.display = 'block';
        setTimeout(() => toast.style.display = 'none', 2500);
    }

    /* Close modals with Escape and click outside */
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeToggleModal();
            closeDeleteModal();
        }
    });
    window.addEventListener('click', function(e) {
        const toggleModal = document.getElementById('toggleModal');
        const deleteModal = document.getElementById('deleteModal');
        if (e.target === toggleModal) closeToggleModal();
        if (e.target === deleteModal) closeDeleteModal();
    });
</script>
@endsection