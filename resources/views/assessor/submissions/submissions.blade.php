@extends('layouts.app')

@section('title', 'All Submissions - Assessor Dashboard')

@section('content')
    <div class="container">

        {{-- Sidebar --}}
        @include('partials.sidebar')

        <main class="main-content">

            {{-- Page Header --}}
            <div class="page-header">
                <h1>All Submissions</h1>
            </div>

            {{-- Filters + Search --}}
            <div class="controls-section">
                <div class="filter-controls">
                    <div class="filter-group">
                        <label for="statusFilterSelect">Filter by Status</label>
                        <select id="statusFilterSelect" class="form-select">
                            <option value="">All</option>
                            <option value="not_ready">Not ready</option>
                            <option value="ready_assessor">Ready for assessor review</option>
                            <option value="for_admin_review">For admin review</option>
                            <option value="awarded">Awarded</option>
                            <option value="rejected">Not qualified</option>
                            <option value="not_4th_year">Not 4th year</option>
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

            {{-- Table: Students With Accepted / Reviewed Submissions --}}
            <div class="submissions-table-container">
                <table class="table submissions-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Program</th>
                            <th>College</th>
                            <th>SLEA Status</th>
                            <th>Date Reviewed</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($students as $student)
                                        @php
                                            $academic = $student->user->studentAcademic ?? null;
                                            $yearLevel = $academic->year_level ?? null;
                                            $readyFlag = (int) ($academic->ready_for_rating ?? 0);
                                            $appStatus = $academic->slea_application_status ?? null;

                                            // Defaults
                                            $sleaStatusLabel = 'Not ready';
                                            $sleaStatusClass = 'slea-status-pill--not-ready';
                                            $sleaStatusKey = 'not_ready';

                                            if (!$academic) {
                                                $sleaStatusLabel = 'No academic record';
                                                $sleaStatusClass = 'slea-status-pill--no-record';
                                                $sleaStatusKey = 'no_record';

                                            } elseif ((string) $yearLevel !== '4') {
                                                // Explicit status for non-4th years
                                                $sleaStatusLabel = 'Not in 4th year';
                                                $sleaStatusClass = 'slea-status-pill--not-4th';
                                                $sleaStatusKey = 'not_4th_year';

                                            } else {
                                                // Normalize: if null, treat as "not_ready"
                                                $statusKey = $appStatus ?: 'not_ready';

                                                switch ($statusKey) {
                                                    case 'not_ready':
                                                        $sleaStatusLabel = 'Not ready';
                                                        $sleaStatusClass = 'slea-status-pill--not-ready';
                                                        $sleaStatusKey = 'not_ready';
                                                        break;

                                                    case 'ready_for_assessor':
                                                        $sleaStatusLabel = 'Ready for assessor review';
                                                        $sleaStatusClass = 'slea-status-pill--ready-assessor';
                                                        $sleaStatusKey = 'ready_for_assessor';
                                                        break;

                                                    case 'for_admin_review':
                                                        $sleaStatusLabel = 'For admin final review';
                                                        $sleaStatusClass = 'slea-status-pill--for-admin';
                                                        $sleaStatusKey = 'for_admin_review';
                                                        break;

                                                    case 'awarded':
                                                        $sleaStatusLabel = 'Awarded';
                                                        $sleaStatusClass = 'slea-status-pill--awarded';
                                                        $sleaStatusKey = 'awarded';
                                                        break;

                                                    case 'not_qualified':
                                                        $sleaStatusLabel = 'Not qualified';
                                                        $sleaStatusClass = 'slea-status-pill--rejected';
                                                        $sleaStatusKey = 'not_qualified';
                                                        break;

                                                    default:
                                                        $sleaStatusLabel = 'In process';
                                                        $sleaStatusClass = 'slea-status-pill--in-process';
                                                        $sleaStatusKey = $statusKey; // keep whatever enum key it is
                                                        break;
                                                }
                                            }
                                        @endphp


                                        <tr data-student-id="{{ $student->id }}" data-slea-status="{{ $sleaStatusKey }}">
                                            <td>{{ $student->student_id }}</td>
                                            <td>{{ $student->user->full_name }}</td>
                                            <td>{{ $student->user->email }}</td>
                                            <td>{{ $student->program }}</td>
                                            <td>{{ $student->college }}</td>
                                            <td>
                                                <span class="slea-status-pill {{ $sleaStatusClass }}">
                                                    {{ $sleaStatusLabel }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $student->latest_reviewed_at
                            ? \Carbon\Carbon::parse($student->latest_reviewed_at)->format('Y-m-d')
                            : 'N/A' }}
                                            </td>
                                            <td>
                                                <button class="btn btn-view" onclick="openStudentSubmissionsModal({{ $student->id }})"
                                                    title="View Submissions">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>

                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    No students with accepted submissions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="pagination-container">
                <div class="pagination-info">
                    Showing
                    <span id="showingStart">1</span> -
                    <span id="showingEnd">{{ $students->count() }}</span>
                    of
                    <span id="totalEntries">{{ $students->count() }}</span>
                    students
                </div>

                <div class="pagination-controls">
                    <button class="pagination-btn" id="prevBtn" disabled>
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>

                    <span class="pagination-pages" id="paginationPages"></span>

                    <button class="pagination-btn" id="nextBtn">
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>

        </main>
    </div>

    {{-- ===========================
    MODAL: STUDENT SUBMISSION LIST
    =========================== --}}
    <div class="modal fade assessor-modal" id="studentSubmissionsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xxl modal-dialog-scrollable">
            <div class="modal-content">

                {{-- Header --}}
                <div class="modal-header">
                    <h5 class="modal-title">
                        All Submissions for
                        <span id="modalStudentNameTitle"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                {{-- Body --}}
                <div class="modal-body">

                    {{-- Student Info --}}
                    <div class="student-details-card info-card mb-4">
                        <div class="card-header">
                            <h6 class="card-title">Student Information</h6>
                        </div>

                        <div class="card-body">
                            <div class="detail-row">
                                <span class="label">Student ID:</span>
                                <span class="value" id="modalStudentIdDetail"></span>
                            </div>

                            <div class="detail-row">
                                <span class="label">Program:</span>
                                <span class="value" id="modalStudentProgramDetail"></span>
                            </div>

                            <div class="detail-row">
                                <span class="label">College:</span>
                                <span class="value" id="modalStudentCollegeDetail"></span>
                            </div>

                            <div class="detail-row">
                                <span class="label">Major:</span>
                                <span class="value" id="modalStudentMajorDetail"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Categorized Submissions (JS renders here) --}}
                    <div id="categorizedSubmissionsContainer">
                        {{-- JS will render the category tables here --}}
                    </div>

                    {{-- Ready for Rating decision --}}
                    <div class="slea-decision mt-4">
                        <p class="slea-decision-text">
                            After reviewing the scores per category above, choose whether this student is
                            <strong>ready for rating</strong> or not. This will mark your decision for this student and
                            show them in your <strong>Assessor Final Review</strong> page if you mark them as ready.
                            It does <em>not</em> yet send the records to the Admin.
                        </p>

                        <div class="slea-decision-actions">
                            <button type="button" class="btn btn-success" id="btnMarkReadyForRating">
                                Student is READY for Admin Review
                            </button>

                            <button type="button" class="btn btn-outline-secondary" id="btnMarkNotReadyForRating">
                                Student is NOT ready
                            </button>
                        </div>

                        <small class="slea-decision-note" id="readyForRatingStatusNote">
                            You can still adjust this decision later from the Assessor Final Review page before sending
                            qualified students to the Admin Final Review.
                        </small>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ===========================
    MODAL: INDIVIDUAL SUBMISSION REVIEW
    =========================== --}}
    <div class="modal fade" id="individualSubmissionModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xxl modal-dialog-scrollable">
            <div class="modal-content">

                {{-- Header --}}
                <div class="modal-header">
                    <h5 class="modal-title">Review Submission</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                {{-- Body --}}
                <div class="modal-body">

                    <div class="submission-content">

                        {{-- Student Details --}}
                        <div class="info-card">
                            <div class="card-header">
                                <h6 class="card-title">Student Details</h6>
                            </div>

                            <div class="card-body">
                                <div class="detail-row">
                                    <span class="label">Student ID:</span>
                                    <span class="value" id="modalIndividualStudentId"></span>
                                </div>

                                <div class="detail-row">
                                    <span class="label">Student Name:</span>
                                    <span class="value" id="modalIndividualStudentName"></span>
                                </div>

                                <div class="detail-row">
                                    <span class="label">Document Title:</span>
                                    <span class="value" id="modalIndividualDocumentTitle"></span>
                                </div>

                                <div class="detail-row">
                                    <span class="label">Date Submitted:</span>
                                    <span class="value" id="modalIndividualDateSubmitted"></span>
                                </div>

                                <div class="detail-row">
                                    <span class="label">Current Status:</span>
                                    <span class="value" id="modalIndividualStatus"></span>
                                </div>

                                <div class="detail-row">
                                    <span class="label">Assigned Assessor:</span>
                                    <span class="value" id="modalIndividualAssessorName"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Document Information --}}
                        <div class="info-card">
                            <div class="card-header">
                                <h6 class="card-title">Document Information</h6>
                            </div>

                            <div class="card-body">
                                <div class="detail-row">
                                    <span class="label">SLEA Section:</span>
                                    <span class="value" id="modalIndividualSleaSection"></span>
                                </div>

                                <div class="detail-row">
                                    <span class="label">Subsection:</span>
                                    <span class="value" id="modalIndividualSubsection"></span>
                                </div>

                                <div class="detail-row">
                                    <span class="label">Role in Activity:</span>
                                    <span class="value" id="modalIndividualRole"></span>
                                </div>

                                <div class="detail-row">
                                    <span class="label">Activity Date:</span>
                                    <span class="value" id="modalIndividualActivityDate"></span>
                                </div>

                                <div class="detail-row">
                                    <span class="label">Organizing Body:</span>
                                    <span class="value" id="modalIndividualOrganizingBody"></span>
                                </div>

                                <div class="detail-row">
                                    <span class="label">Description:</span>
                                    <span class="value" id="modalIndividualDescription"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Uploaded Documents --}}
                        <div class="info-card">
                            <div class="card-header">
                                <h6 class="card-title">Uploaded Document</h6>
                            </div>
                            <div class="card-body">
                                <div id="individualDocumentPreview" class="document-preview"></div>
                            </div>
                        </div>

                        {{-- Auto Score --}}
                        <div class="info-card">
                            <div class="card-header">
                                <h6 class="card-title">System Auto-Generated Score</h6>
                            </div>
                            <div class="card-body">
                                <div class="score-display">
                                    <span id="modalIndividualAutoScore" class="score-value">-</span>
                                </div>
                            </div>
                        </div>

                        {{-- Remarks --}}
                        <div class="info-card">
                            <div class="card-header">
                                <h6 class="card-title">Assessor Remarks</h6>
                            </div>

                            <div class="card-body">
                                <textarea id="individualAssessorRemarks" class="form-control remarks-textarea" rows="4"
                                    placeholder="Remarks..."></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="action-buttons-container">
                        <button type="button" class="btn btn-approve" onclick="handleSubmission('approve', this)">
                            <i class="fas fa-check"></i>
                        </button>

                        <button type="button" class="btn btn-reject" onclick="handleSubmission('reject', this)">
                            <i class="fas fa-times"></i>
                        </button>

                        <button type="button" class="btn btn-return" onclick="handleSubmission('return', this)">
                            <i class="fas fa-undo"></i>
                        </button>

                        <button type="button" class="btn btn-flag" onclick="handleSubmission('flag', this)">
                            <i class="fas fa-flag"></i>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ===========================
    STYLES
    =========================== --}}
    <style>
        /* ==== WIDER MODAL FOR ALL SUBMISSIONS (studentSubmissionsModal) ==== */
        .assessor-modal .modal-dialog {
            max-width: 95% !important;
            width: 95% !important;
            margin: 1.5rem auto;
        }

        .assessor-modal .modal-content {
            min-height: 80vh;
            border-radius: 12px;
        }

        .assessor-modal .modal-body {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }

        /* ==== Page Header ==== */
        .page-header {
            margin-bottom: 1.5rem;
        }

        .page-header h1 {
            color: #8B0000;
            font-size: 2rem;
            margin-bottom: 0;
            font-weight: 700;
        }

        body.dark-mode .page-header h1 {
            color: #f9bd3d !important;
        }

        /* ==== Filter + Search Bar ==== */
        .controls-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 2rem;
            gap: 2rem;
        }

        .filter-controls {
            display: flex;
            gap: 1.5rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-group label {
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }

        .form-select {
            min-width: 150px;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
        }

        .search-controls {
            flex: 1;
            max-width: 300px;
        }

        .search-group {
            position: relative;
        }

        .search-group input {
            width: 100%;
            padding: 0.5rem 2.5rem 0.5rem 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .search-icon {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            pointer-events: none;
        }

        /* ==== Main table ==== */
        .submissions-table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .submissions-table {
            margin: 0;
            width: 100%;
            background: white;
        }

        .submissions-table thead {
            background-color: #8B0000 !important;
        }

        .submissions-table thead th {
            padding: 1rem;
            font-weight: 600;
            color: white !important;
            border-bottom: 1px solid white !important;
            border-right: 1px solid white !important;
            font-size: 0.9rem;
            background-color: #8B0000 !important;
        }

        .submissions-table thead th:last-child {
            border-right: none !important;
        }

        .submissions-table tbody td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            border-right: 1px solid #e9ecef;
            color: #333;
            font-size: 0.9rem;
            background: white;
        }

        .submissions-table tbody td:last-child {
            border-right: none;
        }

        .submissions-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* ==== SLEA status pill ==== */
        .slea-status-pill {
            display: inline-block;
            padding: 0.15rem 0.6rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .slea-status-pill--no-record {
            background: #e5e7eb;
            color: #374151;
            border-color: #d1d5db;
        }

        .slea-status-pill--not-4th {
            background: #eef2ff;
            color: #3730a3;
            border-color: #c4b5fd;
        }

        .slea-status-pill--not-ready {
            background: #fef3c7;
            color: #92400e;
            border-color: #fbbf24;
        }

        .slea-status-pill--ready-assessor {
            background: #e0f2fe;
            color: #075985;
            border-color: #7dd3fc;
        }

        .slea-status-pill--for-admin {
            background: #cffafe;
            color: #0e7490;
            border-color: #22d3ee;
        }

        .slea-status-pill--awarded {
            background: #dcfce7;
            color: #166534;
            border-color: #22c55e;
        }

        .slea-status-pill--rejected {
            background: #fee2e2;
            color: #991b1b;
            border-color: #f87171;
        }

        .slea-status-pill--in-process {
            background: #e5e7eb;
            color: #374151;
            border-color: #d1d5db;
        }

        /* ==== Info cards (inside modals) ==== */
        .info-card {
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .info-card .card-header {
            background: #f9fafb;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-card .card-title {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 600;
            color: #111827;
        }

        .info-card .card-body {
            padding: 0.85rem 1rem 1rem;
        }

        .detail-row {
            display: flex;
            align-items: baseline;
            margin-bottom: 0.4rem;
            font-size: 0.9rem;
        }

        .detail-row .label {
            width: 160px;
            font-weight: 600;
            color: #4b5563;
        }

        .detail-row .value {
            flex: 1;
            color: #111827;
        }

        /* ==== Category sections / totals (JS-generated) ==== */
        .slea-category-section {
            margin-bottom: 2.5rem;
        }

        .category-title {
            font-size: 1rem;
            font-weight: 600;
            color: #111827;
        }

        .category-total-row td {
            background: #f9fafb;
            font-size: 0.9rem;
        }

        .overall-total-section {
            margin-top: 1rem;
        }

        .overall-total-row td {
            background: #111827;
            color: #f9fafb;
            font-weight: 600;
            font-size: 0.95rem;
        }

        /* ==== Document preview list ==== */
        .document-preview {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .document-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0.9rem;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .document-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .document-icon {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .document-icon.image {
            background: #dbeafe;
        }

        .document-icon.pdf {
            background: #fee2e2;
        }

        .document-icon.other {
            background: #e5e7eb;
        }

        .document-details h6 {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .document-details small {
            color: #6b7280;
            font-size: 0.8rem;
        }

        .document-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-preview,
        .btn-download {
            border-radius: 999px;
            border: 1px solid #8B0000;
            padding: 0.25rem 0.75rem;
            font-size: 0.8rem;
            background: #ffffff;
            color: #8B0000;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
        }

        .btn-preview:hover,
        .btn-download:hover {
            background: #8B0000;
            color: #ffffff;
        }

        /* ==== Status badges for submissions inside modal ==== */
        .status-badge {
            display: inline-block;
            padding: 0.15rem 0.6rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-accepted {
            background: #dcfce7;
            color: #166534;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-returned {
            background: #fef3c7;
            color: #92400e;
        }

        .status-flagged {
            background: #fee2e2;
            color: #b91c1c;
        }

        .status-pending {
            background: #e5e7eb;
            color: #374151;
        }

        /* ==== Auto score display ==== */
        .score-display {
            display: flex;
            align-items: center;
            justify-content: flex-start;
        }

        .score-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #8B0000;
        }

        /* ==== Action buttons under individual modal ==== */
        .action-buttons-container {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-top: 1.2rem;
        }

        .action-buttons-container .btn {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border: none;
            color: #ffffff;
            cursor: pointer;
            transition: transform 0.12s ease, box-shadow 0.12s ease, opacity 0.12s ease;
        }

        .btn-approve {
            background: #22c55e;
        }

        .btn-reject {
            background: #b91c1c;
        }

        .btn-return {
            background: #f97316;
        }

        .btn-flag {
            background: #eab308;
        }

        .action-buttons-container .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .action-buttons-container .btn i {
            font-size: 1rem;
        }

        /* ==== Ready for rating area ==== */
        .slea-decision-text {
            font-size: 0.95rem;
            color: #4b5563;
        }

        .slea-decision-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 0.75rem;
        }

        .slea-decision-note {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: #6b7280;
            font-style: italic;
        }

        /* ==== Validation & Success modals (used in JS) ==== */
        .validation-modal-content,
        .success-modal-content {
            border-radius: 12px;
            border: none;
        }

        .validation-title,
        .success-title {
            font-size: 1.1rem;
            font-weight: 700;
        }

        .validation-message,
        .success-message {
            font-size: 0.95rem;
        }

        .validation-icon i,
        .success-icon i {
            filter: drop-shadow(0 0 4px rgba(0, 0, 0, 0.15));
        }

        /* ==== Dark mode overrides ==== */
        body.dark-mode .form-select {
            background-color: #2a2a2a !important;
            border-color: #555 !important;
            color: #f0f0f0 !important;
        }

        body.dark-mode .form-select:focus {
            background-color: #2a2a2a !important;
            border-color: #f9bd3d !important;
            color: #f0f0f0 !important;
            box-shadow: 0 0 0 0.2rem rgba(249, 189, 61, 0.25) !important;
        }

        body.dark-mode .search-group input {
            background-color: #2a2a2a !important;
            border-color: #555 !important;
            color: #f0f0f0 !important;
        }

        body.dark-mode .search-group input:focus {
            background-color: #2a2a2a !important;
            border-color: #f9bd3d !important;
            color: #f0f0f0 !important;
            box-shadow: 0 0 0 0.2rem rgba(249, 189, 61, 0.25) !important;
        }

        body.dark-mode .search-icon {
            color: #aaa !important;
        }

        body.dark-mode .filter-group label {
            color: #f0f0f0 !important;
        }

        body.dark-mode .submissions-table-container {
            background: #2a2a2a !important;
            border: 1px solid #555 !important;
        }

        body.dark-mode .submissions-table {
            background: #2a2a2a !important;
        }

        body.dark-mode .submissions-table thead {
            background-color: #8B0000 !important;
        }

        body.dark-mode .submissions-table thead th {
            color: white !important;
            border-bottom: 1px solid white !important;
            border-right: 1px solid white !important;
            background-color: #8B0000 !important;
        }

        body.dark-mode .submissions-table tbody td {
            background: #363636 !important;
            color: #f0f0f0 !important;
            border-bottom: 1px solid #555 !important;
            border-right: 1px solid #555 !important;
        }

        body.dark-mode .submissions-table tbody tr:hover {
            background-color: #404040 !important;
        }

        body.dark-mode .info-card {
            background: #262626 !important;
            border-color: #444 !important;
        }

        body.dark-mode .info-card .card-header {
            background: #1f2937 !important;
            border-bottom-color: #444 !important;
        }

        body.dark-mode .card-title {
            color: #f9fafb !important;
        }

        body.dark-mode .detail-row .label {
            color: #d1d5db !important;
        }

        body.dark-mode .detail-row .value {
            color: #f9fafb !important;
        }

        body.dark-mode .document-item {
            background: #1f2937 !important;
            border-color: #4b5563 !important;
        }

        body.dark-mode .document-details small {
            color: #9ca3af !important;
        }

        body.dark-mode .btn-preview,
        body.dark-mode .btn-download {
            background: #111827 !important;
            color: #f9fafb !important;
            border-color: #8B0000 !important;
        }

        body.dark-mode .btn-preview:hover,
        body.dark-mode .btn-download:hover {
            background: #8B0000 !important;
            color: #ffffff !important;
        }

        body.dark-mode .overall-total-row td {
            background: #111827 !important;
            color: #f9fafb !important;
        }

        body.dark-mode .slea-decision-text,
        body.dark-mode .slea-decision-note {
            color: #e5e7eb;
        }

        /* ==== Pagination Styles ==== */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding: 1rem 0;
        }

        .pagination-info {
            color: #666;
            font-size: 0.9rem;
        }

        .pagination-controls {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .pagination-btn {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #333;
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-btn:hover:not(:disabled) {
            background: #8B0000;
            color: white;
            border-color: #8B0000;
        }

        .pagination-pages {
            display: flex;
            gap: 0.25rem;
        }

        .pagination-page {
            padding: 0.5rem 0.75rem;
            border: 1px solid #ddd;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #333;
        }

        .pagination-page.active {
            background: #8B0000;
            color: white;
            border-color: #8B0000;
        }

        .pagination-page:hover:not(.active) {
            background: #8B0000;
            color: white;
            border-color: #8B0000;
        }

        body.dark-mode .pagination-info {
            color: #ccc !important;
        }

        body.dark-mode .pagination-btn {
            background: #2a2a2a !important;
            border-color: #555 !important;
            color: #f0f0f0 !important;
        }

        body.dark-mode .pagination-btn:hover:not(:disabled) {
            background: #8B0000 !important;
            color: white !important;
            border-color: #8B0000 !important;
        }

        body.dark-mode .pagination-page {
            background: #2a2a2a !important;
            border-color: #555 !important;
            color: #f0f0f0 !important;
        }

        body.dark-mode .pagination-page.active {
            background: #8B0000 !important;
            color: white !important;
            border-color: #8B0000 !important;
        }

        body.dark-mode .pagination-page:hover:not(.active) {
            background: #8B0000 !important;
            color: white !important;
            border-color: #8B0000 !important;
        }

        /* ==== View button in main table ==== */
        .btn-view {
            background-color: #8B0000;
            color: white;
            border: none;
            border-radius: 6px;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            cursor: pointer;
            padding: 0;
        }

        .btn-view:hover {
            background-color: #A52A2A;
            transform: translateY(-1px);
        }

        .btn-view i {
            font-size: 0.9rem;
        }

        body.dark-mode .btn-view {
            background-color: #8B0000 !important;
            color: white !important;
        }

        body.dark-mode .btn-view:hover {
            background-color: #A52A2A !important;
        }

        /* ==== Responsive ==== */
        @media (max-width: 992px) {
            .detail-row {
                flex-direction: column;
            }

            .detail-row .label {
                width: auto;
                margin-bottom: 0.1rem;
            }
        }

        @media (max-width: 768px) {
            .controls-section {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
            }

            .filter-controls {
                flex-direction: column;
                gap: 1rem;
            }

            .search-controls {
                max-width: none;
            }

            .slea-decision-actions {
                flex-direction: column;
            }

            .assessor-modal .modal-dialog {
                width: 100% !important;
                max-width: 100% !important;
                margin: 1rem;
            }
        }
    </style>

@endsection

@push('scripts')
    <script src="{{ asset('js/assessor-all-submissions.js') }}"></script>
@endpush