@extends('layouts.app')

@section('title', 'Criteria and Points System')

@section('content')
@php
    /** @var \Illuminate\Support\Collection|\App\Models\RubricCategory[] $categories */
@endphp

<div class="container" style="margin-top: 0 !important;">
    @include('partials.sidebar')

    <main class="main-content" style="padding-top: 100px !important;">
        <div class="criteria-guide-container">
            {{-- Page Header --}}
            <div class="page-header">
                <h1>Criteria and Points System</h1>
            </div>

            @php
                // Build page titles - exactly 5 pages, one for each category
                $pageTitles = [
                    1 => 'I. LEADERSHIP EXCELLENCE',
                    2 => 'II. ACADEMIC EXCELLENCE',
                    3 => 'III. AWARDS/RECOGNITION RECEIVED',
                    4 => 'IV. COMMUNITY INVOLVEMENT',
                    5 => 'V. GOOD CONDUCT',
                ];
                $totalPages = 5;
                $initialPage = request()->get('page', 1);
            @endphp

            <div class="criteria-main-container" x-data="criteriaPager(@json($pageTitles), {{ $initialPage }})">
                {{-- Pages --}}
                <div class="criteria-pages">
                    {{-- Page 1: Leadership Excellence --}}
                    <section x-show="page === 1" x-cloak>
                        @include('student.criteria.sections.leadership', [
                            'categories' => $categories,
                            'leadershipSections' => null,
                        ])
                    </section>

                    {{-- Page 2: Academic Excellence --}}
                    <section x-show="page === 2" x-cloak>
                        @include('student.criteria.sections.academic', ['categories' => $categories])
                    </section>

                    {{-- Page 3: Awards/Recognition Received --}}
                    <section x-show="page === 3" x-cloak>
                        @include('student.criteria.sections.awards', ['categories' => $categories])
                    </section>

                    {{-- Page 4: Community Involvement --}}
                    <section x-show="page === 4" x-cloak>
                        @include('student.criteria.sections.community', ['categories' => $categories])
                    </section>

                    {{-- Page 5: Good Conduct --}}
                    <section x-show="page === 5" x-cloak>
                        @include('student.criteria.sections.conduct', ['categories' => $categories])
                    </section>
                </div>

                {{-- Pagination Controls --}}
                <div class="criteria-pagination">
                    <button @click="prev()" :disabled="page === 1" class="btn-prev">
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <div class="page-info">
                        <span class="current-page-label" x-text="pageTitle"></span>
                        <span class="page-numbers" x-text="`Page ${page} of ${maxPage}`"></span>
                    </div>
                    <button @click="next()" :disabled="page === maxPage" class="btn-next">
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection

