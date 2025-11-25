@extends('layouts.app')

@section('title', 'Scoring Rubric Configuration')

@section('content')
@php
    /** @var \Illuminate\Support\Collection|\App\Models\RubricCategory[] $categories */

    // Find Leadership category + its sections
    $leadershipCategory = $categories->firstWhere('key', 'leadership');
    $leadershipSections = $leadershipCategory?->sections ?? collect();

    // 🔧 How many leadership SECTIONS per page? (1 = each section on its own page)
    $leadershipChunkSize = 1;

    // Split leadership into chunks for multi-page display
    $leadershipChunks = $leadershipSections->chunk($leadershipChunkSize);

    // Build page titles in order:
    // 1..N → Leadership chunks (Part 1, Part 2, ...) if more than one chunk
    // then Academic, Awards, Community, Conduct
    $pageTitles = [];
    $pageNumber = 1;

    $hasMultipleLeadershipPages = $leadershipChunks->count() > 1;
    foreach ($leadershipChunks as $index => $chunk) {
        $label = 'I. Leadership Excellence';
        if ($hasMultipleLeadershipPages) {
            $label .= ' (Part ' . ($index + 1) . ')';
        }
        $pageTitles[$pageNumber] = $label;
        $pageNumber++;
    }

    $pageTitles[$pageNumber++] = 'II. Academic Excellence';
    $pageTitles[$pageNumber++] = 'III. Awards & Recognition';
    $pageTitles[$pageNumber++] = 'IV. Community Involvement';
    $pageTitles[$pageNumber++] = 'V. Good Conduct';

    $totalPages = count($pageTitles);
@endphp

<div class="rubric-main-container" x-data="rubricPager(@json($pageTitles))">
    <div class="rubric-content">

        {{-- Back nav --}}
        <div class="rubric-header-nav mb-3">
            <a href="{{ route('admin.profile') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Dashboard</span>
            </a>
        </div>

        {{-- Heading --}}
        <header class="rubric-header text-center mb-3">
            <h2 class="rubric-main-title">Scoring Rubric Configuration</h2>
            <p class="rubric-subtitle">
                Manage all rubric categories and their respective criteria here.
            </p>
        </header>

        {{-- Current rubric label (same style as student-side pages) --}}
        <div class="current-page-label mb-2">
            <span x-text="pageTitle"></span>
        </div>

        {{-- Pages --}}
        <div class="rubric-pages mt-2">

            {{-- Leadership pages (multiple, chunked by section) --}}
            @php $page = 1; @endphp
            @foreach ($leadershipChunks as $chunkIndex => $chunk)
                <section x-show="page === {{ $page }}" x-cloak>
                    @include('admin.rubrics.sections.leadership', [
                        'categories'         => $categories,
                        // this variable lets the partial know which subset to render
                        'leadershipSections' => $chunk,
                    ])
                </section>
                @php $page++; @endphp
            @endforeach

            {{-- Academic --}}
            <section x-show="page === {{ $page }}" x-cloak>
                @include('admin.rubrics.sections.academic', ['categories' => $categories])
            </section>
            @php $page++; @endphp

            {{-- Awards --}}
            <section x-show="page === {{ $page }}" x-cloak>
                @include('admin.rubrics.sections.awards', ['categories' => $categories])
            </section>
            @php $page++; @endphp

            {{-- Community --}}
            <section x-show="page === {{ $page }}" x-cloak>
                @include('admin.rubrics.sections.community', ['categories' => $categories])
            </section>
            @php $page++; @endphp

            {{-- Conduct --}}
            <section x-show="page === {{ $page }}" x-cloak>
                @include('admin.rubrics.sections.conduct', ['categories' => $categories])
            </section>
            {{-- $page should now equal $totalPages+1 --}}
        </div>

        {{-- Bottom pager (Back 1 2 3 ... Next) --}}
        <nav class="rubric-pager mt-3">
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
</div>
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

    .rubric-main-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1.5rem 1rem 3rem;
    }

    .rubric-content {
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
        padding: 1.5rem 1.75rem 2.5rem;
    }

    .rubric-header-nav {
        display: flex;
        justify-content: flex-start;
        margin-bottom: 0.5rem;
    }

    .btn-back {
        background: transparent;
        border: none;
        color: #8B0000;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0;
        text-decoration: none;
    }

    .btn-back i {
        font-size: 0.9rem;
    }

    .rubric-main-title {
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 0.2rem;
        color: #8B0000;
    }

    .rubric-subtitle {
        margin: 0;
        font-size: 0.9rem;
        color: #666;
    }

    .current-page-label {
        font-weight: 700;
        font-size: 0.95rem;
        margin-top: 0.5rem;
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

    @media (max-width: 768px) {
        .rubric-content {
            padding: 1.25rem 1rem 2rem;
        }

        .rubric-pager {
            flex-wrap: wrap;
        }
    }
</style>
