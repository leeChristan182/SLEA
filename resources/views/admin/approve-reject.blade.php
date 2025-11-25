@extends('layouts.app')
@section('title', 'Approve / Reject Student Accounts')

@section('content')
<div class="container">
    <h2 class="manage-title mb-3">Student Account Approval</h2>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Filter/Search -->
    <form method="GET" class="filter-bar mb-3 d-flex flex-wrap gap-2 align-items-center">
        <input
            type="text"
            name="q"
            class="form-control flex-grow-1"
            placeholder="Search by email or student number"
            value="{{ request('q') }}">

        <select name="status" class="form-select w-auto" onchange="this.form.submit()">
            <option value="{{ \App\Models\User::STATUS_PENDING }}"
                {{ request('status', \App\Models\User::STATUS_PENDING) === \App\Models\User::STATUS_PENDING ? 'selected' : '' }}>
                Pending
            </option>
            <option value="{{ \App\Models\User::STATUS_APPROVED }}"
                {{ request('status') === \App\Models\User::STATUS_APPROVED ? 'selected' : '' }}>
                Approved
            </option>
            <option value="{{ \App\Models\User::STATUS_REJECTED }}"
                {{ request('status') === \App\Models\User::STATUS_REJECTED ? 'selected' : '' }}>
                Rejected
            </option>
        </select>

        <button type="submit" class="btn btn-primary">
            Filter
        </button>
    </form>

    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Student No.</th>
                <th>Name</th>
                <th>Email</th>
                <th>Program</th>
                <th>Year Level</th>
                <th>Status</th>
                <th style="width: 180px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
                @php
                    $acad = $student->studentAcademic;
                @endphp
                <tr>
                    <td>{{ $acad->student_number ?? '—' }}</td>

                    {{-- New: Student full name --}}
                    <td>
                        {{ $student->last_name }},
                        {{ $student->first_name }}
                        {{ $student->middle_name }}
                    </td>

                    <td>{{ $student->email }}</td>
                    <td>{{ $acad->program->name ?? '—' }}</td>
                    <td>{{ $acad->year_level ?? '—' }}</td>
                    <td>
                        @php
                            $statusClass = match ($student->status) {
                                \App\Models\User::STATUS_APPROVED => 'bg-success',
                                \App\Models\User::STATUS_REJECTED => 'bg-danger',
                                default => 'bg-warning text-dark',
                            };
                        @endphp
                        <span class="badge {{ $statusClass }}">
                            {{ ucfirst($student->status) }}
                        </span>
                    </td>
                    <td>
                        @if($student->isPending())
                            <form action="{{ route('admin.approve', $student) }}"
                                  method="POST"
                                  class="d-inline">
                                @csrf
                                <button class="btn btn-success btn-sm">
                                    Approve
                                </button>
                            </form>

                            <form action="{{ route('admin.reject', $student) }}"
                                  method="POST"
                                  class="d-inline ms-1">
                                @csrf
                                <button class="btn btn-danger btn-sm">
                                    Reject
                                </button>
                            </form>
                        @else
                            <em class="text-muted">No action available</em>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        No student accounts found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">
        {{ $students->links() }}
    </div>
</div>
@endsection
