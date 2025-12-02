{{-- resources/views/admin/system-monitoring.blade.php --}}
@extends('layouts.app')

@section('title', 'System Monitoring & Logs')

@section('content')
<div class="container">
    @include('partials.sidebar')

    <main class="main-content">
        <div class="page-header">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1>System Monitoring &amp; Logs</h1>
                    <p style="color: #6b7280; margin: 0.5rem 0 0 0; font-size: 0.9rem;">
                        View recorded activities performed by admins, assessors, and students.
                    </p>
                </div>
                <form action="{{ route('admin.system-logs.clear') }}"
                      method="POST"
                      onsubmit="return confirm('Are you sure you want to clear ALL system logs?');"
                      style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-clear" style="background-color: #dc3545; color: white;">
                        <i class="fas fa-trash"></i> Clear All Logs
                    </button>
                </form>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Controls Section --}}
        <div class="controls-section">
            <div class="filter-controls">
                <div class="filter-group">
                    <label for="roleFilter">User Role</label>
                    <select name="role" id="roleFilter" class="form-select">
                        <option value="">All Roles</option>
                        @foreach(['admin' => 'Admin', 'assessor' => 'Assessor', 'student' => 'Student'] as $key => $label)
                            <option value="{{ $key }}" @selected(request('role') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="activityFilter">Activity</label>
                    <select name="activity_type" id="activityFilter" class="form-select">
                        <option value="">All Activities</option>
                        @foreach(['Login', 'Logout', 'Create', 'Update', 'Delete'] as $type)
                            <option value="{{ $type }}" @selected(request('activity_type') === $type)>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="fromDate">From</label>
                    <input type="date"
                           name="from"
                           id="fromDate"
                           class="form-control"
                           value="{{ request('from') }}">
                </div>

                <div class="filter-group">
                    <label for="toDate">To</label>
                    <input type="date"
                           name="to"
                           id="toDate"
                           class="form-control"
                           value="{{ request('to') }}">
                </div>
            </div>

            <div class="search-controls">
                <div class="search-group">
                    <input
                        type="text"
                        name="q"
                        id="searchInput"
                        class="form-control"
                        placeholder="Search by user or description..."
                        value="{{ request('q') }}"
                    >
                    <button type="submit" form="filterForm" class="btn-search-maroon search-btn-attached" title="Search">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('admin.system-logs.index') }}" class="btn-clear" title="Clear filters">
                        Clear
                    </a>
                </div>
            </div>
        </div>

        {{-- Filter Form --}}
        <form method="GET" action="{{ route('admin.system-logs.index') }}" id="filterForm" style="display: none;">
            <input type="hidden" name="role" id="roleHidden" value="{{ request('role') }}">
            <input type="hidden" name="activity_type" id="activityHidden" value="{{ request('activity_type') }}">
            <input type="hidden" name="from" id="fromHidden" value="{{ request('from') }}">
            <input type="hidden" name="to" id="toHidden" value="{{ request('to') }}">
            <input type="hidden" name="q" id="searchHidden" value="{{ request('q') }}">
        </form>

        {{-- Logs Table --}}
        <div class="submissions-table-container">
            <table class="table submissions-table">
                <thead>
                    <tr>
                        <th>Date &amp; Time</th>
                        <th>Role</th>
                        <th>User</th>
                        <th>Activity</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d g:i A') }}</td>
                            <td class="text-capitalize">{{ $log->user_role ?? 'N/A' }}</td>
                            <td>{{ $log->user_name }}</td>
                            <td>{{ $log->activity_type }}</td>
                            <td>{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No logs found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
            <div class="pagination-container" data-pagination-container>
                <div class="pagination-info">
                    Showing {{ $logs->firstItem() ?? 0 }} – {{ $logs->lastItem() ?? 0 }}
                    of {{ $logs->total() }} entries
                </div>

                <div class="unified-pagination">
                    @if($logs->onFirstPage())
                        <button class="btn-nav" disabled>
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                    @else
                        <a href="{{ $logs->previousPageUrl() }}" class="btn-nav">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    @endif

                    <span class="pagination-pages">
                        @php
                            $currentPage = $logs->currentPage();
                            $lastPage = $logs->lastPage();
                            $start = max(1, $currentPage - 2);
                            $end = min($lastPage, $currentPage + 2);
                        @endphp

                        @if($start > 1)
                            <a href="{{ $logs->url(1) }}" class="page-btn">1</a>
                            @if($start > 2)
                                <span class="page-btn disabled">...</span>
                            @endif
                        @endif

                        @for($i = $start; $i <= $end; $i++)
                            @if($i == $currentPage)
                                <span class="page-btn active">{{ $i }}</span>
                            @else
                                <a href="{{ $logs->url($i) }}" class="page-btn">{{ $i }}</a>
                            @endif
                        @endfor

                        @if($end < $lastPage)
                            @if($end < $lastPage - 1)
                                <span class="page-btn disabled">...</span>
                            @endif
                            <a href="{{ $logs->url($lastPage) }}" class="page-btn">{{ $lastPage }}</a>
                        @endif
                    </span>

                    @if($logs->hasMorePages())
                        <a href="{{ $logs->nextPageUrl() }}" class="btn-nav">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <button class="btn-nav" disabled>
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </main>
</div>

<link rel="stylesheet" href="{{ asset('css/pending-submissions.css') }}">
<style>
    /* Prevent horizontal scrollbar */
    body {
        overflow-x: hidden !important;
    }

    .container {
        overflow-x: hidden !important;
        max-width: 100% !important;
    }

    .main-content {
        max-width: 100% !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
    }

    /* System Monitoring Specific Styles */
    .page-header h1 {
        color: #7E0308;
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    body.dark-mode .page-header h1 {
        color: #F9BD3D;
    }

    /* Filter group width adjustments */
    .filter-group select,
    .filter-group input[type="date"] {
        width: 180px;
        max-width: 180px;
        min-width: 150px;
    }

    /* Search input width */
    .search-group .form-control {
        max-width: 350px;
    }

    /* Controls section - prevent overflow */
    .controls-section {
        max-width: 100% !important;
        overflow-x: hidden !important;
        flex-wrap: wrap !important;
    }

    /* Table container - prevent overflow */
    .submissions-table-container {
        max-width: 100% !important;
        overflow-x: auto !important;
        box-sizing: border-box !important;
    }

    .submissions-table {
        width: 100% !important;
        table-layout: auto !important;
    }

    /* Ensure table cells don't overflow */
    .submissions-table td,
    .submissions-table th {
        word-wrap: break-word;
        overflow-wrap: break-word;
        white-space: normal;
    }

    /* Specific column widths for better layout */
    .submissions-table th:nth-child(1),
    .submissions-table td:nth-child(1) {
        width: 15%;
        min-width: 150px;
    }

    .submissions-table th:nth-child(2),
    .submissions-table td:nth-child(2) {
        width: 10%;
        min-width: 80px;
    }

    .submissions-table th:nth-child(3),
    .submissions-table td:nth-child(3) {
        width: 20%;
        min-width: 150px;
    }

    .submissions-table th:nth-child(4),
    .submissions-table td:nth-child(4) {
        width: 12%;
        min-width: 100px;
    }

    .submissions-table th:nth-child(5),
    .submissions-table td:nth-child(5) {
        width: 43%;
        min-width: 200px;
    }

    /* Clear All Logs button styling */
    .btn-clear[style*="background-color: #dc3545"] {
        background-color: #dc3545 !important;
        color: white !important;
        border: none;
        border-radius: 6px;
        padding: 0.5rem 1rem;
        height: 38px;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .btn-clear[style*="background-color: #dc3545"]:hover {
        background-color: #c82333 !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
    }

    body.dark-mode .btn-clear[style*="background-color: #dc3545"] {
        background-color: #dc3545 !important;
    }

    body.dark-mode .btn-clear[style*="background-color: #dc3545"]:hover {
        background-color: #bb2d3b !important;
    }

    /* Center main content */
    .main-content {
        margin-left: 260px !important;
        padding: 2rem !important;
        box-sizing: border-box !important;
        transition: margin-left 0.3s ease;
    }

    /* Adjust when sidebar is collapsed */
    body.collapsed .main-content {
        margin-left: 60px !important;
    }

    @media (max-width: 768px) {
        .main-content {
            margin-left: 0 !important;
        }
        
        body.collapsed .main-content {
            margin-left: 0 !important;
        }
    }
</style>

<script>
    // Handle form submission
    document.addEventListener('DOMContentLoaded', function() {
        const roleFilter = document.getElementById('roleFilter');
        const activityFilter = document.getElementById('activityFilter');
        const fromDate = document.getElementById('fromDate');
        const toDate = document.getElementById('toDate');
        const searchInput = document.getElementById('searchInput');
        const filterForm = document.getElementById('filterForm');
        const searchBtn = document.querySelector('.btn-search-maroon[form="filterForm"]');

        // Sync visible inputs with hidden form inputs
        function syncFormInputs() {
            const roleHidden = document.getElementById('roleHidden');
            const activityHidden = document.getElementById('activityHidden');
            const fromHidden = document.getElementById('fromHidden');
            const toHidden = document.getElementById('toHidden');
            const searchHidden = document.getElementById('searchHidden');

            if (roleHidden && roleFilter) roleHidden.value = roleFilter.value;
            if (activityHidden && activityFilter) activityHidden.value = activityFilter.value;
            if (fromHidden && fromDate) fromHidden.value = fromDate.value;
            if (toHidden && toDate) toHidden.value = toDate.value;
            if (searchHidden && searchInput) searchHidden.value = searchInput.value;
        }

        // Handle search button click
        if (searchBtn && filterForm) {
            searchBtn.addEventListener('click', function(e) {
                e.preventDefault();
                syncFormInputs();
                filterForm.submit();
            });
        }

        // Auto-submit on filter change (optional - can be removed if you want manual submit only)
        // if (roleFilter) {
        //     roleFilter.addEventListener('change', function() {
        //         syncFormInputs();
        //         filterForm.submit();
        //     });
        // }

        // if (activityFilter) {
        //     activityFilter.addEventListener('change', function() {
        //         syncFormInputs();
        //         filterForm.submit();
        //     });
        // }
    });
</script>
<script src="{{ asset('js/admin_pagination.js') }}"></script>
@endsection
