@extends('layouts.app')

@section('title', 'Pending Submissions - Assessor Dashboard')
@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="container-fluid assessor-pending-submissions-page">
    @include('partials.sidebar')

    <main class="main-content">
        <div class="page-header">
            <h1>Pending Submissions</h1>
        </div>

        <!-- Search and Sort Controls -->
        <div class="controls-section">
            <div class="filter-controls">
                <div class="filter-group">
                    <label for="sortSelect">Sort by</label>
                    <select id="sortSelect" class="form-select">
                        <option value="">None</option>
                        <option value="date">Date Submitted</option>
                        <option value="name">Student Name</option>
                        <option value="title">Document Title</option>
                    </select>
                </div>
            </div>

            <div class="search-controls">
                <div class="search-group">
                    <input
                        type="text"
                        id="searchInput"
                        class="form-control"
                        placeholder="Search submissions..."
                    >
                    <button type="button" id="searchBtn" class="btn-search-maroon search-btn-attached" title="Search" onclick="handleSearchClick(event)">
                        <i class="fas fa-search"></i>
                    </button>
                    <button type="button" id="clearBtn" class="btn-clear" title="Clear search" onclick="handleClearClick(event)">
                        Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- Submissions Table -->
        <div class="submissions-table-container">
            <table class="table submissions-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Document Title</th>
                        <th>Date Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendingSubmissions as $submission)
                        @php
                            $studentId = optional($submission->user->studentAcademic)->student_number
                                ?? $submission->user->student_id
                                ?? $submission->user->id;

                            $studentName = $submission->user->full_name
                                ?? trim(($submission->user->first_name ?? '') . ' ' . ($submission->user->last_name ?? ''));
                        @endphp

                        <tr data-submission-id="{{ $submission->id }}">
                            <td>{{ $studentId }}</td>
                            <td>{{ $studentName }}</td>
                            <td>{{ $submission->activity_title }}</td>
                            <td>{{ optional($submission->submitted_at)->format('Y-m-d') }}</td>
                            <td>
                                <button
                                    class="btn btn-view"
                                    data-submission-id="{{ $submission->id }}"
                                    onclick="openSubmissionModalFromButton(this)"
                                    title="View Submission"
                                >
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row="true">
                            <td colspan="5" class="text-center">No pending submissions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container" data-pagination-container>
            <div class="pagination-info">
                <!-- Filled by admin_pagination.js -->
            </div>

            <div class="unified-pagination">
                <button class="btn-nav" id="prevBtn" disabled>
                    <i class="fas fa-chevron-left"></i> Previous
                </button>

                <span class="pagination-pages" id="paginationPages"></span>

                <button class="btn-nav" id="nextBtn" disabled>
                    Next <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- Review Submission Modal -->
        <div
            class="modal fade"
            id="submissionModal"
            tabindex="-1"
            aria-labelledby="submissionModalLabel"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-xl">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title" id="submissionModalLabel">Review Submission</h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                    </div>

                    <div class="modal-body">
                        <div class="submission-content">

                            <!-- Student Details Card -->
                            <div class="info-card">
                                <div class="card-header">
                                    <h6 class="card-title">Student Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="detail-row">
                                        <span class="label">Student ID:</span>
                                        <span class="value" id="modalStudentId">-</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Student Name:</span>
                                        <span class="value" id="modalStudentName">-</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Document Title:</span>
                                        <span class="value" id="modalDocumentTitle">-</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Date Submitted:</span>
                                        <span class="value" id="modalDateSubmitted">-</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Document Information Card -->
                            <div class="info-card">
                                <div class="card-header">
                                    <h6 class="card-title">Document Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="detail-row">
                                        <span class="label">SLEA Section:</span>
                                        <span class="value" id="modalSleaSection">-</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">SLEA Category:</span>
                                        <span class="value" id="modalSleaCategory">-</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Subsection:</span>
                                        <span class="value" id="modalSubsection">-</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Role in Activity:</span>
                                        <span class="value" id="modalRole">-</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Activity Date:</span>
                                        <span class="value" id="modalActivityDate">-</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Organizing Body:</span>
                                        <span class="value" id="modalOrganizingBody">-</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Description:</span>
                                        <span class="value" id="modalDescription">-</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Application Status:</span>
                                        <span class="value" id="modalApplicationStatus">-</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Uploaded Documents Card -->
                            <div class="info-card">
                                <div class="card-header">
                                    <h6 class="card-title">Uploaded Documents</h6>
                                </div>
                                <div class="card-body">
                                    <div id="documentList" class="document-preview">
                                        <p class="text-muted mb-0">No documents uploaded.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Rubric-based Score Card -->
                            <div class="info-card">
                                <div class="card-header">
                                    <h6 class="card-title">Rubric-based Score</h6>
                                    <div class="small text-danger fw-bold mt-1">
                                        Choose the descriptor that best matches the student's submission.
                                        The selected points will be recorded as the score.
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="rubricMismatchBanner" class="rubric-mismatch-banner d-none" role="alert"></div>
                                    <div class="rubric-score-header">
                                        <span class="score-label">Selected Score:</span>
                                        <span class="score-pill" id="modalAutoScore">Not calculated</span>
                                    </div>

                                    <div id="rubricOptionsContainer" class="rubric-options-container">
                                        <p class="text-muted mb-0">No rubric options loaded.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Assessor Remarks Card -->
                            <div class="info-card">
                                <div class="card-header">
                                    <h6 class="card-title">Assessor Remarks (Optional)</h6>
                                </div>
                                <div class="card-body">
                                    <textarea
                                        id="assessorRemarks"
                                        class="form-control remarks-textarea"
                                        rows="4"
                                        placeholder="Enter your remarks and feedback..."
                                    ></textarea>
                                    <small class="remarks-note">
                                        Remarks are required for Reject, Return, and Flag actions.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-buttons-container">
                            <button
                                type="button"
                                class="btn btn-approve"
                                onclick="handleSubmission('approve')"
                                title="Approve Submission"
                            >
                                <i class="fas fa-check"></i>
                            </button>
                            <button
                                type="button"
                                class="btn btn-reject"
                                onclick="handleSubmission('reject')"
                                title="Reject Submission"
                            >
                                <i class="fas fa-times"></i>
                            </button>
                            <button
                                type="button"
                                class="btn btn-return"
                                onclick="handleSubmission('return')"
                                title="Return to Student"
                            >
                                <i class="fas fa-undo"></i>
                            </button>
                            <button
                                type="button"
                                class="btn btn-flag"
                                onclick="handleSubmission('flag')"
                                title="Flag for Admin Review"
                            >
                                <i class="fas fa-flag"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Confirmation Modal (for approve / reject / return / flag) -->
        <div
            class="modal fade"
            id="confirmationModal"
            tabindex="-1"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content confirmation-modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmationModalTitle">Confirm Action</h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <p id="confirmationModalBody">
                            Are you sure you want to perform this action?
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="modal"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="btn btn-primary"
                            id="confirmActionBtn"
                        >
                            Yes, continue
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<link rel="stylesheet" href="{{ asset('css/pending-submissions.css') }}">
<style>
    /* This app uses a fixed header; most pages rely on .container { margin-top:80px } (style.css).
       This view uses container-fluid for full-width tables, so apply the same offset here. */
    .assessor-pending-submissions-page {
        margin-top: 80px;
    }

    /* Expand table container to match the width feel of the assessor "All Submissions" page */
    .assessor-pending-submissions-page .submissions-table-container {
        width: 100%;
        max-width: none;
    }

    .assessor-pending-submissions-page .submissions-table {
        width: 100%;
    }

    /* Rubric mismatch UX (assessor modal) */
    .rubric-mismatch-banner {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 12px;
        margin-bottom: 12px;
        border: 1px solid rgba(220, 53, 69, 0.35);
        border-left: 4px solid #dc3545;
        background: rgba(220, 53, 69, 0.06);
        color: #842029;
        border-radius: 10px;
        font-weight: 600;
        line-height: 1.35;
    }
    .rubric-mismatch-banner .icon {
        flex: 0 0 auto;
        margin-top: 1px;
        color: #dc3545;
    }
    .rubric-mismatch-banner .text strong {
        font-weight: 800;
    }

    .rubric-option {
        border: 1px solid transparent;
        border-radius: 10px;
        padding: 10px 12px;
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }
    .rubric-option.expected {
        border-color: rgba(25, 135, 84, 0.35);
        background: rgba(25, 135, 84, 0.05);
    }
    .rubric-option.mismatch {
        border-color: rgba(220, 53, 69, 0.6);
        background: rgba(220, 53, 69, 0.06);
    }
    .rubric-option.mismatch .form-check-label,
    .rubric-option.mismatch .form-check-label strong,
    .rubric-option.mismatch .rubric-points {
        color: #dc3545 !important;
        font-weight: 700 !important;
    }
    .rubric-option.mismatch .form-check-input {
        accent-color: #dc3545;
    }
</style>
<script src="{{ asset('js/admin_pagination.js') }}"></script>
<script src="{{ asset('js/pending-submission.js') }}"></script>
@endsection
