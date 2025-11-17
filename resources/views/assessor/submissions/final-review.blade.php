@extends('layouts.app')

@section('title', 'Final Review - Assessor Dashboard')

@section('content')
<div class="container">
    @include('partials.assessor_sidebar')

    <main class="main-content">
        <div class="page-header">
            <h1>List of Graduating Student Leaders</h1>
        </div>

        <div class="review-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="stat-content">
                    <h3>8</h3>
                    <p>Ready for Final Review</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3>15</h3>
                    <p>Finalized This Week</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3>3</h3>
                    <p>Pending Your Review</p>
                </div>
            </div>
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
                        <th>Program</th>
                        <th>College</th>
                        <th>Total Score</th>
                        <th>Leadership Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2021-12345</td>
                        <td>DELA CRUZ, Juan M.</td>
                        <td>Bachelor of Science in Computer Science</td>
                        <td>College of Computing</td>
                        <td>85/100</td>
                        <td>Qualified</td>
                        <td>
                            <button class="btn btn-view btn-action" onclick="viewSummary(1)" title="View Summary">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>2021-67890</td>
                        <td>SANTOS, Maria A.</td>
                        <td>Bachelor of Science in Business Administration</td>
                        <td>College of Business</td>
                        <td>92/100</td>
                        <td>Qualified</td>
                        <td>
                            <button class="btn btn-view btn-action" onclick="viewSummary(2)" title="View Summary">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>2021-54321</td>
                        <td>GARCIA, Pedro L.</td>
                        <td>Bachelor of Science in Engineering</td>
                        <td>College of Engineering</td>
                        <td>78/100</td>
                        <td>Under Review</td>
                        <td>
                            <button class="btn btn-view btn-action" onclick="viewSummary(3)" title="View Summary">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>2021-98765</td>
                        <td>RODRIGUEZ, Ana S.</td>
                        <td>Bachelor of Science in Psychology</td>
                        <td>College of Arts and Sciences</td>
                        <td>88/100</td>
                        <td>Qualified</td>
                        <td>
                            <button class="btn btn-view btn-action" onclick="viewSummary(4)" title="View Summary">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>2021-11111</td>
                        <td>MARTINEZ, Carlos R.</td>
                        <td>Bachelor of Science in Information Technology</td>
                        <td>College of Computing</td>
                        <td>90/100</td>
                        <td>Qualified</td>
                        <td>
                            <button class="btn btn-view btn-action" onclick="viewSummary(5)" title="View Summary">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="pagination-container">
            <div class="pagination-info">
                Showing <span id="showingStart">1</span>-<span id="showingEnd">5</span> of <span id="totalEntries">12</span> submissions
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

