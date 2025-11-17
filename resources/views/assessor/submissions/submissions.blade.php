@extends('layouts.app')

@section('title', 'All Submissions - Assessor Dashboard')

@section('content')
<div class="container">
    @include('partials.assessor_sidebar')

    <main class="main-content">
        <div class="page-header">
            <h1>All Submissions</h1>
        </div>

        <!-- Filter and Search Controls (to be implemented) -->
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

        <!-- Submissions Table -->
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
                        <td>{{ $student->user->name }}</td>
                        <td>{{ $student->user->email }}</td>
                        <td>{{ $student->program }}</td>
                        <td>{{ $student->college }}</td>
                        <td>{{ $student->submissions->count() }}</td>
                        <td>{{ $student->latest_reviewed_at ? \Carbon\Carbon::parse($student->latest_reviewed_at)->format('Y-m-d') : 'N/A' }}</td>
                        <td>
                            <button class="btn btn-view" onclick="openStudentSubmissionsModal({{ $student->id }})" title="View Submissions">
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

        <div class="pagination-container">
            <div class="pagination-info">
                Showing <span id="showingStart">1</span>-<span id="showingEnd">5</span> of <span id="totalEntries">{{ $students->count() }}</span> students
            </div>
            <div class="pagination-controls">
                <button class="pagination-btn" id="prevBtn" disabled>
                    <i class="fas fa-chevron-left"></i> Previous
                </button>
                <span class="pagination-pages" id="paginationPages">
                    <!-- Dynamic pages will be generated here -->
                </span>
                <button class="pagination-btn" id="nextBtn">
                    Next <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </main>
</div>

<!-- Student Submissions Modal -->
<div class="modal fade" id="studentSubmissionsModal" tabindex="-1" aria-labelledby="studentSubmissionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="studentSubmissionsModalLabel">All Submissions for <span id="modalStudentNameTitle"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
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

                <div class="all-student-submissions-container" id="categorizedSubmissionsContainer">
                    <h6 class="card-title mb-3">Submissions History</h6>
                    <!-- Categorized submissions tables will be loaded here by JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Individual Submission Modal -->
<div class="modal fade" id="individualSubmissionModal" tabindex="-1" aria-labelledby="individualSubmissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="individualSubmissionModalLabel">Review Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="submission-content">
                    <div class="info-card">
                        <div class="card-header">
                            <h6 class="card-title">Student Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="detail-row">
                                <span class="label">Student ID:</span>
                                <span class="value" id="modalIndividualStudentId">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Student Name:</span>
                                <span class="value" id="modalIndividualStudentName">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Document Title:</span>
                                <span class="value" id="modalIndividualDocumentTitle">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Date Submitted:</span>
                                <span class="value" id="modalIndividualDateSubmitted">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Current Status:</span>
                                <span class="value" id="modalIndividualStatus">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Assigned Assessor:</span>
                                <span class="value" id="modalIndividualAssessorName">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="card-header">
                            <h6 class="card-title">Document Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="detail-row">
                                <span class="label">SLEA Section:</span>
                                <span class="value" id="modalIndividualSleaSection">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Subsection:</span>
                                <span class="value" id="modalIndividualSubsection">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Role in Activity:</span>
                                <span class="value" id="modalIndividualRole">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Activity Date:</span>
                                <span class="value" id="modalIndividualActivityDate">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Organizing Body:</span>
                                <span class="value" id="modalIndividualOrganizingBody">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Description:</span>
                                <span class="value" id="modalIndividualDescription">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="card-header">
                            <h6 class="card-title">Uploaded Document</h6>
                        </div>
                        <div class="card-body">
                            <div id="individualDocumentPreview" class="document-preview">
                            </div>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="card-header">
                            <h6 class="card-title">System Auto-Generated Score</h6>
                        </div>
                        <div class="card-body">
                            <div class="score-display">
                                <div class="score-main">
                                    <span class="score-label">Auto Score:</span>
                                    <span class="value" id="modalIndividualAutoScore">-</span>
                                </div>
                                <div class="score-note">
                                    <small>This score is calculated automatically based on submission criteria.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="card-header">
                            <h6 class="card-title">Assessor Remarks (Optional)</h6>
                        </div>
                        <div class="card-body">
                            <textarea id="individualAssessorRemarks" class="form-control remarks-textarea" rows="4" placeholder="Enter your remarks and feedback..."></textarea>
                            <small class="remarks-note">Note: Remarks are required for Reject, Return, and Flag actions.</small>
                        </div>
                    </div>
                </div>

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

