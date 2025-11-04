@extends('layouts.app')

@section('title', 'Scoring Rubric Configuration')

@section('content')
<div class="rubric-main-container" x-data="rubricTabs()">
    <div class="rubric-content">

        <div class="rubric-header-nav mb-3">
            <a href="{{ route('admin.profile') }}" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <h2 class="rubric-main-title">Scoring Rubric Configuration</h2>
        <p class="text-center text-muted mb-4">Manage all rubric categories and their respective criteria here.</p>

        <div class="tab-nav mb-3">
            <button class="btn" :class="tab === 'leadership' ? 'btn-disable' : ''" @click="tab = 'leadership'">Leadership</button>
            <button class="btn" :class="tab === 'academic' ? 'btn-disable' : ''" @click="tab = 'academic'">Academic</button>
            <button class="btn" :class="tab === 'awards' ? 'btn-disable' : ''" @click="tab = 'awards'">Awards</button>
            <button class="btn" :class="tab === 'community' ? 'btn-disable' : ''" @click="tab = 'community'">Community</button>
            <button class="btn" :class="tab === 'conduct' ? 'btn-disable' : ''" @click="tab = 'conduct'">Conduct</button>
        </div>

        <div class="tab-content">
            <div x-show="tab === 'leadership'">
                @include('admin.rubrics.sections.leadership', ['categories' => $categories])
            </div>
            <div x-show="tab === 'academic'">
                @include('admin.rubrics.sections.academic', ['categories' => $categories])
            </div>
            <div x-show="tab === 'awards'">
                @include('admin.rubrics.sections.awards', ['categories' => $categories])
            </div>
            <div x-show="tab === 'community'">
                @include('admin.rubrics.sections.community', ['categories' => $categories])
            </div>
            <div x-show="tab === 'conduct'">
                @include('admin.rubrics.sections.conduct', ['categories' => $categories])
            </div>
        </div>
    </div>
</div>
@endsection