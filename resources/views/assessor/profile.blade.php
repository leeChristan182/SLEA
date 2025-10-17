@extends('layouts.app')

@section('title', 'Assessor Dashboard')

@section('content')
<div class="container">
    @include('partials.assessor-sidebar')

    <main class="main-content">
        <!-- Profile Header Banner -->
        <div class="profile-banner">
            <div class="profile-avatar">
                <img src="{{ asset('images/avatars/default-avatar.svg') }}" alt="Profile Picture" id="profilePicture">
                <button class="upload-photo-btn" onclick="document.getElementById('avatarUpload').click()">
                    <i class="fas fa-camera"></i>
                </button>
                <input type="file" id="avatarUpload" accept="image/*" style="display:none;" onchange="previewAvatar(event)">
            </div>
            <h1 class="profile-name">Sample Assessor</h1>
        </div>

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
                            <label class="field-label">Assessor ID</label>
                            <input type="text" class="field-input" value="2024-00123" readonly>
                        </div>
                        <div class="info-field">
                            <label class="field-label">First Name</label>
                            <input type="text" class="field-input" value="Sample" readonly>
                        </div>
                        <div class="info-field">
                            <label class="field-label">Position</label>
                            <input type="text" class="field-input" value="Student Affairs Assessor" readonly>
                        </div>
                        <div class="info-field">
                            <label class="field-label">Email Address</label>
                            <input type="text" class="field-input" value="assessor@usep.edu.ph" readonly>
                        </div>
                        <div class="info-field">
                            <label class="field-label">Last Name</label>
                            <input type="text" class="field-input" value="Assessor" readonly>
                        </div>
                        <div class="info-field">
                            <label class="field-label">Contact Number</label>
                            <input type="text" class="field-input" value="09991234567" readonly>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div id="editMode" class="edit-form" style="display: none;">
                        <form id="updateForm">
                            <div class="info-grid">
                                <div class="info-field">
                                    <label class="field-label">Assessor ID</label>
                                    <input type="text" class="field-input" id="updateAssessorId" name="assessor_id" value="2024-00123" readonly>
                                </div>
                                <div class="info-field">
                                    <label class="field-label">First Name</label>
                                    <input type="text" class="field-input" id="updateFirstname" name="firstname" value="Sample">
                                </div>
                                <div class="info-field">
                                    <label class="field-label">Position</label>
                                    <input type="text" class="field-input" id="updatePosition" name="position" value="Student Affairs Assessor">
                                </div>
                                <div class="info-field">
                                    <label class="field-label">Email Address</label>
                                    <input type="email" class="field-input" id="updateEmail" name="email" value="assessor@usep.edu.ph">
                                </div>
                                <div class="info-field">
                                    <label class="field-label">Last Name</label>
                                    <input type="text" class="field-input" id="updateLastname" name="lastname" value="Assessor">
                                </div>
                                <div class="info-field">
                                    <label class="field-label">Contact Number</label>
                                    <input type="tel" class="field-input" id="updatePhone" name="phone" value="09991234567">
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn-save" onclick="updateProfile()">Save Changes</button>
                                <button type="button" class="btn-cancel" onclick="cancelEdit()">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="edit-btn" onclick="toggleEditMode()" id="editPersonalBtn">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="profile-card">
                <div class="card-header">
                    <h2 class="card-title">Change Password</h2>
                </div>
                <div class="card-content">
                    <!-- Display Mode -->
                    <div id="passwordDisplayMode" class="password-display">
                        <div class="password-info">
                            <i class="fas fa-lock"></i>
                            <span>Password was last changed 15 days ago</span>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div id="passwordEditMode" class="password-edit" style="display: none;">
                        <form id="passwordForm">
                            <div class="password-fields">
                                <div class="info-field">
                                    <label class="field-label">Current Password</label>
                                    <input type="password" class="field-input" id="currentPassword" name="current_password" placeholder="Enter current password">
                                </div>

                                <div class="password-requirements">
                                    <p class="requirements-title">Password Requirements:</p>
                                    <ul class="requirements-list" id="passwordChecklist">
                                        <li id="length" class="requirement-item invalid">
                                            <i class="fas fa-circle"></i>
                                            Minimum of 8 characters
                                        </li>
                                        <li id="uppercase" class="requirement-item invalid">
                                            <i class="fas fa-circle"></i>
                                            An uppercase character
                                        </li>
                                        <li id="lowercase" class="requirement-item invalid">
                                            <i class="fas fa-circle"></i>
                                            A lowercase character
                                        </li>
                                        <li id="number" class="requirement-item invalid">
                                            <i class="fas fa-circle"></i>
                                            A number
                                        </li>
                                        <li id="special" class="requirement-item invalid">
                                            <i class="fas fa-circle"></i>
                                            A special character
                                        </li>
                                    </ul>
                                </div>

                                <div class="info-field">
                                    <label class="field-label">New Password</label>
                                    <input type="password" class="field-input" id="newPassword" name="new_password" placeholder="Enter new password" onkeyup="validatePassword()" oninput="validatePassword()">
                                </div>
                                <div class="info-field">
                                    <label class="field-label">Confirm Password</label>
                                    <input type="password" class="field-input" id="confirmPassword" name="confirm_password" placeholder="Confirm new password">
                                </div>
                            </div>

                            <div class="checkbox-field">
                                <label class="checkbox-label">
                                    <input type="checkbox" onclick="togglePassword()">
                                    <span class="checkmark"></span>
                                    Show Password
                                </label>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn-save" onclick="updatePassword()">Change Password</button>
                                <button type="button" class="btn-cancel" onclick="cancelPasswordEdit()">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="edit-btn" onclick="togglePasswordEdit()" id="editPasswordBtn">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Success Modal -->
