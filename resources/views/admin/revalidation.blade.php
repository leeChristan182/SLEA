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
                                <th style="width: 200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr>
                                    <td>{{ $row->id }}</td>
                                    <td>{{ $row->last_name }}, {{ $row->first_name }}</td>
                                    <td>{{ $row->email }}</td>
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
                                        {{-- Approve button --}}
                                        <button type="button" class="btn btn-success btn-sm me-1" data-bs-toggle="modal"
                                            data-bs-target="#approveRevalModal{{ $row->id }}">
                                            Approve
                                        </button>

                                        {{-- Reject button --}}
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#rejectRevalModal{{ $row->id }}">
                                            Reject
                                        </button>
                                    </td>
                                </tr>

                                {{-- APPROVE MODAL --}}
                                <div class="modal fade" id="approveRevalModal{{ $row->id }}" tabindex="-1"
                                    aria-labelledby="approveRevalLabel{{ $row->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.revalidation.approve', $row->id) }}">
                                                @csrf

                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title" id="approveRevalLabel{{ $row->id }}">
                                                        Approve Student Revalidation
                                                    </h5>
                                                    <button type="button" class="btn-close-modal btn-close-white" data-bs-dismiss="modal"
                                                        aria-label="Close" title="Close">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>

                                                <div class="modal-body">
                                                    <p class="mb-2">
                                                        Are you sure you want to mark this student as
                                                        <strong>eligible</strong> again?
                                                    </p>
                                                    <p class="mb-0">
                                                        <strong>{{ $row->last_name }}, {{ $row->first_name }}</strong><br>
                                                        <small class="text-muted">{{ $row->email }}</small>
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
                                <div class="modal fade" id="rejectRevalModal{{ $row->id }}" tabindex="-1"
                                    aria-labelledby="rejectRevalLabel{{ $row->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.revalidation.reject', $row->id) }}">
                                                @csrf

                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title" id="rejectRevalLabel{{ $row->id }}">
                                                        Reject Student Revalidation
                                                    </h5>
                                                    <button type="button" class="btn-close-modal btn-close-white" data-bs-dismiss="modal"
                                                        aria-label="Close" title="Close">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>

                                                <div class="modal-body">
                                                    <p class="mb-2">
                                                        This will mark the student as <strong>ineligible</strong>.
                                                    </p>
                                                    <p class="mb-3">
                                                        <strong>{{ $row->last_name }}, {{ $row->first_name }}</strong><br>
                                                        <small class="text-muted">{{ $row->email }}</small>
                                                    </p>

                                                    {{-- Optional comment textarea if you want to store reason in another column
                                                    later --}}
                                                    {{-- <div class="mb-3">
                                                        <label class="form-label">Reason (optional)</label>
                                                        <textarea name="reason" class="form-control" rows="3"></textarea>
                                                    </div> --}}
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

<style>
    /* Close button styling for modals */
    .btn-close-modal {
        background: rgba(255, 255, 255, 0.2) !important;
        color: white !important;
        border: none !important;
        border-radius: 4px !important;
        width: 32px !important;
        height: 32px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 16px !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        opacity: 1 !important;
        padding: 0 !important;
    }

    .btn-close-modal:hover {
        background: rgba(255, 255, 255, 0.3) !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
    }

    .btn-close-modal:focus {
        outline: none !important;
        box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25) !important;
    }

    .btn-close-modal i {
        font-size: 14px !important;
        color: white !important;
    }

    .btn-close-modal.btn-close-white {
        background: rgba(255, 255, 255, 0.2) !important;
    }

    .btn-close-modal.btn-close-white:hover {
        background: rgba(255, 255, 255, 0.3) !important;
    }
</style>
@endsection