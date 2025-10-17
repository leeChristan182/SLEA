<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'SLEA')</title>
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

    <div class="header-container">
        <div class="header">
            <div class="d-flex align-items-center gap-3">

                <!-- Logo -->
                <img src="{{ asset('images/osas-logo.png') }}" alt="USeP Logo" height="60">
                <span class="fs-3 fw-bolder logo-text">SLEA</span>
                <div style="width: 1px; height: 40px; background-color: #ccc; margin: 0 0.5rem;"></div>

                <!-- Tagline -->
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

    <div class="register-container">
        <main class="flex-grow-1">
            <div class="container py-5">
                <h4 class="text-maroon mb-4 fs-1 fw-bold">Sign Up!</h4>
                <p class="small fs-5 fw-normal">Already have an account? <a href="{{ route('login.show') }}">Login here</a></p>

                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif

                <form method="POST" action="{{ route('register.submit') }}">
                    @csrf

                    <!-- Step 1: Personal Information -->
                    <div class="form-step active">
                        <h5 class="mb-3">Personal Information</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Last Name *</label>
                                <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required>
                                @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">First Name *</label>
                                <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                                @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="middle_name" class="form-control @error('middle_name') is-invalid @enderror" value="{{ old('middle_name') }}">
                                @error('middle_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Birth Date *</label>
                                <input type="date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror" value="{{ old('birth_date') }}" required>
                                @error('birth_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Age *</label>
                                <input type="text" name="age" class="form-control @error('age') is-invalid @enderror" value="{{ old('age') }}" readonly>
                                @error('age')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Contact Number *</label>
                                <input type="text" name="contact" class="form-control @error('contact') is-invalid @enderror" value="{{ old('contact') }}" required>
                                @error('contact')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Academic Information -->
                    <div class="form-step">
                        <h5 class="mb-3">Academic Information</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Student ID *</label>
                                <input type="text" name="student_id" class="form-control @error('student_id') is-invalid @enderror" value="{{ old('student_id') }}" required placeholder="e.g. 2020-12345">
                                @error('student_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">College *</label>
                                <select name="college" class="form-select @error('college') is-invalid @enderror" required>
                                    <option value="">Select College</option>
                                    <option value="CIC" {{ old('college') == 'CIC' ? 'selected' : '' }}>CIC</option>
                                    <option value="COE" {{ old('college') == 'COE' ? 'selected' : '' }}>COE</option>
                                </select>
                                @error('college')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Program *</label>
                                <input type="text" name="program" class="form-control @error('program') is-invalid @enderror" value="{{ old('program') }}" required>
                                @error('program')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Major</label>
                                <input type="text" name="major" class="form-control @error('major') is-invalid @enderror" value="{{ old('major') }}">
                                @error('major')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Year Level *</label>
                                <select name="year_level" class="form-select @error('year_level') is-invalid @enderror" required>
                                    <option value="">Select Year</option>
                                    <option value="1st Year" {{ old('year_level') == '1st Year' ? 'selected' : '' }}>1st Year</option>
                                    <option value="2nd Year" {{ old('year_level') == '2nd Year' ? 'selected' : '' }}>2nd Year</option>
                                    <option value="3rd Year" {{ old('year_level') == '3rd Year' ? 'selected' : '' }}>3rd Year</option>
                                    <option value="4th Year" {{ old('year_level') == '4th Year' ? 'selected' : '' }}>4th Year</option>
                                </select>
                                @error('year_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Expected Year to Graduate *</label>
                                <input type="text" name="expected_grad" class="form-control @error('expected_grad') is-invalid @enderror" value="{{ old('expected_grad') }}" readonly>
                                @error('expected_grad')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Leadership Involvement -->
                    <div class="form-step">
                        <h5 class="mb-3">Leadership Involvement</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Leadership Type *</label>
                                <select name="leadership_type" class="form-select @error('leadership_type') is-invalid @enderror" required>
                                    <option value="">Select Type</option>
                                    <option value="President" {{ old('leadership_type') == 'President' ? 'selected' : '' }}>President</option>
                                    <option value="Member" {{ old('leadership_type') == 'Member' ? 'selected' : '' }}>Member</option>
                                </select>
                                @error('leadership_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Organization Name *</label>
                                <input type="text" name="org_name" class="form-control @error('org_name') is-invalid @enderror" value="{{ old('org_name') }}" required>
                                @error('org_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Organization Role *</label>
                                <select name="org_role" class="form-select @error('org_role') is-invalid @enderror" required>
                                    <option value="">Select Role</option>
                                    <option value="President" {{ old('org_role') == 'President' ? 'selected' : '' }}>President</option>
                                    <option value="Vice President" {{ old('org_role') == 'Vice President' ? 'selected' : '' }}>Vice President</option>
                                    <option value="Secretary" {{ old('org_role') == 'Secretary' ? 'selected' : '' }}>Secretary</option>
                                    <option value="Treasurer" {{ old('org_role') == 'Treasurer' ? 'selected' : '' }}>Treasurer</option>
                                    <option value="Member" {{ old('org_role') == 'Member' ? 'selected' : '' }}>Member</option>
                                </select>
                                @error('org_role')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Issued By *</label>
                                <input type="text" name="issued_by" class="form-control @error('issued_by') is-invalid @enderror" value="{{ old('issued_by') }}" required>
                                @error('issued_by')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Leadership Status *</label>
                                <select name="leadership_status" class="form-select @error('leadership_status') is-invalid @enderror" required>
                                    <option value="active" {{ old('leadership_status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('leadership_status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('leadership_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Term *</label>
                                <input type="text" name="term" class="form-control @error('term') is-invalid @enderror" value="{{ old('term') }}" required>
                                @error('term')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Credentials -->
                    <div class="form-step">
                        <h5 class="mb-3">Login Credentials</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Password *</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password" required>
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <ul class="password-requirements list-unstyled mt-2 small">
                                    <li id="length" class="text-danger"><i class="fa-regular fa-circle-xmark me-1"></i> At least 8 characters</li>
                                    <li id="uppercase" class="text-danger"><i class="fa-regular fa-circle-xmark me-1"></i> At least 1 uppercase letter</li>
                                    <li id="lowercase" class="text-danger"><i class="fa-regular fa-circle-xmark me-1"></i> At least 1 lowercase letter</li>
                                    <li id="number" class="text-danger"><i class="fa-regular fa-circle-xmark me-1"></i> At least 1 number</li>
                                    <li id="special" class="text-danger"><i class="fa-regular fa-circle-xmark me-1"></i> At least 1 special character</li>
                                </ul>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Confirm Password *</label>
                                <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" required>
                                @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-check mt-3">
                            <input type="checkbox" class="form-check-input @error('privacy_agree') is-invalid @enderror" id="privacy_agree" name="privacy_agree" {{ old('privacy_agree') ? 'checked' : '' }} required>
                            <label class="form-check-label" for="privacy_agree">
                                I agree to the <a href="#">Terms</a> & <a href="#">Privacy Policy</a>
                            </label>
                            @error('privacy_agree')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Pagination Controls -->
                    <div class="pagination-controls d-flex justify-content-center align-items-center mt-3 mb-2 gap-1">
                        <button type="button" class="btn btn-secondary px-3 py-2" id="prevBtn" onclick="nextPrev(-1)" disabled>
                            Back
                        </button>
                        <div class="page-numbers d-flex gap-1">
                            <span class="page-number active">1</span>
                            <span class="page-number">2</span>
                            <span class="page-number">3</span>
                            <span class="page-number">4</span>
                        </div>
                        <button type="button" class="btn btn-primary maroon-btn px-3 py-2" id="nextBtn" onclick="nextPrev(1)">
                            Next
                        </button>
                    </div>
                </form>
            </div>
        </main>

        <!-- Floating Tools (shown only on small devices) -->
        <div class="floating-tools d-md-none">
            <button id="darkModeToggleFloating" class="floating-btn" title="Toggle Dark Mode"><i class="fas fa-moon"></i></button>
            <a href="#" class="floating-btn" title="Send us a message"><i class="fa-solid fa-envelope"></i></a>
        </div>
    </div>

    <footer id="page-footer" class="mt-auto text-center py-3 small">
        &copy; {{ date('Y') }} University of Southeastern Philippines. All rights reserved.
    </footer>

    <script>
        // Age calculation
        document.querySelector('input[name="birth_date"]').addEventListener('change', function() {
            const birthDate = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            document.querySelector('input[name="age"]').value = age;
        });

        // Expected graduation year calculation
        document.querySelector('select[name="year_level"]').addEventListener('change', function() {
            const yearLevel = this.value;
            const currentYear = new Date().getFullYear();
            let expectedGrad = '';

            switch (yearLevel) {
                case '1st Year':
                    expectedGrad = currentYear + 3;
                    break;
                case '2nd Year':
                    expectedGrad = currentYear + 2;
                    break;
                case '3rd Year':
                    expectedGrad = currentYear + 1;
                    break;
                case '4th Year':
                    expectedGrad = currentYear;
                    break;
            }

            document.querySelector('input[name="expected_grad"]').value = expectedGrad;
        });

        // Form step navigation
        let currentStep = 0;
        const steps = document.querySelectorAll('.form-step');
        const totalSteps = steps.length;

        function showStep(step) {
            steps.forEach((s, index) => {
                s.classList.toggle('active', index === step);
            });

            // Update page numbers
            document.querySelectorAll('.page-number').forEach((page, index) => {
                page.classList.toggle('active', index === step);
            });

            // Update buttons
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');

            prevBtn.disabled = step === 0;

            if (step === totalSteps - 1) {
                nextBtn.textContent = 'Submit';
                nextBtn.type = 'submit';
            } else {
                nextBtn.textContent = 'Next';
                nextBtn.type = 'button';
            }
        }

        function nextPrev(direction) {
            if (direction === 1) {
                if (currentStep < totalSteps - 1) {
                    currentStep++;
                }
            } else {
                if (currentStep > 0) {
                    currentStep--;
                }
            }
            showStep(currentStep);
        }

        // Initialize
        showStep(0);
    </script>

</body>
<script>
    const passwordInput = document.getElementById('password');
    const requirements = {
        length: document.getElementById('length'),
        uppercase: document.getElementById('uppercase'),
        lowercase: document.getElementById('lowercase'),
        number: document.getElementById('number'),
        special: document.getElementById('special')
    };

    passwordInput.addEventListener('input', function() {
        const value = passwordInput.value;

        // Regex checks
        const hasLength = value.length >= 8;
        const hasUppercase = /[A-Z]/.test(value);
        const hasLowercase = /[a-z]/.test(value);
        const hasNumber = /\d/.test(value);
        const hasSpecial = /[^A-Za-z0-9]/.test(value);

        updateRequirement(requirements.length, hasLength);
        updateRequirement(requirements.uppercase, hasUppercase);
        updateRequirement(requirements.lowercase, hasLowercase);
        updateRequirement(requirements.number, hasNumber);
        updateRequirement(requirements.special, hasSpecial);
    });

    function updateRequirement(element, isValid) {
        if (isValid) {
            element.classList.remove('text-danger');
            element.classList.add('text-success');
            element.querySelector('i').classList.remove('fa-circle-xmark');
            element.querySelector('i').classList.add('fa-circle-check');
        } else {
            element.classList.remove('text-success');
            element.classList.add('text-danger');
            element.querySelector('i').classList.remove('fa-circle-check');
            element.querySelector('i').classList.add('fa-circle-xmark');
        }
    }
</script>

</html>
