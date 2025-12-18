// assessor-all-submissions.js

let currentSubmissionId = null;
let currentStudentIdForModal = null; // remember which student is open

// Pagination + search
let currentPage = 1;
const entriesPerPage = 5;
let totalEntries = 0;
let totalPages = 0;
let initialStudentsData = [];

/* -----------------------------
   GLOBAL FALLBACK FUNCTIONS (for onclick handlers)
----------------------------- */

// Global fallback functions for search and clear buttons
// These are called from onclick handlers in the HTML
window.handleSearchClick = function(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    // Ensure filterAndPaginateStudents is available
    if (typeof filterAndPaginateStudents === 'function') {
        currentPage = 1;
        filterAndPaginateStudents();
    } else {
        // Fallback: trigger the search input event
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            const searchEvent = new Event('input', { bubbles: true });
            searchInput.dispatchEvent(searchEvent);
        }
    }
};

window.handleClearClick = function(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.value = '';
        // Ensure filterAndPaginateStudents is available
        if (typeof filterAndPaginateStudents === 'function') {
            currentPage = 1;
            filterAndPaginateStudents();
        } else {
            // Fallback: trigger the search input event
            const searchEvent = new Event('input', { bubbles: true });
            searchInput.dispatchEvent(searchEvent);
        }
        searchInput.focus();
    }
};

/* -----------------------------
   MODAL HELPERS
----------------------------- */

function showModalLoading(modalId) {
    if (modalId === 'studentSubmissionsModal') {
        const nameTitle = document.getElementById('modalStudentNameTitle');
        const idDetail = document.getElementById('modalStudentIdDetail');
        const nameDetail = document.getElementById('modalStudentNameDetail');
        const programDetail = document.getElementById('modalStudentProgramDetail');
        const collegeDetail = document.getElementById('modalStudentCollegeDetail');
        const container = document.getElementById('categorizedSubmissionsContainer');

        if (nameTitle) nameTitle.textContent = 'Loading...';
        if (idDetail) idDetail.textContent = 'Loading...';
        if (nameDetail) nameDetail.textContent = 'Loading...';
        if (programDetail) programDetail.textContent = 'Loading...';
        if (collegeDetail) collegeDetail.textContent = 'Loading...';
        if (container) {
            container.innerHTML =
                '<p class="text-muted text-center"><i class="fas fa-spinner fa-spin"></i> Loading submissions...</p>';
        }
    } else if (modalId === 'individualSubmissionModal') {
        const idEl = document.getElementById('modalIndividualStudentId');
        const nameEl = document.getElementById('modalIndividualStudentName');
        const titleEl = document.getElementById('modalIndividualDocumentTitle');
        const dateEl = document.getElementById('modalIndividualDateSubmitted');
        const statusEl = document.getElementById('modalIndividualStatus');
        const sectionEl = document.getElementById('modalIndividualSleaSection');
        const subsectionEl = document.getElementById('modalIndividualSubsection');
        const roleEl = document.getElementById('modalIndividualRole');
        const dateActivityEl = document.getElementById('modalIndividualActivityDate');
        const orgBodyEl = document.getElementById('modalIndividualOrganizingBody');
        const descEl = document.getElementById('modalIndividualDescription');
        const autoScoreEl = document.getElementById('modalIndividualAutoScore');
        const remarksEl = document.getElementById('individualAssessorRemarks');
        const assessorNameEl = document.getElementById('modalIndividualAssessorName');
        const docPreview = document.getElementById('individualDocumentPreview');

        if (idEl) idEl.textContent = 'Loading...';
        if (nameEl) nameEl.textContent = 'Loading...';
        if (titleEl) titleEl.textContent = 'Loading...';
        if (dateEl) dateEl.textContent = 'Loading...';
        if (statusEl) statusEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        if (sectionEl) sectionEl.textContent = 'Loading...';
        if (subsectionEl) subsectionEl.textContent = 'Loading...';
        if (roleEl) roleEl.textContent = 'Loading...';
        if (dateActivityEl) dateActivityEl.textContent = 'Loading...';
        if (orgBodyEl) orgBodyEl.textContent = 'Loading...';
        if (descEl) descEl.textContent = 'Loading...';
        if (autoScoreEl) autoScoreEl.textContent = 'Loading...';
        if (remarksEl) remarksEl.value = 'Loading...';
        if (assessorNameEl) assessorNameEl.textContent = 'Loading...';
        if (docPreview) {
            docPreview.innerHTML =
                '<p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Loading documents...</p>';
        }
    }
}

