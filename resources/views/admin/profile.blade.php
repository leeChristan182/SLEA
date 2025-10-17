@extends('layouts.app')

@section('title', 'Admin Profile')

@section('content')
<div class="container-fluid slea-profile-container">
    @include('partials.sidebar')

    <main class="main-content">

        {{-- =================== PROFILE HEADER =================== --}}
        <section class="profile-header text-center py-4">
            <div class="profile-avatar-container position-relative d-inline-block">
                <img src="{{ $admin->profile_picture ? asset('storage/' . $admin->profile_picture) : asset('images/avatars/default-avatar.svg') }}"
                    id="profilePicture"
                    class="rounded-circle border shadow-sm profile-avatar-img"
                    alt="Profile Picture">

                <button class="upload-photo-btn position-absolute bottom-0 end-0 bg-white border rounded-circle p-2"
                    onclick="document.getElementById('avatarUpload').click()">
                    <i class="fas fa-camera"></i>
                </button>
                <input type="file" id="avatarUpload" accept="image/*" class="d-none" onchange="previewAvatar(event)">
            </div>
            <h2 class="fw-bold mt-3 text-uppercase">{{ $admin->first_name }} {{ $admin->last_name }}</h2>
            <p class="text-muted small">{{ $admin->position ?? 'Administrator' }}</p>
        </section>

        {{-- =================== TWO BOXES (INFO + PASSWORD) =================== --}}
        <div class="profile-wrapper d-flex flex-column flex-lg-row gap-4 justify-content-center p-3">

            {{-- LEFT COLUMN: PERSONAL INFO --}}
            <div class="col-12 col-lg-5">
                <div class="card shadow-sm rounded-4 p-4 h-100">
                    <h2 class="card-title fw-bold mb-3">Personal Information</h2>

                    <div class="info-field mb-2">
                        <label class="fw-semibold">Admin ID</label>
                        <input type="text" class="form-control" value="{{ $admin->admin_id }}" readonly>
                    </div>

                    <div class="info-field mb-2">
                        <label class="fw-semibold">First Name</label>
                        <input type="text" class="form-control" value="{{ $admin->first_name }}" readonly>
                    </div>

                    <div class="info-field mb-2">
                        <label class="fw-semibold">Last Name</label>
                        <input type="text" class="form-control" value="{{ $admin->last_name }}" readonly>
                    </div>

                    <div class="info-field mb-2">
                        <label class="fw-semibold">Email Address</label>
                        <input type="text" class="form-control" value="{{ $admin->email_address }}" readonly>
                    </div>

                    <div class="info-field mb-2">
                        <label class="fw-semibold">Contact Number</label>
                        <input type="text" class="form-control" value="{{ $admin->contact_number }}" readonly>
                    </div>

                    <div class="info-field mb-2">
                        <label class="fw-semibold">Position</label>
                        <input type="text" class="form-control" value="{{ $admin->position }}" readonly>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: CHANGE PASSWORD --}}
            <div class="col-12 col-lg-5">
                <div class="card shadow-sm rounded-4 p-4 h-100">
                    <h2 class="card-title fw-bold mb-3">Change Password</h2>

                    <form id="passwordForm" method="POST" action="{{ route('admin.profile.password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="currentPassword" class="fw-semibold">Current Password</label>
                            <input type="password" name="current_password" id="currentPassword"
                                class="form-control" placeholder="Enter current password" required>
                        </div>

                        <div class="password-checklist mb-3">
                            <label class="fw-semibold">Password must contain:</label>
                            <!-- replace your <ul id="passwordChecklist"> ... </ul> with this -->
                            <ul class="list-unstyled ms-3" id="passwordChecklist">
                                <li id="length" class="text-secondary">
                                    <span class="icon-wrap">
                                        <i class="fa fa-circle circle-icon me-2" aria-hidden="true"></i>
                                        <i class="fa fa-check check-icon me-2 d-none" aria-hidden="true"></i>
                                    </span>
                                    8+ characters
                                </li>
                                <li id="uppercase" class="text-secondary">
                                    <span class="icon-wrap">
                                        <i class="fa fa-circle circle-icon me-2" aria-hidden="true"></i>
                                        <i class="fa fa-check check-icon me-2 d-none" aria-hidden="true"></i>
                                    </span>
                                    Uppercase letter
                                </li>
                                <li id="lowercase" class="text-secondary">
                                    <span class="icon-wrap">
                                        <i class="fa fa-circle circle-icon me-2" aria-hidden="true"></i>
                                        <i class="fa fa-check check-icon me-2 d-none" aria-hidden="true"></i>
                                    </span>
                                    Lowercase letter
                                </li>
                                <li id="number" class="text-secondary">
                                    <span class="icon-wrap">
                                        <i class="fa fa-circle circle-icon me-2" aria-hidden="true"></i>
                                        <i class="fa fa-check check-icon me-2 d-none" aria-hidden="true"></i>
                                    </span>
                                    Number
                                </li>
                                <li id="special" class="text-secondary">
                                    <span class="icon-wrap">
                                        <i class="fa fa-circle circle-icon me-2" aria-hidden="true"></i>
                                        <i class="fa fa-check check-icon me-2 d-none" aria-hidden="true"></i>
                                    </span>
                                    Special character
                                </li>
                            </ul>

                        </div>

                        <div class="mb-3">
                            <label for="newPassword" class="fw-semibold">New Password</label>
                            <input type="password" name="new_password" id="newPassword" required
                                class="form-control" placeholder="Enter new password"
                                oninput="validatePassword()" required>
                        </div>

                        <div class="mb-3">
                            <label for="confirmPassword" class="fw-semibold">Confirm Password</label>
                            <input type="password" name="new_password_confirmation" id="confirmPassword" required
                                class="form-control" placeholder="Confirm new password" required>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="showPassword" onclick="togglePassword()">
                            <label class="form-check-label" for="showPassword">Show Password</label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger rounded-pill fw-semibold">
                                Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

