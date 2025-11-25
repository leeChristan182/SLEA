@extends('layouts.app')
@section('title', 'Criteria & Point System')

@section('content')
    <div class="rubric-main-container">
        <div class="container">
            @include('partials.sidebar')

            <main class="main-content">
                <div class="rubric-content">

                    {{-- Back Navigation --}}
                    <div class="rubric-header-nav">
                        <a href="{{ route('student.performance') }}" class="btn-back">
                            <i class="fa-solid fa-arrow-left"></i>
                            <span>Back to Performance Summary</span>
                        </a>
                    </div>

                    {{-- Page Title --}}
                    <h1 class="rubric-main-title">SLEA Criteria &amp; Point System</h1>

                    <p class="rubric-heading">
                        Review how your submissions will be evaluated for each SLEA category.
                        Use the category shortcuts below to jump between sections.
                    </p>

                    {{-- Category quick access (tabs) --}}
                    <div class="mb-3">
                        <div class="tab-nav" id="criteriaTabs"
                            style="flex-wrap: wrap; overflow-x: visible; gap:.5rem; width:100%; justify-content:flex-start;">

                            <button class="btn btn-disable" data-filter="all">
                                All Categories
                            </button>

                            @foreach($categories as $cat)
                                <button class="btn" data-filter="cat-{{ $cat->id }}">
                                    {{ $loop->iteration }}. {{ $cat->title }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Criteria Display --}}
                    @forelse ($categories as $cat)
                        <section class="rubric-section criteria-category" data-category-key="cat-{{ $cat->id }}">

                            {{-- Category Header --}}
                            <div class="rubric-heading d-flex justify-content-between align-items-center">
                                <div>{{ $loop->iteration }}. {{ $cat->title }}</div>
                                <div>
                                    @if(!is_null($cat->min_required_points))
                                        <span class="badge badge--red ms-1">
                                            Min required:
                                            {{ rtrim(rtrim(number_format($cat->min_required_points, 2), '0'), '.') }} pts
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Description --}}
                            @if($cat->description)
                                <div class="account-summary mb-3">
                                    <div class="summary-row">
                                        <span class="summary-label">Description</span>
                                        <span class="summary-value">{{ $cat->description }}</span>
                                    </div>
                                </div>
                            @endif

                            {{-- Sections --}}
                            @forelse($cat->sections as $section)
                                <div class="subsection">

                                    <div class="subsection-title">
                                        {{ $section->title }}
                                        @if(!is_null($section->max_points))
                                            <span class="text-muted ms-1">
                                                (Section max:
                                                {{ rtrim(rtrim(number_format($section->max_points, 2), '0'), '.') }} pts)
                                            </span>
                                        @endif
                                    </div>

                                    <div class="table-responsive rubric-table-wrapper">
                                        <table class="manage-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 24%">Subsection</th>
                                                    <th style="width: 30%">Positions / Options</th>
                                                    <th style="width: 12%">Points</th>
                                                    <th style="width: 17%">Evidence Needed</th>
                                                    <th style="width: 17%">Notes</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @forelse($section->subsections as $sub)
                                                    @php
                                                        $options = $sub->options;
                                                        $optionCount = max(1, $options->count());

                                                        // SAFE decode for score_params (can be array or JSON string)
                                                        if (is_array($sub->score_params)) {
                                                            $params = $sub->score_params;
                                                        } elseif (is_string($sub->score_params) && trim($sub->score_params) !== '') {
                                                            $params = json_decode($sub->score_params, true);
                                                        } else {
                                                            $params = null;
                                                        }

                                                        // Fallback points text for subsections without options
                                                        $noOptionDisplay = null;
                                                        if (!is_null($sub->max_points)) {
                                                            $noOptionDisplay =
                                                                rtrim(rtrim(number_format($sub->max_points, 2), '0'), '.') . ' pts';
                                                        } elseif (!is_null($sub->cap_points)) {
                                                            $noOptionDisplay =
                                                                'Up to ' .
                                                                rtrim(rtrim(number_format($sub->cap_points, 2), '0'), '.') .
                                                                ' pts';
                                                        } elseif ($params && isset($params['rate'])) {
                                                            $noOptionDisplay =
                                                                rtrim(rtrim(number_format($params['rate'], 2), '0'), '.') .
                                                                ' pts per ' . ($sub->unit ?: 'unit');
                                                        }
                                                    @endphp

                                                    {{-- First row per subsection --}}
                                                    <tr class="criteria-row">

                                                        {{-- Subsection name (spans all option rows) --}}
                                                        <td rowspan="{{ $optionCount }}">
                                                            <strong>{{ $sub->sub_section }}</strong>
                                                        </td>

                                                        {{-- First option / position --}}
                                                        <td style="padding:6px 10px; border-bottom:1px solid #e3e3e3;">
                                                            @if($options->count())
                                                                {{ $options[0]->label }}
                                                            @else
                                                                <span class="text-muted">No specific options listed.</span>
                                                            @endif
                                                        </td>

                                                        {{-- First points cell --}}
                                                        <td style="padding:6px 10px; border-bottom:1px solid #e3e3e3;">
                                                            @if($options->count())
                                                                @php $opt = $options[0]; @endphp
                                                                @if(!is_null($opt->points))
                                                                    {{ rtrim(rtrim(number_format($opt->points, 2), '0'), '.') }} pts
                                                                @else
                                                                    —
                                                                @endif
                                                            @else
                                                                {{ $noOptionDisplay ?? '—' }}
                                                            @endif
                                                        </td>

                                                        {{-- Evidence (spans all option rows) --}}
                                                        <td rowspan="{{ $optionCount }}">
                                                            @if($sub->evidence_needed)
                                                                {!! nl2br(e($sub->evidence_needed)) !!}
                                                            @elseif($section->evidence)
                                                                {!! nl2br(e($section->evidence)) !!}
                                                            @else
                                                                <span class="text-muted">See category guidelines.</span>
                                                            @endif
                                                        </td>

                                                        {{-- Notes (spans all option rows) --}}
                                                        <td rowspan="{{ $optionCount }}">
                                                            @if($sub->notes)
                                                                {!! nl2br(e($sub->notes)) !!}
                                                            @elseif($section->notes)
                                                                {!! nl2br(e($section->notes)) !!}
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    {{-- Remaining options for this subsection --}}
                                                    @if($options->count() > 1)
                                                        @foreach($options->slice(1) as $opt)
                                                            <tr>
                                                                <td style="padding:6px 10px; border-bottom:1px solid #e3e3e3;">
                                                                    {{ $opt->label }}
                                                                </td>
                                                                <td style="padding:6px 10px; border-bottom:1px solid #e3e3e3;">
                                                                    @if(!is_null($opt->points))
                                                                        {{ rtrim(rtrim(number_format($opt->points, 2), '0'), '.') }} pts
                                                                    @else
                                                                        —
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">
                                                            No subsections configured for this section yet.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>

                                        </table>
                                    </div>

                                </div>
                            @empty
                                <div class="alert alert-light mt-2">
                                    No sections configured under this category yet.
                                </div>
                            @endforelse

                        </section>
                    @empty
                        <div class="alert alert-info mt-4">
                            Criteria are not yet configured. Please check again later.
                        </div>
                    @endforelse

                </div>
            </main>
        </div>
    </div>

    {{-- Only category filtering (no search) --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const tabs = document.querySelectorAll('#criteriaTabs .btn');
                const sections = document.querySelectorAll('.criteria-category');

                let activeFilter = 'all';

                function applyFilters() {
                    sections.forEach(section => {
                        const matches = (activeFilter === 'all') ||
                            (section.dataset.categoryKey === activeFilter);
                        section.style.display = matches ? '' : 'none';
                    });
                }

                tabs.forEach(tab => {
                    tab.addEventListener('click', () => {
                        tabs.forEach(t => t.classList.remove('btn-disable'));
                        tab.classList.add('btn-disable');
                        activeFilter = tab.dataset.filter || 'all';
                        applyFilters();
                    });
                });

                applyFilters();
            });
        </script>
    @endpush

@endsection