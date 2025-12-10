<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SLEA - Registration</title>
    <link rel="icon" href="{{ asset('images/osas-logo.png') }}?v={{ filemtime(public_path('images/osas-logo.png')) }}" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    {{-- Prevent browser from caching --}}
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    {{-- Cross-browser compatibility --}}
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="renderer" content="webkit">
    {{-- CSRF --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
                <button id="darkModeToggle" class="dark-toggle-btn" title="Toggle Dark Mode">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Registration Content -->
    <div class="register-container d-flex flex-column">
        <main class="flex-grow-1" style="overflow: hidden; min-height: 0;">
            <div class="container py-1">
                <div class="signup-card">
                    <h4 class="signup-title">Sign up now</h4>
                    <p class="signup-login-link">
                        Already have account? <a href="{{ route('login.show') }}">Login here</a>
                    </p>

                    {{-- Error Modal will be shown via JavaScript if errors exist --}}

                    @if (session('status'))
                        <div class="alert alert-success" role="status">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register.store') }}" id="registerForm" novalidate>
                        @csrf

                        {{-- First Name and Last Name - Side by Side --}}
                        <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="first_name">
                                        First Name <span class="required">*</span>
                                    </label>
                                    <input
                                        id="first_name"
                                        type="text"
                                        name="first_name"
                                        class="form-control @error('first_name') is-invalid @enderror"
                                        value="{{ old('first_name') }}"
                                        required
                                        autocomplete="given-name">
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="last_name">
                                        Last Name <span class="required">*</span>
                                    </label>
                                    <input
                                        id="last_name"
                                        type="text"
                                        name="last_name"
                                        class="form-control @error('last_name') is-invalid @enderror"
                                        value="{{ old('last_name') }}"
                                        required
                                        autocomplete="family-name">
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                        </div>

                        {{-- Email - Full Width --}}
                        <div class="mb-3">
                                <label class="form-label" for="email">
                                    USeP Email <span class="required">*</span>
                                </label>
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    placeholder="example@usep.edu.ph"
                                    value="{{ old('email') }}"
                                    required
                                    autocomplete="email">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                        </div>

                        {{-- Password and Confirm Password - Side by Side --}}
                        <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="password">
                                        Password <span class="required">*</span>
                                    </label>
                                    <input
                                        id="password"
                                        type="password"
                                        name="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        required
                                        autocomplete="new-password">
                                    <div id="passwordError" class="password-error-message" style="display: none;"></div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="password_confirmation">
                                        Confirm Password <span class="required">*</span>
                                    </label>
                                    <input
                                        id="password_confirmation"
                                        type="password"
                                        name="password_confirmation"
                                        class="form-control"
                                        required
                                        autocomplete="new-password">
                                </div>
                        </div>

                        {{-- Password Requirements --}}
                        <div class="mb-2">
                                <small class="form-text text-muted" style="font-size: 0.75rem;">
                                    Use 8 or more characters with a mix of letters, numbers, and symbols.
                                </small>
                        </div>

                        {{-- Show Password Checkbox --}}
                        <div class="mb-2">
                            <div class="form-check show-password-checkbox">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="showPasswordCheckbox"
                                    name="show_password"
                                    value="1">
                                <label class="form-check-label" for="showPasswordCheckbox">
                                    Show password
                                </label>
                            </div>
                        </div>

                        {{-- Privacy Agreement --}}
                        <div class="mb-2">
                            <div class="form-check privacy-checkbox-wrapper">
                                <input
                                    class="form-check-input @error('privacy_agree') is-invalid @enderror"
                                    type="checkbox"
                                    name="privacy_agree"
                                    id="privacy_agree"
                                    value="1"
                                    required>
                                <label class="form-check-label privacy-text-label" for="privacy_agree">
                                    <span class="privacy-text">
                                        By continuing to browse this website, you agree to the University of Southeastern Philippines' Data Privacy Statement. The full text of The Statement can be accessed through this <a href="https://www.usep.edu.ph/usep-data-privacy-statement/" target="_blank">link</a>.
                                        <span class="required">*</span>
                                    </span>
                                </label>
                            </div>
                            @error('privacy_agree')
                                <div class="invalid-feedback d-block privacy-error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Submit Button --}}
                        <div class="text-end">
                            <button type="submit" class="btn btn-signup">
                                Sign Up
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    {{-- Error Modal --}}
    <div id="errorModal" class="error-modal-overlay" style="display: none;">
        <div class="error-modal-content">
            <button type="button" class="error-modal-close" id="errorModalClose">
                <i class="fas fa-times"></i>
            </button>
            <div class="error-modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h5 class="error-modal-title">Login Error!</h5>
            <div class="error-modal-body">
                <p class="error-modal-subtitle">Please check the following:</p>
                <ul id="errorModalList" class="error-modal-list"></ul>
            </div>
            <button type="button" class="error-modal-btn" id="errorModalOk">
                Okay
            </button>
        </div>
    </div>

    {{-- Success Modal --}}
    <div id="successModal" class="success-modal-overlay" style="display: none;">
        <div class="success-modal-content">
            <button type="button" class="success-modal-close" id="successModalClose">
                <i class="fas fa-times"></i>
            </button>
            <div class="success-modal-icon">
                <i class="fas fa-bell"></i>
            </div>
            <h5 class="success-modal-title">Your application is now<br>pending for approval!</h5>
            <div class="success-modal-body">
                <p>Wait for approval from the admin. If your sign-up for SLEA<br>is approved, a message will be sent to your email.</p>
            </div>
            <button type="button" class="success-modal-btn" id="successModalOk">
                Okay
            </button>
        </div>
    </div>

    {{-- Footer --}}
    <footer id="page-footer" class="text-center py-2">
        <small class="text-muted">
            &copy; {{ date('Y') }} University of Southeastern Philippines. All rights reserved.
            <a href="https://www.usep.edu.ph/usep-data-privacy-statement/" target="_blank">Data Privacy Statement</a>
        </small>
    </footer>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/dark-mode.js') }}"></script>
    {{-- register.js removed - it's for the old multi-step form, not needed for universal registration --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show password checkbox functionality - Cross-browser compatible
            const showPasswordCheckbox = document.getElementById('showPasswordCheckbox');
            const passwordField = document.getElementById('password');
            const passwordConfirmationField = document.getElementById('password_confirmation');

            // Function to toggle password visibility
            function togglePasswordVisibility() {
                if (!passwordField || !passwordConfirmationField) {
                    console.warn('Password fields not found');
                    return;
                }
                
                const isChecked = showPasswordCheckbox ? showPasswordCheckbox.checked : false;
                const newType = isChecked ? 'text' : 'password';
                
                // Use multiple methods for cross-browser compatibility
                try {
                    // Direct property assignment (modern browsers)
                    passwordField.type = newType;
                    passwordConfirmationField.type = newType;
                    // Also set attribute (for older browsers)
                    passwordField.setAttribute('type', newType);
                    passwordConfirmationField.setAttribute('type', newType);
                } catch (e) {
                    console.error('Error changing password type:', e);
                    // Fallback for older browsers
                    if (isChecked) {
                        passwordField.setAttribute('type', 'text');
                        passwordConfirmationField.setAttribute('type', 'text');
                    } else {
                        passwordField.setAttribute('type', 'password');
                        passwordConfirmationField.setAttribute('type', 'password');
                    }
                }
            }

            // Toggle both passwords with checkbox - Multiple event handlers for cross-browser support
            if (showPasswordCheckbox && passwordField && passwordConfirmationField) {
                // Primary handler - change event (most reliable, fires after checkbox state changes)
                showPasswordCheckbox.addEventListener('change', function(e) {
                    e.stopPropagation();
                    togglePasswordVisibility();
                }, false);
                
                // Secondary handler - click event (for better cross-browser support)
                showPasswordCheckbox.addEventListener('click', function(e) {
                    // Small delay to ensure checkbox state is updated first
                    setTimeout(function() {
                        togglePasswordVisibility();
                    }, 10);
                }, false);
                
                // Handle click on label (Bootstrap default behavior)
                const showPasswordLabel = document.querySelector('label[for="showPasswordCheckbox"]');
                if (showPasswordLabel) {
                    showPasswordLabel.addEventListener('click', function(e) {
                        // Longer delay to let default checkbox toggle happen first
                        setTimeout(function() {
                            togglePasswordVisibility();
                        }, 20);
                    }, false);
                }
                
                // Also handle mousedown for immediate feedback
                showPasswordCheckbox.addEventListener('mousedown', function(e) {
                    // Don't prevent default, just prepare for toggle
                    setTimeout(function() {
                        togglePasswordVisibility();
                    }, 15);
                }, false);
                
                // Initialize - check if checkbox is already checked (for page refresh)
                if (showPasswordCheckbox.checked) {
                    togglePasswordVisibility();
                }
            } else {
                console.warn('Show password checkbox or password fields not found:', {
                    checkbox: !!showPasswordCheckbox,
                    password: !!passwordField,
                    confirm: !!passwordConfirmationField
                });
            }

            // Real-time password validation
            const passwordErrorDiv = document.getElementById('passwordError');
            const passwordRequirements = {
                minLength: 8,
                hasUppercase: /[A-Z]/,
                hasLowercase: /[a-z]/,
                hasNumber: /[0-9]/,
                hasSpecial: /[^A-Za-z0-9]/
            };

            function validatePassword(password) {
                const errors = [];
                
                if (password.length < passwordRequirements.minLength) {
                    errors.push('Password must be at least 8 characters long');
                }
                if (!passwordRequirements.hasUppercase.test(password)) {
                    errors.push('Password must contain at least one uppercase letter');
                }
                if (!passwordRequirements.hasLowercase.test(password)) {
                    errors.push('Password must contain at least one lowercase letter');
                }
                if (!passwordRequirements.hasNumber.test(password)) {
                    errors.push('Password must contain at least one number');
                }
                if (!passwordRequirements.hasSpecial.test(password)) {
                    errors.push('Password must contain at least one special character');
                }
                
                return errors;
            }

            if (passwordField && passwordErrorDiv) {
                passwordField.addEventListener('input', function() {
                    const password = this.value;
                    const errors = validatePassword(password);
                    
                    if (password.length > 0 && errors.length > 0) {
                        // Show error
                        passwordField.classList.add('is-invalid');
                        passwordErrorDiv.textContent = errors[0]; // Show first error
                        passwordErrorDiv.style.display = 'block';
                    } else if (password.length > 0 && errors.length === 0) {
                        // Password is valid
                        passwordField.classList.remove('is-invalid');
                        passwordErrorDiv.style.display = 'none';
                    } else {
                        // Empty password
                        passwordField.classList.remove('is-invalid');
                        passwordErrorDiv.style.display = 'none';
                    }
                });

                passwordField.addEventListener('blur', function() {
                    const password = this.value;
                    if (password.length > 0) {
                        const errors = validatePassword(password);
                        if (errors.length > 0) {
                            passwordField.classList.add('is-invalid');
                            passwordErrorDiv.textContent = errors[0];
                            passwordErrorDiv.style.display = 'block';
                        }
                    }
                });
            }

            // Error modal handlers - Set up once on page load
            const errorModal = document.getElementById('errorModal');
            const errorModalClose = document.getElementById('errorModalClose');
            const errorModalOk = document.getElementById('errorModalOk');
            const errorList = document.getElementById('errorModalList');

            function hideErrorModal() {
                if (errorModal) {
                    errorModal.style.display = 'none';
                    document.body.style.overflow = '';
                }
            }

            // Set up error modal button handlers once
            if (errorModalClose) {
                errorModalClose.addEventListener('click', hideErrorModal);
            }
            if (errorModalOk) {
                errorModalOk.addEventListener('click', hideErrorModal);
            }
            if (errorModal) {
                errorModal.addEventListener('click', function(e) {
                    if (e.target === errorModal) {
                        hideErrorModal();
                    }
                });
            }

            // Success modal handlers
            const successModal = document.getElementById('successModal');
            const successModalOk = document.getElementById('successModalOk');
            const successModalClose = document.getElementById('successModalClose');

            function showSuccessModal() {
                if (successModal) {
                    successModal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
            }

            function hideSuccessModal() {
                if (successModal) {
                    successModal.style.display = 'none';
                    document.body.style.overflow = '';
                    // Redirect to login page immediately
                    window.location.href = '{{ route("login.show") }}';
                }
            }

            // Okay button - redirect to login immediately
            successModalOk?.addEventListener('click', function() {
                window.location.href = '{{ route("login.show") }}';
            });
            
            // Close button - also redirect to login
            successModalClose?.addEventListener('click', function() {
                window.location.href = '{{ route("login.show") }}';
            });
            
            // Click on backdrop - also redirect to login
            successModal?.addEventListener('click', function(e) {
                if (e.target === successModal) {
                    window.location.href = '{{ route("login.show") }}';
                }
            });

            // Handle form submission with AJAX to show success modal
            const registerForm = document.getElementById('registerForm');
            if (registerForm) {
                const submitBtn = registerForm.querySelector('button[type="submit"]');
                
                registerForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    // Validate password before submission
                    const password = passwordField.value;
                    const errors = validatePassword(password);
                    
                    if (password.length > 0 && errors.length > 0) {
                        passwordField.classList.add('is-invalid');
                        passwordErrorDiv.textContent = errors[0];
                        passwordErrorDiv.style.display = 'block';
                        passwordField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return false;
                    }

                    // Check privacy agreement
                    const privacyCheckbox = document.getElementById('privacy_agree');
                    if (!privacyCheckbox.checked) {
                        privacyCheckbox.classList.add('is-invalid');
                        privacyCheckbox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return false;
                    }

                    const formData = new FormData(registerForm);
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                    // Disable submit button to prevent double submission
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Submitting...';
                    }

                    try {
                        const response = await fetch(registerForm.action, {
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

                        // Check response type
                        const contentType = response.headers.get('content-type');
                        const isJson = contentType && contentType.includes('application/json');
                        
                        console.log('Response status:', response.status);
                        console.log('Is JSON:', isJson);
                        console.log('Response headers:', Object.fromEntries(response.headers.entries()));

                        if (response.status === 422) {
                            // Validation errors
                            if (isJson) {
                                const data = await response.json();
                                console.log('Validation error data:', data);
                                
                                if (data.errors) {
                                    // Show error modal with specific field errors
                                    errorList.innerHTML = '';
                                    
                                    // Collect all error messages
                                    const allErrors = [];
                                    Object.keys(data.errors).forEach(field => {
                                        if (Array.isArray(data.errors[field])) {
                                            data.errors[field].forEach(msg => allErrors.push(msg));
                                        } else {
                                            allErrors.push(data.errors[field]);
                                        }
                                    });
                                    
                                    // Display all errors
                                    allErrors.forEach(errorMsg => {
                                        const li = document.createElement('li');
                                        li.textContent = errorMsg;
                                        errorList.appendChild(li);
                                    });
                                    
                                    // Also show the main message if available
                                    if (data.message && allErrors.length === 0) {
                                        errorList.innerHTML = '<li>' + data.message + '</li>';
                                    }
                                    
                                    errorModal.style.display = 'flex';
                                    document.body.style.overflow = 'hidden';
                                } else if (data.message) {
                                    // Single error message
                                    errorList.innerHTML = '<li>' + data.message + '</li>';
                                    errorModal.style.display = 'flex';
                                    document.body.style.overflow = 'hidden';
                                } else {
                                    errorList.innerHTML = '<li>Please check all fields and try again.</li>';
                                    errorModal.style.display = 'flex';
                                    document.body.style.overflow = 'hidden';
                                }
                            } else {
                                // Non-JSON validation error - try to get text
                                const text = await response.text();
                                console.error('Non-JSON validation error:', text);
                                errorList.innerHTML = '<li>Please check all fields and try again.</li>';
                                errorModal.style.display = 'flex';
                                document.body.style.overflow = 'hidden';
                            }
                        } else if (response.status === 200 && isJson) {
                            // Success - parse JSON response
                            const data = await response.json();
                            console.log('Success data:', data);
                            
                            if (data.success) {
                                showSuccessModal();
                            } else {
                                // Success false but 200 status
                                const errorMsg = data.message || 'An error occurred. Please try again.';
                                errorList.innerHTML = '<li>' + errorMsg + '</li>';
                                errorModal.style.display = 'flex';
                                document.body.style.overflow = 'hidden';
                            }
                        } else if (response.status === 200 || response.ok) {
                            // Success - show success modal
                            showSuccessModal();
                        } else {
                            // Try to parse error response
                            let errorMessage = 'An error occurred. Please try again.';
                            let allErrorMessages = [];
                            
                            if (isJson) {
                                try {
                                    const data = await response.json();
                                    console.log('Error response data:', data);
                                    
                                    if (data.message) {
                                        allErrorMessages.push(data.message);
                                    }
                                    
                                    if (data.errors) {
                                        // Flatten all error messages
                                        Object.keys(data.errors).forEach(field => {
                                            if (Array.isArray(data.errors[field])) {
                                                data.errors[field].forEach(msg => allErrorMessages.push(msg));
                                            } else {
                                                allErrorMessages.push(data.errors[field]);
                                            }
                                        });
                                    }
                                    
                                    // Show debug info if available (only in development)
                                    if (data.debug) {
                                        console.error('Debug info:', data.debug);
                                    }
                                    
                                    errorMessage = allErrorMessages.length > 0 ? allErrorMessages.join(', ') : errorMessage;
                                } catch (e) {
                                    console.error('Error parsing JSON response:', e);
                                    // Try to get text response
                                    try {
                                        const text = await response.text();
                                        console.error('Error response text:', text);
                                        if (text && text.length < 500) {
                                            errorMessage = text;
                                        }
                                    } catch (textError) {
                                        console.error('Error getting text response:', textError);
                                    }
                                }
                            } else {
                                // Try to get text response
                                try {
                                    const text = await response.text();
                                    console.error('Non-JSON error response:', text);
                                    if (text && text.length < 500) {
                                        errorMessage = text.substring(0, 200);
                                    }
                                } catch (textError) {
                                    console.error('Error getting text response:', textError);
                                }
                            }
                            
                            // Show error modal with all error messages
                            errorList.innerHTML = '';
                            if (allErrorMessages.length > 0) {
                                allErrorMessages.forEach(msg => {
                                    const li = document.createElement('li');
                                    li.textContent = msg;
                                    errorList.appendChild(li);
                                });
                            } else {
                                errorList.innerHTML = '<li>' + errorMessage + '</li>';
                            }
                            errorModal.style.display = 'flex';
                            document.body.style.overflow = 'hidden';
                        }
                    } catch (error) {
                        console.error('Registration error:', error);
                        console.error('Error stack:', error.stack);
                        
                        // Re-enable submit button on error
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Sign Up';
                        }
                        
                        // Show detailed error message
                        let errorMsg = 'An error occurred during registration. Please try again.';
                        if (error.message) {
                            errorMsg = error.message;
                        }
                        
                        errorList.innerHTML = '<li>' + errorMsg + '</li>';
                        errorModal.style.display = 'flex';
                        document.body.style.overflow = 'hidden';
                    }
                });
            }

            // Show error modal if there are server-side errors (from validation redirect)
            @if ($errors->any())
                // Clear existing list items
                errorList.innerHTML = '';
                
                // Add error messages to list
                @foreach ($errors->all() as $error)
                    const li = document.createElement('li');
                    li.textContent = '{{ addslashes($error) }}';
                    errorList.appendChild(li);
                @endforeach
                
                // Show modal
                errorModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            @endif
        });
    </script>
</body>

</html>

