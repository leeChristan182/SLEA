@extends('layouts.app')
@section('title', 'Approve / Reject Student Accounts')

@section('content')
<div class="container">
    <h2 class="manage-title">Student Account Approval</h2>

    <!-- Filter/Search -->
    <form method="GET" class="filter-bar mb-3">
        <input type="text" name="q" placeholder="Search by email or student ID" value="{{ request('q') }}">
        <select name="status" onchange="this.form.submit()">
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    <table class="table table-hover">
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Email</th>
                <th>Program</th>
                <th>Year Level</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
            <tr>
                <td>{{ $student->academicInfo->student_id ?? '-' }}</td>
                <td>{{ $student->email_address }}</td>
                <td>{{ $student->academicInfo->program ?? '-' }}</td>
                <td>{{ $student->academicInfo->year_level ?? '-' }}</td>
                <td>
                    <span class="badge {{ $student->status }}">{{ ucfirst($student->status) }}</span>
                </td>
                <td>
                    @if($student->status === 'pending')
                    <form action="{{ route('admin.approve', $student->student_id) }}" method="POST" style="display:inline">
                        @csrf
                        <button class="btn btn-success btn-sm">Approve</button>
                    </form>
                    <form action="{{ route('admin.reject', $student->student_id) }}" method="POST" style="display:inline">
                        @csrf
                        <button class="btn btn-danger btn-sm">Reject</button>
                    </form>
                    @else
                    <em>No action available</em>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No student accounts found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">{{ $students->links() }}</div>
</div>
@endsection