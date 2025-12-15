@extends('layouts.app')

@section('title', 'Programs Management')

@section('content')
    <div class="container">
        @include('partials.sidebar')

        <main class="main-content">
            <div class="page-header">
                <h1>Programs Management</h1>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Filter Section -->
            <div class="filter-section mb-4">
                <form method="GET" action="{{ route('admin.programs.index') }}" id="filterForm">
                    <div class="filter-row d-flex justify-content-between align-items-end flex-wrap gap-2">
                        <div class="d-flex align-items-end gap-2 flex-wrap">
                            <div class="filter-item">
                                <label for="college_filter">College</label>
                                <select name="college_filter" id="college_filter" class="filter-select">
                                    <option value="">All Colleges</option>
                                    @foreach ($colleges as $college)
                                        <option value="{{ $college->id }}" {{ request('college_filter') == $college->id ? 'selected' : '' }}>
                                            {{ $college->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="filter-item">
                                <label for="q">Search</label>
                                <div class="search-input-group">
                                    <input type="text" name="q" id="q" class="filter-input search-input-with-btn"
                                        placeholder="Search by name, code, or college..." value="{{ request('q') }}">
                                    <button type="submit" class="btn-search-maroon search-btn-attached">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="filter-actions d-flex align-items-center gap-2">
                            <button type="button" class="btn-export-enhanced" onclick="openProgramModal()">
                                <i class="fas fa-plus"></i> Add Program
                            </button>
                            <a href="{{ route('admin.programs.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="submissions-table-container">
                <table class="table submissions-table">
                    <thead>
                        <tr>
                            <th>Program Name</th>
                            <th>College</th>
                            <th>Code</th>
                            <th>Majors Count</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($programs as $program)
                            <tr>
                                <td>{{ $program->name }}</td>
                                <td>{{ $program->college->name ?? '—' }}</td>
                                <td>{{ $program->code ?? '—' }}</td>
                                <td>{{ $program->majors()->count() }}</td>
                                <td>
                                    <div class="action-buttons-group">
                                        <button class="btn-edit" title="Edit" data-program-id="{{ $program->id }}"
                                            data-program-name="{{ $program->name }}" 
                                            data-program-college-id="{{ $program->college_id }}"
                                            data-program-code="{{ $program->code ?? '' }}"
                                            onclick="openProgramModalFromButton(this)">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <button type="button" class="btn-delete" title="Delete" data-program-id="{{ $program->id }}"
                                            data-program-name="{{ $program->name }}"
                                            onclick="openDeleteModalFromButton(this)">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No programs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($programs->hasPages())
                <div class="pagination-wrapper mt-4">
                    @if ($programs->onFirstPage())
                        <span class="page-btn disabled"><i class="fas fa-chevron-left"></i></span>
                    @else
                        <a href="{{ $programs->previousPageUrl() }}" class="page-btn">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    @endif

                    @foreach ($programs->links()->elements ?? [] as $element)
                        @if (is_string($element))
                            <span class="page-btn disabled">{{ $element }}</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $programs->currentPage())
                                    <span class="page-btn active">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    @if ($programs->hasMorePages())
                        <a href="{{ $programs->nextPageUrl() }}" class="page-btn">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <span class="page-btn disabled"><i class="fas fa-chevron-right"></i></span>
                    @endif
                </div>

                <div class="text-center small text-muted mt-2">
                    Showing {{ $programs->firstItem() ?? 0 }} – {{ $programs->lastItem() ?? 0 }}
                    of {{ $programs->total() }} programs
                </div>
            @endif

        </main>
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
                    <p class="delete-message">Are you sure you want to delete this program? This action cannot be undone.</p>
                    <p class="delete-org-name" id="deleteProgramName"></p>
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

    <!-- Add/Edit Program Modal -->
    <div id="programModal" class="org-modal" style="display: none !important;">
        <div class="modal-dialog org-modal-dialog">
            <div class="modal-content org-modal-content p-4 rounded-3 shadow">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 id="programModalTitle" class="form-title mb-0">Add Program</h3>
                    <button type="button" class="btn-close-modal" onclick="closeProgramModal()" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="programForm" method="POST" action="{{ route('admin.programs.store') }}">
                    @csrf
                    <input type="hidden" id="program_id" name="id">

                    <div class="form-fields-container">
                        <div class="form-group">
                            <label for="name">Program Name <span class="required">*</span></label>
                            <input type="text" id="name" name="name" class="form-control form-control-lg" required>
                        </div>

                        <div class="form-group">
                            <label for="college_id">College <span class="required">*</span></label>
                            <select id="college_id" name="college_id" class="form-control form-control-lg" required>
                                <option value="">-- Select College --</option>
                                @foreach ($colleges as $college)
                                    <option value="{{ $college->id }}">{{ $college->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="code">Code</label>
                            <input type="text" id="code" name="code" class="form-control form-control-lg" placeholder="Optional">
                        </div>

                        <div class="form-group">
                            <label for="major_name">Major <span class="required">*</span></label>
                            <input type="text" id="major_name" name="major_name" class="form-control form-control-lg" placeholder="Enter major name" required>
                            <small class="form-text text-muted">This major will be associated with the program.</small>
                        </div>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="save-btn btn btn-primary">Save</button>
                        <button type="button" class="cancel-btn btn btn-secondary" onclick="closeProgramModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="{{ asset('css/pending-submissions.css') }}">

    <script>
        const programBaseUrl = "{{ url('admin/programs') }}";

        function openProgramModal(program = null) {
            const modal = document.getElementById('programModal');
            const title = document.getElementById('programModalTitle');
            const form = document.getElementById('programForm');

            if (!modal) return;

            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.width = '100%';
            document.body.style.height = '100%';

            modal.style.display = 'flex';
            modal.style.position = 'fixed';
            modal.style.top = '0';
            modal.style.left = '0';
            modal.style.right = '0';
            modal.style.bottom = '0';

            const existingMethod = form.querySelector('input[name="_method"]');
            if (existingMethod) existingMethod.remove();

            const nameInput = document.getElementById('name');
            const collegeSelect = document.getElementById('college_id');
            const codeInput = document.getElementById('code');
            const majorInput = document.getElementById('major_name');

            if (program) {
                title.textContent = 'Edit Program';
                form.action = `${programBaseUrl}/${program.id}`;

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                form.appendChild(methodInput);

                document.getElementById('program_id').value = program.id ?? '';
                if (nameInput) nameInput.value = program.name ?? '';
                if (collegeSelect) collegeSelect.value = program.college_id ?? '';
                if (codeInput) codeInput.value = program.code ?? '';
                if (majorInput) majorInput.value = '';
                if (majorInput) majorInput.required = false;
            } else {
                title.textContent = 'Add Program';
                form.action = '{{ route("admin.programs.store") }}';
                form.reset();
                document.getElementById('program_id').value = '';
                if (collegeSelect) collegeSelect.value = '';
                if (majorInput) majorInput.required = true;
            }
        }

        function openProgramModalFromButton(button) {
            const program = {
                id: button.getAttribute('data-program-id'),
                name: button.getAttribute('data-program-name'),
                college_id: button.getAttribute('data-program-college-id'),
                code: button.getAttribute('data-program-code'),
            };
            openProgramModal(program);
        }

        function closeProgramModal() {
            const modal = document.getElementById('programModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
                document.body.style.height = '';
            }
        }

        function openDeleteModal(program) {
            const modal = document.getElementById('deleteModal');
            const form = document.getElementById('deleteForm');
            const programNameElement = document.getElementById('deleteProgramName');

            if (!modal || !form) return;

            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.width = '100%';
            document.body.style.height = '100%';

            modal.style.display = 'flex';
            modal.style.position = 'fixed';
            modal.style.top = '0';
            modal.style.left = '0';
            modal.style.right = '0';
            modal.style.bottom = '0';

            form.action = `${programBaseUrl}/${program.id}`;
            if (programNameElement) {
                programNameElement.textContent = program.name;
            }
        }

        function openDeleteModalFromButton(button) {
            const program = {
                id: button.getAttribute('data-program-id'),
                name: button.getAttribute('data-program-name'),
            };
            openDeleteModal(program);
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
                document.body.style.height = '';
            }
        }

        function closeSuccessModal() {
            const modal = document.getElementById('successModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        }

        // Auto-close success modal after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const successModal = document.getElementById('successModal');
            if (successModal && successModal.style.display === 'flex') {
                setTimeout(() => {
                    closeSuccessModal();
                }, 3000);
            }
        });
    </script>

    <style>
        /* Reuse organization modal styles */
        .org-modal {
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

        .org-modal[style*="flex"] {
            display: flex !important;
        }

        .org-modal-dialog {
            width: 100%;
            max-width: 600px;
            margin: auto !important;
            position: relative !important;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            z-index: 10001 !important;
        }

        .org-modal-content {
            width: 100% !important;
            max-height: 90vh !important;
            min-height: 400px !important;
            padding: 40px !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            background: var(--card-bg, #fff) !important;
            position: relative;
            margin: 0;
            display: flex;
            flex-direction: column;
            border-radius: 0;
        }

        .org-modal-content form {
            display: flex;
            flex-direction: column;
            min-height: 0;
            flex: 1;
            overflow: hidden;
        }

        .org-modal-content .form-fields-container {
            width: 100%;
            max-width: 800px;
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
            overflow-y: auto;
        }

        .org-modal-content .form-group {
            flex-shrink: 0;
            width: 100%;
            margin-bottom: 1.5rem;
        }

        .org-modal-content .button-group {
            margin-top: auto !important;
            flex-shrink: 0 !important;
            display: flex !important;
            justify-content: flex-end !important;
            gap: 12px !important;
            padding-top: 20px !important;
            border-top: 1px solid #e5e7eb !important;
            position: sticky !important;
            bottom: 0 !important;
            background: var(--card-bg, #fff) !important;
            z-index: 10 !important;
        }

        .org-modal-content .button-group .btn {
            min-width: 120px !important;
            padding: 12px 24px !important;
            font-weight: 600 !important;
            font-size: 15px !important;
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

        .org-modal-content .form-group label {
            font-weight: bold;
            margin-bottom: 10px;
            color: var(--text-color, #333);
            font-size: 15px;
            display: block;
        }

        .org-modal-content .form-control {
            width: 100%;
            padding: 14px 18px;
            font-size: 16px;
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

        .org-modal-content .form-title {
            color: #7E0308;
            font-weight: 700;
            font-size: 26px;
        }

        .delete-modal-content {
            width: 100% !important;
            max-width: 450px !important;
            padding: 24px !important;
            background: var(--card-bg, #fff) !important;
            position: relative;
            margin: 0;
            border-radius: 0;
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

        .success-modal {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background: rgba(0, 0, 0, 0.5) !important;
            backdrop-filter: blur(5px) !important;
            -webkit-backdrop-filter: blur(5px) !important;
            z-index: 10000 !important;
            display: none !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .success-modal[style*="flex"] {
            display: flex !important;
        }

        .success-modal-content {
            background: #fff;
            border-radius: 0;
            padding: 0;
            max-width: 380px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
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
            margin-bottom: 25px;
        }

        .success-message {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #059669;
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
            position: absolute;
            top: 15px;
            right: 15px;
        }

        body.dark-mode .org-modal-content,
        body.dark-mode .delete-modal-content,
        body.dark-mode .success-modal-content {
            background: #1f1f1f !important;
            color: #f0f0f0;
        }

        body.dark-mode .org-modal-content .form-group label,
        body.dark-mode .org-modal-content .form-control {
            color: #f0f0f0;
        }

        body.dark-mode .org-modal-content .form-control {
            background: #2a2a2a;
            border-color: #555;
        }

        body.dark-mode .org-modal-content .form-control:focus {
            border-color: #7E0308;
            box-shadow: 0 0 0 2px rgba(126, 3, 8, 0.3);
        }
    </style>
@endsection

