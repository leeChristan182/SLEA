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
            <div class="controls-section">
                <div class="filter-controls">
                    <div class="filter-group">
                        <label for="statusFilter">Filter by Status</label>
                        <select id="statusFilter" class="form-select">
                            <option value="">All</option>
                            <option value="draft">Draft</option>
                            <option value="queued_for_admin">Queued for Admin</option>
                            <option value="finalized">Finalized</option>
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
                        <input type="text" id="searchInput" class="form-control" placeholder="Search submissions...">
                        <button type="button" id="searchBtn" class="btn-search-maroon search-btn-attached" title="Search"
                            onclick="handleSearchClick(event)">
                            <i class="fas fa-search"></i>
                        </button>
                        <button type="button" id="clearBtn" class="btn-clear" title="Clear search"
                            onclick="handleClearClick(event)">
                            Clear
                        </button>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="submissions-table-container">
                <table class="table submissions-table" id="finalReviewTable">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>College</th>
                            <th>Program</th>
                            <th>Total Score</th>
                            <th>Status</th>
                            <th>Action</th>
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
                                <td class="score-cell">{{ number_format($item->total_score ?? 0, 2) }}</td>
                                <td>
                                    <span class="status-badge {{ 'status-' . $statusKey }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons-group">
                                        <button type="button" class="btn btn-submit-admin" data-bs-toggle="modal"
                                            data-bs-target="#viewSummaryModal" data-student-id="{{ $student->id ?? '' }}"
                                            data-student-number="{{ $studentNumber }}" data-student-name="{{ $studentName }}"
                                            data-program="{{ $programName }}" data-college="{{ $collegeName }}"
                                            data-major="{{ $majorName }}"
                                            data-score="{{ number_format($item->total_score ?? 0, 2) }}"
                                            data-total-max="{{ $item->max_possible ?? $breakdown->sum('max_points') }}"
                                            data-status="{{ $statusLabel }}" data-breakdown='@json($breakdown)'
                                            title="Submit to Admin">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                        <button type="button" class="btn btn-reject-final"
                                            data-student-id="{{ $student->id ?? '' }}" data-student-name="{{ $studentName }}"
                                            title="Reject">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    </div>
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

            {{-- Pagination --}}
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
        </main>
    </div>

    {{-- Hidden form for submit --}}
    <form id="finalReviewSubmitForm" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="action" value="submit">
        <input type="hidden" name="remarks" value="">
    </form>
    <form id="finalReviewRejectForm" method="POST" class="d-none">
        @csrf
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
                        Submit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="{{ asset('css/pending-submissions.css') }}">
@endsection

