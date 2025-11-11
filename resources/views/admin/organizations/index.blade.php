@extends('layouts.app')

@section('title', 'Organizations Management')

@section('content')
<div class="page-wrapper">


    <div class="manage-container orgs-box">
        <h2 class="manage-title">Organizations Management</h2>

        <!-- Back button -->
        <div class="rubric-header-nav mb-3">
            <a href="{{ route('admin.profile') }}" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        <!-- Controls -->
        <div class="controls d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <div class="controls-left mb-2">
                <button type="button" class="btn-export-enhanced" onclick="openOrgModal()">
                    <i class="fas fa-plus"></i> Add Organization
                </button>
            </div>

            <div class="controls-right d-flex align-items-center gap-2 mb-2">
                <form method="GET" action="{{ route('admin.organizations.index') }}" class="d-flex align-items-center">
                    <input type="text" name="q" placeholder="Search..." value="{{ request('q') }}"
                        class="form-control search-input" style="width: 180px;">

                    <select name="cluster_filter" class="form-select ms-2" style="width: 180px;"
                        onchange="this.form.submit()">
                        <option value="">All Clusters</option>
                        @foreach ($clusters as $cluster)
                        <option value="{{ $cluster->id }}" {{ request('cluster_filter') == $cluster->id ? 'selected' : '' }}>
                            {{ $cluster->name }}
                        </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-primary ms-2"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="table-wrap compact-table">
            <table class="manage-table">
                <thead>
                    <tr>
                        <th>Organization Name</th>
                        <th>Cluster</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($organizations as $org)
                    <tr>
                        <td>{{ $org->name }}</td>
                        <td>{{ $org->cluster->name ?? '—' }}</td>
                        <td>{{ Str::limit($org->description, 60) ?? '—' }}</td>
                        <td class="action-cell">
                            <div class="action-buttons">
                                <button class="action-btn btn-edit" title="Edit" onclick='openOrgModal(@json($org))'>
                                    <i class="fas fa-edit"></i>
                                </button>

                                <form action="{{ route('admin.organizations.destroy', $org) }}" method="POST"
                                    onsubmit="return confirm('Delete this organization?');" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn btn-delete" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">No organizations found.</td>
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

<!-- Modal -->
<div id="orgModal" class="modal" style="display:none;">
    <div class="modal-dialog" style="max-width:600px;">
        <div class="modal-content p-4 rounded-3 shadow">
            <h3 id="orgModalTitle" class="form-title mb-3">Add Organization</h3>

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

        // show modal
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

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
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // optional: close modal on background click (improve UX)
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('orgModal');
        if (!modal) return;
        if (e.target === modal) closeOrgModal();
    });
</script>

<style>
    .orgs-box {
        width: 80%;
        margin: 0 auto 40px;
        background: var(--card-bg, #fff);
        border-radius: 14px;
        padding: 30px;
        transition: background 0.3s, color 0.3s;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
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
        background: var(--primary-light, #e8f0fe);
        color: var(--primary, #007bff);
    }

    .page-btn.active {
        background: var(--primary, #007bff);
        color: #fff;
        border-color: var(--primary, #007bff);
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
        background: var(--primary-dark, #3498db);
        border-color: var(--primary-dark, #3498db);
    }
</style>
@endsection