/* -----------------------------
   DOCUMENT PREVIEW
----------------------------- */

function populateDocumentPreview(documents, containerId) {
    const previewContainer = document.getElementById(containerId);
    if (!previewContainer) return;

    previewContainer.innerHTML = '';

    if (!documents || documents.length === 0) {
        previewContainer.innerHTML = '<p class="text-muted">No documents uploaded.</p>';
        return;
    }

    documents.forEach(doc => {
        const documentItem = document.createElement('div');
        documentItem.className = 'document-item';

        const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes((doc.file_type || '').toLowerCase());
        const isPdf = (doc.file_type || '').toLowerCase() === 'pdf';

        const iconClass = isPdf ? 'pdf' : (isImage ? 'image' : 'other');
        const iconSymbol = isPdf ? '📄' : (isImage ? '🖼️' : '📎');

        documentItem.innerHTML = `
            <div class="document-info">
                <div class="document-icon ${iconClass}">
                    ${iconSymbol}
                </div>
                <div class="document-details">
                    <h6>${doc.original_filename}</h6>
                    <small>${(doc.file_type || '').toUpperCase()} • ${doc.formatted_size || ''}</small>
                </div>
            </div>
            <div class="document-actions">
                ${
                    isPdf || isImage
                        ? `<button class="btn-preview" data-file-path="${doc.file_path}" data-mime-type="${doc.mime_type}">Preview</button>`
                        : ''
                }
                <button class="btn-download" data-file-path="${doc.file_path}" data-file-name="${doc.original_filename}">
                    Download
                </button>
            </div>
        `;

        previewContainer.appendChild(documentItem);
    });

    // Attach click handlers for preview & download
    previewContainer.querySelectorAll('.btn-download').forEach(btn => {
        btn.addEventListener('click', () => {
            const filePath = btn.getAttribute('data-file-path');
            const fileName = btn.getAttribute('data-file-name');
            downloadDocument(filePath, fileName);
        });
    });

    previewContainer.querySelectorAll('.btn-preview').forEach(btn => {
        btn.addEventListener('click', () => {
            const filePath = btn.getAttribute('data-file-path');
            const mimeType = btn.getAttribute('data-mime-type');
            previewDocument(filePath, mimeType);
        });
    });
}

function downloadDocument(filePath, fileName) {
    if (!filePath) return;
    const link = document.createElement('a');
    link.href = `/storage/${filePath}`;
    link.download = fileName || filePath.split('/').pop();
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function previewDocument(filePath, mimeType) {
    if (!filePath) return;

    const fileExtension = filePath.split('.').pop().toLowerCase();
    const viewerUrl = `/assessor/document-viewer?path=${encodeURIComponent(filePath)}&mime=${encodeURIComponent(
        mimeType || ''
    )}`;

    if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
        window.open(`/storage/${filePath}`, '_blank');
    } else if (fileExtension === 'pdf') {
        window.open(viewerUrl, '_blank');
    } else {
        alert('No preview available for this file type. Downloading instead.');
        downloadDocument(filePath);
    }
}

/* -----------------------------
   FETCH: INDIVIDUAL SUBMISSION
----------------------------- */

