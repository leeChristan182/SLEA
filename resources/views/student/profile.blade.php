@extends('layouts.app')

@section('title', 'Student Profile')

@section('content')
<div class="student-profile-page">
    <div class="container">
        @include('partials.sidebar')

        <main class="main-content">
            <!-- Avatar -->
            <div class="avatar-container">
                <img src="{{ $student->profile_picture_path ? asset('storage/'.$student->profile_picture_path) : 'https://via.placeholder.com/120' }}"
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

            <!-- Personal + Academic -->
            <section class="profile-section">
                <!-- Personal Information -->
                <div class="profile-info">
                    <h3>Personal Information</h3>
                    <p><strong>Name:</strong>
                        <span>{{ strtoupper($student->last_name) }}, {{ $student->first_name }} {{ $student->middle_name }}</span>
                    </p>
                    <p><strong>Contact Number:</strong> <span>{{ $student->contact_number ?? 'N/A' }}</span></p>
                    <p><strong>Email Address:</strong> <span>{{ $student->email_address }}</span></p>
                    <p><strong>Birth Date:</strong>
                        <span>{{ \Carbon\Carbon::parse($student->date_of_birth)->format('F d, Y') }}</span>
                    </p>
                    <p><strong>Age:</strong> <span>{{ $student->age ?? 'N/A' }}</span></p>
                </div>

                <!-- Academic Information -->
                <div class="profile-info">
                    <h3>Academic Information</h3>
                    <p><strong>Student ID:</strong> <span>{{ $student->student_id }}</span></p>
                    <p><strong>Program:</strong> <span>{{ $student->academicInformation->program ?? 'N/A' }}</span></p>
                    <p><strong>Major:</strong> <span>{{ $student->academicInformation->major ?? 'N/A' }}</span></p>
                    <p><strong>College:</strong> <span>{{ $student->academicInformation->college ?? 'N/A' }}</span></p>
                    <p><strong>Year Level:</strong> <span>{{ $student->academicInformation->year_level ?? 'N/A' }}</span></p>
                    <p><strong>Expected Year to Graduate:</strong> <span>{{ $student->academicInformation->expected_grad_year ?? 'N/A' }}</span></p>
                </div>
            </section>

            <!-- Leadership Information -->
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
                            @forelse ($student->leadershipInformation as $lead)
                            <tr>
                                <td>{{ $lead->type ?? '—' }}</td>
                                <td>{{ $lead->organization_name ?? '—' }}</td>
                                <td>{{ $lead->role ?? '—' }}</td>
                                <td>{{ $lead->term ?? '—' }}</td>
                                <td>{{ $lead->issued_by ?? '—' }}</td>
                                <td>{{ $lead->status ?? '—' }}</td>
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

            <!-- Settings: Change Password | Update Year Level | Upload COR -->
            <section class="settings-grid" style="margin-top:24px;">
                <!-- Change Password -->
                <div class="change-password settings-left" style="max-width:none;">
                    <h3>Change Password</h3>

                    <form action="{{ route('student.changePassword') }}" method="POST">
                        @csrf
                        <label for="current_password">Present Password</label>
                        <input id="current_password" name="current_password" type="password" required>

                        <div class="requirements">
                            <p>A new password must contain the following:</p>
                            <ul id="passwordChecklist">
                                <li>Minimum of 8 characters</li>
                                <li>An uppercase character</li>
                                <li>A lowercase character</li>
                                <li>A number</li>
                                <li>A special character</li>
                            </ul>
                        </div>

                        <label for="password">New Password</label>
                        <input id="password" name="password" type="password" required>

                        <label for="password_confirmation">Confirm Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required>

                        <button class="change-btn" type="submit">Change Password</button>
                    </form>
                </div>

                <!-- Update Year Level -->
                <div class="profile-info settings-year" style="max-width:none;">
                    <h3>Update Year Level</h3>
                    <form action="{{ route('student.updateAcademic') }}" method="POST">
                        @csrf
                        <label for="year_level">Select year level</label>
                        <select id="year_level" name="year_level" required>
                            <option value="">— Select —</option>
                            @foreach(['1st Year', '2nd Year', '3rd Year', '4th Year'] as $level)
                            <option value="{{ $level }}"
                                {{ ($student->academicInformation->year_level ?? '') === $level ? 'selected' : '' }}>
                                {{ $level }}
                            </option>
                            @endforeach
                        </select>

                        <label for="program" style="margin-top:10px;">Program</label>
                        <input id="program" name="program" type="text"
                            value="{{ $student->academicInformation->program ?? '' }}" required>

                        <label for="major" style="margin-top:10px;">Major (optional)</label>
                        <input id="major" name="major" type="text"
                            value="{{ $student->academicInformation->major ?? '' }}">

                        <button class="change-btn" type="submit" style="margin-top:14px;">Update</button>
                    </form>
                </div>

                <!-- Upload COR -->
                <div class="profile-info settings-cor" style="max-width:none;">
                    <h3>Upload Certificate of Registration</h3>
                    <form action="{{ route('student.uploadCOR') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label for="cor">Choose file</label>
                        <input id="cor" name="cor" type="file" accept=".jpg,.jpeg,.png,.pdf" required>
                        <small>max size 5MB • JPG, PNG or PDF</small>
                        <button class="change-btn" type="submit" style="margin-top:14px;">Upload</button>

                        @if(!empty($student->academicInformation->cor_file))
                        <p style="margin-top:8px;">
                            Current file:
                            <a href="{{ asset('storage/'.$student->academicInformation->cor_file) }}" target="_blank">
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

{{-- Custom styles --}}
<style>
    .student-profile-page .settings-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-template-areas:
            "left rightTop"
            "left rightBottom";
        gap: 24px;
        align-items: start;
    }

    .student-profile-page .settings-left {
        grid-area: left;
        display: flex;
        flex-direction: column;
    }

    .student-profile-page .settings-year {
        grid-area: rightTop;
    }

    .student-profile-page .settings-cor {
        grid-area: rightBottom;
    }

    @media (max-width:1200px) {
        .student-profile-page .settings-grid {
            grid-template-columns: 1fr;
            grid-template-areas:
                "rightTop"
                "rightBottom"
                "left";
        }
    }
</style>
@endsection