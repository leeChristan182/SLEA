@extends('layouts.app')

@section('title', 'Create Assessor Account')

@section('content')
<div class="container">
    @include('partials.sidebar')

    <main class="main-content">
        <div class="page-with-back-button">
            <div class="page-content">
                <!-- Back Button -->
                <div class="rubric-header-nav">
                    <a href="{{ route('admin.profile') }}" class="btn btn-back">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <h2 class="manage-title">Create Assessor's Account</h2>

                <form action="#" method="GET">
                    @csrf
                    <div class="form-row">
                        <div class="form-group">
                            <label for="last_name">Last Name <span class="required">*</span></label>
                            <input type="text" id="last_name" name="last_name" required>
                        </div>
                        <div class="form-group">
                            <label for="first_name">First Name <span class="required">*</span></label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="position">Position <span class="required">*</span></label>
                            <input type="text" id="position" name="position" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="default_password">Default Password</label>
                            <input type="text" id="default_password" name="default_password" value="{{ $defaultPassword }}" disabled>
                        </div>
                    </div>

                    <div class="button-group">
                        <button type="button" class="save-btn" id="saveBtn">Save</button>
                        <button type="button" class="cancel-btn" onclick="window.history.back()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Success Modal -->
        <div id="successModal" class="modal" style="display: none;">
            <div class="modal-content success-modal">
                <div class="modal-header">
                    <h3>Success</h3>
                    <span class="close" onclick="closeSuccessModal()">&times;</span>
                </div>
                <div class="modal-body text-center">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 id="successMessage">Assessor account created successfully!</h3>
                    <p>The new assessor account has been created and is ready to use.</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" onclick="closeSuccessModal()">OK</button>
                </div>
            </div>
        </div>
</div>
</main>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, setting up event listeners');

        const saveBtn = document.getElementById('saveBtn');
        const form = document.querySelector('form');

        if (saveBtn && form) {
            saveBtn.addEventListener('click', function(event) {
                console.log('Save button clicked!');
                handleSubmit(event);
            });
        } else {
            console.error('Save button or form not found!');
        }
    });

    function handleSubmit(event) {
        console.log('handleSubmit called');

        // Get the form element
        const form = document.querySelector('form');
        if (!form) {
            console.error('Form not found!');
            alert('Form not found. Please refresh the page.');
            return;
        }

        // Get form data
        const formData = new FormData(form);
        const lastName = formData.get('last_name');
        const firstName = formData.get('first_name');
        const email = formData.get('email');
        const position = formData.get('position');

        console.log('Form data:', {
            lastName,
            firstName,
            email,
            position
        });

        // Basic validation
        if (!lastName || !firstName || !email || !position) {
            alert('Please fill in all required fields.');
            return;
        }

        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Please enter a valid email address.');
            return;
        }

        // Show loading state
        const saveBtn = event.target;
        const originalText = saveBtn.textContent;
        saveBtn.textContent = 'Saving...';
        saveBtn.disabled = true;

        // Simulate form submission (replace with actual AJAX call)
        setTimeout(() => {
            console.log('Showing success modal');
            showSuccessModal();

            // Reset button state
            saveBtn.textContent = originalText;
            saveBtn.disabled = false;

            // Reset form after success
            setTimeout(() => {
                form.reset();
            }, 2000);
        }, 1000); // Simulate 1 second processing time
    }

    function showSuccessModal() {
        const modal = document.getElementById('successModal');
        const messageElement = document.getElementById('successMessage');

        messageElement.textContent = 'Assessor account created successfully!';
        modal.style.display = 'block';

        // Auto close after 5 seconds
        setTimeout(() => {
            closeSuccessModal();
        }, 5000);
    }

    function closeSuccessModal() {
        const modal = document.getElementById('successModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('successModal');
        if (event.target === modal) {
            closeSuccessModal();
        }
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeSuccessModal();
        }
    });
</script>
@endsection