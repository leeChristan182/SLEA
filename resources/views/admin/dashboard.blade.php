@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('head')
    {{-- Belt-and-suspenders redirect (routes already use role:admin middleware) --}}
    @if(auth()->check() && auth()->user()->role !== 'admin')
        @php
            $redir = auth()->user()->role === 'assessor'
                ? route('assessor.profile')
                : route('student.profile');
        @endphp
        <meta http-equiv="refresh" content="0; url={{ $redir }}">
    @endif

    <style>
        /* ===================== DASHBOARD CSS ===================== */
        .dash-wrap {
            padding: 8px 18px 20px;
        }

        .dash-title {
            font-weight: 800;
            letter-spacing: .2px;
        }

        .dash-subtitle {
            color: rgba(0, 0, 0, .55);
            font-size: 13px;
        }

        body.dark-mode .dash-subtitle {
            color: rgba(255, 255, 255, .7);
        }

        .dash-grid {
            display: grid;
            gap: 14px;
        }

        .dash-grid.top {
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }

        .dash-grid.mid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dash-grid.charts {
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 18px;
        }

        @media (max-width: 1200px) {
            .dash-grid.charts {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .dash-grid.top {
                grid-template-columns: 1fr;
            }

            .dash-grid.mid {
                grid-template-columns: 1fr;
            }
        }

        .dash-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid rgba(0, 0, 0, .08);
            box-shadow: 0 6px 22px rgba(0, 0, 0, .06);
            overflow: hidden;
            margin-bottom: 12px;
        }

        body.dark-mode .dash-card {
            background: #2a2a2a;
            border-color: rgba(255, 255, 255, .10);
            box-shadow: 0 6px 22px rgba(0, 0, 0, .25);
        }

        .dash-card .pad {
            padding: 14px 16px;
        }

        .dash-kpi {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .dash-kpi .label {
            font-size: 12px;
            color: rgba(0, 0, 0, .55);
        }

        body.dark-mode .dash-kpi .label {
            color: rgba(255, 255, 255, .65);
        }

        .dash-kpi .value {
            font-size: 34px;
            font-weight: 900;
            line-height: 1;
        }

        .dash-kpi .icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: rgba(123, 0, 0, .10);
            color: #7b0000;
            flex: 0 0 auto;
        }

        body.dark-mode .dash-kpi .icon {
            background: rgba(255, 255, 255, .08);
            color: #fff;
        }

        .dash-chip {
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid rgba(0, 0, 0, .10);
            color: rgba(0, 0, 0, .65);
            background: rgba(0, 0, 0, .02);
        }

        body.dark-mode .dash-chip {
            border-color: rgba(255, 255, 255, .12);
            color: rgba(255, 255, 255, .75);
            background: rgba(255, 255, 255, .06);
        }

        .chart-wrap {
            padding: 18px 18px 20px;
        }

        .chart-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .chart-title {
            font-weight: 800;
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

        .subtext {
            font-size: 13px;
            color: rgba(0, 0, 0, .6);
        }

        body.dark-mode .subtext {
            color: rgba(255, 255, 255, .7);
        }

        .btn-link-chip {
            padding: 6px 10px;
            border-radius: 10px;
            background: rgba(123, 0, 0, 0.08);
            color: #7b0000;
            text-decoration: none;
            font-weight: 700;
            font-size: 12px;
        }

        .btn-link-chip:hover {
            background: rgba(123, 0, 0, 0.14);
            color: #7b0000;
        }

        body.dark-mode .btn-link-chip {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }

        body.dark-mode .btn-link-chip:hover {
            background: rgba(255, 255, 255, 0.14);
            color: #fff;
        }

        .btn-link-chip:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(123, 0, 0, 0.1);
        }
    </style>
@endsection

