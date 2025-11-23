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

<body class="d-flex flex-column {{ session('dark_mode', false) ? 'dark-mode' : '' }}">
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

    <div class="d-flex flex-column flex-md-row m-0 p-0 login-main-container">
        <div class="login-left flex-shrink-0 flex-fill">
            <div class="login-content-wrapper">
                <h3 class="login-welcome-title">Welcome, USePians!</h3>
                <p class="login-subtitle">Please login to get started.</p>

                {{-- Validation & Status - Fixed height container to prevent layout shift --}}
                <div class="error-message-container">
                    <div class="error-placeholder" style="display: {{ ($errors->any() || session('status')) ? 'none' : 'block' }};"></div>
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
                </div>

                {{-- Login Form --}}
                <form method="POST" action="{{ route('login.auth') }}" autocomplete="on" autocapitalize="none" autocorrect="off" novalidate>
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-normal" style="color:white;">USeP Email</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="e.g. juandelacruz001@usep.edu.ph"
                                value="{{ old('email') }}"
                                required
                                inputmode="email"
                                autocomplete="username"
                                pattern="^[a-zA-Z0-9._%+\-]+@usep\.edu\.ph$">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <small class="text-light-50 d-block mt-1">Use your <strong>@usep.edu.ph</strong> email.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-normal" style="color:white;">Password</label>
                        <div class="input-group input-group-lg password-input-wrapper">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input
                                type="password"
                                name="password"
                                id="passwordInput"
                                class="form-control @error('password') is-invalid @enderror"
                                required
                                autocomplete="current-password"
                                data-custom-toggle="true">
                            <button class="toggle-password" type="button" title="Show/Hide">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <label class="form-check-label text-light">
                            <input class="form-check-input me-2" type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                            Remember me
                        </label>
                        {{-- <a href="{{ route('password.request') }}" class="link-light">Forgot password?</a> --}}
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">
                        <i class="fas fa-sign-in-alt me-2"></i> Log In
                    </button>

                    <div class="text-center signup-link-wrapper">
                        <small style="color:white; font-size: 0.85rem;">Don't have an account? <a href="{{ route('register.show') }}">Sign Up</a></small>
                    </div>
                </form>
            </div>

            <div class="footer-wrapper text-center">
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
                    <img src="{{ asset('images/security-illustration.png') }}" alt="Security" class="mb-4" style="max-width:230px;">
                    <p class="mb-4 fs-5 px-3">
                        By continuing to use the <strong>Student Portal</strong>, you agree to the
                        <a href="https://www.usep.edu.ph/usep-data-privacy-statement/" target="_blank" class="text-decoration-none text-danger fw-semibold">
                            University of Southeastern Philippines’ Data Privacy Statement
                        </a>.
                    </p>
                    <button type="button" class="btn btn-danger px-5 py-2 rounded-pill fw-bold mt-auto" data-bs-dismiss="modal">CONTINUE</button>
                </div>
                <div class="w-100" style="height:12px;background-color:#C84848;"></div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/login.js') }}"></script>
</body>

</html>