@push('scripts')
    @php
        $storeUrlTemplate = route('assessor.final-review.store', ['student' => '__STUDENT__']);
        $rejectUrlTemplate = route('assessor.final-review.reject', ['student' => '__STUDENT__']);

    @endphp
    <script>
        // Global functions for search and clear buttons - will be updated after DOM loads
        window.applyFiltersFunction = null;

        window.handleSearchClick = function (event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            console.log('handleSearchClick called');
            // Try to call applyFilters if available
            if (typeof window.applyFiltersFunction === 'function') {
                window.applyFiltersFunction();
            } else {
                // Fallback: trigger input event which will trigger the listener
                const searchInput = document.getElementById('searchInput');
                if (searchInput) {
                    console.log('Dispatching input event');
                    const searchEvent = new Event('input', { bubbles: true });
                    searchInput.dispatchEvent(searchEvent);
                }
            }
        };

        window.handleClearClick = function (event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            console.log('handleClearClick called');
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.value = '';
                // Try to call applyFilters if available
                if (typeof window.applyFiltersFunction === 'function') {
                    window.applyFiltersFunction();
                } else {
                    // Fallback: trigger input event which will trigger the listener
                    console.log('Dispatching input event for clear');
                    const searchEvent = new Event('input', { bubbles: true });
                    searchInput.dispatchEvent(searchEvent);
                }
                searchInput.focus();
            }
        };

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

            const storeUrlTemplate = '{{ $storeUrlTemplate }}';
            const rejectUrlTemplate = '{{ $rejectUrlTemplate }}';

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

            // Make applyFilters available globally for onclick handlers
            applyFiltersFunction = applyFilters;

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

            // Update global function reference for onclick handlers
            window.applyFiltersFunction = applyFilters;

            // Search button functionality
            const searchBtn = document.getElementById('searchBtn');
            if (searchBtn) {
                searchBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Search button clicked (event listener)');
                    applyFilters();
                });
            } else {
                console.error('Search button not found');
            }

            // Clear button functionality
            const clearBtn = document.getElementById('clearBtn');
            if (clearBtn) {
                clearBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Clear button clicked (event listener)');
                    if (searchInput) {
                        searchInput.value = '';
                        applyFilters();
                        searchInput.focus();
                    }
                });
            } else {
                console.error('Clear button not found');
            }

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
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Error',
                            text: 'Missing student ID for this review.',
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    } else {
                        alert('Missing student ID for this review.');
                    }
                    return;
                }

                if (!goodConductCheck.checked) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Confirmation Required',
                            text: 'Please confirm good conduct before submitting to Admin.',
                            icon: 'warning',
                            confirmButtonColor: '#198754'
                        });
                    } else {
                        alert('Please confirm good conduct before submitting to Admin.');
                    }
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
                    // Use SweetAlert2 instead of native confirm
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Submit Final Review',
                            text: 'Submit this student\'s final review to Admin?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#198754',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, submit',
                            cancelButtonText: 'Cancel',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                submitReview();
                            }
                        });
                    } else {
                        // Fallback to native confirm if SweetAlert2 is not loaded
                        if (confirm('Submit this student\'s final review to Admin?')) {
                            submitReview();
                        }
                    }
                });
            }

            // Handle reject button clicks
            // Handle reject button clicks
            const rejectButtons = document.querySelectorAll('.btn-reject-final');
            const rejectForm = document.getElementById('finalReviewRejectForm');

            if (rejectButtons && rejectForm) {
                rejectButtons.forEach(button => {
                    button.addEventListener('click', function () {
                        const studentId = this.getAttribute('data-student-id');
                        const studentName = this.getAttribute('data-student-name') || 'this student';

                        if (!studentId) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Missing student ID for this action.',
                                    icon: 'error',
                                    confirmButtonColor: '#dc3545',
                                });
                            } else {
                                alert('Missing student ID for this action.');
                            }
                            return;
                        }

                        const doSubmitReject = () => {
                            const url = rejectUrlTemplate.replace('__STUDENT__', studentId);

                            // copy remarks from the modal textarea (same as submit)
                            const remarksHidden = rejectForm.querySelector('input[name="remarks"]');
                            if (remarksHidden && remarksInput) {
                                remarksHidden.value = remarksInput.value;
                            }

                            rejectForm.action = url;
                            rejectForm.submit();
                        };

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Reject Student?',
                                text: `Are you sure you want to reject ${studentName}? This will mark the student as not qualified and remove them from your final review list.`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#dc3545',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: 'Yes, reject',
                                cancelButtonText: 'Cancel',
                                reverseButtons: true,
                            }).then(result => {
                                if (result.isConfirmed) {
                                    doSubmitReject();
                                }
                            });
                        } else {
                            if (confirm(`Are you sure you want to reject ${studentName}?`)) {
                                doSubmitReject();
                            }
                        }
                    });
                });
            }

            // Initialize pagination
            if (typeof initializeAdminPagination !== 'undefined') {
                initializeAdminPagination('#finalReviewTable', 10);
            }
        });
    </script>
    <script src="{{ asset('js/admin_pagination.js') }}"></script>
@endpush

