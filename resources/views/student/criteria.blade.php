
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

    <div class="container rubric-wide-container-student" style="margin-top: 0 !important;">
        @include('partials.sidebar')

        <main class="main-content" style="padding-top: 48px !important;">
            @php
                // Determine initial page (default to 1 since no filter)
                $initialPage = 1;
            @endphp

            <div class="rubric-main-container" x-data="rubricPager(@json($pageTitles), {{ $initialPage }})">

                {{-- Current rubric label (same style as admin-side pages) --}}
                <div class="current-page-label">
                    <span x-text="pageTitle"></span>
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

        .rubric-wide-container-student {
            max-width: 1400px;
            width: min(95vw, 1400px);
        }

        body.dark-mode .main-content {
            background: #2a2a2a !important;
            color: #f0f0f0 !important;
        }

        .rubric-main-container {
            width: 100%;
            margin-top: 0 !important;
            padding-top: 48px !important; /* 0.5 inch gap from header */
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

        /* Rubric section heading */
        .rubric-section {
            margin-bottom: 30px;
            text-align: left;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            color: #212529 !important;
            width: 100%;
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
            max-width: none;
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
            max-width: none;
        }

        body.dark-mode .rubric-category-description {
            color: #ccc !important;
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
            width: 100%;
        }

        body.dark-mode .table-wrap {
            background: transparent !important;
        }

        /* CRITICAL: Override ALL global CSS with maximum specificity */
        /* Use the EXACT same structure as admin but with higher specificity */
        
        /* Override global .rubric-section .manage-table rules */
        .rubric-main-container .rubric-section .manage-table,
        .rubric-main-container .rubric-section .table-wrap .manage-table,
        .rubric-main-container .table-wrap .manage-table,
        .rubric-section .table-wrap .manage-table {
            width: 100% !important;
            border-collapse: collapse !important; /* CRITICAL: override global 'separate' */
            border-spacing: 0 !important;
            background: #fff !important;
            border-radius: 8px !important;
            overflow: hidden !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06) !important;
            color: #212529 !important;
            table-layout: auto !important; /* Override global fixed */
            border: none !important;
            margin: 0 !important;
        }

        .rubric-main-container .rubric-section .manage-table thead,
        .rubric-main-container .rubric-section .table-wrap .manage-table thead,
        .rubric-main-container .table-wrap .manage-table thead,
        .rubric-section .table-wrap .manage-table thead {
            background: #7b0000 !important;
        }

        .rubric-main-container .rubric-section .manage-table thead th,
        .rubric-main-container .rubric-section .table-wrap .manage-table thead th,
        .rubric-main-container .table-wrap .manage-table thead th,
        .rubric-section .table-wrap .manage-table thead th {
            background: #7b0000 !important;
            color: #fff !important;
            font-weight: 600 !important;
            padding: 15px 12px !important;
            text-align: left !important;
            border-right: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-bottom: 2px solid #fff !important;
            border-top: none !important;
            border-left: none !important;
            font-size: 14px !important;
        }

        .rubric-main-container .rubric-section .manage-table thead th:last-child,
        .rubric-main-container .rubric-section .table-wrap .manage-table thead th:last-child,
        .rubric-main-container .table-wrap .manage-table thead th:last-child,
        .rubric-section .table-wrap .manage-table thead th:last-child {
            border-right: none !important;
        }

        /* Points column - narrow width (3rd column in Leadership category only) */
        .rubric-section[data-category="leadership"] .manage-table thead th:nth-child(3),
        .rubric-section[data-category="leadership"] .manage-table tbody td:nth-child(3) {
            width: 100px !important;
            min-width: 100px !important;
            max-width: 100px !important;
            text-align: center !important;
            white-space: normal !important;
            word-wrap: break-word !important;
            word-break: break-word !important;
            padding: 12px 8px !important;
        }

        /* Max Points column - narrow width (3rd column in categories II-V) */
        .rubric-main-container .rubric-section .manage-table thead th:nth-child(3),
        .rubric-main-container .rubric-section .manage-table tbody td:nth-child(3),
        .rubric-main-container .rubric-section .table-wrap .manage-table thead th:nth-child(3),
        .rubric-main-container .rubric-section .table-wrap .manage-table tbody td:nth-child(3),
        .rubric-main-container .table-wrap .manage-table thead th:nth-child(3),
        .rubric-main-container .table-wrap .manage-table tbody td:nth-child(3),
        .rubric-section .table-wrap .manage-table thead th:nth-child(3),
        .rubric-section .table-wrap .manage-table tbody td:nth-child(3) {
            width: 100px !important;
            min-width: 100px !important;
            max-width: 100px !important;
            text-align: center !important;
            white-space: normal !important;
            word-wrap: break-word !important;
            word-break: break-word !important;
            padding: 12px 8px !important;
        }

        /* CRITICAL: Override global height: 64px and other conflicting rules */
        .rubric-main-container .rubric-section .manage-table tbody td,
        .rubric-main-container .rubric-section .table-wrap .manage-table tbody td,
        .rubric-main-container .table-wrap .manage-table tbody td,
        .rubric-section .table-wrap .manage-table tbody td {
            padding: 12px !important;
            border-right: 1px solid #dee2e6 !important;
            border-bottom: 1px solid #dee2e6 !important;
            border-left: none !important;
            border-top: none !important;
            background: #fff !important;
            color: #212529 !important;
            vertical-align: top !important; /* Override global middle */
            position: relative !important;
            height: auto !important; /* CRITICAL: Override global 64px */
            text-align: left !important; /* Override global center */
            min-height: auto !important;
            max-height: none !important;
            line-height: 1.6 !important;
            word-wrap: break-word !important;
        }

        /* Ensure merged cells have proper borders */
        .rubric-main-container .rubric-section .manage-table tbody td[rowspan],
        .rubric-main-container .rubric-section .table-wrap .manage-table tbody td[rowspan],
        .rubric-main-container .table-wrap .manage-table tbody td[rowspan],
        .rubric-section .table-wrap .manage-table tbody td[rowspan] {
            border-right: 1px solid #dee2e6 !important;
            border-bottom: 1px solid #dee2e6 !important;
            border-left: none !important;
            border-top: none !important;
            height: auto !important;
            min-height: auto !important;
            max-height: none !important;
        }

        /* Placeholder cells used instead of rowspan to keep borders consistent */
        .rubric-main-container .rubric-section .manage-table td.rubric-merged-placeholder,
        .rubric-main-container .rubric-section .table-wrap .manage-table td.rubric-merged-placeholder,
        .rubric-main-container .table-wrap .manage-table td.rubric-merged-placeholder,
        .rubric-section .table-wrap .manage-table td.rubric-merged-placeholder {
            background: inherit !important;
            color: transparent !important;
            user-select: none;
        }

        .rubric-main-container .rubric-section .manage-table tbody td:not(:last-child),
        .rubric-main-container .rubric-section .table-wrap .manage-table tbody td:not(:last-child),
        .rubric-main-container .table-wrap .manage-table tbody td:not(:last-child),
        .rubric-section .table-wrap .manage-table tbody td:not(:last-child) {
            vertical-align: top !important;
        }

        /* Notes column - left alignment (5th column) */
        .rubric-main-container .rubric-section .manage-table thead th:nth-child(5),
        .rubric-main-container .rubric-section .manage-table tbody td:nth-child(5),
        .rubric-main-container .rubric-section .table-wrap .manage-table thead th:nth-child(5),
        .rubric-main-container .rubric-section .table-wrap .manage-table tbody td:nth-child(5),
        .rubric-main-container .table-wrap .manage-table thead th:nth-child(5),
        .rubric-main-container .table-wrap .manage-table tbody td:nth-child(5),
        .rubric-section .table-wrap .manage-table thead th:nth-child(5),
        .rubric-section .table-wrap .manage-table tbody td:nth-child(5) {
            text-align: left !important;
        }

        /* Evidence and Notes content styling */
        .evidence-notes-content {
            line-height: 1.6;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* Max Points column - exclude leadership */
        .rubric-section:not([data-category="leadership"]) .manage-table thead th:nth-child(3),
        .rubric-section:not([data-category="leadership"]) .manage-table tbody td:nth-child(3) {
            width: 100px !important;
            min-width: 100px !important;
            max-width: 100px !important;
            text-align: center !important;
            white-space: normal !important;
            word-wrap: break-word !important;
            word-break: break-word !important;
            padding: 12px 8px !important;
        }

        .rubric-main-container .rubric-section .manage-table tbody tr:last-child td,
        .rubric-main-container .rubric-section .table-wrap .manage-table tbody tr:last-child td,
        .rubric-main-container .table-wrap .manage-table tbody tr:last-child td,
        .rubric-section .table-wrap .manage-table tbody tr:last-child td {
            border-bottom: none !important;
        }

        /* Alternating row colors - matching admin */
        .rubric-main-container .rubric-section .manage-table tbody tr:nth-child(even) td,
        .rubric-main-container .rubric-section .table-wrap .manage-table tbody tr:nth-child(even) td,
        .rubric-main-container .table-wrap .manage-table tbody tr:nth-child(even) td,
        .rubric-section .table-wrap .manage-table tbody tr:nth-child(even) td {
            color: #212529 !important;
        }

        /* CRITICAL: Disable hover effects */
        .rubric-main-container .rubric-section .manage-table tbody tr:hover td,
        .rubric-main-container .rubric-section .table-wrap .manage-table tbody tr:hover td,
        .rubric-main-container .table-wrap .manage-table tbody tr:hover td,
        .rubric-section .table-wrap .manage-table tbody tr:hover td {
            background: inherit !important;
            color: inherit !important;
        }

        /* Dark mode - same high specificity */
        body.dark-mode .rubric-main-container .rubric-section .manage-table,
        body.dark-mode .rubric-main-container .rubric-section .table-wrap .manage-table,
        body.dark-mode .rubric-main-container .table-wrap .manage-table,
        body.dark-mode .rubric-section .table-wrap .manage-table {
            background: #2b2b2b !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3) !important;
            color: #f0f0f0 !important;
        }

        body.dark-mode .rubric-main-container .rubric-section .manage-table thead,
        body.dark-mode .rubric-main-container .rubric-section .table-wrap .manage-table thead,
        body.dark-mode .rubric-main-container .table-wrap .manage-table thead,
        body.dark-mode .rubric-section .table-wrap .manage-table thead {
            background: #5c0000 !important;
        }

        body.dark-mode .rubric-main-container .rubric-section .manage-table thead th,
        body.dark-mode .rubric-main-container .rubric-section .table-wrap .manage-table thead th,
        body.dark-mode .rubric-main-container .table-wrap .manage-table thead th,
        body.dark-mode .rubric-section .table-wrap .manage-table thead th {
            background: #5c0000 !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
            color: #fff !important;
        }

        body.dark-mode .rubric-main-container .rubric-section .manage-table tbody td,
        body.dark-mode .rubric-main-container .rubric-section .table-wrap .manage-table tbody td,
        body.dark-mode .rubric-main-container .table-wrap .manage-table tbody td,
        body.dark-mode .rubric-section .table-wrap .manage-table tbody td {
            background: #3a3a3a !important;
            border-color: #555 !important;
            color: #f0f0f0 !important;
            height: auto !important;
        }

        body.dark-mode .rubric-main-container .rubric-section .manage-table tbody td[rowspan],
        body.dark-mode .rubric-main-container .rubric-section .table-wrap .manage-table tbody td[rowspan],
        body.dark-mode .rubric-main-container .table-wrap .manage-table tbody td[rowspan],
        body.dark-mode .rubric-section .table-wrap .manage-table tbody td[rowspan] {
            background: #3a3a3a !important;
            border-color: #555 !important;
            height: auto !important;
        }

        body.dark-mode .rubric-main-container .rubric-section .manage-table tbody tr:nth-child(even) td,
        body.dark-mode .rubric-main-container .rubric-section .table-wrap .manage-table tbody tr:nth-child(even) td,
        body.dark-mode .rubric-main-container .table-wrap .manage-table tbody tr:nth-child(even) td,
        body.dark-mode .rubric-section .table-wrap .manage-table tbody tr:nth-child(even) td {
            background: #333 !important;
            color: #f0f0f0 !important;
        }

        body.dark-mode .rubric-main-container .rubric-section .manage-table tbody tr:hover td,
        body.dark-mode .rubric-main-container .rubric-section .table-wrap .manage-table tbody tr:hover td,
        body.dark-mode .rubric-main-container .table-wrap .manage-table tbody tr:hover td,
        body.dark-mode .rubric-section .table-wrap .manage-table tbody tr:hover td {
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
        }
    </style>
@endpush

