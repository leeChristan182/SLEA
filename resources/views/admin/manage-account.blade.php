{{-- resources/views/admin/manage.blade.php --}}
@extends('layouts.app')

@section('title', 'Manage User Accounts')

@section('content')
<div class="container">

    <main class="main-content">
        <div class="page-with-back-button">
            <div class="page-content">

                {{-- Back Button --}}
                <div class="rubric-header-nav mb-3">
                    <a href="{{ route('admin.profile') }}" class="btn btn-back">
                        <i class="fas fa-arrow-left"></i> Back to Profile
                    </a>
                </div>

                <h2 class="manage-title mb-3">Manage User Accounts</h2>

                {{-- Flash Messages --}}
                @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
                @endif
                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="m-0 ps-3">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Filters / Search --}}
                <form class="controls d-flex flex-wrap justify-content-between align-items-end mb-3"
                    method="GET"
                    action="{{ route('admin.manage-account') }}">

                    <div class="d-flex flex-wrap gap-2 align-items-end">
                        {{-- Role filter --}}
                        <div>
                            <label class="small text-muted d-block mb-1">Role</label>
                            <select name="role" class="form-select">
                                <option value="">All</option>
                                <option value="{{ \App\Models\User::ROLE_ADMIN }}"
                                    @selected(request('role')===\App\Models\User::ROLE_ADMIN)>
                                    Admin
                                </option>
                                <option value="{{ \App\Models\User::ROLE_ASSESSOR }}"
                                    @selected(request('role')===\App\Models\User::ROLE_ASSESSOR)>
                                    Assessor
                                </option>
                                <option value="{{ \App\Models\User::ROLE_STUDENT }}"
                                    @selected(request('role')===\App\Models\User::ROLE_STUDENT)>
                                    Student
                                </option>
                            </select>
                        </div>

                        {{-- Status filter --}}
                        <div>
                            <label class="small text-muted d-block mb-1">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All</option>
                                <option value="{{ \App\Models\User::STATUS_PENDING }}"
                                    @selected(request('status')===\App\Models\User::STATUS_PENDING)>
                                    Pending
                                </option>
                                <option value="{{ \App\Models\User::STATUS_APPROVED }}"
                                    @selected(request('status')===\App\Models\User::STATUS_APPROVED)>
                                    Approved
                                </option>
                                <option value="{{ \App\Models\User::STATUS_DISABLED }}"
                                    @selected(request('status')===\App\Models\User::STATUS_DISABLED)>
                                    Disabled
                                </option>
                                <option value="{{ \App\Models\User::STATUS_REJECTED }}"
                                    @selected(request('status')===\App\Models\User::STATUS_REJECTED)>
                                    Rejected
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 align-items-end">
                        {{-- Search --}}
                        <div>
                            <label class="small text-muted d-block mb-1">Search</label>
                            <input type="text"
                                name="q"
                                class="form-control"
                                placeholder="Search by name or email…"
                                value="{{ request('q') }}">
                        </div>

                        <div class="mb-1">
                            <button type="submit" class="btn btn-primary mt-4 mt-md-0">
                                <i class="fas fa-search me-1"></i> Filter
                            </button>
                        </div>

                        <div class="mb-1">
                            <a href="{{ route('admin.create_user') }}"
                                class="btn btn-success mt-4 mt-md-0">
                                <i class="fas fa-user-plus me-1"></i> Add Admin / Assessor
                            </a>
                        </div>
                    </div>
                </form>

                {{-- Users Table --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>{{ $user->full_name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ ucfirst($user->role) }}</td>
                                <td>
                                    @php
                                    $badgeClass = match ($user->status) {
                                    \App\Models\User::STATUS_APPROVED => 'bg-success',
                                    \App\Models\User::STATUS_PENDING => 'bg-warning text-dark',
                                    \App\Models\User::STATUS_DISABLED => 'bg-secondary',
                                    \App\Models\User::STATUS_REJECTED => 'bg-danger',
                                    default => 'bg-light text-dark',
                                    };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td>{{ $user->created_at?->format('M d, Y h:i A') }}</td>
                                <td class="text-center">
                                    {{-- Toggle (approved <-> disabled) --}}
                                    <form action="{{ route('admin.manage.toggle', $user) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Toggle status for {{ $user->full_name }}?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="btn btn-sm btn-warning"
                                            title="Enable / Disable">
                                            <i class="fas fa-user-slash"></i>
                                        </button>
                                    </form>

                                    {{-- Delete --}}
                                    <form action="{{ route('admin.manage.destroy', $user) }}"
                                        method="POST"
                                        class="d-inline ms-1"
                                        onsubmit="return confirm('Delete {{ $user->full_name }}? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="btn btn-sm btn-danger"
                                            title="Delete user">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No user accounts found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination / Info --}}
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <div class="text-muted small">
                        Showing
                        @if($users->total() > 0)
                        {{ $users->firstItem() }}–{{ $users->lastItem() }}
                        @else
                        0
                        @endif
                        of {{ $users->total() }} entries
                    </div>
                    <div>
                        {{ $users->appends(request()->query())->links() }}
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>
@endsection