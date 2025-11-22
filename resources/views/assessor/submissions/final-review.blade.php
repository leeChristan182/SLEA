@extends('layouts.app')

@section('title', 'Final Review - Assessor Dashboard')

@section('content')
<div class="container">
    @include('partials.assessor_sidebar')

    <main class="main-content">
        <div class="page-header">
            <h1>Graduating Student Leaders - Final Review</h1>
        </div>

        {{-- Stats --}}
        <div class="review-stats">
            @php
            $total = $items instanceof \Illuminate\Support\Collection ? $items->count() : $items->total();
            $averageScore = $total > 0
            ? number_format(
            ($items instanceof \Illuminate\Support\Collection ? $items->avg('total_score') : $items->average('total_score')),
            2
            )
            : '0.00';
            $lastReviewed = $items->max('reviewed_at') ?? null;
            @endphp

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $total }}</h3>
                    <p>Assigned Graduating Students</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $averageScore }}</h3>
                    <p>Average Final Score</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $lastReviewed ? \Carbon\Carbon::parse($lastReviewed)->format('M d, Y') : '—' }}</h3>
                    <p>Last Reviewed</p>
                </div>
            </div>
        </div>

        {{-- Filter & Search --}}
        <div class="controls-section">
            <div class="filter-controls">
                <div class="filter-group">
                    <label for="statusFilterSelect">Filter by Status</label>
                    <select id="statusFilterSelect" class="form-select">
                        <option value="">All</option>
                        <option value="finalized">Finalized</option>
                        <option value="submitted">Submitted to Admin</option>
                        <option value="draft">Draft</option>
                        <option value="flagged">Flagged</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="sortSelect">Sort by</label>
                    <select id="sortSelect" class="form-select">
                        <option value="">None</option>
                        <option value="name">Student Name</option>
                        <option value="program">Program</option>
                        <option value="score-desc">Highest Score</option>
                        <option value="score-asc">Lowest Score</option>
                    </select>
                </div>
            </div>

            <div class="search-controls">
                <div class="search-group">
                    <label for="searchInput" class="search-label">Search</label>
                    <div class="search-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="searchInput" class="form-control"
                            placeholder="Search by ID, name, program, or college...">
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-container">
            <table class="table table-hover graduating-table" id="finalReviewTable">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Program</th>
                        <th>College</th>
                        <th>Final Score</th>
                        <th>Status</th>
                        <th style="width: 80px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    @php
                    $student = $item->student ?? null;
                    $academic = $student->studentAcademic ?? null;

                    $studentNumber = $academic->student_number
                    ?? $academic->student_id
                    ?? $student->student_id
                    ?? $student->id;

                    $lastName = $student->last_name ?? $student->lastname ?? '';
                    $firstName = $student->first_name ?? $student->firstname ?? '';
                    $middleName = $student->middle_name ?? $student->middlename ?? '';

                    $studentName = trim(strtoupper($lastName) . ', ' . $firstName . ' ' . $middleName);

                    $programName = optional($academic->program)->name
                    ?? optional($academic)->program_name
                    ?? '—';

                    $collegeName = optional($academic->college)->short_name
                    ?? optional($academic->college)->name
                    ?? optional($academic)->college_name
                    ?? '—';

                    $statusKey = $item->status ?? 'finalized';
                    $statusLabel = strtoupper(str_replace('_', ' ', $statusKey));
                    @endphp
                    <tr class="student-row"
                        data-status="{{ $statusKey }}"
                        data-name="{{ $studentName }}"
                        data-program="{{ $programName }}"
                        data-score="{{ $item->total_score ?? 0 }}">
                        <td class="student-id-cell">{{ $studentNumber }}</td>
                        <td class="student-name-cell">{{ $studentName }}</td>
                        <td class="program-cell">{{ $programName }}</td>
                        <td class="college-cell">{{ $collegeName }}</td>
                        <td class="score-cell">{{ number_format($item->total_score ?? 0, 2) }}</td>
                        <td>
                            <span class="status-badge {{ 'status-' . $statusKey }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td>
                            <button type="button"
                                class="btn btn-view btn-action"
                                data-bs-toggle="modal"
                                data-bs-target="#viewSummaryModal"
                                data-student-id="{{ $student->id ?? '' }}"
                                data-student-number="{{ $studentNumber }}"
                                data-student-name="{{ $studentName }}"
                                data-program="{{ $programName }}"
                                data-college="{{ $collegeName }}"
                                data-score="{{ number_format($item->total_score ?? 0, 2) }}"
                                data-status="{{ $statusLabel }}">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No graduating students found for final review.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- If you later switch to paginator --}}
        @if(method_exists($items, 'links'))
        <div class="pagination-container">
            <div class="pagination-info">
                Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ $items->total() }} students
            </div>
            <div class="pagination-links">
                {{ $items->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </main>
</div>

{{-- Hidden form for submit/flag actions --}}
<form id="finalReviewSubmitForm" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="action" value="">
    <input type="hidden" name="remarks" value="">
</form>

{{-- View Summary Modal --}}
<div class="modal fade" id="viewSummaryModal" tabindex="-1" aria-labelledby="viewSummaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content final-review-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="viewSummaryModalLabel">Graduating Student Final Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Student header --}}
                <div class="student-header">
                    <div>
                        <h4 id="summaryStudentName" class="mb-1"></h4>
                        <p id="summaryStudentMeta" class="mb-0 text-muted small"></p>
                    </div>
                    <div class="summary-score-pill">
                        <span class="label">Final Score</span>
                        <span class="value" id="summaryTotalScore">0.00</span>
                    </div>
                </div>

                {{-- Overview cards --}}
                <div class="summary-grid">
                    <div class="summary-card">
                        <p class="summary-label">Student ID</p>
                        <p class="summary-value" id="summaryStudentNumber">—</p>
                    </div>
                    <div class="summary-card">
                        <p class="summary-label">Qualification</p>
                        <p class="summary-value" id="summaryQualification">For Final Review</p>
                    </div>
                    <div class="summary-card">
                        <p class="summary-label">Review Status</p>
                        <p class="summary-value" id="summaryStatus">—</p>
                    </div>
                </div>

                {{-- Category breakdown (placeholder; you can wire to compiled scores later) --}}
                <div class="summary-table-container mt-4">
                    <h6 class="mb-3">Category Breakdown <span class="text-muted">(from compiled scores)</span></h6>
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
                        <tbody id="summaryCategoryRows">
                            <tr class="text-muted">
                                <td colspan="5" class="text-center">
                                    Category-level scores will appear here once connected to compiled scores.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Good conduct & remarks --}}
                <div class="good-conduct-section mt-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="goodConductCheck">
                        <label class="form-check-label" for="goodConductCheck">
                            I confirm that this student has <strong>no recorded disciplinary actions</strong>
                            based on official OSAS / Guidance records.
                        </label>
                    </div>
                    <small class="text-muted d-block mt-1">
                        This confirmation is required before submitting the final review to the Admin.
                    </small>
                </div>

                <div class="mt-3">
                    <label for="assessorRemarks" class="form-label">Remarks to Admin (optional)</label>
                    <textarea class="form-control" id="assessorRemarks" rows="3"
                        placeholder="e.g., All documents complete and verified."></textarea>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Close
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-warning" id="flagForAdminBtn">
                        <i class="fas fa-flag"></i> Flag for Admin
                    </button>
                    <button type="button" class="btn btn-primary" id="submitToAdminBtn">
                        <i class="fas fa-paper-plane"></i> Submit to Admin
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const table = document.getElementById('finalReviewTable');
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilterSelect');
        const sortSelect = document.getElementById('sortSelect');

        const modal = document.getElementById('viewSummaryModal');
        const goodConductCheck = document.getElementById('goodConductCheck');
        const remarksInput = document.getElementById('assessorRemarks');
        const submitBtn = document.getElementById('submitToAdminBtn');
        const flagBtn = document.getElementById('flagForAdminBtn');
        const submitForm = document.getElementById('finalReviewSubmitForm');

        const storeUrlTemplate = @json(route('assessor.final-review.store', ['student' => '__STUDENT__']));

        // Simple client-side search + filter
        function applyFilters() {
            const search = (searchInput.value || '').toLowerCase();
            const status = statusFilter.value || '';

            const rows = table.querySelectorAll('tbody tr.student-row');
            rows.forEach(row => {
                const name = (row.dataset.name || '').toLowerCase();
                const program = (row.dataset.program || '').toLowerCase();
                const idCell = (row.querySelector('.student-id-cell')?.textContent || '').toLowerCase();
                const collegeCell = (row.querySelector('.college-cell')?.textContent || '').toLowerCase();
                const rowStatus = row.dataset.status || '';

                let matchesSearch = !search ||
                    name.includes(search) ||
                    program.includes(search) ||
                    idCell.includes(search) ||
                    collegeCell.includes(search);

                let matchesStatus = !status || rowStatus === status;

                row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
            });
        }

        function applySort() {
            const value = sortSelect.value;
            if (!value) return;

            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr.student-row'));

            rows.sort((a, b) => {
                if (value === 'name') {
                    return (a.dataset.name || '').localeCompare(b.dataset.name || '');
                }
                if (value === 'program') {
                    return (a.dataset.program || '').localeCompare(b.dataset.program || '');
                }
                if (value === 'score-desc') {
                    return parseFloat(b.dataset.score || 0) - parseFloat(a.dataset.score || 0);
                }
                if (value === 'score-asc') {
                    return parseFloat(a.dataset.score || 0) - parseFloat(b.dataset.score || 0);
                }
                return 0;
            });

            rows.forEach(r => tbody.appendChild(r));
        }

        if (searchInput) {
            searchInput.addEventListener('input', applyFilters);
        }
        if (statusFilter) {
            statusFilter.addEventListener('change', applyFilters);
        }
        if (sortSelect) {
            sortSelect.addEventListener('change', function() {
                applySort();
                applyFilters();
            });
        }

        // Modal fill
        if (modal) {
            modal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                if (!button) return;

                const studentId = button.getAttribute('data-student-id');
                const studentNumber = button.getAttribute('data-student-number') || '—';
                const name = button.getAttribute('data-student-name') || '';
                const program = button.getAttribute('data-program') || '—';
                const college = button.getAttribute('data-college') || '—';
                const score = button.getAttribute('data-score') || '0.00';
                const status = button.getAttribute('data-status') || '—';

                modal.dataset.studentId = studentId || '';

                document.getElementById('summaryStudentName').textContent = name;
                document.getElementById('summaryStudentMeta').textContent =
                    program + ' • ' + college;
                document.getElementById('summaryStudentNumber').textContent = studentNumber;
                document.getElementById('summaryTotalScore').textContent = score;
                document.getElementById('summaryStatus').textContent = status;

                // Simple qualification logic (you can adjust threshold)
                const numericScore = parseFloat(score) || 0;
                document.getElementById('summaryQualification').textContent =
                    numericScore >= 75 ? 'Meets SLEA Threshold' : 'Below Threshold';

                // Reset form bits
                goodConductCheck.checked = false;
                remarksInput.value = '';
            });
        }

        function submitReview(actionType) {
            const studentId = modal?.dataset.studentId || '';
            if (!studentId) {
                alert('Missing student ID for this review.');
                return;
            }

            if (actionType === 'submit' && !goodConductCheck.checked) {
                alert('Please confirm good conduct before submitting to Admin.');
                return;
            }

            if (actionType === 'flag' && !remarksInput.value.trim()) {
                alert('Please provide a short reason in the remarks before flagging for Admin.');
                return;
            }

            if (!submitForm) return;

            const actionInput = submitForm.querySelector('input[name="action"]');
            const remarksHidden = submitForm.querySelector('input[name="remarks"]');

            const url = storeUrlTemplate.replace('__STUDENT__', studentId);

            actionInput.value = actionType;
            remarksHidden.value = remarksInput.value;

            submitForm.action = url;
            submitForm.submit();
        }

        if (submitBtn) {
            submitBtn.addEventListener('click', function() {
                submitReview('submit');
            });
        }

        if (flagBtn) {
            flagBtn.addEventListener('click', function() {
                submitReview('flag');
            });
        }
    });
