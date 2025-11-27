@extends('layouts.app')
@section('title', 'Performance Overview')

@section('content')
    <div class="performance-page">
        <div class="container">
            @include('partials.sidebar')

            <main class="main-content">

                {{-- Flash alerts --}}
                @if (session('success'))
                    <div class="alert alert-success mb-3">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger mb-3">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Performance Overview Title -->
                <div class="performance-overview-title">
                    <h2>Performance Overview</h2>
                </div>

                <!-- Total Score Card -->
                <section class="po-card po-score">
                    <div class="po-medal">
                        <i class="fas fa-medal"></i>
                    </div>
                    <div class="po-points" id="totalPoints">0</div>
                    <div class="po-sub">Total Accumulated<br>Points</div>
                </section>

                <!-- Overall Progress -->
                <section class="po-card">
                    <h3 class="po-title">Overall Progress</h3>
                    <div class="po-progress">
                        <div class="po-progress-fill" id="overallFill" style="width:0%"></div>
                    </div>
                    <div class="po-progress-legend">
                        <span><strong id="earnedLegend">0</strong> Points Earned</span>
                        <span><strong id="maxLegend">0</strong> Total Points</span>
                    </div>
                </section>

                <!-- Category Progress -->
                <section class="po-card">
                    <h3 class="po-title">Points Per Categories</h3>
                    <div id="categoryList" class="po-category-list"></div>
                </section>

                <!-- SLEA Application Status -->
                <section class="po-card po-slea-status">
                    <h3 class="po-title">SLEA Application Status</h3>

                    @php
                        $status = $slea_application_status ?? null;
                        $ready = $ready_for_rating ?? false;
                    @endphp

                    {{-- NOT YET APPLIED / INCOMPLETE --}}
                    @if ((!$status || $status === 'incomplete') && !$ready)
                        <p class="po-slea-text">
                            You have not yet submitted any documents for the Student Leadership Excellence Award.
                        </p>
                        <p class="po-slea-note mt-2">
                            Submit your documents in the Submit section and select "For Final Application" to apply for the award.
                        </p>

                        {{-- PENDING ASSESSOR EVALUATION --}}
                    @elseif ($status === 'pending_assessor_evaluation')
                        <p class="po-slea-text">
                            <strong>Status:</strong>
                            <span class="po-slea-badge po-slea-badge-ready">Pending Assessor Evaluation</span>
                        </p>
                        <p class="po-slea-text">
                            Your application has been submitted and is currently queued for review by your assessor.
                        </p>

                        {{-- PENDING ADMINISTRATIVE VALIDATION --}}
                    @elseif ($status === 'pending_administrative_validation')
                        <p class="po-slea-text">
                            <strong>Status:</strong>
                            <span class="po-slea-badge po-slea-badge-pending">Pending Administrative Validation</span>
                        </p>
                        <p class="po-slea-note mt-1">
                            Your application passed assessor evaluation and is now pending final review by the administrator.
                            No further action is needed from you at this time.
                        </p>

                        {{-- QUALIFIED --}}
                    @elseif ($status === 'qualified')
                        <p class="po-slea-text">
                            <strong>Status:</strong>
                            <span class="po-slea-badge po-slea-badge-awarded">Qualified</span>
                        </p>
                        <p class="po-slea-text">
                            <strong>Congratulations!</strong> You have been recommended to receive the Student Leadership
                            Excellence Award for your graduation.
                        </p>
                        <p class="po-slea-note mt-1">
                            Please coordinate with OSAS for award confirmation and graduation arrangements.
                        </p>

                        {{-- NOT QUALIFIED --}}
                    @elseif ($status === 'not_qualified')
                        <p class="po-slea-text">
                            <strong>Status:</strong>
                            <span class="po-slea-badge po-slea-badge-rejected">Not Qualified</span>
                        </p>
                        <p class="po-slea-note mt-1">
                            Your application has been reviewed and did not meet the requirements for the Student Leadership
                            Excellence Award.
                        </p>

                        {{-- FALLBACK --}}
                    @else
                        <p class="po-slea-text">
                            Your SLEA status is currently being processed.
                        </p>
                    @endif
                </section>
            </main>


        </div>
    </div>
@endsection

