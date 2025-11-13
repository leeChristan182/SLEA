@extends('layouts.app')

@section('title', 'Student Profile')

@section('content')
@php
use Carbon\Carbon;
/** @var \App\Models\User $student */
$student = $student ?? auth()->user();
$acad = optional($student->studentAcademic);
$age = $student->birth_date ? Carbon::parse($student->birth_date)->age : null;
@endphp

<div class="student-profile-page">
    <div class="container">
        @include('partials.sidebar')

        <main class="main-content">
            {{-- Avatar --}}
            <div class="avatar-container">
                <img
                    src="{{ $student->profile_picture_path ? asset('storage/'.$student->profile_picture_path) : asset('images/avatars/default-avatar.png') }}"
                    class="avatar"
                    id="avatarPreview"
                    alt="Avatar">

                <form action="{{ route('student.updateAvatar') }}" method="POST" enctype="multipart/form-data" id="avatarForm">
                    @csrf
                    <button class="edit-icon" type="button"
                        onclick="document.getElementById('avatarUpload').click()"
                        title="Change photo">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <input type="file" id="avatarUpload" name="avatar" accept="image/*" style="display:none;"
                        onchange="document.getElementById('avatarForm').submit();">
                </form>
            </div>

            {{-- Personal + Academic --}}
            <section class="profile-section">
                {{-- Personal Information --}}
                <div class="profile-info">
                    <h3>Personal Information</h3>
                    <p><strong>Name:</strong>
                        <span>{{ strtoupper($student->last_name) }}, {{ $student->first_name }} {{ $student->middle_name }}</span>
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
                    <p><strong>Student Number:</strong> <span>{{ $acad->student_number ?? 'N/A' }}</span></p>
                    <p><strong>College:</strong> <span>{{ $acad->college_name ?? 'N/A' }}</span></p>
                    <p><strong>Program:</strong> <span>{{ $acad->program ?? 'N/A' }}</span></p>
                    <p><strong>Major:</strong> <span>{{ $acad->major ?? 'N/A' }}</span></p>
                    <p><strong>Year Level:</strong> <span>{{ $acad->year_level ?? 'N/A' }}</span></p>
                    <p><strong>Expected Year to Graduate:</strong> <span>{{ $acad->expected_grad_year ?? 'N/A' }}</span></p>
                    <p><strong>Eligibility Status:</strong>
                        <span class="badge"
                            style="background:#{{ [
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

            {{-- Leadership Information (optional, shows if you have a relation/table) --}}
            <section class="profile-info" style="margin-top:24px;">
                <h3>Leadership Information</h3>

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
                            @forelse (($student->leaderships ?? []) as $lead)
                            <tr>
                                <td>{{ $lead->type->name ?? $lead->leadership_type ?? '—' }}</td>
                                <td>{{ $lead->organization->name ?? $lead->organization_name ?? '—' }}</td>
                                <td>{{ $lead->position->name ?? $lead->position ?? '—' }}</td>
                                <td>{{ $lead->term ?? '—' }}</td>
                                <td>{{ $lead->issued_by ?? '—' }}</td>
                                <td>{{ $lead->leadership_status ?? '—' }}</td>
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
                            <i class="fas fa-eye toggle-password" data-target="password"></i>
                        </div>

                        <label for="password_confirmation">Confirm Password</label>
                        <div class="password-wrapper">
                            <input id="password_confirmation" name="password_confirmation" type="password" required>
                            <i class="fas fa-eye toggle-password" data-target="password_confirmation"></i>
                        </div>

                        <button class="change-btn" type="submit">Change Password</button>
                    </form>
                </div>

                {{-- Update Academic Details --}}
                <div class="profile-info settings-year">
                    <h3>Update Academic Details</h3>
                    <form action="{{ route('student.updateAcademic') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="year_level">Year Level</label>
                            {{-- Controller expects numeric 1..5 per our validation --}}
                            <select id="year_level" name="year_level" required>
                                <option value="">— Select —</option>
                                @foreach([1=>'1st Year',2=>'2nd Year',3=>'3rd Year',4=>'4th Year',5=>'5th Year'] as $val=>$label)
                                <option value="{{ $val }}" {{ (string)($acad->year_level ?? '') === (string)$val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="program">Program</label>
                            <input id="program" name="program" type="text"
                                value="{{ $acad->program ?? '' }}" required>
                        </div>

                        <div class="form-group">
                            <label for="major">Major (optional)</label>
                            <input id="major" name="major" type="text"
                                value="{{ $acad->major ?? '' }}">
                        </div>

                        <button class="change-btn" type="submit">Update</button>
                    </form>
                </div>

                {{-- Upload COR --}}
                <div class="profile-info settings-cor">
                    <h3>Upload Certificate of Registration</h3>
                    <form action="{{ route('student.uploadCOR') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label for="cor">Choose file</label>
                        <input id="cor" name="cor" type="file" accept=".jpg,.jpeg,.png,.pdf" required>
                        <small>Max size 5MB • JPG, PNG, or PDF</small>
                        <button class="change-btn" type="submit" style="margin-top:12px;">Upload</button>

                        @if(!empty($acad->cor_file))
                        <p style="margin-top:8px;">
                            Current file:
                            <a href="{{ asset('storage/'.$acad->cor_file) }}" target="_blank">
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
        border-left: 5px solid #c0392b;
        border-radius: 10px;
        padding: 14px 18px;
        margin: 14px 0 20px;
        color: #2d2d2d;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.04);
    }

    .requirements.visible-box strong {
        display: block;
        font-weight: 700;
        color: #b21d1d;
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
        color: #b21d1d;
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
        border-color: #b21d1d;
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
        color: #c0392b;
    }

    /* === Buttons === */
    .change-btn {
        background-color: #c0392b;
        border: none;
        color: white;
        padding: 10px 16px;
        border-radius: 6px;
        font-weight: 600;
        transition: .25s;
        width: 100%;
    }

    .change-btn:hover {
        background-color: #a93226;
    }

    /* === Cards === */
    .profile-info,
    .change-password {
        border-top: 3px solid #c0392b;
        background-color: white;
        box-shadow: 0 3px 6px rgba(0, 0, 0, .06);
        border-radius: 10px;
        padding: 20px;
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

<script>
    document.querySelectorAll('.toggle-password').forEach(icon => {
        icon.addEventListener('click', () => {
            const target = document.getElementById(icon.dataset.target);
            if (!target) return;
            const isPassword = target.type === 'password';
            target.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('fa-eye-slash', isPassword);
        });
    });
</script>
@endsection