@section('content')
    <div class="container">
        @include('partials.sidebar')

        <main class="main-content">
            <div class="dash-wrap">
                {{-- Header removed per request --}}

                {{-- TOP KPI CARDS --}}
                <div class="dash-grid top mb-3">
                    <div class="dash-card">
                        <div class="pad dash-kpi">
                            <div>
                                <div class="label">Approved Students</div>
                                <div class="value">{{ $roleCounts['student'] }}</div>
                            </div>
                            <div class="icon"><i class="fas fa-user-graduate"></i></div>
                        </div>
                    </div>

                    <div class="dash-card">
                        <div class="pad dash-kpi">
                            <div>
                                <div class="label">Approved Assessors</div>
                                <div class="value">{{ $roleCounts['assessor'] }}</div>
                            </div>
                            <div class="icon"><i class="fas fa-user-tie"></i></div>
                        </div>
                    </div>

                    <div class="dash-card">
                        <div class="pad dash-kpi" style="flex-wrap: wrap;">
                            <div style="flex: 1; min-width: 0;">
                                <div class="label">New Submissions</div>
                                <div class="value">{{ $initialValidationQueueCount }}</div>
                                <div class="subtext">Pending in Initial Validation queue</div>
                            </div>
                            <div style="display: flex; align-items: center;">
                                <a class="btn-link-chip" href="{{ url('/admin/initial-validation') }}">View Queue</a>
                            </div>
                        </div>
                    </div>

                    <div class="dash-card">
                        <div class="pad dash-kpi" style="flex-wrap: wrap;">
                            <div style="flex: 1; min-width: 0;">
                                <div class="label">Pending Approval Queue</div>
                                <div class="value">{{ $pendingApproval }}</div>
                                <div class="subtext">Validated, awaiting final approval</div>
                            </div>
                            <div style="display: flex; align-items: center;">
                                <a class="btn-link-chip" href="{{ url('/admin/final-review') }}">View Queue</a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SLEA STATUS --}}
                <div class="dash-grid mid mb-3">
                    <div class="dash-card">
                        <div class="pad dash-kpi">
                            <div>
                                <div class="label">SLEA Qualified</div>
                                <div class="value">{{ $qualifiedCount }}</div>
                            </div>
                            <div class="icon"><i class="fas fa-award"></i></div>
                        </div>
                    </div>

                    <div class="dash-card">
                        <div class="pad dash-kpi">
                            <div class="w-100">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="label">SLEA Not Qualified</div>
                                        <div class="value">{{ $notQualifiedCount }}</div>
                                    </div>
                                    <div class="icon"><i class="fas fa-circle-xmark"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submission Insights --}}
                <div class="dash-grid charts mt-3">
                    <div class="dash-card">
                        <div class="chart-wrap">
                            <div class="chart-head mb-2">
                                <div class="chart-title">College Submission Breakdown</div>
                                <div class="chart-meta">Total: <b>{{ $collegeTotal }}</b></div>
                            </div>
                            <canvas id="collegePieChart" height="230"></canvas>
                            @if(empty($collegeData))
                                <div class="dash-alert mt-2">No submissions yet.</div>
                            @endif
                        </div>
                    </div>

                    <div class="dash-card">
                        <div class="chart-wrap">
                            <div class="chart-head mb-2">
                                <div class="chart-title">Submission Status</div>
                                <div class="chart-meta">Workflow health</div>
                            </div>
                            <canvas id="statusDonutChart" height="230"></canvas>
                        </div>
                    </div>
                </div>

                {{-- CHARTS (Admin Decisions & Scores) --}}
                <div class="dash-grid charts">
                    {{-- Admin Final Decisions --}}
                    <div class="dash-card">
                        <div class="chart-wrap">
                            <div class="chart-head mb-2">
                                <div class="chart-title">Admin Final Decisions</div>
                                <span class="dash-chip">FinalReview</span>
                            </div>
                            <canvas id="finalDecisionsChart" height="210"></canvas>
                        </div>
                    </div>

                    {{-- Assessor Scores Histogram --}}
                    <div class="dash-card">
                        <div class="chart-wrap">
                            <div class="chart-head mb-2">
                                <div class="chart-title">Score Distribution (Finalized)</div>
                                <div class="chart-meta">Avg: <b>{{ number_format($avgScore, 2) }}</b></div>
                            </div>
                            <canvas id="scoresChart" height="210"></canvas>

                            @if(empty($scores))
                                <div class="dash-alert">
                                    No finalized scores yet. This will populate once assessors finalize reviews.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const finalDecisions = @json($finalDecisions);
        const scores = @json($scores);
        const collegeLabels = @json($collegeLabels);
        const collegeData = @json($collegeData);
        const submissionStatus = @json($submissionStatus);

        // --- Admin Final Decisions doughnut ---
        new Chart(document.getElementById('finalDecisionsChart'), {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'Not Qualified'],
                datasets: [{
                    data: [finalDecisions.approved, finalDecisions.not_qualified],
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // --- Scores histogram (10 bins) ---
        function buildHistogram(values, bins = 10) {
            if (!values || values.length === 0) return { labels: [], counts: [] };

            const min = Math.min(...values);
            const max = Math.max(...values);
            const span = (max - min) || 1;
            const step = span / bins;

            const counts = Array(bins).fill(0);
            for (const v of values) {
                let idx = Math.floor((v - min) / step);
                if (idx >= bins) idx = bins - 1;
                if (idx < 0) idx = 0;
                counts[idx]++;
            }

            const labels = Array.from({ length: bins }, (_, i) => {
                const a = (min + step * i);
                const b = (min + step * (i + 1));
                return `${a.toFixed(1)}–${b.toFixed(1)}`;
            });

            return { labels, counts };
        }

        const hist = buildHistogram(scores, 10);

        new Chart(document.getElementById('scoresChart'), {
            type: 'bar',
            data: {
                labels: hist.labels,
                datasets: [{
                    label: 'Students',
                    data: hist.counts
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });

        // --- College submission breakdown (Pie) ---
        if (collegeLabels.length && collegeData.length) {
            new Chart(document.getElementById('collegePieChart'), {
                type: 'pie',
                data: {
                    labels: collegeLabels,
                    datasets: [{
                        data: collegeData,
                        backgroundColor: [
                            '#7b0000', '#b30000', '#f9bd3d', '#ff8c42', '#4f46e5',
                            '#0ea5e9', '#10b981', '#f472b6', '#6366f1', '#f97316'
                        ],
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const total = collegeData.reduce((a, b) => a + b, 0) || 1;
                                    const val = ctx.parsed;
                                    const pct = ((val / total) * 100).toFixed(1);
                                    return `${ctx.label}: ${val} (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // --- Submission status (Donut) ---
        const statusLabels = ['Approved', 'Rejected', 'In Review', 'Complete'];
        const statusValues = [
            submissionStatus.approved || 0,
            submissionStatus.rejected || 0,
            submissionStatus.in_review || 0,
            submissionStatus.complete || 0,
        ];

        new Chart(document.getElementById('statusDonutChart'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: ['#16a34a', '#dc2626', '#f59e0b', '#2563eb']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const total = statusValues.reduce((a, b) => a + b, 0) || 1;
                                const val = ctx.parsed;
                                const pct = ((val / total) * 100).toFixed(1);
                                return `${ctx.label}: ${val} (${pct}%)`;
                            }
                        }
                    }
                },
                cutout: '55%'
            }
        });
    </script>
@endpush