@push('styles')
    <style>
        /* ============================================
                           FINAL REVIEW TABLE - CLEAN REBUILD
                           ============================================ */

        /* Page Header */
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

        body.dark-mode .filter-group label {
            color: #ddd;
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
            max-width: 500px;
        }

        .search-group {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .search-group input {
            flex: 1;
            padding: 0.5rem 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        /* Search button - maroon with icon */
        .btn-search-maroon {
            background-color: #7E0308;
            color: white;
            border: 1px solid #7E0308;
            border-radius: 6px;
            padding: 0;
            min-width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 16px;
            line-height: 1;
            pointer-events: auto;
            z-index: 10;
            position: relative;
        }

        .btn-search-maroon:hover {
            background-color: #5a0206;
            border-color: #5a0206;
        }

        .btn-search-maroon:active {
            background-color: #4a0105;
            border-color: #4a0105;
        }

        .btn-search-maroon i {
            font-size: 16px;
            line-height: 1;
        }

        /* Clear button */
        .btn-clear {
            background-color: #6c757d;
            color: white;
            border: 1px solid #6c757d;
            border-radius: 6px;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
            height: 38px;
            white-space: nowrap;
            pointer-events: auto;
            z-index: 10;
            position: relative;
        }

        .btn-clear:hover {
            background-color: #5a6268;
            border-color: #5a6268;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
        }

        .btn-clear:active {
            background-color: #545b62;
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(108, 117, 125, 0.2);
        }

        body.dark-mode .btn-clear {
            background-color: #495057;
            border-color: #495057;
        }

        body.dark-mode .btn-clear:hover {
            background-color: #3d4146;
            border-color: #3d4146;
        }

        /* Main content centering - match submissions page */
        .main-content {
            max-width: 100%;
            overflow-x: hidden;
            box-sizing: border-box;
        }

        body {
            overflow-x: hidden !important;
        }

        .container {
            overflow-x: hidden !important;
            max-width: 100vw;
            box-sizing: border-box;
        }

        /* Table styling to match other tables */
        .submissions-table-container {
            background: white;
            border-radius: 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow-x: hidden;
            overflow-y: visible;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        body.dark-mode .submissions-table-container {
            background: #2b2b2b;
        }

        .submissions-table {
            margin: 0;
            width: 100% !important;
            max-width: 100% !important;
            background: white;
            table-layout: auto !important;
            border-collapse: collapse;
        }

        body.dark-mode .submissions-table {
            background: #2b2b2b;
        }

        .submissions-table thead {
            background-color: #8B0000 !important;
        }

        .submissions-table thead th {
            padding: 0.7rem 0.75rem;
            font-weight: 600;
            color: white !important;
            border-bottom: 1px solid white !important;
            border-right: 1px solid rgba(255, 255, 255, 0.2) !important;
            font-size: 0.9rem;
            background-color: #8B0000 !important;
            text-align: left;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .submissions-table thead th:nth-child(7) {
            text-align: center;
            font-size: 0.85rem;
            white-space: nowrap;
            padding: 0.7rem 0.25rem;
        }

        .submissions-table thead th:last-child {
            border-right: none !important;
        }

        .submissions-table tbody td {
            padding: 0.65rem 0.5rem;
            font-size: 0.85rem;
            color: #333;
            border-bottom: 1px solid #e9ecef;
            border-right: 1px solid #e9ecef;
            vertical-align: middle;
            max-width: 0;
        }

        .submissions-table tbody td:last-child {
            border-right: none;
        }

        .submissions-table tbody tr {
            height: auto;
            min-height: 45px;
        }

        /* Ensure Student ID displays properly in one line */
        .submissions-table tbody td.student-id-cell {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            font-family: 'Courier New', monospace;
            font-weight: 500;
        }

        body.dark-mode .submissions-table tbody td {
            color: #f0f0f0;
            border-bottom-color: #444;
        }

        .submissions-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        body.dark-mode .submissions-table tbody tr:hover {
            background-color: #333;
        }

        /* Column widths - optimized for compact Action column */
        .submissions-table thead th:nth-child(1),
        .submissions-table tbody td:nth-child(1) {
            width: 13% !important;
        }

        /* Student ID - increased from 10% */

        .submissions-table thead th:nth-child(2),
        .submissions-table tbody td:nth-child(2) {
            width: 17% !important;
        }

        /* Student Name - increased from 14% */

        .submissions-table thead th:nth-child(3),
        .submissions-table tbody td:nth-child(3) {
            width: 16% !important;
        }

        /* College */

        .submissions-table thead th:nth-child(4),
        .submissions-table tbody td:nth-child(4) {
            width: 18% !important;
        }

        /* Program */

        .submissions-table thead th:nth-child(5),
        .submissions-table tbody td:nth-child(5) {
            width: 13% !important;
        }

        /* Total Score - increased from 12% */

        .submissions-table thead th:nth-child(6),
        .submissions-table tbody td:nth-child(6) {
            width: 14% !important;
        }

        /* Status */

        .submissions-table thead th:nth-child(7),
        .submissions-table tbody td:nth-child(7) {
            width: 9% !important;
        }

        /* Action - reduced from 16% to 9% */

        /* Student ID - single line, no wrapping, increased width */
        .submissions-table tbody td:nth-child(1) {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 0.65rem 0.75rem;
        }

        /* Student Name - allow wrapping if needed, increased width */
        .submissions-table tbody td:nth-child(2) {
            white-space: normal;
            overflow: hidden;
            text-overflow: clip;
            word-wrap: break-word;
            overflow-wrap: break-word;
            line-height: 1.4;
            hyphens: auto;
            padding: 0.65rem 0.75rem;
        }

        /* College - allow wrapping if needed */
        .submissions-table tbody td:nth-child(3) {
            white-space: normal;
            overflow: hidden;
            text-overflow: clip;
            word-wrap: break-word;
            overflow-wrap: break-word;
            line-height: 1.4;
        }

        /* Program - allow wrapping if needed */
        .submissions-table tbody td:nth-child(4) {
            white-space: normal;
            overflow: hidden;
            text-overflow: clip;
            word-wrap: break-word;
            overflow-wrap: break-word;
            line-height: 1.4;
            hyphens: auto;
        }

        /* Total Score - single line, increased width */
        .submissions-table tbody td:nth-child(5) {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: clip;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.65rem 0.75rem;
        }

        /* Status - allow wrapping for badge */
        .submissions-table tbody td:nth-child(6) {
            white-space: normal;
            overflow: hidden;
            text-overflow: clip;
        }

        /* Action - single line, centered, compact */
        .submissions-table tbody td:nth-child(7) {
            white-space: nowrap !important;
            text-align: center !important;
            padding: 0.5rem 0.25rem !important;
            width: 9% !important;
        }

        /* Pagination styles */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
            padding: 1rem 0;
        }

        .pagination-info {
            color: #666;
            font-size: 0.9rem;
        }

        body.dark-mode .pagination-info {
            color: #ccc;
        }

        .unified-pagination {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-nav {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #333;
            font-size: 0.9rem;
        }

        .btn-nav:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-nav:hover:not(:disabled) {
            background: #7E0308;
            color: white;
            border-color: #7E0308;
        }

        body.dark-mode .btn-nav {
            background: #2a2a2a;
            border-color: #555;
            color: #f0f0f0;
        }

        body.dark-mode .btn-nav:hover:not(:disabled) {
            background: #7E0308;
            color: white;
            border-color: #7E0308;
        }

        .pagination-pages {
            display: flex;
            gap: 0.25rem;
        }

        .page-btn {
            padding: 0.5rem 0.75rem;
            border: 1px solid #ddd;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #333;
            font-size: 0.9rem;
            min-width: 36px;
            text-align: center;
        }

        .page-btn.active {
            background: #7E0308;
            color: white;
            border-color: #7E0308;
        }

        .page-btn:hover:not(.active) {
            background: #7E0308;
            color: white;
            border-color: #7E0308;
        }

        body.dark-mode .page-btn {
            background: #2a2a2a;
            border-color: #555;
            color: #f0f0f0;
        }

        body.dark-mode .page-btn.active {
            background: #7E0308;
            color: white;
            border-color: #7E0308;
        }

        body.dark-mode .page-btn:hover:not(.active) {
            background: #7E0308;
            color: white;
            border-color: #7E0308;
        }

        /* Action buttons - compact icon-only buttons */
        .action-buttons-group {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            flex-wrap: nowrap;
        }

        .btn-submit-admin,
        .btn-reject-final {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            width: 26px !important;
            height: 26px !important;
            min-width: 26px !important;
            min-height: 26px !important;
            max-width: 26px !important;
            max-height: 26px !important;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            padding: 0 !important;
            flex-shrink: 0;
            position: relative;
        }

        .btn-submit-admin i,
        .btn-reject-final i {
            font-size: 0.8rem;
            line-height: 1;
        }

        .btn-submit-admin {
            background-color: #28a745;
            color: white;
        }

        .btn-submit-admin:hover {
            background-color: #218838;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
        }

        .btn-submit-admin:active {
            background-color: #1e7e34;
            transform: translateY(0);
        }

        .btn-submit-admin:focus {
            outline: 2px solid rgba(40, 167, 69, 0.5);
            outline-offset: 2px;
        }

        .btn-reject-final {
            background-color: #dc3545;
            color: white;
        }

        .btn-reject-final:hover {
            background-color: #c82333;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
        }

        .btn-reject-final:active {
            background-color: #bd2130;
            transform: translateY(0);
        }

        .btn-reject-final:focus {
            outline: 2px solid rgba(220, 53, 69, 0.5);
            outline-offset: 2px;
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
@endpush