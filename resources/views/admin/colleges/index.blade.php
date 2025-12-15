@extends('layouts.app')

@section('title', 'Academic Management')

@section('content')
    <div class="container">
        @include('partials.sidebar')

        <main class="main-content">
            <div class="page-header">
                <h1>Academic Management</h1>
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
                <form method="GET" action="{{ route('admin.colleges.index') }}" id="filterForm">
                    <div class="filter-row d-flex justify-content-between align-items-end flex-wrap gap-2">
                        <div class="d-flex align-items-end gap-2 flex-wrap">
                            <div class="filter-item">
                                <label for="q">Search</label>
                                <div class="search-input-group">
                                    <input type="text" name="q" id="q" class="filter-input search-input-with-btn"
                                        placeholder="Search by name or code..." value="{{ request('q') }}">
                                    <button type="submit" class="btn-search-maroon search-btn-attached">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="filter-actions d-flex align-items-center gap-2">
                            <button type="button" class="btn-export-enhanced" onclick="openCollegeModal()">
                                <i class="fas fa-plus"></i> Add College
                            </button>
                            <button type="button" class="btn-export-enhanced" onclick="openProgramModal()">
                                <i class="fas fa-plus"></i> Add Program
                            </button>
                            <a href="{{ route('admin.colleges.index') }}" class="btn btn-secondary">
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
                            <th>College Name</th>
                            <th>Code</th>
                            <th>Programs</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($colleges as $college)
                            <tr>
                                <td>{{ $college->name }}</td>
                                <td>{{ $college->code ?? '—' }}</td>
                                <td>
                                    @if($college->programs->count() > 0)
                                        <div class="programs-list">
                                            @foreach($college->programs as $program)
                                                <span class="program-badge">{{ $program->name }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons-group">
                                        <button class="btn-edit" title="Edit" data-college-id="{{ $college->id }}"
                                            data-college-name="{{ $college->name }}" 
                                            data-college-code="{{ $college->code ?? '' }}"
                                            onclick="openCollegeModalFromButton(this)">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <button type="button" class="btn-delete" title="Delete" data-college-id="{{ $college->id }}"
                                            data-college-name="{{ $college->name }}"
                                            onclick="openDeleteModalFromButton(this)">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No colleges found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($colleges->hasPages())
                <div class="pagination-wrapper mt-4">
                    @if ($colleges->onFirstPage())
                        <span class="page-btn disabled"><i class="fas fa-chevron-left"></i></span>
                    @else
                        <a href="{{ $colleges->previousPageUrl() }}" class="page-btn">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    @endif

                    @foreach ($colleges->links()->elements ?? [] as $element)
                        @if (is_string($element))
                            <span class="page-btn disabled">{{ $element }}</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $colleges->currentPage())
                                    <span class="page-btn active">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    @if ($colleges->hasMorePages())
                        <a href="{{ $colleges->nextPageUrl() }}" class="page-btn">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <span class="page-btn disabled"><i class="fas fa-chevron-right"></i></span>
                    @endif
                </div>

                <div class="text-center small text-muted mt-2">
                    Showing {{ $colleges->firstItem() ?? 0 }} – {{ $colleges->lastItem() ?? 0 }}
                    of {{ $colleges->total() }} colleges
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
                    <p class="delete-message">Are you sure you want to delete this college? This action cannot be undone.</p>
                    <p class="delete-org-name" id="deleteCollegeName"></p>
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

    <!-- Success Modal - Moved outside container for proper viewport coverage -->
    @if (session('success'))
        <div id="successModal" class="success-modal" style="display: flex !important; position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: 100vw !important; height: 100vh !important; z-index: 99999 !important;">
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

    <!-- Add/Edit College Modal -->
    <div id="collegeModal" class="org-modal" style="display: none !important;">
        <div class="modal-dialog org-modal-dialog">
            <div class="modal-content org-modal-content p-4 rounded-3 shadow">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 id="collegeModalTitle" class="form-title mb-0">Edit College</h3>
                    <button type="button" class="btn-close-modal" onclick="closeCollegeModal()" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="collegeForm" method="POST" action="{{ route('admin.colleges.store') }}">
                    @csrf
                    <input type="hidden" id="college_id" name="id">

                    <div class="form-fields-container">
                        {{-- Note at the top --}}
                        <div class="alert alert-info mb-4" id="editNote" style="display: none;">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> Edit program names and codes. Use the "Add Program" button in the main page to add new programs.
                        </div>

                        {{-- College Name (required) --}}
                        <div class="form-group">
                            <label for="name">College Name <span class="required">*</span></label>
                            <input type="text" id="name" name="name" class="form-control form-control-lg" required>
                        </div>

                        {{-- Code --}}
                        <div class="form-group">
                            <label for="code">Code</label>
                            <input type="text" id="code" name="code" class="form-control form-control-lg" placeholder="Optional">
                        </div>

                        {{-- Programs Section (only shown when editing) --}}
                        <div id="editProgramsGroup" style="display: none;">
                            <div class="form-group programs-section">
                                <label>Programs</label>
                                <div id="programsEditContainer" class="programs-edit-container">
                                    {{-- Will be populated by JavaScript --}}
                                </div>
                            </div>
                        </div>

                        {{-- Add College Programs Section (only shown when adding) --}}
                        <div class="form-group" id="programsGroup" style="display: none;">
                            <label for="programsInput">Programs</label>
                            <div class="programs-tags-container">
                                <div id="programsTags" class="programs-tags"></div>
                                <input type="text" id="programsInput" class="form-control form-control-lg" placeholder="Type program name and press Enter">
                            </div>
                            <input type="hidden" id="programsHidden" name="programs[]" value="">
                            <small class="form-text text-muted">Press Enter after each program name to add it as a tag.</small>
                        </div>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="save-btn btn btn-primary">Save</button>
                        <button type="button" class="cancel-btn btn btn-secondary" onclick="closeCollegeModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Program Modal -->
    <div id="programModal" class="org-modal" style="display: none !important;">
        <div class="modal-dialog org-modal-dialog">
            <div class="modal-content org-modal-content p-4 rounded-3 shadow">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="form-title mb-0">Add Program</h3>
                    <button type="button" class="btn-close-modal" onclick="closeProgramModal()" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="programForm" method="POST" action="{{ route('admin.colleges.store-program') }}">
                    @csrf
                    <div class="form-fields-container">
                        <div class="form-group">
                            <label for="program_college_id">College <span class="required">*</span></label>
                            <select id="program_college_id" name="college_id" class="form-control form-control-lg" required>
                                <option value="">-- Select College --</option>
                                @foreach ($allColleges ?? [] as $college)
                                    <option value="{{ $college->id }}">{{ $college->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="program_name">Program <span class="required">*</span></label>
                            <input type="text" id="program_name" name="program_name" class="form-control form-control-lg" placeholder="Enter program name" required>
                        </div>

                        <div class="form-group">
                            <label for="program_code">Code</label>
                            <input type="text" id="program_code" name="program_code" class="form-control form-control-lg" placeholder="Optional - Enter program code" maxlength="50">
                            <small class="form-text text-muted">Optional: Add a code for this program.</small>
                        </div>

                        <div class="form-group">
                            <label for="program_major_name">Major</label>
                            <input type="text" id="program_major_name" name="major_name" class="form-control form-control-lg" placeholder="Optional - Enter major name">
                            <small class="form-text text-muted">Optional: Add a major for this program.</small>
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
        const collegeBaseUrl = "{{ url('admin/colleges') }}";

        function openCollegeModal(college = null) {
            const modal = document.getElementById('collegeModal');
            const title = document.getElementById('collegeModalTitle');
            const form = document.getElementById('collegeForm');

            if (!modal) return;

            // Prevent body scroll
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.width = '100%';
            document.body.style.height = '100%';
            document.body.style.top = '0';
            document.body.style.left = '0';

            // Ensure modal covers full viewport
            modal.style.display = 'flex';
            modal.style.position = 'fixed';
            modal.style.top = '0';
            modal.style.left = '0';
            modal.style.right = '0';
            modal.style.bottom = '0';
            modal.style.width = '100vw';
            modal.style.height = '100vh';
            modal.style.margin = '0';
            modal.style.padding = '0';

            const existingMethod = form.querySelector('input[name="_method"]');
            if (existingMethod) existingMethod.remove();

            const nameInput = document.getElementById('name');
            const codeInput = document.getElementById('code');

            if (college) {
                title.textContent = 'Edit College';
                form.action = `${collegeBaseUrl}/${college.id}`;

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                form.appendChild(methodInput);

                document.getElementById('college_id').value = college.id ?? '';
                if (nameInput) nameInput.value = college.name ?? '';
                if (codeInput) codeInput.value = college.code ?? '';
                
                // Hide programs field when editing, show edit programs group
                const programsGroup = document.getElementById('programsGroup');
                if (programsGroup) programsGroup.style.display = 'none';
                clearProgramTags();
                
                // Show note when editing
                const editNote = document.getElementById('editNote');
                if (editNote) editNote.style.display = 'block';
                
                // Show and populate programs for editing
                const editProgramsGroup = document.getElementById('editProgramsGroup');
                if (editProgramsGroup) {
                    editProgramsGroup.style.display = 'block';
                    populateEditProgramsFields(college.programs || []);
                }
            } else {
                title.textContent = 'Add College';
                form.action = '{{ route("admin.colleges.store") }}';
                form.reset();
                document.getElementById('college_id').value = '';
                
                // Hide note when adding
                const editNote = document.getElementById('editNote');
                if (editNote) editNote.style.display = 'none';
                
                // Show programs field when adding, hide edit programs group
                const programsGroup = document.getElementById('programsGroup');
                if (programsGroup) programsGroup.style.display = 'block';
                clearProgramTags();
                
                const editProgramsGroup = document.getElementById('editProgramsGroup');
                if (editProgramsGroup) editProgramsGroup.style.display = 'none';
            }
        }

        function populateEditProgramsFields(programs) {
            const container = document.getElementById('programsEditContainer');
            if (!container) return;
            
            container.innerHTML = '';
            
            if (programs.length === 0) {
                container.innerHTML = '<p class="text-muted small">No programs yet. Use the "Add Program" button to add programs.</p>';
                return;
            }
            
            programs.forEach((program, index) => {
                const programRow = createEditProgramRow(program, index);
                container.appendChild(programRow);
            });
        }

        function createEditProgramRow(program, index) {
            const row = document.createElement('div');
            // Match the "Program 1 / Program Name / Program Code / Major / Add Major +" layout
            row.className = 'program-edit-row program-major-row mb-3 p-3 border rounded';
            row.dataset.programId = program.id || '';
            
            const programId = program.id || '';
            const programName = program.name || '';
            const programCode = program.code || '';
            
            const majors = program.majors || [];
            const majorsHtml = majors.length > 0 ? majors.map((major, majorIndex) => {
                const majorName = major.major_name || major.name || '';
                return `
                    <div class="major-input-row mb-2 d-flex gap-2 align-items-center">
                        <input type="text" 
                               name="edit_programs[${index}][majors][${majorIndex}][name]" 
                               class="form-control form-control-lg" 
                               value="${majorName}" 
                               placeholder="Enter major (optional)">
                        <input type="hidden" name="edit_programs[${index}][majors][${majorIndex}][id]" value="${major.id || ''}">
                        <button type="button" class="major-remove-btn" onclick="removeMajorInput(this)" title="Remove major" aria-label="Remove major">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            }).join('') : '';
            
            row.innerHTML = `
                <div class="program-header mb-3">
                    <strong>Program ${index + 1}</strong>
                </div>
                <div class="form-group">
                    <label class="form-label">Program Name <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="edit_programs[${index}][name]" 
                           class="form-control form-control-lg" 
                           value="${programName}" 
                           placeholder="Enter program name"
                           required>
                    <input type="hidden" name="edit_programs[${index}][id]" value="${programId}">
                </div>
                <div class="form-group">
                    <label class="form-label">Program Code</label>
                    <input type="text" 
                           name="edit_programs[${index}][code]" 
                           class="form-control form-control-lg" 
                           value="${programCode}" 
                           placeholder="Optional - enter program code"
                           maxlength="50">
                </div>
                <div class="form-group majors-section">
                    <label class="form-label">Major</label>
                    <button type="button" class="btn btn-success btn-add-major w-100 mb-2" onclick="addMajorInput(this, ${index})">
                        Add Major +
                    </button>
                    <div class="majors-input-container" data-program-index="${index}">
                        ${majorsHtml}
                    </div>
                </div>
            `;
            
            return row;
        }

        function addMajorInput(button, programIndex) {
            const container = button.closest('.majors-section').querySelector('.majors-input-container');
            if (!container) return;
            
            const majorIndex = container.querySelectorAll('.major-input-row').length;
            const majorRow = document.createElement('div');
            majorRow.className = 'major-input-row mb-2 d-flex gap-2 align-items-center';
            majorRow.innerHTML = `
                <input type="text" 
                       name="edit_programs[${programIndex}][majors][${majorIndex}][name]" 
                       class="form-control form-control-lg" 
                       placeholder="Enter major (optional)">
                <input type="hidden" name="edit_programs[${programIndex}][majors][${majorIndex}][id]" value="">
                <button type="button" class="major-remove-btn" onclick="removeMajorInput(this)" title="Remove major" aria-label="Remove major">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(majorRow);
        }

        function removeMajorInput(button) {
            const row = button.closest('.major-input-row');
            if (row) row.remove();
        }

        function openCollegeModalFromButton(button) {
            const collegeId = button.getAttribute('data-college-id');
            const college = {
                id: collegeId,
                name: button.getAttribute('data-college-name'),
                code: button.getAttribute('data-college-code'),
                programs: []
            };
            
            // Fetch college with programs and majors
            fetch(`{{ url('admin/colleges') }}/${collegeId}/programs-majors`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    college.programs = data.programs || [];
                    openCollegeModal(college);
                })
                .catch(error => {
                    console.error('Error fetching programs:', error);
                    // Open modal with empty programs array
                    openCollegeModal(college);
                });
        }

        function closeCollegeModal() {
            const modal = document.getElementById('collegeModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
                document.body.style.height = '';
                document.body.style.top = '';
                document.body.style.left = '';
            }
        }

        function openDeleteModal(college) {
            const modal = document.getElementById('deleteModal');
            const form = document.getElementById('deleteForm');
            const collegeNameElement = document.getElementById('deleteCollegeName');

            if (!modal || !form) return;

            // Prevent body scroll
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.width = '100%';
            document.body.style.height = '100%';
            document.body.style.top = '0';
            document.body.style.left = '0';

            // Ensure modal covers full viewport
            modal.style.display = 'flex';
            modal.style.position = 'fixed';
            modal.style.top = '0';
            modal.style.left = '0';
            modal.style.right = '0';
            modal.style.bottom = '0';
            modal.style.width = '100vw';
            modal.style.height = '100vh';
            modal.style.margin = '0';
            modal.style.padding = '0';

            form.action = `${collegeBaseUrl}/${college.id}`;
            if (collegeNameElement) {
                collegeNameElement.textContent = college.name;
            }
        }

        function openDeleteModalFromButton(button) {
            const college = {
                id: button.getAttribute('data-college-id'),
                name: button.getAttribute('data-college-name'),
            };
            openDeleteModal(college);
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
                document.body.style.height = '';
                document.body.style.top = '';
                document.body.style.left = '';
            }
        }

        function closeSuccessModal() {
            const modal = document.getElementById('successModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
                document.body.style.height = '';
                document.body.style.top = '';
                document.body.style.left = '';
            }
        }

        // Auto-close success modal after 3 seconds and ensure proper styling
        document.addEventListener('DOMContentLoaded', function() {
            const successModal = document.getElementById('successModal');
            if (successModal) {
                // Move modal to body to break out of container constraints
                if (successModal.parentElement && successModal.parentElement !== document.body) {
                    document.body.appendChild(successModal);
                }
                
                // Ensure modal covers full viewport with aggressive styling
                successModal.style.cssText = `
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
                    margin: 0 !important;
                    padding: 0 !important;
                    z-index: 99999 !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    background: rgba(0, 0, 0, 0.5) !important;
                    backdrop-filter: blur(5px) !important;
                    -webkit-backdrop-filter: blur(5px) !important;
                    overflow: hidden !important;
                    box-sizing: border-box !important;
                `;
                
                // Ensure content is centered
                const modalContent = successModal.querySelector('.success-modal-content');
                if (modalContent) {
                    modalContent.style.cssText = `
                        margin: 0 auto !important;
                        position: relative !important;
                        z-index: 100000 !important;
                    `;
                }
                
                // Prevent body scroll
                document.body.style.cssText += `
                    overflow: hidden !important;
                    position: fixed !important;
                    width: 100% !important;
                    height: 100% !important;
                    top: 0 !important;
                    left: 0 !important;
                `;
                
                // Auto-close after 3 seconds
                setTimeout(() => {
                    closeSuccessModal();
                }, 3000);
            }

            // Programs tag functionality
            const programsInput = document.getElementById('programsInput');
            if (programsInput) {
                programsInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const value = this.value.trim();
                        if (value) {
                            addProgramTag(value);
                            this.value = '';
                        }
                    }
                });
            }
        });

        // Program tags management
        let programTags = [];

        function addProgramTag(name) {
            if (programTags.includes(name)) return;
            
            programTags.push(name);
            updateProgramTagsDisplay();
            updateProgramsHiddenInput();
        }

        function removeProgramTag(name) {
            programTags = programTags.filter(tag => tag !== name);
            updateProgramTagsDisplay();
            updateProgramsHiddenInput();
        }

        function clearProgramTags() {
            programTags = [];
            updateProgramTagsDisplay();
            updateProgramsHiddenInput();
        }

        function updateProgramTagsDisplay() {
            const container = document.getElementById('programsTags');
            if (!container) return;
            
            container.innerHTML = '';
            programTags.forEach(tag => {
                const tagElement = document.createElement('span');
                tagElement.className = 'program-tag';
                tagElement.innerHTML = `
                    ${tag}
                    <button type="button" onclick="removeProgramTag('${tag.replace(/'/g, "\\'")}')" class="tag-remove">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                container.appendChild(tagElement);
            });
        }

        function updateProgramsHiddenInput() {
            const hiddenInput = document.getElementById('programsHidden');
            if (hiddenInput) {
                // Remove all existing hidden inputs
                const form = document.getElementById('collegeForm');
                const existingInputs = form.querySelectorAll('input[name="programs[]"]');
                existingInputs.forEach(input => {
                    if (input !== hiddenInput) input.remove();
                });
                
                // Add new hidden inputs for each tag
                programTags.forEach(tag => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'programs[]';
                    input.value = tag;
                    form.appendChild(input);
                });
            }
        }

        // Program modal functions
        function openProgramModal() {
            const modal = document.getElementById('programModal');
            if (!modal) return;

            // Prevent body scroll
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.width = '100%';
            document.body.style.height = '100%';
            document.body.style.top = '0';
            document.body.style.left = '0';

            // Ensure modal covers full viewport
            modal.style.display = 'flex';
            modal.style.position = 'fixed';
            modal.style.top = '0';
            modal.style.left = '0';
            modal.style.right = '0';
            modal.style.bottom = '0';
            modal.style.width = '100vw';
            modal.style.height = '100vh';
            modal.style.margin = '0';
            modal.style.padding = '0';
        }

        function closeProgramModal() {
            const modal = document.getElementById('programModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
                document.body.style.height = '';
                document.body.style.top = '';
                document.body.style.left = '';
                document.getElementById('programForm').reset();
            }
        }
    </script>

    <style>
        /* Ensure container doesn't constrain modals */
        .container {
            position: relative;
        }

        /* Main content: use page scrollbar (no inner scrolling) */
        .main-content {
            max-width: 100%;
            overflow: visible;
            overflow-x: hidden;
            box-sizing: border-box;
            display: block;
            height: auto;
            min-height: calc(100vh - 65px);
            padding: 20px 40px !important;
        }

        /* Page header */
        .page-header {
            flex-shrink: 0;
            margin-bottom: 1rem;
        }

        /* Filter section */
        .filter-section {
            flex-shrink: 0;
            margin-bottom: 1rem;
        }

        /* Center and constrain table container (no flex sizing to avoid inner scroll) */
        .submissions-table-container {
            margin: 0 auto;
            max-width: 100%;
            width: 100%;
            box-sizing: border-box;
            overflow: visible;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Hide scrollbar on table container if any */
        .submissions-table-container::-webkit-scrollbar {
            display: none;
        }
        
        /* Ensure success modal breaks out of any container */
        #successModal {
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
            margin: 0 !important;
            padding: 0 !important;
            transform: none !important;
        }
        
        /* Override any parent container constraints */
        .container #successModal,
        .main-content #successModal,
        body #successModal {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
        }
        
        /* Reuse organization modal styles */
        .org-modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px) !important;
            -webkit-backdrop-filter: blur(5px) !important;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .org-modal[style*="flex"] {
            display: flex !important;
        }

        .org-modal-dialog {
            width: 100%;
            max-width: 1200px;
            margin: auto !important;
            position: relative !important;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            z-index: 10001 !important;
            height: 100%;
        }

        .org-modal-content {
            width: 100% !important;
            max-height: 90vh !important;
            min-height: 400px !important;
            padding: 40px !important;
            overflow: hidden !important;
            background: var(--card-bg, #fff) !important;
            position: relative;
            margin: 0;
            display: flex;
            flex-direction: column;
            border-radius: 0;
        }

        /* Hide scrollbar but keep functionality */
        .org-modal-content::-webkit-scrollbar {
            width: 8px;
        }

        .org-modal-content::-webkit-scrollbar-track {
            background: transparent;
        }

        .org-modal-content::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 4px;
        }

        .org-modal-content::-webkit-scrollbar-thumb:hover {
            background: #999;
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
            max-width: 100%;
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 10px;
        }

        /* Hide scrollbar but keep functionality */
        .org-modal-content .form-fields-container::-webkit-scrollbar {
            width: 6px;
        }

        .org-modal-content .form-fields-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .org-modal-content .form-fields-container::-webkit-scrollbar-thumb {
            background: #ddd;
            border-radius: 3px;
        }

        .org-modal-content .form-fields-container::-webkit-scrollbar-thumb:hover {
            background: #bbb;
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
            min-width: 100vw !important;
            min-height: 100vh !important;
            max-width: 100vw !important;
            max-height: 100vh !important;
            background: rgba(0, 0, 0, 0.5) !important;
            backdrop-filter: blur(5px) !important;
            -webkit-backdrop-filter: blur(5px) !important;
            z-index: 99999 !important;
            display: none !important;
            align-items: center !important;
            justify-content: center !important;
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
            box-sizing: border-box !important;
        }

        .success-modal[style*="flex"],
        .success-modal[style*="flex !important"] {
            display: flex !important;
        }

        .success-modal-content {
            background: #fff;
            border-radius: 0;
            padding: 0;
            max-width: 380px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            margin: 0 !important;
            position: relative;
            z-index: 100000;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
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

        /* Program Edit Rows Styling */
        .programs-edit-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .programs-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        body.dark-mode .programs-section {
            background: #2a2a2a;
        }

        .program-edit-row {
            background: var(--card-bg, #fff);
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .program-header {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-color, #333);
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #dee2e6;
        }

        body.dark-mode .program-header {
            color: #f0f0f0;
            border-bottom-color: #555;
        }

        .program-edit-row .row {
            margin: 0;
        }

        .program-edit-row .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-color, #333);
            font-size: 14px;
        }

        .program-edit-row .form-control-lg {
            width: 100%;
            padding: 12px 16px;
            font-size: 15px;
        }

        body.dark-mode .program-edit-row {
            background: #2a2a2a;
            border-color: #555;
        }

        body.dark-mode .program-edit-row .form-label {
            color: #f0f0f0;
        }

        .program-edit-row .majors-section {
            margin-top: 1rem;
        }

        .btn-add-major {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
            color: #fff !important;
            font-weight: 500;
            padding: 12px !important;
        }

        .btn-add-major:hover {
            background-color: #218838 !important;
            border-color: #1e7e34 !important;
        }

        .program-edit-row .majors-input-container {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .program-edit-row .major-input-row {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .program-edit-row .major-input-row .form-control {
            flex: 1;
            min-width: 0; /* prevent overflow in flex rows */
        }

        .major-remove-btn {
            flex: 0 0 40px;
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 8px;
            background: #dc3545;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.15s ease, transform 0.15s ease;
        }

        .major-remove-btn:hover {
            background: #bb2d3b;
            transform: translateY(-1px);
        }

        body.dark-mode .major-remove-btn {
            background: #dc3545;
        }

        body.dark-mode .major-remove-btn:hover {
            background: #bb2d3b;
        }

        body.dark-mode .program-edit-row .majors-section {
            border-top-color: #555;
        }

        /* Program Tags Styling */
        .programs-tags-container {
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 8px;
            min-height: 50px;
            background: var(--card-bg, #fff);
        }

        .programs-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 8px;
        }

        .program-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #7b0000;
            color: #fff;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .tag-remove {
            background: none;
            border: none;
            color: #fff;
            cursor: pointer;
            padding: 0;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.2s;
        }

        .tag-remove:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .tag-remove i {
            font-size: 12px;
        }

        #programsInput {
            border: none;
            outline: none;
            width: 100%;
            padding: 4px 8px;
            font-size: 16px;
        }

        body.dark-mode .programs-tags-container {
            background: #2a2a2a;
            border-color: #555;
        }

        body.dark-mode #programsInput {
            background: #2a2a2a;
            color: #f0f0f0;
        }

        /* Action buttons styling */
        .action-buttons-group {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-edit,
        .btn-delete {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 14px;
        }

        .btn-edit {
            background: #0d6efd;
            color: #fff;
        }

        .btn-edit:hover {
            background: #0b5ed7;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(13, 110, 253, 0.3);
        }

        .btn-delete {
            background: #dc3545;
            color: #fff;
        }

        .btn-delete:hover {
            background: #bb2d3b;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
        }

        body.dark-mode .btn-edit {
            background: #0d6efd;
        }

        body.dark-mode .btn-edit:hover {
            background: #0b5ed7;
        }

        body.dark-mode .btn-delete {
            background: #dc3545;
        }

        body.dark-mode .btn-delete:hover {
            background: #bb2d3b;
        }

        /* Programs List Styling */
        .programs-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            max-width: 100%;
        }

        .program-badge {
            display: inline-block;
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid #bbdefb;
            white-space: nowrap;
        }

        body.dark-mode .program-badge {
            background: #1e3a5f;
            color: #90caf9;
            border-color: #1565c0;
        }

        /* Majors List Styling */
        .majors-list {
            display: flex;
            flex-direction: column;
            gap: 4px;
            max-width: 100%;
        }

        .majors-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            max-width: 100%;
        }

        .major-badge {
            display: inline-block;
            background: #e8f5e9;
            color: #2e7d32 !important;
            padding: 4px 10px;
            border-radius: 10px;
            font-size: 12px !important;
            font-weight: 500;
            border: 1px solid #a5d6a7;
            white-space: nowrap;
            line-height: 1.4;
            min-width: auto;
            opacity: 1 !important;
            visibility: visible !important;
        }

        body.dark-mode .major-badge {
            background: #1b5e20;
            color: #81c784;
            border-color: #4caf50;
        }

        .submissions-table td {
            vertical-align: middle;
        }

        /* Table Container Styling - Match Organizations Management */
        .submissions-table-container {
            background: #fff;
            border-radius: 0;
            padding: 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            overflow: visible;
            width: fit-content;
            min-width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            margin: 0 auto;
        }

        .submissions-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 0;
            table-layout: fixed;
        }

        .submissions-table thead {
            background: #7b0000;
        }

        .submissions-table thead th {
            background: #7b0000;
            color: #fff;
            font-weight: 600;
            padding: 15px 12px;
            text-align: left;
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            border-bottom: 2px solid #fff;
            font-size: 14px;
            box-sizing: border-box;
        }

        .submissions-table thead th:last-child {
            border-right: none;
            text-align: center;
        }

        /* Column width adjustments - Programs gets more space, Actions expanded */
        .submissions-table thead th:nth-child(1),
        .submissions-table tbody td:nth-child(1) {
            width: 23%;
            min-width: 150px;
        }

        .submissions-table thead th:nth-child(2),
        .submissions-table tbody td:nth-child(2) {
            width: 10%;
            min-width: 80px;
        }

        .submissions-table thead th:nth-child(3),
        .submissions-table tbody td:nth-child(3) {
            width: 52%;
            min-width: 300px;
        }

        .submissions-table thead th:nth-child(4),
        .submissions-table tbody td:nth-child(4) {
            width: 15%;
            min-width: 120px;
        }

        .submissions-table tbody td {
            padding: 12px;
            border-right: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
            background: #fff;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* Allow programs column to wrap content */
        .submissions-table tbody td:nth-child(3) {
            overflow: visible;
            text-overflow: clip;
            white-space: normal;
        }

        .submissions-table tbody td:last-child {
            border-right: none;
            text-align: center;
        }

        .submissions-table tbody tr:last-child td {
            border-bottom: none;
        }

        .submissions-table tbody tr:nth-child(even) td {
            background: #f8f9fa;
        }

        .submissions-table tbody tr:hover td {
            background: #e3f2fd;
        }

        /* Dark mode support for table container */
        body.dark-mode .submissions-table-container {
            background: #2b2b2b;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .submissions-table {
            color: #f0f0f0;
        }

        body.dark-mode .submissions-table thead {
            background: #5c0000;
        }

        body.dark-mode .submissions-table thead th {
            background: #5c0000;
            border-color: rgba(255, 255, 255, 0.15);
        }

        body.dark-mode .submissions-table tbody td {
            background: #3a3a3a;
            border-color: #555;
            color: #f0f0f0;
        }

        body.dark-mode .submissions-table tbody tr:nth-child(even) td {
            background: #333;
        }

        body.dark-mode .submissions-table tbody tr:hover td {
            background: #404040;
        }
    </style>
@endsection

