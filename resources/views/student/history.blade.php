@extends('layouts.app')

@section('title', 'Submission History')

@section('content')
<div class="student-history-page">
    <div class="container">
        @include('partials.sidebar')

        <main class="main-content">
            <h2 class="page-title">Submission History</h2>

            <div class="history-table-wrapper">
                <table class="history-table w-100" id="historyTable">
                        <thead>
                            <tr>
                                <th>Date Submitted</th>
                                <th>Date of Activity</th>
                                <th>Activity Type</th>
                                <th>Organization</th>
                                <th>Role in Activity</th>
                                <th>Status</th>
                                <th>Score</th>
                                <th>Remarks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($submissions as $submission)
                            @php
                            $submittedAt = $submission->submitted_at ?? $submission->created_at;

                            // meta is cast to array in model
                            $meta = is_array($submission->meta)
                            ? $submission->meta
                            : (json_decode($submission->meta ?? '[]', true) ?: []);

                            $activityType = $meta['activity_type']
                            ?? $submission->activity_title
                            ?? optional($submission->category)->title
                            ?? '—';

                            $orgName = $meta['organizing_body']
                            ?? optional($submission->leadership)->organization_name
                            ?? '—';

                            $roleInActivity = $meta['role_in_activity']
                            ?? optional($submission->leadership)->position
                            ?? '—';

                            $dateOfActivity = isset($meta['date_of_activity']) && $meta['date_of_activity']
                            ? \Illuminate\Support\Carbon::parse($meta['date_of_activity'])->format('F d, Y')
                            : '—';

                            $status = $submission->status
                            ?? optional($submission->latestHistory)->new_status
                            ?? 'Pending';

                            $remarks = $submission->remarks
                            ?? optional($submission->latestHistory)->remarks
                            ?? '';

                            // Get score from latest review
                            $latestReview = $submission->reviews->first();
                            $score = $latestReview ? (float) $latestReview->score : null;
                            @endphp

                            <tr>
                                {{-- Date submitted to system --}}
                                <td>{{ optional($submittedAt)->format('F d, Y') }}</td>

                                {{-- Date of the actual activity --}}
                                <td>{{ $dateOfActivity }}</td>

                                {{-- Activity type from meta --}}
                                <td>{{ $activityType }}</td>

                                {{-- Organizing body from meta --}}
                                <td>{{ $orgName }}</td>

                                {{-- Role in activity --}}
                                <td>{{ $roleInActivity }}</td>

                                {{-- Status --}}
                                <td>
                                    <span class="history-status {{ strtolower($status) }}">
                                        {{ strtoupper($status) }}
                                    </span>
                                </td>

                                {{-- Score --}}
                                <td>
                                    @if($score !== null)
                                        {{ number_format($score, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>

                                {{-- Remarks (assessor feedback) --}}
                                <td title="{{ $remarks }}">
                                    {{ \Illuminate\Support\Str::limit($remarks, 40) ?: '—' }}
                                </td>

                                {{-- View file --}}
                                <td>
                                    <button class="history-btn view-btn"
                                        data-file="{{ route('student.submissions.preview', $submission->id) }}"
                                        title="View Submission">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">No submissions yet.</td>
                            </tr>
                            @endforelse
                        </tbody>

                    </table>
            </div>

            {{-- Pagination --}}
            @php
                $isPaginator = isset($submissions) && ($submissions instanceof \Illuminate\Pagination\LengthAwarePaginator || method_exists($submissions, 'links'));
            @endphp
            @if($isPaginator)
            <div class="pagination-wrapper mt-4">
                {{-- Previous --}}
                @if($submissions->onFirstPage())
                <span class="page-btn disabled"><i class="fas fa-chevron-left"></i> Previous</span>
                @else
                <a href="{{ $submissions->previousPageUrl() }}" class="page-btn">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
                @endif

                {{-- Page numbers --}}
                @php
                    $links = $submissions->links();
                    $elements = $links->elements ?? [];
                @endphp
                @foreach($elements as $element)
                    @if(is_string($element))
                    <span class="page-btn disabled">{{ $element }}</span>
                    @endif

                    @if(is_array($element))
                        @foreach($element as $page => $url)
                            @if($page == $submissions->currentPage())
                            <span class="page-btn active">{{ $page }}</span>
                            @else
                            <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next --}}
                @if($submissions->hasMorePages())
                <a href="{{ $submissions->nextPageUrl() }}" class="page-btn">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
                @else
                <span class="page-btn disabled">Next <i class="fas fa-chevron-right"></i></span>
                @endif
            </div>

            <div class="text-right small text-muted mt-2">
                Showing {{ $submissions->firstItem() ?? 0 }} – {{ $submissions->lastItem() ?? 0 }}
                of {{ $submissions->total() }} submissions
            </div>
            @endif

            {{-- Slide-in file viewer (just the file) --}}
            <div id="pdfViewerContainer" class="pdf-viewer-container hidden">
                <iframe id="pdfViewer" src="" frameborder="0"></iframe>
            </div>

            {{-- Floating Close Button --}}
            <button id="closeViewer" class="close-viewer-btn hidden" title="Close Preview">
                <i class="fas fa-times"></i>
            </button>
        </main>
    </div>
</div>

{{-- =============== STYLES =============== --}}
<style>
    .student-history-page {
        display: flex;
        width: 100%;
    }

    .student-history-page .container {
        display: flex;
    }

    .page-title {
        margin-bottom: 12px;
        font-size: 1.4rem;
        font-weight: 600;
        color: #8B0000;
        text-align: left;
    }

    body.dark-mode .page-title {
        color: #f1f1f1;
    }

    /* Table container matching assessor/admin design */
    .history-table-wrapper {
        margin: 16px 0;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        overflow-x: hidden;
        overflow-y: visible;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }

    body.dark-mode .history-table-wrapper {
        background: #2a2a2a;
        border: 1px solid #555;
    }

    /* Table styling matching assessor/admin tables */
    .history-table {
        margin: 0;
        width: 100%;
        max-width: 100%;
        background: white;
        table-layout: auto;
        border-collapse: collapse;
    }

    body.dark-mode .history-table {
        background: #2a2a2a;
    }

    .history-table thead {
        background-color: #8B0000 !important;
    }

    body.dark-mode .history-table thead {
        background-color: #8B0000 !important;
    }

    .history-table thead th {
        padding: 0.7rem 0.75rem;
        font-weight: 600;
        color: white !important;
        border-bottom: 1px solid white !important;
        border-right: 1px solid rgba(255, 255, 255, 0.2) !important;
        font-size: 0.9rem;
        background-color: #8B0000 !important;
        text-align: left;
        white-space: nowrap;
    }

    body.dark-mode .history-table thead th {
        color: white !important;
        border-bottom: 1px solid white !important;
        border-right: 1px solid rgba(255, 255, 255, 0.2) !important;
        background-color: #8B0000 !important;
    }

    .history-table thead th:last-child {
        border-right: none !important;
    }

    .history-table tbody td {
        padding: 0.65rem 0.75rem;
        font-size: 0.85rem;
        color: #333;
        border-bottom: 1px solid #e9ecef;
        border-right: 1px solid #e9ecef;
        vertical-align: middle;
        background: white;
    }

    body.dark-mode .history-table tbody td {
        background: #363636 !important;
        color: #f0f0f0 !important;
        border-bottom: 1px solid #555 !important;
        border-right: 1px solid #555 !important;
    }

    .history-table tbody td:last-child {
        border-right: none;
    }

    .history-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    body.dark-mode .history-table tbody tr:hover {
        background-color: #404040 !important;
    }

    /* Status badges */
    .history-status.approved {
        color: #2ecc71;
        font-weight: 600;
    }

    body.dark-mode .history-status.approved {
        color: #4ade80;
    }

    .history-status.pending {
        color: #f39c12;
        font-weight: 600;
    }

    body.dark-mode .history-status.pending {
        color: #fbbf24;
    }

    .history-status.rejected {
        color: #e74c3c;
        font-weight: 600;
    }

    body.dark-mode .history-status.rejected {
        color: #f87171;
    }

    /* Score column styling */
    .history-table tbody td:nth-child(7) {
        text-align: center;
        font-weight: 500;
    }

    /* View button - Icon only, maroon */
    .history-btn {
        background: #8B0000;
        color: #fff;
        border: none;
        padding: 8px 10px;
        border-radius: 6px;
        cursor: pointer;
        margin-right: 6px;
        font-size: 0.9rem;
        transition: all 0.2s;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
    }

    .history-btn:hover {
        background: #6B0000;
        transform: scale(1.05);
    }

    .history-btn i {
        font-size: 1rem;
    }

    body.dark-mode .history-btn {
        background: #8B0000;
        color: #fff;
    }

    body.dark-mode .history-btn:hover {
        background: #6B0000;
    }

    /* Empty state */
    body.dark-mode .text-center {
        color: #e0e0e0;
    }

    body.dark-mode .main-content {
        color: #f1f1f1;
    }

    /* Viewer panel */
    .pdf-viewer-container {
        position: fixed;
        top: 80px;
        right: 0;
        width: 50%;
        height: calc(100vh - 80px);
        background: #fff;
        box-shadow: -3px 0 10px rgba(0, 0, 0, 0.25);
        z-index: 1100;
        display: flex;
        flex-direction: column;
        border-left: 2px solid #ddd;
        transition: transform 0.3s ease-in-out;
        transform: translateX(100%);
    }

    .pdf-viewer-container.active {
        transform: translateX(0);
    }

    .pdf-viewer-container iframe {
        flex: 1;
        width: 100%;
        height: 100%;
    }

    body.dark-mode .pdf-viewer-container {
        background: #2d2d2d;
        border-left: 2px solid #555;
    }

    body.dark-mode .pdf-viewer-container iframe {
        background: #2d2d2d;
    }

    /* Close button */
    .close-viewer-btn {
        position: fixed;
        top: 90px;
        right: 52%;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #c0392b;
        color: #fff;
        border: none;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        z-index: 1200;
        transition: opacity 0.2s, transform 0.3s ease;
    }

    .close-viewer-btn.hidden {
        opacity: 0;
        pointer-events: none;
        transform: scale(0.8);
    }

    .close-viewer-btn:hover {
        background: #a93226;
    }

    body.dark-mode .close-viewer-btn {
        background: #dc2626;
        color: #fff;
    }

    body.dark-mode .close-viewer-btn:hover {
        background: #b91c1c;
    }

    /* Pagination Styles */
    .pagination-wrapper {
        display: flex !important;
        justify-content: flex-end;
        align-items: center;
        gap: 0.5rem;
        margin-top: 1.5rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        width: 100%;
        visibility: visible !important;
    }

    .page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0.5rem 0.75rem;
        background: white;
        color: #333;
        border: 1px solid #ddd;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .page-btn:hover:not(.disabled):not(.active) {
        background: #f8f9fa;
        border-color: #8B0000;
        color: #8B0000;
    }

    .page-btn.active {
        background: #8B0000;
        color: white;
        border-color: #8B0000;
        font-weight: 600;
    }

    .page-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f5f5f5;
    }

    body.dark-mode .page-btn {
        background: #363636;
        color: #f0f0f0;
        border-color: #555;
    }

    body.dark-mode .page-btn:hover:not(.disabled):not(.active) {
        background: #404040;
        border-color: #8B0000;
        color: #f0f0f0;
    }

    body.dark-mode .page-btn.active {
        background: #8B0000;
        color: white;
        border-color: #8B0000;
    }

    body.dark-mode .page-btn.disabled {
        background: #2a2a2a;
        opacity: 0.5;
    }

    body.dark-mode .text-muted {
        color: #aaa !important;
    }

    /* Main content layout */
    .main-content {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    body.dark-mode .main-content {
        color: #f1f1f1;
    }

    /* Ensure container doesn't cause horizontal scroll */
    .student-history-page .container {
        overflow-x: hidden;
        max-width: 100%;
    }

    body {
        overflow-x: hidden;
    }
</style>

{{-- =============== JS =============== --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const viewerContainer = document.getElementById('pdfViewerContainer');
        const viewerFrame = document.getElementById('pdfViewer');
        const closeBtn = document.getElementById('closeViewer');

        // Open file viewer
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const fileUrl = btn.getAttribute('data-file');

                viewerFrame.src = fileUrl;
                viewerContainer.classList.add('active');
                closeBtn.classList.remove('hidden');
                document.body.classList.add('collapsed'); // auto-collapse sidebar if you use that class
            });
        });

        // Close viewer
        closeBtn.addEventListener('click', () => {
            viewerContainer.classList.remove('active');
            closeBtn.classList.add('hidden');
            viewerFrame.src = '';
            document.body.classList.remove('collapsed');
        });
    });
</script>

@endsection