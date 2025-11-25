{{-- resources/views/admin/system-monitoring.blade.php --}}
@extends('layouts.app')

@section('title', 'System Monitoring & Logs')

@section('content')
<div class="container">
    @include('partials.sidebar')

    <main class="main-content">
        <div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <h1 class="mb-1">System Monitoring &amp; Logs</h1>
                <p class="text-muted mb-0">
                    View recorded activities performed by admins, assessors, and students.
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <form action="{{ route('admin.system-logs.clear') }}"
                      method="POST"
                      onsubmit="return confirm('Are you sure you want to clear ALL system logs?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="fa-solid fa-trash-can me-1"></i> Clear All Logs
                    </button>
                </form>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Filters --}}
        <section class="card mb-3 p-3">
            <form method="GET" action="{{ route('admin.system-logs.index') }}">
                <div class="row g-2 align-items-end">
                    {{-- Role --}}
                    <div class="col-md-3">
                        <label class="form-label mb-1">User Role</label>
                        <select name="role" class="form-select form-select-sm">
                            <option value="">All Roles</option>
                            @foreach(['admin' => 'Admin', 'assessor' => 'Assessor', 'student' => 'Student'] as $key => $label)
                                <option value="{{ $key }}" @selected(request('role') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Activity Type --}}
                    <div class="col-md-3">
                        <label class="form-label mb-1">Activity</label>
                        <select name="activity_type" class="form-select form-select-sm">
                            <option value="">All Activities</option>
                            @foreach(['Login', 'Logout', 'Create', 'Update', 'Delete'] as $type)
                                <option value="{{ $type }}" @selected(request('activity_type') === $type)>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- From --}}
                    <div class="col-md-2">
                        <label class="form-label mb-1">From</label>
                        <input type="date"
                               name="from"
                               class="form-control form-control-sm"
                               value="{{ request('from') }}">
                    </div>

                    {{-- To --}}
                    <div class="col-md-2">
                        <label class="form-label mb-1">To</label>
                        <input type="date"
                               name="to"
                               class="form-control form-control-sm"
                               value="{{ request('to') }}">
                    </div>

                    {{-- Search --}}
                    <div class="col-md-2">
                        <label class="form-label mb-1">Search</label>
                        <input type="text"
                               name="q"
                               class="form-control form-control-sm"
                               placeholder="User or description"
                               value="{{ request('q') }}">
                    </div>
                </div>

                <div class="mt-3 d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.system-logs.index') }}" class="btn btn-secondary btn-sm">
                        Clear
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm">
                        Apply Filters
                    </button>
                </div>
            </form>
        </section>

        {{-- Logs table --}}
        <section class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 18%;">Date &amp; Time</th>
                                <th style="width: 10%;">Role</th>
                                <th style="width: 20%;">User</th>
                                <th style="width: 12%;">Activity</th>
                                <th style="width: 40%;">Description</th>
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

                <div class="p-2 border-top">
                    {{ $logs->links() }}
                </div>
            </div>
        </section>
    </main>
</div>
@endsection
