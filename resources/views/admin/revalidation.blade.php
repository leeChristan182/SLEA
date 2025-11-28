@extends('layouts.app')

@section('title', 'Student Revalidation Queue')

@section('content')
    <div class="container">
        @include('partials.sidebar')

        <main class="main-content">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="mb-0">Student Revalidation Queue</h2>
            </div>

            {{-- Flash messages --}}
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($rows->isEmpty())
                <div class="alert alert-info mt-3">
                    There are currently no students flagged for revalidation.
                </div>
            @else
                <div class="table-responsive mt-3">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 70px;">ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Expected Grad Year</th>
                                <th>Eligibility Status</th>
                                <th>Last Updated</th>
                                <th style="width: 260px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                @php
                                    // $row is StudentAcademic, with related User
                                    $user = $row->user;
                                @endphp

                                <tr>
                                    {{-- Show USER id, not academic id, since routes use {user} --}}
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->last_name }}, {{ $user->first_name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $row->expected_grad_year ?? '—' }}</td>
                                    <td>
                                        @php
                                            $status = (string) $row->eligibility_status;
                                        @endphp

                                        @if ($status === 'needs_revalidation')
                                            <span class="badge bg-warning text-dark">Needs Revalidation</span>
                                        @elseif ($status === 'under_review')
                                            <span class="badge bg-info text-dark">Under Review</span>
                                        @elseif ($status === 'eligible')
                                            <span class="badge bg-success">Eligible</span>
                                        @elseif ($status === 'ineligible')
                                            <span class="badge bg-danger">Ineligible</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($row->updated_at)->format('M d, Y') }}</td>

                                    <td>
                                        {{-- View COR button (only if student has uploaded one) --}}
                                        @if (method_exists($row, 'hasCor') ? $row->hasCor() : !empty($row->certificate_of_registration_path))
                                            <a href="{{ route('admin.revalidation.cor', $user->id) }}"
                                                class="btn btn-outline-primary btn-sm me-1" target="_blank">
                                                View COR
                                            </a>
                                        @else
                                            <span class="badge bg-secondary me-1">No COR</span>
                                        @endif

                                        {{-- Approve button --}}
                                        <button type="button" class="btn btn-success btn-sm me-1" data-bs-toggle="modal"
                                            data-bs-target="#approveRevalModal{{ $user->id }}">
                                            Approve
                                        </button>

                                        {{-- Reject button --}}
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#rejectRevalModal{{ $user->id }}">
                                            Reject
                                        </button>
                                    </td>
                                </tr>

                                {{-- APPROVE MODAL --}}
                                <div class="modal fade" id="approveRevalModal{{ $user->id }}" tabindex="-1"
                                    aria-labelledby="approveRevalLabel{{ $user->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.revalidation.approve', $user->id) }}">
                                                @csrf

                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title" id="approveRevalLabel{{ $user->id }}">
                                                        Approve Student Revalidation
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>

                                                <div class="modal-body">
                                                    <p class="mb-2">
                                                        Are you sure you want to mark this student as
                                                        <strong>eligible</strong> again?
                                                    </p>
                                                    <p class="mb-0">
                                                        <strong>{{ $user->last_name }}, {{ $user->first_name }}</strong><br>
                                                        <small class="text-muted">{{ $user->email }}</small>
                                                    </p>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" class="btn btn-success">
                                                        Yes, Approve
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- REJECT MODAL --}}
                                <div class="modal fade" id="rejectRevalModal{{ $user->id }}" tabindex="-1"
                                    aria-labelledby="rejectRevalLabel{{ $user->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.revalidation.reject', $user->id) }}">
                                                @csrf

                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title" id="rejectRevalLabel{{ $user->id }}">
                                                        Reject Student Revalidation
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>

                                                <div class="modal-body">
                                                    <p class="mb-2">
                                                        This will mark the student as <strong>ineligible</strong>.
                                                    </p>
                                                    <p class="mb-3">
                                                        <strong>{{ $user->last_name }}, {{ $user->first_name }}</strong><br>
                                                        <small class="text-muted">{{ $user->email }}</small>
                                                    </p>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" class="btn btn-danger">
                                                        Yes, Reject
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $rows->links() }}
                </div>
            @endif

        </main>
    </div>
@endsection