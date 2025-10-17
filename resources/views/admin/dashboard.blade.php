{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="dashboard-container py-4">
    @include('partials.sidebar')
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h2 class="fw-bold text-dark mb-0">
                <i class="fas fa-tachometer-alt me-2 text-primary"></i> Admin Dashboard
            </h2>
            <p class="text-muted mb-0">Welcome, <strong>{{ auth()->user()->first_name ?? 'Admin' }}</strong>!</p>
        </div>
        <span class="text-muted small mt-2 mt-md-0">
            {{ now()->format('F j, Y') }}
        </span>
    </div>

    {{-- Stats Cards Row --}}
    <div class="row g-4 stats-row">
        {{-- Total Students --}}
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card shadow-sm border-0 h-100 p-4 text-center">
                <i class="fas fa-user-graduate fa-3x text-primary mb-3"></i>
                <h5 class="text-uppercase small fw-bold mb-1 text-secondary">Students</h5>
                <h1 class="fw-bold text-dark mb-0">{{ $studentCount }}</h1>
            </div>
        </div>

        {{-- Total Assessors --}}
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card shadow-sm border-0 h-100 p-4 text-center">
                <i class="fas fa-user-tie fa-3x text-success mb-3"></i>
                <h5 class="text-uppercase small fw-bold mb-1 text-secondary">Assessors</h5>
                <h1 class="fw-bold text-dark mb-0">{{ $assessorCount }}</h1>
            </div>
        </div>

        {{-- Total Admins --}}
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card shadow-sm border-0 h-100 p-4 text-center">
                <i class="fas fa-user-shield fa-3x text-warning mb-3"></i>
                <h5 class="text-uppercase small fw-bold mb-1 text-secondary">Admins</h5>
                <h1 class="fw-bold text-dark mb-0">{{ $adminCount }}</h1>
            </div>
        </div>

        {{-- Total Submissions --}}
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card shadow-sm border-0 h-100 p-4 text-center">
                <i class="fas fa-file-alt fa-3x text-danger mb-3"></i>
                <h5 class="text-uppercase small fw-bold mb-1 text-secondary">Submissions</h5>
                <h1 class="fw-bold text-dark mb-0">{{ $submissionCount }}</h1>
            </div>
        </div>
    </div>

    {{-- Recent Logs Section --}}
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-clipboard-list me-2"></i> Recent System Logs</h5>
                    <a href="{{ route('admin.system-monitoring') }}" class="btn btn-sm btn-light text-primary fw-bold">
                        View All
                    </a>
                </div>

                <div class="card-body p-0">
                    @if($logs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Date</th>
                                    <th scope="col">User Role</th>
                                    <th scope="col">User</th>
                                    <th scope="col">Activity</th>
                                    <th scope="col">Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A') }}</td>
                                    <td><span class="badge bg-info text-dark">{{ $log->user_role }}</span></td>
                                    <td>{{ $log->user_name }}</td>
                                    <td>{{ $log->activity_type }}</td>
                                    <td>{{ $log->description }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p class="mb-0">No recent logs found.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    /* ===== DASHBOARD CONTAINER ===== */
    .dashboard-container {
        margin-left: 260px;
        /* matches sidebar width */
        transition: margin-left 0.3s ease;
        padding-right: 20px;
    }

    /* When sidebar collapsed */
    body.collapsed .dashboard-container {
        margin-left: 70px;
    }

    /* ===== STATS CARDS ===== */
    .stat-card {
        background: #fff;
        border-radius: 15px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
    }

    /* Make cards large on wide screens */
    @media (min-width: 1200px) {
        .stats-row .col-xl-3 {
            flex: 0 0 25%;
            max-width: 25%;
        }

        .stat-card h1 {
            font-size: 2.5rem;
        }
    }

    /* Compress spacing slightly when collapsed */
    body.collapsed .stats-row {
        gap: 1rem !important;
    }

    body.collapsed .stat-card {
        padding: 1.5rem !important;
    }

    /* ===== TABLE ===== */
    .table th {
        font-weight: 600;
    }
</style>
@endpush