{{-- Reusing the styles from pending-submissions.blade.php for consistency --}}
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
    #studentSubmissionsModal {
        display: none !important;
        position: fixed !important;
        z-index: 9999 !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background-color: rgba(0, 0, 0, 0.5) !important;
        backdrop-filter: blur(5px);
    }

    #studentSubmissionsModal.show {
        display: block !important;
    }

    #studentSubmissionsModal .modal-dialog {
        max-width: 80vw !important;
        /* Wider for student list */
        width: 80vw !important;
        margin: 1.75rem auto !important;
        display: flex !important;
        align-items: center !important;
        min-height: calc(100% - 3.5rem) !important;
        position: relative !important;
    }

    #studentSubmissionsModal .modal-content {
        background-color: #fff !important;
        margin: 0 !important;
        padding: 0 !important;
        border-radius: 15px !important;
        width: 100% !important;
        max-width: none !important;
        max-height: 80vh !important;
        /* Adjusted for better vertical balance */
        overflow-y: auto !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2) !important;
        display: flex !important;
        flex-direction: column !important;
        animation: modalSlideIn 0.3s ease-out !important;
    }

    body.dark-mode #studentSubmissionsModal .modal-content {
        background-color: #2a2a2a !important;
        color: #f0f0f0 !important;
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

    #individualSubmissionModal {
        display: none !important;
        position: fixed !important;
        z-index: 9999 !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background-color: rgba(0, 0, 0, 0.5) !important;
        backdrop-filter: blur(5px);
    }

    #individualSubmissionModal.show {
        display: block !important;
    }

    #individualSubmissionModal .modal-dialog {
        max-width: 65vw !important;
        /* Adjusted for individual submission modal */
        width: 65vw !important;
        margin: 1.75rem auto !important;
        display: flex !important;
        align-items: center !important;
        min-height: calc(100% - 3.5rem) !important;
        position: relative !important;
    }

    #individualSubmissionModal .modal-content {
        background-color: #fff !important;
        margin: 0 !important;
        padding: 0 !important;
        border-radius: 15px !important;
        width: 100% !important;
        max-width: none !important;
        max-height: 75vh !important;
        /* Adjusted for individual submission modal */
        overflow-y: auto !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2) !important;
        display: flex !important;
        flex-direction: column !important;
        animation: modalSlideIn 0.3s ease-out !important;
    }

    body.dark-mode #individualSubmissionModal .modal-content {
        background-color: #2a2a2a !important;
        color: #f0f0f0 !important;
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
        /* Light background for total row */
        border-top: 2px solid #8B0000 !important;
        /* Stronger top border for total row */
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

