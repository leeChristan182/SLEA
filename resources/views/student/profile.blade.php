@extends('layouts.app')

@section('title', 'Student Profile')

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta id="slea-routes" data-clusters="{{ route('ajax.clusters') }}"
        data-organizations="{{ route('ajax.organizations') }}"
        @if(\Illuminate\Support\Facades\Route::has('ajax.council.positions'))
        data-council-positions="{{ route('ajax.council.positions') }}" @endif>
@endsection

@section('content')
    @php
        use Carbon\Carbon;
        /** @var \App\Models\User $user */

        // The controller provides `user` and `academic`. Fallback to the `student` guard then default auth.
        $student = $student ?? ($user ?? auth()->guard('student')->user() ?? auth()->user());
        $acad = $academic ?? optional($student->studentAcademic);

        // Safely resolve related names
        $collegeName = $acad && $acad->college
            ? ($acad->college->college_name ?? $acad->college->name)
            : null;

        $programName = $acad && $acad->program
            ? $acad->program->name
            : null;

        $majorName = $acad && $acad->major
            ? $acad->major->name
            : null;
    @endphp

    <div class="student-profile-page">
        <div class="container">
            @include('partials.sidebar')

            <main class="main-content">
                <!-- Profile Header Banner -->
                <div class="profile-banner">
                    <div class="profile-avatar">
                        <img src="{{ $student->profile_picture_path ? asset('storage/' . $student->profile_picture_path) : asset('images/avatars/default-avatar.png') }}"
                            alt="Profile Picture" id="profilePicture">

                        <form id="avatarForm" method="POST" action="{{ route('student.updateAvatar') }}"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="file" id="avatarUpload" name="avatar" accept="image/*" style="display:none;">
                        </form>

                        <button type="button" class="upload-photo-btn" id="uploadPhotoBtn" title="Change Profile Picture">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>

                    <h1 class="profile-name">
                        {{ $student->first_name ?? 'N/A' }}
                        {{ $student->last_name ?? '' }}
                    </h1>
                    <p class="small text-white">
                        Student
                    </p>
                </div>

                {{-- Personal + Academic --}}
                <section class="profile-section">
                    {{-- Personal Information --}}
                    <div class="profile-info">
                        <h3>Personal Information</h3>
                        <p><strong>Name:</strong>
                            <span>{{ strtoupper($student->last_name) }}, {{ $student->first_name }}
                                {{ $student->middle_name }}</span>
                        </p>
                        <p><strong>Contact Number:</strong> <span>{{ $student->contact ?? 'N/A' }}</span></p>
                        <p><strong>Email Address:</strong> <span>{{ $student->email }}</span></p>
                    </div>

                    {{-- Academic Information --}}
                    <div class="profile-info">
                        <h3>Academic Information</h3>
                        <p><strong>Student ID:</strong> <span>{{ $acad->student_number ?? 'N/A' }}</span></p>
                        <p><strong>College:</strong> <span>{{ $collegeName ?? 'N/A' }}</span></p>
                        <p><strong>Program:</strong> <span>{{ $programName ?? 'N/A' }}</span></p>
                        <p><strong>Major:</strong> <span>{{ $majorName ?? 'N/A' }}</span></p>
                        <p><strong>Year Level:</strong> <span>{{ $acad->year_level ?? 'N/A' }}</span></p>
                        <p><strong>Expected Year to Graduate:</strong> <span>{{ $acad->expected_grad_year ?? 'N/A' }}</span>
                        </p>
                        <p><strong>Eligibility Status:</strong>
                            <span class="badge" style="background:#{{ [
        'eligible' => '198754',              // green
        'needs_revalidation' => 'fd7e14',    // orange
        'under_review' => '0d6efd',          // blue
        'ineligible' => 'dc3545',            // red
    ][$acad->eligibility_status ?? 'eligible'] ?? '6c757d' }};">
                                {{ $acad->eligibility_status ?? 'eligible' }}
                            </span>
                        </p>
                        @if($acad && $acad->eligibility_status !== 'eligible')
                            <small class="text-muted">Some features may be locked until revalidation is cleared.</small>
                        @endif
                    </div>
                </section>

                {{-- Leadership Information --}}
                <section class="profile-info" style="margin-top:24px;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h3 class="mb-0">Leadership Information</h3>
                        <button type="button" class="change-btn" data-bs-toggle="modal"
                            data-bs-target="#addLeadershipModal">
                            + Add Leadership Info
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="approval-table w-100">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Organization Name</th>
                                    <th>Organization Role</th>
                                    <th>Term</th>
                                    <th>Issued By</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse (($leaderships ?? []) as $lead)
                                    <tr>
                                        <td>{{ $lead->leadership_type_name ?? '—' }}</td>
                                        <td>{{ $lead->organization_name ?? '—' }}</td>
                                        <td>{{ $lead->position_name ?? '—' }}</td>
                                        <td>{{ $lead->term ?? '—' }}</td>
                                        <td>{{ $lead->issued_by ?? '—' }}</td>
                                        <td>{{ $lead->leadership_status ?? $lead->status ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No leadership records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- Settings --}}
                <section class="settings-grid" style="margin-top:24px;">
                    {{-- Change Password --}}
                    <div class="change-password settings-left">
                        <h3>Change Password</h3>

                        <form action="{{ route('student.changePassword') }}" method="POST">
                            @csrf
                            <label for="current_password">Present Password</label>
                            <div class="password-wrapper">
                                <input id="current_password" name="current_password" type="password" required>
                                <i class="fas fa-eye toggle-password" data-target="current_password"></i>
                            </div>

                            <div class="requirements visible-box">
                                <strong>A new password must contain the following:</strong>
                                <ul id="passwordChecklist">
                                    <li>Minimum of 8 characters</li>
                                    <li>An uppercase character</li>
                                    <li>A lowercase character</li>
                                    <li>A number</li>
                                    <li>A special character</li>
                                </ul>
                            </div>

                            <label for="password">New Password</label>
                            <div class="password-wrapper">
                                <input id="password" name="password" type="password" required>
                            </div>

                            <label for="password_confirmation">Confirm Password</label>
                            <div class="password-wrapper">
                                <input id="password_confirmation" name="password_confirmation" type="password" required>
                            </div>

                            <div class="checkbox-field" style="margin-top: 10px;">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="showPasswordCheckbox" onchange="toggleNewPasswords()">
                                    Show Password
                                </label>
                            </div>

                            <button class="change-btn" type="submit">Change Password</button>
                        </form>
                    </div>

                    {{-- Update Academic Details --}}
                    <div class="profile-info settings-year">
                        <h3>Update Academic Details</h3>
                        <form action="{{ route('student.updateAcademic') }}" method="POST">
                            @csrf

                            {{-- Year Level only (program & major are shown but not edited here) --}}
                            <div class="form-group">
                                <label for="year_level">Year Level</label>
                                <select id="year_level" name="year_level" required>
                                    <option value="">— Select —</option>
                                    @foreach([1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year', 4 => '4th Year', 5 => '5th Year'] as $val => $label)
                                        <option value="{{ $val }}" {{ (string) ($acad->year_level ?? '') === (string) $val ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="program_display">Program</label>
                                <input id="program_display" type="text" value="{{ $programName ?? '' }}" readonly>
                                <small class="text-muted">Program changes are handled during registration or by the
                                    office.</small>
                            </div>

                            <div class="form-group">
                                <label for="major_display">Major (if any)</label>
                                <input id="major_display" type="text" value="{{ $majorName ?? '' }}" readonly>
                            </div>

                            <button class="change-btn" type="submit">Update</button>
                        </form>
                    </div>

                    {{-- Upload COR --}}
                    {{-- Upload COR --}}
                    <div class="profile-info settings-cor">
                        <h3>Upload Certificate of Registration</h3>
                        <form id="uploadCORForm" action="{{ route('student.uploadCOR') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <label for="cor">Choose file</label>
                            <input id="cor" name="cor" type="file" accept=".jpg,.jpeg,.png,.pdf" required>
                            <small>Max size 5MB • JPG, PNG, or PDF</small>
                            <button class="change-btn" type="submit" style="margin-top:12px;">Upload</button>

                            @if(!empty($acad->certificate_of_registration_path))
                                <p style="margin-top:8px;">
                                    <a href="{{ asset('storage/' . $acad->certificate_of_registration_path) }}" target="_blank">
                                        View uploaded COR
                                    </a>
                                </p>
                            @endif
                        </form>
                    </div>

                </section>
            </main>
        </div>
    </div>

    {{-- Styles & JS --}}
    <style>
        /* === Settings Grid Layout === */
        .student-profile-page .settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-areas:
                "left rightTop"
                "left rightBottom";
            gap: 24px;
            align-items: start;
        }

        .settings-left {
            grid-area: left;
            display: flex;
            flex-direction: column;
        }

        .settings-year {
            grid-area: rightTop;
            padding-bottom: 15px;
        }

        .settings-cor {
            grid-area: rightBottom;
            min-height: 280px;
        }

        /* === Password Requirement Box === */
        .requirements.visible-box {
            background-color: #fff8f8;
            border: 1px solid #e5bebe;
            border-left: 5px solid #8B0000;
            border-radius: 10px;
            padding: 14px 18px;
            margin: 14px 0 20px;
            color: #2d2d2d;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.04);
        }

        .requirements.visible-box strong {
            display: block;
            font-weight: 700;
            color: #8B0000;
            margin-bottom: 6px;
            font-size: 15px;
        }

        #passwordChecklist li {
            color: #333 !important;
            font-size: 14px;
            padding: 3px 0;
            list-style: circle;
            margin-left: 20px;
        }

        #passwordChecklist li:hover {
            color: #8B0000;
            font-weight: 500;
        }

        /* === Inputs === */
        .form-group {
            margin-bottom: 14px;
        }

        label {
            display: block;
            margin-bottom: 4px;
            font-weight: 600;
        }

        select,
        input[type="text"],
        input[type="password"],
        input[type="file"] {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 8px 10px;
            transition: border-color .2s ease;
        }

        select:focus,
        input:focus {
            border-color: #8B0000;
            outline: none;
        }

        /* === Password Visibility Toggle === */
        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #555;
            font-size: 1rem;
            transition: color .2s;
        }

        .toggle-password:hover {
            color: #8B0000;
        }

        /* === Checkbox Field === */
        .checkbox-field {
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-weight: 500;
            color: #333;
        }

        .checkbox-label input[type="checkbox"] {
            width: auto;
            margin: 0;
            cursor: pointer;
            accent-color: #8B0000;
        }

        .checkbox-label:hover {
            color: #8B0000;
        }

        body.dark-mode .checkbox-label {
            color: #f0f0f0;
        }

        /* === Buttons === */
        .change-btn {
            background-color: #8B0000;
            border: none;
            color: white;
            padding: 10px 16px;
            border-radius: 6px;
            font-weight: 600;
            transition: .25s;
            width: 100%;
        }

        .change-btn:hover {
            background-color: #6B0000;
        }

        /* === Cards === */
        .profile-info,
        .change-password {
            background-color: white;
            box-shadow: 0 3px 6px rgba(0, 0, 0, .06);
            border-radius: 10px;
            padding: 20px;
        }

        .profile-info h3,
        .change-password h3 {
            color: #8B0000;
            margin-bottom: 15px;
            border-bottom: none !important;
            padding-bottom: 0;
        }

        @media (max-width: 1200px) {
            .student-profile-page .settings-grid {
                grid-template-columns: 1fr;
                grid-template-areas:
                    "rightTop"
                    "rightBottom"
                    "left";
            }
        }
    </style>
    <script src="{{ asset('js/student_profile.js') }}"></script>

    <script>
        // Toggle visibility for Present Password (keep individual icon)
        document.querySelectorAll('.toggle-password').forEach(icon => {
            icon.addEventListener('click', () => {
                const target = document.getElementById(icon.dataset.target);
                if (!target) return;
                const isPassword = target.type === 'password';
                target.type = isPassword ? 'text' : 'password';
                icon.classList.toggle('fa-eye-slash', isPassword);
            });
        });

        // Toggle visibility for both New Password and Confirm Password fields
        window.toggleNewPasswords = function () {
            const checkbox = document.getElementById('showPasswordCheckbox');
            const passwordField = document.getElementById('password');
            const confirmPasswordField = document.getElementById('password_confirmation');

            if (checkbox && passwordField && confirmPasswordField) {
                const showPassword = checkbox.checked;
                passwordField.type = showPassword ? 'text' : 'password';
                confirmPasswordField.type = showPassword ? 'text' : 'password';
            }
        };
    </script>
    {{-- Add Leadership Info Modal --}}
    <div class="modal fade" id="addLeadershipModal" tabindex="-1" aria-labelledby="addLeadershipModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                {{-- IMPORTANT: id="updateLeadershipForm" --}}
                <form id="updateLeadershipForm" method="POST" action="{{ route('student.updateLeadership') }}">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title" id="addLeadershipModalLabel">Add Leadership Information</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="leadership[0][id]" value="">

                        {{-- Leadership Type --}}
                        <div class="row g-3 mb-2">
                            <div class="col-md-6">
                                <label class="form-label" for="modal_leadership_type_id">
                                    Leadership Type <span class="required">*</span>
                                </label>
                                <select id="modal_leadership_type_id" name="leadership[0][leadership_type_id]"
                                    class="form-select" required>
                                    <option value="">Select Leadership Type</option>
                                    @foreach($leadershipTypes ?? [] as $type)
                                        <option value="{{ $type->id }}"
                                            data-requires-org="{{ (int) ($type->requires_org ?? 0) }}"
                                            data-key="{{ $type->key ?? '' }}">
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Cluster / Organization --}}
                        <div class="row g-3 mb-2">
                            <div class="col-md-6" id="modal_cluster_wrap" style="display:none;">
                                <label class="form-label" for="modal_cluster_id">
                                    Cluster <span id="modal_cluster_required_star" class="required"
                                        style="display:none;">*</span>
                                </label>
                                <select id="modal_cluster_id" name="leadership[0][cluster_id]" class="form-select">
                                    <option value="">Select Cluster</option>
                                </select>
                            </div>

                            <div class="col-md-6" id="modal_org_wrap" style="display:none;">
                                <label class="form-label" for="modal_organization_id">
                                    Organization <span id="modal_org_required_star" class="required"
                                        style="display:none;">*</span>
                                </label>
                                <select id="modal_organization_id" name="leadership[0][organization_id]"
                                    class="form-select">
                                    <option value="">Select Organization</option>
                                </select>
                                <small id="modal_org_optional_hint" class="text-muted" style="display:none;">
                                    Optional for non-CCO.
                                </small>
                            </div>
                        </div>

                        {{-- Position & Leadership Status --}}
                        <div class="row g-3 mb-2">
                            <div class="col-md-6">
                                <label class="form-label" for="modal_position_id">
                                    Position Held <span class="required">*</span>
                                </label>
                                <select id="modal_position_id" name="leadership[0][position_id]" class="form-select"
                                    required>
                                    <option value="">Select Position</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="modal_leadership_status">
                                    Leadership Status <span class="required">*</span>
                                </label>
                                <select id="modal_leadership_status" name="leadership[0][leadership_status]"
                                    class="form-select" required>
                                    <option value="">Select your leadership status</option>
                                    <option value="Active">Active (Current Officer/Leader)</option>
                                    <option value="Inactive">Inactive (Former Officer/Leader)</option>
                                </select>
                            </div>
                        </div>

                        {{-- Term & Issued By --}}
                        <div class="row g-3 mb-2">
                            <div class="col-md-6">
                                <label class="form-label" for="modal_term">
                                    Leadership Term (School Year) <span class="required">*</span>
                                </label>
                                <input id="modal_term" type="text" name="leadership[0][term]" class="form-control"
                                    placeholder="e.g., 2023-2024" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="modal_issued_by">
                                    Issued By <span class="required">*</span>
                                </label>
                                <input id="modal_issued_by" type="text" name="leadership[0][issued_by]" class="form-control"
                                    required>
                            </div>
                        </div>

                        {{-- Optional: scope/from/to --}}

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Leadership Info</button>
                        </div>
                </form>
            </div>
        </div>
    </div>

