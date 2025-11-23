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
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="sectionFilterSelect">Filter by SLEA Section</label>
                    <select id="sectionFilterSelect" class="form-select">
                        <option value="">All</option>
                        <option value="Leadership Excellence">Leadership Excellence</option>
                        <option value="Academic Excellence">Academic Excellence</option>
                        <option value="Awards Recognition">Awards Recognition</option>
                        <option value="Community Involvement">Community Involvement</option>
                        <option value="Good Conduct">Good Conduct</option>
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

        {{-- Table: Students With Approved/Rejected Submissions --}}
        <div class="submissions-table-container">
            <table class="table submissions-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Program</th>
                        <th>College</th>
                        <th>Total Submissions</th>
                        <th>Date Reviewed</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($students as $student)
                    <tr>
                        <td>{{ $student->student_id }}</td>
                        <td>{{ $student->user->full_name }}</td>
                        <td>{{ $student->user->email }}</td>
                        <td>{{ $student->program }}</td>
                        <td>{{ $student->college }}</td>
                        <td>{{ $student->submissions->count() }}</td>
                        <td>{{ $student->latest_reviewed_at ? \Carbon\Carbon::parse($student->latest_reviewed_at)->format('Y-m-d') : 'N/A' }}</td>
                        <td>
                            <button
                                class="btn btn-view"
                                onclick="openStudentSubmissionsModal({{ $student->id }})"
                                title="View Submissions">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No students with approved or rejected submissions found.</td>
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
                <span id="showingEnd">5</span>
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
<div class="modal fade" id="studentSubmissionsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xxl modal-dialog-scrollable">

        <div class="modal-content">

            {{-- Header --}}
            <div class="modal-header">
                <h5 class="modal-title">
                    All Submissions for <span id="modalStudentNameTitle"></span>
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
                            <span class="label">Name:</span>
                            <span class="value" id="modalStudentNameDetail"></span>
                        </div>

                        <div class="detail-row">
                            <span class="label">Program:</span>
                            <span class="value" id="modalStudentProgramDetail"></span>
                        </div>

                        <div class="detail-row">
                            <span class="label">College:</span>
                            <span class="value" id="modalStudentCollegeDetail"></span>
                        </div>
                    </div>
                </div>

                {{-- Categorized Tables --}}
                <div id="categorizedSubmissionsContainer">
                    <h6 class="card-title mb-3">Submissions History</h6>
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
                            <textarea
                                id="individualAssessorRemarks"
                                class="form-control remarks-textarea"
                                rows="4"
                                placeholder="Remarks..."></textarea>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="action-buttons-container">
                    <button type="button" class="btn btn-approve" onclick="handleSubmission('approve')">
                        <i class="fas fa-check"></i>
                    </button>

                    <button type="button" class="btn btn-reject" onclick="handleSubmission('reject')">
                        <i class="fas fa-times"></i>
                    </button>

                    <button type="button" class="btn btn-return" onclick="handleSubmission('return')">
                        <i class="fas fa-undo"></i>
                    </button>

                    <button type="button" class="btn btn-flag" onclick="handleSubmission('flag')">
                        <i class="fas fa-flag"></i>
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
<style>
    .page-header {
        margin-bottom: 1.5rem;
    }

    .page-header h1 {
        color: #8B0000;
        font-size: 2rem;
        margin-bottom: 0;
        font-weight: 700;
    }

    /* Dark mode page header */
    body.dark-mode .page-header h1 {
        color: #f9bd3d !important;
    }

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

    /* Dark mode table styling */
    /* Dark mode form controls */
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

    /* Pagination Styles */
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

    /* Dark mode pagination */
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

    body.dark-mode .submissions-table thead th:last-child {
        border-right: none !important;
    }

    body.dark-mode .submissions-table tbody td {
        background: #363636 !important;
        color: #f0f0f0 !important;
        border-bottom: 1px solid #555 !important;
        border-right: 1px solid #555 !important;
    }

    body.dark-mode .submissions-table tbody td:last-child {
        border-right: none !important;
    }

    body.dark-mode .submissions-table tbody tr:hover {
        background-color: #404040 !important;
    }

    /* Additional specificity to override Bootstrap and other styles */
    .submissions-table-container .submissions-table thead th {
        background-color: #8B0000 !important;
        color: white !important;
        border-bottom: 1px solid white !important;
        border-right: 1px solid white !important;
    }

    .submissions-table-container .submissions-table thead th:last-child {
        border-right: none !important;
    }

    body.dark-mode .submissions-table-container .submissions-table thead th {
        background-color: #8B0000 !important;
        color: white !important;
        border-bottom: 1px solid #555 !important;
        border-right: 1px solid #555 !important;
    }

    body.dark-mode .submissions-table-container .submissions-table thead th:last-child {
        border-right: none !important;
    }

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

    /* Dark mode button styling */
    body.dark-mode .btn-view {
        background-color: #8B0000 !important;
        color: white !important;
    }

    body.dark-mode .btn-view:hover {
        background-color: #A52A2A !important;
    }

    /* Success Modal Styles */
    .success-modal-content {
        border: none !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
    }

    .success-icon {
        animation: successPulse 0.6s ease-in-out;
    }

    .success-title {
        color: #333 !important;
        font-weight: 600 !important;
        font-size: 1.5rem !important;
    }

    .success-message {
        color: #666 !important;
        font-size: 1rem !important;
        line-height: 1.5 !important;
    }

    .success-modal-content .btn-success {
        background-color: #28a745 !important;
        border-color: #28a745 !important;
        padding: 0.75rem 2rem !important;
        border-radius: 6px !important;
        font-weight: 500 !important;
        transition: all 0.2s ease !important;
        color: white !important;
    }

    .success-modal-content .btn-success:hover {
        background-color: #218838 !important;
        border-color: #218838 !important;
        transform: translateY(-1px) !important;
        color: white !important;
    }

    /* Dark mode success modal */
    body.dark-mode .success-modal-content {
        background-color: #2a2a2a !important;
        color: #f0f0f0 !important;
    }

    body.dark-mode .success-title {
        color: #f0f0f0 !important;
    }

    body.dark-mode .success-message {
        color: #ccc !important;
    }

    /* Success animation */
    @keyframes successPulse {
        0% {
            transform: scale(0.8);
            opacity: 0;
        }

        50% {
            transform: scale(1.1);
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    /* Validation Modal Styles */
    .validation-modal-content {
        border: none !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
    }

    .validation-icon {
        animation: validationShake 0.6s ease-in-out;
    }

    .validation-title {
        color: #333 !important;
        font-weight: 600 !important;
        font-size: 1.5rem !important;
    }

    .validation-message {
        color: #666 !important;
        font-size: 1rem !important;
        line-height: 1.5 !important;
    }

    .validation-modal-content .btn-warning {
        background-color: #ffc107 !important;
        border-color: #ffc107 !important;
        padding: 0.75rem 2rem !important;
        border-radius: 6px !important;
        font-weight: 500 !important;
        transition: all 0.2s ease !important;
        color: #212529 !important;
    }

    .validation-modal-content .btn-warning:hover {
        background-color: #e0a800 !important;
        border-color: #e0a800 !important;
        transform: translateY(-1px) !important;
        color: #212529 !important;
    }

    /* Dark mode validation modal */
    body.dark-mode .validation-modal-content {
        background-color: #2a2a2a !important;
        color: #f0f0f0 !important;
    }

    body.dark-mode .validation-title {
        color: #f0f0f0 !important;
    }

    body.dark-mode .validation-message {
        color: #ccc !important;
    }

    /* Validation animation */
    @keyframes validationShake {

        0%,
        100% {
            transform: translateX(0);
        }

        10%,
        30%,
        50%,
        70%,
        90% {
            transform: translateX(-5px);
        }

        20%,
        40%,
        60%,
        80% {
            transform: translateX(5px);
        }
    }

    /* Modal Styles - Override Main CSS */

    /* Clean modal sizing – let Bootstrap handle centering */
    /* Make "All Submissions" modal wide and centered */


    #studentSubmissionsModal .modal-content {
        height: 85vh !important;
        max-height: 85vh !important;
        overflow-y: auto !important;
        /* scroll inside modal, not whole page */
    }


    /* Individual submission modal a bit narrower */
    #individualSubmissionModal .modal-dialog {
        width: 80vw !important;
        max-width: 1100px !important;
        margin: 1.5rem auto !important;
    }

    #individualSubmissionModal .modal-content {
        height: 80vh !important;
        max-height: 80vh !important;
        overflow-y: auto !important;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }


    .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: 1.5rem 2rem;
        text-align: left;
    }

    .modal-title {
        font-weight: 700;
        color: #333;
        font-size: 1.25rem;
        margin: 0;
    }

    .modal-body {
        padding: 2rem;
        flex: 1;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }

    .submission-content {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        flex: 1;
    }

    /* Info Card Styles */
    .info-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: 1px solid #e9ecef;
    }

    .card-header {
        background-color: #f8f9fa;
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #dee2e6;
    }

    .card-title {
        font-weight: 600;
        color: #8B0000;
        font-size: 1.25rem;
        margin: 0;
        border-bottom: 2px solid #8B0000;
        padding-bottom: 0.5rem;
        display: inline-block;
    }

    .card-body {
        padding: 2rem;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #f1f3f4;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-row .label {
        font-weight: 600;
        color: #333;
        font-size: 1rem;
        min-width: 150px;
    }

    .detail-row .value {
        color: #666;
        font-size: 1rem;
        text-align: right;
        flex: 1;
    }

    /* Status Badge Styles */
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 1px solid;
    }

    .status-pending {
        background-color: #fff3cd;
        color: #856404;
        border-color: #ffc107;
    }

    .status-approved {
        background-color: #d4edda;
        color: #155724;
        border-color: #28a745;
    }

    .status-rejected {
        background-color: #f8d7da;
        color: #721c24;
        border-color: #dc3545;
    }

    .status-returned {
        background-color: #cce5ff;
        color: #004085;
        border-color: #007bff;
    }

    .status-flagged {
        background-color: #fce5d4;
        color: #8b4a04;
        border-color: #ff8c00;
    }

    /* Dark mode status badges */
    body.dark-mode .status-pending {
        background-color: #744210;
        color: #f6e05e;
        border-color: #f6e05e;
    }

    body.dark-mode .status-approved {
        background-color: #1e4d2b;
        color: #68d391;
        border-color: #68d391;
    }

    body.dark-mode .status-rejected {
        background-color: #742a2a;
        color: #feb2b2;
        border-color: #feb2b2;
    }

    body.dark-mode .status-returned {
        background-color: #1d3a5e;
        color: #6cb6ff;
        border-color: #6cb6ff;
    }

    body.dark-mode .status-flagged {
        background-color: #7a431c;
        color: #ffc107;
        border-color: #ffc107;
    }

    .remarks-textarea {
        border: 1px solid #ddd;
        border-radius: 8px;
        resize: vertical;
        min-height: 120px;
        width: 100%;
        padding: 1rem;
        font-size: 1rem;
        font-family: inherit;
    }

    .remarks-note {
        display: block;
        margin-top: 0.5rem;
        color: #666;
        font-size: 0.85rem;
        font-style: italic;
    }

    .action-buttons-container {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #dee2e6;
        width: 100%;
    }

    .action-buttons-container .btn {
        padding: 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        width: 50px;
        height: 50px;
        flex-shrink: 0;
    }

    /* Document Preview Styles */
    .document-preview {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .document-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        background: #f8f9fa;
    }

    .document-info {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex: 1;
    }

    .document-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-size: 1.5rem;
        color: white;
    }

    .document-icon.pdf {
        background: #dc3545;
    }

    .document-icon.image {
        background: #28a745;
    }

    .document-icon.other {
        background: #6c757d;
    }

    .document-details h6 {
        margin: 0;
        font-size: 1rem;
        color: #333;
    }

    .document-details small {
        color: #666;
    }

    .document-actions {
        display: flex;
        gap: 0.5rem;
    }

    .btn-download {
        background: #007bff;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: background 0.2s ease;
    }

    .btn-download:hover {
        background: #0056b3;
    }

    .btn-preview {
        background: #28a745;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: background 0.2s ease;
    }

    .btn-preview:hover {
        background: #1e7e34;
    }

    /* Score Display Styles */
    .score-display {
        text-align: center;
        padding: 1rem;
    }

    .score-main {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 0.5rem;
    }

    .score-label {
        font-weight: 600;
        font-size: 1.1rem;
        color: #333;
    }

    .score-value {
        font-weight: 700;
        font-size: 2rem;
        color: #28a745;
        background: #d4edda;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: 2px solid #28a745;
    }

    .score-note {
        color: #666;
        font-style: italic;
    }

    /* Dark mode document preview */
    body.dark-mode .document-item {
        background: #363636 !important;
        border-color: #555 !important;
    }

    body.dark-mode .document-details h6 {
        color: #f0f0f0 !important;
    }

    body.dark-mode .document-details small {
        color: #ccc !important;
    }

    body.dark-mode .score-label {
        color: #f0f0f0 !important;
    }

    body.dark-mode .score-value {
        background: #1e4d2b !important;
        color: #68d391 !important;
        border-color: #68d391 !important;
    }

    body.dark-mode .score-note {
        color: #ccc !important;
    }

    .btn-approve {
        background-color: #28a745 !important;
        color: white !important;
        border: none !important;
    }

    .btn-approve:hover {
        background-color: #218838 !important;
        transform: translateY(-1px);
        color: white !important;
    }

    .btn-reject {
        background-color: #8B0000 !important;
        color: white !important;
        border: none !important;
    }

    .btn-reject:hover {
        background-color: #A52A2A !important;
        transform: translateY(-1px);
        color: white !important;
    }

    .btn-return {
        background-color: #FFD700 !important;
        color: #212529 !important;
        border: none !important;
    }

    .btn-return:hover {
        background-color: #FFA500 !important;
        transform: translateY(-1px);
        color: #212529 !important;
    }

    .btn-flag {
        background-color: #dc3545 !important;
        color: white !important;
        border: none !important;
    }

    .btn-flag:hover {
        background-color: #c82333 !important;
        transform: translateY(-1px);
        color: white !important;
    }

    /* Dark Mode Styles - Matching Admin Dashboard */
    body.dark-mode .modal-content {
        background-color: #2a2a2a !important;
        color: #f0f0f0;
    }

    body.dark-mode #individualSubmissionModal .modal-body {
        background-color: #2a2a2a !important;
        color: #f0f0f0 !important;
    }

    body.dark-mode #individualSubmissionModal .submission-content {
        background-color: #2a2a2a !important;
        color: #f0f0f0 !important;
    }

    body.dark-mode .modal-header {
        background: #2a2a2a;
        border-color: #555;
        padding: 1.5rem 2rem;
    }

    body.dark-mode .modal-title {
        color: #F9BD3D;
    }

    body.dark-mode .info-card {
        background: #2a2a2a !important;
        border-color: #555 !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
    }

    body.dark-mode .card-header {
        background: #2a2a2a !important;
        border-bottom-color: #555 !important;
    }

    body.dark-mode .card-title {
        color: #F9BD3D;
        border-bottom-color: #F9BD3D;
    }

    body.dark-mode .detail-row {
        border-bottom-color: #555;
    }

    body.dark-mode .detail-row .label {
        color: #f0f0f0;
    }

    body.dark-mode .detail-row .value {
        color: #f0f0f0;
    }

    body.dark-mode .status-pending {
        color: #f6e05e !important;
        background-color: #744210;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-weight: 600;
    }

    body.dark-mode .remarks-textarea {
        background-color: #2a2a2a !important;
        border-color: #555 !important;
        color: #f0f0f0;
    }

    body.dark-mode .remarks-textarea::placeholder {
        color: #aaa;
    }

    body.dark-mode .remarks-note {
        color: #aaa !important;
    }

    body.dark-mode .action-buttons-container {
        border-top-color: #555 !important;
    }

    #individualSubmissionModal .btn-close {
        background: #dc3545 !important;
        color: white !important;
        border-radius: 4px !important;
        width: 24px !important;
        height: 24px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 14px !important;
        border: none !important;
        transition: all 0.2s ease !important;
        opacity: 1 !important;
        background-image: none !important;
    }

    #individualSubmissionModal .btn-close::before {
        content: "×" !important;
        font-size: 18px !important;
        font-weight: bold !important;
        color: white !important;
        line-height: 1 !important;
    }

    #individualSubmissionModal .btn-close:hover {
        background: #c82333 !important;
        color: white !important;
        transform: translateY(-1px) !important;
        opacity: 1 !important;
    }

    #individualSubmissionModal .btn-close:focus {
        background: #dc3545 !important;
        color: white !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }

    body.dark-mode #individualSubmissionModal .btn-close {
        background: #dc3545 !important;
        color: white !important;
        opacity: 1 !important;
    }

    body.dark-mode #individualSubmissionModal .btn-close:hover {
        background: #c82333 !important;
        color: white !important;
        opacity: 1 !important;
    }

    /* Dark mode button hover effects */
    body.dark-mode .btn-approve:hover {
        background-color: #2d5a2d !important;
    }

    body.dark-mode .btn-reject:hover {
        background-color: #8b0000 !important;
    }

    body.dark-mode .btn-return:hover {
        background-color: #b8860b !important;
    }

    body.dark-mode .btn-flag:hover {
        background-color: #8b0000 !important;
    }

    .slea-category-section .category-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #8B0000;
        display: block;
    }

    body.dark-mode .slea-category-section .category-title {
        color: #F9BD3D;
        border-bottom-color: #F9BD3D;
    }

    .category-table {
        border: 1px solid #c2c2c2;
        /* Darker, subtle border */
        border-collapse: separate;
        /* Use separate to allow border-spacing */
        border-spacing: 0;
        /* Remove space between borders */
        width: 100%;
        margin-bottom: 1.5rem;
        /* Increase margin for better separation */
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        /* More pronounced shadow */
        border-radius: 8px;
        overflow: hidden;
    }

    .category-table thead th {
        background-color: #8B0000;
        /* Dark red background for header */
        color: white;
        /* White text for header */
        font-weight: 600;
        padding: 0.75rem 1rem;
        text-align: left;
        border: 1px solid white;
        /* White borders between header cells */
    }

    .category-table thead th:first-child {
        border-left: none;
        /* No left border for the first header cell */
    }

    .category-table thead th:last-child {
        border-right: none;
        /* No right border for the last header cell */
    }

    .category-table tbody td {
        padding: 0.75rem 1rem;
        border: 1px solid #e9ecef;
        /* Light gray borders for body cells */
        color: #333;
        background-color: white;
    }

    .category-table tbody tr:last-child td {
        border-bottom: none;
        /* No bottom border for the last row */
    }

    body.dark-mode .category-table thead th {
        background-color: #8B0000 !important;
        color: white !important;
        border: 1px solid #555 !important;
        /* Darker borders for dark mode header */
    }

    body.dark-mode .category-table tbody td {
        background-color: #363636 !important;
        color: #f0f0f0 !important;
        border: 1px solid #555 !important;
        /* Darker borders for dark mode body cells */
    }

    .category-total-row td {
        font-weight: 700;
        background-color: #f2f2f2 !important;
        border-top: 2px solid #8B0000 !important;
        text-align: right;
        white-space: nowrap;
    }


    body.dark-mode .category-total-row td {
        background-color: #404040 !important;
        border-top: 2px solid #F9BD3D !important;
    }

    /* Overall Total Score Section */
    .overall-total-section {
        margin-top: 2rem;
    }

    .overall-total-table {
        border: 2px solid #8B0000;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 0;
        box-shadow: 0 8px 16px rgba(139, 0, 0, 0.3);
    }

    .overall-total-table .overall-total-row td {
        background-color: #8B0000;
        color: white;
        font-size: 1.3rem;
        font-weight: 700;
        padding: 1.5rem;
        text-align: center;
        border: none;
    }

    body.dark-mode .overall-total-table {
        border-color: #F9BD3D;
        box-shadow: 0 8px 16px rgba(249, 189, 61, 0.3);
    }

    body.dark-mode .overall-total-table .overall-total-row td {
        background-color: #F9BD3D;
        color: #333;
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

        .action-buttons-container {
            flex-direction: column;
            align-items: stretch;
        }

        .action-buttons-container .btn {
            width: 100%;
            justify-content: center;
        }

        .detail-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.25rem;
        }

        .detail-row span {
            text-align: left;
        }
    }
</style>
@endsection



@push('scripts')
<script src="{{ asset('js/assessor_submission.js') }}"></script>
@endpush