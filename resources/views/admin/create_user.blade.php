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
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('generated_password'))
            <div class="alert alert-warning">
                <strong>Temporary password (copy now):</strong>
                <code>{{ session('generated_password') }}</code>
                <div class="small text-muted">For security, this will not be shown again.</div>
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
        padding: 0.5rem;
        border-radius: 6px;
        flex-shrink: 0;
        font-size: 0.8rem;
    }

    .alert code {
        background: #f8f9fa;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-family: monospace;
    }

    body.dark-mode .alert code {
        background: #1a1a1a;
        color: #f0f0f0;
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
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.display = 'none';
            }, 5000);
        }
    });
</script>
@endsection
