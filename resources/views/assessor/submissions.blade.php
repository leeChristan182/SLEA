@extends('layouts.app')

@section('title', 'All Submissions - Assessor Dashboard')

@section('content')
<div class="container">
    @include('partials.assessor-sidebar')

    <main class="main-content">
        <div class="page-header">
            <h1>All Submissions</h1>
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

        <div class="submissions-table-container">
            <table class="table submissions-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Document Title</th>
                        <th>Category</th>
                        <th>Score</th>
                        <th>Date Reviewed</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2021-12345</td>
                        <td>DELA CRUZ, Juan M.</td>
                        <td>Leadership Portfolio</td>
                        <td>Leadership</td>
                        <td>85/100</td>
                        <td>2024-01-16</td>
                        <td>
                            <button class="btn btn-view" onclick="viewSubmission(1)" title="View Submission">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>2021-67890</td>
                        <td>SANTOS, Maria A.</td>
                        <td>Community Service Report</td>
                        <td>Community Service</td>
                        <td>92/100</td>
                        <td>2024-01-15</td>
                        <td>
                            <button class="btn btn-view" onclick="viewSubmission(2)" title="View Submission">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>2021-54321</td>
                        <td>GARCIA, Pedro L.</td>
                        <td>Academic Excellence Portfolio</td>
                        <td>Academic</td>
                        <td>78/100</td>
                        <td>2024-01-14</td>
                        <td>
                            <button class="btn btn-view" onclick="viewSubmission(3)" title="View Submission">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>2021-98765</td>
                        <td>RODRIGUEZ, Ana S.</td>
                        <td>Leadership Development Plan</td>
                        <td>Leadership</td>
                        <td>88/100</td>
                        <td>2024-01-13</td>
                        <td>
                            <button class="btn btn-view" onclick="viewSubmission(4)" title="View Submission">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>2021-11111</td>
                        <td>MARTINEZ, Carlos R.</td>
                        <td>Innovation Project Proposal</td>
                        <td>Innovation</td>
                        <td>90/100</td>
                        <td>2024-01-12</td>
                        <td>
                            <button class="btn btn-view" onclick="viewSubmission(5)" title="View Submission">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="pagination-container">
            <div class="pagination-info">
                Showing <span id="showingStart">1</span>-<span id="showingEnd">5</span> of <span id="totalEntries">25</span> submissions
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

