@extends('layouts.app')

@section('title', 'Pending Submissions - Assessor Dashboard')
@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="container">
    @include('partials.sidebar')

    <main class="main-content">
        <div class="page-header">
            <h1>Pending Submissions</h1>
        </div>

        <!-- Filter and Search Controls -->
        <div class="controls-section">
            <div class="filter-controls">
                <div class="filter-group">
                    <label for="filterSelect">Filter</label>
                    <select id="filterSelect" class="form-select">
                        <option value="">None</option>
                        <option value="recent">Recent</option>
                        <option value="overdue">Overdue</option>
                        <option value="priority">Priority</option>
                    </select>
                </div>

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
                    <input type="text" id="searchInput" class="form-control" placeholder="Search submissions...">
                    <i class="fas fa-search search-icon"></i>
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
                    @forelse($pendingSubmissions as $submission)
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
                            <button class="btn btn-view" onclick="openSubmissionModal({{ $submission->id }})" title="View Submission">
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

        <!-- Pagination (info + controls are handled by admin_pagination.js) -->
        <div class="pagination-container">
            <div class="pagination-info">
                <!-- Populated dynamically -->
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
    </main>
</div>

<!-- Review Submission Modal -->
<div class="modal fade" id="submissionModal" tabindex="-1" aria-labelledby="submissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="submissionModalLabel">Review Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="submission-content">
                    <!-- Student Details Panel -->
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
                        </div>
                    </div>

                    <!-- Uploaded Documents (no inline preview) -->
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

                    <!-- Rubric-based Scoring (cleaner layout) -->
                    <div class="info-card">
                        <div class="card-header">
                            <h6 class="card-title">Rubric-based Score</h6>
                        </div>
                        <div class="card-body">
                            <div class="rubric-score-header">
                                <span class="score-label">Selected Score</span>
                                <span class="score-pill" id="modalAutoScore">Not calculated</span>
                            </div>

                            <div id="rubricOptionsContainer" class="rubric-options-container">
                                <p class="text-muted mb-0">No rubric options loaded.</p>
                            </div>

                            <small class="remarks-note">
                                Choose the descriptor that best matches the student's submission. The selected points will be recorded as the score.
                            </small>
                        </div>
                    </div>

                    <!-- Assessor Remarks -->
                    <div class="info-card">
                        <div class="card-header">
                            <h6 class="card-title">Assessor Remarks (Optional)</h6>
                        </div>
                        <div class="card-body">
                            <textarea id="assessorRemarks" class="form-control remarks-textarea" rows="4" placeholder="Enter your remarks and feedback..."></textarea>
                            <small class="remarks-note">Remarks are required for Reject, Return, and Flag actions.</small>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons-container">
                    <button type="button" class="btn btn-approve" onclick="handleSubmission('approve')" title="✅ Approve">
                        <i class="fas fa-check"></i>
                    </button>
                    <button type="button" class="btn btn-reject" onclick="handleSubmission('reject')" title="❌ Reject">
                        <i class="fas fa-times"></i>
                    </button>
                    <button type="button" class="btn btn-return" onclick="handleSubmission('return')" title="↩ Return to Student">
                        <i class="fas fa-undo"></i>
                    </button>
                    <button type="button" class="btn btn-flag" onclick="handleSubmission('flag')" title="🚩 Flag for Admin">
                        <i class="fas fa-flag"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Keep your stylesheet reference; most of the CSS you had can live in pending-submissions.css --}}
<link rel="stylesheet" href="{{ asset('css/pending-submissions.css') }}">

<script src="{{ asset('js/admin_pagination.js') }}"></script>
<script src="{{ asset('js/pending-submission.js') }}"></script>
@endsection