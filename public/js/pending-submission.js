// public/js/pending-submission.js

let currentSubmissionId = null;
const entriesPerPage = 5;

// small helper to avoid "cannot set textContent of null"
function setText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
}

// Minimal modal / message helpers (fallbacks).
function showErrorModal(message) {
    try {
        alert(message);
    } catch (e) {
        console.error('showErrorModal fallback failed', e);
    }
}

function showValidationError(message) {
    console.warn('Validation:', message);
    alert(message);
}

function showSuccessMessage(action) {
    const msg =
        action === 'approve' ? 'Submission approved.' :
        action === 'reject'  ? 'Submission rejected.' :
        action === 'return'  ? 'Submission returned to student.' :
        'Submission flagged for admin review.';

    try {
        if (window.toastr) {
            toastr.success(msg);
        } else {
            alert(msg);
        }
    } catch (e) {
        alert(msg);
    }
}

// Provide initializePagination wrapper if not already defined by included scripts
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
   MODAL HELPERS
   ========================= */

function showModalLoading() {
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
    ].forEach(id => setText(id, 'Loading...'));
}

window.openSubmissionModal = async function (submissionId) {
    try {
        showModalLoading();

        const response = await fetch(`/assessor/submissions/${submissionId}/details`, {
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            const text = await response.text();
            throw new Error(`HTTP ${response.status}: ${text.substring(0, 120)}...`);
        }

        const data = await response.json();
        const submission = data.submission;

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
            submission.auto_generated_score != null
                ? `${submission.auto_generated_score} pts`
                : 'Not calculated'
        );

        // docs & rubric
        populateDocumentPreview(submission.documents || []);
        populateRubricOptions(
            submission.rubric && submission.rubric.options
                ? submission.rubric.options
                : []
        );

        const remarksEl = document.getElementById('assessorRemarks');
        if (remarksEl) remarksEl.value = '';

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
   RUBRIC OPTIONS (cleaner + score pill)
   ========================= */

function populateRubricOptions(options) {
    const container = document.getElementById('rubricOptionsContainer');
    if (!container) return;

    container.innerHTML = '';

    if (!options || options.length === 0) {
        container.innerHTML =
            '<p class="text-muted mb-0">No rubric options defined for this subsection.</p>';
        return;
    }

    const list = document.createElement('div');
    list.className = 'rubric-options-list';

    options.forEach(opt => {
        const item = document.createElement('div');
        item.className = 'form-check rubric-option';

        item.innerHTML = `
            <input class="form-check-input" type="radio"
                   name="rubric_option" id="rubric_option_${opt.id}"
                   value="${opt.points}">
            <label class="form-check-label" for="rubric_option_${opt.id}">
                <strong>${opt.label}</strong>
                ${opt.points !== null ? ` <span class="rubric-points">(${opt.points} pts)</span>` : ''}
            </label>
        `;

        list.appendChild(item);
    });

    container.appendChild(list);

    // update score pill when a descriptor is picked
    const radios = container.querySelectorAll('input[name="rubric_option"]');
    radios.forEach(input => {
        input.addEventListener('change', () => {
            const pts = parseFloat(input.value);
            if (!isNaN(pts)) {
                setText('modalAutoScore', `${pts} pts`);
            }
        });
    });
}

/* =========================
   ACTION HANDLER
   ========================= */

window.handleSubmission = async function (action) {
    if (!currentSubmissionId) {
        showErrorModal('No submission selected');
        return;
    }

    const remarks = (document.getElementById('assessorRemarks')?.value || '').trim();

    if ((action === 'reject' || action === 'return' || action === 'flag') && !remarks) {
        showValidationError('Please provide remarks before performing this action.');
        return;
    }

    const selectedOption = document.querySelector('input[name="rubric_option"]:checked');
    const totalPoints = selectedOption ? parseFloat(selectedOption.value) : null;

    if (action === 'approve' && (totalPoints === null || isNaN(totalPoints))) {
        showValidationError('Please select a rubric descriptor (score) before approving.');
        return;
    }

    // CSRF token
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
                'Accept': 'application/json',              // 👈 important
                'X-Requested-With': 'XMLHttpRequest',      // 👈 helps Laravel treat it as AJAX
            },
            body: JSON.stringify({
                action: action,
                remarks: remarks,
                total_points: totalPoints
            })
        });

        const raw = await response.text(); // get text first
        let data = null;

        // Try to parse JSON if possible
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
            // Laravel JSON errors often put message or errors here
            const msg =
                data?.message ||
                data?.error ||
                (data?.errors ? JSON.stringify(data.errors) : null) ||
                `HTTP ${response.status}`;
            throw new Error(msg);
        }

        // Success
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
   INIT
   ========================= */

document.addEventListener('DOMContentLoaded', function () {
    initializePagination();
});
