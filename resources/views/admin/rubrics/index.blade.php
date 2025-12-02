@extends('layouts.app')

@section('title', 'Scoring Rubric Configuration')

@section('content')
@php
    /** @var \Illuminate\Support\Collection|\App\Models\RubricCategory[] $categories */

    // Build page titles - exactly 5 pages, one for each category
    $pageTitles = [
        1 => 'I. LEADERSHIP EXCELLENCE',
        2 => 'II. ACADEMIC EXCELLENCE',
        3 => 'III. AWARDS/RECOGNITION RECEIVED',
        4 => 'IV. COMMUNITY INVOLVEMENT',
        5 => 'V. GOOD CONDUCT',
    ];

    $totalPages = 5;
@endphp

<div class="container" style="margin-top: 0 !important;">
    @include('partials.sidebar')

    <main class="main-content" style="padding-top: 0 !important; margin-top: 0 !important;">
        @php
            // Determine initial page (default to 1 since no filter)
            $initialPage = 1;
        @endphp

        <div class="rubric-main-container" x-data="rubricPager(@json($pageTitles), {{ $initialPage }})">

            {{-- Current rubric label (same style as student-side pages) --}}
            <div class="current-page-label">
                <span x-text="pageTitle"></span>
            </div>

            {{-- Pages --}}
            <div class="rubric-pages">

            {{-- Page 1: Leadership Excellence (all subsections A-D) --}}
            <section x-show="page === 1" x-cloak>
                @include('admin.rubrics.sections.leadership', [
                    'categories' => $categories,
                    'leadershipSections' => null, // null means show all sections
                ])
            </section>

            {{-- Page 2: Academic Excellence --}}
            <section x-show="page === 2" x-cloak>
                @include('admin.rubrics.sections.academic', ['categories' => $categories])
            </section>

            {{-- Page 3: Awards/Recognition Received --}}
            <section x-show="page === 3" x-cloak>
                @include('admin.rubrics.sections.awards', ['categories' => $categories])
            </section>

            {{-- Page 4: Community Involvement --}}
            <section x-show="page === 4" x-cloak>
                @include('admin.rubrics.sections.community', ['categories' => $categories])
            </section>

            {{-- Page 5: Good Conduct --}}
            <section x-show="page === 5" x-cloak>
                @include('admin.rubrics.sections.conduct', ['categories' => $categories])
            </section>
        </div>

        </div>
    </main>
</div>

{{-- Include Modals --}}
@include('admin.rubrics.partials.modals')

{{-- Include CSS for search button styling --}}
<link rel="stylesheet" href="{{ asset('css/pending-submissions.css') }}">
@endsection