<!-- View Summary Modal -->
<div class="modal fade" id="viewSummaryModal" tabindex="-1" aria-labelledby="viewSummaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewSummaryModalLabel">View Summary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="summary-table-container">
                    <table class="table summary-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Leadership Status</th>
                                <th>Score</th>
                                <th>Max Points</th>
                                <th>Verified</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>I</td>
                                <td>Qualified</td>
                                <td>18</td>
                                <td>20</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td>II</td>
                                <td>Tracking</td>
                                <td>16</td>
                                <td>20</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td>III</td>
                                <td>For Final Review</td>
                                <td>19</td>
                                <td>20</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td>IV</td>
                                <td>Qualified</td>
                                <td>17</td>
                                <td>20</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td>V</td>
                                <td>Tracking</td>
                                <td>15</td>
                                <td>20</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td>Total Score</td>
                                <td>For Final Review</td>
                                <td>85</td>
                                <td>100</td>
                                <td>Yes</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-actions">
                    <button class="btn btn-success" onclick="submitForFinalReview()">Submit for Final Review</button>
                    <button class="btn btn-secondary" onclick="flagSubmission()">Flag</button>
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

    /* Dark mode statistics cards */
    body.dark-mode .stat-card {
        background: #E8A840;
    }

    body.dark-mode .stat-icon {
        background: #5C0000;
    }

    body.dark-mode .stat-content h3 {
        color: #5C0000;
    }

    body.dark-mode .stat-content p {
        color: #5C0000;
    }

    .review-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: #F9BD3D;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        background: #7B0000;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }

    .stat-content h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #7B0000;
        margin: 0;
    }

    .stat-content p {
        color: #7B0000;
        margin: 0.25rem 0 0 0;
        font-size: 0.9rem;
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

    body.dark-mode .submissions-table-container {
        background: #2a2a2a !important;
        border: 1px solid #555 !important;
    }

    /* Dark mode modal styles */
    body.dark-mode .large-modal {
        background-color: #2a2a2a !important;
        border-color: #555 !important;
    }

    body.dark-mode .modal-header {
        background-color: #2a2a2a !important;
        border-bottom-color: #555 !important;
    }

    body.dark-mode .modal-header h3 {
        color: #f0f0f0 !important;
    }

    body.dark-mode .modal-body {
        background-color: #2a2a2a !important;
    }

    /* Dark mode modal styles */
    body.dark-mode #viewSummaryModal .modal-content {
        background-color: #2a2a2a !important;
        border-color: #555 !important;
    }

    body.dark-mode #viewSummaryModal .modal-header {
        background-color: #2a2a2a !important;
        border-bottom-color: #555 !important;
    }

    body.dark-mode #viewSummaryModal .modal-title {
        color: #f0f0f0 !important;
    }

    body.dark-mode #viewSummaryModal .modal-body {
        background-color: #2a2a2a !important;
    }

    /* Summary Table Styles */
    .summary-table-container {
        margin-bottom: 2rem;
        position: relative !important;
        width: 100% !important;
    }

    .summary-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .summary-table thead th {
        background-color: #8B0000;
        color: white;
        padding: 1rem;
        text-align: center;
        font-weight: 600;
        border: none;
        border-bottom: 1px solid white;
        border-right: 1px solid white;
        white-space: nowrap;
    }

    .summary-table thead th:last-child {
        border-right: none;
    }

    .summary-table tbody td {
        padding: 1rem;
        text-align: center;
        border-bottom: 1px solid #e9ecef;
        border-right: 1px solid #e9ecef;
        color: #333;
        background: white;
    }

    .summary-table tbody td:last-child {
        border-right: none;
    }

    .summary-table tbody tr:last-child td {
        border-bottom: none;
        font-weight: 600;
        background-color: #f8f9fa;
    }

    /* Modal Actions */
    .modal-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 2rem;
        position: relative !important;
        width: 100% !important;
    }

    .modal-actions .btn {
        padding: 0.75rem 2rem;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .modal-actions .btn-success {
        background-color: #28a745;
        border-color: #28a745;
        color: white;
    }

    .modal-actions .btn-success:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }

    .modal-actions .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
        color: white;
    }

    .modal-actions .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }

    /* Dark mode summary table */
    body.dark-mode .summary-table {
        background: #2a2a2a;
    }

    body.dark-mode .summary-table thead th {
        background-color: #8B0000;
        color: white;
        border-bottom-color: white;
        border-right-color: white;
    }

    body.dark-mode .summary-table thead th:last-child {
        border-right: none;
    }

    body.dark-mode .summary-table tbody td {
        background: #2a2a2a;
        color: #f0f0f0;
        border-bottom-color: #555;
        border-right-color: #555;
    }

    body.dark-mode .summary-table tbody td:last-child {
        border-right: none;
    }

    body.dark-mode .summary-table tbody tr:last-child td {
        background-color: #333;
        color: #f0f0f0;
    }

    body.dark-mode .submissions-table {
        background: #2a2a2a !important;
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
    }

    .success-modal-content .btn-success:hover {
        background-color: #218838 !important;
        border-color: #1e7e34 !important;
        transform: translateY(-1px) !important;
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
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }

        100% {
            transform: scale(1);
        }
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
        padding: 0.5rem 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.2s ease;
        cursor: pointer;
        min-width: 60px;
    }

    .btn-view.btn-action {
        width: 32px;
        height: 32px;
        min-width: 32px;
        padding: 0;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-view:hover {
        background-color: #A52A2A;
        transform: translateY(-1px);
    }

    .btn-view i {
        font-size: 0.9rem;
    }

    .btn-view.btn-action i {
        font-size: 0.8rem;
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

    /* Modal Styles */
    #viewSummaryModal {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        z-index: 1055 !important;
        width: 100% !important;
        height: 100% !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
        outline: 0 !important;
    }

    #viewSummaryModal .modal-dialog {
        position: relative !important;
        width: auto !important;
        max-width: 60vw !important;
        margin: 1.75rem auto !important;
        pointer-events: none !important;
    }

    #viewSummaryModal .modal-content {
        position: relative !important;
        display: flex !important;
        flex-direction: column !important;
        width: 100% !important;
        pointer-events: auto !important;
        background-color: #fff !important;
        background-clip: padding-box !important;
        border: 1px solid rgba(0, 0, 0, 0.2) !important;
        border-radius: 0.3rem !important;
        outline: 0 !important;
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.5) !important;
    }

    #viewSummaryModal .modal-header {
        padding: 1rem 1.5rem !important;
        border-bottom: 1px solid #e9ecef !important;
        flex-shrink: 0 !important;
    }

    #viewSummaryModal .modal-title {
        font-size: 1.25rem !important;
        font-weight: 600 !important;
        color: #333 !important;
        margin: 0 !important;
    }

    #viewSummaryModal .btn-close {
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

    #viewSummaryModal .btn-close::before {
        content: "×" !important;
        color: white !important;
        font-weight: bold !important;
        font-size: 16px !important;
    }

    #viewSummaryModal .btn-close:hover {
        background: #c82333 !important;
        color: white !important;
        transform: translateY(-1px) !important;
    }

    #viewSummaryModal .modal-body {
        position: relative !important;
        flex: 1 1 auto !important;
        padding: 1rem !important;
    }

    .review-form {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .review-section h4 {
        color: #333;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #dee2e6;
    }

    .review-criteria {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .criteria-item label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        font-weight: normal;
    }

    .score-input {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        max-width: 200px;
    }

    .score-input input {
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    textarea {
        width: 100%;
        padding: 1rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        resize: vertical;
    }

    .decision-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        text-decoration: none;
    }

    .btn-success {
        background: #28a745;
        color: white;
    }

    .btn-success:hover {
        background: #218838;
    }

    .btn-warning {
        background: #ffc107;
        color: #212529;
    }

    .btn-warning:hover {
        background: #e0a800;
    }

    .btn-danger {
        background: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background: #c82333;
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

        .decision-buttons {
            flex-direction: column;
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

    function viewSummary(submissionId) {
        // Show the modal using Bootstrap
        const modal = new bootstrap.Modal(document.getElementById('viewSummaryModal'));
        modal.show();
    }

    function submitForFinalReview() {
        // Close the modal using Bootstrap
        const modal = bootstrap.Modal.getInstance(document.getElementById('viewSummaryModal'));
        modal.hide();

        // Show success message
        showSuccessMessage('submit');
    }

    function flagSubmission() {
        // Close the modal using Bootstrap
        const modal = bootstrap.Modal.getInstance(document.getElementById('viewSummaryModal'));
        modal.hide();

        // Show success message
        showSuccessMessage('flag');
    }

    function showSuccessMessage(action) {
        // Create success modal
        const successModal = document.createElement('div');
        successModal.className = 'modal fade';
        successModal.id = 'successModal';

        let message, icon, color;
        if (action === 'submit') {
            message = 'Successfully submitted for final review!';
            icon = 'fas fa-check-circle';
            color = '#28a745';
        } else if (action === 'flag') {
            message = 'Submission has been flagged for review!';
            icon = 'fas fa-flag';
            color = '#ffc107';
        }

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

        // Add to body
        document.body.appendChild(successModal);

        // Show modal
        const modal = new bootstrap.Modal(successModal);
        modal.show();

        // Remove modal from DOM when hidden
        successModal.addEventListener('hidden.bs.modal', function() {
            document.body.removeChild(successModal);
        });
    }

    function viewCompletedReview(submissionId) {
        alert('Viewing completed review for student: ' + submissionId);
    }
</script>
@endsection