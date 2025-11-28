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

<div class="container">
    @include('partials.sidebar')

    <main class="main-content">
        <div class="page-header">
            <h1>Scoring Rubric Configuration</h1>
            <p class="rubric-subtitle">
                Manage all rubric categories and their respective criteria here.
            </p>
        </div>

        <div class="rubric-main-container" x-data="rubricPager(@json($pageTitles))">

            {{-- Current rubric label (same style as student-side pages) --}}
            <div class="current-page-label mb-3">
                <span x-text="pageTitle"></span>
            </div>

            {{-- Pages --}}
            <div class="rubric-pages mt-2">

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

            {{-- Bottom pager (Back 1 2 3 ... Next) --}}
            <nav class="rubric-pager mt-4">
                <button
                    type="button"
                    class="pager-btn pager-nav"
                    @click="prev"
                    :disabled="page === 1"
                >
                    Back
                </button>

                <template x-for="n in maxPage" :key="n">
                    <button
                        type="button"
                        class="pager-btn pager-page"
                        :class="{ 'active': page === n }"
                        @click="setPage(n)"
                        x-text="n"
                    ></button>
                </template>

                <button
                    type="button"
                    class="pager-btn pager-nav"
                    @click="next"
                    :disabled="page === maxPage"
                >
                    Next
                </button>
            </nav>

        </div>
    </main>
</div>

{{-- Include Modals --}}
@include('admin.rubrics.partials.modals')
@endsection

@push('scripts')
<script>
    function rubricPager(pageTitles) {
        return {
            page: 1,
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
    .main-content {
        padding: 20px;
        width: 100%;
    }

    .page-header {
        margin-bottom: 12px;
    }

    .page-header h1 {
        font-size: 28px;
        font-weight: 700;
        color: #7b0000;
        margin: 0 0 4px 0;
    }

    body.dark-mode .page-header h1 {
        color: #f9bd3d;
    }

    .rubric-subtitle {
        margin: 0 0 8px 0;
        font-size: 14px;
        color: #666;
    }

    body.dark-mode .rubric-subtitle {
        color: #ccc;
    }

    .rubric-main-container {
        width: 100%;
    }

    .current-page-label {
        font-weight: 700;
        font-size: 16px;
        color: #7b0000;
        padding: 8px 0;
        border-bottom: 2px solid #7b0000;
        margin-bottom: 16px;
    }

    body.dark-mode .current-page-label {
        color: #f9bd3d;
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
        background-color: #fff;
        font-size: 0.85rem;
        cursor: pointer;
        min-width: 2.1rem;
    }

    .pager-btn:disabled {
        opacity: 0.6;
        cursor: default;
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

    body.dark-mode .rubric-subtitle {
        color: #ccc;
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
        background: #fff;
        border-radius: 12px;
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
        text-align: center;
    }

    .submissions-table tbody td {
        padding: 12px;
        border-right: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
        background: #fff;
        vertical-align: top;
    }

    .submissions-table tbody td:last-child {
        border-right: none;
        text-align: center;
    }

    .submissions-table tbody tr:last-child td {
        border-bottom: none;
    }

    .submissions-table tbody tr:nth-child(even) td {
        background: #f8f9fa;
    }

    .submissions-table tbody tr:hover td {
        background: #e3f2fd !important;
    }

    /* Action buttons styling */
    .action-buttons-group {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-edit,
    .btn-delete {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
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
        background: #2b2b2b;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }

    body.dark-mode .submissions-table {
        color: #f0f0f0;
    }

    body.dark-mode .submissions-table thead {
        background: #5c0000;
    }

    body.dark-mode .submissions-table thead th {
        background: #5c0000;
        border-color: rgba(255, 255, 255, 0.15);
    }

    body.dark-mode .submissions-table tbody td {
        background: #3a3a3a;
        border-color: #555;
        color: #f0f0f0;
    }

    body.dark-mode .submissions-table tbody tr:nth-child(even) td {
        background: #333;
    }

    body.dark-mode .submissions-table tbody tr:hover td {
        background: #404040 !important;
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
    }

    .rubric-heading {
        font-size: 20px;
        font-weight: 700;
        color: #7b0000;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid #7b0000;
        text-align: left;
        width: 100%;
        max-width: 1200px;
    }

    body.dark-mode .rubric-heading {
        color: #f9bd3d;
        border-bottom-color: #f9bd3d;
    }

    .rubric-category-description {
        font-size: 14px;
        color: #666;
        margin-bottom: 16px;
        text-align: left;
        line-height: 1.6;
        width: 100%;
        max-width: 1200px;
    }

    body.dark-mode .rubric-category-description {
        color: #ccc;
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
    }

    .subsection-title {
        font-size: 18px;
        font-weight: 600;
        color: #7b0000;
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 1px solid #dee2e6;
    }

    body.dark-mode .subsection-title {
        color: #f9bd3d;
        border-bottom-color: #555;
    }

    .table-wrap {
        margin-bottom: 20px;
        overflow-x: auto;
    }

    .manage-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
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

    .manage-table tbody td {
        padding: 12px;
        border-right: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
        background: #fff;
        vertical-align: top;
    }

    .manage-table tbody td:last-child {
        border-right: none;
        text-align: center;
    }

    .manage-table tbody tr:last-child td {
        border-bottom: none;
    }

    .manage-table tbody tr:nth-child(even) td {
        background: #f8f9fa;
    }

    .manage-table tbody tr:hover td {
        background: #e3f2fd !important;
    }

    /* Dark mode for manage-table */
    body.dark-mode .manage-table {
        background: #2b2b2b;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }

    body.dark-mode .manage-table thead {
        background: #5c0000;
    }

    body.dark-mode .manage-table thead th {
        background: #5c0000;
        border-color: rgba(255, 255, 255, 0.15);
    }

    body.dark-mode .manage-table tbody td {
        background: #3a3a3a;
        border-color: #555;
        color: #f0f0f0;
    }

    body.dark-mode .manage-table tbody tr:nth-child(even) td {
        background: #333;
    }

    body.dark-mode .manage-table tbody tr:hover td {
        background: #404040 !important;
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
