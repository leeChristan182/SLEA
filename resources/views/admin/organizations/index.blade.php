@extends('layouts.app')

@section('title', 'Organizations Management')

@section('content')
<div class="page-wrapper">


    <div class="manage-container orgs-box">
        <h2 class="manage-title">Organizations Management</h2>

        @if ($errors->any())
        <div class="alert alert-danger mt-2">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Back button -->
        <div class="rubric-header-nav mb-2">
            <a href="{{ route('admin.profile') }}" class="btn-back-maroon">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        <!-- Filter Section -->
        <div class="filter-section mb-3">
            <form method="GET" action="{{ route('admin.organizations.index') }}" id="filterForm">
                <div class="filter-row d-flex justify-content-between align-items-end flex-wrap gap-2">
                    <div class="d-flex align-items-end gap-2 flex-wrap">
                        <div class="filter-item">
                            <label for="cluster_filter">Cluster</label>
                            <select name="cluster_filter" id="cluster_filter" class="filter-select">
                        <option value="">All Clusters</option>
                        @foreach ($clusters as $cluster)
                        <option value="{{ $cluster->id }}" {{ request('cluster_filter') == $cluster->id ? 'selected' : '' }}>
                            {{ $cluster->name }}
                        </option>
                        @endforeach
                    </select>
                        </div>

                        <div class="filter-item">
                            <label for="q">Search</label>
                            <div class="search-input-group">
                                <input type="text" name="q" id="q" class="filter-input search-input-with-btn" placeholder="Search by name..." value="{{ request('q') }}">
                                <button type="submit" class="btn-search-maroon search-btn-attached">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="filter-actions d-flex align-items-center gap-2">
                        <button type="button" class="btn-export-enhanced" onclick="openOrgModal()">
                            <i class="fas fa-plus"></i> Add Organization
                        </button>
                        <a href="{{ route('admin.organizations.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
                </form>
        </div>

        <!-- Table -->
        <div class="table-wrap compact-table">
            <table class="manage-table">
                <thead>
                    <tr>
                        <th>Organization Name</th>
                        <th>Cluster</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($organizations as $org)
                    <tr>
                        <td>{{ $org->name }}</td>
                        <td>{{ $org->cluster->name ?? '—' }}</td>
                        <td class="action-cell">
                            <div class="action-buttons">
                                <button class="action-btn btn-edit" title="Edit" onclick='openOrgModal(@json($org))'>
                                    <i class="fas fa-edit"></i>
                                </button>

                                <button type="button" class="action-btn btn-delete" title="Delete" onclick='openDeleteModal(@json($org))'>
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-4 text-muted">No organizations found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- INLINE pagination (builds from elements()) -->
        @if ($organizations->hasPages())
        <div class="pagination-wrapper mt-4">
            {{-- Previous --}}
            @if ($organizations->onFirstPage())
            <span class="page-btn disabled"><i class="fas fa-chevron-left"></i></span>
            @else
            <a href="{{ $organizations->previousPageUrl() }}" class="page-btn">
                <i class="fas fa-chevron-left"></i>
            </a>
            @endif

            {{-- Page numbers --}}
            @foreach ($organizations->links()->elements ?? [] as $element)
            @if (is_string($element))
            <span class="page-btn disabled">{{ $element }}</span>
            @endif

            @if (is_array($element))
            @foreach ($element as $page => $url)
            @if ($page == $organizations->currentPage())
            <span class="page-btn active">{{ $page }}</span>
            @else
            <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
            @endif
            @endforeach
            @endif
            @endforeach

            {{-- Next --}}
            @if ($organizations->hasMorePages())
            <a href="{{ $organizations->nextPageUrl() }}" class="page-btn">
                <i class="fas fa-chevron-right"></i>
            </a>
            @else
            <span class="page-btn disabled"><i class="fas fa-chevron-right"></i></span>
            @endif
        </div>

        <div class="text-center small text-muted mt-2">
            Showing {{ $organizations->firstItem() ?? 0 }} – {{ $organizations->lastItem() ?? 0 }}
            of {{ $organizations->total() }} organizations
        </div>
        @endif

    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="org-modal" style="display: none !important;">
    <div class="modal-dialog org-modal-dialog">
        <div class="modal-content delete-modal-content p-4 rounded-3 shadow">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="form-title mb-0">Confirm Delete</h3>
                <button type="button" class="btn-close-modal" onclick="closeDeleteModal()" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="delete-modal-body">
                <div class="delete-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <p class="delete-message">Are you sure you want to delete this organization? This action cannot be undone.</p>
                <p class="delete-org-name" id="deleteOrgName"></p>
            </div>
            <div class="button-group">
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
@if (session('success'))
<div id="successModal" class="success-modal" style="display: flex !important;">
    <div class="success-modal-content">
        <div class="success-modal-header">
            <button type="button" class="success-close-btn" onclick="closeSuccessModal()" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="success-modal-body">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <p class="success-message">{{ session('success') }}</p>
        </div>
    </div>
