@extends('layouts.app')

@section('title', 'Submission History')

@section('content')
<div class="student-history-page">
    <div class="container">
        <main class="main-content">
            <h2 class="page-title">Submission History</h2>

            <div class="history-table-wrapper">
                <table class="history-table w-100" id="historyTable">
                    <thead>
                        <tr>
                            <th>Date Submitted</th>
                            <th>Activity Type</th>
                            <th>Organization</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Example rows --}}
                        @php
                        $records = [
                        ['date' => 'September 2, 2025', 'type' => 'Training', 'org' => 'CICS Society', 'role' => 'Participant', 'status' => 'Approved'],
                        ['date' => 'August 10, 2025', 'type' => 'Seminar', 'org' => 'Google Dev Club', 'role' => 'Speaker', 'status' => 'Pending'],
                        ['date' => 'July 15, 2025', 'type' => 'Workshop', 'org' => 'USEP DevTeam', 'role' => 'Lead', 'status' => 'Rejected']
                        ];
                        @endphp

                        @foreach ($records as $record)
                        <tr>
                            <td>{{ $record['date'] }}</td>
                            <td>{{ $record['type'] }}</td>
                            <td>{{ $record['org'] }}</td>
                            <td>{{ $record['role'] }}</td>
                            <td>
                                <span class="history-status {{ strtolower($record['status']) }}">
                                    {{ $record['status'] }}
                                </span>
                            </td>
                            <td>
                                @if (in_array($record['status'], ['Pending', 'Approved']))
                                <button class="history-btn view-btn" data-file="{{ asset('samples/training.pdf') }}">View</button>
                                @elseif ($record['status'] === 'Rejected')
                                <button class="history-btn view-btn" data-file="{{ asset('samples/training.pdf') }}">View</button>
                                <button class="history-btn resubmit-btn">Resubmit</button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- PDF Viewer -->
            <div id="pdfViewerContainer" class="pdf-viewer-container hidden">
                <iframe id="pdfViewer" src="" frameborder="0"></iframe>
            </div>

            <!-- Floating Close Button -->
            <button id="closeViewer" class="close-viewer-btn hidden" title="Close Preview">
                <i class="fas fa-times"></i>
            </button>
        </main>
    </div>
</div>

<style>
    /* ====================== HISTORY PAGE STYLES ====================== */
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

    /* === Table === */
    .student-history-page .history-table-wrapper {
        margin-top: 16px;
        overflow-x: auto;
    }

    .student-history-page .history-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        border-radius: 6px;
        text-align: left;
    }

    .student-history-page .history-table th,
    .student-history-page .history-table td {
        padding: 12px 10px;
        border-bottom: 1px solid #ddd;
        vertical-align: middle;
    }

    .student-history-page .history-status.approved {
        color: #2ecc71;
        font-weight: 600;
    }

    .student-history-page .history-status.pending {
        color: #f39c12;
        font-weight: 600;
    }

    .student-history-page .history-status.rejected {
        color: #e74c3c;
        font-weight: 600;
    }

    /* === Buttons === */
    .student-history-page .history-btn {
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
        /* prevent cutting text */
    }

    .student-history-page .history-btn:hover {
        background: #00448f;
    }

    .student-history-page .resubmit-btn {
        background: #6c757d;
    }

    .student-history-page .resubmit-btn:hover {
        background: #565e64;
    }

    /* === PDF Viewer === */
    .student-history-page .pdf-viewer-container {
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

    .student-history-page .pdf-viewer-container.active {
        transform: translateX(0);
    }

    .student-history-page .pdf-viewer-container iframe {
        flex: 1;
        width: 100%;
        height: 100%;
    }

    /* === Floating Close Button === */
    .student-history-page .close-viewer-btn {
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

    .student-history-page .close-viewer-btn.hidden {
        opacity: 0;
        pointer-events: none;
        transform: scale(0.8);
    }

    .student-history-page .close-viewer-btn:hover {
        background: #a93226;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const viewerContainer = document.getElementById('pdfViewerContainer');
        const viewerFrame = document.getElementById('pdfViewer');
        const closeBtn = document.getElementById('closeViewer');

        // Open PDF viewer (for View buttons)
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const fileUrl = btn.getAttribute('data-file');
                viewerFrame.src = fileUrl;
                viewerContainer.classList.add('active');
                closeBtn.classList.remove('hidden');
                document.body.classList.add('collapsed'); // auto-collapse sidebar
            });
        });

        // Close viewer
        closeBtn.addEventListener('click', () => {
            viewerContainer.classList.remove('active');
            closeBtn.classList.add('hidden');
            viewerFrame.src = '';
            document.body.classList.remove('collapsed'); // restore sidebar
        });

        // Demo resubmit action
        document.querySelectorAll('.resubmit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                alert('Resubmission form popup goes here.');
            });
        });
    });
</script>
@endsection