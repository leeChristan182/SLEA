@extends('layouts.app')

@section('title', 'SLEA Awards Report')

@section('content')
<div class="container">
    @include('partials.sidebar')

    <main class="main-content awards-report-page">

        {{-- Page header --}}
        <div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <h1 class="mb-1">SLEA Awards Report</h1>
                <p class="text-muted mb-0">
                    View and export qualified student leaders based on their final compiled scores.
                </p>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                {{-- Export PDF button --}}
                <a href="{{ route('admin.pdf.award-report.pdf', request()->query()) }}"
                   class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <section class="card mb-3 p-3">
            <form method="GET" action="{{ route('admin.award-report') }}" id="filterForm">
                <div class="row g-2 align-items-end">
                    {{-- Search --}}
                    <div class="col-md-3">
                        <label for="q" class="form-label mb-1">Search</label>
                        <input type="text"
                               name="q"
                               id="q"
                               class="form-control form-control-sm"
                               placeholder="Search by name or student no."
                               value="{{ request('q') }}">
                    </div>

                    {{-- College --}}
                    <div class="col-md-3">
                        <label for="college_id" class="form-label mb-1">College</label>
                        <select name="college_id" id="college_id" class="form-select form-select-sm">
                            <option value="">All Colleges</option>
                            @foreach($colleges as $college)
                                <option value="{{ $college->id }}"
                                    @selected(request('college_id') == $college->id)>
                                    {{ $college->code ?? $college->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Program --}}
                    <div class="col-md-3">
                        <label for="program_id" class="form-label mb-1">Program</label>
                        <select name="program_id" id="program_id" class="form-select form-select-sm">
                            <option value="">All Programs</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}"
                                    @selected(request('program_id') == $program->id)>
                                    {{ $program->code ?? $program->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Min Score --}}
                    <div class="col-md-2">
                        <label for="min_score" class="form-label mb-1">Min Score (%)</label>
                        <select name="min_score" id="min_score" class="form-select form-select-sm">
                            <option value="">Any</option>
                            @foreach([70, 80, 85, 90] as $cutoff)
                                <option value="{{ $cutoff }}"
                                    @selected((string)request('min_score') === (string)$cutoff)>
                                    ≥ {{ $cutoff }}%
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Apply --}}
                    <div class="col-md-1 d-flex">
                        <button type="submit"
                                class="btn btn-primary btn-sm ms-auto w-100">
                            Apply
                        </button>
                    </div>
                </div>

                {{-- Award level quick filter tabs (optional) --}}
                <div class="mt-3 d-flex flex-wrap gap-2 small">
                    @php
                        $currentLevel = request('award_level');
                        $tabBaseQuery = request()->except('award_level', 'page');
                    @endphp

                    <span class="me-2 fw-semibold text-muted">Filter by award:</span>

                    <a href="{{ route('admin.award-report', $tabBaseQuery) }}"
                       class="badge rounded-pill px-3 py-2 {{ $currentLevel === null ? 'bg-primary text-white' : 'bg-light text-muted' }}">
                        All
                    </a>

                    <a href="{{ route('admin.award-report', array_merge($tabBaseQuery, ['award_level' => 'gold'])) }}"
                       class="badge rounded-pill px-3 py-2 {{ $currentLevel === 'gold' ? 'bg-warning text-dark' : 'bg-light text-muted' }}">
                        Gold
                    </a>

                    <a href="{{ route('admin.award-report', array_merge($tabBaseQuery, ['award_level' => 'silver'])) }}"
                       class="badge rounded-pill px-3 py-2 {{ $currentLevel === 'silver' ? 'bg-secondary text-white' : 'bg-light text-muted' }}">
                        Silver
                    </a>

                    <a href="{{ route('admin.award-report', array_merge($tabBaseQuery, ['award_level' => 'qualified'])) }}"
                       class="badge rounded-pill px-3 py-2 {{ $currentLevel === 'qualified' ? 'bg-success text-white' : 'bg-light text-muted' }}">
                        SLEA Qualified
                    </a>

                    <a href="{{ route('admin.award-report', array_merge($tabBaseQuery, ['award_level' => 'tracking'])) }}"
                       class="badge rounded-pill px-3 py-2 {{ $currentLevel === 'tracking' ? 'bg-info text-dark' : 'bg-light text-muted' }}">
                        Tracking
                    </a>
                </div>
            </form>
        </section>

        {{-- Summary cards --}}
        <section class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body py-3">
                        <div class="text-muted small">Total Students</div>
                        <div class="fs-4 fw-bold">{{ $stats['total'] ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body py-3">
                        <div class="text-muted small">Gold Awardees</div>
                        <div class="fs-4 fw-bold text-warning">{{ $stats['gold'] ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body py-3">
                        <div class="text-muted small">Silver Awardees</div>
                        <div class="fs-4 fw-bold text-secondary">{{ $stats['silver'] ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body py-3">
                        <div class="text-muted small">SLEA Qualified</div>
                        <div class="fs-4 fw-bold text-success">{{ $stats['qualified'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </section>

        {{-- MAIN TABLE – with raw score --}}
        <section class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 15%;">Student ID</th>
                                <th style="width: 30%;">Full Name</th>
                                <th style="width: 25%;">College</th>
                                <th style="width: 15%;">Program</th>
                                <th style="width: 15%;">Total Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $row)
                                <tr>
                                    {{-- Student ID --}}
                                    <td>
                                        {{ $row->student_number
                                           ?? $row->user->studentAcademic->student_id
                                           ?? 'N/A' }}
                                    </td>

                                    {{-- Full Name --}}
                                    <td>
                                        {{ $row->user->full_name ?? 'N/A' }}
                                    </td>

                                    {{-- College --}}
                                    <td>
                                        {{ $row->college_name
                                           ?? $row->user->studentAcademic->college->name
                                           ?? 'N/A' }}
                                    </td>

                                    {{-- Program --}}
                                    <td>
                                        {{ $row->program_code
                                           ?? $row->user->studentAcademic->program->code
                                           ?? 'N/A' }}
                                    </td>

                                    {{-- RAW TOTAL SCORE: x / y --}}
                                    <td>
                                        @php
                                            $score = $row->raw_total_score ?? $row->total_score ?? 0;
                                            $max   = $row->raw_max_points ?? $row->max_points ?? 0;
                                        @endphp
                                        {{ $score }} / {{ $max }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No students match your current filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="p-2 border-top">
                    {{ $students->appends(request()->query())->links() }}
                </div>
            </div>
        </section>
    </main>
</div>
@endsection