<script>
    let currentSubmissionId = null;
    let allStudentsData = []; // Store student data with submissions for filtering/pagination

    async function openIndividualSubmissionModal(submissionId) {
        try {
            showModalLoading('individualSubmissionModal');
            const response = await fetch(`/assessor/submissions/${submissionId}/details`);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Failed to fetch submission details');
            }

            const submission = data.submission;

            document.getElementById('modalIndividualStudentId').textContent = submission.student.student_id;
            document.getElementById('modalIndividualStudentName').textContent = submission.student.user?.name || 'N/A';
            document.getElementById('modalIndividualDocumentTitle').textContent = submission.document_title;
            document.getElementById('modalIndividualDateSubmitted').textContent = new Date(submission.submitted_at).toLocaleDateString();
            document.getElementById('modalIndividualStatus').innerHTML = `<span class="status-badge status-${submission.status?.toLowerCase() || 'unknown'}">${submission.status?.charAt(0).toUpperCase() + submission.status?.slice(1) || 'Unknown'}</span>`;

            document.getElementById('modalIndividualSleaSection').textContent = submission.slea_section || '-';
            document.getElementById('modalIndividualSubsection').textContent = submission.subsection || '-';
            document.getElementById('modalIndividualRole').textContent = submission.role_in_activity || '-';
            document.getElementById('modalIndividualActivityDate').textContent = submission.activity_date || '-';
            document.getElementById('modalIndividualOrganizingBody').textContent = submission.organizing_body || '-';
            const descriptionElement = document.getElementById('modalIndividualDescription');
            if (descriptionElement) {
                descriptionElement.textContent = submission.description || '-';
            }

            document.getElementById('modalIndividualAutoScore').textContent = submission.auto_generated_score ?
                `${submission.auto_generated_score}/100` : 'Not calculated';

            populateDocumentPreview(submission.documents, 'individualDocumentPreview');

            document.getElementById('individualAssessorRemarks').value = submission.assessor_remarks || '';
            document.getElementById('modalIndividualAssessorName').textContent = submission.assessor?.name || 'N/A';

            currentSubmissionId = submissionId;

            const modal = new bootstrap.Modal(document.getElementById('individualSubmissionModal'));
            modal.show();
        } catch (error) {
            console.error('Error fetching individual submission details:', error);
            showErrorModal('Failed to load submission details: ' + error.message);
        }
    }

    async function openStudentSubmissionsModal(studentId) {
        try {
            showModalLoading('studentSubmissionsModal');
            const response = await fetch(`/assessor/students/${studentId}/details`);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Failed to fetch student details');
            }

            const student = data.student;
            const categorizedSubmissions = data.submissions;
            const categoryTotals = data.category_totals;

            document.getElementById('modalStudentNameTitle').textContent = student.user.name;
            document.getElementById('modalStudentIdDetail').textContent = student.student_id;
            document.getElementById('modalStudentNameDetail').textContent = student.user.name;
            document.getElementById('modalStudentProgramDetail').textContent = student.program;
            document.getElementById('modalStudentCollegeDetail').textContent = student.college;

            const submissionsContainer = document.getElementById('categorizedSubmissionsContainer');
            submissionsContainer.innerHTML = ''; // Clear previous content

            const overallTotalScore = data.overall_total_score || 0;

            const sleaSectionsOrder = [
                'Leadership Excellence',
                'Academic Excellence',
                'Awards Recognition',
                'Community Involvement',
                'Good Conduct'
            ];

            let romanNumeralMap = {
                1: 'I',
                2: 'II',
                3: 'III',
                4: 'IV',
                5: 'V'
            };

            let sectionCounter = 1;

            let hasSubmissions = false;
            for (const section of sleaSectionsOrder) {
                if (categorizedSubmissions[section] && categorizedSubmissions[section].length > 0) {
                    hasSubmissions = true;
                    break;
                }
            }

            if (!hasSubmissions) {
                submissionsContainer.innerHTML = '<p class="text-muted text-center">No submissions found for this student.</p>';
            } else {
                for (const section of sleaSectionsOrder) {
                    const sectionSubmissions = categorizedSubmissions[section] || [];
                    const totalCategoryScore = categoryTotals[section]?.score || 0;
                    const maxCategoryScore = categoryTotals[section]?.max_score || 0;

                    const sectionDiv = document.createElement('div');
                    sectionDiv.className = 'slea-category-section mb-5';

                    let tableRowsHtml = '';
                    if (sectionSubmissions.length > 0) {
                        sectionSubmissions.forEach(submission => {
                            tableRowsHtml += `
                            <tr>
                                <td>${submission.document_title || '-'}</td>
                                <td>${submission.subsection || '-'}</td>
                                <td>${submission.role_in_activity || '-'}</td>
                                <td>${submission.reviewed_at ? new Date(submission.reviewed_at).toLocaleDateString() : 'N/A'}</td>
                                <td>${submission.assessor?.name || 'N/A'}</td>
                                <td><span class="status-badge status-${submission.status?.toLowerCase() || 'unknown'}">${submission.status?.charAt(0).toUpperCase() + submission.status?.slice(1) || 'Unknown'}</span></td>
                                <td>${submission.assessor_score ?? 'N/A'}</td>
                            </tr>
                        `;
                        });
                    } else {
                        tableRowsHtml = '<tr><td colspan="7" class="text-center">No submissions for this category.</td></tr>';
                    }

                    sectionDiv.innerHTML = `
                    <h5 class="category-title mb-3">${romanNumeralMap[sectionCounter++]}. <strong>${section}</strong></h5>
                    <div class="table-responsive mb-3">
                        <table class="table submissions-table category-table">
                            <thead>
                                <tr>
                                    <th>Document Title</th>
                                    <th>Type of Activity</th>
                                    <th>Role in Activity</th>
                                    <th>Date Reviewed</th>
                                    <th>Reviewed By</th>
                                    <th>Submission Status</th>
                                    <th>Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableRowsHtml}
                                <tr class="category-total-row">
                                    <td colspan="6" class="text-start"><strong>Total Score for ${section}:</strong></td>
                                    <td><strong>${totalCategoryScore.toFixed(2)}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                `;
                    submissionsContainer.appendChild(sectionDiv);
                }

                // Add Overall Total Score section at the end
                const overallTotalDiv = document.createElement('div');
                overallTotalDiv.className = 'overall-total-section mt-4';
                overallTotalDiv.innerHTML = `
                <div class="table-responsive">
                    <table class="table overall-total-table">
                        <tbody>
                            <tr class="overall-total-row">
                                <td class="text-center">
                                    <strong>Overall Total Score: ${overallTotalScore.toFixed(2)} / 75</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `;
                submissionsContainer.appendChild(overallTotalDiv);
            }

            const modal = new bootstrap.Modal(document.getElementById('studentSubmissionsModal'));
            modal.show();

        } catch (error) {
            console.error('Error fetching student submissions:', error);
            showErrorModal('Failed to load student submissions: ' + error.message);
        }
    }

    function showModalLoading(modalId) {
        if (modalId === 'studentSubmissionsModal') {
            document.getElementById('modalStudentNameTitle').textContent = 'Loading...';
            document.getElementById('modalStudentIdDetail').textContent = 'Loading...';
            document.getElementById('modalStudentNameDetail').textContent = 'Loading...';
            document.getElementById('modalStudentProgramDetail').textContent = 'Loading...';
            document.getElementById('modalStudentCollegeDetail').textContent = 'Loading...';
            document.getElementById('categorizedSubmissionsContainer').innerHTML = '<p class="text-muted text-center"><i class="fas fa-spinner fa-spin"></i> Loading submissions...</p>';
        } else if (modalId === 'individualSubmissionModal') {
            document.getElementById('modalIndividualStudentId').textContent = 'Loading...';
            document.getElementById('modalIndividualStudentName').textContent = 'Loading...';
            document.getElementById('modalIndividualDocumentTitle').textContent = 'Loading...';
            document.getElementById('modalIndividualDateSubmitted').textContent = 'Loading...';
            document.getElementById('modalIndividualStatus').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            document.getElementById('modalIndividualSleaSection').textContent = 'Loading...';
            document.getElementById('modalIndividualSubsection').textContent = 'Loading...';
            document.getElementById('modalIndividualRole').textContent = 'Loading...';
            document.getElementById('modalIndividualActivityDate').textContent = 'Loading...';
            document.getElementById('modalIndividualOrganizingBody').textContent = 'Loading...';
            document.getElementById('modalIndividualDescription').textContent = 'Loading...';
            document.getElementById('modalIndividualAutoScore').textContent = 'Loading...';
            document.getElementById('individualAssessorRemarks').value = 'Loading...';
            document.getElementById('modalIndividualAssessorName').textContent = 'Loading...';
            document.getElementById('individualDocumentPreview').innerHTML = '<p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Loading documents...</p>';
        }
    }

    function populateDocumentPreview(documents, containerId) {
        const previewContainer = document.getElementById(containerId);
        previewContainer.innerHTML = '';

        if (!documents || documents.length === 0) {
            previewContainer.innerHTML = '<p class="text-muted">No documents uploaded.</p>';
            return;
        }

        documents.forEach(doc => {
            const documentItem = document.createElement('div');
            documentItem.className = 'document-item';

            const iconClass = doc.file_type === 'pdf' ? 'pdf' : (['jpg', 'jpeg', 'png', 'gif'].includes(doc.file_type) ? 'image' : 'other');
            const iconSymbol = doc.file_type === 'pdf' ? '📄' : (['jpg', 'jpeg', 'png', 'gif'].includes(doc.file_type) ? '🖼️' : '📎');

            documentItem.innerHTML = `
            <div class="document-info">
                <div class="document-icon ${iconClass}">
                    ${iconSymbol}
                </div>
                <div class="document-details">
                    <h6>${doc.original_filename}</h6>
                    <small>${doc.file_type.toUpperCase()} • ${doc.formatted_size}</small>
                </div>
            </div>
            <div class="document-actions">
                ${doc.file_type === 'pdf' || (['jpg', 'jpeg', 'png', 'gif'].includes(doc.file_type)) ? `<button class="btn-preview" onclick="previewDocument('${doc.file_path}', '${doc.mime_type}')">Preview</button>` : ''}
                <button class="btn-download" onclick="downloadDocument('${doc.file_path}', '${doc.original_filename}')">Download</button>
            </div>
        `;

            previewContainer.appendChild(documentItem);
        });
    }

    async function handleSubmission(action) {
        if (!currentSubmissionId) {
            showErrorModal('No submission selected');
            return;
        }

        const remarks = document.getElementById('individualAssessorRemarks').value.trim();

        if ((action === 'reject' || action === 'return' || action === 'flag') && !remarks) {
            showValidationError('Please provide remarks before performing this action.');
            return;
        }

        let score = null;
        if (action === 'approve') {
            score = prompt("Enter assessor score (0-100):");
            if (score === null || isNaN(score) || score < 0 || score > 100) {
                showValidationError("Please enter a valid score between 0 and 100.");
                return;
            }
            score = parseFloat(score);
        }

        try {
            const actionButton = event.target.closest('.btn');
            const originalText = actionButton.innerHTML;
            actionButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            actionButton.disabled = true;

            const response = await fetch(`/assessor/submissions/${currentSubmissionId}/action`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    action: action,
                    remarks: remarks,
                    assessor_score: score
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Failed to process action');
            }

            const individualModal = bootstrap.Modal.getInstance(document.getElementById('individualSubmissionModal'));
            individualModal.hide();

            showSuccessMessage(action);
            location.reload();

        } catch (error) {
            console.error('Error processing submission action:', error);
            showErrorModal('Failed to process action: ' + error.message);
            const actionButton = event.target.closest('.btn');
            actionButton.innerHTML = originalText;
            actionButton.disabled = false;
        }
    }

    function downloadDocument(filePath, fileName) {
        const link = document.createElement('a');
        link.href = `/storage/${filePath}`;
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function previewDocument(filePath, mimeType) {
        const fileExtension = filePath.split('.').pop().toLowerCase();
        const viewerUrl = `/assessor/document-viewer?path=${encodeURIComponent(filePath)}&mime=${encodeURIComponent(mimeType)}`;

        if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
            window.open(`/storage/${filePath}`, '_blank');
        } else if (fileExtension === 'pdf') {
            window.open(viewerUrl, '_blank');
        } else {
            alert('No preview available for this file type. Downloading instead.');
            downloadDocument(filePath, filePath.split('/').pop());
        }
    }


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

        errorModal.addEventListener('hidden.bs.modal', function() {
            document.body.removeChild(errorModal);
        });
    }

    function showValidationError(message) {
        const errorModal = document.createElement('div');
        errorModal.className = 'modal fade';
        errorModal.id = 'validationModal';
        errorModal.innerHTML = `
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

        document.body.appendChild(errorModal);
        const modal = new bootstrap.Modal(errorModal);
        modal.show();

        errorModal.addEventListener('hidden.bs.modal', function() {
            document.body.removeChild(errorModal);
        });
    }

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

        document.body.appendChild(successModal);
        const modal = new bootstrap.Modal(successModal);
        modal.show();

        successModal.addEventListener('hidden.bs.modal', function() {
            document.body.removeChild(successModal);
        });
    }

    // Search and Filter Logic for Student Table
    let currentPage = 1;
    const entriesPerPage = 5;
    let totalEntries = 0;
    let totalPages = 0;

    // This will store the initial student data from the server
    let initialStudentsData = [];

    function initializeStudentPage() {
        const studentRows = document.querySelectorAll('.submissions-table tbody tr');
        initialStudentsData = Array.from(studentRows).map(row => {
            return {
                element: row,
                studentId: row.children[0].textContent.toLowerCase(),
                studentName: row.children[1].textContent.toLowerCase(),
                program: row.children[2].textContent.toLowerCase(),
                college: row.children[3].textContent.toLowerCase()
            };
        });
        filterAndPaginateStudents();
    }

    function filterAndPaginateStudents() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();

        const filteredStudents = initialStudentsData.filter(student => {
            return student.studentId.includes(searchTerm) ||
                student.studentName.includes(searchTerm) ||
                student.program.includes(searchTerm) ||
                student.college.includes(searchTerm);
        });

        totalEntries = filteredStudents.length;
        totalPages = Math.ceil(totalEntries / entriesPerPage);
        currentPage = 1;

        initialStudentsData.forEach(student => student.element.style.display = 'none');

        const start = (currentPage - 1) * entriesPerPage;
        const end = start + entriesPerPage;
        filteredStudents.slice(start, end).forEach(student => {
            student.element.style.display = '';
        });

        updatePaginationInfo();
        generatePageButtons();
        updateNavigationButtons();
    }

    function updatePaginationInfo() {
        const start = (currentPage - 1) * entriesPerPage + 1;
        const end = Math.min(currentPage * entriesPerPage, totalEntries);

        document.getElementById('showingStart').textContent = totalEntries === 0 ? 0 : start;
        document.getElementById('showingEnd').textContent = end;
        document.getElementById('totalEntries').textContent = totalEntries;
    }

    function generatePageButtons() {
        const paginationPages = document.getElementById('paginationPages');
        paginationPages.innerHTML = '';

        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);

        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.className = 'pagination-page';
            if (i === currentPage) {
                pageBtn.classList.add('active');
            }
            pageBtn.textContent = i;
            pageBtn.onclick = () => goToPage(i);
            paginationPages.appendChild(pageBtn);
        }
    }

    function goToPage(page) {
        currentPage = page;
        filterAndPaginateStudents();
    }

    function updateNavigationButtons() {
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages || totalPages === 0;
    }

    document.getElementById('searchInput').addEventListener('input', function(e) {
        filterAndPaginateStudents();
    });

    document.addEventListener('DOMContentLoaded', function() {
        initializeStudentPage();
    });

    // Close the student submissions modal when individual submission modal is opened
    const individualSubmissionModalElement = document.getElementById('individualSubmissionModal');
    individualSubmissionModalElement.addEventListener('show.bs.modal', function() {
        const studentSubmissionsModal = bootstrap.Modal.getInstance(document.getElementById('studentSubmissionsModal'));
        if (studentSubmissionsModal) {
            studentSubmissionsModal.hide();
        }
    });
</script>
@endsection