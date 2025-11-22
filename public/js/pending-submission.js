// public/js/pending-submission.js

let currentSubmissionId = null;
const entriesPerPage = 5;

// rubric context for currently open modal
let currentAutoScore = null;        // numeric score we'll send
let currentScoringMethod = null;    // 'option' | 'rate' | null
let currentRate = null;             // for rate-based subsections
let currentCap = null;              // cap_points at subsection level (may be null)
let currentRubricOptionId = null;   // selected descriptor id (if any)

// small helper to avoid "cannot set textContent of null"
function setText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
}

// Minimal modal / message helpers (fallbacks)
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
    ].forEach(id => setText(id, 'Loading...'));

    currentSubmissionId   = null;
    currentAutoScore      = null;
    currentScoringMethod  = null;
    currentRate           = null;
    currentCap            = null;
    currentRubricOptionId = null;
}

window.openSubmissionModal = async function (submissionId) {
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
   RUBRIC OPTIONS (option vs rate)
   ========================= */

function populateRubricOptions(options) {
    const container = document.getElementById('rubricOptionsContainer');
    if (!container) return;

    container.innerHTML = '';

    // CASE 1: option-based rubric (leadership A/B/C, academic, awards, conduct)
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

    // CASE 2: rate-based rubric (D trainings, community service, etc.)
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
            // Try to back-calc days just for convenience
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

                // Round to 2 decimals to match DB decimal(10,2)
                score = Math.round(score * 100) / 100;

                currentAutoScore = score;
                const label = `${score} pts`;
                scoreDisplay.textContent = label;
                setText('modalAutoScore', label);
            });
        }

        // no radios here
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
        showErrorModal('No submission selected');
        return;
    }

    const remarks = (document.getElementById('assessorRemarks')?.value || '').trim();

    if ((action === 'reject' || action === 'return' || action === 'flag') && !remarks) {
        showValidationError('Please provide remarks before performing this action.');
        return;
    }

    const selectedOption = document.querySelector('input[name="rubric_option"]:checked');
    let totalPoints = selectedOption ? parseFloat(selectedOption.value) : null;

    // For approve: require either a radio (option-based) OR a valid auto score (rate-based/manual)
    if (action === 'approve') {
        if (selectedOption && !isNaN(totalPoints)) {
            // descriptor-based path is fine
        } else if (currentScoringMethod === 'rate' &&
                   currentAutoScore != null &&
                   !isNaN(currentAutoScore)) {
            totalPoints = currentAutoScore;
        } else {
            showValidationError(
                'Please select a rubric descriptor or enter the number of days to compute the score before approving.'
            );
            return;
        }
    }

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
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                action: action,
                remarks: remarks,
                total_points: totalPoints,
                rubric_option_id: currentRubricOptionId,
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
   INIT
   ========================= */

document.addEventListener('DOMContentLoaded', function () {
    initializePagination();
});