@push('scripts')
<script>
    function rubricPager(pageTitles, initialPage = 1) {
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
                    // Scroll to top of content whenever page changes
                    const el = document.querySelector('.rubric-main-container');
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

<style>
    /* Layout shell – partials control table/frontend look */
    .container {
        margin-top: 0 !important;
    }

    body.dark-mode .container {
        background: #2a2a2a !important;
        color: #f0f0f0 !important;
    }

    .main-content {
        padding: 0 !important;
        margin-top: 0 !important;
        width: 100%;
        background: #fff !important;
        color: #212529 !important;
    }

    body.dark-mode .main-content {
        background: #2a2a2a !important;
        color: #f0f0f0 !important;
    }

    .rubric-main-container {
        width: 100%;
        margin-top: 0 !important;
        padding-top: 24px !important; /* 1 inch gap from header */
        padding: 24px 20px 20px 20px;
        background: transparent;
        color: inherit;
    }

    body.dark-mode .rubric-main-container {
        background: transparent !important;
        color: #f0f0f0 !important;
    }

    .current-page-label {
        font-weight: 700;
        font-size: 16px;
        color: #7b0000 !important; /* Explicit text color for light mode */
        padding: 8px 0;
        border-bottom: 2px solid #7b0000;
        margin-bottom: 16px;
        margin-top: 0 !important;
    }

    body.dark-mode .current-page-label {
        color: #f9bd3d !important;
        border-bottom-color: #f9bd3d;
    }

    /* Pager */

    .rubric-pager {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.4rem;
        margin-top: 1.25rem;
    }

    .pager-btn {
        border-radius: 4px;
        padding: 0.35rem 0.75rem;
        border: 1px solid #ccc;
        background-color: #fff !important;
        color: #212529 !important; /* Explicit text color for light mode */
        font-size: 0.85rem;
        cursor: pointer;
        min-width: 2.1rem;
    }

    .pager-btn:disabled {
        opacity: 0.6;
        cursor: default;
    }

    body.dark-mode .pager-btn:disabled {
        background-color: #262626 !important;
        color: #888 !important;
        border-color: #555 !important;
    }

    .pager-page.active {
        background-color: #8B0000;
        border-color: #8B0000;
        color: #fff;
        font-weight: 600;
    }

    .pager-nav {
        min-width: 3.2rem;
    }

    /* Dark mode */

    body.dark-mode .rubric-content {
        background-color: #2a2a2a;
        color: #f0f0f0;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.5);
    }

    body.dark-mode .rubric-main-title {
        color: #f9bd3d;
    }


    body.dark-mode .rubric-pager .pager-btn {
        background-color: #262626;
        border-color: #555;
        color: #eee;
    }

    body.dark-mode .pager-page.active {
        background-color: #f9bd3d;
        border-color: #f9bd3d;
        color: #2a2a2a;
    }

    /* Table Styling - Match other admin tables */
    .submissions-table-container {
        background: #fff !important;
        border-radius: 0;
        padding: 1rem 1.25rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        overflow-x: auto;
        margin-bottom: 20px;
    }

    .submissions-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin: 0;
        color: #212529 !important; /* Explicit text color for light mode */
    }

    .submissions-table thead {
        background: #7b0000;
    }

    .submissions-table thead th {
        background: #7b0000;
        color: #fff;
        font-weight: 600;
        padding: 15px 12px;
        text-align: left;
        border-right: 1px solid rgba(255, 255, 255, 0.2);
        border-bottom: 2px solid #fff;
        font-size: 14px;
    }

    .submissions-table thead th:last-child {
        border-right: none;
        text-align: center !important;
        vertical-align: middle !important;
        width: 120px;
        min-width: 120px;
        max-width: 120px;
    }

    .submissions-table tbody td {
        padding: 12px;
        border-right: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
        background: #fff !important;
        color: #212529 !important; /* Explicit text color for light mode */
        vertical-align: top;
        position: relative;
    }

    .submissions-table tbody td:not(:last-child) {
        vertical-align: top;
    }


    .submissions-table tbody tr:last-child td {
        border-bottom: none;
    }

    .submissions-table tbody tr:nth-child(even) td {
        background: #f8f9fa;
        color: #212529 !important; /* Explicit text color for light mode */
    }

    .submissions-table tbody tr:hover td {
        background: inherit !important;
        color: inherit !important;
    }

    /* Action buttons styling */
    .action-buttons-group {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        flex-wrap: nowrap !important;
    }

    .submissions-table tbody td:last-child,
    .manage-table tbody td:last-child {
        text-align: center !important;
        vertical-align: middle !important;
        padding: 12px 8px !important;
        width: 120px !important;
        min-width: 120px !important;
        max-width: 120px !important;
    }

    .submissions-table tbody td:last-child .action-buttons-group,
    .manage-table tbody td:last-child .action-buttons-group {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        width: 100% !important;
        margin: 0 auto !important;
        padding: 0 !important;
        height: auto !important;
        min-height: 36px !important;
    }

    .btn-edit,
    .btn-delete {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 36px !important;
        height: 36px !important;
        min-width: 36px !important;
        min-height: 36px !important;
        max-width: 36px !important;
        max-height: 36px !important;
        border: none !important;
        border-radius: 6px !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        font-size: 14px !important;
        flex-shrink: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        line-height: 1 !important;
    }

    .btn-edit {
        background: #ffc107;
        color: #000;
    }

    .btn-edit:hover {
        background: #ffb300;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(255, 193, 7, 0.3);
    }

    .btn-delete {
        background: #dc3545;
        color: #fff;
    }

    .btn-delete:hover {
        background: #bb2d3b;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
    }

    /* Dark mode support */
    body.dark-mode .submissions-table-container {
        background: #2b2b2b !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        color: #f0f0f0 !important;
    }

    body.dark-mode .submissions-table {
        color: #f0f0f0 !important;
    }

    body.dark-mode .submissions-table thead {
        background: #5c0000 !important;
    }

    body.dark-mode .submissions-table thead th {
        background: #5c0000 !important;
        border-color: rgba(255, 255, 255, 0.15) !important;
        color: #fff !important;
    }

    body.dark-mode .submissions-table tbody td {
        background: #3a3a3a !important;
        border-color: #555 !important;
        color: #f0f0f0 !important;
    }

    body.dark-mode .submissions-table tbody tr:nth-child(even) td {
        background: #333 !important;
        color: #f0f0f0 !important;
    }

    body.dark-mode .submissions-table tbody tr:hover td {
        background: inherit !important;
        color: inherit !important;
    }

    body.dark-mode .btn-edit {
        background: #ffc107;
        color: #000;
    }

    body.dark-mode .btn-edit:hover {
        background: #ffb300;
    }

    body.dark-mode .btn-delete {
        background: #dc3545;
    }

    body.dark-mode .btn-delete:hover {
        background: #bb2d3b;
    }

    /* Rubric section heading */
    .rubric-section {
        margin-bottom: 30px;
        text-align: left;
        display: flex;
        flex-direction: column;
        align-items: center;
        color: #212529 !important;
    }

    body.dark-mode .rubric-section {
        color: #f0f0f0 !important;
    }

    .rubric-heading {
        font-size: 20px;
        font-weight: 700;
        color: #7b0000 !important; /* Explicit text color for light mode */
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid #7b0000;
        text-align: left;
        width: 100%;
        max-width: 1200px;
    }

    body.dark-mode .rubric-heading {
        color: #f9bd3d !important;
        border-bottom-color: #f9bd3d;
    }

    .rubric-category-description {
        font-size: 14px;
        color: #666 !important; /* Explicit text color for light mode */
        margin-bottom: 16px;
        text-align: left;
        line-height: 1.6;
        width: 100%;
        max-width: 1200px;
    }

    body.dark-mode .rubric-category-description {
        color: #ccc !important;
    }

    /* Table container - centered */
    .rubric-section .submissions-table-container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Old design styling - subsection and table-wrap */
    .subsection {
        margin-bottom: 2rem;
        color: #212529 !important;
    }

    body.dark-mode .subsection {
        color: #f0f0f0 !important;
    }

    .subsection-title {
        font-size: 18px;
        font-weight: 600;
        color: #7b0000 !important; /* Explicit text color for light mode */
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 1px solid #dee2e6;
    }

    body.dark-mode .subsection-title {
        color: #f9bd3d !important;
        border-bottom-color: #555;
    }

    .table-wrap {
        margin-bottom: 20px;
        overflow-x: auto;
        background: transparent;
    }

    body.dark-mode .table-wrap {
        background: transparent !important;
    }

    .manage-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff !important;
        border-radius: 0;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
        color: #212529 !important; /* Explicit text color for light mode */
    }

    .manage-table thead {
        background: #7b0000;
    }

    .manage-table thead th {
        background: #7b0000;
        color: #fff;
        font-weight: 600;
        padding: 15px 12px;
        text-align: left;
        border-right: 1px solid rgba(255, 255, 255, 0.2);
        border-bottom: 2px solid #fff;
        font-size: 14px;
    }

    .manage-table thead th:last-child {
        border-right: none;
        text-align: center;
    }

    /* Points column - narrow width (3rd column in Leadership category only) */
    .rubric-section[data-category="leadership"] .manage-table thead th:nth-child(3),
    .rubric-section[data-category="leadership"] .manage-table tbody td:nth-child(3) {
        width: 100px;
        min-width: 100px;
        max-width: 100px;
        text-align: center;
        white-space: normal;
        word-wrap: break-word;
        word-break: break-word;
        padding: 12px 8px;
    }

    /* Max Points column - narrow width (3rd column in categories II-V) */
    .manage-table thead th:nth-child(3),
    .manage-table tbody td:nth-child(3) {
        width: 100px;
        min-width: 100px;
        max-width: 100px;
        text-align: center;
        white-space: normal;
        word-wrap: break-word;
        word-break: break-word;
        padding: 12px 8px;
    }

    .manage-table tbody td {
        padding: 12px;
        border-right: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
        background: #fff !important;
        color: #212529 !important; /* Explicit text color for light mode */
        vertical-align: top;
        position: relative;
    }

    .manage-table tbody td:not(:last-child) {
        vertical-align: top;
    }

    /* Notes column - left alignment (5th column) */
    .manage-table thead th:nth-child(5),
    .manage-table tbody td:nth-child(5) {
        text-align: left !important;
    }

    /* Evidence and Notes content styling - no bullets, line breaks with spacing */
    .evidence-notes-content {
        line-height: 1.6;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    /* Max Points column - narrow width (3rd column in categories II-V) */
    /* Exclude leadership category which uses Points column */
    .rubric-section:not([data-category="leadership"]) .manage-table thead th:nth-child(3),
    .rubric-section:not([data-category="leadership"]) .manage-table tbody td:nth-child(3) {
        width: 100px;
        min-width: 100px;
        max-width: 100px;
        text-align: center;
        white-space: normal;
        word-wrap: break-word;
        word-break: break-word;
        padding: 12px 8px;
    }

    /* Actions column - ensure proper alignment */
    .manage-table thead th:last-child {
        width: 120px;
        min-width: 120px;
        max-width: 120px;
        text-align: center;
        vertical-align: middle;
    }

    .manage-table tbody tr:last-child td {
        border-bottom: none;
    }

    .manage-table tbody tr:nth-child(even) td {
        background: #f8f9fa !important;
        color: #212529 !important; /* Explicit text color for light mode */
    }

    .manage-table tbody tr:hover td {
        background: inherit !important;
        color: inherit !important;
    }

    /* Dark mode for manage-table */
    body.dark-mode .manage-table {
        background: #2b2b2b !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        color: #f0f0f0 !important;
    }

    body.dark-mode .manage-table thead {
        background: #5c0000 !important;
    }

    body.dark-mode .manage-table thead th {
        background: #5c0000 !important;
        border-color: rgba(255, 255, 255, 0.15) !important;
        color: #fff !important;
    }

    body.dark-mode .manage-table tbody td {
        background: #3a3a3a !important;
        border-color: #555 !important;
        color: #f0f0f0 !important;
    }

    body.dark-mode .manage-table tbody tr:nth-child(even) td {
        background: #333 !important;
        color: #f0f0f0 !important;
    }

    body.dark-mode .manage-table tbody tr:hover td {
        background: inherit !important;
        color: inherit !important;
    }

    /* Dark mode for lists and text elements */
    body.dark-mode ul,
    body.dark-mode li {
        color: #f0f0f0 !important;
    }

    body.dark-mode .mb-0 {
        color: #f0f0f0 !important;
    }

    body.dark-mode .mb-0 ul,
    body.dark-mode .mb-0 li {
        color: #f0f0f0 !important;
    }

    /* Ensure all text in dark mode is visible */
    body.dark-mode p {
        color: #f0f0f0 !important;
    }

    body.dark-mode span {
        color: inherit;
    }

    body.dark-mode .rubric-pages {
        color: #f0f0f0 !important;
    }

    body.dark-mode .rubric-pages * {
        color: inherit;
    }

    /* Dark mode for disabled pager buttons */
    body.dark-mode .pager-btn:disabled {
        background-color: #262626 !important;
        color: #888 !important;
        border-color: #555 !important;
    }

    @media (max-width: 768px) {
        .rubric-pager {
            flex-wrap: wrap;
        }

        .submissions-table-container {
            padding: 0.75rem 1rem;
        }
    }
</style>

