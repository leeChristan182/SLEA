@extends('layouts.app')

@section('title', 'Final Review - Assessor Dashboard')

@section('content')
    <div class="container">
        @include('partials.sidebar')

        <main class="main-content">
            <div class="page-header">
                <h1>Graduating Student Leaders - Final Review</h1>
            </div>

            {{-- Flash messages --}}
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Filter + search bar --}}
            <div class="filter-bar">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="statusFilter" class="filter-label">Filter by Status</label>
                        <select id="statusFilter" class="form-select">
                            <option value="">All</option>
                            <option value="draft">Draft</option>
                            <option value="queued_for_admin">Queued for Admin</option>
                            <option value="finalized">Finalized</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="sortSelect" class="filter-label">Sort by</label>
                        <select id="sortSelect" class="form-select">
                            <option value="">None</option>
                            <option value="name">Student Name</option>
                            <option value="program">Program</option>
                            <option value="score-desc">Highest Score</option>
                            <option value="score-asc">Lowest Score</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="searchInput" class="search-label">Search</label>
                        <div class="search-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="searchInput" class="form-control"
                                placeholder="Search by ID, name, college, program, or major...">
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
                            <th>College</th>
                            <th>Program</th>
                            <th>Major</th>
                            <th>Total Score</th>
                            <th>Status</th>
                            <th style="width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
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

                                $collegeName = optional($academic->program->college)->short_name
                                    ?? optional($academic->program->college)->name
                                    ?? optional($academic->college)->short_name
                                    ?? optional($academic->college)->name
                                    ?? optional($academic)->college_name
                                    ?? '—';

                                $majorName = optional($academic->major)->name
                                    ?? optional($academic)->major_name
                                    ?? '—';

                                $statusKey = $item->status ?? 'draft';

                                $statusLabels = [
                                    'draft' => 'Draft',
                                    'queued_for_admin' => 'Queued for Admin',
                                    'finalized' => 'Finalized',
                                ];
                                $statusLabel = $statusLabels[$statusKey]
                                    ?? strtoupper(str_replace('_', ' ', $statusKey));

                                $breakdown = $item->compiledScores
                                    ? $item->compiledScores->map(function ($cs) {
                                        return [
                                            'category' => optional($cs->category)->title ?? '—',
                                            'result' => $cs->category_result,
                                            'score' => (float) $cs->total_score,
                                            'max_points' => (float) $cs->max_points,
                                            'min_required' => (float) $cs->min_required_points,
                                        ];
                                    })->values()
                                    : [];
                            @endphp

                            <tr class="student-row" data-status="{{ $statusKey }}" data-name="{{ $studentName }}"
                                data-program="{{ $programName }}" data-score="{{ $item->total_score ?? 0 }}">
                                <td class="student-id-cell">{{ $studentNumber }}</td>
                                <td class="student-name-cell">{{ $studentName }}</td>
                                <td class="college-cell">{{ $collegeName }}</td>
                                <td class="program-cell">{{ $programName }}</td>
                                <td class="major-cell">{{ $majorName }}</td>
                                <td class="score-cell">{{ number_format($item->total_score ?? 0, 2) }}</td>
                                <td>
                                    <span class="status-badge {{ 'status-' . $statusKey }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-view btn-action" data-bs-toggle="modal"
                                        data-bs-target="#viewSummaryModal" data-student-id="{{ $student->id ?? '' }}"
                                        data-student-number="{{ $studentNumber }}" data-student-name="{{ $studentName }}"
                                        data-program="{{ $programName }}" data-college="{{ $collegeName }}"
                                        data-major="{{ $majorName }}"
                                        data-score="{{ number_format($item->total_score ?? 0, 2) }}"
                                        data-total-max="{{ $item->max_possible ?? $breakdown->sum('max_points') }}"
                                        data-status="{{ $statusLabel }}" data-breakdown='@json($breakdown)'>
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No graduating students found for final review.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    {{-- Hidden form for submit --}}
    <form id="finalReviewSubmitForm" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="action" value="submit">
        <input type="hidden" name="remarks" value="">
    </form>

    {{-- View Summary Modal --}}
    <div class="modal fade" id="viewSummaryModal" tabindex="-1" aria-labelledby="viewSummaryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content final-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewSummaryModalLabel">View Summary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    {{-- Student + score header --}}
                    <div class="modal-student-header">
                        <div>
                            <h4 id="summaryStudentName" class="mb-1"></h4>
                            <p id="summaryStudentMeta" class="mb-0 text-muted small"></p>
                            <p id="summaryStudentNumberLine" class="mb-0 text-muted small"></p>
                        </div>
                        <div class="summary-score-pill">
                            <span class="label">FINAL SCORE</span>
                            <span class="value" id="summaryTotalScore">0.00</span>
                        </div>
                    </div>

                    {{-- Category table --}}
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered summary-table">
                            <thead>
                                <tr>
                                    <th style="width: 30%">Category</th>
                                    <th style="width: 25%">Status</th>
                                    <th style="width: 20%">Score</th>
                                    <th style="width: 25%">Max Points</th>
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

                    <p class="threshold-info small text-muted" id="thresholdInfo"></p>

                    {{-- Good conduct & remarks --}}
                    <div class="good-conduct-section mt-3">
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

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="button" class="btn btn-success final-submit-btn" id="submitToAdminBtn">
                        Submit for Final Review
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const table = document.getElementById('finalReviewTable');
            const statusFilter = document.getElementById('statusFilter');
            const sortSelect = document.getElementById('sortSelect');
            const searchInput = document.getElementById('searchInput');
            const modal = document.getElementById('viewSummaryModal');
            const submitBtn = document.getElementById('submitToAdminBtn');
            const goodConductCheck = document.getElementById('goodConductCheck');
            const remarksInput = document.getElementById('assessorRemarks');
            const submitForm = document.getElementById('finalReviewSubmitForm');

            const storeUrlTemplate = @json(route('assessor.final-review.store', ['student' => '__STUDENT__']));

            function applyFilters() {
                if (!table) return;

                const search = (searchInput?.value || '').toLowerCase().trim();
                const status = statusFilter?.value || '';

                const rows = table.querySelectorAll('tbody tr.student-row');
                rows.forEach(row => {
                    const name = (row.querySelector('.student-name-cell')?.textContent || '').toLowerCase();
                    const program = (row.querySelector('.program-cell')?.textContent || '').toLowerCase();
                    const major = (row.querySelector('.major-cell')?.textContent || '').toLowerCase();
                    const idCell = (row.querySelector('.student-id-cell')?.textContent || '').toLowerCase();
                    const collegeCell = (row.querySelector('.college-cell')?.textContent || '').toLowerCase();
                    const rowStatus = row.dataset.status || '';

                    let matchesSearch = !search ||
                        name.includes(search) ||
                        program.includes(search) ||
                        major.includes(search) ||
                        idCell.includes(search) ||
                        collegeCell.includes(search);

                    let matchesStatus = !status || rowStatus === status;

                    row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
                });
            }

            function applySort() {
                if (!table || !sortSelect) return;

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

            if (searchInput) searchInput.addEventListener('input', applyFilters);
            if (statusFilter) statusFilter.addEventListener('change', applyFilters);
            if (sortSelect) sortSelect.addEventListener('change', applySort);

            // Fill summary modal on open
            if (modal) {
                modal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    if (!button) return;

                    const studentId = button.getAttribute('data-student-id');
                    const studentNumber = button.getAttribute('data-student-number') || '—';
                    const name = button.getAttribute('data-student-name') || '';
                    const program = button.getAttribute('data-program') || '—';
                    const college = button.getAttribute('data-college') || '—';
                    const major = button.getAttribute('data-major') || '—';
                    const score = button.getAttribute('data-score') || '0.00';
                    const totalMax = parseFloat(button.getAttribute('data-total-max') || '0') || 0;
                    const breakdownRaw = button.getAttribute('data-breakdown') || '[]';

                    modal.dataset.studentId = studentId || '';

                    document.getElementById('summaryStudentName').textContent = name;
                    document.getElementById('summaryStudentMeta').textContent =
                        college + ' • ' + program + (major && major !== '—' ? ' • ' + major : '');
                    document.getElementById('summaryStudentNumberLine').textContent =
                        'Student ID: ' + studentNumber;

                    document.getElementById('summaryTotalScore').textContent = score;

                    const numericScore = parseFloat(score) || 0;
                    const thresholdPercent = 0.75;
                    const thresholdScore = totalMax * thresholdPercent;

                    const qualEl = document.getElementById('summaryQualification');
                    if (qualEl) {
                        if (totalMax > 0) {
                            qualEl.textContent = numericScore >= thresholdScore
                                ? `Meets Threshold (≥ ${thresholdScore.toFixed(2)})`
                                : `Below Threshold (Threshold: ${thresholdScore.toFixed(2)})`;
                        } else {
                            qualEl.textContent = 'Threshold not available (no max points)';
                        }
                    }

                    const thresholdInfoEl = document.getElementById('thresholdInfo');
                    if (thresholdInfoEl) {
                        if (totalMax > 0) {
                            thresholdInfoEl.textContent =
                                `Threshold is set at % of the total possible points. ` +
                                `Current total max points: ${totalMax.toFixed(2)}, ` +
                                `threshold score: ${thresholdScore.toFixed(2)}.`;
                        } else {
                            thresholdInfoEl.textContent =
                                'Threshold score cannot be computed because max points are missing.';
                        }
                    }

                    const tbody = document.getElementById('summaryCategoryRows');
                    tbody.innerHTML = '';

                    let breakdown = [];
                    try {
                        breakdown = JSON.parse(breakdownRaw);
                    } catch (e) {
                        console.error('Invalid breakdown JSON', e);
                        breakdown = [];
                    }

                    if (!Array.isArray(breakdown) || breakdown.length === 0) {
                        tbody.innerHTML = `
                                                                <tr class="text-muted">
                                                                    <td colspan="5" class="text-center">
                                                                        No category breakdown available for this student.
                                                                    </td>
                                                                </tr>`;
                    } else {
                        let totalScore = 0;
                        let totalMax = 0;

                        breakdown.forEach((row, index) => {
                            const catName = row.category || `Category ${index + 1}`;
                            const result = (row.result || '').replace(/_/g, ' ').toUpperCase();
                            const sc = parseFloat(row.score) || 0;
                            const maxPts = parseFloat(row.max_points) || 0;

                            totalScore += sc;
                            totalMax += maxPts;

                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                            <td>${catName}</td>
                                            <td>${result || '—'}</td>
                                            <td>${sc.toFixed(2)}</td>
                                            <td>${maxPts.toFixed(2)}</td>
                                        `;
                            tbody.appendChild(tr);
                        });

                        const totalTr = document.createElement('tr');
                        totalTr.classList.add('summary-total-row');
                        totalTr.innerHTML = `
                                <td><strong>Total Score</strong></td>
                                <td></td>
                                <td><strong>${totalScore.toFixed(2)}</strong></td>
                                <td><strong>${totalMax.toFixed(2)}</strong></td>
                            `;
                        tbody.appendChild(totalTr);

                    }

                    goodConductCheck.checked = false;
                    remarksInput.value = '';
                });
            }

            function submitReview() {
                const studentId = modal?.dataset.studentId || '';
                if (!studentId) {
                    alert('Missing student ID for this review.');
                    return;
                }

                if (!goodConductCheck.checked) {
                    alert('Please confirm good conduct before submitting to Admin.');
                    return;
                }

                if (!submitForm) return;

                const remarksHidden = submitForm.querySelector('input[name="remarks"]');
                const url = storeUrlTemplate.replace('__STUDENT__', studentId);

                remarksHidden.value = remarksInput.value;
                submitForm.action = url;
                submitForm.submit();
            }

            if (submitBtn) {
                submitBtn.addEventListener('click', function () {
                    if (confirm('Submit this student\'s final review to Admin?')) {
                        submitReview();
                    }
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

    .filter-bar {
        background: #fff;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
    }

    body.dark-mode .filter-bar {
        background: #2b2b2b;
    }

    .filter-label,
    .search-label {
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
        color: #555;
    }

    body.dark-mode .filter-label,
    body.dark-mode .search-label {
        color: #ddd;
    }

    .search-wrapper {
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.85rem;
        color: #999;
    }

    .search-wrapper input {
        padding-left: 2rem;
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
        font-weight: 600;
    }

    .status-draft {
        background: rgba(108, 117, 125, 0.15);
        color: #6c757d;
    }

    .status-queued_for_admin {
        background: rgba(255, 193, 7, 0.2);
        color: #b58100;
    }

    .status-finalized {
        background: rgba(40, 167, 69, 0.15);
        color: #198754;
    }

    /* MODAL UI */

    .final-modal {
        border-radius: 18px;
    }

    .modal-student-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .summary-score-pill {
        background: #8B0000;
        color: #fff;
        border-radius: 999px;
        padding: 0.5rem 1.4rem;
        text-align: right;
    }

    .summary-score-pill .label {
        display: block;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        opacity: 0.85;
    }

    .summary-score-pill .value {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .summary-table th,
    .summary-table td {
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .summary-total-row td {
        border-top: 2px solid #ccc !important;
    }

    .threshold-info {
        margin-top: -0.5rem;
    }

    .good-conduct-section {
        border-top: 1px solid #eee;
        padding-top: 0.75rem;
    }

    .final-submit-btn {
        border-radius: 999px;
        padding-inline: 1.5rem;
    }

    /* FORCE SUPER-WIDE FINAL REVIEW MODAL
   Completely overrides bootstrap + your style.css */
    #viewSummaryModal .modal-dialog {
        max-width: 1200px !important;
        width: 95vw !important;
        margin-left: auto !important;
        margin-right: auto !important;
    }

    #viewSummaryModal .modal-content.final-modal {
        border-radius: 22px !important;
        padding: 20px 28px !important;
    }

    /* Modal header refinements */
    #viewSummaryModal .modal-header {
        border-bottom: none !important;
        padding-bottom: 0 !important;
    }

    /* Stretch student header spacing */
    .modal-student-header {
        margin-bottom: 1.8rem !important;
    }

    /* Bigger Final Score badge */
    .summary-score-pill {
        padding: 0.7rem 1.6rem !important;
        border-radius: 50px !important;
    }

    .summary-score-pill .label {
        font-size: 0.75rem !important;
    }

    .summary-score-pill .value {
        font-size: 1.7rem !important;
        font-weight: 800 !important;
    }

    /* Make the table look tight, clean, full width */
    .summary-table {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    .summary-table th,
    .summary-table td {
        padding: 10px 12px !important;
        font-size: 0.95rem !important;
        border-color: #ddd !important;
    }

    .summary-total-row td {
        border-top: 2px solid #bbb !important;
        font-weight: 700 !important;
    }

    /* Scrollable body when needed but with padding */
    #viewSummaryModal .modal-dialog {
        max-width: 95% !important;
        width: 95% !important;
        margin: 1.5rem auto;
    }

    #viewSummaryModal .modal-content {
        max-width: 95% !important;
        width: 95% !important;
        margin: 1.5rem auto;
    }

    #viewSummaryModal .modal-body {
        max-height: 70vh !important;
        overflow-y: auto !important;
        padding-right: 10px !important;
    }

    /* Adjust footer */
    #viewSummaryModal .modal-footer {
        border-top: none !important;
        padding-top: 0 !important;
    }

    /* Make the submit button nicer */
    .final-submit-btn {
        padding: 10px 25px !important;
        border-radius: 50px !important;
        font-size: 1rem !important;
        font-weight: 600 !important;
    }

    /* Remove unwanted shadows from style.css */
    #viewSummaryModal * {
        box-shadow: none !important;
    }

    /* Stretch table evenly */
    .summary-table th:nth-child(1),
    .summary-table td:nth-child(1) {
        width: 35% !important;
    }

    /* Remove Verified – already handled in JS */
</style>