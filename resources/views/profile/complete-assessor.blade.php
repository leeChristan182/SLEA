@extends('layouts.app')

@section('title', 'Complete Your Profile')

@section('content')
    <div class="container">
        @include('partials.sidebar')

        <main class="main-content">
            <div class="page-header">
                <h1>Complete Your Profile</h1>
                <p class="text-muted">Please complete your assessor information to continue.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('profile.complete.assessor.store') }}" id="profileForm" novalidate>
                @csrf

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Assessor Information</h5>
                        <div class="row g-3">
                            {{-- Office/Unit Assigned --}}
                            <div class="col-md-6">
                                <label class="form-label" for="office_unit">
                                    Office/Unit Assigned <span class="required">*</span>
                                </label>
                                <input
                                    id="office_unit"
                                    type="text"
                                    name="office_unit"
                                    class="form-control @error('office_unit') is-invalid @enderror"
                                    placeholder="e.g. OSAS, College-Based Office"
                                    value="{{ old('office_unit') }}"
                                    required>
                                @error('office_unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Position --}}
                            <div class="col-md-6">
                                <label class="form-label" for="position">
                                    Position <span class="required">*</span>
                                </label>
                                <input
                                    id="position"
                                    type="text"
                                    name="position"
                                    class="form-control @error('position') is-invalid @enderror"
                                    placeholder="e.g. Student Affairs Officer"
                                    value="{{ old('position') }}"
                                    required>
                                @error('position')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Assessor Code (Optional) --}}
                            <div class="col-md-6">
                                <label class="form-label" for="assessor_code">
                                    Assessor Code <span class="text-muted">(Optional)</span>
                                </label>
                                <input
                                    id="assessor_code"
                                    type="text"
                                    name="assessor_code"
                                    class="form-control @error('assessor_code') is-invalid @enderror"
                                    placeholder="e.g. ASC-001"
                                    value="{{ old('assessor_code') }}">
                                @error('assessor_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Complete Profile
                    </button>
                </div>
            </form>
        </main>
    </div>
@endsection


