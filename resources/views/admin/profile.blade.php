@extends('layouts.app')

@section('title', 'Admin Profile')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container">
    @include('partials.sidebar')

    <main class="main-content">
        <!-- Profile Header Banner -->
        <div class="profile-banner">
            <div class="profile-avatar">
                <img src="{{ $admin->profile_picture_path ? asset('storage/' . $admin->profile_picture_path) : asset('images/avatars/default-avatar.png') }}"
                    alt="Profile Picture"
                    id="profilePicture">

                <form id="avatarForm" method="POST" action="{{ route('admin.profile.avatar') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="file" id="avatarUpload" name="avatar" accept="image/*" style="display:none;">
                </form>

                <button type="button" class="upload-photo-btn" id="uploadPhotoBtn" title="Change Profile Picture">
                    <i class="fas fa-camera"></i>
                </button>
            </div>

            <h1 class="profile-name">{{ $admin->first_name }} {{ $admin->last_name }}</h1>
            <p class="small {{ session('dark_mode') ? 'text-white-50' : 'text-muted' }}">
                {{ $admin->position ?? 'Administrator' }}
            </p>
        </div>

        <!-- FLASH MESSAGES -->
        @if(session('success'))
        <div class="alert alert-success text-center mt-3">{{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger text-center mt-3">
            <ul class="mb-0 list-unstyled">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Profile Content -->
        <div class="profile-content">
            <!-- Personal Information Card -->
            <div class="profile-card">
                <div class="card-header">
                    <h2 class="card-title">Personal Information</h2>
                </div>
                <div class="card-content">
                    <!-- Display Mode -->
                    <div id="displayMode" class="info-grid">
                        <div class="info-field">
                            <label class="field-label">Admin ID</label>
                            <input type="text" class="field-input" value="{{ $admin->admin_id }}" readonly>
                        </div>
                        <div class="info-field">
                            <label class="field-label">First Name</label>
                            <input type="text" class="field-input" value="{{ $admin->first_name }}" readonly>
                        </div>
                        <div class="info-field">
                            <label class="field-label">Last Name</label>
                            <input type="text" class="field-input" value="{{ $admin->last_name }}" readonly>
                        </div>
                        <div class="info-field">
                            <label class="field-label">Position</label>
                            <input type="text" class="field-input" value="{{ $admin->position }}" readonly>
                        </div>
                        <div class="info-field">
                            <label class="field-label">Email Address</label>
                            <input type="text" class="field-input" value="{{ $admin->email_address }}" readonly>
                        </div>
                        <div class="info-field">
                            <label class="field-label">Contact Number</label>
                            <input type="text" class="field-input" value="{{ $admin->contact_number }}" readonly>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div id="editMode" class="edit-form" style="display:none;">
                        <form id="updateForm" method="POST" action="{{ route('admin.profile.update') }}">
                            @csrf
                            @method('PUT')
                            <div class="info-grid">
                                <div class="info-field">
                                    <label class="field-label">Admin ID</label>
                                    <input type="text" class="field-input" name="admin_id" value="{{ $admin->admin_id }}" readonly>
                                </div>
                                <div class="info-field">
                                    <label class="field-label">First Name</label>
                                    <input type="text" class="field-input" name="first_name" value="{{ $admin->first_name }}">
                                </div>
                                <div class="info-field">
                                    <label class="field-label">Last Name</label>
                                    <input type="text" class="field-input" name="last_name" value="{{ $admin->last_name }}">
                                </div>
                                <div class="info-field">
                                    <label class="field-label">Position</label>
                                    <input type="text" class="field-input" name="position" value="{{ $admin->position }}">
                                </div>
                                <div class="info-field">
                                    <label class="field-label">Email Address</label>
                                    <input type="email" class="field-input" name="email_address" value="{{ $admin->email_address }}" readonly>
                                </div>
                                <div class="info-field">
                                    <label class="field-label">Contact Number</label>
                                    <input type="tel" class="field-input" name="contact_number" value="{{ $admin->contact_number }}">
                                </div>
                            </div>
                            <div class="form-actions" style="display:none;">
                                <button type="submit" class="btn-save">Save Changes</button>
                                <button type="button" class="btn-cancel" id="cancelPersonalBtn">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="edit-btn" id="editPersonalBtn">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="profile-card">
                <div class="card-header">
                    <h2 class="card-title">Change Password</h2>
                </div>
                <div class="card-content">
                    <div id="passwordDisplayMode" class="password-display">
                        <div class="password-info">
                            <i class="fas fa-lock"></i>
                            <span>Password last changed recently</span>
                        </div>
                    </div>

                    <div id="passwordEditMode" class="password-edit" style="display:none;">
                        <form id="passwordForm" method="POST" action="{{ route('admin.profile.password.update') }}">
                            @csrf
                            @method('PUT')
                            <div class="info-field">
                                <label class="field-label">Current Password</label>
                                <input type="password" class="field-input" name="current_password" id="currentPassword" required>
                            </div>

                            <div class="password-requirements">
                                <p class="requirements-title">Password Requirements:</p>
                                <ul class="requirements-list" id="passwordChecklist">
                                    <li id="length" class="requirement-item invalid"><i class="fas fa-circle"></i> Minimum of 8 characters</li>
                                    <li id="uppercase" class="requirement-item invalid"><i class="fas fa-circle"></i> An uppercase character</li>
                                    <li id="lowercase" class="requirement-item invalid"><i class="fas fa-circle"></i> A lowercase character</li>
                                    <li id="number" class="requirement-item invalid"><i class="fas fa-circle"></i> A number</li>
                                    <li id="special" class="requirement-item invalid"><i class="fas fa-circle"></i> A special character</li>
                                </ul>
                            </div>

                            <div class="info-field">
                                <label class="field-label">New Password</label>
                                <input type="password" class="field-input" name="new_password" id="newPassword" oninput="validatePassword()" required>
                            </div>
                            <div class="info-field">
                                <label class="field-label">Confirm Password</label>
                                <input type="password" class="field-input" name="new_password_confirmation" id="confirmPassword" required>
                            </div>

                            <div class="checkbox-field">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="togglePasswordCheckbox"> Show Password
                                </label>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn-save">Change Password</button>
                                <button type="button" class="btn-cancel" id="cancelPasswordBtn">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="edit-btn" id="editPasswordBtn">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Success Modal -->
<div id="successModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Success</h3>
            <span class="close" id="closeSuccessModal">&times;</span>
        </div>
        <div class="modal-body">
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <p id="successMessage">Operation completed successfully!</p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="okSuccessModal">OK</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://kit.fontawesome.com/a2e0ad2a6a.js" crossorigin="anonymous"></script>
<script src="{{ asset('js/admin_profile.js') }}"></script>
@endpush