</div>
@endif

<!-- Add/Edit Organization Modal -->
<div id="orgModal" class="org-modal" style="display: none !important;">
    <div class="modal-dialog org-modal-dialog">
        <div class="modal-content org-modal-content p-4 rounded-3 shadow">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 id="orgModalTitle" class="form-title mb-0">Add Organization</h3>
                <button type="button" class="btn-close-modal" onclick="closeOrgModal()" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- We'll replace action dynamically in JS -->
            <form id="orgForm" method="POST" action="{{ route('admin.organizations.store') }}">
                @csrf
                <input type="hidden" id="org_id" name="id">

                <div class="form-group mb-3">
                    <label for="name">Organization Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>

                <div class="form-group mb-3">
                    <label for="cluster_id">Cluster <span class="required">*</span></label>
                    <select id="cluster_id" name="cluster_id" class="form-control" required>
                        <option value="">-- Select Cluster --</option>
                        @foreach ($clusters as $cluster)
                        <option value="{{ $cluster->id }}">{{ $cluster->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" class="form-control"></textarea>
                </div>

                <div class="button-group">
                    <button type="submit" class="save-btn btn btn-primary">Save</button>
                    <button type="button" class="cancel-btn btn btn-secondary" onclick="closeOrgModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // base URL for update/put (works if app is in subfolder)
    const orgBaseUrl = "{{ url('admin/organizations') }}";

    function openOrgModal(org = null) {
        const modal = document.getElementById('orgModal');
        const title = document.getElementById('orgModalTitle');
        const form = document.getElementById('orgForm');

        if (!modal) return;

        // Prevent body scroll and ensure modal is on top
        document.body.style.overflow = 'hidden';
        document.body.style.position = 'fixed';
        document.body.style.width = '100%';
        document.body.style.height = '100%';
        
        // Show modal with proper centering
        modal.style.display = 'flex';
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.right = '0';
        modal.style.bottom = '0';

        // remove _method if present
        const existingMethod = form.querySelector('input[name="_method"]');
        if (existingMethod) existingMethod.remove();

        if (org) {
            title.textContent = 'Edit Organization';

            // set proper action using base URL
            form.action = `${orgBaseUrl}/${org.id}`;

            // add _method PUT
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PUT';
            form.appendChild(methodInput);

            // fill form fields — note: org passed is a JS object
            document.getElementById('org_id').value = org.id ?? '';
            document.getElementById('name').value = org.name ?? '';
            document.getElementById('cluster_id').value = org.cluster_id ?? '';
            document.getElementById('description').value = org.description ?? '';
        } else {
            title.textContent = 'Add Organization';
            form.action = '{{ route("admin.organizations.store") }}';
            // reset form fields
            form.reset();
            document.getElementById('org_id').value = '';
        }
    }

    function closeOrgModal() {
        const modal = document.getElementById('orgModal');
        if (modal) {
            modal.style.display = 'none';
            // Restore body scroll
            document.body.style.overflow = '';
            document.body.style.position = '';
            document.body.style.width = '';
            document.body.style.height = '';
        }
    }

    // Delete Modal Functions
    function openDeleteModal(org) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        const orgNameElement = document.getElementById('deleteOrgName');

        if (!modal || !form) return;

        // Prevent body scroll and ensure modal is on top
        document.body.style.overflow = 'hidden';
        document.body.style.position = 'fixed';
        document.body.style.width = '100%';
        document.body.style.height = '100%';
        
        // Show modal with proper centering
        modal.style.display = 'flex';
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.right = '0';
        modal.style.bottom = '0';

        // Set form action
        form.action = `${orgBaseUrl}/${org.id}`;
        
        // Set organization name
        if (orgNameElement) {
            orgNameElement.textContent = `"${org.name}"`;
        }
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        if (modal) {
        modal.style.display = 'none';
            // Restore body scroll
            document.body.style.overflow = '';
            document.body.style.position = '';
            document.body.style.width = '';
            document.body.style.height = '';
        }
    }

    // Close modal on background click
    document.addEventListener('click', function(e) {
        const orgModal = document.getElementById('orgModal');
        const deleteModal = document.getElementById('deleteModal');
        
        // Close org modal if clicking backdrop
        if (orgModal && (e.target === orgModal || e.target.classList.contains('org-modal'))) {
            closeOrgModal();
        }
        
        // Close delete modal if clicking backdrop
        if (deleteModal && (e.target === deleteModal || e.target.classList.contains('org-modal'))) {
            closeDeleteModal();
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const orgModal = document.getElementById('orgModal');
            if (orgModal && orgModal.style.display === 'flex') {
                closeOrgModal();
            }
            const deleteModal = document.getElementById('deleteModal');
            if (deleteModal && deleteModal.style.display === 'flex') {
                closeDeleteModal();
            }
            const successModal = document.getElementById('successModal');
            if (successModal && successModal.style.display === 'flex') {
                closeSuccessModal();
            }
        }
    });

    // Success Modal Functions
    function closeSuccessModal() {
        const successModal = document.getElementById('successModal');
        if (successModal) {
            successModal.style.display = 'none';
            // Restore body scroll
            document.body.style.overflow = '';
            document.body.style.position = '';
            document.body.style.width = '';
            document.body.style.height = '';
        }
    }

    // Auto-close success modal after 3 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const successModal = document.getElementById('successModal');
        if (successModal) {
            // Check if modal is visible (check inline style or computed style)
            const inlineDisplay = successModal.style.display;
            const computedDisplay = window.getComputedStyle(successModal).display;
            const isVisible = inlineDisplay === 'flex' || 
                             inlineDisplay === 'flex !important' ||
                             inlineDisplay.includes('flex') ||
                             computedDisplay === 'flex';
            
            if (isVisible) {
                // Prevent body scroll when success modal is shown
                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.width = '100%';
                document.body.style.height = '100%';
                
                // Auto-close after 3 seconds
                setTimeout(function() {
                    closeSuccessModal();
                }, 3000);
            }
        }
    });

    // Close success modal on background click
    document.addEventListener('click', function(e) {
        const successModal = document.getElementById('successModal');
        if (successModal && e.target === successModal) {
            closeSuccessModal();
        }
    });
