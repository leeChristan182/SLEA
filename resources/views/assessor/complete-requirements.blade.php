@extends('layouts.app')

@section('title', 'Complete Assessor Requirements')

@section('content')
<div class="container">
    @include('partials.sidebar')

    <main class="main-content">

        <div class="alert alert-warning mb-3">
            <strong>Account Limited.</strong><br>
            Please complete the assessor requirements below.
            Once submitted, wait for <strong>Admin validation</strong>.
        </div>

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

        {{-- MAIN SUBMISSION FORM --}}
        <form method="POST" action="{{ route('profile.complete.assessor.store') }}">
            @csrf

            {{-- =========================
               ASSESSOR INFORMATION
               ========================= --}}
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Assessor Information</h5></div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Office / Unit <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="office_unit"
                                   id="office_unit"
                                   class="form-control"
                                   value="{{ old('office_unit', $user->assessorInfo->office_unit ?? '') }}"
                                   placeholder="e.g. Sports Unit, Discipline"
                                   maxlength="150"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Position / Designation <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="position"
                                   id="position"
                                   class="form-control"
                                   value="{{ old('position', $user->assessorInfo->position ?? '') }}"
                                   placeholder="e.g. staff"
                                   maxlength="100"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Assessor Code <span class="text-muted">(optional)</span></label>
                            <input type="text"
                                   name="assessor_code"
                                   id="assessor_code"
                                   class="form-control"
                                   value="{{ old('assessor_code', $user->assessorInfo->assessor_code ?? '') }}"
                                   placeholder="If provided by the institution"
                                   maxlength="50">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success flex-fill">
                    Submit for Validation
                </button>
            </div>
        </form>

        {{-- LOGOUT (SEPARATE FORM - NOT NESTED) --}}
        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit" class="btn btn-outline-danger w-100">
                Logout
            </button>
        </form>

    </main>
</div>

{{-- Logout form for waiting modal --}}
<form id="logout-form" method="POST" action="{{ route('logout') }}">
    @csrf
</form>

{{-- Waiting modal (optional) --}}
@if(session('show_waiting_modal'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'info',
                title: 'Submission Under Review',
                html: `
                    <p>Your submitted information is currently being reviewed by the administrator.</p>
                    <p>Please wait for approval before accessing other features.</p>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                showCancelButton: true,
                cancelButtonText: 'Logout',
                cancelButtonColor: '#dc3545'
            }).then(result => {
                if (result.dismiss === Swal.DismissReason.cancel) {
                    document.getElementById('logout-form').submit();
                }
            });
        });
    </script>
@endif
@endsection