@extends('layouts.app')

@section('title', 'System Monitoring and Logs')

@section('content')
<div class="container">
    @include('partials.sidebar')

    <main class="main-content">
        <div class="page-with-back-button">
            <div class="page-content">
                <div class="rubric-header-nav">
                    <a href="{{ route('admin.profile') }}" class="btn btn-back">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <h2 class="manage-title">System Monitoring and Logs</h2>

                {{-- Filter Section --}}
                <div class="filter-section">
                    <form method="GET" action="{{ route('admin.system-monitoring') }}">
                        <div class="filter-row">
                            <div class="filter-items-left">
                                <div class="filter-item">
                                    <label for="from">From</label>
                                    <select id="from" name="from" class="filter-select">
                                        <option value="" @selected(request('from')==='' )>None</option>
                                        <option value="today" @selected(request('from')==='today' )>Today</option>
                                        <option value="yesterday" @selected(request('from')==='yesterday' )>Yesterday</option>
                                        <option value="week" @selected(request('from')==='week' )>This Week</option>
                                        <option value="month" @selected(request('from')==='month' )>This Month</option>
                                    </select>
                                </div>
                                <div class="filter-item">
                                    <label for="to">To</label>
                                    <select id="to" name="to" class="filter-select">
                                        <option value="" @selected(request('to')==='' )>None</option>
                                        <option value="today" @selected(request('to')==='today' )>Today</option>
                                        <option value="yesterday" @selected(request('to')==='yesterday' )>Yesterday</option>
                                        <option value="week" @selected(request('to')==='week' )>This Week</option>
                                        <option value="month" @selected(request('to')==='month' )>This Month</option>
                                    </select>
                                </div>
                                <div class="filter-item">
                                    <label for="activity_type">Activity Type</label>
                                    <select id="activity_type" name="activity_type" class="filter-select">
                                        <option value="" @selected(request('activity_type')==='' )>None</option>
                                        <option value="login" @selected(request('activity_type')==='login' )>Login</option>
                                        <option value="logout" @selected(request('activity_type')==='logout' )>Logout</option>
                                        <option value="create" @selected(request('activity_type')==='create' )>Create</option>
                                        <option value="update" @selected(request('activity_type')==='update' )>Update</option>
                                        <option value="delete" @selected(request('activity_type')==='delete' )>Delete</option>
                                        <option value="approve" @selected(request('activity_type')==='approve' )>Approve</option>
                                        <option value="reject" @selected(request('activity_type')==='reject' )>Reject</option>
                                        <option value="export" @selected(request('activity_type')==='export' )>Export</option>
                                    </select>
                                </div>
                            </div>
                            <div class="search-box">
                                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search logs...">
                                <button class="search-btn" type="submit" aria-label="Search">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Table --}}
                <div class="table-wrap">
                    <table class="monitoring-table">
                        <thead>
                            <tr>
                                <th class="col-timestamp">Timestamp</th>
                                <th class="col-role">User Role</th>
                                <th class="col-name">User Name</th>
                                <th class="col-activity">Activity Type</th>
                                <th class="col-description">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            // Sample data for demonstration - Limited to 10 entries per page
                            $logs = collect([
                            (object) ['timestamp' => '2025-01-15 14:30:25', 'user_role' => 'Admin', 'user_name' => 'John Doe', 'activity_type' => 'Login', 'description' => 'User logged in successfully'],
                            (object) ['timestamp' => '2025-01-15 14:25:10', 'user_role' => 'Assessor', 'user_name' => 'Jane Smith', 'activity_type' => 'Create', 'description' => 'Created new rubric configuration'],
                            (object) ['timestamp' => '2025-01-15 14:20:45', 'user_role' => 'Admin', 'user_name' => 'Mike Johnson', 'activity_type' => 'Approve', 'description' => 'Approved student submission #1234'],
                            (object) ['timestamp' => '2025-01-15 14:15:30', 'user_role' => 'Admin', 'user_name' => 'Sarah Wilson', 'activity_type' => 'Export', 'description' => 'Exported award report for College of Engineering'],
                            (object) ['timestamp' => '2025-01-15 14:10:15', 'user_role' => 'Assessor', 'user_name' => 'David Brown', 'activity_type' => 'Update', 'description' => 'Updated scoring criteria for leadership assessment'],
                            (object) ['timestamp' => '2025-01-15 14:05:42', 'user_role' => 'Admin', 'user_name' => 'Lisa Chen', 'activity_type' => 'Delete', 'description' => 'Deleted user account #5678'],
                            (object) ['timestamp' => '2025-01-15 14:00:18', 'user_role' => 'Student', 'user_name' => 'Maria Santos', 'activity_type' => 'Submit', 'description' => 'Submitted leadership certificate for review'],
                            (object) ['timestamp' => '2025-01-15 13:55:33', 'user_role' => 'Admin', 'user_name' => 'Robert Kim', 'activity_type' => 'Login', 'description' => 'User logged in successfully'],
                            (object) ['timestamp' => '2025-01-15 13:50:07', 'user_role' => 'Assessor', 'user_name' => 'Emily Davis', 'activity_type' => 'Review', 'description' => 'Reviewed student submission #9012'],
                            (object) ['timestamp' => '2025-01-15 13:45:21', 'user_role' => 'Admin', 'user_name' => 'Carlos Martinez', 'activity_type' => 'Export', 'description' => 'Exported system logs for audit']
                            ]);
                            @endphp

                            @if($logs->count() > 0)
                            @foreach($logs as $log)
                            <tr>
                                <td>{{ $log->timestamp }}</td>
                                <td>
                                    <span class="badge {{ $log->user_role === 'Admin' ? 'badge--red' : 'badge--blue' }}">
                                        {{ $log->user_role }}
                                    </span>
                                </td>
                                <td>{{ $log->user_name }}</td>
                                <td>
                                    <span class="activity-badge activity-{{ strtolower($log->activity_type) }}">
                                        {{ $log->activity_type }}
                                    </span>
                                </td>
                                <td>{{ $log->description }}</td>
                            </tr>
                            @endforeach
                            @else
                            {{-- Empty state with placeholder rows --}}
                            @for ($i = 0; $i < 5; $i++)
                                <tr>
                                <td>......</td>
                                <td>......</td>
                                <td>......</td>
                                <td>......</td>
                                <td>......</td>
                                </tr>
                                @endfor
                                @endif
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="unified-pagination">
                    <button class="btn-nav" disabled>
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <button class="btn-page active">1</button>
                    <button class="btn-page">2</button>
                    <button class="btn-page">3</button>
                    <button class="btn-page">4</button>
                    <button class="btn-nav">
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <script>
            // Initialize when DOM is ready
            document.addEventListener('DOMContentLoaded', function() {
                console.log('System Monitoring: Page loaded');

                // Handle pagination
                document.addEventListener('click', function(e) {
                    if (e.target.closest('.btn-page')) {
                        e.preventDefault();
                        const button = e.target.closest('.btn-page');

                        // Remove active class from all buttons
                        document.querySelectorAll('.btn-page').forEach(btn => {
                            btn.classList.remove('active');
                        });

                        // Add active class to clicked button
                        button.classList.add('active');

                        console.log('Page changed to:', button.textContent);
                    }

                    if (e.target.closest('.btn-nav:not([disabled])')) {
                        e.preventDefault();
                        const button = e.target.closest('.btn-nav');
                        const isNext = button.textContent.includes('Next');

                        console.log('Pagination:', isNext ? 'Next' : 'Previous');
                    }
                });

                // Handle filter form submission
                const filterForm = document.querySelector('.filter-section form');
                if (filterForm) {
                    filterForm.addEventListener('submit', function(e) {
                        console.log('Filter form submitted');
                    });
                }
            });
        </script>

</div>
</main>
</div>

@endsection