{{-- SUCCESS MODAL --}}
<div id="successModal" class="modal" style="display:none;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Success</h5>
                <button type="button" class="btn-close" onclick="closeSuccessModal()"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fa-solid fa-check text-success fa-3x mb-3"></i>
                <p id="successMessage" class="fw-semibold">Operation completed successfully!</p>
            </div>
            <div class="modal-footer border-0 text-center">
                <button type="button" class="btn btn-primary rounded-pill" onclick="closeSuccessModal()">OK</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword() {
        ['currentPassword', 'newPassword', 'confirmPassword'].forEach(id => {
            const input = document.getElementById(id);
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    }

    function validatePassword() {
        const val = document.getElementById('newPassword').value;
        const rules = {
            length: val.length >= 8,
            uppercase: /[A-Z]/.test(val),
            lowercase: /[a-z]/.test(val),
            number: /\d/.test(val),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(val)
        };

        Object.entries(rules).forEach(([key, valid]) => {
            const el = document.getElementById(key);
            if (!el) return;
            // text coloring
            el.classList.toggle('text-success', valid);
            el.classList.toggle('text-secondary', !valid);

            // icons
            const circle = el.querySelector('.circle-icon');
            const check = el.querySelector('.check-icon');
            if (circle) circle.classList.toggle('d-none', valid); // hide circle when valid
            if (check) check.classList.toggle('d-none', !valid); // show check when valid
        });
    }


    function previewAvatar(event) {
        const file = event.target.files[0];
        if (!file || !file.type.startsWith('image/')) return alert('Please upload a valid image.');
        if (file.size > 5 * 1024 * 1024) return alert('Max file size is 5MB.');
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('profilePicture').src = e.target.result;
            showSuccessModal('Profile picture updated successfully!');
        };
        reader.readAsDataURL(file);
    }

    function showSuccessModal(message) {
        document.getElementById('successMessage').textContent = message;
        document.getElementById('successModal').style.display = 'block';
    }

    function closeSuccessModal() {
        document.getElementById('successModal').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const saved = localStorage.getItem('profileImage');
        if (saved) document.getElementById('profilePicture').src = saved;
    });
</script>
@endpush

@push('styles')
<style>
    /* Scoped styling for Admin Profile page only */

    .profile-avatar-container {
        width: 130px;
        height: 130px;
    }

    .profile-avatar-img {
        width: 130px;
        height: 130px;
        object-fit: cover;
    }

    .passwordChecklist li.text-success i.check-icon {
        color: #28a745 !important;
    }

    .passwordChecklist li.text-secondary i.circle-icon {
        color: #aaa !important;
    }


    /* Dark mode visibility improvements */
    body.dark-mode .card {
        background-color: #1e1e1e !important;
        color: #e0e0e0 !important;
    }

    body.dark-mode .form-control[readonly] {
        background-color: #2a2a2a !important;
        color: #f5f5f5 !important;
        border-color: #444 !important;
    }

    body.dark-mode label,
    body.dark-mode h2,
    body.dark-mode .fw-semibold {
        color: #ffffff !important;
    }

    body.dark-mode input::placeholder {
        color: #aaaaaa;
    }

    body.dark-mode .form-check-label {
        color: #ddd !important;
    }
</style>
@endpush