async function openIndividualSubmissionModal(submissionId) {
    try {
        currentSubmissionId = submissionId;
        showModalLoading('individualSubmissionModal');

        const response = await fetch(`/assessor/submissions/${submissionId}/details`);
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Failed to fetch submission details');
        }

        const submission = data.submission;

        const idEl = document.getElementById('modalIndividualStudentId');
        const nameEl = document.getElementById('modalIndividualStudentName');
        const titleEl = document.getElementById('modalIndividualDocumentTitle');
        const dateEl = document.getElementById('modalIndividualDateSubmitted');
        const statusEl = document.getElementById('modalIndividualStatus');
        const sectionEl = document.getElementById('modalIndividualSleaSection');
        const subsectionEl = document.getElementById('modalIndividualSubsection');
        const roleEl = document.getElementById('modalIndividualRole');
        const dateActivityEl = document.getElementById('modalIndividualActivityDate');
        const orgBodyEl = document.getElementById('modalIndividualOrganizingBody');
        const descEl = document.getElementById('modalIndividualDescription');
        const autoScoreEl = document.getElementById('modalIndividualAutoScore');
        const remarksEl = document.getElementById('individualAssessorRemarks');
        const assessorNameEl = document.getElementById('modalIndividualAssessorName');

        if (idEl) idEl.textContent = submission.student?.student_id || '-';
        if (nameEl) nameEl.textContent = submission.student?.user?.name || 'N/A';
        if (titleEl) titleEl.textContent = submission.document_title || '-';
        if (dateEl) {
            dateEl.textContent = submission.submitted_at
                ? new Date(submission.submitted_at).toLocaleDateString()
                : '-';
        }

        if (statusEl) {
            const status = submission.status || 'Unknown';
            const normalized = (status || '').toLowerCase();
            statusEl.innerHTML = `
                <span class="status-badge status-${normalized}">
                    ${status.charAt(0).toUpperCase() + status.slice(1)}
                </span>
            `;
        }

        if (sectionEl) sectionEl.textContent = submission.slea_section || '-';
        if (subsectionEl) subsectionEl.textContent = submission.subsection || '-';
        if (roleEl) roleEl.textContent = submission.role_in_activity || '-';
        if (dateActivityEl) dateActivityEl.textContent = submission.activity_date || '-';
        if (orgBodyEl) orgBodyEl.textContent = submission.organizing_body || '-';
        if (descEl) descEl.textContent = submission.description || '-';

        if (autoScoreEl) {
            autoScoreEl.textContent = submission.auto_generated_score
                ? `${submission.auto_generated_score}/100`
                : 'Not calculated';
        }

        if (remarksEl) {
            remarksEl.value = submission.assessor_remarks || '';
        }

        if (assessorNameEl) {
            assessorNameEl.textContent = submission.assessor?.name || 'N/A';
        }

        populateDocumentPreview(submission.documents || [], 'individualDocumentPreview');

        const modalEl = document.getElementById('individualSubmissionModal');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    } catch (error) {
        console.error('Error fetching individual submission details:', error);
        showErrorModal('Failed to load submission details: ' + error.message);
    }
}

/* -----------------------------
   FETCH: STUDENT + ALL SUBMISSIONS
----------------------------- */

// Helper function to open modal from button data attribute
// Made global for inline onclick handlers
window.openStudentSubmissionsModalFromButton = function(button) {
    const studentId = button.getAttribute('data-student-id');
    if (studentId) {
        openStudentSubmissionsModal(parseInt(studentId));
    }
};

