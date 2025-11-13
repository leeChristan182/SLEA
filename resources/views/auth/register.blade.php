<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SLEA - Student Registration</title>
    <link rel="icon" href="{{ asset('images/osas-logo.png') }}?v={{ filemtime(public_path('images/osas-logo.png')) }}" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- CSRF --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

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

    <link href="{{ asset('css/header.css') }}" rel="stylesheet">
    <link href="{{ asset('css/register.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100 {{ session('dark_mode', false) ? 'dark-mode' : '' }}">
    <!-- Header -->
    <div class="header-container">
        <div class="header">
            <div class="d-flex align-items-center gap-3">
                <img src="{{ asset('images/osas-logo.png') }}" alt="USeP Logo" height="60">
                <span class="fs-3 fw-bolder logo-text">SLEA</span>
                <div style="width: 1px; height: 40px; background-color: #ccc; margin: 0 0.5rem;"></div>
                <div class="tagline ms-3">
                    <span class="gold1">Empowering</span> <span class="maroon1">Leadership.</span><br>
                    <span class="maroon1">Recognizing</span> <span class="gold1">Excellence.</span>
                </div>
            </div>

            <div class="header-right d-flex align-items-center gap-3">
                <div class="text-end">
                    <small>Having Trouble?</small><br>
                    <a href="#">Send us a message</a>
                </div>
                <button id="darkModeToggle" class="dark-toggle-btn" title="Toggle Dark Mode">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Registration Content -->
    <div class="register-container">
        <main class="flex-grow-1">
            <div class="container py-5">
                <h4 class="text-maroon mb-4 fs-1 fw-bold">Sign Up!</h4>
                <p class="small fs-5 fw-normal">
                    Already have an account? <a href="{{ route('login.show') }}">Login here</a>
                </p>

                @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if (session('status'))
                <div class="alert alert-success" role="status">
                    {{ session('status') }}
                </div>
                @endif

                <form method="POST" action="{{ route('register.store') }}" novalidate>
                    @csrf

                    <!-- Step 1: Personal Information -->
                    <div class="form-step active">
                        <h5 class="mb-3">Personal Information</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="last_name">Last Name <span class="required">*</span></label>
                                <input id="last_name" type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required autocomplete="family-name">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="first_name">First Name <span class="required">*</span></label>
                                <input id="first_name" type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required autocomplete="given-name">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="middle_name">Middle Name</label>
                                <input id="middle_name" type="text" name="middle_name" class="form-control" value="{{ old('middle_name') }}" autocomplete="additional-name">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="birth_date">Birth Date <span class="required">*</span></label>
                                <input id="birth_date" type="date" name="birth_date" class="form-control" value="{{ old('birth_date') }}" required autocomplete="bday">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="age">Age <span class="required">*</span></label>
                                <input id="age" type="text" name="age" class="form-control" readonly value="{{ old('age') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="email_address">USeP Email <span class="required">*</span></label>
                                <input id="email_address" type="email" name="email_address" class="form-control" placeholder="example@usep.edu.ph" value="{{ old('email_address') }}" required autocomplete="email">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="contact">Contact Number <span class="required">*</span></label>
                                <input id="contact" type="text" name="contact" class="form-control" value="{{ old('contact') }}" required autocomplete="tel">
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Academic Information -->
                    <div class="form-step">
                        <h5 class="mb-3">Academic Information</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="student_id">Student ID <span class="required">*</span></label>
                                <input id="student_id" type="text" name="student_id" class="form-control"
                                    placeholder="e.g. 2021-00001" value="{{ old('student_id') }}" required autocomplete="off">
                            </div>

                            {{-- College --}}
                            <div class="col-md-4">
                                <label class="form-label" for="college_id">College <span class="required">*</span></label>
                                <select name="college_id" id="college_id"
                                    class="form-select @error('college_id') is-invalid @enderror"
                                    required data-old="{{ old('college_id') }}" autocomplete="organization">
                                    <option value="">Select College</option>
                                    @foreach ($colleges as $c)
                                    <option value="{{ $c->id }}" {{ (string)old('college_id')===(string)$c->id ? 'selected' : '' }}>
                                        {{ $c->college_name ?? $c->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('college_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Program --}}
                            <div class="col-md-4">
                                <label class="form-label" for="program_id">Program <span class="required">*</span></label>
                                <select name="program_id" id="program_id"
                                    class="form-select @error('program_id') is-invalid @enderror"
                                    required data-old="{{ old('program_id') }}">
                                    <option value="">Select Program</option>
                                </select>
                                @error('program_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Major --}}
                            <div class="col-md-4">
                                <label class="form-label" for="major_id">Major</label>
                                <select name="major_id" id="major_id"
                                    class="form-select @error('major_id') is-invalid @enderror"
                                    data-old="{{ old('major_id') }}">
                                    <option value="">Select Major</option>
                                </select>
                                @error('major_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label" for="year_level">Year Level <span class="required">*</span></label>
                                <select id="year_level" name="year_level" class="form-select" required>
                                    <option value="">--</option>
                                    <option value="1" {{ old('year_level')=='1' ? 'selected' : '' }}>1st Year</option>
                                    <option value="2" {{ old('year_level')=='2' ? 'selected' : '' }}>2nd Year</option>
                                    <option value="3" {{ old('year_level')=='3' ? 'selected' : '' }}>3rd Year</option>
                                    <option value="4" {{ old('year_level')=='4' ? 'selected' : '' }}>4th Year</option>
                                    <option value="5" {{ old('year_level')=='5' ? 'selected' : '' }}>5th Year</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label" for="expected_grad">Expected Year to Graduate <span class="required">*</span></label>
                                <input id="expected_grad" type="text" name="expected_grad" class="form-control" readonly value="{{ old('expected_grad') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Leadership Involvement -->
                    <div class="form-step">
                        <h5 class="mb-3">Leadership Information</h5>

                        <div class="row g-3">
                            {{-- Leadership Type (council list incl. LCM; CCO requires cluster/org) --}}
                            <div class="col-md-6">
                                <label class="form-label" for="leadership_type_id">
                                    Leadership Type <span class="required">*</span>
                                </label>
                                <select id="leadership_type_id" name="leadership_type_id"
                                    class="form-select @error('leadership_type_id') is-invalid @enderror"
                                    required data-old="{{ old('leadership_type_id') }}">
                                    <option value="">Select Leadership Type</option>
                                    @foreach ($leadershipTypes ?? [] as $type)
                                    <option
                                        value="{{ $type->id }}"
                                        data-requires-org="{{ (int)($type->requires_org ?? 0) }}"
                                        data-key="{{ $type->key ?? '' }}"
                                        {{ old('leadership_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('leadership_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted d-block mt-1">
                                    USG, OSC, LC, CCO, LGU, or LCM. For CCO (club/org), specify Cluster & Organization.
                                </small>
                            </div>

                            {{-- Cluster (shown only when requires_org = true, e.g., CCO) --}}
                            <div class="col-md-6" id="cluster_wrap" style="display:none;">
                                <label class="form-label" for="cluster_id">
                                    Cluster <span id="cluster_required_star" class="required" style="display:none;">*</span>
                                </label>
                                <select id="cluster_id" name="cluster_id"
                                    class="form-select @error('cluster_id') is-invalid @enderror"
                                    data-old="{{ old('cluster_id') }}">
                                    <option value="">Select Cluster</option>
                                </select>
                                @error('cluster_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Organization (shown only when requires_org = true, e.g., CCO) --}}
                            <div class="col-md-6" id="org_wrap" style="display:none;">
                                <label class="form-label" for="organization_id">
                                    Organization <span id="org_required_star" class="required" style="display:none;">*</span>
                                </label>
                                <select id="organization_id" name="organization_id"
                                    class="form-select @error('organization_id') is-invalid @enderror"
                                    data-old="{{ old('organization_id') }}">
                                    <option value="">Select Organization</option>
                                </select>
                                <small id="org_optional_hint" class="text-muted" style="display:none;">Optional for non-CCO.</small>
                                @error('organization_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Positions (either council union or per-organization) --}}
                            <div class="col-md-6">
                                <label class="form-label" for="position_id">Position Held <span class="required">*</span></label>
                                <select id="position_id" name="position_id"
                                    class="form-select @error('position_id') is-invalid @enderror"
                                    required data-old="{{ old('position_id') }}">
                                    <option value="">Select Position</option>
                                </select>
                                @error('position_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label" for="term">Term of Service <span class="required">*</span></label>
                                <input id="term" type="text" name="term" class="form-control @error('term') is-invalid @enderror"
                                    value="{{ old('term') }}" placeholder="e.g., 2024–2025" required>
                                @error('term') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label" for="issued_by">Issued By <span class="required">*</span></label>
                                <input id="issued_by" type="text" name="issued_by" class="form-control @error('issued_by') is-invalid @enderror"
                                    value="{{ old('issued_by') }}" required>
                                @error('issued_by') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label" for="leadership_status">Leadership Status <span class="required">*</span></label>
                                <select id="leadership_status" name="leadership_status" class="form-select @error('leadership_status') is-invalid @enderror" required>
                                    <option value="">Select Status</option>
                                    <option value="Active" {{ old('leadership_status')=='Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ old('leadership_status')=='Inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('leadership_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Account Credentials -->
                    <div class="form-step">
                        <h5 class="mb-3">Login Credentials</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="password">Password <span class="required">*</span></label>
                                <input id="password" type="password" name="password" class="form-control" required>
                                <ul class="password-requirements list-unstyled mt-2 small">
                                    <li id="length" class="text-danger"><i class="fa-regular fa-circle-xmark me-1"></i> At least 8 characters</li>
                                    <li id="uppercase" class="text-danger"><i class="fa-regular fa-circle-xmark me-1"></i> At least 1 uppercase letter</li>
                                    <li id="lowercase" class="text-danger"><i class="fa-regular fa-circle-xmark me-1"></i> At least 1 lowercase letter</li>
                                    <li id="number" class="text-danger"><i class="fa-regular fa-circle-xmark me-1"></i> At least 1 number</li>
                                    <li id="special" class="text-danger"><i class="fa-regular fa-circle-xmark me-1"></i> At least 1 special character</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="password_confirmation">Confirm Password <span class="required">*</span></label>
                                <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-check mt-3">
                            <input class="form-check-input" id="privacy_agree" type="checkbox" name="privacy_agree" {{ old('privacy_agree') ? 'checked' : '' }} required>
                            <label class="form-check-label" for="privacy_agree">
                                I agree to the <a href="https://www.usep.edu.ph/usep-data-privacy-statement/" target="_blank" rel="noopener">Data Privacy Policy</a>.
                            </label>
                        </div>
                    </div>

                    <!-- Step Controls -->
                    <div class="pagination-controls d-flex justify-content-center align-items-center mt-3 mb-2 gap-1">
                        <button type="button" class="btn btn-secondary px-3 py-2" id="prevBtn" onclick="nextPrev(-1)" disabled>Back</button>
                        <div class="page-numbers d-flex gap-1" aria-label="form steps">
                            <span class="page-number active">1</span>
                            <span class="page-number">2</span>
                            <span class="page-number">3</span>
                            <span class="page-number">4</span>
                        </div>
                        <button type="button" class="btn btn-primary maroon-btn px-3 py-2" id="nextBtn" onclick="nextPrev(1)">Next</button>
                    </div>
                </form>
            </div>
        </main>

        <div class="floating-tools d-md-none">
            <button id="darkModeToggleFloating" class="floating-btn" title="Toggle Dark Mode"><i class="fas fa-moon"></i></button>
            <a href="#" class="floating-btn" title="Send us a message"><i class="fa-solid fa-envelope"></i></a>
        </div>
    </div>

    <footer id="page-footer" class="mt-auto text-center py-3 small">
        &copy; {{ date('Y') }} University of Southeastern Philippines. All rights reserved.
    </footer>

    {{-- main behaviour --}}
    <script src="{{ asset('js/register.js') }}"></script>
</body>

</html>