@endsection

{{-- Profile Completion Modal - Render outside content section for proper positioning --}}
@php
    $shouldShowModal = !$user->profile_completed || session('show_profile_modal');
@endphp
@if ($shouldShowModal)
<div id="profileCompletionModal" class="profile-completion-modal" style="display: none;">
    <div class="profile-completion-backdrop"></div>
    <div class="profile-completion-dialog">
        <div class="profile-completion-header">
            <h2>Complete Your Profile</h2>
            <p class="text-muted">Please complete your academic and leadership information to continue.</p>
        </div>
        
        <div class="profile-completion-body">
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('profile.complete.student.store') }}" id="profileCompletionForm" novalidate>
                @csrf

                {{-- Centralized route registry for register.js --}}
                <meta id="slea-routes"
                    data-programs="{{ route('ajax.programs') }}"
                    data-majors="{{ route('ajax.majors') }}"
                    data-clusters="{{ route('ajax.clusters') }}"
                    data-organizations="{{ route('ajax.organizations') }}"
                    data-positions="{{ route('ajax.positions') }}"
                    @if(\Illuminate\Support\Facades\Route::has('ajax.council.positions'))
                    data-council-positions="{{ route('ajax.council.positions') }}"
                    @endif
                    @if(\Illuminate\Support\Facades\Route::has('ajax.academics.map'))
                    data-academics-map="{{ route('ajax.academics.map') }}"
                    @endif>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Academic Information</h5>
                        <div class="row g-3">
                            {{-- Student ID --}}
                            <div class="col-md-4">
                                <label class="form-label" for="modal_student_id">
                                    Student ID <span class="required">*</span>
                                </label>
                                <input
                                    id="modal_student_id"
                                    type="text"
                                    name="student_id"
                                    class="form-control @error('student_id') is-invalid @enderror"
                                    placeholder="e.g. 2021-00001"
                                    value="{{ old('student_id') }}"
                                    required
                                    autocomplete="off">
                                @error('student_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- College --}}
                            <div class="col-md-4">
                                <label class="form-label" for="modal_college_id">
                                    College <span class="required">*</span>
                                </label>
                                <select
                                    name="college_id"
                                    id="modal_college_id"
                                    class="form-select @error('college_id') is-invalid @enderror"
                                    required>
                                    <option value="">Select College</option>
                                    @foreach ($colleges as $c)
                                        <option
                                            value="{{ $c->id }}"
                                            {{ (string)old('college_id') === (string)$c->id ? 'selected' : '' }}>
                                            {{ $c->college_name ?? $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('college_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Program --}}
                            <div class="col-md-4">
                                <label class="form-label" for="modal_program_id">
                                    Program <span class="required">*</span>
                                </label>
                                <select
                                    name="program_id"
                                    id="modal_program_id"
                                    class="form-select @error('program_id') is-invalid @enderror"
                                    required>
                                    <option value="">Select Program</option>
                                </select>
                                @error('program_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Major --}}
                            <div class="col-md-4">
                                <label class="form-label" for="modal_major_id">Major</label>
                                <select
                                    name="major_id"
                                    id="modal_major_id"
                                    class="form-select @error('major_id') is-invalid @enderror">
                                    <option value="">Select Major</option>
                                </select>
                                @error('major_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Year Level --}}
                            <div class="col-md-4">
                                <label class="form-label" for="modal_year_level">
                                    Year Level <span class="required">*</span>
                                </label>
                                <select
                                    id="modal_year_level"
                                    name="year_level"
                                    class="form-select @error('year_level') is-invalid @enderror"
                                    required>
                                    <option value="">--</option>
                                    <option value="1" {{ old('year_level') == '1' ? 'selected' : '' }}>1st Year</option>
                                    <option value="2" {{ old('year_level') == '2' ? 'selected' : '' }}>2nd Year</option>
                                    <option value="3" {{ old('year_level') == '3' ? 'selected' : '' }}>3rd Year</option>
                                    <option value="4" {{ old('year_level') == '4' ? 'selected' : '' }}>4th Year</option>
                                    <option value="5" {{ old('year_level') == '5' ? 'selected' : '' }}>5th Year</option>
                                    <option value="6" {{ old('year_level') == '6' ? 'selected' : '' }}>6th Year</option>
                                    <option value="7" {{ old('year_level') == '7' ? 'selected' : '' }}>7th Year</option>
                                    <option value="8" {{ old('year_level') == '8' ? 'selected' : '' }}>8th Year</option>
                                </select>
                                @error('year_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Leadership Information</h5>
                        <div class="row g-3">
                            {{-- Leadership Type --}}
                            <div class="col-md-4">
                                <label class="form-label" for="modal_leadership_type_id">
                                    Leadership Type <span class="required">*</span>
                                </label>
                                <select
                                    name="leadership_type_id"
                                    id="modal_leadership_type_id"
                                    class="form-select @error('leadership_type_id') is-invalid @enderror"
                                    required>
                                    <option value="">Select Leadership Type</option>
                                    @foreach ($leadershipTypes as $type)
                                        <option
                                            value="{{ $type->id }}"
                                            data-key="{{ $type->key ?? '' }}"
                                            data-requires-org="{{ (int)($type->requires_org ?? 0) }}"
                                            data-old="{{ old('leadership_type_id') }}"
                                            {{ (string)old('leadership_type_id') === (string)$type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('leadership_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Cluster (conditional) --}}
                            <div class="col-md-4" id="modal_cluster_wrap" style="display: none;">
                                <label class="form-label" for="modal_cluster_id">
                                    Cluster <span id="modal_cluster_required_star" class="required" style="display: none;">*</span>
                                </label>
                                <select
                                    name="cluster_id"
                                    id="modal_cluster_id"
                                    class="form-select @error('cluster_id') is-invalid @enderror">
                                    <option value="">Select Cluster</option>
                                </select>
                                @error('cluster_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Organization (conditional) --}}
                            <div class="col-md-4" id="modal_org_wrap" style="display: none;">
                                <label class="form-label" for="modal_organization_id">
                                    Organization <span id="modal_org_required_star" class="required" style="display: none;">*</span>
                                </label>
                                <select
                                    name="organization_id"
                                    id="modal_organization_id"
                                    class="form-select @error('organization_id') is-invalid @enderror">
                                    <option value="">Select Organization</option>
                                </select>
                                <small id="modal_org_optional_hint" class="text-muted" style="display: none;">Optional for non-CCO.</small>
                                @error('organization_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Position --}}
                            <div class="col-md-4">
                                <label class="form-label" for="modal_position_id">
                                    Position <span class="required">*</span>
                                </label>
                                <select
                                    name="position_id"
                                    id="modal_position_id"
                                    class="form-select @error('position_id') is-invalid @enderror"
                                    data-old="{{ old('position_id') }}"
                                    required>
                                    <option value="">Select Position</option>
                                </select>
                                @error('position_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Term --}}
                            <div class="col-md-4">
                                <label class="form-label" for="modal_term">
                                    Term <span class="required">*</span>
                                </label>
                                <input
                                    id="modal_term"
                                    type="text"
                                    name="term"
                                    class="form-control @error('term') is-invalid @enderror"
                                    placeholder="e.g. 2023-2024"
                                    value="{{ old('term') }}"
                                    required>
                                @error('term')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Issued By --}}
                            <div class="col-md-4">
                                <label class="form-label" for="modal_issued_by">
                                    Issued By <span class="required">*</span>
                                </label>
                                <input
                                    id="modal_issued_by"
                                    type="text"
                                    name="issued_by"
                                    class="form-control @error('issued_by') is-invalid @enderror"
                                    placeholder="e.g. OSAS"
                                    value="{{ old('issued_by') }}"
                                    required>
                                @error('issued_by')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Leadership Status --}}
                            <div class="col-md-4">
                                <label class="form-label" for="modal_leadership_status">
                                    Status <span class="required">*</span>
                                </label>
                                <select
                                    id="modal_leadership_status"
                                    name="leadership_status"
                                    class="form-select @error('leadership_status') is-invalid @enderror"
                                    required>
                                    <option value="">--</option>
                                    <option value="Active" {{ old('leadership_status') == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ old('leadership_status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('leadership_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Complete Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('styles')
<style>
    /* Profile Completion Modal Styles */
    .profile-completion-modal {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 999999 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
    }

    .profile-completion-backdrop {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background-color: rgba(0, 0, 0, 0.6) !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
        z-index: 1000000 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .profile-completion-dialog {
        position: fixed !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        -webkit-transform: translate(-50%, -50%) !important;
        z-index: 1000001 !important;
        background: #fff;
        border-radius: 12px;
        max-width: 900px;
        width: 90%;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        margin: 0 !important;
        overflow: hidden !important;
    }

    body.dark-mode .profile-completion-dialog {
        background: #2b2b2b;
        color: #fff;
    }

    .profile-completion-header {
        padding: 24px 30px;
        border-bottom: 1px solid #dee2e6;
        flex-shrink: 0;
    }

    body.dark-mode .profile-completion-header {
        border-bottom-color: #444;
    }

    .profile-completion-header h2 {
        margin: 0 0 8px 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
    }

    body.dark-mode .profile-completion-header h2 {
        color: #fff;
    }

    .profile-completion-body {
        padding: 24px 30px;
        overflow-y: auto;
        flex: 1;
        min-height: 0;
    }

    .profile-completion-body::-webkit-scrollbar {
        width: 8px;
    }

    .profile-completion-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    body.dark-mode .profile-completion-body::-webkit-scrollbar-track {
        background: #2e2e2e;
    }

    .profile-completion-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .profile-completion-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    body.dark-mode .profile-completion-body::-webkit-scrollbar-thumb {
        background: #666;
    }

    body.dark-mode .profile-completion-body::-webkit-scrollbar-thumb:hover {
        background: #888;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/register.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('profileCompletionModal');
        if (!modal) return;

        // Show modal automatically if profile is incomplete
        const profileCompleted = @json($user->profile_completed ?? false);
        const showModalFlag = @json(session('show_profile_modal', false));
        const shouldShow = !profileCompleted || showModalFlag;
        
        if (shouldShow) {
            // Force modal to show with high z-index and proper positioning
            modal.style.display = 'flex';
            modal.style.visibility = 'visible';
            modal.style.opacity = '1';
            modal.style.zIndex = '999999';
            modal.style.position = 'fixed';
            modal.style.top = '0';
            modal.style.left = '0';
            modal.style.width = '100vw';
            modal.style.height = '100vh';
            modal.style.margin = '0';
            modal.style.padding = '0';
            document.body.style.overflow = 'hidden';
            
            // Ensure backdrop and dialog are properly positioned
            const backdrop = modal.querySelector('.profile-completion-backdrop');
            const dialog = modal.querySelector('.profile-completion-dialog');
            if (backdrop) {
                backdrop.style.position = 'fixed';
                backdrop.style.top = '0';
                backdrop.style.left = '0';
                backdrop.style.width = '100vw';
                backdrop.style.height = '100vh';
                backdrop.style.zIndex = '1000000';
                backdrop.style.margin = '0';
                backdrop.style.padding = '0';
            }
            if (dialog) {
                dialog.style.position = 'fixed';
                dialog.style.top = '50%';
                dialog.style.left = '50%';
                dialog.style.transform = 'translate(-50%, -50%)';
                dialog.style.zIndex = '1000001';
                dialog.style.margin = '0';
            }
            
            // Force modal to be visible above everything (double-check after a short delay)
            setTimeout(function() {
                if (modal) {
                    modal.style.display = 'flex';
                    modal.style.visibility = 'visible';
                    modal.style.opacity = '1';
                }
            }, 50);
        }

        // Prevent closing modal by clicking backdrop
        const backdrop = modal.querySelector('.profile-completion-backdrop');
        if (backdrop) {
            backdrop.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                // Do nothing - modal cannot be closed
            });
        }

        // Prevent ESC key from closing
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.style.display === 'flex') {
                e.preventDefault();
                e.stopPropagation();
                // Do nothing - modal cannot be closed
            }
        });

        // Custom handlers for modal fields (register.js may not work with modal IDs)
        const form = document.getElementById('profileCompletionForm');
        if (form) {
            // College -> Program -> Major chain
            const collegeSelect = document.getElementById('modal_college_id');
            const programSelect = document.getElementById('modal_program_id');
            const majorSelect = document.getElementById('modal_major_id');
            
            if (collegeSelect && programSelect) {
                collegeSelect.addEventListener('change', function() {
                    const collegeId = this.value;
                    programSelect.innerHTML = '<option value="">Select Program</option>';
                    if (majorSelect) majorSelect.innerHTML = '<option value="">Select Major</option>';
                    
                    if (collegeId) {
                        fetch(`{{ route('ajax.programs') }}?college_id=${collegeId}`)
                            .then(res => res.json())
                            .then(data => {
                                data.forEach(prog => {
                                    const option = document.createElement('option');
                                    option.value = prog.id;
                                    option.textContent = prog.name;
                                    programSelect.appendChild(option);
                                });
                            })
                            .catch(err => console.error('Error loading programs:', err));
                    }
                });
            }

            if (programSelect && majorSelect) {
                programSelect.addEventListener('change', function() {
                    const programId = this.value;
                    majorSelect.innerHTML = '<option value="">Select Major</option>';
                    
                    if (programId) {
                        fetch(`{{ route('ajax.majors') }}?program_id=${programId}`)
                            .then(res => res.json())
                            .then(data => {
                                data.forEach(major => {
                                    const option = document.createElement('option');
                                    option.value = major.id;
                                    option.textContent = major.name;
                                    majorSelect.appendChild(option);
                                });
                            })
                            .catch(err => console.error('Error loading majors:', err));
                    }
                });
            }

            // Leadership type handler
            const leadershipTypeSelect = document.getElementById('modal_leadership_type_id');
            const clusterWrap = document.getElementById('modal_cluster_wrap');
            const orgWrap = document.getElementById('modal_org_wrap');
            const clusterSelect = document.getElementById('modal_cluster_id');
            const orgSelect = document.getElementById('modal_organization_id');
            const positionSelect = document.getElementById('modal_position_id');
            const clusterStar = document.getElementById('modal_cluster_required_star');
            const orgStar = document.getElementById('modal_org_required_star');

            if (leadershipTypeSelect) {
                leadershipTypeSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const requiresOrg = selectedOption.dataset.requiresOrg === '1';
                    const isCCO = selectedOption.dataset.key === 'cco';
                    const leadershipTypeId = this.value;

                    // Show/hide cluster and org fields
                    if (requiresOrg && !isCCO) {
                        if (clusterWrap) clusterWrap.style.display = 'block';
                        if (orgWrap) orgWrap.style.display = 'block';
                        if (clusterStar) clusterStar.style.display = 'inline';
                        if (orgStar) orgStar.style.display = 'inline';
                        if (clusterSelect) clusterSelect.setAttribute('required', 'required');
                        if (orgSelect) orgSelect.setAttribute('required', 'required');
                    } else {
                        if (clusterWrap) clusterWrap.style.display = 'none';
                        if (orgWrap) orgWrap.style.display = 'none';
                        if (clusterStar) clusterStar.style.display = 'none';
                        if (orgStar) orgStar.style.display = 'none';
                        if (clusterSelect) {
                            clusterSelect.removeAttribute('required');
                            clusterSelect.value = '';
                        }
                        if (orgSelect) {
                            orgSelect.removeAttribute('required');
                            orgSelect.value = '';
                        }
                    }

                    // Load clusters if needed
                    if (requiresOrg && !isCCO && clusterSelect) {
                        fetch(`{{ route('ajax.clusters') }}`)
                            .then(res => res.json())
                            .then(data => {
                                clusterSelect.innerHTML = '<option value="">Select Cluster</option>';
                                data.forEach(cluster => {
                                    const option = document.createElement('option');
                                    option.value = cluster.id;
                                    option.textContent = cluster.name;
                                    clusterSelect.appendChild(option);
                                });
                            })
                            .catch(err => console.error('Error loading clusters:', err));
                    }

                    // Load positions
                    if (positionSelect && leadershipTypeId) {
                        fetch(`{{ route('ajax.positions') }}?leadership_type_id=${leadershipTypeId}`)
                            .then(res => res.json())
                            .then(data => {
                                positionSelect.innerHTML = '<option value="">Select Position</option>';
                                data.forEach(position => {
                                    const option = document.createElement('option');
                                    option.value = position.id;
                                    option.textContent = position.name;
                                    positionSelect.appendChild(option);
                                });
                            })
                            .catch(err => console.error('Error loading positions:', err));
                    }
                });

                // Trigger change if value is already selected
                if (leadershipTypeSelect.value) {
                    leadershipTypeSelect.dispatchEvent(new Event('change'));
                }
            }

            // Cluster change handler
            if (clusterSelect && orgSelect) {
                clusterSelect.addEventListener('change', function() {
                    const clusterId = this.value;
                    orgSelect.innerHTML = '<option value="">Select Organization</option>';
                    
                    if (clusterId) {
                        const leadershipTypeId = leadershipTypeSelect?.value || '';
                        fetch(`{{ route('ajax.organizations') }}?cluster_id=${clusterId}${leadershipTypeId ? '&leadership_type_id=' + leadershipTypeId : ''}`)
                            .then(res => res.json())
                            .then(data => {
                                data.forEach(org => {
                                    const option = document.createElement('option');
                                    option.value = org.id;
                                    option.textContent = org.name;
                                    orgSelect.appendChild(option);
                                });
                            })
                            .catch(err => console.error('Error loading organizations:', err));
                    }
                });
            }

            // Organization change handler (may need to reload positions)
            if (orgSelect && positionSelect && leadershipTypeSelect) {
                orgSelect.addEventListener('change', function() {
                    const orgId = this.value;
                    const leadershipTypeId = leadershipTypeSelect.value;
                    if (leadershipTypeId) {
                        fetch(`{{ route('ajax.positions') }}?leadership_type_id=${leadershipTypeId}${orgId ? '&organization_id=' + orgId : ''}`)
                            .then(res => res.json())
                            .then(data => {
                                positionSelect.innerHTML = '<option value="">Select Position</option>';
                                data.forEach(position => {
                                    const option = document.createElement('option');
                                    option.value = position.id;
                                    option.textContent = position.name;
                                    positionSelect.appendChild(option);
                                });
                            })
                            .catch(err => console.error('Error loading positions:', err));
                    }
                });
            }
        }

        // Block navigation to other sections - show modal instead of alert
        const sidebarLinks = document.querySelectorAll('.sidebar a, .sidebar button');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (modal && modal.style.display === 'flex') {
                    e.preventDefault();
                    e.stopPropagation();
                    // Show the profile completion modal instead of alert
                    if (modal) {
                        modal.style.display = 'flex';
                        modal.style.zIndex = '10000';
                        document.body.style.overflow = 'hidden';
                    }
                    return false;
                }
            });
        });
    });
</script>
@endpush