// public/js/pending-submission.js

let currentSubmissionId = null;

const entriesPerPage = 5;

// rubric context for currently open modal
let currentAutoScore = null;        // numeric score we'll send
let currentScoringMethod = null;    // 'option' | 'rate' | null
let currentRate = null;             // for rate-based subsections
let currentCap = null;              // cap_points at subsection level (may be null)
let currentRubricOptionId = null;   // selected descriptor id (if any)

/* =========================
   BASIC HELPERS
   ========================= */

function setText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
}

/* =========================
   MODAL HELPERS (ERROR / VALIDATION / SUCCESS)
   ========================= */

// ERROR MODAL (server / network / unexpected errors)
function showErrorModal(message) {
    const errorModal = document.createElement('div');
    errorModal.className = 'modal fade';
    errorModal.id = 'errorModal';

    errorModal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content validation-modal-content">
                <div class="modal-body text-center p-4">
                    <div class="validation-icon mb-3">
                        <i class="fas fa-exclamation-triangle" style="color: #dc3545; font-size: 3rem;"></i>
                    </div>
                    <h5 class="validation-title mb-3">Error</h5>
                    <p class="validation-message mb-4">${message}</p>
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal">
                        OK
                    </button>
                </div>
            </div>
        </div>
    `;

    // ensure it appears above the review modal
    errorModal.style.zIndex = '22000';

    document.body.appendChild(errorModal);

    const modal = new bootstrap.Modal(errorModal, {
        backdrop: 'static',
        keyboard: false
    });

    errorModal.addEventListener('shown.bs.modal', () => {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        const backdrop = backdrops[backdrops.length - 1];
        if (backdrop) {
            backdrop.style.zIndex = '21900';
        }
    });

    errorModal.addEventListener('hidden.bs.modal', () => {
        document.body.removeChild(errorModal);
    });

    modal.show();
}

// VALIDATION MODAL (missing remarks, missing score, etc.)
function showValidationError(message) {
    const validationModal = document.createElement('div');
    validationModal.className = 'modal fade';
    validationModal.id = 'validationModal';

    validationModal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content validation-modal-content">
                <div class="modal-body text-center p-4">
                    <div class="validation-icon mb-3">
                        <i class="fas fa-exclamation-triangle" style="color: #dc3545; font-size: 3rem;"></i>
                    </div>
                    <h5 class="validation-title mb-3">Validation Required</h5>
                    <p class="validation-message mb-4">${message}</p>
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal">
                        OK
                    </button>
                </div>
            </div>
        </div>
    `;

    validationModal.style.zIndex = '22000';

    document.body.appendChild(validationModal);

    const modal = new bootstrap.Modal(validationModal, {
        backdrop: 'static',
        keyboard: false
    });

    validationModal.addEventListener('shown.bs.modal', () => {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        const backdrop = backdrops[backdrops.length - 1];
        if (backdrop) {
            backdrop.style.zIndex = '21900';
        }
    });

    validationModal.addEventListener('hidden.bs.modal', () => {
        document.body.removeChild(validationModal);
    });

    modal.show();
}

