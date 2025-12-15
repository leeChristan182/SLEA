<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'SLEA')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/osas-logo.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Prevent browser from caching login page --}}
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    {{-- Optional: CSRF meta (handy for JS if ever needed) --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Global CSS -->
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Refresh CSRF token function
        function refreshCsrfToken() {
            return fetch('{{ route("login.show") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newToken = doc.querySelector('meta[name="csrf-token"]')?.content;
                if (newToken) {
                    // Update meta tag
                    const metaTag = document.querySelector('meta[name="csrf-token"]');
                    if (metaTag) {
                        metaTag.setAttribute('content', newToken);
                    }
                    // Update all CSRF token inputs
                    document.querySelectorAll('input[name="_token"]').forEach(input => {
                        input.value = newToken;
                    });
                    return newToken;
                }
                return null;
            })
            .catch(error => {
                console.error('Failed to refresh CSRF token:', error);
                return null;
            });
        }

        // Handle 419 errors globally
        document.addEventListener('DOMContentLoaded', function() {
            // Intercept form submissions to refresh token if needed
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    // Refresh token before submission if form has been on page for a while
                    const formAge = Date.now() - (window.formLoadTime || Date.now());
                    if (formAge > 60000) { // If form has been on page for more than 1 minute
                        e.preventDefault();
                        refreshCsrfToken().then(() => {
                            form.submit();
                        });
                    }
                });
            });

            // Store page load time
            window.formLoadTime = Date.now();
        });
    </script>

    @yield('head')
</head>

