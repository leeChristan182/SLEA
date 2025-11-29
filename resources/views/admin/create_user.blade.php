@extends('layouts.app')

@section('title', 'Create Account')

@section('content')
<div class="container">
    @include('partials.sidebar')

    <main class="main-content">
        <div class="page-header">
            <h1>Create Assessor's Account</h1>
        </div>

        {{-- Messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('generated_password'))
            <div class="alert alert-info alert-dismissible fade show password-alert" role="alert">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fas fa-key"></i>
                    <strong>System Generated Password:</strong>
                </div>
                <div class="password-display">
                    <code id="generatedPassword" class="password-code">{{ session('generated_password') }}</code>
                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="copyPassword()" title="Copy password">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
                <div class="small text-muted mt-2">
                    <i class="fas fa-exclamation-triangle"></i> Please save this password. The assessor will need it to log in. This will not be shown again after you close this alert.
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Create Account Form --}}
        <div class="form-container">
            <form action="{{ route('admin.store_user') }}" method="POST" id="createUserForm">
                @csrf
                <input type="hidden" name="role" value="assessor">

                <div class="form-row">
                    <div class="form-group">
                        <label for="last_name">Last Name <span class="required">*</span></label>
                        <input type="text" id="last_name" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="first_name">First Name <span class="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="middle_name">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name" class="form-control" value="{{ old('middle_name') }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="contact">Contact (optional)</label>
                        <input type="text" id="contact" name="contact" class="form-control" value="{{ old('contact') }}">
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="save-btn" id="saveBtn">Save</button>
                    <button type="button" class="cancel-btn" onclick="window.history.back()">Cancel</button>
                </div>
            </form>
        </div>
    </main>
</div>

<link rel="stylesheet" href="{{ asset('css/pending-submissions.css') }}">
<style>
    /* Single viewport layout - no scrollbar */
    html, body {
        height: 100%;
        overflow: hidden;
    }

    /* Account for header and footer */
    .container {
        height: calc(100vh - 200px);
        min-height: calc(100vh - 200px);
        max-height: calc(100vh - 200px);
        overflow: hidden;
        display: flex;
    }

    .main-content {
        height: 100%;
        overflow: visible;
        display: flex;
        flex-direction: column;
        padding: 0.5rem 1.5rem;
        box-sizing: border-box;
    }

    .page-header {
        flex-shrink: 0;
        margin-bottom: 0.5rem;
    }

    .page-header h1 {
        margin-bottom: 0;
        font-size: 2rem;
        font-weight: 700;
        color: #7E0308;
    }

    body.dark-mode .page-header h1 {
        color: #f9bd3d;
    }

    /* Alerts container */
    .main-content > .alert {
        flex-shrink: 0;
        margin-bottom: 0.5rem;
    }

    /* Form Container */
    .form-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 1rem;
        margin-top: 0.25rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 0;
        overflow: visible;
    }

    body.dark-mode .form-container {
        background: #2a2a2a;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    #createUserForm {
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: visible;
        gap: 0;
    }

    /* Form Rows */
    .form-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.6rem;
        margin-bottom: 0.6rem;
        flex-shrink: 0;
    }

    .form-row:last-of-type {
        grid-template-columns: repeat(2, 1fr);
        margin-bottom: 0.5rem;
    }

    /* Form Groups */
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }

    .form-group label {
        font-weight: 600;
        color: #333;
        font-size: 0.8rem;
    }

    body.dark-mode .form-group label {
        color: #f0f0f0;
    }

    .form-group .form-control {
        padding: 0.45rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 0.85rem;
        background: white;
        transition: all 0.2s ease;
    }

    body.dark-mode .form-group .form-control {
        background: #4a4a4a;
        border-color: #555;
        color: #f0f0f0;
    }

    .form-group .form-control:focus {
        outline: none;
        border-color: #7E0308;
        box-shadow: 0 0 0 3px rgba(126, 3, 8, 0.1);
    }

    body.dark-mode .form-group .form-control:focus {
        border-color: #f9bd3d;
        box-shadow: 0 0 0 3px rgba(249, 189, 61, 0.2);
    }

    .required {
        color: #dc3545;
    }

    /* Button Group */
    .button-group {
        display: flex;
        gap: 0.6rem;
        justify-content: center;
        margin-top: auto;
        padding-top: 0.6rem;
        border-top: 1px solid #dee2e6;
        flex-shrink: 0;
    }

    body.dark-mode .button-group {
        border-top-color: #555;
    }

    .save-btn,
    .cancel-btn {
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        border: none;
    }

    .save-btn {
        background: #28a745;
        color: white;
    }

    .save-btn:hover {
        background: #218838;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
    }

    .cancel-btn {
        background: #6c757d;
        color: white;
    }

    .cancel-btn:hover {
        background: #5a6268;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
    }

    /* Alerts */
    .alert {
        margin-bottom: 0.5rem;
        padding: 0.75rem 1rem;
        border-radius: 6px;
        flex-shrink: 0;
        font-size: 0.85rem;
        position: relative;
    }

    .alert-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }

    body.dark-mode .alert-success {
        background-color: #1e4620;
        border-color: #2d5a31;
        color: #90ee90;
    }

    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    body.dark-mode .alert-danger {
        background-color: #4a1e1e;
        border-color: #5a2a2a;
        color: #ff6b6b;
    }

    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }

    body.dark-mode .alert-info {
        background-color: #1e3a3f;
        border-color: #2d4a50;
        color: #7dd3fc;
    }

    .password-alert {
        border-left: 4px solid #0dcaf0;
    }

    body.dark-mode .password-alert {
        border-left-color: #7dd3fc;
    }

    .password-display {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0.5rem 0;
    }

    .password-code {
        background: #f8f9fa;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        font-family: 'Courier New', monospace;
        font-size: 1.1rem;
        font-weight: 600;
        color: #7E0308;
        letter-spacing: 1px;
        border: 2px solid #dee2e6;
    }

    body.dark-mode .password-code {
        background: #1a1a1a;
        color: #f9bd3d;
        border-color: #444;
    }

    .btn-close {
        opacity: 0.5;
        cursor: pointer;
    }

    .btn-close:hover {
        opacity: 1;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .form-container {
            padding: 1.5rem;
        }

        .form-row {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .form-row:last-of-type {
            grid-template-columns: 1fr;
        }

        .button-group {
            flex-direction: column;
        }

        .save-btn,
        .cancel-btn {
            width: 100%;
        }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-hide success alert after 5 seconds (but not password alert)
        const successAlert = document.querySelector('.alert-success');
        if (successAlert && !successAlert.closest('.password-alert')) {
            setTimeout(() => {
                successAlert.style.display = 'none';
            }, 5000);
        }

        // Don't auto-hide password alert - user needs to see it
    });

    function copyPassword() {
        const passwordElement = document.getElementById('generatedPassword');
        if (passwordElement) {
            const password = passwordElement.textContent.trim();
            
            // Copy to clipboard
            navigator.clipboard.writeText(password).then(function() {
                // Show feedback
                const btn = event.target.closest('button');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-success');
                
                setTimeout(function() {
                    btn.innerHTML = originalHTML;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-secondary');
                }, 2000);
            }).catch(function(err) {
                console.error('Failed to copy password:', err);
                alert('Failed to copy password. Please copy it manually: ' + password);
            });
        }
    }
</script>
@endsection
