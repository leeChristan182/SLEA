@extends('layouts.app')

@section('title', 'Assessor Dashboard')

@section('head')
    <style>
        .dash-wrap {
            padding: 18px 18px 30px;
        }

        .dash-title {
            font-weight: 800;
        }

        .dash-sub {
            color: rgba(0, 0, 0, .55);
            font-size: 13px;
        }

        body.dark-mode .dash-sub {
            color: rgba(255, 255, 255, .7);
        }

        .dash-grid {
            display: grid;
            gap: 14px;
        }

        .dash-grid.kpis {
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }

        @media (max-width: 992px) {
            .dash-grid.kpis {
                grid-template-columns: 1fr;
            }
        }

        .dash-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid rgba(0, 0, 0, .08);
            box-shadow: 0 6px 22px rgba(0, 0, 0, .06);
            overflow: hidden;
        }

        body.dark-mode .dash-card {
            background: #2a2a2a;
            border-color: rgba(255, 255, 255, .10);
            box-shadow: 0 6px 22px rgba(0, 0, 0, .25);
        }

        .pad {
            padding: 14px 16px;
        }

        .kpi {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .kpi .label {
            font-size: 12px;
            color: rgba(0, 0, 0, .55);
        }

        body.dark-mode .kpi .label {
            color: rgba(255, 255, 255, .65);
        }

        .kpi .value {
            font-size: 34px;
            font-weight: 900;
            line-height: 1;
        }

        .kpi .icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: rgba(13, 110, 253, .10);
            color: #0d6efd;
        }

        body.dark-mode .kpi .icon {
            background: rgba(255, 255, 255, .08);
            color: #fff;
        }

        /* Pill-style maroon CTA (View Queue) */
        .btn-pill-maroon {
            background: rgba(123, 0, 0, 0.12);
            color: #7b0000;
            border: none;
            border-radius: 999px;
            padding: 8px 18px;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.15s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-pill-maroon:hover {
            background: rgba(123, 0, 0, 0.18);
            color: #7b0000;
        }

        body.dark-mode .btn-pill-maroon {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
        }

        body.dark-mode .btn-pill-maroon:hover {
            background: rgba(255, 255, 255, 0.22);
            color: #fff;
        }

        .table-wrap {
            overflow: auto;
        }

        .dash-table {
            width: 100%;
            min-width: 760px;
            border-collapse: collapse;
        }

        .dash-table th,
        .dash-table td {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(0, 0, 0, .08);
        }

        body.dark-mode .dash-table th,
        body.dark-mode .dash-table td {
            border-bottom-color: rgba(255, 255, 255, .10);
        }

        .dash-table th {
            text-align: left;
            font-size: 12px;
            color: rgba(0, 0, 0, .55);
            font-weight: 700;
        }

        body.dark-mode .dash-table th {
            color: rgba(255, 255, 255, .65);
        }

        .badge-soft {
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid rgba(0, 0, 0, .10);
            background: rgba(0, 0, 0, .03);
            color: rgba(0, 0, 0, .70);
            display: inline-block;
        }

        body.dark-mode .badge-soft {
            border-color: rgba(255, 255, 255, .14);
            background: rgba(255, 255, 255, .06);
            color: rgba(255, 255, 255, .80);
        }

        .badge-pending {
            background: rgba(255, 193, 7, .12);
            border-color: rgba(255, 193, 7, .25);
        }

        .badge-submitted {
            background: rgba(13, 110, 253, .12);
            border-color: rgba(13, 110, 253, .25);
        }

        .badge-finalized {
            background: rgba(25, 135, 84, .12);
            border-color: rgba(25, 135, 84, .25);
        }

        .muted {
            color: rgba(0, 0, 0, .55);
            font-size: 13px;
        }

        body.dark-mode .muted {
            color: rgba(255, 255, 255, .70);
        }

        .chart-wrap {
            padding: 12px 16px 16px;
        }

        .chart-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
        }

        .chart-title {
            font-weight: 800;
            font-size: 16px;
        }

        .chart-meta {
            font-size: 12px;
            color: rgba(0, 0, 0, .55);
        }

        body.dark-mode .chart-meta {
            color: rgba(255, 255, 255, .7);
        }

        .dash-alert {
            margin-top: 12px;
            border-radius: 12px;
            padding: 10px 12px;
            border: 1px solid rgba(0, 0, 0, .10);
            background: rgba(255, 193, 7, .10);
            color: rgba(0, 0, 0, .70);
            font-size: 13px;
        }

        body.dark-mode .dash-alert {
            border-color: rgba(255, 255, 255, .12);
            background: rgba(255, 193, 7, .15);
            color: rgba(255, 255, 255, .80);
        }

        /* Ensure charts don't overflow */
        canvas {
            max-width: 100% !important;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        @include('partials.sidebar')

        <main class="main-content">
            <div class="dash-wrap">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <div>
                        <h2 class="dash-title mb-1">Assessor Dashboard</h2>
                        <div class="dash-sub">Individual reviews and submission status overview</div>
                    </div>
                </div>

                {{-- KPI COUNTS --}}
                <div class="dash-grid kpis mb-3">
                    <div class="dash-card">
                        <div class="pad kpi">
                            <div>
                                <div class="label">Pending Submissions Queue</div>
                                <div class="value">{{ $pendingQueueCount }}</div>
                                <div class="muted mt-1">Assigned to you and not started</div>
                            </div>
                            <div class="icon" style="background: rgba(123,0,0,.12); color:#7b0000;">
                                <i class="fas fa-inbox"></i>
                            </div>
                        </div>
                    </div>

                    <div class="dash-card">
                        <div class="pad kpi">
                            <div>
                                <div class="label">Pending Reviews</div>
                                <div class="value">{{ $reviewStats['pending'] }}</div>
                                <div class="muted mt-1">Not yet submitted / not finalized</div>
                            </div>
                            <div class="icon" style="background: rgba(123,0,0,.12); color:#7b0000;"><i class="fas fa-hourglass-half"></i></div>
                        </div>
                    </div>

                    <div class="dash-card">
                        <div class="pad kpi">
                            <div>
                                <div class="label">Submitted Reviews</div>
                                <div class="value">{{ $reviewStats['submitted'] }}</div>
                                <div class="muted mt-1">Submitted but not finalized</div>
                            </div>
                            <div class="icon" style="background: rgba(123,0,0,.12); color:#7b0000;"><i class="fas fa-paper-plane"></i></div>
                        </div>
                    </div>

                    <div class="dash-card">
                        <div class="pad kpi">
                            <div>
                                <div class="label">Finalized Reviews</div>
                                <div class="value">{{ $reviewStats['finalized'] }}</div>
                                <div class="muted mt-1">Completed assessments</div>
                            </div>
                            <div class="icon" style="background: rgba(123,0,0,.12); color:#7b0000;"><i class="fas fa-circle-check"></i></div>
                        </div>
                    </div>
                </div>

                {{-- FINALIZED REVIEWS CHART --}}
                <div class="dash-card mb-3">
                    <div class="chart-wrap">
                        <div class="chart-head">
                            <div class="chart-title">Finalized Reviews Over Time</div>
                            <div class="chart-meta">Last 12 months</div>
                        </div>
                        <canvas id="finalizedReviewsChart" height="80"></canvas>
                        @if(array_sum($chartData) === 0)
                            <div class="dash-alert">
                                No finalized reviews yet. This chart will populate once you finalize reviews.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- INDIVIDUAL REVIEWS TABLE --}}
                <div class="dash-card">
                    <div class="pad d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <div class="fw-bold">Individual Reviews</div>
                            <div class="muted">All your review records (regardless of student)</div>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th style="width: 90px;">Review ID</th>
                                    <th>Student</th>
                                    <th style="width: 140px;">Status</th>
                                    <th style="width: 160px;">Last Updated</th>
                                    <th style="width: 140px;">Total Score</th>
                                    <th style="width: 140px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reviews as $r)
                                    @php
                                        $st = strtolower($r->status ?? '');
                                        $badgeClass = $st === 'finalized' ? 'badge-finalized'
                                            : ($st === 'submitted' ? 'badge-submitted' : 'badge-pending');
                                    @endphp
                                    <tr>
                                        <td>#{{ $r->id }}</td>
                                        <td>
                                            <div class="fw-semibold">
                                                {{ optional($r->student)->name ?? '—' }}
                                            </div>
                                            <div class="muted">
                                                {{ optional($r->student)->email ?? '' }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge-soft {{ $badgeClass }}">
                                                {{ ucfirst($r->status ?? 'pending') }}
                                            </span>
                                        </td>
                                        <td class="muted">{{ optional($r->updated_at)->format('M d, Y h:i A') }}</td>
                                        <td>
                                            <span class="fw-semibold">
                                                {{ $r->total_score !== null ? number_format((float) $r->total_score, 2) : '—' }}
                                            </span>
                                        </td>
                                        <td>
                                            {{-- Replace route name with your actual assessor review route --}}
                                            <a href="{{ route('assessor.final-review.index') }}"
                                                class="btn btn-sm btn-outline-primary">
                                                {{ strtolower($r->status) === 'finalized' ? 'View' : 'Continue' }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="muted">No review records yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="pad">
                        {{ $reviews->links() }}
                    </div>
                </div>
            </div>
        </main>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const chartLabels = @json($chartLabels);
        const chartData = @json($chartData);

        // Finalized Reviews Bar Chart
        const ctx = document.getElementById('finalizedReviewsChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Finalized Reviews',
                        data: chartData,
                        backgroundColor: 'rgba(25, 135, 84, 0.6)',
                        borderColor: 'rgba(25, 135, 84, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Finalized: ' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                stepSize: 1
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                }
            });
        }
    </script>
@endpush
