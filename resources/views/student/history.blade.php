@extends('layouts.app')

@section('title', 'Submission History')

@section('content')
<div class="student-history-page">
    <div class="container">
        @include('partials.sidebar')

        <main class="main-content">
            <h2 class="page-title">Submission History</h2>

            <div class="history-table-wrapper">
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
                                <th>Details</th>
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

                            $details = $submission->description ?? '';
                            $remarks = $submission->remarks
                            ?? optional($submission->latestHistory)->remarks
                            ?? '';
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

                                {{-- Details (student’s description of submission) --}}
                                <td title="{{ $details }}">
                                    {{ \Illuminate\Support\Str::limit($details, 40) ?: '—' }}
                                </td>

                                {{-- Remarks (assessor feedback) --}}
                                <td title="{{ $remarks }}">
                                    {{ \Illuminate\Support\Str::limit($remarks, 40) ?: '—' }}
                                </td>

                                {{-- View file --}}
                                <td>
                                    <button class="history-btn view-btn"
                                        data-file="{{ route('student.submissions.preview', $submission->id) }}">
                                        View
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

            </div>

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
    }

    .history-table-wrapper {
        margin-top: 16px;
        overflow-x: auto;
    }

    .history-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        border-radius: 6px;
        text-align: left;
    }

    .history-table th,
    .history-table td {
        padding: 12px 10px;
        border-bottom: 1px solid #ddd;
        vertical-align: middle;
    }

    .history-status.approved {
        color: #2ecc71;
        font-weight: 600;
    }

    .history-status.pending {
        color: #f39c12;
        font-weight: 600;
    }

    .history-status.rejected {
        color: #e74c3c;
        font-weight: 600;
    }

    .history-btn {
        background: #0056b3;
        color: #fff;
        border: none;
        padding: 6px 14px;
        border-radius: 6px;
        cursor: pointer;
        margin-right: 6px;
        font-size: 0.9rem;
        transition: background 0.2s;
        white-space: nowrap;
    }

    .history-btn:hover {
        background: #00448f;
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