</script>
@endpush

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

    body.dark-mode .page-header h1 {
        color: #f9bd3d !important;
    }

    .review-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.25rem;
        border-radius: 12px;
        background: #fdf3e3;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #8B0000, #c62828);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.4rem;
    }

    .stat-content h3 {
        font-size: 1.4rem;
        font-weight: 700;
        color: #7B0000;
        margin: 0;
    }

    .stat-content p {
        margin: 0;
        font-size: 0.9rem;
        color: #666;
    }

    /* Dark mode stats */
    body.dark-mode .stat-card {
        background: #E8A840;
    }

    body.dark-mode .stat-icon {
        background: linear-gradient(135deg, #3b0b0b, #8B0000);
    }

    body.dark-mode .stat-content h3 {
        color: #3b0b0b;
    }

    body.dark-mode .stat-content p {
        color: #3b0b0b;
    }

    .controls-section {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 1.5rem;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .filter-controls {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .filter-group label {
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
    }

    .form-select {
        min-width: 180px;
        border-radius: 6px;
        border: 1px solid #ddd;
    }

    .search-controls {
        flex-grow: 1;
        max-width: 380px;
    }

    .search-group {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .search-label {
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
    }

    .search-wrapper {
        position: relative;
    }

    .search-wrapper .search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.9rem;
        color: #999;
    }

    .search-wrapper .form-control {
        padding-left: 2.25rem;
        border-radius: 999px;
    }

    .table-container {
        background: #fff;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
    }

    body.dark-mode .table-container {
        background: #2b2b2b;
    }

    .graduating-table thead th {
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #666;
        border-bottom: 2px solid #eee;
    }

    .graduating-table tbody td {
        vertical-align: middle;
        font-size: 0.92rem;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.35rem 0.6rem;
        border-radius: 999px;
        border: none;
        font-size: 0.9rem;
    }

    .btn-view {
        background: #8B0000;
        color: #fff;
    }

    .btn-view:hover {
        background: #a00000;
        color: #fff;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weigh