</script>

<style>
    .page-wrapper {
        padding-top: 60px;
    }

    .orgs-box {
        width: 80%;
        margin: 0 auto 40px;
        margin-top: 20px;
        background: var(--card-bg, #fff);
        border-radius: 14px;
        padding: 30px;
        transition: background 0.3s, color 0.3s;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    }

    .orgs-box .manage-title {
        margin-bottom: 10px;
    }

    .orgs-box .rubric-header-nav {
        margin-bottom: 10px;
    }


    /* Pagination */
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 18px;
        flex-wrap: wrap;
    }

    .page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: 1px solid var(--border-color, #ddd);
        background: var(--card-bg, #fff);
        color: var(--text-color, #333);
        text-decoration: none;
        transition: all .15s ease;
    }

    .page-btn:hover {
        transform: scale(1.05);
        background: rgba(126, 3, 8, 0.1);
        color: #7E0308;
        border-color: #7E0308;
    }

    .page-btn.active {
        background: #7E0308;
        color: #fff;
        border-color: #7E0308;
    }

    .page-btn.disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    /* Dark-mode adjustments if you use body.dark-mode */
    body.dark-mode .orgs-box {
        background: #1f1f1f;
        color: #f0f0f0;
        box-shadow: 0 0 12px rgba(255, 255, 255, 0.03);
    }

    body.dark-mode .page-btn {
        background: #2b2b2b;
        border-color: #444;
        color: #eaeaea;
    }

    body.dark-mode .page-btn.active {
        background: #7E0308;
        border-color: #7E0308;
    }

    body.dark-mode .page-btn:hover {
        background: rgba(126, 3, 8, 0.2);
        color: #fff;
        border-color: #7E0308;
    }

    .btn-back-maroon {
        background-color: #7E0308;
        color: #fff;
        border: 1px solid #7E0308;
        border-radius: 6px;
        padding: 8px 16px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        font-size: 14px;
        font-weight: 500;
    }

    .btn-back-maroon:hover {
        background-color: #5a0206;
        border-color: #5a0206;
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(126, 3, 8, 0.3);
        text-decoration: none;
    }

    .btn-back-maroon:active {
        transform: translateY(0);
        box-shadow: 0 1px 2px rgba(126, 3, 8, 0.3);
    }

    .btn-back-maroon i {
        font-size: 14px;
    }

    body.dark-mode .btn-back-maroon {
        background-color: #7E0308;
        border-color: #7E0308;
    }

    body.dark-mode .btn-back-maroon:hover {
        background-color: #9a040a;
        border-color: #9a040a;
    }

    .btn-search-maroon {
        background-color: #7E0308;
        color: #fff;
        border: 1px solid #7E0308;
        border-radius: 6px;
        padding: 8px 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .btn-search-maroon:hover {
        background-color: #5a0206;
        border-color: #5a0206;
        transform: none;
        box-shadow: none;
    }

    .btn-search-maroon:active {
        background-color: #4a0105;
        border-color: #4a0105;
        transform: none;
        box-shadow: none;
    }

    .btn-search-maroon i {
        font-size: 16px;
    }

    body.dark-mode .btn-search-maroon {
        background-color: #7E0308;
        border-color: #7E0308;
    }

    body.dark-mode .btn-search-maroon:hover {
        background-color: #9a040a;
        border-color: #9a040a;
    }

    /* Filter Section Styling */
    .filter-section {
        background: var(--card-bg, #f9fafb);
        padding: 20px;
        border-radius: 8px;
        border: 1px solid var(--border-color, #e5e7eb);
    }

    body.dark-mode .filter-section {
        background: #2a2a2a;
        border-color: #444;
    }

    .filter-section .filter-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .filter-section .filter-item label {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-color, #333);
        margin-bottom: 0;
    }

    .filter-section .filter-row {
        align-items: flex-end !important;
    }

    .filter-section .filter-actions {
        align-items: flex-end;
        display: flex;
        gap: 8px;
    }

    .filter-section .filter-actions button,
    .filter-section .filter-actions a {
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    body.dark-mode .filter-section .filter-item label {
        color: #f0f0f0;
    }

    .filter-select {
        padding: 8px 12px;
        border: 1px solid #ced4da;
        border-radius: 6px;
        background: #fff;
        color: #495057;
        font-size: 14px;
        width: 180px;
        cursor: pointer;
    }

    .filter-select:focus {
        outline: none;
        border-color: #7b0000;
        box-shadow: 0 0 0 2px rgba(123, 0, 0, 0.2);
    }

    body.dark-mode .filter-select {
        background: #2a2a2a;
        border-color: #555;
        color: #f0f0f0;
    }

    body.dark-mode .filter-select:focus {
        border-color: #F9BD3D;
        box-shadow: 0 0 0 2px rgba(249, 189, 61, 0.2);
    }

    .filter-input {
        padding: 8px 12px;
        border: 1px solid #ced4da;
        border-radius: 6px;
        background: #fff;
        color: #495057;
        font-size: 14px;
        width: 180px;
        height: 38px;
        box-sizing: border-box;
    }

    .filter-input:focus {
        outline: none;
        border-color: #7b0000;
        box-shadow: 0 0 0 2px rgba(123, 0, 0, 0.2);
    }

    body.dark-mode .filter-input {
        background: #2a2a2a;
        border-color: #555;
        color: #f0f0f0;
    }

    body.dark-mode .filter-input:focus {
        border-color: #F9BD3D;
        box-shadow: 0 0 0 2px rgba(249, 189, 61, 0.2);
    }

    /* Search input with attached button */
    .search-input-group {
        display: flex;
        align-items: center;
        gap: 0;
    }

    .search-input-with-btn {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
        border-right: none !important;
        width: 180px;
    }

    .search-btn-attached {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        height: 38px !important;
        width: 38px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 38px !important;
    }

    .search-btn-attached i {
        font-size: 16px !important;
        line-height: 1 !important;
    }

    .search-input-with-btn:focus {
        border-right: 1px solid #7b0000 !important;
    }

    body.dark-mode .search-input-with-btn:focus {
        border-right: 1px solid #F9BD3D !important;
    }

    /* Add Organization button styling - match Clear button style */
    .filter-section .btn-export-enhanced {
        background: #7E0308 !important;
        color: #fff !important;
        border: 1px solid #7E0308 !important;
        padding: 10px 20px !important;
        border-radius: 6px !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        text-decoration: none !important;
        transition: all 0.3s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        height: 38px !important;
        box-shadow: none !important;
        width: auto !important;
        min-width: auto !important;
        position: relative !important;
        overflow: visible !important;
        white-space: nowrap !important;
        line-height: 1.2 !important;
    }

    .filter-section .btn-export-enhanced i {
        font-size: 13px !important;
        line-height: 1 !important;
    }

    .filter-section .btn-export-enhanced::before {
        display: none !important;
    }

    .filter-section .btn-export-enhanced:hover {
        background-color: #5a0206 !important;
        border-color: #5a0206 !important;
        color: #fff !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 8px rgba(126, 3, 8, 0.3) !important;
        text-decoration: none !important;
    }

    .filter-section .btn-export-enhanced:active {
        background-color: #4a0105 !important;
        border-color: #4a0105 !important;
        transform: translateY(0) !important;
        box-shadow: 0 2px 4px rgba(126, 3, 8, 0.2) !important;
    }

    body.dark-mode .filter-section .btn-export-enhanced {
        background: #7E0308 !important;
        border-color: #7E0308 !important;
    }

    body.dark-mode .filter-section .btn-export-enhanced:hover {
        background-color: #9a040a !important;
        border-color: #9a040a !important;
    }

    /* Modal Styling */
    .org-modal {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        min-width: 100vw !important;
        min-height: 100vh !important;
        max-width: 100vw !important;
        max-height: 100vh !important;
        background: rgba(0, 0, 0, 0.5) !important;
        backdrop-filter: blur(5px) !important;
        -webkit-backdrop-filter: blur(5px) !important;
        z-index: 10000 !important;
        display: none !important;
        align-items: center !important;
        justify-content: center !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        margin: 0 !important;
        padding: 0 !important;
        box-sizing: border-box !important;
    }

    .org-modal[style*="flex"],
    .org-modal[style*="display: flex"] {
        display: flex !important;
    }

    .org-modal-dialog {
        max-width: 1000px !important;
        width: 1000px !important;
        margin: auto !important;
        position: relative !important;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        z-index: 10001 !important;
    }

    @media (max-width: 1100px) {
        .org-modal-dialog {
            max-width: 95% !important;
            width: 95% !important;
            padding: 15px;
        }
    }

    @media (max-height: 700px) {
        .org-modal-content {
            max-height: 90vh !important;
            overflow-y: auto !important;
        }
    }

    .org-modal-content {
        width: 100% !important;
        height: 550px !important;
        padding: 40px !important;
        overflow: visible !important;
        background: var(--card-bg, #fff) !important;
        position: relative;
        margin: 0;
        display: flex;
        flex-direction: column;
    }

    .org-modal-content form {
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: visible;
        flex: 1;
        min-height: 0;
    }

    .org-modal-content .form-group {
        flex-shrink: 0;
    }

    .org-modal-content .button-group {
        margin-top: auto !important;
        flex-shrink: 0 !important;
        display: flex !important;
        justify-content: flex-end !important;
        gap: 12px !important;
        padding-top: 20px !important;
        border-top: 1px solid #e5e7eb !important;
        visibility: visible !important;
        opacity: 1 !important;
        position: relative !important;
    }

    .org-modal-content .button-group .btn {
        min-width: 120px !important;
        padding: 12px 24px !important;
        font-weight: 600 !important;
        font-size: 15px !important;
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    body.dark-mode .org-modal-content .button-group {
        border-top-color: #444 !important;
    }

    .btn-close-modal {
        background: none !important;
        border: none !important;
        color: #7E0308;
        font-size: 24px;
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .btn-close-modal:hover {
        color: #5a0206;
        transform: scale(1.1);
    }

    .btn-close-modal:active {
        transform: scale(0.95);
    }

    body.dark-mode .btn-close-modal {
        color: #f0f0f0;
    }

    body.dark-mode .btn-close-modal:hover {
        color: #ff6b6b;
    }

    .org-modal-content .form-group {
        margin-bottom: 1.5rem;
    }

    .org-modal-content .form-group label {
        font-weight: 600;
        margin-bottom: 10px;
        color: var(--text-color, #333);
        font-size: 15px;
        display: block;
    }

    .org-modal-content .form-control {
        width: 100%;
        padding: 12px 16px;
        font-size: 15px;
        border: 1px solid #ced4da;
        border-radius: 6px;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
        background: var(--card-bg, #fff);
    }

    .org-modal-content .form-control:focus {
        outline: none;
        border-color: #7E0308;
        box-shadow: 0 0 0 3px rgba(126, 3, 8, 0.1);
    }

    /* Disable textarea resizing */
    .org-modal-content textarea.form-control {
        resize: none !important;
        min-height: 100px;
        max-height: 150px;
        overflow-y: auto;
    }

    /* Hide scrollbar for textarea but keep functionality */
    .org-modal-content textarea.form-control::-webkit-scrollbar {
        width: 6px;
    }

    .org-modal-content textarea.form-control::-webkit-scrollbar-track {
        background: transparent;
    }

    .org-modal-content textarea.form-control::-webkit-scrollbar-thumb {
        background: rgba(126, 3, 8, 0.3);
        border-radius: 3px;
    }

    .org-modal-content textarea.form-control::-webkit-scrollbar-thumb:hover {
        background: rgba(126, 3, 8, 0.5);
    }

    .org-modal-content .form-title {
        color: #7E0308;
        font-weight: 700;
        font-size: 26px;
        margin-bottom: 2rem;
        padding-bottom: 0.5rem;
    }


    body.dark-mode .org-modal-content {
        background: #1f1f1f !important;
        color: #f0f0f0;
    }

    body.dark-mode .org-modal-content .form-group label {
        color: #f0f0f0;
    }

    body.dark-mode .org-modal-content .form-control {
        background: #2a2a2a;
        border-color: #555;
        color: #f0f0f0;
    }

    body.dark-mode .org-modal-content .form-control:focus {
        border-color: #7E0308;
        box-shadow: 0 0 0 2px rgba(126, 3, 8, 0.3);
    }

    body.dark-mode .org-modal-content textarea.form-control::-webkit-scrollbar-thumb {
        background: rgba(126, 3, 8, 0.5);
    }

    body.dark-mode .org-modal-content textarea.form-control::-webkit-scrollbar-thumb:hover {
        background: rgba(126, 3, 8, 0.7);
    }

    body.dark-mode .org-modal-content .button-group {
        border-top-color: #444;
    }

    /* Success Modal Styling */
    .success-modal {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        min-width: 100vw !important;
        min-height: 100vh !important;
        max-width: 100vw !important;
        max-height: 100vh !important;
        background: rgba(0, 0, 0, 0.5) !important;
        backdrop-filter: blur(5px) !important;
        -webkit-backdrop-filter: blur(5px) !important;
        z-index: 10000 !important;
        display: none !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 !important;
        padding: 0 !important;
        box-sizing: border-box !important;
    }

    .success-modal[style*="flex"] {
        display: flex !important;
    }

    .success-modal-content {
        background: #fff;
        border-radius: 12px;
        padding: 0;
        max-width: 380px;
        width: 90%;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        animation: successModalSlideIn 0.3s ease-out;
    }

    @keyframes successModalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-20px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .success-modal-header {
        display: flex;
        justify-content: flex-end;
        align-items: flex-start;
        padding: 15px 15px 0 0;
        border-bottom: none;
        position: relative;
    }

    .success-modal-body {
        padding: 45px 35px 40px 35px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0;
    }

    .success-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 90px;
        height: 90px;
        background: rgba(209, 250, 229, 0.6);
        border-radius: 50%;
        color: #059669;
        font-size: 48px;
        order: 1;
        flex-shrink: 0;
        margin-bottom: 25px;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.15);
    }

    .success-message {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #059669;
        order: 2;
        line-height: 1.4;
    }

    .success-close-btn {
        background: none !important;
        border: none !important;
        color: #6b7280;
        font-size: 20px;
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s ease;
        position: absolute;
        top: 15px;
        right: 15px;
    }

    .success-close-btn:hover {
        background: #f3f4f6;
        color: #374151;
    }


    body.dark-mode .success-modal-content {
        background: #1f1f1f;
    }

    body.dark-mode .success-modal-header {
        border-bottom-color: #444;
    }

    body.dark-mode .success-icon {
        background: rgba(16, 185, 129, 0.25);
        color: #10b981;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
    }

    body.dark-mode .success-close-btn {
        color: #9ca3af;
    }

    body.dark-mode .success-close-btn:hover {
        background: #2a2a2a;
        color: #f0f0f0;
    }

    body.dark-mode .success-message {
        color: #10b981;
    }

    /* Delete Confirmation Modal Styling */
    .delete-modal-content {
        width: 100% !important;
        max-width: 450px !important;
        padding: 24px !important;
        background: var(--card-bg, #fff) !important;
        position: relative;
        margin: 0;
    }

    .delete-modal-body {
        text-align: center;
        padding: 16px 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
    }

    .delete-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 64px;
        height: 64px;
        background: #fee2e2;
        border-radius: 50%;
        color: #dc2626;
        font-size: 32px;
        flex-shrink: 0;
    }

    .delete-message {
        margin: 0;
        font-size: 15px;
        font-weight: 500;
        color: var(--text-color, #333);
        line-height: 1.5;
    }

    .delete-org-name {
        margin: 0;
        font-size: 17px;
        font-weight: 700;
        color: #dc2626;
        word-break: break-word;
    }

    .delete-modal-content .button-group {
        margin-top: 24px !important;
        display: flex !important;
        justify-content: center !important;
        gap: 12px !important;
        padding-top: 20px !important;
        border-top: 1px solid #e5e7eb !important;
    }

    .delete-modal-content .button-group .btn {
        min-width: 100px !important;
        padding: 10px 20px !important;
        font-weight: 600 !important;
        font-size: 14px !important;
    }

    .delete-modal-content .button-group .btn-danger {
        background-color: #dc2626 !important;
        border-color: #dc2626 !important;
        color: #fff !important;
    }

    .delete-modal-content .button-group .btn-danger:hover {
        background-color: #b91c1c !important;
        border-color: #b91c1c !important;
    }

    body.dark-mode .delete-modal-content {
        background: #1f1f1f !important;
    }

    body.dark-mode .delete-icon {
        background: rgba(220, 38, 38, 0.2);
        color: #f87171;
    }

    body.dark-mode .delete-message {
        color: #f0f0f0;
    }

    body.dark-mode .delete-modal-content .button-group {
        border-top-color: #444 !important;
    }
</style>
@endsection