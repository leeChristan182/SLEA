@extends('layouts.app')

@section('title', 'Profile Dashboard')

@section('content')
<div class="student-profile-page"> {{-- NEW: scope wrapper --}}
    <div class="container">
        @include('partials.sidebar')
        <main class="main-content">
            <!-- Avatar -->
            <div class="avatar-container">
                <img src="https://via.placeholder.com/120" class="avatar" id="avatarPreview" alt="Avatar">
                <button class="edit-icon" onclick="document.getElementById('avatarUpload').click()" type="button" title="Change photo">
                    <i class="fas fa-pencil-alt"></i>
                </button>
                <input type="file" id="avatarUpload" accept="image/*" style="display:none;">
            </div>

            <!-- Top: Personal + Academic -->
            <section class="profile-section">
                <!-- Personal Information -->
                <div class="profile-info">
                    <h3>Personal Information</h3>
                    <p><strong>Name:</strong> <span>MANOCAY, Edryan S.</span></p>
                    <p><strong>Contact Number:</strong> <span>09991752790</span></p>
                    <p><strong>Email Address:</strong> <span>student.name@usep.edu.ph</span></p>
                    <p><strong>Birth Date:</strong> <span>March 16, 2004</span></p>
                    <p><strong>Age:</strong> <span>21</span></p>
                </div>

                <!-- Academic Information -->
                <div class="profile-info">
                    <h3>Academic Information</h3>
                    <p><strong>Student ID:</strong> <span>2022-00216</span></p>
                    <p><strong>Program:</strong> <span>Bachelor of Science in Computer Science</span></p>
                    <p><strong>Major:</strong> <span>Data Science</span></p>
                    <p><strong>College:</strong> <span>College of Information and Computing</span></p>
                    <p><strong>Year Level:</strong> <span>3rd Year</span></p>
                    <p><strong>Expected Year to Graduate:</strong> <span>2026</span></p>
                </div>
            </section>

            <!-- Leadership Information -->
            <section class="profile-info" style="margin-top:24px;">
                <h3>Leadership Information</h3>
                <form class="mb-3" onsubmit="return false;">
                    <label for="leadershipType" class="me-2">Type</label>
                    <select id="leadershipType" name="type">
                        <option value="">All</option>
                        <option value="University">University</option>
                        <option value="College">College</option>
                        <option value="Department">Department</option>
                        <option value="Organization">Organization</option>
                    </select>
                </form>
                <div class="table-responsive">
                    <table class="approval-table w-100" id="leadershipTable">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Organization Name</th>
                                <th>Organization Role</th>
                                <th>Term</th>
                                <th>Issued By</th>
                                <th>Leadership Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr data-type="University">
                                <td>University</td>
                                <td>Student Council</td>
                                <td>President</td>
                                <td>AY 2024–2025</td>
                                <td>OSAS</td>
                                <td>Active</td>
                            </tr>
                            <tr data-type="College">
                                <td>College</td>
                                <td>CICS Society</td>
                                <td>Secretary</td>
                                <td>AY 2023–2024</td>
                                <td>CICS</td>
                                <td>Inactive</td>
                            </tr>
                            <tr data-type="Organization">
                                <td>Organization</td>
                                <td>Google Dev Club</td>
                                <td>Member</td>
                                <td>2022–2023</td>
                                <td>GDSC</td>
                                <td>Active</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Bottom: Change Password | Update Year Level | Upload COR -->
            <section class="settings-grid" style="margin-top:24px;">
                <div class="change-password settings-left" style="max-width:none;">
                    <h3>Change Password</h3>
                    <label for="current_password">Present Password</label>
                    <input id="current_password" type="password" placeholder="••••••••">
                    <div class="requirements">
                        <p>A new password must contain the following:</p>
                        <ul id="passwordChecklist">
                            <li class="invalid" data-rule="length">Minimum of 8 characters</li>
                            <li class="invalid" data-rule="uppercase">An uppercase character</li>
                            <li class="invalid" data-rule="lowercase">A lowercase character</li>
                            <li class="invalid" data-rule="number">A number</li>
                            <li class="invalid" data-rule="special">A special character</li>
                        </ul>
                    </div>
                    <label for="password">New Password</label>
                    <input id="password" type="password" placeholder="••••••••">
                    <label for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" type="password" placeholder="••••••••">
                    <label style="display:flex;align-items:center;gap:8px;margin-top:6px;">
                        <input type="checkbox" id="showPass"> Show Password
                    </label>
                    <button class="change-btn" type="button" onclick="alert('Demo only')">Change Password</button>
                </div>

                <div class="profile-info settings-year" style="max-width:none;">
                    <h3>Update Year Level</h3>
                    <label for="year_level">Select year level</label>
                    <select id="year_level">
                        <option value="">— Select —</option>
                        <option>1st Year</option>
                        <option>2nd Year</option>
                        <option selected>3rd Year</option>
                        <option>4th Year</option>
                    </select>
                    <label for="program" style="margin-top:10px;">Program</label>
                    <input id="program" type="text" value="Bachelor of Science in Computer Science">
                    <label for="major" style="margin-top:10px;">Major (optional)</label>
                    <input id="major" type="text" value="Data Science">
                    <button class="change-btn" type="button" style="margin-top:14px;" onclick="alert('Demo only')">Update</button>
                </div>

                <div class="profile-info settings-cor" style="max-width:none;">
                    <h3>Upload Certificate of Registration</h3>
                    <label for="cor">Choose file</label>
                    <input id="cor" type="file" accept=".jpg,.jpeg,.png,.pdf">
                    <small>max size 5MB • JPG, PNG or PDF</small>
                    <button class="change-btn" type="button" style="margin-top:14px;" onclick="alert('Demo only')">Upload</button>
                </div>
            </section>
        </main>
    </div>
</div> {{-- /student-profile-page --}}
{{-- Minimal, front‑end only helpers --}}

<style>
    /* Scoped only to .student-profile-page */
    .student-profile-page .settings-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-template-rows: auto auto;
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
        height: 100%;
        flex: 1;
    }

    .student-profile-page .settings-year {
        grid-area: rightTop;
        display: flex;
        flex-direction: column;
        height: 100%;
        flex: 1;


    }

    .student-profile-page .settings-cor {
        grid-area: rightBottom;
        display: flex;
        flex-direction: column;
        height: 100%;
        flex: 1;


    }

    .student-profile-page .settings-grid>.profile-info,
    .student-profile-page .settings-grid>.change-password {
        width: 100%;
    }

    .student-profile-page .settings-grid .change-btn {
        width: 100%;
    }

    @media (min-width:1400px) {
        .student-profile-page .settings-grid {
            grid-template-columns: 1fr 1fr;
            gap: 28px;
        }
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