<body class="d-flex flex-column min-vh-100 {{ session('dark_mode', false) ? 'dark-mode' : '' }}">

    <!-- Header -->
    <div class="header-container">
        <div class="header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <img src="{{ asset('images/osas-logo.png') }}" alt="USeP Logo" height="60">
                <span class="fs-3 fw-bolder logo-text">SLEA</span>
                <div style="width:1px;height:40px;background-color:#ccc;margin:0 .5rem;"></div>
                <div class="tagline ms-3">
                    <span class="gold1">Empowering</span> <span class="maroon1">Leadership.</span><br>
                    <span class="maroon1">Recognizing</span> <span class="gold1">Excellence.</span>
                </div>
            </div>

            <div class="header-right d-flex align-items-center gap-3">
                <button id="darkModeToggle" class="dark-toggle-btn" title="Toggle Dark Mode" type="button">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column flex-md-row m-0 p-0" style="flex:1 1 auto;height:100vh;">
        <div class="login-left flex-shrink-0 flex-fill">
            <div style="max-width:640px;margin:0 auto;width:100%;">

                <div class="welcome-section">
                    <h3 class="text-center mb-2 display-5 fw-bold" style="font-family:'Quicksand','sans-serif';">
                        Welcome, USePians!
                    </h3>
                    <p class="text-center mb-0 display-6 fw-normal" style="color:#F9BD3D">
                        Please login to get started.
                    </p>
                </div>

                {{-- Validation (used for modals, not inline alerts) --}}
                @php
                    $loginStatus = session('status');

                    // Exclude OTP errors from the generic error modal list
                    $loginErrors = collect($errors->getMessages())
                        ->except(['otp'])
                        ->flatten()
                        ->values()
                        ->all();
                @endphp


                {{-- Login Form --}}
                <div class="login-form-wrapper">
                    <form id="loginForm" method="POST" action="{{ route('login.auth') }}" autocomplete="off"
                        autocorrect="off" autocapitalize="none" novalidate>
                        @csrf

                        {{-- Dummy fields to trap browser autofill --}}
                        <input type="text" name="fake_username" autocomplete="username" style="display:none;">
                        <input type="password" name="fake_password" autocomplete="current-password"
                            style="display:none;">

                        {{-- Real fields actually submitted (HIDDEN) --}}
                        <input type="hidden" name="email" id="email_real"
                            value="{{ old('email', $rememberedEmail ?? '') }}">
                        <input type="hidden" name="password" id="password_real">

                        {{-- EMAIL (visible, NO name so browser won't bind credentials) --}}
                        <div class="mb-2">
                            <label class="form-label fs-5 fw-normal text-light">USeP Email</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-envelope"></i>
                                </span>

                                <input type="email" id="email_display"
                                    class="form-control"
                                    placeholder="e.g. juandelacruz001@usep.edu.ph"
                                    value="{{ old('email', $rememberedEmail ?? '') }}" required inputmode="email"
                                    autocomplete="off" spellcheck="false" pattern="^[a-zA-Z0-9._%+\-]+@usep\.edu\.ph$">
                            </div>
                        </div>

                        {{-- PASSWORD (visible, NO name; real one is hidden) --}}
                        <div class="mb-2">
                            <label class="form-label fs-5 fw-normal text-light">Password</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-lock"></i>
                                </span>

                                <input type="password" id="passwordInput"
                                    class="form-control @error('password') is-invalid @enderror" required
                                    autocomplete="off">

                                <button class="input-group-text toggle-password" type="button" id="loginPasswordToggle"
                                    title="Show/Hide">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>

                            {{-- move the error OUTSIDE the input-group so it doesn't break layout --}}
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- REMEMBER ME + FORGOT PASSWORD (unchanged) --}}
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="form-check">
                                <input class="form-check-input me-2" type="checkbox" id="remember" name="remember"
                                    value="1" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label text-light" for="remember">
                                    Remember me
                                </label>
                            </div>

                            <button type="button" class="btn btn-link p-0 text-light text-decoration-none small"
                                data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                                Forgot password?
                            </button>
                        </div>

                        {{-- SUBMIT --}}
                        <div class="text-center">
                            <button type="submit" id="loginSubmitBtn"
                                class="btn btn-primary btn-lg fw-bold login-submit-btn">
                                <i class="fas fa-sign-in-alt me-2"></i> Log In
                            </button>
                        </div>

                        <div class="text-center mt-2">
                            <small class="text-light signup-link-text">
                                Don't have an account?
                                <a href="#" id="openSignupOverlay">Sign Up</a>
                            </small>
                        </div>
                    </form>
                </div>
            </div>

            <div class="footer-wrapper text-center fs-6">
                &copy; {{ date('Y') }} University of Southeastern Philippines. All rights reserved.<br>
                <a href="#" target="_blank">Terms of Use</a> |
                <a href="https://www.usep.edu.ph/usep-data-privacy-statement/" target="_blank">Privacy Policy</a>
            </div>
        </div>

        <div class="login-right flex-fill d-none d-md-block">
            <div class="mascot-wrapper">
                <img src="{{ asset('images/final_usep_vector_2.png') }}" alt="Mascot" class="mascot-img">
            </div>
        </div>
    </div>

    {{-- Floating Tools (mobile) --}}
    <div class="floating-tools d-md-none">
        <button id="darkModeToggleFloating" class="floating-btn" title="Toggle Dark Mode" type="button">
            <i class="fas fa-moon"></i>
        </button>
        <a href="#" class="floating-btn" title="Send us a message">
            <i class="fa-solid fa-square-envelope"></i>
        </a>
    </div>

    {{-- =============== PRIVACY MODAL =============== --}}
    <div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content text-center privacy-modal-card shadow-lg border-0 overflow-hidden">
                <div class="modal-body px-5 py-5 d-flex flex-column align-items-center" style="min-height:460px;">
                    <img src="{{ asset('images/security-illustration.png') }}" alt="Security" class="mb-4"
                        style="max-width:230px;">
                    <p class="mb-4 fs-5 px-3 text-dark">
                        By continuing to use this platform, you agree to the
                        <a href="https://www.usep.edu.ph/usep-data-privacy-statement/" target="_blank"
                            class="text-decoration-none privacy-link fw-semibold">
                            University of Southeastern Philippines’ Data Privacy Statement
                        </a>.
                    </p>
                    <button type="button" class="btn btn-maroon px-5 py-2 rounded-pill fw-bold mt-auto"
                        data-bs-dismiss="modal">
                        CONTINUE
                    </button>
                </div>
                <div class="w-100 privacy-accent-bar"></div>
            </div>
        </div>
    </div>

    {{-- =============== FORGOT PASSWORD MODAL =============== --}}
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content forgot-password-modal-content border-0" style="border-radius: 0 !important;">
                <div class="modal-header forgot-password-modal-header border-0">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">Forgot Password</h5>
                    <button type="button" class="btn-close forgot-password-modal-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body forgot-password-modal-body">
                    <p class="mb-3">
                        Enter your registered <strong>@usep.edu.ph</strong> email.
                        We'll send you an OTP to reset your password.
                    </p>

                    <form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">USeP Email</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-envelope"></i>
                                </span>
                                <input type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                                    required inputmode="email" autocomplete="email"
                                    pattern="^[a-zA-Z0-9._%+\-]+@usep\.edu\.ph$">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa-solid fa-paper-plane me-2"></i> Send OTP
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>

    {{-- =============== OTP MODAL (LOGIN & PASSWORD RESET) =============== --}}
    <div class="modal fade" id="otpModal" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content otp-modal-content border-0" style="border-radius: 0 !important;">
                <div class="modal-header border-0 otp-modal-header">
                    <h5 class="modal-title" id="otpModalLabel">One-Time Password</h5>
                    <button type="button" class="btn-close otp-modal-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body otp-modal-body">
                    <p class="mb-3">
                        Enter the 6-digit code sent to
                        <strong>{{ session('otp_display_email') ?? 'your email' }}</strong>.
                    </p>

                    {{-- VERIFY OTP --}}
                    <form method="POST" action="{{ route('otp.verify') }}" id="otpForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">OTP Code</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-key"></i>
                                </span>
                                <input type="text" name="otp" class="form-control" maxlength="6" required
                                    inputmode="numeric" pattern="\d{6}">
                            </div>
                            <small class="otp-validity-text d-block mt-1">
                                This code is valid for {{ config('auth.otp.lifetime_minutes', 10) }} minutes.
                            </small>
                        </div>

                        @if ($errors->has('otp'))
                            <div class="alert alert-danger py-1 px-2">{{ $errors->first('otp') }}</div>
                        @endif

                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            Verify
                        </button>
                    </form>

                    {{-- RESEND OTP --}}
                    <form method="POST" action="{{ route('otp.resend') }}" id="resendOtpForm" class="text-end mt-1">
                        @csrf
                        <button type="submit" class="btn btn-link otp-resend-link text-decoration-none p-0 small">
                            Resend code
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>

    {{-- =============== RESET PASSWORD MODAL (AFTER OTP) =============== --}}
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content reset-password-modal-content border-0" style="border-radius: 0 !important;">
                <div class="modal-header reset-password-modal-header border-0">
                    <h5 class="modal-title" id="resetPasswordModalLabel">Set New Password</h5>
                    <button type="button" class="btn-close reset-password-modal-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body reset-password-modal-body">

                    <p class="mb-3">
                        OTP verified. Set your new password below.
                    </p>

                    <form method="POST" action="{{ route('password.update') }}" id="resetPasswordForm">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="reset-password-validity-text d-block mt-1">
                                At least 8 characters with uppercase, lowercase, number, and symbol.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            Update Password
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>

    {{-- =============== LOGIN ERROR MODAL =============== --}}
    @if (!empty($loginErrors))
        <div class="modal fade" id="loginErrorModal" tabindex="-1" aria-labelledby="loginErrorModalLabel" aria-hidden="true"
            data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content login-error-modal-content border-0">
                    <button type="button" class="login-error-modal-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="login-error-modal-body">
                        <div class="login-error-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3 class="login-error-title">Login Error!</h3>
                        <p class="login-error-text">Please check the following:</p>
                        <ul class="login-error-list">
                            @foreach ($loginErrors as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn login-error-ok-btn" data-bs-dismiss="modal">
                            Okay
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- =============== LOGIN SUCCESS MODAL =============== --}}
    @if (!empty($loginStatus))
        @php
            $isOtpMessage = str_contains(strtolower($loginStatus), 'otp') || str_contains(strtolower($loginStatus), 'one-time password');
            $isRegistrationMessage = str_contains(strtolower($loginStatus), 'registration received') ||
                str_contains(strtolower($loginStatus), 'account approval');
        @endphp
        @if (!$isRegistrationMessage)
            <div class="modal fade" id="loginSuccessModal" tabindex="-1" aria-labelledby="loginSuccessModalLabel"
                aria-hidden="true"
                data-otp-followup="{{ $isOtpMessage && (session('show_otp_modal') || session()->has('otp_pending_user_id')) ? 'true' : 'false' }}">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 login-success-modal-content">
                        <div class="modal-header bg-success text-white border-0">
                            <h5 class="modal-title d-flex align-items-center gap-2" id="loginSuccessModalLabel">
                                <i class="fas fa-check-circle"></i>
                                Success
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-0">
                                {{ $loginStatus }}
                            </p>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-success" id="successModalOkBtn" data-bs-dismiss="modal">
                                OK
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- =============== ACCOUNT DISABLED MODAL =============== --}}
    <div class="modal fade" id="accountDisabledModal" tabindex="-1" aria-labelledby="accountDisabledModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title d-flex align-items-center gap-2" id="accountDisabledModalLabel">
                        <i class="fas fa-exclamation-triangle"></i>
                        Account Disabled
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="alert alert-danger d-flex align-items-start gap-3 mb-0" role="alert">
                        <i class="fas fa-ban fa-2x mt-1"></i>
                        <div>
                            <h6 class="alert-heading fw-bold mb-2">
                                Your account has been disabled by the administrator.
                            </h6>
                            <p class="mb-0">
                                Please go to the OSAS office to discuss this problem.
                                For further assistance, you may contact the Office of Student Affairs and Services.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- =============== SIGNUP OVERLAY (INLINE) =============== --}}
    <div class="signup-overlay" id="signupOverlay" aria-hidden="true">
        <div class="signup-modal">
            <button type="button" class="signup-close" id="closeSignupOverlay" aria-label="Close">&times;</button>
            <h4 class="signup-title mb-1">Sign up now</h4>
            <p class="signup-login-link mb-3">
                Already have account?
                <a href="{{ route('login.show') }}">Login here</a>
            </p>

            <form method="POST" action="{{ route('register.store') }}" id="signupFormInline" novalidate>
                @csrf

                <div class="row g-3 mb-2">
                    <div class="col-md-4">
                        <label class="form-label" for="first_name_inline">First Name <span class="required">*</span></label>
                        <input id="first_name_inline" type="text" name="first_name" class="form-control" required autocomplete="given-name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="middle_name_inline">Middle Name</label>
                        <input id="middle_name_inline" type="text" name="middle_name" class="form-control" autocomplete="additional-name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="last_name_inline">Last Name <span class="required">*</span></label>
                        <input id="last_name_inline" type="text" name="last_name" class="form-control" required autocomplete="family-name">
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label" for="email_address_inline">USeP Email <span class="required">*</span></label>
                    <input id="email_address_inline" type="email" name="email_address" class="form-control" placeholder="example@usep.edu.ph" required autocomplete="email">
                </div>

                <div class="row g-3 mb-2">
                    <div class="col-md-6">
                        <label class="form-label" for="contact_inline">Contact Number <span class="required">*</span></label>
                        <input id="contact_inline" type="text" name="contact" class="form-control" placeholder="09XXXXXXXXX" required autocomplete="tel">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="birth_date_inline">Birth Date</label>
                        <input id="birth_date_inline" type="date" name="birth_date" class="form-control">
                    </div>
                </div>

                <div class="row g-3 mb-2">
                    <div class="col-md-6">
                        <label class="form-label" for="password_inline">Password <span class="required">*</span></label>
                        <input id="password_inline" type="password" name="password" class="form-control" required autocomplete="new-password">
                        <div id="signupPasswordFeedback" class="signup-password-feedback" aria-live="polite"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password_confirmation_inline">Confirm Password <span class="required">*</span></label>
                        <input id="password_confirmation_inline" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                    </div>
                </div>

                <div class="mb-2">
                    <small class="form-text text-muted" style="font-size: 0.75rem;">
                        Use 8 or more characters with a mix of letters, numbers, and symbols.
                    </small>
                </div>

                <div class="mb-2 d-flex align-items-center gap-2">
                    <input class="form-check-input" type="checkbox" id="showPasswordInline">
                    <label class="form-check-label" for="showPasswordInline">Show password</label>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="privacy_agree" id="privacy_agree_inline" value="1" required>
                        <label class="form-check-label" for="privacy_agree_inline" style="font-size:0.8rem;">
                            By continuing, you agree to the University of Southeastern Philippines' Data Privacy Statement.
                            Read it through this <a href="https://www.usep.edu.ph/usep-data-privacy-statement/" target="_blank">link</a>.
                            <span class="required">*</span>
                        </label>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-signup-inline">Sign Up</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Privacy Agreement Error Modal --}}
    <div class="modal fade" id="privacyAgreementErrorModal" tabindex="-1" aria-labelledby="privacyAgreementErrorModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content login-error-modal-content border-0">
                <div class="login-error-modal-body">
                    <div class="login-error-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 class="login-error-title">Privacy Agreement Required</h3>
                    <p class="login-error-text">You must agree to the Data Privacy Statement to continue.</p>
                    <button type="button" class="btn login-error-ok-btn" data-bs-dismiss="modal">
                        Okay
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Success Modal for Signup --}}
    <div id="signupSuccessModal" class="signup-success-modal-overlay" style="display: none;">
        <div class="signup-success-modal-content">
            <button type="button" class="signup-success-modal-close" id="signupSuccessModalClose">
                <i class="fas fa-times"></i>
            </button>
            <div class="signup-success-modal-icon">
                <i class="fas fa-bell"></i>
            </div>
            <h5 class="signup-success-modal-title">Your application is now<br>pending for approval!</h5>
            <div class="signup-success-modal-body">
                <p>Wait for approval from the admin. If your sign-up for SLEA<br>is approved, a message will be sent to your email.</p>
            </div>
            <button type="button" class="signup-success-modal-btn" id="signupSuccessModalOk">
                Okay
            </button>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/login.js') }}"></script>

    {{-- Auto-open modals based on session flags --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // 1) Disabled always wins
            @if (session('show_disabled_modal'))
                new bootstrap.Modal(document.getElementById('accountDisabledModal'), {
                    backdrop: 'static',
                    keyboard: false
                }).show();
                return;
            @endif

            // 2) Reset password next
            @if (session('show_reset_modal'))
                new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
                return;
            @endif

            // 3) Forgot password next
            @if (session('show_forgot_modal'))
                new bootstrap.Modal(document.getElementById('forgotPasswordModal')).show();
                return;
            @endif

                // 4) Generic login errors (but NOT otp)
                @if ($errors->any() && !$errors->has('otp'))
                    var errorModalEl = document.getElementById('loginErrorModal');
                    if (errorModalEl) {
                        var modal = new bootstrap.Modal(errorModalEl);
                        
                        errorModalEl.addEventListener('shown.bs.modal', function () {
                            var backdrop = document.querySelector('.modal-backdrop');
                            if (backdrop) {
                                backdrop.style.backdropFilter = 'blur(5px)';
                                backdrop.style.webkitBackdropFilter = 'blur(5px)';
                                backdrop.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
                                backdrop.classList.add('login-error-backdrop');
                            }
                        });

                        errorModalEl.addEventListener('hide.bs.modal', function () {
                            var backdrop = document.querySelector('.modal-backdrop');
                            if (backdrop) {
                                backdrop.style.backdropFilter = '';
                                backdrop.style.webkitBackdropFilter = '';
                            }
                        });

                        modal.show();
                    }
                    return;
                @endif

                // 5) Success modal (then OTP follow-up if needed)
                @if (session('status'))
                    var successModalEl = document.getElementById('loginSuccessModal');
                    if (successModalEl) {
                        var successModal = new bootstrap.Modal(successModalEl, {
                            backdrop: true
                        });
                        var isOtpFollowup = successModalEl.getAttribute('data-otp-followup') === 'true';

                        // Add blur to backdrop when modal is shown
                        successModalEl.addEventListener('shown.bs.modal', function () {
                            var backdrop = document.querySelector('.modal-backdrop');
                            if (backdrop) {
                                backdrop.style.backdropFilter = 'blur(10px)';
                                backdrop.style.webkitBackdropFilter = 'blur(10px)';
                                backdrop.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
                            }
                        });

                        if (isOtpFollowup) {
                            successModalEl.addEventListener('hidden.bs.modal', function () {
                                setTimeout(function () {
                                    new bootstrap.Modal(document.getElementById('otpModal')).show();
                                }, 100);
                            }, { once: true });
                        }

                        successModal.show();
                    }
                    return;
                @endif

            // 6) OTP modal direct open (skip for admin accounts)
            @if (session('show_otp_modal') || session()->has('otp_pending_user_id'))
                @php
                    $pendingUserId = session('otp_pending_user_id');
                    $shouldShowOtp = true;
                    if ($pendingUserId) {
                        $pendingUser = \App\Models\User::find($pendingUserId);
                        if ($pendingUser && $pendingUser->isAdmin()) {
                            $shouldShowOtp = false;
                            // Clear OTP session data for admin
                            session()->forget(['otp_pending_user_id', 'otp_context', 'otp_remember_me', 'otp_display_email', 'show_otp_modal']);
                        }
                    }
                @endphp
                @if ($shouldShowOtp)
                    new bootstrap.Modal(document.getElementById('otpModal')).show();
                    return;
                @endif
            @endif

});
    </script>

    {{-- Signup overlay controls --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const overlay = document.getElementById('signupOverlay');
            const openLink = document.getElementById('openSignupOverlay');
            const closeBtn = document.getElementById('closeSignupOverlay');
            const showPw = document.getElementById('showPasswordInline');
            const pw = document.getElementById('password_inline');
            const pwc = document.getElementById('password_confirmation_inline');
            const pwFeedback = document.getElementById('signupPasswordFeedback');
            const signupForm = document.getElementById('signupFormInline');

            function openSignup(e) {
                if (e) e.preventDefault();
                if (!overlay) return;
                overlay.classList.add('active');
                overlay.setAttribute('aria-hidden', 'false');
                document.body.classList.add('signup-overlay-open');
                // Focus first field
                const first = document.getElementById('first_name_inline');
                if (first) { first.focus(); }
            }

            function closeSignup(e) {
                if (e) e.preventDefault();
                if (!overlay) return;
                overlay.classList.remove('active');
                overlay.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('signup-overlay-open');
            }

            openLink?.addEventListener('click', openSignup);
            closeBtn?.addEventListener('click', closeSignup);
            overlay?.addEventListener('click', function (evt) {
                if (evt.target === overlay) {
                    closeSignup();
                }
            });
            document.addEventListener('keyup', function (evt) {
                if (evt.key === 'Escape' && overlay?.classList.contains('active')) {
                    closeSignup();
                }
            });

            showPw?.addEventListener('change', function () {
                const newType = this.checked ? 'text' : 'password';
                if (pw) pw.type = newType;
                if (pwc) pwc.type = newType;
            });

            // Password requirements validation (keep layout stable by reserving space for message)
            function getPasswordError(password) {
                if (!password) return null;
                if (password.length < 8) return 'Password must be at least 8 characters.';
                if (!/[A-Za-z]/.test(password)) return 'Password must include at least one letter.';
                if (!/[0-9]/.test(password)) return 'Password must include at least one number.';
                if (!/[^A-Za-z0-9]/.test(password)) return 'Password must include at least one symbol.';
                return null;
            }

            function setPasswordValidity() {
                if (!pw || !pwFeedback) return true;
                const error = getPasswordError(pw.value);

                if (error) {
                    pw.classList.add('is-invalid');
                    pwFeedback.textContent = error;
                    pwFeedback.classList.add('show');
                    return false;
                }

                pw.classList.remove('is-invalid');
                pwFeedback.textContent = '';
                pwFeedback.classList.remove('show');
                return true;
            }

            pw?.addEventListener('input', setPasswordValidity);
            pw?.addEventListener('blur', setPasswordValidity);

            // Success modal handlers
            const signupSuccessModal = document.getElementById('signupSuccessModal');
            const signupSuccessModalOk = document.getElementById('signupSuccessModalOk');
            const signupSuccessModalClose = document.getElementById('signupSuccessModalClose');

            function showSignupSuccessModal() {
                if (signupSuccessModal) {
                    signupSuccessModal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                    // Close signup overlay
                    closeSignup();
                }
            }

            function hideSignupSuccessModal() {
                if (signupSuccessModal) {
                    signupSuccessModal.style.display = 'none';
                    document.body.style.overflow = '';
                }
            }

            signupSuccessModalOk?.addEventListener('click', function() {
                hideSignupSuccessModal();
                window.location.reload();
            });

            signupSuccessModalClose?.addEventListener('click', function() {
                hideSignupSuccessModal();
                window.location.reload();
            });

            signupSuccessModal?.addEventListener('click', function(e) {
                if (e.target === signupSuccessModal) {
                    hideSignupSuccessModal();
                    window.location.reload();
                }
            });

            // Handle signup form submission with AJAX
            signupForm?.addEventListener('submit', async function (e) {
                e.preventDefault();

                // Validate password
                const ok = setPasswordValidity();
                if (!ok) {
                    pw?.focus();
                    return;
                }

                // Check privacy agreement
                const privacyCheckbox = document.getElementById('privacy_agree_inline');
                if (!privacyCheckbox || !privacyCheckbox.checked) {
                    if (privacyCheckbox) {
                        privacyCheckbox.classList.add('is-invalid');
                    }
                    // Show custom modal instead of browser alert
                    const privacyErrorModalEl = document.getElementById('privacyAgreementErrorModal');
                    const privacyErrorModal = new bootstrap.Modal(privacyErrorModalEl, {
                        backdrop: 'static'
                    });
                    
                    // Ensure modal appears above signup overlay
                    privacyErrorModalEl.style.zIndex = '2100';
                    
                    // Add blur to backdrop when modal is shown
                    privacyErrorModalEl.addEventListener('shown.bs.modal', function () {
                        // Find the backdrop (it should be the last one created)
                        const backdrops = document.querySelectorAll('.modal-backdrop');
                        const backdrop = backdrops[backdrops.length - 1];
                        if (backdrop) {
                            backdrop.style.zIndex = '2099';
                            backdrop.style.backdropFilter = 'blur(5px)';
                            backdrop.style.webkitBackdropFilter = 'blur(5px)';
                            backdrop.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
                            backdrop.classList.add('login-error-backdrop', 'privacy-agreement-backdrop');
                        }
                        // Ensure modal dialog is on top
                        const modalDialog = privacyErrorModalEl.querySelector('.modal-dialog');
                        if (modalDialog) {
                            modalDialog.style.zIndex = '2101';
                        }
                    });

                    privacyErrorModalEl.addEventListener('hidden.bs.modal', function () {
                        const backdrops = document.querySelectorAll('.modal-backdrop');
                        const backdrop = backdrops[backdrops.length - 1];
                        if (backdrop) {
                            backdrop.style.backdropFilter = '';
                            backdrop.style.webkitBackdropFilter = '';
                        }
                        // Focus on privacy checkbox after modal closes
                        if (privacyCheckbox) {
                            privacyCheckbox.focus();
                        }
                    });

                    privacyErrorModal.show();
                    return;
                }

                const formData = new FormData(signupForm);
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                const submitBtn = signupForm.querySelector('button[type="submit"]');

                // Disable submit button
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Submitting...';
                }

                try {
                    const response = await fetch(signupForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    // Re-enable submit button
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Sign Up';
                    }

                    const contentType = response.headers.get('content-type');
                    const isJson = contentType && contentType.includes('application/json');

                    if (response.status === 422) {
                        // Validation errors
                        if (isJson) {
                            const data = await response.json();
                            if (data.errors) {
                                // Show first error
                                const firstError = Object.values(data.errors)[0][0];
                                alert(firstError);
                            }
                        }
                        return;
                    }

                    if (response.ok && isJson) {
                        const data = await response.json();
                        if (data.success || data.ok) {
                            showSignupSuccessModal();
                            return;
                        }
                    }

                    if (response.ok) {
                        showSignupSuccessModal();
                        return;
                    }

                    alert('Registration failed. Please try again.');
                } catch (error) {
                    console.error('Registration error:', error);
                    alert('An error occurred during registration. Please try again.');
                }
            });
        });
    </script>

    {{-- Password show/hide --}}


    {{-- Handle browser back/forward to avoid stale state --}}
    <script>
        window.addEventListener('pageshow', function (event) {
            let navType = null;
            if (performance && performance.getEntriesByType) {
                const entries = performance.getEntriesByType('navigation');
                if (entries && entries.length > 0) {
                    navType = entries[0].type; // 'navigate' | 'reload' | 'back_forward'
                }
            }

            const cameFromHistory = event.persisted || navType === 'back_forward';

            if (cameFromHistory) {
                window.location.reload();
                return;
            }

            const submitBtn = document.getElementById('loginSubmitBtn');
            if (submitBtn) {
                submitBtn.disabled = false;
                if (submitBtn.dataset.originalHtml) {
                    submitBtn.innerHTML = submitBtn.dataset.originalHtml;
                } else {
                    submitBtn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i> Log In';
                }
            }

            const form = document.getElementById('loginForm');
            const passwordInput = document.getElementById('passwordInput');

            if (form) {
                form.classList.remove('was-validated');
            }

            if (passwordInput) {
                passwordInput.value = '';
            }
        });



    </script>


</body>

</html>