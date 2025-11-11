@extends('layouts.app')

@section('title', 'Create Account')

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

                <h2 class="manage-title">Create Account</h2>

                {{-- Slots summary (for admins) --}}
                @php
                $limit = $limit ?? config('slea.max_admin_accounts', 3);
                $adminCnt = $adminCnt ?? 0;
                $remaining = $remaining ?? max($limit - $adminCnt, 0);
                @endphp
                <div class="mb-3" style="font-weight:600;">
                    Admin slots: <span>{{ $adminCnt }}</span> / <span>{{ $limit }}</span>
                    @if ($remaining > 0)
                    <span id="slotBadge" class="badge bg-success ms-2">{{ $remaining }} remaining</span>
                    @else
                    <span id="slotBadge" class="badge bg-danger ms-2">No remaining slots</span>
                    @endif
                </div>

                {{-- Messages --}}
                @if (session('success'))
                <div class="alert alert-success" style="margin-bottom:20px;">
                    {{ session('success') }}
                </div>
                @endif

                @if ($errors->any())
                <div class="alert alert-danger" style="margin-bottom:20px;">
                    <ul style="margin:0;">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if (session('generated_password'))
                <div class="alert alert-warning" style="margin-bottom:20px;">
                    <strong>Temporary password (copy now):</strong>
                    <code>{{ session('generated_password') }}</code>
                    <div class="small text-muted">For security, this will not be shown again.</div>
                </div>
                @endif

                <!-- Create Account Form -->
                <form action="{{ route('admin.store_user') }}" method="POST" id="createUserForm">
                    @csrf

                    <div class="form-row">
                        <div class="form-group">
                            <label for="role">Role <span class="required">*</span></label>
                            <select id="role" name="role" required>
                                <option value="" disabled {{ old('role') ? '' : 'selected' }}>— Select —</option>
                                <option value="admin" {{ old('role')==='admin' ? 'selected' : '' }}>Admin</option>
                                <option value="assessor" {{ old('role')==='assessor' ? 'selected' : '' }}>Assessor</option>
                            </select>
                            <small class="text-muted d-block mt-1">
                                Admin creation is limited to {{ $limit }} total.
                            </small>
                        </div>
                    </div>

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
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="contact">Contact (optional)</label>
                            <input type="text" id="contact" name="contact" value="{{ old('contact') }}">
                        </div>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="save-btn" id="saveBtn">Save</button>
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
        var role = document.getElementById('role');
        var saveBtn = document.getElementById('saveBtn');

        // Prettier-safe: emits a number literal
        var remain = @json((int) $remaining);

        function applyLimit() {
            saveBtn.disabled = (role.value === 'admin' && remain <= 0);
        }

        role.addEventListener('change', applyLimit);
        applyLimit();

        const successAlert = document.querySelector('.alert-success');
        if (successAlert) setTimeout(() => successAlert.style.display = 'none', 5000);
    });
</script>

@endsection