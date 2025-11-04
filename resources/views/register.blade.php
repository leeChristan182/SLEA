<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SLEA - Student Registration</title>
    <link rel="icon" href="{{ asset('images/osas-logo.png') }}?v={{ filemtime(public_path('images/osas-logo.png')) }}" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Global CSS -->
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
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
                @endif

                <form method="POST" action="{{ route('register.store') }}">
                    @csrf

                    <!-- Step 1: Personal Information -->
                    <div class="form-step active">
                        <h5 class="mb-3">Personal Information</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Last Name <span class="required">* </span></label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">First Name <span class="required">* </span></label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="middle_name" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Birth Date <span class="required">* </span></label>
                                <input type="date" name="birth_date" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Age <span class="required">* </span></label>
                                <input type="text" name="age" class="form-control" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">USeP Email <span class="required">* </span></label>
                                <input type="email" name="email_address" class="form-control" placeholder="example@usep.edu.ph" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Contact Number <span class="required">* </span></label>
                                <input type="text" name="contact" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Academic Information -->
                    <div class="form-step">
                        <h5 class="mb-3">Academic Information</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Student ID <span class="required">* </span></label>
                                <input type="text" name="student_id" class="form-control" placeholder="e.g. 2021-00001" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">College <span class="required">* </span></label>
                                <select name="college_name" class="form-select" required>
                                    <option value="">Select College</option>
                                    @foreach ($colleges as $college)
                                    <option value="{{ $college->college_name }}">{{ $college->college_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Program *</label>
                                <select name="program" class="form-select @error('program') is-invalid @enderror" required>
                                    <option value="">Select Program</option>
                                </select>
                                @error('program')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Major</label>
                                <select name="major_name" class="form-select @error('major_name') is-invalid @enderror">
                                    <option value="">Select Major</option>
                                </select>
                                @error('major_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="col-md-4">
                                <label class="form-label">Year Level <span class="required">* </span></label>
                                <select name="year_level" class="form-select" required>
                                    <option value="">--</option>
                                    <option value="1">1st Year</option>
                                    <option value="2">2nd Year</option>
                                    <option value="3">3rd Year</option>
                                    <option value="4">4th Year</option>
                                    <option value="5">5th Year</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Expected Year to Graduate <span class="required">* </span></label>
                                <input type="text" name="expected_grad" class="form-control" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Leadership Involvement -->
                    <div class="form-step">
                        <h5 class="mb-3">Leadership Information</h5>
                        <div class="row g-3">
                            <!-- Leadership Type -->
                            <div class="col-md-4">
                                <label class="form-label">Leadership Type <span class="required">*</span></label>
                                <select name="leadership_type_id"
                                    class="form-select @error('leadership_type_id') is-invalid @enderror"
                                    required
                                    data-old="{{ old('leadership_type_id') }}">
                                    <option value="">Select Leadership Type</option>
                                    @foreach($leadershipTypes ?? [] as $type)
                                    <option value="{{ $type->id }}" {{ old('leadership_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('leadership_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Cluster -->
                            <div class="col-md-4">
                                <label class="form-label">Cluster <span class="required">*</span></label>
                                <select name="cluster_id"
                                    class="form-select @error('cluster_id') is-invalid @enderror"
                                    required
                                    data-old="{{ old('cluster_id') }}">
                                    <option value="">Select Cluster</option>
                                    <!-- optional: pre-fill if $clusters is passed from controller -->
                                </select>
                                @error('cluster_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Organization -->
                            <div class="col-md-4">
                                <label class="form-label">Organization <span class="required">*</span></label>
                                <select name="organization_id"
                                    class="form-select @error('organization_id') is-invalid @enderror"
                                    required
                                    data-old="{{ old('organization_id') }}">
                                    <option value="">Select Organization</option>
                                </select>
                                @error('organization_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Position -->
                            <div class="col-md-4">
                                <label class="form-label">Position Held <span class="required">*</span></label>
                                <select name="position_id"
                                    class="form-select @error('position_id') is-invalid @enderror"
                                    required
                                    data-old="{{ old('position_id') }}">
                                    <option value="">Select Position</option>
                                    <!-- dynamically filled via JS -->
                                </select>
                                @error('position_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            <!-- Term -->
                            <div class="col-md-4">
                                <label class="form-label">Term of Service <span class="required">*</span></label>
                                <input type="text" name="term" class="form-control @error('term') is-invalid @enderror"
                                    value="{{ old('term') }}" placeholder="e.g., 2024–2025" required>
                                @error('term')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Issued By -->
                            <div class="col-md-4">
                                <label class="form-label">Issued By <span class="required">*</span></label>
                                <input type="text" name="issued_by" class="form-control @error('issued_by') is-invalid @enderror"
                                    value="{{ old('issued_by') }}" required>
                                @error('issued_by')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Leadership Status -->
                            <div class="col-md-4">
                                <label class="form-label">Leadership Status <span class="required">*</span></label>
                                <select name="leadership_status" class="form-select @error('leadership_status') is-invalid @enderror" required>
                                    <option value="">Select Status</option>
                                    <option value="Active" {{ old('leadership_status') == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ old('leadership_status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('leadership_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Account Credentials -->
                    <div class="form-step">
                        <h5 class="mb-3">Login Credentials</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Password <span class="required">* </span></label>
                                <input type="password" name="password" id="password" class="form-control" required>
                                <ul class="password-requirements list-unstyled mt-2 small">
                                    <li id="length" class="text-danger"><i class="fa-regular fa-circle-xmark me-1"></i> At least 8 characters</li>
                                    <li id="uppercase" class="text-danger"><i class="fa-regular fa-circle-xmark me-1"></i> At least 1 uppercase letter</li>
                                    <li id="lowercase" class="text-danger"><i class="fa-regular fa-circle-xmark me-1"></i> At least 1 lowercase letter</li>
                                    <li id="number" class="text-danger"><i class="fa-regular fa-circle-xmark me-1"></i> At least 1 number</li>
                                    <li id="special" class="text-danger"><i class="fa-regular fa-circle-xmark me-1"></i> At least 1 special character</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password <span class="required">* </span></label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-check mt-3">
                            <input type="checkbox" class="form-check-input" id="privacy_agree" name="privacy_agree" required>
                            <label class="form-check-label" for="privacy_agree">
                                I agree to the <a href="https://www.usep.edu.ph/usep-data-privacy-statement/" target="_blank">Data Privacy Policy</a>.
                            </label>
                        </div>
                    </div>

                    <!-- Step Controls -->
                    <div class="pagination-controls d-flex justify-content-center align-items-center mt-3 mb-2 gap-1">
                        <button type="button" class="btn btn-secondary px-3 py-2" id="prevBtn" onclick="nextPrev(-1)" disabled>Back</button>
                        <div class="page-numbers d-flex gap-1">
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

        <!-- Floating Tools -->
        <div class="floating-tools d-md-none">
            <button id="darkModeToggleFloating" class="floating-btn" title="Toggle Dark Mode"><i class="fas fa-moon"></i></button>
            <a href="#" class="floating-btn" title="Send us a message"><i class="fa-solid fa-envelope"></i></a>
        </div>
    </div>

    <footer id="page-footer" class="mt-auto text-center py-3 small">
        &copy; {{ date('Y') }} University of Southeastern Philippines. All rights reserved.
    </footer>

    <script src="{{ asset('js/register.js') }}"></script>
</body>

</html>