// SUCCESS MODAL (approve / reject / return / flag)
function showSuccessMessage(action) {
    let message = '';
    let icon = '';
    let color = '';

    switch (action) {
        case 'approve':
            message = 'Submission has been successfully approved!';
            icon = 'fas fa-check-circle';
            color = '#28a745';
            break;
        case 'reject':
            message = 'Submission has been successfully rejected.';
            icon = 'fas fa-times-circle';
            color = '#8B0000';
            break;
        case 'return':
            message = 'Submission has been returned to the student for revision.';
            icon = 'fas fa-undo';
            color = '#FFD700';
            break;
        case 'flag':
            message = 'Submission has been flagged for further review.';
            icon = 'fas fa-flag';
            color = '#dc3545';
            break;
        default:
            message = 'Action completed successfully!';
            icon = 'fas fa-info-circle';
            color = '#007bff';
    }

    const successModal = document.createElement('div');
    successModal.className = 'modal fade';
    successModal.id = 'successModal';

    successModal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content success-modal-content">
                <div class="modal-body text-center p-4">
                    <div class="success-icon mb-3">
                        <i class="${icon}" style="color: ${color}; font-size: 3rem;"></i>
                    </div>
                    <h5 class="success-title mb-3">Success!</h5>
                    <p class="success-message mb-4">${message}</p>
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                        OK
                    </button>
                </div>
            </div>
        </div>
    `;

    successModal.style.zIndex = '22000';

    document.body.appendChild(successModal);

    const modal = new bootstrap.Modal(successModal, {
        backdrop: 'static',
        keyboard: false
    });

    successModal.addEventListener('shown.bs.modal', () => {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        const backdrop = backdrops[backdrops.length - 1];
        if (backdrop) {
            backdrop.style.zIndex = '21900';
        }
    });

    successModal.addEventListener('hidden.bs.modal', () => {
        document.body.removeChild(successModal);
    });

    modal.show();
}

/* =========================
   CONFIRMATION MODAL (UI)
   ========================= */

function showConfirmModal({ title, message, confirmLabel = 'Yes', confirmClass = 'btn-primary' }) {
    return new Promise((resolve) => {
        const modalEl = document.getElementById('confirmationModal');
        const titleEl = document.getElementById('confirmationModalTitle');
        const bodyEl = document.getElementById('confirmationModalBody');
        const confirmBtn = document.getElementById('confirmActionBtn');

        // Fallback to window.confirm if modal/bootstrap is missing
        if (!modalEl || !titleEl || !bodyEl || !confirmBtn || typeof bootstrap === 'undefined') {
            const ok = window.confirm(message || 'Are you sure?');
            resolve(ok);
            return;
        }

        titleEl.textContent = title || 'Confirm Action';
        bodyEl.textContent = message || 'Are you sure you want to continue?';

        confirmBtn.className = 'btn ' + confirmClass;
        confirmBtn.textContent = confirmLabel || 'Yes';

        let resolved = false;

        const modal = new bootstrap.Modal(modalEl, {
            backdrop: 'static',
            keyboard: false
        });

        const handleConfirm = () => {
            if (resolved) return;
            resolved = true;
            modal.hide();
            resolve(true);
        };

        const handleHidden = () => {
            if (resolved) return;
            resolved = true;
            resolve(false);
        };

        const handleShown = () => {
            modalEl.style.zIndex = '23000';

            const backdrops = document.querySelectorAll('.modal-backdrop');
            const backdrop = backdrops[backdrops.length - 1];
            if (backdrop) {
                backdrop.classList.add('confirmation-backdrop');
                backdrop.style.zIndex = '22900';
            }

            modalEl.removeEventListener('shown.bs.modal', handleShown);
        };

        confirmBtn.addEventListener('click', handleConfirm, { once: true });
        modalEl.addEventListener('hidden.bs.modal', handleHidden, { once: true });
        modalEl.addEventListener('shown.bs.modal', handleShown, { once: true });

        modal.show();
    });
}

/* =========================
   PAGINATION WRAPPER
   ========================= */

if (typeof initializePagination === 'undefined') {
    window.initializePagination = function () {
        if (typeof initializeAdminPagination !== 'undefined') {
            initializeAdminPagination('.submissions-table', entriesPerPage);
        } else {
            console.warn('No pagination implementation available.');
        }
    };
}

/* =========================
   MODAL STATE + DETAILS LOADER
   ========================= */

function resetModalState() {
    [
        'modalStudentId',
        'modalStudentName',
        'modalDocumentTitle',
        'modalDateSubmitted',
        'modalSleaSection',
        'modalSubsection',
        'modalRole',
        'modalActivityDate',
        'modalOrganizingBody',
        'modalDescription',
        'modalAutoScore'
    ].forEach(id => setText(id, '-'));

    currentSubmissionId   = null;
    currentAutoScore      = null;
    currentScoringMethod  = null;
    currentRate           = null;
    currentCap            = null;
    currentRubricOptionId = null;

    const remarksEl = document.getElementById('assessorRemarks');
    if (remarksEl) remarksEl.value = '';
}

// Ensure function is available globally
window.openSubmissionModal = window.openSubmissionModal || async function (submissionId) {
    try {
        resetModalState();

        const response = await fetch(`/assessor/submissions/${submissionId}/details`, {
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            const text = await response.text();
            throw new Error(`HTTP ${response.status}: ${text.substring(0, 120)}...`);
        }

        const data = await response.json();
        const submission = data.submission;

        const rubric = submission.rubric || {};
        currentScoringMethod  = rubric.scoring_method || null;
        currentCap            = rubric.cap_points ?? null;
        currentRate           = rubric.score_params && rubric.score_params.rate != null
            ? parseFloat(rubric.score_params.rate)
            : null;
        currentAutoScore      = submission.auto_generated_score != null
            ? parseFloat(submission.auto_generated_score)
            : null;
        currentRubricOptionId = null;

        // student info
        setText(
            'modalStudentId',
            submission.student && submission.student.student_id
                ? submission.student.student_id
                : '-'
        );
        setText(
            'modalStudentName',
            submission.student && submission.student.name
                ? submission.student.name
                : '-'
        );

        // general info
        setText('modalDocumentTitle', submission.document_title || '-');
        setText(
            'modalDateSubmitted',
            submission.submitted_at
                ? new Date(submission.submitted_at).toLocaleDateString()
                : '-'
        );
        setText('modalSleaSection', submission.slea_section || '-');
        setText('modalSubsection', submission.subsection || '-');
        setText('modalRole', submission.role_in_activity || '-');
        setText('modalActivityDate', submission.activity_date || '-');
        setText('modalOrganizingBody', submission.organizing_body || '-');
        setText('modalDescription', submission.description || '-');

        setText(
            'modalAutoScore',
            currentAutoScore != null && !isNaN(currentAutoScore)
                ? `${currentAutoScore} pts`
                : 'Not calculated'
        );

        // docs & rubric
        populateDocumentPreview(submission.documents || []);
        populateRubricOptions(rubric.options || []);

        currentSubmissionId = submissionId;

        const modal = new bootstrap.Modal(document.getElementById('submissionModal'));
        modal.show();

    } catch (error) {
        console.error('Error fetching submission details:', error);
        showErrorModal('Failed to load submission details: ' + error.message);
    }
};

/* =========================
   DOCUMENT LIST (no inline preview)
   ========================= */

function populateDocumentPreview(documents) {
    const listContainer = document.getElementById('documentList');

    if (!listContainer) return;

    listContainer.innerHTML = '';

    if (!documents || documents.length === 0) {
        listContainer.innerHTML = '<p class="text-muted mb-0">No documents uploaded.</p>';
        return;
    }

    documents.forEach(doc => {
        const documentItem = document.createElement('div');
        documentItem.className = 'document-item';

        const iconClass = doc.is_pdf ? 'pdf' : (doc.is_image ? 'image' : 'other');
        const iconSymbol = doc.is_pdf ? '📄' : (doc.is_image ? '🖼️' : '📎');

        documentItem.innerHTML = `
            <div class="document-info">
                <div class="document-icon ${iconClass}">
                    ${iconSymbol}
                </div>
                <div class="document-details">
                    <h6>${doc.original_filename}</h6>
                    <small>${(doc.file_type || '').toUpperCase()} • ${doc.file_size || ''}</small>
                </div>
            </div>
            <div class="document-actions">
                <button class="btn-view-doc" type="button"
                        onclick="viewDocument('${doc.view_url}')">
                    <i class="fas fa-external-link-alt"></i> View
                </button>
                <button class="btn-download" type="button"
                        onclick="downloadDocument('${doc.download_url || doc.view_url}')">
                    <i class="fas fa-download"></i> Download
                </button>
            </div>
        `;

        listContainer.appendChild(documentItem);
    });
}

window.viewDocument = function (url) {
    if (url) window.open(url, '_blank');
};

window.downloadDocument = function (url) {
    if (url) window.open(url, '_blank');
};

/* =========================
   RUBRIC OPTIONS (option vs rate)
   ========================= */

function populateRubricOptions(options) {
    const container = document.getElementById('rubricOptionsContainer');
    if (!container) return;

    container.innerHTML = '';

    // CASE 1: option-based rubric
    if (options && options.length > 0) {
        const list = document.createElement('div');
        list.className = 'rubric-options-list';

        options.forEach(opt => {
            const item = document.createElement('div');
            item.className = 'form-check rubric-option';

            item.innerHTML = `
                <input class="form-check-input" type="radio"
                       name="rubric_option" id="rubric_option_${opt.id}"
                       value="${opt.points}"
                       data-option-id="${opt.id}">
                <label class="form-check-label" for="rubric_option_${opt.id}">
                    <strong>${opt.label}</strong>
                    ${opt.points !== null ? ` <span class="rubric-points">(${opt.points} pts)</span>` : ''}
                </label>
            `;

            list.appendChild(item);
        });

        container.appendChild(list);

        // Update score pill when a descriptor is picked
        const radios = container.querySelectorAll('input[name="rubric_option"]');
        radios.forEach(input => {
            input.addEventListener('change', () => {
                const pts = parseFloat(input.value);
                const optId = parseInt(input.getAttribute('data-option-id'), 10);
                if (!isNaN(pts)) {
                    currentAutoScore = pts;
                    setText('modalAutoScore', `${pts} pts`);
                }
                currentRubricOptionId = !isNaN(optId) ? optId : null;
            });
        });

        return;
    }

    // CASE 2: rate-based rubric
    if (currentScoringMethod === 'rate') {
        const rateText = currentRate != null ? `${currentRate} pts/day` : 'rate not defined';
        const currentScoreText =
            currentAutoScore != null && !isNaN(currentAutoScore)
                ? `${currentAutoScore} pts`
                : 'No auto-calculated score available.';

        container.innerHTML = `
            <p class="text-muted mb-1">
                This item uses a rate-based score (days × rate, capped per level).
            </p>
            <p class="mb-1"><strong>Rate:</strong> ${rateText}</p>
            <div class="mb-2">
                <label for="rateDaysInput" class="form-label">
                    Number of days (enter based on the student's note):
                </label>
                <input type="number" min="0" step="0.5"
                       id="rateDaysInput"
                       class="form-control"
                       placeholder="e.g. 3">
            </div>
            <p><strong>Score:</strong> <span id="rateScoreDisplay">${currentScoreText}</span></p>
        `;

        const daysInput = document.getElementById('rateDaysInput');
        const scoreDisplay = document.getElementById('rateScoreDisplay');

        if (daysInput && currentAutoScore != null && !isNaN(currentAutoScore) && currentRate) {
            const approxDays = currentAutoScore / currentRate;
            if (!isNaN(approxDays)) {
                daysInput.value = approxDays.toFixed(2).replace(/\.00$/, '');
            }
        }

        if (daysInput) {
            daysInput.addEventListener('input', () => {
                const days = parseFloat(daysInput.value);
                if (isNaN(days) || days < 0 || currentRate == null) {
                    currentAutoScore = null;
                    scoreDisplay.textContent = 'No auto-calculated score available.';
                    setText('modalAutoScore', 'Not calculated');
                    return;
                }

                let score = days * currentRate;
                if (currentCap != null && !isNaN(currentCap)) {
                    score = Math.min(score, currentCap);
                }

                score = Math.round(score * 100) / 100;

                currentAutoScore = score;
                const label = `${score} pts`;
                scoreDisplay.textContent = label;
                setText('modalAutoScore', label);
            });
        }

        currentRubricOptionId = null;
        return;
    }

    // Fallback if truly nothing defined
    container.innerHTML =
        '<p class="text-muted mb-0">No rubric options defined for this subsection.</p>';
}

/* =========================
   ACTION HANDLER
   ========================= */

window.handleSubmission = async function (action) {
    if (!currentSubmissionId) {
        showErrorModal('No submission selected.');
        return;
    }

    const remarks = (document.getElementById('assessorRemarks')?.value || '').trim();

    // Require remarks for reject/return/flag
    if ((action === 'reject' || action === 'return' || action === 'flag') && !remarks) {
        showValidationError('Please provide remarks before performing this action.');
        return;
    }

    const selectedOption = document.querySelector('input[name="rubric_option"]:checked');

    // Default score to 0 to avoid NULL inserts
    let totalPoints = 0;

    // For approve: require either a descriptor OR a valid rate-based score
    if (action === 'approve') {
        if (selectedOption && !isNaN(parseFloat(selectedOption.value))) {
            totalPoints = parseFloat(selectedOption.value);
        } else if (
            currentScoringMethod === 'rate' &&
            currentAutoScore != null &&
            !isNaN(currentAutoScore)
        ) {
            totalPoints = currentAutoScore;
        } else {
            showValidationError(
                'Please select a rubric descriptor or enter the number of days to compute the score before approving.'
            );
            return;
        }
    }

    // Confirmation config
    let confirmConfig = {
        title: 'Confirm Action',
        message: 'Are you sure you want to perform this action?',
        confirmLabel: 'Yes, continue',
        confirmClass: 'btn-primary'
    };

    switch (action) {
        case 'approve':
            confirmConfig = {
                title: 'Confirm Approval',
                message:
                    `Are you sure you want to APPROVE this submission` +
                    (totalPoints ? ` with a score of ${totalPoints} pts` : '') +
                    `?\n\nThis will finalize the review for this entry.`,
                confirmLabel: 'Yes, approve',
                confirmClass: 'btn-success'
            };
            break;
        case 'reject':
            confirmConfig = {
                title: 'Confirm Rejection',
                message:
                    'Are you sure you want to REJECT this submission?\n\n' +
                    'The student will see your remarks and the submission will be marked as rejected.',
                confirmLabel: 'Yes, reject',
                confirmClass: 'btn-danger'
            };
            break;
        case 'return':
            confirmConfig = {
                title: 'Return Submission',
                message:
                    'Are you sure you want to RETURN this submission to the student for revision?\n\n' +
                    'They will be asked to revise and resubmit based on your remarks.',
                confirmLabel: 'Yes, return',
                confirmClass: 'btn-warning'
            };
            break;
        case 'flag':
            confirmConfig = {
                title: 'Flag for Admin Review',
                message:
                    'Are you sure you want to FLAG this submission for admin review?\n\n' +
                    'Use this for suspicious or problematic documents.',
                confirmLabel: 'Yes, flag',
                confirmClass: 'btn-danger'
            };
            break;
    }

    const confirmed = await showConfirmModal(confirmConfig);
    if (!confirmed) return;

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfMeta) {
        showErrorModal('CSRF token not found in page. Please ensure <meta name="csrf-token"> is in the layout.');
        return;
    }
    const csrfToken = csrfMeta.getAttribute('content');

    try {
        const response = await fetch(`/assessor/submissions/${currentSubmissionId}/action`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: action,
                remarks: remarks,
                total_points: totalPoints,
                rubric_option_id: currentRubricOptionId
            })
        });

        const raw = await response.text();
        let data = null;

        try {
            data = raw ? JSON.parse(raw) : null;
        } catch (e) {
            console.error('Non-JSON response from server:', raw);
            throw new Error(
                `Server returned an unexpected response (status ${response.status}). ` +
                `Check Laravel logs / Network tab for details.`
            );
        }

        if (!response.ok) {
            const msg =
                data?.message ||
                data?.error ||
                (data?.errors ? JSON.stringify(data.errors) : null) ||
                `HTTP ${response.status}`;
            throw new Error(msg);
        }

        const modalEl = document.getElementById('submissionModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        showSuccessMessage(action);
        window.location.reload();

    } catch (error) {
        console.error('Error processing submission action:', error);
        showErrorModal('Failed to process action: ' + error.message);
    }
};

/* =========================
   SEARCH FUNCTIONALITY
   ========================= */

function initializeSearch() {
    console.log('Initializing search functionality...');
    
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const clearBtn = document.getElementById('clearBtn');
    const sortSelect = document.getElementById('sortSelect');
    
    // Try multiple selectors for the table
    const table = document.querySelector('.submissions-table') || 
                  document.querySelector('table.submissions-table') ||
                  document.querySelector('table.table.submissions-table') ||
                  document.querySelector('.submissions-table-container table');

    console.log('Elements found:', {
        searchInput: !!searchInput,
        searchBtn: !!searchBtn,
        clearBtn: !!clearBtn,
        sortSelect: !!sortSelect,
        table: !!table
    });

    if (!table) {
        console.error('Search initialization: table not found');
        return;
    }
    
    if (!searchInput) {
        console.error('Search initialization: searchInput not found');
        return;
    }

    let allRows = Array.from(table.querySelectorAll('tbody tr')).filter(row => {
        return !row.hasAttribute('data-empty-row');
    });
    
    console.log('Total rows found:', allRows.length);

    function performSearch() {
        console.log('performSearch called');
        const searchTerm = (searchInput.value || '').toLowerCase().trim();
        console.log('Search term:', searchTerm);
        
        let visibleCount = 0;
        
        // Store original display state for pagination
        allRows.forEach(row => {
            if (row.hasAttribute('data-empty-row')) {
                // Hide empty row if searching
                row.style.display = searchTerm ? 'none' : '';
                return;
            }

            const cells = row.querySelectorAll('td');
            if (cells.length === 0) {
                row.style.display = 'none';
                return;
            }

            const studentId = (cells[0]?.textContent || '').toLowerCase();
            const studentName = (cells[1]?.textContent || '').toLowerCase();
            const documentTitle = (cells[2]?.textContent || '').toLowerCase();
            const dateSubmitted = (cells[3]?.textContent || '').toLowerCase();

            const matches = !searchTerm || 
                studentId.includes(searchTerm) ||
                studentName.includes(searchTerm) ||
                documentTitle.includes(searchTerm) ||
                dateSubmitted.includes(searchTerm);

            // Store match state in data attribute for pagination
            row.dataset.searchMatch = matches ? 'true' : 'false';
            row.style.display = matches ? '' : 'none';
            
            if (matches) visibleCount++;
        });
        
        console.log('Visible rows after search:', visibleCount);

        // Apply sorting if sort is selected
        if (sortSelect && sortSelect.value) {
            applySort();
        }

        // Update pagination with filtered results
        updatePaginationAfterSearch(searchTerm);
    }

    function applySort() {
        const sortValue = sortSelect ? sortSelect.value : '';
        if (!sortValue) return;

        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        const visibleRows = Array.from(tbody.querySelectorAll('tr')).filter(row => {
            return !row.hasAttribute('data-empty-row') && row.style.display !== 'none';
        });

        visibleRows.sort((a, b) => {
            const aCells = a.querySelectorAll('td');
            const bCells = b.querySelectorAll('td');
            
            if (aCells.length === 0 || bCells.length === 0) return 0;

            let aValue = '';
            let bValue = '';

            switch(sortValue) {
                case 'date':
                    aValue = (aCells[3]?.textContent || '').trim();
                    bValue = (bCells[3]?.textContent || '').trim();
                    // Sort dates descending (newest first)
                    return new Date(bValue) - new Date(aValue);
                case 'name':
                    aValue = (aCells[1]?.textContent || '').trim().toLowerCase();
                    bValue = (bCells[1]?.textContent || '').trim().toLowerCase();
                    return aValue.localeCompare(bValue);
                case 'title':
                    aValue = (aCells[2]?.textContent || '').trim().toLowerCase();
                    bValue = (bCells[2]?.textContent || '').trim().toLowerCase();
                    return aValue.localeCompare(bValue);
                default:
                    return 0;
            }
        });

        // Reorder rows in DOM
        visibleRows.forEach(row => tbody.appendChild(row));
    }

    function updatePaginationAfterSearch(searchTerm) {
        if (typeof paginationInstances !== 'undefined') {
            const visibleRows = allRows.filter(row => {
                const matchAttr = row.dataset.searchMatch;
                return matchAttr === 'true' || (!searchTerm && matchAttr !== 'false');
            });
            
            // Try multiple selectors for pagination instance
            const pagination = paginationInstances['.submissions-table'] || 
                             paginationInstances['table.submissions-table'] ||
                             paginationInstances['.table.submissions-table'];
            
            if (pagination) {
                // Update pagination with filtered count
                pagination.totalEntries = visibleRows.length;
                pagination.totalPages = Math.ceil(pagination.totalEntries / pagination.entriesPerPage) || 1;
                pagination.currentPage = 1; // Reset to first page
                pagination.updatePaginationInfo();
                pagination.generatePageButtons();
                pagination.updateButtonStates();
                pagination.updateTableDisplay();
            } else {
                // Reinitialize pagination if not found
                if (typeof initializeAdminPagination !== 'undefined') {
                    setTimeout(() => {
                        initializeAdminPagination('.submissions-table', entriesPerPage);
                    }, 100);
                }
            }
        }
    }

    function clearSearch() {
        console.log('clearSearch called');
        if (searchInput) {
            searchInput.value = '';
            console.log('Search input cleared');
            performSearch();
            searchInput.focus();
        } else {
            console.error('searchInput not found in clearSearch');
        }
    }

    // Search on input (debounced)
    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(performSearch, 300);
        });
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout);
                performSearch();
            }
        });
    }

    // Search button click
    if (searchBtn) {
        console.log('Adding click listener to search button');
        searchBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            console.log('Search button clicked');
            clearTimeout(searchTimeout);
            performSearch();
        });
    } else {
        console.error('searchBtn not found');
    }

    // Clear button click
    if (clearBtn) {
        console.log('Adding click listener to clear button');
        clearBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            console.log('Clear button clicked');
            clearSearch();
        });
    } else {
        console.error('clearBtn not found');
    }

    // Sort dropdown change
    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            applySort();
            updatePaginationAfterSearch(searchInput.value || '');
        });
    }
}

/* =========================
   INIT
   ========================= */

document.addEventListener('DOMContentLoaded', function () {
    console.log('DOMContentLoaded - Initializing pending submissions page');
    
    // Initialize pagination first
    initializePagination();
    
    // Then initialize search after a short delay to ensure pagination is ready
    setTimeout(() => {
        console.log('Initializing search after delay');
        initializeSearch();
    }, 200);
});

// Also try to initialize if DOM is already loaded
if (document.readyState === 'loading') {
    // DOM is still loading, wait for DOMContentLoaded
} else {
    // DOM is already loaded
    console.log('DOM already loaded - Initializing immediately');
    setTimeout(() => {
        initializePagination();
        initializeSearch();
    }, 100);
}

// Global fallback functions for onclick handlers
window.handleSearchClick = function(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    console.log('handleSearchClick called (onclick fallback)');
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) {
        console.error('searchInput not found');
        return;
    }
    
    const searchTerm = searchInput.value.toLowerCase().trim();
    console.log('Searching for:', searchTerm);
    
    const table = document.querySelector('.submissions-table') || 
                  document.querySelector('table.submissions-table') ||
                  document.querySelector('.submissions-table-container table');
    
    if (!table) {
        console.error('Table not found');
        return;
    }
    
    const rows = table.querySelectorAll('tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        if (row.hasAttribute('data-empty-row')) {
            row.style.display = searchTerm ? 'none' : '';
            return;
        }
        
        const cells = row.querySelectorAll('td');
        if (cells.length === 0) {
            row.style.display = 'none';
            return;
        }
        
        const studentId = (cells[0]?.textContent || '').toLowerCase();
        const studentName = (cells[1]?.textContent || '').toLowerCase();
        const documentTitle = (cells[2]?.textContent || '').toLowerCase();
        const dateSubmitted = (cells[3]?.textContent || '').toLowerCase();
        
        const matches = !searchTerm || 
            studentId.includes(searchTerm) ||
            studentName.includes(searchTerm) ||
            documentTitle.includes(searchTerm) ||
            dateSubmitted.includes(searchTerm);
        
        row.style.display = matches ? '' : 'none';
        if (matches) visibleCount++;
    });
    
    console.log('Search complete. Visible rows:', visibleCount);
    
    // Update pagination if available
    if (typeof paginationInstances !== 'undefined') {
        const pagination = paginationInstances['.submissions-table'];
        if (pagination) {
            pagination.totalEntries = visibleCount;
            pagination.totalPages = Math.ceil(pagination.totalEntries / pagination.entriesPerPage) || 1;
            pagination.currentPage = 1;
            pagination.updatePaginationInfo();
            pagination.generatePageButtons();
            pagination.updateButtonStates();
            pagination.updateTableDisplay();
        }
    }
};

window.handleClearClick = function(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    console.log('handleClearClick called (onclick fallback)');
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) {
        console.error('searchInput not found');
        return;
    }
    
    searchInput.value = '';
    console.log('Search input cleared');
    
    const table = document.querySelector('.submissions-table') || 
                  document.querySelector('table.submissions-table') ||
                  document.querySelector('.submissions-table-container table');
    
    if (table) {
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.style.display = '';
        });
        console.log('All rows shown');
    }
    
    // Update pagination if available
    if (typeof paginationInstances !== 'undefined') {
        const pagination = paginationInstances['.submissions-table'];
        if (pagination) {
            const allRows = table.querySelectorAll('tbody tr:not([data-empty-row])');
            pagination.totalEntries = allRows.length;
            pagination.totalPages = Math.ceil(pagination.totalEntries / pagination.entriesPerPage) || 1;
            pagination.currentPage = 1;
            pagination.updatePaginationInfo();
            pagination.generatePageButtons();
            pagination.updateButtonStates();
            pagination.updateTableDisplay();
        }
    }
    
    searchInput.focus();
};

/* =========================
   INIT
   ========================= */

document.addEventListener('DOMContentLoaded', function () {
    console.log('DOMContentLoaded - Initializing pending submissions page');
    
    // Initialize pagination first
    initializePagination();
    
    // Then initialize search after a short delay to ensure pagination is ready
    setTimeout(() => {
        console.log('Initializing search after delay');
        initializeSearch();
    }, 200);
});

// Also try to initialize if DOM is already loaded
if (document.readyState === 'loading') {
    // DOM is still loading, wait for DOMContentLoaded
} else {
    // DOM is already loaded
    console.log('DOM already loaded - Initializing immediately');
    setTimeout(() => {
        initializePagination();
        initializeSearch();
    }, 100);
}

// Global fallback functions for onclick handlers
window.handleSearchClick = function(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    console.log('handleSearchClick called (onclick fallback)');
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) {
        console.error('searchInput not found');
        return;
    }
    
    const searchTerm = searchInput.value.toLowerCase().trim();
    console.log('Searching for:', searchTerm);
    
    const table = document.querySelector('.submissions-table') || 
                  document.querySelector('table.submissions-table') ||
                  document.querySelector('.submissions-table-container table');
    
    if (!table) {
        console.error('Table not found');
        return;
    }
    
    const rows = table.querySelectorAll('tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        if (row.hasAttribute('data-empty-row')) {
            row.style.display = searchTerm ? 'none' : '';
            return;
        }
        
        const cells = row.querySelectorAll('td');
        if (cells.length === 0) {
            row.style.display = 'none';
            return;
        }
        
        const studentId = (cells[0]?.textContent || '').toLowerCase();
        const studentName = (cells[1]?.textContent || '').toLowerCase();
        const documentTitle = (cells[2]?.textContent || '').toLowerCase();
        const dateSubmitted = (cells[3]?.textContent || '').toLowerCase();
        
        const matches = !searchTerm || 
            studentId.includes(searchTerm) ||
            studentName.includes(searchTerm) ||
            documentTitle.includes(searchTerm) ||
            dateSubmitted.includes(searchTerm);
        
        row.style.display = matches ? '' : 'none';
        if (matches) visibleCount++;
    });
    
    console.log('Search complete. Visible rows:', visibleCount);
    
    // Update pagination if available
    if (typeof paginationInstances !== 'undefined') {
        const pagination = paginationInstances['.submissions-table'];
        if (pagination) {
            pagination.totalEntries = visibleCount;
            pagination.totalPages = Math.ceil(pagination.totalEntries / pagination.entriesPerPage) || 1;
            pagination.currentPage = 1;
            pagination.updatePaginationInfo();
            pagination.generatePageButtons();
            pagination.updateButtonStates();
            pagination.updateTableDisplay();
        }
    }
};

window.handleClearClick = function(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    console.log('handleClearClick called (onclick fallback)');
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) {
        console.error('searchInput not found');
        return;
    }
    
    searchInput.value = '';
    console.log('Search input cleared');
    
    const table = document.querySelector('.submissions-table') || 
                  document.querySelector('table.submissions-table') ||
                  document.querySelector('.submissions-table-container table');
    
    if (table) {
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.style.display = '';
        });
        console.log('All rows shown');
    }
    
    // Update pagination if available
    if (typeof paginationInstances !== 'undefined') {
        const pagination = paginationInstances['.submissions-table'];
        if (pagination) {
            const allRows = table.querySelectorAll('tbody tr:not([data-empty-row])');
            pagination.totalEntries = allRows.length;
            pagination.totalPages = Math.ceil(pagination.totalEntries / pagination.entriesPerPage) || 1;
            pagination.currentPage = 1;
            pagination.updatePaginationInfo();
            pagination.generatePageButtons();
            pagination.updateButtonStates();
            pagination.updateTableDisplay();
        }
    }
    
    searchInput.focus();
};
