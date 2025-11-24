<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'SLEA')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/osas-logo.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">

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
        <div class="header">
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
                <div class="text-end">
                    <small>Having Trouble?</small><br>
                    <a href="#">Send us a message</a>
                </div>
                <button id="darkModeToggle" class="dark-toggle-btn" title="Toggle Dark Mode" type="button">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column flex-md-row m-0 p-0" style="flex:1 1 auto;height:100vh;">
        <div class="login-left flex-shrink-0 flex-fill">
            <div style="max-width:640px;margin:0 auto;width:100%;">

                <h3 class="text-center mb-4 display-5 fw-bold" style="font-family:'Quicksand','sans-serif';">
                    Welcome, USePians!
                </h3>
                <p class="text-center mb-5 display-6 fw-normal" style="color:#F9BD3D">Please login to get started.</p>

                {{-- Validation --}}
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
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                {{-- Login Form --}}
                <form id="loginForm" method="POST" action="{{ route('login.auth') }}" autocomplete="off"
                    autocorrect="off" autocapitalize="none" novalidate>

                    @csrf

                    {{-- Dummy fields to trap browser autofill --}}
                    <input type="text" name="fake_username" autocomplete="username" style="display:none;">
                    <input type="password" name="fake_password" autocomplete="current-password" style="display:none;">

                    {{-- Real fields actually submitted --}}
                    <input type="hidden" name="email" id="email_real" value="{{ old('email') }}">
                    <input type="hidden" name="password" id="password_real">

                    {{-- EMAIL (visible, no name so browser won’t bind credentials) --}}
                    <div class="mb-4">
                        <label class="form-label fs-5 fw-normal text-light">USeP Email</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">
                                <i class="fa-solid fa-envelope"></i>
                            </span>

                            <input type="email" id="email_display"
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="e.g. juandelacruz001@usep.edu.ph" value="" required inputmode="email"
                                autocomplete="off" spellcheck="false" pattern="^[a-zA-Z0-9._%+\-]+@usep\.edu\.ph$">

                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <small class="text-light-50 d-block mt-1">
                            Use your <strong>@usep.edu.ph</strong> email.
                        </small>
                    </div>

                    {{-- PASSWORD (visible, no name; real one is hidden) --}}
                    <div class="mb-4">
                        <label class="form-label fs-5 fw-normal text-light">Password</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">
                                <i class="fa-solid fa-lock"></i>
                            </span>

                            <input type="password" id="passwordInput"
                                class="form-control @error('password') is-invalid @enderror" required
                                autocomplete="off">

                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <button class="input-group-text toggle-password" type="button" title="Show/Hide">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    {{-- REMEMBER ME + FORGOT PASSWORD (modal trigger) --}}
                    <div class="d-flex align-items-center justify-content-between mb-3">
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
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">
                        <i class="fas fa-sign-in-alt me-2"></i> Log In
                    </button>

                    <div class="text-center mt-3 fs-6 fw-bold">
                        <small class="text-light">
                            Don’t have an account?
                            <a href="{{ route('register.show') }}">Sign Up</a>
                        </small>
                    </div>

                </form>
            </div>

            <div class="footer-wrapper text-center mt-1 fs-6">
                &copy; {{ date('Y') }} University of Southeastern Philippines. All rights reserved.<br>
                <a href="#" target="_blank">Terms of Use</a> |
                <a href="https://www.usep.edu.ph/usep-data-privacy-statement/" target="_blank">Privacy Policy</a>
            </div>
        </div>

        <div class="login-right flex-fill d-none d-md-block">
            <div class="mascot-wrapper">
                <img src="{{ asset('images/usep-mascot1.png') }}" alt="Mascot" class="mascot-img">
            </div>
        </div>
    </div>

    {{-- Floating Tools --}}
    <div class="floating-tools d-md-none">
        <button id="darkModeToggleFloating" class="floating-btn" title="Toggle Dark Mode" type="button">
            <i class="fas fa-moon"></i>
        </button>
        <a href="#" class="floating-btn" title="Send us a message">
            <i class="fa-solid fa-square-envelope"></i>
        </a>
    </div>

    {{-- Privacy Modal --}}
    <div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content text-center bg-dark text-light rounded-4 shadow-lg border-0 overflow-hidden">
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
            <div class="modal-content bg-dark text-light rounded-4 border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">Forgot Password</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <p class="mb-3">
                        Enter your registered <strong>@usep.edu.ph</strong> email. We’ll send you an OTP to reset your
                        password.
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
            <div class="modal-content bg-dark text-light rounded-4 border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="otpModalLabel">One-Time Password</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">

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
                            <small class="text-light-50 d-block mt-1">
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
                        <button type="submit" class="btn btn-link text-warning text-decoration-none p-0 small">
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
            <div class="modal-content bg-dark text-light rounded-4 border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="resetPasswordModalLabel">Set New Password</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">

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
                            <small class="text-light-50 d-block mt-1">
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

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/login.js') }}"></script>

    {{-- Auto-open modals based on session flags --}}
    <!-- prettier-ignore-start -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            @if (session('show_forgot_modal'))
                var forgotModal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
                forgotModal.show();
            @endif

                @if (session('show_otp_modal') || session()->has('otp_pending_user_id'))
                    var otpModal = new bootstrap.Modal(document.getElementById('otpModal'));
                    otpModal.show();
                @endif

                @if (session('show_reset_modal'))
                    var resetModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
                    resetModal.show();
                @endif

    });
    </script>
    <!-- prettier-ignore-end -->

</body>

</html>