@push('styles')
<style>
    /* Main Container */
    .criteria-guide-container {
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    body.dark-mode .criteria-guide-container {
        background-color: #2a2a2a;
        color: #f0f0f0;
    }

    /* Page Header */
    .page-header {
        margin-bottom: 32px;
        padding-bottom: 0;
    }

    .page-header h1 {
        font-size: 28px;
        font-weight: 700;
        color: #7b0000 !important;
        margin: 0;
        padding: 0;
    }

    body.dark-mode .page-header h1 {
        color: #f9bd3d !important;
    }

    /* Criteria Main Container */
    .criteria-main-container {
        position: relative;
    }

    /* Criteria Pages */
    .criteria-pages {
        min-height: 400px;
    }

    .criteria-pages section {
        display: block;
    }

    /* Rubric Section */
    .rubric-section {
        margin-bottom: 0;
    }

    .rubric-heading {
        font-size: 20px;
        font-weight: 700;
        color: #7b0000;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid #7b0000;
    }

    body.dark-mode .rubric-heading {
        color: #f9bd3d;
        border-bottom-color: #f9bd3d;
    }

    .rubric-category-description {
        font-size: 14px;
        line-height: 1.6;
        color: #333;
        margin-bottom: 20px;
    }

    body.dark-mode .rubric-category-description {
        color: #e0e0e0;
    }

    /* Table Styling - shadcn/ui style */
    .criteria-guide-container .table-wrap {
        overflow-x: auto !important;
        margin-bottom: 20px !important;
        border-radius: 0 !important;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
    }

    body.dark-mode .criteria-guide-container .table-wrap {
        background: transparent !important;
        border: none !important;
    }

    .criteria-guide-container .guide-table {
        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
        border-collapse: collapse !important;
        border-radius: 0 !important;
        font-size: 14px !important;
        background: transparent !important;
        border: none !important;
        border-spacing: 0 !important;
        table-layout: auto !important; /* Allow flexible column widths */
    }

    .criteria-guide-container .guide-table thead {
        background: transparent !important;
    }

    .criteria-guide-container .guide-table thead tr {
        background: transparent !important;
    }

    .criteria-guide-container .guide-table th {
        padding: 12px 16px !important;
        text-align: left !important;
        font-weight: 500 !important;
        font-size: 14px !important;
        color: #6b7280 !important;
        background: transparent !important;
        border: none !important;
        border-bottom: 1px solid #e5e7eb !important;
        vertical-align: middle !important;
        width: auto !important;
        min-width: auto !important;
        max-width: none !important;
    }

    body.dark-mode .criteria-guide-container .guide-table th {
        color: #9ca3af !important;
        border-bottom-color: #374151 !important;
        background: transparent !important;
    }

    .criteria-guide-container .guide-table tbody {
        background: transparent !important;
    }

    .criteria-guide-container .guide-table tbody tr {
        background: transparent !important;
    }

    .criteria-guide-container .guide-table td {
        padding: 12px 16px !important;
        border: none !important;
        border-bottom: 1px solid #e5e7eb !important;
        font-size: 14px !important;
        vertical-align: top !important;
        color: #111827 !important;
        background: transparent !important;
        width: auto !important;
        min-width: auto !important;
        max-width: none !important;
        white-space: normal !important;
        word-wrap: break-word !important;
    }

    body.dark-mode .criteria-guide-container .guide-table td {
        border-bottom-color: #374151 !important;
        color: #f9fafb !important;
        background: transparent !important;
    }

    .criteria-guide-container .guide-table tbody tr:last-child td {
        border-bottom: none !important;
    }

    .criteria-guide-container .guide-table tbody tr:hover {
        background-color: transparent !important;
    }

    .criteria-guide-container .guide-table tbody tr:nth-child(even) {
        background-color: transparent !important;
    }

    /* Evidence and Notes Content */
    .evidence-notes-content {
        line-height: 1.6;
    }

    /* Subsection Title */
    .subsection-title {
        font-size: 18px;
        font-weight: 700 !important;
        color: #7b0000;
        margin-bottom: 12px;
        margin-top: 20px;
    }

    .subsection-title strong {
        font-weight: 700 !important;
    }

    body.dark-mode .subsection-title {
        color: #f9bd3d;
    }

    /* Merged Evidence Cell - Remove borders between merged rows */
    .criteria-guide-container .merged-evidence-cell {
        border-bottom: none !important;
        border-top: none !important;
    }

    .criteria-guide-container .guide-table tbody tr:not(:last-child) .merged-evidence-cell {
        border-bottom: none !important;
    }

    .criteria-guide-container .guide-table tbody tr:not(:first-child) .merged-evidence-cell {
        border-top: none !important;
    }

    /* Remove all internal borders for merged cells */
    .criteria-guide-container .guide-table tbody tr .merged-evidence-cell {
        border: none !important;
    }

    /* Only show border on the outer edges */
    .criteria-guide-container .guide-table tbody tr:first-child .merged-evidence-cell {
        border-top: 1px solid #e5e7eb !important;
    }

    body.dark-mode .criteria-guide-container .guide-table tbody tr:first-child .merged-evidence-cell {
        border-top-color: #374151 !important;
    }

    .criteria-guide-container .guide-table tbody tr:last-child .merged-evidence-cell {
        border-bottom: 1px solid #e5e7eb !important;
    }

    body.dark-mode .criteria-guide-container .guide-table tbody tr:last-child .merged-evidence-cell {
        border-bottom-color: #374151 !important;
    }

    /* Points Display */
    .criteria-guide-container .points-cell {
        font-weight: 500 !important;
        color: #111827 !important;
        width: auto !important;
        min-width: auto !important;
        max-width: none !important;
    }

    body.dark-mode .criteria-guide-container .points-cell {
        color: #f9fafb !important;
    }

    /* Subsection Cell */
    .criteria-guide-container .subsection-cell {
        font-weight: 600 !important;
        vertical-align: middle !important;
        background-color: #f9fafb !important;
    }

    body.dark-mode .criteria-guide-container .subsection-cell {
        background-color: #1f2937 !important;
    }


    /* For tables with subsection column (4 columns) */
    .criteria-guide-container .guide-table.has-subsection-column th:nth-child(1),
    .criteria-guide-container .guide-table.has-subsection-column td:nth-child(1) {
        text-align: left !important; /* Subsection column */
    }

    .criteria-guide-container .guide-table.has-subsection-column th:nth-child(2),
    .criteria-guide-container .guide-table.has-subsection-column td:nth-child(2) {
        text-align: left !important; /* Criteria column */
    }

    .criteria-guide-container .guide-table.has-subsection-column th:nth-child(3),
    .criteria-guide-container .guide-table.has-subsection-column td:nth-child(3) {
        text-align: right !important; /* Points column */
    }

    .criteria-guide-container .guide-table.has-subsection-column th:nth-child(4),
    .criteria-guide-container .guide-table.has-subsection-column td:nth-child(4) {
        text-align: left !important; /* Evidence column */
    }

    /* For tables without subsection column (3 columns total) */
    .criteria-guide-container .guide-table:not(.has-subsection-column) th:nth-child(1),
    .criteria-guide-container .guide-table:not(.has-subsection-column) td:nth-child(1) {
        text-align: left !important; /* Criteria column */
    }

    .criteria-guide-container .guide-table:not(.has-subsection-column) th:nth-child(2),
    .criteria-guide-container .guide-table:not(.has-subsection-column) td:nth-child(2) {
        text-align: right !important; /* Points column */
    }

    .criteria-guide-container .guide-table:not(.has-subsection-column) th:nth-child(3),
    .criteria-guide-container .guide-table:not(.has-subsection-column) td:nth-child(3) {
        text-align: left !important; /* Evidence column */
    }

    /* Ensure table columns can expand/contract - no fixed widths */
    .criteria-guide-container .guide-table colgroup,
    .criteria-guide-container .guide-table col {
        width: auto !important;
        min-width: auto !important;
        max-width: none !important;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .criteria-guide-container {
            padding: 15px;
        }

        .page-header h1 {
            font-size: 24px;
        }

        .rubric-heading {
            font-size: 18px;
        }

        .guide-table {
            font-size: 12px;
        }

        .guide-table th,
        .guide-table td {
            padding: 8px 12px;
        }
    }

    /* Pagination Controls */
    .criteria-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 40px;
        padding: 20px;
        background: #f9fafb;
        border-radius: 8px;
        gap: 20px;
    }

    body.dark-mode .criteria-pagination {
        background: #1f2937;
    }

    .criteria-pagination .btn-prev,
    .criteria-pagination .btn-next {
        padding: 10px 20px;
        background: #7b0000;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .criteria-pagination .btn-prev:hover:not(:disabled),
    .criteria-pagination .btn-next:hover:not(:disabled) {
        background: #5a0000;
        transform: translateY(-1px);
    }

    .criteria-pagination .btn-prev:disabled,
    .criteria-pagination .btn-next:disabled {
        background: #ccc;
        cursor: not-allowed;
        opacity: 0.5;
    }

    body.dark-mode .criteria-pagination .btn-prev,
    body.dark-mode .criteria-pagination .btn-next {
        background: #7b0000;
    }

    body.dark-mode .criteria-pagination .btn-prev:disabled,
    body.dark-mode .criteria-pagination .btn-next:disabled {
        background: #555;
    }

    .criteria-pagination .page-info {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }

    .criteria-pagination .current-page-label {
        font-size: 20px;
        font-weight: 700;
        color: #7b0000;
    }

    body.dark-mode .criteria-pagination .current-page-label {
        color: #f9bd3d;
    }

    .criteria-pagination .page-numbers {
        font-size: 14px;
        color: #6b7280;
    }

    body.dark-mode .criteria-pagination .page-numbers {
        color: #9ca3af;
    }
</style>
@endpush

@push('scripts')
<script>
    function criteriaPager(pageTitles, initialPage = 1) {
        return {
            page: initialPage || 1,
            titles: pageTitles || {},
            get maxPage() {
                return Object.keys(this.titles).length;
            },
            get pageTitle() {
                return this.titles[this.page] || '';
            },
            setPage(n) {
                if (n >= 1 && n <= this.maxPage) {
                    this.page = n;
                    // Update URL without reload
                    const url = new URL(window.location);
                    url.searchParams.set('page', n);
                    window.history.pushState({}, '', url);
                    // Scroll to top of content whenever page changes
                    const el = document.querySelector('.criteria-main-container');
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            },
            next() {
                if (this.page < this.maxPage) this.setPage(this.page + 1);
            },
            prev() {
                if (this.page > 1) this.setPage(this.page - 1);
            },
        };
    }
</script>
@endpush

