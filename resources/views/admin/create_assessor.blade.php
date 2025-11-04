@extends('layouts.app')

@section('title', 'Create Assessor Account')

@section('content')
<div class="container">
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

                <!-- ✅ Display messages -->
                @if (session('success'))
                <div class="alert alert-success" style="margin-bottom: 20px;">
                    {{ session('success') }}
                </div>
                @endif

                @if ($errors->any())
                <div class="alert alert-danger" style="margin-bottom: 20px;">
                    <ul style="margin: 0;">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- ✅ Create Assessor Form -->
                <form action="{{ route('admin.store_assessor') }}" method="POST">
                    @csrf

                    <div class="form-row">
                        <div class="form-group">
                            <label for="last_name">Last Name <span class="required">*</span></label>
                            <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="first_name">First Name <span class="required">*</span></label>
                            <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name') }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email_address">Email <span class="required">*</span></label>
                            <input type="email" id="email_address" name="email_address" value="{{ old('email_address') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="position">Position <span class="required">*</span></label>
                            <input type="text" id="position" name="position" value="{{ old('position') }}" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="default_password">Default Password</label>
                            <input type="text"
                                id="default_password"
                                name="default_password"
                                value="{{ session('default_password', 'Auto-generated on save') }}"
                                readonly>

                        </div>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="save-btn">Save</button>
                        <button type="button" class="cancel-btn" onclick="window.history.back()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Optional small UX enhancement
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            setTimeout(() => successAlert.style.display = 'none', 5000);
        }
    });
</script>
@endsection