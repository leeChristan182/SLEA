@extends('layouts.app')

@section('title', 'Complete Your Profile')

@section('content')
    <div class="container">
        @include('partials.sidebar')

        <main class="main-content">
            <div class="page-header">
                <h1>Complete Your Profile</h1>
                <p class="text-muted">Please complete your academic and leadership information to continue.</p>
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

            <form method="POST" action="{{ route('profile.complete.student.store') }}" id="profileForm" novalidate>
                @csrf

                {{-- Centralized route registry for register.js --}}
                <meta id="slea-routes"
                    data-programs="{{ route('ajax.programs') }}"
                    data-majors="{{ route('ajax.majors') }}"
                    data-clusters="{{ route('ajax.clusters') }}"
                    data-organizations="{{ route('ajax.organizations') }}"
                    data-positions="{{ route('ajax.positions') }}"
                    @if(\Illuminate\Support\Facades\Route::has('ajax.council.positions'))
                    data-council-positions="{{ route('ajax.council.positions') }}"
                    @endif
                    @if(\Illuminate\Support\Facades\Route::has('ajax.academics.map'))
                    data-academics-map="{{ route('ajax.academics.map') }}"
                    @endif>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Academic Information</h5>
                        <div class="row g-3">
                            {{-- Student ID --}}
                            <div class="col-md-4">
                                <label class="form-label" for="student_id">
                                    Student ID <span class="required">*</span>
                                </label>
                                <input
                                    id="student_id"
                                    type="text"
                                    name="student_id"
                                    class="form-control @error('student_id') is-invalid @enderror"
                                    placeholder="e.g. 2021-00001"
                                    value="{{ old('student_id') }}"
                                    required
                                    autocomplete="off">
                                @error('student_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- College --}}
                            <div class="col-md-4">
                                <label class="form-label" for="college_id">
                                    College <span class="required">*</span>
                                </label>
                                <select
                                    name="college_id"
                                    id="college_id"
                                    class="form-select @error('college_id') is-invalid @enderror"
                                    required>
                                    <option value="">Select College</option>
                                    @foreach ($colleges as $c)
                                        <option
                                            value="{{ $c->id }}"
                                            {{ (string)old('college_id') === (string)$c->id ? 'selected' : '' }}>
                                            {{ $c->college_name ?? $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('college_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Program --}}
                            <div class="col-md-4">
                                <label class="form-label" for="program_id">
                                    Program <span class="required">*</span>
                                </label>
                                <select
                                    name="program_id"
                                    id="program_id"
                                    class="form-select @error('program_id') is-invalid @enderror"
                                    required>
                                    <option value="">Select Program</option>
                                </select>
                                @error('program_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Major --}}
                            <div class="col-md-4">
                                <label class="form-label" for="major_id">Major</label>
                                <select
                                    name="major_id"
                                    id="major_id"
                                    class="form-select @error('major_id') is-invalid @enderror">
                                    <option value="">Select Major</option>
                                </select>
                                @error('major_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Year Level --}}
                            <div class="col-md-4">
                                <label class="form-label" for="year_level">
                                    Year Level <span class="required">*</span>
                                </label>
                                <select
                                    id="year_level"
                                    name="year_level"
                                    class="form-select @error('year_level') is-invalid @enderror"
                                    required>
                                    <option value="">--</option>
                                    <option value="1" {{ old('year_level') == '1' ? 'selected' : '' }}>1st Year</option>
                                    <option value="2" {{ old('year_level') == '2' ? 'selected' : '' }}>2nd Year</option>
                                    <option value="3" {{ old('year_level') == '3' ? 'selected' : '' }}>3rd Year</option>
                                    <option value="4" {{ old('year_level') == '4' ? 'selected' : '' }}>4th Year</option>
                                    <option value="5" {{ old('year_level') == '5' ? 'selected' : '' }}>5th Year</option>
                                    <option value="6" {{ old('year_level') == '6' ? 'selected' : '' }}>6th Year</option>
                                    <option value="7" {{ old('year_level') == '7' ? 'selected' : '' }}>7th Year</option>
                                    <option value="8" {{ old('year_level') == '8' ? 'selected' : '' }}>8th Year</option>
                                </select>
                                @error('year_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Leadership Information</h5>
                        <div class="row g-3">
                            {{-- Leadership Type --}}
                            <div class="col-md-4">
                                <label class="form-label" for="leadership_type_id">
                                    Leadership Type <span class="required">*</span>
                                </label>
                                <select
                                    name="leadership_type_id"
                                    id="leadership_type_id"
                                    class="form-select @error('leadership_type_id') is-invalid @enderror"
                                    required>
                                    <option value="">Select Leadership Type</option>
                                    @foreach ($leadershipTypes as $type)
                                        <option
                                            value="{{ $type->id }}"
                                            data-key="{{ $type->key ?? '' }}"
                                            data-requires-org="{{ (int)($type->requires_org ?? 0) }}"
                                            data-old="{{ old('leadership_type_id') }}"
                                            {{ (string)old('leadership_type_id') === (string)$type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('leadership_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Cluster (conditional) --}}
                            <div class="col-md-4" id="cluster_wrap" style="display: none;">
                                <label class="form-label" for="cluster_id">
                                    Cluster <span id="cluster_required_star" class="required" style="display: none;">*</span>
                                </label>
                                <select
                                    name="cluster_id"
                                    id="cluster_id"
                                    class="form-select @error('cluster_id') is-invalid @enderror">
                                    <option value="">Select Cluster</option>
                                </select>
                                @error('cluster_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Organization (conditional) --}}
                            <div class="col-md-4" id="org_wrap" style="display: none;">
                                <label class="form-label" for="organization_id">
                                    Organization <span id="org_required_star" class="required" style="display: none;">*</span>
                                </label>
                                <select
                                    name="organization_id"
                                    id="organization_id"
                                    class="form-select @error('organization_id') is-invalid @enderror">
                                    <option value="">Select Organization</option>
                                </select>
                                <small id="org_optional_hint" class="text-muted" style="display: none;">Optional for non-CCO.</small>
                                @error('organization_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Position --}}
                            <div class="col-md-4">
                                <label class="form-label" for="position_id">
                                    Position <span class="required">*</span>
                                </label>
                                <select
                                    name="position_id"
                                    id="position_id"
                                    class="form-select @error('position_id') is-invalid @enderror"
                                    data-old="{{ old('position_id') }}"
                                    required>
                                    <option value="">Select Position</option>
                                </select>
                                @error('position_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Term --}}
                            <div class="col-md-4">
                                <label class="form-label" for="term">
                                    Term <span class="required">*</span>
                                </label>
                                <input
                                    id="term"
                                    type="text"
                                    name="term"
                                    class="form-control @error('term') is-invalid @enderror"
                                    placeholder="e.g. 2023-2024"
                                    value="{{ old('term') }}"
                                    required>
                                @error('term')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Issued By --}}
                            <div class="col-md-4">
                                <label class="form-label" for="issued_by">
                                    Issued By <span class="required">*</span>
                                </label>
                                <input
                                    id="issued_by"
                                    type="text"
                                    name="issued_by"
                                    class="form-control @error('issued_by') is-invalid @enderror"
                                    placeholder="e.g. OSAS"
                                    value="{{ old('issued_by') }}"
                                    required>
                                @error('issued_by')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Leadership Status --}}
                            <div class="col-md-4">
                                <label class="form-label" for="leadership_status">
                                    Status <span class="required">*</span>
                                </label>
                                <select
                                    id="leadership_status"
                                    name="leadership_status"
                                    class="form-select @error('leadership_status') is-invalid @enderror"
                                    required>
                                    <option value="">--</option>
                                    <option value="Active" {{ old('leadership_status') == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ old('leadership_status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('leadership_status')
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

    <script src="{{ asset('js/register.js') }}"></script>
@endsection