{{-- Icons --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

{{-- Inject performance data from controller --}}
<script>
    window.__PERF__ = @json($perfData ?? []);
</script>

<style>
    /* Base card/layout */
    .performance-page .po-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 18px;
        margin-bottom: 16px;
        box-shadow: 0 1px 6px rgba(0, 0, 0, .06);
    }

    body.dark-mode .performance-page .po-card {
        background: #333;
        border-color: #555;
        color: #f1f1f1;
    }

    /* Performance Overview Title */
    .performance-overview-title {
        margin-bottom: 24px;
        padding-bottom: 0;
        border-bottom: none; /* Remove the line */
    }

    .performance-overview-title h2 {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        color: #7b0000; /* Maroon color */
    }

    body.dark-mode .performance-overview-title h2 {
        color: #f9bd3d; /* Gold color for dark mode */
    }
    
    /* Match the width of assessor dashboard main content */
    .performance-page .main-content {
        max-width: 100%;
        width: 100%;
    }

    .performance-page .po-score {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        max-width: 360px;
        margin: 0 auto 16px;
    }

    .performance-page .po-medal {
        width: 76px;
        height: 76px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
        color: #7b0000;
        font-size: 28px;
    }

    body.dark-mode .performance-page .po-medal {
        background: #2b2b2b;
    }

    .performance-page .po-points {
        font-size: 44px;
        font-weight: 800;
        line-height: 1;
        margin-top: 2px;
    }

    .performance-page .po-sub {
        font-size: 12px;
        text-align: center;
        color: #6b7280;
    }

    .performance-page .po-title {
        margin: 0 0 10px;
        color: #111827;
        font-weight: 700;
    }

    body.dark-mode .performance-page .po-title {
        color: #f4f4f4;
    }

    /* Overall progress bar */
    .performance-page .po-progress {
        height: 14px;
        background: #e5e7eb;
        border-radius: 999px;
        overflow: hidden;
    }

    body.dark-mode .performance-page .po-progress {
        background: #444;
    }

    .performance-page .po-progress-fill {
        height: 100%;
        background: #22c55e;
        border-radius: 999px;
        transition: width .6s ease;
    }

    .performance-page .po-progress-legend {
        display: flex;
        justify-content: space-between;
        margin-top: 6px;
        font-size: 12px;
        color: #6b7280;
    }

    /* Category grid */
    .performance-page .po-category-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .performance-page .po-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    @media (max-width: 720px) {
        .performance-page .po-row {
            grid-template-columns: 1fr;
        }
    }

    .performance-page .po-cat {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 10px 12px;
    }

    body.dark-mode .performance-page .po-cat {
        background: #2a2a2a;
        border-color: #444;
    }

    .performance-page .po-cat-title {
        font-size: 12px;
        color: #374151;
        margin-bottom: 8px;
    }

    body.dark-mode .performance-page .po-cat-title {
        color: #d1d5db;
    }

    .performance-page .po-cat-bar {
        height: 10px;
        background: #e5e7eb;
        border-radius: 999px;
        overflow: hidden;
    }

    body.dark-mode .performance-page .po-cat-bar {
        background: #444;
    }

    .performance-page .po-cat-fill {
        height: 100%;
        background: #22c55e;
        border-radius: 999px;
        transition: width .5s ease;
    }

    .performance-page .po-cat-legend {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        margin-top: 6px;
        color: #6b7280;
    }

    /* SLEA status card */
    .performance-page .po-slea-status {
        margin-top: 8px;
    }

    .performance-page .po-slea-text {
        font-size: 13px;
        color: #4b5563;
        margin-bottom: 6px;
    }

    .performance-page .po-slea-note {
        font-size: 12px;
        color: #6b7280;
    }

    body.dark-mode .performance-page .po-slea-text,
    body.dark-mode .performance-page .po-slea-note {
        color: #e5e7eb;
    }

    .performance-page .po-slea-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
    }

    .po-slea-badge-ready {
        background: #fef3c7;
        color: #92400e;
    }

    .po-slea-badge-pending {
        background: #e0f2fe;
        color: #075985;
    }

    .po-slea-badge-awarded {
        background: #dcfce7;
        color: #166534;
    }

    .po-slea-badge-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    /* ---------- Modal ---------- */
    /* ---------- Modal ---------- */
    .performance-page .po-modal {
        position: fixed;
        inset: 0;
        display: none;
        /* hidden by default */
        align-items: center;
        justify-content: center;
        z-index: 50;
    }

    .performance-page .po-modal.show {
        display: flex;
        /* flex center when visible */
    }

    .performance-page .po-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        /* a bit darker for contrast */
    }

    /* Bigger, more readable dialog */
    .performance-page .po-modal-dialog {
        position: relative;
        z-index: 51;
        max-width: 580px;
        /* was 420px */
        width: 90%;
        background: #ffffff;
        border: none;
        border-radius: 18px;
        padding: 26px 28px;
        /* more padding */
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.25);
    }

    body.dark-mode .performance-page .po-modal-dialog {
        background: #111827;
    }

    .performance-page .po-modal-title {
        margin: 0 0 12px;
        font-size: 22px;
        /* was 18px */
        font-weight: 700;
        color: #111827;
    }

    body.dark-mode .performance-page .po-modal-title {
        color: #f9fafb;
    }

    /* Larger text + better spacing */
    .performance-page .po-modal-text {
        font-size: 15px;
        /* was 13px */
        line-height: 1.7;
        color: #374151;
        /* darker */
        margin-bottom: 20px;
        white-space: pre-line;
    }

    body.dark-mode .performance-page .po-modal-text {
        color: #e5e7eb;
    }

    .performance-page .po-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    /* Optional: make buttons a bit taller */
    .performance-page .po-modal-actions .btn {
        padding-top: 8px;
        padding-bottom: 8px;
        min-width: 120px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // -------- Normalize performance data --------
        let rawPerf = window.__PERF__ || {};
        let perfData = {};

        // If the controller accidentally sends an array or an unexpected shape,
        // fall back gracefully instead of throwing JS errors.
        if (!rawPerf || typeof rawPerf !== 'object' || Array.isArray(rawPerf)) {
            perfData = { totals: { earned: 0, max: 0 }, categories: [] };
        } else {
            perfData = rawPerf;
        }

        // Always have a categories array
        const categories = Array.isArray(perfData.categories) ? perfData.categories : [];

        // Build totals from categories if they are missing / invalid
        let totals = perfData.totals;
        if (!totals || typeof totals !== 'object') {
            let earnedSum = 0;
            let maxSum = 0;

            categories.forEach(cat => {
                const earned = Number(cat.earned ?? 0);
                const max = Number(cat.max ?? 0);

                earnedSum += isNaN(earned) ? 0 : earned;
                maxSum += isNaN(max) ? 0 : max;
            });

            totals = { earned: earnedSum, max: maxSum };
            perfData.totals = totals;
        }

        const totalEarned = Number(totals.earned ?? 0);
        const totalMax = Number(totals.max ?? 0);

        // Totals UI
        const totalPts = document.getElementById('totalPoints');
        const overallFill = document.getElementById('overallFill');
        const earnedLegend = document.getElementById('earnedLegend');
        const maxLegend = document.getElementById('maxLegend');

        if (totalPts) totalPts.textContent = totalEarned;
        if (earnedLegend) earnedLegend.textContent = totalEarned;
        if (maxLegend) maxLegend.textContent = totalMax;

        const overallPct = totalMax > 0 ? (totalEarned / totalMax) * 100 : 0;
        if (overallFill) {
            overallFill.style.width = Math.min(100, overallPct).toFixed(2) + '%';
        }

        // -------- Category bars --------
        const holder = document.getElementById('categoryList');
        if (holder) {
            holder.innerHTML = '';

            for (let i = 0; i < categories.length; i += 2) {
                const row = document.createElement('div');
                row.className = 'po-row';

                [categories[i], categories[i + 1]].forEach(cat => {
                    if (!cat) return;

                    const earned = Number(cat.earned ?? 0);
                    const max = Number(cat.max ?? 0);
                    const cpct = max > 0 ? (earned / max) * 100 : 0;

                    const card = document.createElement('div');
                    card.className = 'po-cat';
                    card.innerHTML = `
                        <div class="po-cat-title">${cat.label}</div>
                        <div class="po-cat-bar">
                            <div class="po-cat-fill" style="width:${Math.min(100, cpct).toFixed(2)}%"></div>
                        </div>
                        <div class="po-cat-legend">
                            <span><strong>${earned}</strong> Points Earned</span>
                            <span><strong>${max}</strong> Max Points</span>
                        </div>
                    `;
                    row.appendChild(card);
                });

                holder.appendChild(row);
            }
        }

        // Mark ready functionality removed - now handled in submission form
    });
</script>