// Make sure this is ASYNC
async function openStudentSubmissionsModal(studentId) {
    currentStudentIdForModal = studentId;
    showModalLoading('studentSubmissionsModal');

    let rawText;
    let data;

    try {
        const response = await fetch(`/assessor/students/${studentId}/details`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        rawText = await response.text();

        // Non-2xx responses: try to pull a JSON message, otherwise use raw text
        if (!response.ok) {
            try {
                const errJson = rawText ? JSON.parse(rawText) : null;
                const msg =
                    (errJson && (errJson.message || errJson.error)) ||
                    `Server returned status ${response.status}`;
                throw new Error(msg);
            } catch (e) {
                // rawText is not JSON
                throw new Error(rawText || `Server returned status ${response.status}`);
            }
        }

        if (!rawText) {
            throw new Error('Server returned an empty response.');
        }

        // Safe JSON parse with friendly error
        try {
            data = JSON.parse(rawText);
        } catch (e) {
            throw new Error(`Unexpected response format (${e.message}).`);
        }
    } catch (err) {
        hideModalLoading('studentSubmissionsModal');
        showErrorAlert(`Failed to load student submissions: ${err.message}`);
        return;
    }

    hideModalLoading('studentSubmissionsModal');

    // ----- Populate modal from data -----
    const student = data.student || {};
    const acad    = student.studentAcademic || {};
    const categorized = data.submissions || {};
    const categoryTotals = data.category_totals || {};
    const overallTotal = Number(data.overall_total_score ?? 0);

    const nameTitle   = document.getElementById('modalStudentNameTitle');
    const idDetail    = document.getElementById('modalStudentIdDetail');
    const nameDetail  = document.getElementById('modalStudentNameDetail');
    const programDet  = document.getElementById('modalStudentProgramDetail');
    const collegeDet  = document.getElementById('modalStudentCollegeDetail');
    const majorDet    = document.getElementById('modalStudentMajorDetail');
    const statusText  = document.getElementById('currentStatusText');
    const container   = document.getElementById('categorizedSubmissionsContainer');

    if (nameTitle)  nameTitle.textContent  = student.user?.name || 'Student';
    if (idDetail)   idDetail.textContent   = student.student_id || '—';
    if (nameDetail) nameDetail.textContent = student.user?.name || '—';
    if (programDet) programDet.textContent = student.program || '—';
    if (collegeDet) collegeDet.textContent = student.college || '—';
    if (majorDet)   majorDet.textContent   = acad.major || acad.major_name || '—';

    if (statusText) {
        const sleaStatus = acad.slea_application_status || null;
        if (!sleaStatus) {
            statusText.textContent = 'No application yet.';
        } else if (sleaStatus === 'pending_assessor_evaluation') {
            statusText.textContent = 'Pending Assessor Evaluation.';
        } else if (sleaStatus === 'pending_administrative_validation') {
            statusText.textContent = 'Pending Administrative Validation.';
        } else if (sleaStatus === 'qualified') {
            statusText.textContent = 'Qualified for SLEA.';
        } else if (sleaStatus === 'not_qualified') {
            statusText.textContent = 'Not qualified.';
        } else {
            statusText.textContent = 'Status: ' + sleaStatus;
        }
    }

    // Render per-category submissions
    if (container) {
        const categoryKeys = Object.keys(categorized);

        if (!categoryKeys.length) {
            container.innerHTML =
                '<p class="text-muted text-center">No submissions found for this student.</p>';
        } else {
            let html = '';

            categoryKeys.forEach((label) => {
                const rows = categorized[label] || [];
                const totalRow = categoryTotals[label] || { score: 0, max_score: 0 };

                html += `
                    <div class="mb-3">
                        <h6 class="fw-semibold mb-1">${label}</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-1">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40%">Document Title</th>
                                        <th style="width: 20%">Subsection</th>
                                        <th style="width: 15%">Status</th>
                                        <th style="width: 10%; text-align:right;">Score</th>
                                        <th style="width: 15%">Reviewed At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${
                                        rows.length
                                            ? rows
                                                  .map((row) => `
                                                        <tr>
                                                            <td>${row.document_title || '—'}</td>
                                                            <td>${row.subsection || '—'}</td>
                                                            <td>${row.status || '—'}</td>
                                                            <td style="text-align:right;">${row.assessor_score ?? 0}</td>
                                                            <td>${row.reviewed_at ? new Date(row.reviewed_at).toLocaleString() : '—'}</td>
                                                        </tr>
                                                    `)
                                                  .join('')
                                            : '<tr><td colspan="5" class="text-center text-muted">No submissions in this category.</td></tr>'
                                    }
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total</th>
                                        <th style="text-align:right;">${totalRow.score ?? 0} / ${totalRow.max_score ?? 0}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }
    }

    // Show the modal
// Show the modal
const modalEl = document.getElementById('studentSubmissionsModal');
if (modalEl) {
    // Create or get the Bootstrap modal instance
    const studentModal = bootstrap.Modal.getOrCreateInstance(modalEl);
    studentModal.show();
}

}

// ---------------------------------------------------------------------
// Backwards-compatibility helpers for the student submissions modal.
// Old code calls showModalLoading()/hideModalLoading(), so we define
// safe no-op versions here to avoid ReferenceError and JS crashes.
// ---------------------------------------------------------------------
if (typeof window.showModalLoading !== 'function') {
    window.showModalLoading = function () {
        const modal = document.getElementById('studentSubmissionsModal');
        if (!modal) return;

        // Optional: show a loading element if you have one
        const loader =
            modal.querySelector('[data-role="modal-loading"]') ||
            modal.querySelector('.po-modal-loading');

        if (loader) {
            loader.classList.remove('d-none');
            loader.style.display = '';
        }
    };
}

if (typeof window.hideModalLoading !== 'function') {
    window.hideModalLoading = function () {
        const modal = document.getElementById('studentSubmissionsModal');
        if (!modal) return;

        const loader =
            modal.querySelector('[data-role="modal-loading"]') ||
            modal.querySelector('.po-modal-loading');

        if (loader) {
            loader.classList.add('d-none');
            loader.style.display = 'none';
        }
    };
}

/* -----------------------------
   READY / NOT READY FOR RATING
----------------------------- */

async function updateReadyForRating(isReady) {
    if (!currentStudentIdForModal) {
        console.warn('No student selected for ready/not ready action.');
        return;
    }

    const noteEl = document.getElementById('readyForRatingStatusNote');

    // Note: Confirmation is handled by showConfirmModal() before this function is called
    // No need for native browser confirm() dialog here

    if (noteEl) {
        noteEl.textContent = 'Saving ready status...';
    }

    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null;

    try {
        const response = await fetch(
            `/assessor/students/${currentStudentIdForModal}/ready-status`,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                },
                body: JSON.stringify({
                    ready: isReady ? 1 : 0,
                }),
            }
        );

        const data = await response.json();
        console.log('ready-status response', data);

        if (!response.ok || data.success === false) {
            throw new Error(data.error || 'Failed to update ready status');
        }

        const newStatusKey = data.slea_status?.key ?? (isReady ? 'pending_assessor_evaluation' : 'incomplete');
        const newStatusLabel =
            data.slea_status?.label ?? (isReady ? 'Pending Assessor Evaluation' : 'Incomplete');

        // Update note under the buttons
        if (noteEl) {
            if (isReady) {
                noteEl.textContent =
                    'Marked as READY for rating. This student will appear in your Assessor Final Review list.';
            } else {
                noteEl.textContent = 'Marked as NOT ready for rating.';
            }
        }

        // 🔄 Update pill in the main students table, but DO NOT remove the row
        const row = document.querySelector(
            `tr[data-student-id="${currentStudentIdForModal}"]`
        );
        if (row) {
            row.dataset.sleaStatus = newStatusKey;

            const pill = row.querySelector('.slea-status-pill');
            if (pill) {
                // reset classes
                pill.className = 'slea-status-pill';

                switch (newStatusKey) {
                    case 'pending_assessor_evaluation':
                        pill.classList.add('slea-status-pill--ready-assessor');
                        break;
                    case 'incomplete':
                        pill.classList.add('slea-status-pill--not-ready');
                        break;
                    case 'pending_administrative_validation':
                        pill.classList.add('slea-status-pill--for-admin');
                        break;
                    case 'qualified':
                        pill.classList.add('slea-status-pill--awarded');
                        break;
                    case 'rejected':
                    case 'not_qualified':
                        pill.classList.add('slea-status-pill--rejected');
                        break;
                    case 'not_eligible':
                        pill.classList.add('slea-status-pill--not-4th');
                        break;
                    default:
                        pill.classList.add('slea-status-pill--in-process');
                        break;
                }

                pill.textContent = newStatusLabel;
            }
        }

        // Close the student submissions modal after successful update
        const studentSubmissionsModalEl = document.getElementById('studentSubmissionsModal');
        if (studentSubmissionsModalEl) {
            const studentSubmissionsModal = bootstrap.Modal.getInstance(studentSubmissionsModalEl);
            if (studentSubmissionsModal) {
                // Close modal after a brief delay to show the updated status message
                setTimeout(() => {
                    studentSubmissionsModal.hide();
                }, 1000);
            }
        }

    } catch (error) {
        console.error('Error updating ready status:', error);
        if (noteEl) {
            noteEl.textContent = 'Failed to update ready status: ' + error.message;
        }
        if (typeof showErrorModal === 'function') {
            showErrorModal('Failed to update ready status: ' + error.message);
        }
    }
}

/* -----------------------------
   HANDLE ACTIONS (APPROVE / REJECT / RETURN / FLAG)
----------------------------- */

// Handle approve / reject / return / flag from the individual submission modal
async function handleSubmission(action, buttonEl) {
    if (!currentSubmissionId) {
        showErrorModal('No submission selected.');
        return;
    }

    const remarksEl = document.getElementById('individualAssessorRemarks');
    const remarks = remarksEl ? remarksEl.value.trim() : '';

    // Remarks required for negative actions
    if ((action === 'reject' || action === 'return' || action === 'flag') && !remarks) {
        showValidationError('Please provide remarks before performing this action.');
        return;
    }

    let score = null;
    if (action === 'approve') {
        const input = prompt('Enter assessor score (0–100):');
        if (input === null) return; // cancelled

        const parsed = parseFloat(input);
        if (Number.isNaN(parsed) || parsed < 0 || parsed > 100) {
            showValidationError('Please enter a valid score between 0 and 100.');
            return;
        }
        score = parsed;
    }

    // Use the button passed from onclick (no reliance on global event)
    let actionButton = buttonEl || null;
    let originalHtml = '';

    if (actionButton) {
        originalHtml = actionButton.innerHTML;
        actionButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        actionButton.disabled = true;
    }

    try {
        const response = await fetch(`/assessor/submissions/${currentSubmissionId}/action`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') || '',
                Accept: 'application/json',
            },
            body: JSON.stringify({
                action: action,
                remarks: remarks,
                assessor_score: score,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data?.error || 'Failed to process action.');
        }

        // Close modal
        const individualModalEl = document.getElementById('individualSubmissionModal');
        if (individualModalEl) {
            const modal = bootstrap.Modal.getInstance(individualModalEl);
            if (modal) modal.hide();
        }

        // Success modal
        let msg = '';
        if (action === 'approve') {
            msg = 'Submission has been successfully approved!';
        } else if (action === 'reject') {
            msg = 'Submission has been successfully rejected.';
        } else if (action === 'return') {
            msg = 'Submission has been returned to the student for revision.';
        } else if (action === 'flag') {
            msg = 'Submission has been flagged for further review.';
        } else {
            msg = 'Action completed successfully.';
        }

        showSuccessModal('Action completed', msg, function () {
            // Reload to update lists / scores
            window.location.reload();
        });
    } catch (error) {
        console.error('Error processing submission action:', error);
        showErrorModal(
            'Failed to process submission action. Please check your connection and try again.'
        );
    } finally {
        if (actionButton) {
            actionButton.innerHTML = originalHtml || actionButton.innerHTML;
            actionButton.disabled = false;
        }
    }
}

/* -----------------------------
   MODAL: ERROR / VALIDATION / SUCCESS / CONFIRM
----------------------------- */

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

    document.body.appendChild(errorModal);
    const modal = new bootstrap.Modal(errorModal);
    modal.show();

    errorModal.addEventListener('hidden.bs.modal', function () {
        document.body.removeChild(errorModal);
    });
}

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

    document.body.appendChild(validationModal);
    const modal = new bootstrap.Modal(validationModal);
    modal.show();

    validationModal.addEventListener('hidden.bs.modal', function () {
        document.body.removeChild(validationModal);
    });
}

// Generic success modal, with optional onClose callback
function showSuccessModal(title, message, onClose) {
    const successModal = document.createElement('div');
    successModal.className = 'modal fade';
    successModal.id = 'successModal';

    successModal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content success-modal-content">
                <div class="modal-body text-center p-4">
                    <div class="success-icon mb-3">
                        <i class="fas fa-check-circle" style="color: #28a745; font-size: 3rem;"></i>
                    </div>
                    <h5 class="success-title mb-3">${title}</h5>
                    <p class="success-message mb-4">${message}</p>
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                        OK
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(successModal);
    const modal = new bootstrap.Modal(successModal);
    modal.show();

    successModal.addEventListener('hidden.bs.modal', function () {
        document.body.removeChild(successModal);
        if (typeof onClose === 'function') {
            onClose();
        }
    });
}

// Confirmation modal used for READY / NOT READY
function showConfirmModal(title, message, onConfirm) {
    const confirmModal = document.createElement('div');
    confirmModal.className = 'modal fade';
    confirmModal.id = 'confirmModal';

    confirmModal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content validation-modal-content">
                <div class="modal-body text-center p-4">
                    <div class="validation-icon mb-3">
                        <i class="fas fa-question-circle" style="color: #0d6efd; font-size: 3rem;"></i>
                    </div>
                    <h5 class="validation-title mb-3">${title}</h5>
                    <p class="validation-message mb-4">${message}</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-primary" id="confirmModalConfirmBtn">
                            Yes, proceed
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(confirmModal);
    const modal = new bootstrap.Modal(confirmModal);
    modal.show();

    const confirmBtn = confirmModal.querySelector('#confirmModalConfirmBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
            modal.hide();
        });
    }

    confirmModal.addEventListener('hidden.bs.modal', function () {
        document.body.removeChild(confirmModal);
    });
}

/* -----------------------------
   TABLE SEARCH + PAGINATION
----------------------------- */

function initializeStudentPage() {
    const studentRows = document.querySelectorAll('.submissions-table tbody tr');

    initialStudentsData = Array.from(studentRows)
        .filter(row => row.querySelector('td')) // ignore "no data" row
        .map(row => {
            const cells = row.children;
            return {
                element: row,
                studentId: (cells[0]?.textContent || '').toLowerCase(),
                studentName: (cells[1]?.textContent || '').toLowerCase(),
                email: (cells[2]?.textContent || '').toLowerCase(),
                program: (cells[3]?.textContent || '').toLowerCase(),
                college: (cells[4]?.textContent || '').toLowerCase(),
                sleaStatus: row.dataset.sleaStatus || '', // Get status from data-slea-status attribute
            };
        });

    filterAndPaginateStudents();
}

function filterAndPaginateStudents() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilterSelect');
    const searchTerm = (searchInput?.value || '').toLowerCase();
    const statusValue = (statusFilter?.value || '').toLowerCase();

    const filteredStudents = initialStudentsData.filter(student => {
        // Filter by search term
        const matchesSearch = !searchTerm || (
            student.studentId.includes(searchTerm) ||
            student.studentName.includes(searchTerm) ||
            student.email.includes(searchTerm) ||
            student.program.includes(searchTerm) ||
            student.college.includes(searchTerm)
        );

        // Filter by status
        const matchesStatus = !statusValue || student.sleaStatus === statusValue;

        return matchesSearch && matchesStatus;
    });

    totalEntries = filteredStudents.length;
    totalPages = Math.ceil(totalEntries / entriesPerPage) || 1;

    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    // Hide all
    initialStudentsData.forEach(s => {
        s.element.style.display = 'none';
    });

    const start = (currentPage - 1) * entriesPerPage;
    const end = start + entriesPerPage;

    filteredStudents.slice(start, end).forEach(s => {
        s.element.style.display = '';
    });

    updatePaginationInfo();
    generatePageButtons();
    updateNavigationButtons();
}

function updatePaginationInfo() {
    const showingStart = document.getElementById('showingStart');
    const showingEnd = document.getElementById('showingEnd');
    const totalEntriesEl = document.getElementById('totalEntries');

    const start = totalEntries === 0 ? 0 : (currentPage - 1) * entriesPerPage + 1;
    const end = Math.min(currentPage * entriesPerPage, totalEntries);

    if (showingStart) showingStart.textContent = start;
    if (showingEnd) showingEnd.textContent = end;
    if (totalEntriesEl) totalEntriesEl.textContent = totalEntries;
}

function generatePageButtons() {
    const paginationPages = document.getElementById('paginationPages');
    if (!paginationPages) return;

    paginationPages.innerHTML = '';

    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, startPage + 4);

    if (endPage - startPage < 4) {
        startPage = Math.max(1, endPage - 4);
    }

    for (let i = startPage; i <= endPage; i++) {
        const pageBtn = document.createElement('button');
        pageBtn.className = 'pagination-page';
        if (i === currentPage) pageBtn.classList.add('active');
        pageBtn.textContent = String(i);
        pageBtn.addEventListener('click', () => {
            currentPage = i;
            filterAndPaginateStudents();
        });
        paginationPages.appendChild(pageBtn);
    }
}

function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');

    if (prevBtn) {
        prevBtn.disabled = currentPage === 1;
        prevBtn.onclick = () => {
            if (currentPage > 1) {
                currentPage--;
                filterAndPaginateStudents();
            }
        };
    }

    if (nextBtn) {
        nextBtn.disabled = currentPage === totalPages || totalPages === 0;
        nextBtn.onclick = () => {
            if (currentPage < totalPages) {
                currentPage++;
                filterAndPaginateStudents();
            }
        };
    }
}

/* -----------------------------
   DOM READY
----------------------------- */

document.addEventListener('DOMContentLoaded', function () {
    // Initialize table search + pagination
    initializeStudentPage();

    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const clearBtn = document.getElementById('clearBtn');

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            currentPage = 1;
            filterAndPaginateStudents();
        });
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                currentPage = 1;
                filterAndPaginateStudents();
            }
        });
    }

    // Search button click
    if (searchBtn) {
        searchBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            currentPage = 1;
            filterAndPaginateStudents();
        });
    }

    // Clear button click
    if (clearBtn) {
        clearBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (searchInput) {
                searchInput.value = '';
                currentPage = 1;
                filterAndPaginateStudents();
                searchInput.focus();
            }
        });
    }

    // Add event listener for status filter dropdown
    const statusFilter = document.getElementById('statusFilterSelect');
    if (statusFilter) {
        statusFilter.addEventListener('change', () => {
            currentPage = 1;
            filterAndPaginateStudents();
        });
    }

    // READY / NOT READY buttons inside the All Submissions modal
    const readyBtn = document.getElementById('btnMarkReadyForRating');
    if (readyBtn) {
        readyBtn.addEventListener('click', function () {
showConfirmModal(
    'Mark student as READY for rating?',
    'This will mark the student as READY for rating...',
    function () {
        updateReadyForRating(true);
    }
);

        });
    }

    const notReadyBtn = document.getElementById('btnMarkNotReadyForRating');
    if (notReadyBtn) {
        notReadyBtn.addEventListener('click', function () {
            showConfirmModal(
                'Mark student as NOT ready?',
                'This will mark the student as NOT ready for rating. They will stay in your All Submissions list.',
                function () {
                    updateReadyForRating(false);
                }
            );
        });
    }

    // When individual modal opens, hide student list modal (if open)
    const individualSubmissionModalElement =
        document.getElementById('individualSubmissionModal');
    if (individualSubmissionModalElement) {
        individualSubmissionModalElement.addEventListener(
            'show.bs.modal',
            function () {
                const studentSubmissionsModalEl =
                    document.getElementById('studentSubmissionsModal');
                if (studentSubmissionsModalEl) {
                    const studentSubmissionsModal =
                        bootstrap.Modal.getInstance(studentSubmissionsModalEl);
                    if (studentSubmissionsModal) {
                        studentSubmissionsModal.hide();
                    }
                }
            }
        );
    }
});
