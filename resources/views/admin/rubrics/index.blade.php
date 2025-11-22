@extends('layouts.app')

@section('title', 'Scoring Rubric Configuration')

@section('content')
@php
// Expected from controller: $categories = RubricCategory::with(['sections.subsections.options'])->get();
$tabs = [
'leadership' => 'I. Leadership',
'academic' => 'II. Academic Performance',
'awards' => 'III. Awards & Recognition',
'community' => 'IV. Community Involvement',
'conduct' => 'V. Conduct',
];
$firstKey = array_key_first($tabs);
@endphp

<div class="rubric-main-container" x-data="rubricTabs('{{ $firstKey }}')">
    <div class="rubric-content">

        {{-- Back nav --}}
        <div class="rubric-header-nav mb-3">
            <a href="{{ route('admin.profile') }}" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <h2 class="rubric-main-title">Scoring Rubric Configuration</h2>
        <p class="text-center text-muted mb-4">
            Manage all rubric categories and their respective criteria here.
        </p>

        {{-- Tabs --}}
        <div class="rubric-tabs-wrapper">
            <ul class="rubric-tabs nav nav-tabs" role="tablist">
                @foreach($tabs as $key => $label)
                <li class="nav-item" role="presentation">
                    <button type="button"
                        class="nav-link rubric-tab-btn"
                        :class="{ 'active': tab === '{{ $key }}' }"
                        @click="tab = '{{ $key }}'"
                        role="tab">
                        {{ $label }}
                    </button>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Tab Panels --}}
        <div class="rubric-tabs-content mt-3">

            {{-- Leadership (uses $categories collection) --}}
            <div x-show="tab === 'leadership'" x-cloak>
                @include('admin.rubrics.sections.leadership', ['categories' => $categories])
            </div>

            {{-- Academic --}}
            <div x-show="tab === 'academic'" x-cloak>
                @include('admin.rubrics.sections.academic', ['categories' => $categories])
            </div>

            {{-- Awards --}}
            <div x-show="tab === 'awards'" x-cloak>
                @include('admin.rubrics.sections.awards', ['categories' => $categories])
            </div>

            {{-- Community --}}
            <div x-show="tab === 'community'" x-cloak>
                @include('admin.rubrics.sections.community', ['categories' => $categories])
            </div>

            {{-- Conduct --}}
            <div x-show="tab === 'conduct'" x-cloak>
                @include('admin.rubrics.sections.conduct', ['categories' => $categories])
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function rubricTabs(initialTab) {
        return {
            tab: initialTab || 'leadership',
        };
    }
</script>
@endpush

<style>
    /* Container */
    .rubric-main-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1.5rem 1rem 3rem;
    }

    .rubric-content {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
        padding: 1.5rem 1.75rem 2.5rem;
    }

    .rubric-header-nav .btn-back {
        background: transparent;
        border: none;
        color: #8B0000;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0;
    }

    .rubric-main-title {
        text-align: center;
        font-weight: 700;
        margin-bottom: 0.25rem;
        color: #8B0000;
    }

    /* Tabs */
    .rubric-tabs-wrapper {
        border-bottom: 1px solid #e0e0e0;
        margin-top: 1rem;
        overflow-x: auto;
    }

    .rubric-tabs {
        border: none;
        gap: 0.5rem;
        white-space: nowrap;
    }

    .rubric-tab-btn {
        border: none;
        background: transparent;
        border-radius: 999px 999px 0 0;
        padding: 0.5rem 1rem;
        font-weight: 600;
        color: #555;
        border-bottom: 3px solid transparent;
        cursor: pointer;
    }

    .rubric-tab-btn.active {
        background-color: #fff6f6;
        border-color: #8B0000;
        color: #8B0000;
    }

    .rubric-tabs-content {
        margin-top: 1.25rem;
    }

    /* Dark mode tweaks */
    body.dark-mode .rubric-content {
        background: #2a2a2a;
        color: #f0f0f0;
    }

    body.dark-mode .rubric-main-title {
        color: #f9bd3d;
    }

    body.dark-mode .rubric-tabs-wrapper {
        border-bottom-color: #555;
    }

    body.dark-mode .rubric-tab-btn {
        color: #ddd;
    }

    body.dark-mode .rubric-tab-btn.active {
        background-color: #3a2a2a;
        border-color: #f9bd3d;
        color: #f9bd3d;
    }

    @media (max-width: 768px) {
        .rubric-content {
            padding: 1.25rem 1rem 2rem;
        }
    }
</style>