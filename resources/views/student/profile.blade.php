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
        $age = $student->birth_date ? Carbon::parse($student->birth_date)->age : null;

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
                        <p><strong>Birth Date:</strong>
                            <span>{{ $student->birth_date ? Carbon::parse($student->birth_date)->format('F d, Y') : 'N/A' }}</span>
                        </p>
                        <p><strong>Age:</strong> <span>{{ $age ?? 'N/A' }}</span></p>
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