{{-- resources/views/admin/final-review.blade.php --}}
@extends('layouts.app')

@section('title', 'Final Review - Admin Dashboard')

@section('content')
    <div class="container">
        @include('partials.sidebar')

        <main class="main-content">
            <div class="page-header">
                <h1>Graduating Student Leaders - Admin Final Review</h1>
            </div>

            {{-- Flash messages --}}
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- Controls Section --}}
            <div class="controls-section">
                <div class="filter-controls">
                    <div class="filter-group">
                        <label for="statusFilter">Filter by Decision</label>
                        <select id="statusFilter" class="form-select">
                            <option value="">All</option>
                            <option value="pending">Pending decision</option>
                            <option value="approved">Qualified</option>
                            <option value="not_qualified">Not qualified</option>
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
                        <input type="text" id="searchInput" class="form-control"
                            placeholder="Search by ID, name, college, program, or major...">
                        <button type="button" class="btn-search-maroon search-btn-attached" id="searchBtn" title="Search">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="submissions-table-container">
                <table class="table submissions-table" id="adminFinalReviewTable">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>College</th>
                            <th>Program</th>
                            <th>Major</th>
                            <th>Final Score</th>
                            <th>Decision</th>
                            <th style="width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            @php
                                /** @var \App\Models\AssessorFinalReview $afr */
                                $afr = $item;
                                $student = $afr->student ?? null;
                                $academic = $student->studentAcademic ?? null;
                                $final = $afr->finalReview ?? null;

                                $studentNumber = $academic->student_number
                                    ?? $academic->student_id
                                    ?? $student->student_id
                                    ?? $student->id;

                                $yearLevel = $academic->year_level ?? '—';

                                $lastName = $student->last_name ?? $student->lastname ?? '';
                                $firstName = $student->first_name ?? $student->firstname ?? '';
                                $middleName = $student->middle_name ?? $student->middlename ?? '';

                                $studentName = trim(strtoupper($lastName) . ', ' . $firstName . ' ' . $middleName);

                                $programName = optional($academic->program)->name
                                    ?? optional($academic)->program_name
                                    ?? '—';

                                $collegeName = optional(optional($academic->program)->college)->short_name
                                    ?? optional(optional($academic->program)->college)->name
                                    ?? optional($academic->college)->short_name
                                    ?? optional($academic->college)->name
                                    ?? optional($academic)->college_name
                                    ?? '—';

                                $majorName = optional($academic->major)->name
                                    ?? optional($academic)->major_name
                                    ?? '—';

                                // decision from final_reviews table (enum: approved / not_qualified)
                                $decisionKey = $final->decision ?? null;
                                $decisionLabels = [
                                    'approved' => 'Qualified',
                                    'not_qualified' => 'Not qualified',
                                ];
                                $decisionLabel = $decisionKey
                                    ? ($decisionLabels[$decisionKey] ?? ucfirst(str_replace('_', ' ', $decisionKey)))
                                    : 'Pending';

                                $decisionClass = match ($decisionKey) {
                                    'approved' => 'badge-approved',
                                    'not_qualified' => 'badge-not-qualified',
                                    default => 'badge-pending',
                                };

                                // Get all categories in order and match with compiled scores
                                $allCategories = \App\Models\RubricCategory::orderBy('order_no')->get();
                                $compiledScores = $afr->compiledScores ?? collect();
                                $scoresByCategory = $compiledScores->keyBy('rubric_category_id');

                                // Build breakdown with all 5 categories in order
                                $breakdown = $allCategories->map(function ($category) use ($scoresByCategory) {
                                    $cs = $scoresByCategory->get($category->id);
                                    return [
                                        'category' => $category->title ?? '—',
                                        'order_no' => $category->order_no ?? 999,
                                        'result' => $cs->category_result ?? null,
                                        'score' => (float) ($cs->total_score ?? 0),
                                        'max_points' => (float) ($cs->max_points ?? $category->max_points ?? 0),
                                        'min_required' => (float) ($cs->min_required_points ?? $category->min_required_points ?? 0),
                                    ];
                                })->sortBy('order_no')->values();
                            @endphp

                            <tr class="student-row" data-decision="{{ $decisionKey ?? 'pending' }}"
                                data-name="{{ $studentName }}" data-program="{{ $programName }}"
                                data-score="{{ $afr->total_score ?? 0 }}">
                                <td class="student-id-cell">{{ $studentNumber }}</td>
                                <td class="student-name-cell">{{ $studentName }}</td>
                                <td class="college-cell">{{ $collegeName }}</td>
                                <td class="program-cell">{{ $programName }}</td>
                                <td class="major-cell">{{ $majorName }}</td>
                                <td class="score-cell">{{ number_format($afr->total_score ?? 0, 2) }}</td>
                                <td>
                                    <span class="decision-badge {{ $decisionClass }}">
                                        {{ $decisionLabel }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons-group">
                                        <button type="button" class="btn-view btn-action" data-bs-toggle="modal"
                                            data-bs-target="#adminViewSummaryModal" data-afr-id="{{ $afr->id }}"
                                            data-student-number="{{ $studentNumber }}" data-student-name="{{ $studentName }}"
                                            data-college="{{ $collegeName }}" data-program="{{ $programName }}"
                                            data-major="{{ $majorName }}" data-year-level="{{ $yearLevel }}"
                                            data-score="{{ number_format($afr->total_score ?? 0, 2) }}"
                                            data-decision="{{ $decisionKey ?? 'pending' }}" data-breakdown='@json($breakdown)'
                                            title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No students queued for Admin final review.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($items->hasPages())
                <div class="pagination-container" data-pagination-container>
                    <div class="pagination-info">
                        Showing {{ $items->firstItem() ?? 0 }} – {{ $items->lastItem() ?? 0 }}
                        of {{ $items->total() }} entries
                    </div>

                    <div class="unified-pagination">
                        @if($items->onFirstPage())
                            <button class="btn-nav" disabled>
                                <i class="fas fa-chevron-left"></i> Previous
                            </button>
                        @else
                            <a href="{{ $items->previousPageUrl() }}" class="btn-nav">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        @endif

                        <span class="pagination-pages">
                            @php
                                $currentPage = $items->currentPage();
                                $lastPage = $items->lastPage();
                                $start = max(1, $currentPage - 2);
                                $end = min($lastPage, $currentPage + 2);
                            @endphp

                            @if($start > 1)
                                <a href="{{ $items->url(1) }}" class="page-btn">1</a>
                                @if($start > 2)
                                    <span class="page-btn disabled">...</span>
                                @endif
                            @endif

                            @for($i = $start; $i <= $end; $i++)
                                @if($i == $currentPage)
                                    <span class="page-btn active">{{ $i }}</span>
                                @else
                                    <a href="{{ $items->url($i) }}" class="page-btn">{{ $i }}</a>
                                @endif
                            @endfor

                            @if($end < $lastPage)
                                @if($end < $lastPage - 1)
                                    <span class="page-btn disabled">...</span>
                                @endif
                                <a href="{{ $items->url($lastPage) }}" class="page-btn">{{ $lastPage }}</a>
                            @endif
                        </span>

                        @if($items->hasMorePages())
                            <a href="{{ $items->nextPageUrl() }}" class="btn-nav">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        @else
                            <button class="btn-nav" disabled>
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </main>
    </div>

    {{-- Hidden form for admin decision --}}
    <form id="adminFinalDecisionForm" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="decision" value="">
    </form>

    <link rel="stylesheet" href="{{ asset('css/pending-submissions.css') }}">
    <script src="{{ asset('js/admin_pagination.js') }}"></script>

    {{-- Admin View Summary Modal --}}
    <div class="modal fade admin-final-modal" {{-- <==extra class to scope overrides --}} id="adminViewSummaryModal"
        tabindex="-1" aria-labelledby="adminViewSummaryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content final-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="adminViewSummaryModalLabel">
                        Graduating Student - Final Review
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    {{-- Header: name + score --}}
                    <div class="student-header modal-student-header">
                        <div>
                            <h4 id="adminSummaryStudentName" class="mb-1"></h4>
                            <p id="adminSummaryStudentMeta" class="mb-0 text-muted small"></p>
                        </div>
                        <div class="summary-score-pill">
                            <span class="label">Assessor Final Score</span>
                            <span class="value" id="adminSummaryTotalScore">0.00</span>
                        </div>
                    </div>

                    {{-- Student info grid (ID, year level, college, program, major) --}}
                    <div class="admin-student-info-grid mb-4">
                        <div class="info-block">
                            <p class="info-label">Student ID</p>
                            <p class="info-value" id="adminInfoStudentNumber">—</p>
                        </div>
                        <div class="info-block">
                            <p class="info-label">Year Level</p>
                            <p class="info-value" id="adminInfoYearLevel">—</p>
                        </div>
                        <div class="info-block">
                            <p class="info-label">College</p>
                            <p class="info-value" id="adminInfoCollege">—</p>
                        </div>
                        <div class="info-block">
                            <p class="info-label">Program</p>
                            <p class="info-value" id="adminInfoProgram">—</p>
                        </div>
                        <div class="info-block">
                            <p class="info-label">Major</p>
                            <p class="info-value" id="adminInfoMajor">—</p>
                        </div>
                        <div class="info-block">
                            <p class="info-label">Current Decision</p>
                            <p class="info-value" id="adminSummaryDecision">Pending</p>
                        </div>
                    </div>

                    {{-- Category breakdown --}}
                    <div class="mb-4 mt-3">
                        <h5 class="section-title">Category Score Breakdown</h5>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle summary-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:40%">Category</th>
                                        <th style="width:30%">Score</th>
                                        <th style="width:30%">Max Points</th>
                                    </tr>
                                </thead>
                                <tbody id="adminSummaryCategoryRows">
                                    <tr class="text-muted">
                                        <td colspan="3" class="text-center">
                                            Category-level scores will appear here once connected to compiled scores.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="decision-buttons-group">
                        <button type="button" class="btn btn-success admin-decision-btn" id="adminApproveBtn">
                            <i class="fas fa-check-circle"></i> Qualified
                        </button>
                        <button type="button" class="btn btn-danger admin-decision-btn" id="adminNotQualifiedBtn">
                            <i class="fas fa-times-circle"></i> Not Qualified
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const table = document.getElementById('adminFinalReviewTable');
            const statusFilter = document.getElementById('statusFilter');
            const sortSelect = document.getElementById('sortSelect');
            const searchInput = document.getElementById('searchInput');
            const modalEl = document.getElementById('adminViewSummaryModal');
            const decisionForm = document.getElementById('adminFinalDecisionForm');
            const approveBtn = document.getElementById('adminApproveBtn');
            const notQualBtn = document.getElementById('adminNotQualifiedBtn');

            const storeUrlTemplate = @json(route('admin.final-review.decision', ['assessorFinalReview' => '__AFR__']));

            function applyFilters() {
                if (!table) return;

                const search = (searchInput?.value || '').toLowerCase().trim();
                const decision = statusFilter?.value || '';

                const rows = table.querySelectorAll('tbody tr.student-row');
                rows.forEach(row => {
                    const name = (row.querySelector('.student-name-cell')?.textContent || '').toLowerCase();
                    const program = (row.querySelector('.program-cell')?.textContent || '').toLowerCase();
                    const major = (row.querySelector('.major-cell')?.textContent || '').toLowerCase();
                    const idCell = (row.querySelector('.student-id-cell')?.textContent || '').toLowerCase();
                    const college = (row.querySelector('.college-cell')?.textContent || '').toLowerCase();
                    const rowDec = row.dataset.decision || 'pending';

                    let matchesSearch =
                        !search ||
                        name.includes(search) ||
                        program.includes(search) ||
                        major.includes(search) ||
                        idCell.includes(search) ||
                        college.includes(search);

                    let matchesDecision =
                        !decision ||
                        (decision === 'pending' && rowDec === 'pending') ||
                        rowDec === decision;

                    row.style.display = (matchesSearch && matchesDecision) ? '' : 'none';
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
            if (modalEl) {
                modalEl.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    if (!button) return;

                    const afrId = button.getAttribute('data-afr-id');
                    const studentNum = button.getAttribute('data-student-number') || '—';
                    const name = button.getAttribute('data-student-name') || '';
                    const program = button.getAttribute('data-program') || '—';
                    const college = button.getAttribute('data-college') || '—';
                    const major = button.getAttribute('data-major') || '—';
                    const yearLevel = button.getAttribute('data-year-level') || '—';
                    const score = button.getAttribute('data-score') || '0.00';
                    const decisionKey = button.getAttribute('data-decision') || 'pending';
                    const breakdownRaw = button.getAttribute('data-breakdown') || '[]';

                    modalEl.dataset.afrId = afrId || '';

                    // Header
                    document.getElementById('adminSummaryStudentName').textContent = name;
                    document.getElementById('adminSummaryStudentMeta').textContent =
                        `${college} • ${program} • ${major} • Year ${yearLevel}`;
                    document.getElementById('adminSummaryTotalScore').textContent = score;

                    // Info grid
                    document.getElementById('adminInfoStudentNumber').textContent = studentNum;
                    document.getElementById('adminInfoYearLevel').textContent = yearLevel;
                    document.getElementById('adminInfoCollege').textContent = college;
                    document.getElementById('adminInfoProgram').textContent = program;
                    document.getElementById('adminInfoMajor').textContent = major;

                    const decisionLabel =
                        decisionKey === 'approved'
                            ? 'Qualified'
                            : decisionKey === 'not_qualified'
                                ? 'Not qualified'
                                : 'Pending';
                    document.getElementById('adminSummaryDecision').textContent = decisionLabel;

                    // Category rows
                    const tbody = document.getElementById('adminSummaryCategoryRows');
                    tbody.innerHTML = '';

                    let breakdown = [];
                    try {
                        breakdown = JSON.parse(breakdownRaw);
                    } catch (e) {
                        breakdown = [];
                    }

                    let totalScore = 0;
                    let totalMax = 0;

                    if (!Array.isArray(breakdown) || breakdown.length === 0) {
                        tbody.innerHTML = `
                                    <tr class="text-muted">
                                        <td colspan="3" class="text-center">
                                            No category breakdown available for this student.
                                        </td>
                                    </tr>`;
                    } else {
                        // Sort breakdown by order_no to ensure correct sequence
                        breakdown.sort((a, b) => {
                            const orderA = a.order_no ?? 999;
                            const orderB = b.order_no ?? 999;
                            return orderA - orderB;
                        });

                        // Roman numeral mapping
                        const romanNumerals = {
                            1: 'I',
                            2: 'II',
                            3: 'III',
                            4: 'IV',
                            5: 'V',
                            6: 'VI',
                        };

                        breakdown.forEach((row, index) => {
                            const orderNo = row.order_no ?? (index + 1);
                            const roman = romanNumerals[orderNo] || orderNo;
                            const catName = row.category || `Category ${index + 1}`;
                            const sc = parseFloat(row.score) || 0;
                            const maxPts = parseFloat(row.max_points) || 0;

                            totalScore += sc;
                            totalMax += maxPts;

                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                        <td>${roman}. ${catName}</td>
                                        <td>${sc.toFixed(2)}</td>
                                        <td>${maxPts.toFixed(2)}</td>
                                    `;
                            tbody.appendChild(tr);
                        });

                        const totalTr = document.createElement('tr');
                        totalTr.classList.add('summary-total-row');
                        totalTr.innerHTML = `
                                    <td><strong>Total Score</strong></td>
                                    <td><strong>${totalScore.toFixed(2)}</strong></td>
                                    <td><strong>${totalMax.toFixed(2)}</strong></td>
                                `;
                        tbody.appendChild(totalTr);
                    }
                });
            }

            function submitDecision(decisionKey) {
                const afrId = modalEl?.dataset.afrId || '';
                if (!afrId) {
                    alert('Missing review ID.');
                    return;
                }
                if (!decisionForm) return;

                const url = storeUrlTemplate.replace('__AFR__', afrId);
                decisionForm.action = url;
                decisionForm.querySelector('input[name="decision"]').value = decisionKey;
                decisionForm.submit();
            }

            if (approveBtn) {
                approveBtn.addEventListener('click', function () {
                    submitDecision('approved');
                });
            }

            if (notQualBtn) {
                notQualBtn.addEventListener('click', function () {
                    submitDecision('not_qualified');
                });
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        /* ------------------------------
                   Admin Final Review – override global .modal rules safely
                ------------------------------ */

        /* Only this modal: center it instead of bottom-sheet style.css behavior */
        .admin-final-modal.modal {
            align-items: center !important;
            justify-content: center !important;
        }

        .admin-final-modal .modal-dialog {
            max-width: 1200px !important;
            width: 95vw !important;
            margin: 1.5rem auto !important;
        }

        .admin-final-modal .modal-content {
            border-radius: 12px !important;
            max-height: 85vh;
        }

        .admin-final-modal .modal-body {
            max-height: calc(85vh - 140px);
            overflow-y: auto;
        }

        /* Final Review Specific Styles */
        .page-header h1 {
            color: #7E0308;
            font-size: 2rem;
            font-weight: 700;
        }

        body.dark-mode .page-header h1 {
            color: #F9BD3D;
        }

        /* Filter group width adjustments */
        .filter-group select {
            width: 200px;
            max-width: 200px;
            min-width: 150px;
        }

        /* Search input width */
        .search-group .form-control {
            max-width: 400px;
        }

        /* Action buttons group */
        .action-buttons-group {
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.35rem 0.6rem;
            border-radius: 6px;
            border: none;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-view {
            background: #7E0308;
            color: #fff;
        }

        .btn-view:hover {
            background: #5a0206;
            color: #fff;
            transform: translateY(-1px);
        }

        .decision-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-pending {
            background: rgba(108, 117, 125, 0.15);
            color: #6c757d;
        }

        .badge-approved {
            background: rgba(40, 167, 69, 0.15);
            color: #198754;
        }

        .badge-not-qualified {
            background: rgba(220, 53, 69, 0.15);
            color: #b21f2d;
        }

        /* ---- Header & score pill ---- */
        .student-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .summary-score-pill {
            background: #8B0000;
            color: #fff;
            border-radius: 999px;
            padding: 0.5rem 1.2rem;
            text-align: right;
        }

        .summary-score-pill .label {
            display: block;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            opacity: 0.8;
        }

        .summary-score-pill .value {
            font-size: 1.3rem;
            font-weight: 700;
        }

        /* ---- Student info grid ---- */
        .admin-student-info-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem 1rem;
        }

        .admin-student-info-grid .info-block {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 0.55rem 0.75rem;
        }

        body.dark-mode .admin-student-info-grid .info-block {
            background: #343a40;
        }

        .info-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #666;
            margin-bottom: 0.1rem;
        }

        .info-value {
            font-size: 0.95rem;
            font-weight: 600;
        }

        /* ---- Category table ---- */
        .section-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .summary-table th,
        .summary-table td {
            padding: 10px 12px;
            font-size: 0.95rem;
            border-color: #ddd;
        }

        .summary-total-row td {
            border-top: 2px solid #bbb;
            font-weight: 700;
        }

        /* ---- Buttons in footer ---- */
        .modal-footer {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0;
            padding: 1rem 1.5rem;
            border-top: 1px solid #dee2e6;
        }

        .decision-buttons-group {
            display: flex;
            flex-direction: row;
            gap: 1rem;
            align-items: center;
            justify-content: center;
            width: 100%;
            flex-wrap: nowrap;
        }

        .admin-decision-btn {
            min-width: 160px;
            max-width: 200px;
            flex: 0 1 auto;
            padding: 0.6rem 1.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            white-space: nowrap;
            text-align: center;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .admin-decision-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .admin-decision-btn.btn-success {
            background-color: #198754;
            border-color: #198754;
            color: white;
        }

        .admin-decision-btn.btn-success:hover {
            background-color: #157347;
            border-color: #146c43;
        }

        .admin-decision-btn.btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }

        .admin-decision-btn.btn-danger:hover {
            background-color: #bb2d3b;
            border-color: #b02a37;
        }

        @media (max-width: 768px) {
            .modal-footer {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
            }

            .decision-buttons-group {
                flex-direction: row;
                width: 100%;
                justify-content: space-between;
                gap: 1rem;
            }

            .admin-decision-btn {
                flex: 1;
                min-width: 0;
            }
        }

        @media (max-width: 768px) {
            .admin-student-info-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .student-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .summary-score-pill {
                align-self: flex-end;
            }

            /* Close button styling for modals - matching system style */
            .admin-final-modal .btn-close-modal {
                background: #dc3545 !important;
                color: white !important;
                border: none !important;
                border-radius: 4px !important;
                width: 32px !important;
                height: 32px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                font-size: 16px !important;
                cursor: pointer !important;
                transition: all 0.2s ease !important;
                opacity: 1 !important;
                padding: 0 !important;
                background-image: none !important;
                position: relative !important;
                z-index: 1 !important;
            }

            /* Hide Bootstrap's default btn-close if it appears */
            .admin-final-modal .btn-close:not(.btn-close-modal) {
                display: none !important;
            }

            .admin-final-modal .btn-close-modal:hover {
                background: #c82333 !important;
                transform: translateY(-1px) !important;
                box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3) !important;
                opacity: 1 !important;
            }

            .admin-final-modal .btn-close-modal:focus {
                outline: none !important;
                box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
            }

            .admin-final-modal .btn-close-modal i {
                font-size: 14px !important;
                color: white !important;
            }

            body.dark-mode .admin-final-modal .btn-close-modal {
                background: #dc3545 !important;
            }

            body.dark-mode .admin-final-modal .btn-close-modal:hover {
                background: #c82333 !important;
            }
        }
    </style>
@endpush