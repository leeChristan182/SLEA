@extends('layouts.app')

@section('title', 'Criteria and Points System')

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

    <main class="main-content" style="padding-top: 100px !important;">
        @php
            // Determine initial page (default to 1 since no filter)
            $initialPage = 1;
        @endphp

        <div class="rubric-main-container" x-data="rubricPager(@json($pageTitles), {{ $initialPage }})">

            {{-- Page Header --}}
            <div class="page-header">
                <h1>Criteria and Points System</h1>
            </div>

            {{-- Pages --}}
            <div class="rubric-pages">

            {{-- Page 1: Leadership Excellence (all subsections A-D) --}}
            <section x-show="page === 1" x-cloak>
                @include('student.criteria.sections.leadership', [
                    'categories' => $categories,
                    'leadershipSections' => null, // null means show all sections
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

        </div>
    </main>
</div>

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
        };
    }
</script>
@endpush

@push('styles')
<style>
    /* Main Container */
    .rubric-main-container {
        padding: 20px;
        padding-top: 20px; /* Normal padding since main-content now handles header spacing */
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    body.dark-mode .rubric-main-container {
        background-color: #2a2a2a;
        color: #f0f0f0;
    }

    /* Page Header */
    .page-header {
        margin-bottom: 24px;
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

    /* Current Page Label */
    .current-page-label {
        font-size: 24px;
        font-weight: 700;
        color: #7b0000;
        margin-bottom: 20px;
        margin-top: 20px; /* Additional top margin for visibility */
        padding-bottom: 10px;
        border-bottom: 2px solid #7b0000;
    }

    body.dark-mode .current-page-label {
        color: #f9bd3d;
        border-bottom-color: #f9bd3d;
    }

    /* Rubric Pages */
    .rubric-pages {
        min-height: 400px;
    }

    /* Rubric Section */
    .rubric-section {
        margin-bottom: 192px; /* 2 inches spacing between sections */
        margin-top: 1rem; /* Additional spacing for category title */
        color: #212529 !important;
    }

    body.dark-mode .rubric-section {
        color: #f0f0f0 !important;
    }

    .rubric-heading {
        font-size: 20px;
        font-weight: 700;
        color: #7b0000 !important;
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 2px solid #7b0000;
    }

    body.dark-mode .rubric-heading {
        color: #f9bd3d !important;
        border-bottom-color: #f9bd3d;
    }

    .rubric-category-description {
        font-size: 14px;
        line-height: 1.6;
        color: #555;
        margin-bottom: 20px;
        padding: 12px;
        background: #f8f9fa;
        border-left: 4px solid #7b0000;
        border-radius: 4px;
    }

    body.dark-mode .rubric-category-description {
        color: #ccc;
        background: #333;
        border-left-color: #f9bd3d;
    }

    /* Subsection */
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
        color: #7b0000 !important;
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
        color: #212529 !important;
    }

    body.dark-mode .manage-table {
        background: #333 !important;
        color: #f0f0f0 !important;
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
        color: #212529 !important;
        vertical-align: top;
        position: relative;
    }

    body.dark-mode .manage-table tbody td {
        background: #333 !important;
        color: #f0f0f0 !important;
        border-right-color: #555;
        border-bottom-color: #555;
    }

    .manage-table tbody td:not(:last-child) {
        vertical-align: top;
    }

    /* Evidence Needed column - left alignment (4th column) */
    .manage-table thead th:nth-child(4),
    .manage-table tbody td:nth-child(4) {
        text-align: left !important;
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

    .manage-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Pagination Controls */
    .rubric-pager {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 30px;
        padding: 20px;
    }

    .pager-btn {
        padding: 8px 16px;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        color: #7b0000;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.2s;
    }

    .pager-btn:hover:not(:disabled) {
        background: #7b0000;
        color: #fff;
        border-color: #7b0000;
    }

    .pager-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    body.dark-mode .pager-btn {
        background-color: #262626;
        border-color: #555;
        color: #eee;
    }

    body.dark-mode .pager-btn:hover:not(:disabled) {
        background: #f9bd3d;
        color: #2a2a2a;
        border-color: #f9bd3d;
    }

    .pager-pages {
        display: flex;
        gap: 5px;
    }

    .pager-page {
        min-width: 40px;
        height: 40px;
        padding: 0 12px;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        color: #7b0000;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pager-page:hover {
        background: #f8f9fa;
        border-color: #7b0000;
    }

    .pager-page.active {
        background: #7b0000;
        border-color: #7b0000;
        color: #fff;
        font-weight: 600;
    }

    body.dark-mode .pager-page {
        background-color: #262626;
        border-color: #555;
        color: #eee;
    }

    body.dark-mode .pager-page:hover {
        background: #333;
        border-color: #f9bd3d;
    }

    body.dark-mode .pager-page.active {
        background-color: #f9bd3d;
        border-color: #f9bd3d;
        color: #2a2a2a;
    }
</style>
@endpush