<!-- View Submission Modal -->
<div class="modal fade" id="submissionModal" tabindex="-1" aria-labelledby="submissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="submissionModalLabel">Submission Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="submission-content">
                    <!-- Student Information Card -->
                    <div class="info-card">
                        <div class="card-header">
                            <h6 class="card-title">Student Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="detail-row">
                                <span class="detail-label">Student ID:</span>
                                <span class="detail-value" id="studentId">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Student Name:</span>
                                <span class="detail-value" id="studentName">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Email:</span>
                                <span class="detail-value" id="studentEmail">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Program:</span>
                                <span class="detail-value" id="studentProgram">-</span>
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
                                <span class="detail-label">Document Title:</span>
                                <span class="detail-value" id="documentTitle">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Category:</span>
                                <span class="detail-value" id="documentCategory">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date Submitted:</span>
                                <span class="detail-value" id="dateSubmitted">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date Reviewed:</span>
                                <span class="detail-value" id="dateReviewed">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Score:</span>
                                <span class="detail-value" id="submissionScore">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Status:</span>
                                <span class="detail-value">
                                    <span class="status-badge status-approved" id="submissionStatus">Approved</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Assessment Details Card -->
                    <div class="info-card">
                        <div class="card-header">
                            <h6 class="card-title">Assessment Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="detail-row">
                                <span class="detail-label">Assessor:</span>
                                <span class="detail-value" id="assessorName">Dr. Smith</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Assessment Date:</span>
                                <span class="detail-value" id="assessmentDate">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Criteria Met:</span>
                                <span class="detail-value" id="criteriaMet">8/10</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Overall Rating:</span>
                                <span class="detail-value" id="overallRating">Excellent</span>
                            </div>
                        </div>
                    </div>

                    <!-- Comments Card -->
                    <div class="info-card">
                        <div class="card-header">
                            <h6 class="card-title">Assessor Comments</h6>
                        </div>
                        <div class="card-body">
                            <div class="comments-section">
                                <p id="assessorComments">This submission demonstrates excellent understanding of the leadership principles and shows clear evidence of practical application. The student has provided comprehensive documentation and thoughtful reflection on their experiences.</p>
                            </div>
                        </div>
                    </div>
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
        gap: 2rem;
        align-items: flex-end;
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
        text-align: center !important;
        white-space: nowrap !important;
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

    /* Modal Styles */
    #submissionModal .modal-dialog {
        max-width: 60vw !important;
        width: 60vw !important;
        margin: 1.75rem auto !important;
        display: flex !important;
        align-items: center !important;
        min-height: calc(100vh - 3.5rem) !important;
        justify-content: center !important;
    }

    #submissionModal .modal-content {
        max-height: 70vh !important;
        display: flex !important;
        flex-direction: column !important;
        border: none !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
        animation: modalSlideIn 0.3s ease-out !important;
        width: 100% !important;
        margin: 0 auto !important;
    }

    #submissionModal .modal-header {
        padding: 1rem 1.5rem !important;
        border-bottom: 1px solid #e9ecef !important;
        flex-shrink: 0 !important;
    }

    #submissionModal .modal-title {
        font-size: 1.25rem !important;
        font-weight: 600 !important;
        color: #333 !important;
        margin: 0 !important;
    }

    #submissionModal .btn-close {
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

    #submissionModal .btn-close::before {
        content: "×" !important;
        color: white !important;
        font-weight: bold !important;
        font-size: 16px !important;
    }

    #submissionModal .btn-close:hover {
        background: #c82333 !important;
        color: white !important;
        transform: translateY(-1px) !important;
    }

    #submissionModal .modal-body {
        padding: 1.5rem !important;
        overflow-y: auto !important;
        flex: 1 !important;
    }

    .submission-content {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .info-card {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .card-header {
        background: #f8f9fa;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e9ecef;
    }

    .card-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
        color: #333;
    }

    .card-body {
        padding: 1rem;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f8f9fa;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        font-weight: 500;
        color: #666;
        flex: 1;
    }

    .detail-value {
        font-weight: 400;
        color: #333;
        flex: 2;
        text-align: right;
    }

    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-approved {
        background-color: #d4edda;
        color: #155724;
    }

    .status-pending {
        background-color: #fff3cd;
        color: #856404;
    }

    .status-rejected {
        background-color: #f8d7da;
        color: #721c24;
    }

    .comments-section {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 6px;
    }

    .comments-section p {
        margin: 0;
        line-height: 1.6;
        color: #555;
    }

    /* Dark mode modal styles */
    body.dark-mode #submissionModal .modal-content {
        background-color: #2a2a2a !important;
        border-color: #555 !important;
    }

    body.dark-mode #submissionModal .modal-header {
        background-color: #2a2a2a !important;
        border-bottom-color: #555 !important;
    }

    body.dark-mode #submissionModal .modal-title {
        color: #f0f0f0 !important;
    }

    body.dark-mode #submissionModal .modal-body {
        background-color: #2a2a2a !important;
    }

    body.dark-mode .info-card {
        background-color: #2a2a2a !important;
        border-color: #555 !important;
    }

    body.dark-mode .card-header {
        background-color: #333 !important;
        border-bottom-color: #555 !important;
    }

    body.dark-mode .card-title {
        color: #f9bd3d !important;
    }

    body.dark-mode .detail-label {
        color: #ccc !important;
    }

    body.dark-mode .detail-value {
        color: #f0f0f0 !important;
    }

    body.dark-mode .comments-section {
        background-color: #333 !important;
    }

    body.dark-mode .comments-section p {
        color: #ccc !important;
    }

    /* Modal animation */
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

    /* Additional modal centering overrides */
    #submissionModal {
        text-align: center !important;
    }

    #submissionModal .modal-dialog.modal-xl {
        max-width: 60vw !important;
        width: 60vw !important;
        margin: 1.75rem auto !important;
        position: relative !important;
        left: auto !important;
        right: auto !important;
        transform: none !important;
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
        text-align: center !important;
        white-space: nowrap !important;
    }

    .submissions-table-container .submissions-table thead th:last-child {
        border-right: none !important;
    }

    body.dark-mode .submissions-table-container .submissions-table thead th {
        background-color: #8B0000 !important;
        color: white !important;
        border-bottom: 1px solid #555 !important;
        border-right: 1px solid #555 !important;
        text-align: center !important;
        white-space: nowrap !important;
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
        display: flex;
        align-items: center;
        gap: 0.5rem;
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

    @media (max-width: 768px) {
        .controls-section {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
        }

        .filter-controls {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
        }

        .search-controls {
            margin-left: 0;
        }

        .search-group input {
            min-width: 100%;
        }

        .pagination-container {
            flex-direction: column;
            gap: 1rem;
            align-items: center;
        }
    }
</style>

<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const tableRows = document.querySelectorAll('.submissions-table tbody tr');

        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Filter functionality
    document.getElementById('filterSelect').addEventListener('change', function(e) {
        // Implement filter logic here
        console.log('Filter changed:', e.target.value);
    });

    // Sort functionality
    document.getElementById('sortSelect').addEventListener('change', function(e) {
        // Implement sort logic here
        console.log('Sort changed:', e.target.value);
    });

    // Dynamic Pagination
    let currentPage = 1;
    const entriesPerPage = 5;
    let totalEntries = 0;
    let totalPages = 0;

    // Initialize pagination
    function initializePagination() {
        // Count total entries (rows in table)
        const tableRows = document.querySelectorAll('.submissions-table tbody tr');
        totalEntries = tableRows.length;
        totalPages = Math.ceil(totalEntries / entriesPerPage);

        // Update pagination info
        updatePaginationInfo();

        // Generate page buttons
        generatePageButtons();

        // Show/hide entries based on current page
        showPageEntries();
    }

    // Update pagination info
    function updatePaginationInfo() {
        const start = (currentPage - 1) * entriesPerPage + 1;
        const end = Math.min(currentPage * entriesPerPage, totalEntries);

        document.getElementById('showingStart').textContent = start;
        document.getElementById('showingEnd').textContent = end;
        document.getElementById('totalEntries').textContent = totalEntries;
    }

    // Generate page buttons
    function generatePageButtons() {
        const paginationPages = document.getElementById('paginationPages');
        paginationPages.innerHTML = '';

        // Show max 5 page buttons
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);

        // Adjust start page if we're near the end
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

    // Go to specific page
    function goToPage(page) {
        currentPage = page;
        showPageEntries();
        updatePaginationInfo();
        generatePageButtons();
        updateNavigationButtons();
    }

    // Show entries for current page
    function showPageEntries() {
        const tableRows = document.querySelectorAll('.submissions-table tbody tr');
        const start = (currentPage - 1) * entriesPerPage;
        const end = start + entriesPerPage;

        tableRows.forEach((row, index) => {
            if (index >= start && index < end) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Update navigation buttons
    function updateNavigationButtons() {
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;
    }

    // Previous page
    document.getElementById('prevBtn').addEventListener('click', function() {
        if (currentPage > 1) {
            goToPage(currentPage - 1);
        }
    });

    // Next page
    document.getElementById('nextBtn').addEventListener('click', function() {
        if (currentPage < totalPages) {
            goToPage(currentPage + 1);
        }
    });

    // Initialize pagination when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initializePagination();
    });

    function viewSubmission(submissionId) {
        // Mock data for different submissions
        const submissionData = {
            1: {
                studentId: '2021-12345',
                studentName: 'DELA CRUZ, Juan M.',
                studentEmail: 'juan.delacruz@student.edu',
                studentProgram: 'Bachelor of Science in Computer Science',
                documentTitle: 'Leadership Portfolio',
                documentCategory: 'Leadership',
                dateSubmitted: '2024-01-15',
                dateReviewed: '2024-01-16',
                submissionScore: '85/100',
                submissionStatus: 'Approved',
                assessorName: 'Dr. Smith',
                assessmentDate: '2024-01-16',
                criteriaMet: '8/10',
                overallRating: 'Good',
                assessorComments: 'This submission demonstrates good understanding of leadership principles. The student has provided adequate documentation and shows evidence of practical application. Some areas could be improved with more detailed reflection.'
            },
            2: {
                studentId: '2021-67890',
                studentName: 'SANTOS, Maria A.',
                studentEmail: 'maria.santos@student.edu',
                studentProgram: 'Bachelor of Science in Business Administration',
                documentTitle: 'Community Service Report',
                documentCategory: 'Community Service',
                dateSubmitted: '2024-01-14',
                dateReviewed: '2024-01-15',
                submissionScore: '92/100',
                submissionStatus: 'Approved',
                assessorName: 'Dr. Johnson',
                assessmentDate: '2024-01-15',
                criteriaMet: '9/10',
                overallRating: 'Excellent',
                assessorComments: 'Outstanding community service work with comprehensive documentation. The student demonstrates exceptional commitment to community engagement and provides thoughtful reflection on their experiences.'
            },
            3: {
                studentId: '2021-54321',
                studentName: 'GARCIA, Pedro L.',
                studentEmail: 'pedro.garcia@student.edu',
                studentProgram: 'Bachelor of Science in Engineering',
                documentTitle: 'Academic Excellence Portfolio',
                documentCategory: 'Academic',
                dateSubmitted: '2024-01-13',
                dateReviewed: '2024-01-14',
                submissionScore: '78/100',
                submissionStatus: 'Approved',
                assessorName: 'Dr. Williams',
                assessmentDate: '2024-01-14',
                criteriaMet: '7/10',
                overallRating: 'Satisfactory',
                assessorComments: 'Good academic performance with solid documentation. The student shows consistent effort and achievement. Some areas could benefit from more detailed analysis and reflection.'
            },
            4: {
                studentId: '2021-98765',
                studentName: 'RODRIGUEZ, Ana S.',
                studentEmail: 'ana.rodriguez@student.edu',
                studentProgram: 'Bachelor of Science in Psychology',
                documentTitle: 'Leadership Development Plan',
                documentCategory: 'Leadership',
                dateSubmitted: '2024-01-12',
                dateReviewed: '2024-01-13',
                submissionScore: '88/100',
                submissionStatus: 'Approved',
                assessorName: 'Dr. Brown',
                assessmentDate: '2024-01-13',
                criteriaMet: '8/10',
                overallRating: 'Very Good',
                assessorComments: 'Well-structured leadership development plan with clear goals and actionable steps. The student demonstrates good self-awareness and provides realistic timelines for development.'
            },
            5: {
                studentId: '2021-11111',
                studentName: 'MARTINEZ, Carlos R.',
                studentEmail: 'carlos.martinez@student.edu',
                studentProgram: 'Bachelor of Science in Information Technology',
                documentTitle: 'Innovation Project Proposal',
                documentCategory: 'Innovation',
                dateSubmitted: '2024-01-11',
                dateReviewed: '2024-01-12',
                submissionScore: '90/100',
                submissionStatus: 'Approved',
                assessorName: 'Dr. Davis',
                assessmentDate: '2024-01-12',
                criteriaMet: '9/10',
                overallRating: 'Excellent',
                assessorComments: 'Highly innovative project proposal with excellent technical feasibility analysis. The student demonstrates strong problem-solving skills and provides comprehensive implementation strategies.'
            }
        };

        const data = submissionData[submissionId];
        if (data) {
            // Populate modal with data
            document.getElementById('studentId').textContent = data.studentId;
            document.getElementById('studentName').textContent = data.studentName;
            document.getElementById('studentEmail').textContent = data.studentEmail;
            document.getElementById('studentProgram').textContent = data.studentProgram;
            document.getElementById('documentTitle').textContent = data.documentTitle;
            document.getElementById('documentCategory').textContent = data.documentCategory;
            document.getElementById('dateSubmitted').textContent = data.dateSubmitted;
            document.getElementById('dateReviewed').textContent = data.dateReviewed;
            document.getElementById('submissionScore').textContent = data.submissionScore;
            document.getElementById('assessorName').textContent = data.assessorName;
            document.getElementById('assessmentDate').textContent = data.assessmentDate;
            document.getElementById('criteriaMet').textContent = data.criteriaMet;
            document.getElementById('overallRating').textContent = data.overallRating;
            document.getElementById('assessorComments').textContent = data.assessorComments;

            // Update status badge
            const statusElement = document.getElementById('submissionStatus');
            statusElement.textContent = data.submissionStatus;
            statusElement.className = 'status-badge status-approved'; // Default to approved for this view

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('submissionModal'));
            modal.show();
        }
    }
</script>
@endsection
