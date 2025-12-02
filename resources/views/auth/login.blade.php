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
                    $loginErrors = $errors->any() ? $errors->all() : [];
                    $loginStatus = session('status');
                @endphp

                {{-- Login Form --}}
                <div class="login-form-wrapper">
                <form id="loginForm" method="POST" action="{{ route('login.auth') }}" autocomplete="off"
                    autocorrect="off" autocapitalize="none" novalidate>
                    @csrf

                    {{-- Dummy fields to trap browser autofill --}}
                    <input type="text" name="fake_username" autocomplete="username" style="display:none;">
                    <input type="password" name="fake_password" autocomplete="current-password" style="display:none;">

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
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="e.g. juandelacruz001@usep.edu.ph"
                                value="{{ old('email', $rememberedEmail ?? '') }}" required inputmode="email"
                                autocomplete="off" spellcheck="false" pattern="^[a-zA-Z0-9._%+\-]+@usep\.edu\.ph$">

                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                            <input class="form-check-input me-2" type="checkbox" id="remember" name="remember" value="1"
                                {{ old('remember') ? 'checked' : '' }}>
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
                        <button type="submit" id="loginSubmitBtn" class="btn btn-primary btn-lg fw-bold login-submit-btn">
                            <i class="fas fa-sign-in-alt me-2"></i> Log In
                        </button>
                    </div>

                    <div class="text-center mt-2">
                        <small class="text-light signup-link-text">
                            Don't have an account?
                            <a href="{{ route('register.show') }}">Sign Up</a>
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
            <div class="modal-content text-center bg-dark text-light shadow-lg border-0 overflow-hidden" style="border-radius: 0 !important;">
                <div class="modal-body px-5 py-5 d-flex flex-column align-items-center" style="min-height:460px;">
                    <img src="{{ asset('images/security-illustration.png') }}" alt="Security" class="mb-4"
                        style="max-width:230px;">
                    <p class="mb-4 fs-5 px-3">
                        By continuing to use the <strong>Student Portal</strong>, you agree to the
                        <a href="https://www.usep.edu.ph/usep-data-privacy-statement/" target="_blank"
                            class="text-decoration-none text-danger fw-semibold">
                            University of Southeastern Philippines’ Data Privacy Statement
                        </a>.
                    </p>
                    <button type="button" class="btn btn-danger px-5 py-2 rounded-pill fw-bold mt-auto"
                        data-bs-dismiss="modal">
                        CONTINUE
                    </button>
                </div>
                <div class="w-100" style="height:12px;background-color:#C84848;"></div>
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
        <div class="modal fade" id="loginErrorModal" tabindex="-1" aria-labelledby="loginErrorModalLabel"
            aria-hidden="true" data-bs-backdrop="static">
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
                aria-hidden="true" data-otp-followup="{{ $isOtpMessage && (session('show_otp_modal') || session()->has('otp_pending_user_id')) ? 'true' : 'false' }}">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0">
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

    {{-- Handle Privacy Modal with localStorage --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalEl = document.getElementById('privacyModal');

            if (!modalEl || typeof bootstrap === 'undefined') {
                return;
            }

            const STORAGE_KEY = 'slea_privacy_ack_v2';

            if (localStorage.getItem(STORAGE_KEY) === '1') {
                return;
            }

            const privacyModal = new bootstrap.Modal(modalEl, {
                backdrop: 'static',
                keyboard: false
            });

            modalEl.addEventListener('hidden.bs.modal', function () {
                localStorage.setItem(STORAGE_KEY, '1');
            }, { once: true });

            privacyModal.show();
        });
    </script>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/login.js') }}"></script>

    {{-- Auto-open modals based on session flags --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            @if (session('show_forgot_modal'))
                var forgotModal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
                forgotModal.show();
            @endif

                @if (session('show_reset_modal'))
                    var resetModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
                    resetModal.show();
                @endif

                @if (session('show_disabled_modal'))
                    var disabledModal = new bootstrap.Modal(document.getElementById('accountDisabledModal'), {
                        backdrop: 'static',
                        keyboard: false
                    });
                    disabledModal.show();
                @endif

                @if ($errors->any())
                    var errorModalEl = document.getElementById('loginErrorModal');
                    if (errorModalEl) {
                        // Add blur to backdrop when modal is shown
                        errorModalEl.addEventListener('shown.bs.modal', function() {
                            var backdrop = document.querySelector('.modal-backdrop');
                            if (backdrop) {
                                backdrop.style.backdropFilter = 'blur(5px)';
                                backdrop.style.webkitBackdropFilter = 'blur(5px)';
                                backdrop.classList.add('login-error-backdrop');
                            }
                        });
                        
                        var errorModal = new bootstrap.Modal(errorModalEl);
                        errorModal.show();
                    }
                @endif

                // Show success modal first if status exists (but not for registration approval messages)
                @if (session('status'))
                    var successModalEl = document.getElementById('loginSuccessModal');
                    if (successModalEl) {
                        var successModal = new bootstrap.Modal(successModalEl);
                        var isOtpFollowup = successModalEl.getAttribute('data-otp-followup') === 'true';
                        
                        // If this is an OTP success message, set up handler to show OTP modal after success modal closes
                        if (isOtpFollowup) {
                            // Listen for when success modal is hidden, then show OTP modal
                            successModalEl.addEventListener('hidden.bs.modal', function() {
                                setTimeout(function() {
                                    var otpModal = new bootstrap.Modal(document.getElementById('otpModal'));
                                    otpModal.show();
                                }, 100); // Small delay to ensure modal is fully closed
                            }, { once: true });
                        }
                        
                        successModal.show();
                    }
                @elseif (session('show_otp_modal') || session()->has('otp_pending_user_id'))
                    // Only show OTP modal directly if there's no success message
                    var otpModal = new bootstrap.Modal(document.getElementById('otpModal'));
                    otpModal.show();
                @endif

        });
    </script>

    {{-- Password show/hide --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('passwordInput');
            const toggleBtn = document.getElementById('loginPasswordToggle');

            if (!passwordInput || !toggleBtn) return;

            toggleBtn.addEventListener('click', function (e) {
                e.preventDefault();

                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';

                const icon = toggleBtn.querySelector('i');
                if (icon) {
                    if (isHidden) {
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                        toggleBtn.setAttribute('title', 'Hide password');
                    } else {
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                        toggleBtn.setAttribute('title', 'Show password');
                    }
                }

                passwordInput.focus();
                const len = passwordInput.value.length;
                passwordInput.setSelectionRange(len, len);
            });
        });
    </script>

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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('loginForm');
            const emailDisplay = document.getElementById('email_display');
            const emailReal = document.getElementById('email_real');
            const passwordInput = document.getElementById('passwordInput');
            const passwordReal = document.getElementById('password_real');

            if (!form) return;

            form.addEventListener('submit', function () {
                if (emailDisplay && emailReal) {
                    emailReal.value = emailDisplay.value.trim();
                }

                if (passwordInput && passwordReal) {
                    passwordReal.value = passwordInput.value;
                }
            });

            // Show/hide password (your existing logic)
            const toggleBtn = document.getElementById('loginPasswordToggle');
            if (passwordInput && toggleBtn) {
                toggleBtn.addEventListener('click', function (e) {
                    e.preventDefault();

                    const isHidden = passwordInput.type === 'password';
                    passwordInput.type = isHidden ? 'text' : 'password';

                    const icon = toggleBtn.querySelector('i');
                    if (icon) {
                        if (isHidden) {
                            icon.classList.remove('fa-eye');
                            icon.classList.add('fa-eye-slash');
                            toggleBtn.setAttribute('title', 'Hide password');
                        } else {
                            icon.classList.remove('fa-eye-slash');
                            icon.classList.add('fa-eye');
                            toggleBtn.setAttribute('title', 'Show password');
                        }
                    }

                    // keep focus & move cursor to end
                    passwordInput.focus();
                    const len = passwordInput.value.length;
                    passwordInput.setSelectionRange(len, len);
                });
            }
        });
    </script>

</body>

</html>