<div id="successModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Success</h3>
            <span class="close" onclick="closeSuccessModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <p id="successMessage">Operation completed successfully!</p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" onclick="closeSuccessModal()">OK</button>
        </div>
    </div>
</div>

<script>
    // Toggle Edit Mode
    function toggleEditMode() {
        document.getElementById('displayMode').style.display = 'none';
        document.getElementById('editMode').style.display = 'block';
        document.getElementById('editPersonalBtn').style.display = 'none';
    }

    // Cancel Edit
    function cancelEdit() {
        document.getElementById('displayMode').style.display = 'block';
        document.getElementById('editMode').style.display = 'none';
        document.getElementById('editPersonalBtn').style.display = 'flex';

        // Reset form to original values
        document.getElementById('updateFirstname').value = 'Sample';
        document.getElementById('updateLastname').value = 'Assessor';
        document.getElementById('updatePhone').value = '09991234567';
        document.getElementById('updateEmail').value = 'assessor@usep.edu.ph';
        document.getElementById('updatePosition').value = 'Student Affairs Assessor';
    }

    // Update Profile
    function updateProfile() {
        const form = document.getElementById('updateForm');
        const formData = new FormData(form);

        // Here you would send the data to the server
        console.log('Updating profile:', Object.fromEntries(formData));

        // Update the display values
        document.querySelector('#displayMode .info-field:nth-child(1) .field-input').value = document.getElementById('updateAssessorId').value;
        document.querySelector('#displayMode .info-field:nth-child(2) .field-input').value = document.getElementById('updateFirstname').value;
        document.querySelector('#displayMode .info-field:nth-child(3) .field-input').value = document.getElementById('updatePosition').value;
        document.querySelector('#displayMode .info-field:nth-child(4) .field-input').value = document.getElementById('updateEmail').value;
        document.querySelector('#displayMode .info-field:nth-child(5) .field-input').value = document.getElementById('updateLastname').value;
        document.querySelector('#displayMode .info-field:nth-child(6) .field-input').value = document.getElementById('updatePhone').value;

        // Switch back to display mode
        document.getElementById('displayMode').style.display = 'block';
        document.getElementById('editMode').style.display = 'none';
        document.getElementById('editPersonalBtn').style.display = 'flex';

        // Show success message
        showSuccessModal('Personal information updated successfully!');
    }

    // Toggle Password Edit Mode
    function togglePasswordEdit() {
        document.getElementById('passwordDisplayMode').style.display = 'none';
        document.getElementById('passwordEditMode').style.display = 'block';
        document.getElementById('editPasswordBtn').style.display = 'none';

        // Initialize button state as disabled
        const changePasswordBtn = document.querySelector('#passwordEditMode .btn-save');
        if (changePasswordBtn) {
            changePasswordBtn.disabled = true;
            changePasswordBtn.style.opacity = '0.5';
            changePasswordBtn.style.cursor = 'not-allowed';
        }

        // Reset all requirements to invalid state
        document.querySelectorAll('.requirement-item').forEach(item => {
            item.classList.remove('valid');
            item.classList.add('invalid');
        });
    }

    // Cancel Password Edit
    function cancelPasswordEdit() {
        document.getElementById('passwordDisplayMode').style.display = 'block';
        document.getElementById('passwordEditMode').style.display = 'none';
        document.getElementById('editPasswordBtn').style.display = 'flex';

        // Clear form
        document.getElementById('passwordForm').reset();

        // Reset password requirements
        document.querySelectorAll('.requirement-item').forEach(item => {
            item.classList.remove('valid');
            item.classList.add('invalid');
        });

        // Reset button state
        const changePasswordBtn = document.querySelector('#passwordEditMode .btn-save');
        if (changePasswordBtn) {
            changePasswordBtn.disabled = false;
            changePasswordBtn.style.opacity = '1';
            changePasswordBtn.style.cursor = 'pointer';
        }
    }

    // Update Password
    function updatePassword() {
        const form = document.getElementById('passwordForm');
        const formData = new FormData(form);

        // Validate passwords match
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (newPassword !== confirmPassword) {
            alert('Passwords do not match!');
            return;
        }

        // Here you would send the data to the server
        console.log('Updating password:', Object.fromEntries(formData));

        // Switch back to display mode
        document.getElementById('passwordDisplayMode').style.display = 'block';
        document.getElementById('passwordEditMode').style.display = 'none';
        document.getElementById('editPasswordBtn').style.display = 'flex';

        // Clear form
        form.reset();

        // Show success message
        showSuccessModal('Password updated successfully!');
    }

    // Password validation is now handled by the external admin_profile.js file

    // Toggle Password Visibility
    function togglePassword() {
        const inputs = document.querySelectorAll('#passwordEditMode input[type="password"]');
        inputs.forEach(input => {
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    }

    // Success Modal
    function showSuccessModal(message) {
        document.getElementById('successMessage').textContent = message;
        document.getElementById('successModal').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeSuccessModal() {
        document.getElementById('successModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('successModal');
        if (e.target === modal) {
            closeSuccessModal();
        }
    });

    // Avatar preview function
    function previewAvatar(event) {
        const file = event.target.files[0];
        if (file) {
            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Please select a valid image file.');
                return;
            }

            // Validate file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('Image size should be less than 5MB.');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                // Update profile picture
                const profileImg = document.getElementById('profilePicture');
                if (profileImg) {
                    profileImg.src = e.target.result;
                    console.log('Profile picture updated successfully');
                }

                // Update sidebar profile icon
                updateSidebarProfile(e.target.result);

                // Show success message
                showSuccessModal('Profile picture updated successfully!');

                // Here you would typically upload to server
                console.log('Profile picture updated:', file.name);
            };
            reader.readAsDataURL(file);
        }
    }

    // Update sidebar profile icon
    function updateSidebarProfile(imageSrc) {
        const sidebarProfile = document.querySelector('#sidebarProfileIcon');
        if (sidebarProfile) {
            sidebarProfile.style.backgroundImage = `url('${imageSrc}')`;
            sidebarProfile.style.backgroundSize = 'cover';
            sidebarProfile.style.backgroundPosition = 'center';

            // Store in localStorage for persistence
            localStorage.setItem('profileImage', imageSrc);
        }
    }

    // Load profile image from localStorage on page load
    document.addEventListener('DOMContentLoaded', function() {
        const savedImage = localStorage.getItem('profileImage');
        if (savedImage) {
            document.getElementById('profilePicture').src = savedImage;
            updateSidebarProfile(savedImage);
        }
    });
</script>
@endsection
