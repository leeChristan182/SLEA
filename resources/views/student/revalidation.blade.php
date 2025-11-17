@extends('layouts.app')

@section('title', 'Account Revalidation')

@section('content')
@php
use Carbon\Carbon;
/** @var \App\Models\User $user */

// Same student/academic resolution as profile
$student = $student ?? ($user ?? auth()->guard('student')->user() ?? auth()->user());
$acad = $academic ?? optional($student->studentAcademic);
$age = $student->birth_date ? Carbon::parse($student->birth_date)->age : null;

// Determine status with safe default
$currentStatus = $acad->eligibility_status ?? 'eligible';

$badgeColors = [
'eligible' => '198754', // green
'needs_revalidation' => 'fd7e14', // orange
'under_review' => '0d6efd', // blue
'ineligible' => 'dc3545', // red
];

$badgeColor = $badgeColors[$currentStatus] ?? '6c757d';

// Compute "past expected grad year" like in middleware
$nowYear = (int) now()->year;
$expected = $acad && $acad->expected_grad_year ? (int) $acad->expected_grad_year : null;
$isPastGrad = $expected ? ($nowYear > $expected) : false;

// Decide if they REALLY need revalidation
$needsRevalidationStatuses = ['needs_revalidation', 'under_review', 'ineligible'];
$requiresRevalidation = $isPastGrad || in_array($currentStatus, $needsRevalidationStatuses, true);
@endphp

<div class="student-profile-page">
    <div class="container">
        @include('partials.sidebar')

        <main class="main-content">
            {{-- Banner --}}
            <section class="profile-info" style="margin-bottom:20px; border-top-color:#fd7e14;">
                @if($requiresRevalidation)
                <h3>Account Revalidation Required</h3>
                <p>
                    Your SLEA access is temporarily limited. Please update your academic
                    information and upload your latest Certificate of Registration (COR).
                    Once verified by OSAS, full functionality (submissions, history, etc.)
                    will be restored.
                </p>
                @else
                <h3>Account Status</h3>
                <p>
                    Your account is currently <strong>eligible</strong> for SLEA awards and full access.
                    You may still update your academic information or upload a newer COR below,
                    but revalidation is <strong>not required</strong> at this time.
                </p>
                @endif

                <p><strong>Current eligibility status:</strong>
                    <span class="badge" style="background:#{{ $badgeColor }};">
                        {{ $currentStatus }}
                    </span>
                </p>
            </section>

            {{-- Quick personal + academic summary (read-only) --}}
            <section class="profile-section" style="margin-bottom:24px;">
                <div class="profile-info">
                    <h3>Personal Information</h3>
                    <p><strong>Name:</strong>
                        <span>{{ strtoupper($student->last_name) }}, {{ $student->first_name }} {{ $student->middle_name }}</span>
                    </p>
                    <p><strong>Email Address:</strong> <span>{{ $student->email }}</span></p>
                    <p><strong>Birth Date:</strong>
                        <span>{{ $student->birth_date ? Carbon::parse($student->birth_date)->format('F d, Y') : 'N/A' }}</span>
                    </p>
                    <p><strong>Age:</strong> <span>{{ $age ?? 'N/A' }}</span></p>
                </div>

                <div class="profile-info">
                    <h3>Current Academic Info (for reference)</h3>
                    <p><strong>Student Number:</strong> <span>{{ $acad->student_number ?? 'N/A' }}</span></p>
                    <p><strong>College:</strong> <span>{{ $acad->college_name ?? 'N/A' }}</span></p>
                    <p><strong>Program:</strong> <span>{{ $acad->program ?? 'N/A' }}</span></p>
                    <p><strong>Major:</strong> <span>{{ $acad->major ?? 'N/A' }}</span></p>
                    <p><strong>Year Level:</strong> <span>{{ $acad->year_level ?? 'N/A' }}</span></p>
                    <p><strong>Expected Year to Graduate:</strong> <span>{{ $acad->expected_grad_year ?? 'N/A' }}</span></p>
                </div>
            </section>

            {{-- Revalidation forms: Update Academic + Upload COR --}}
            <section class="settings-grid" style="margin-top:0;">
                {{-- Update Academic Details --}}
                <div class="profile-info settings-year">
                    <h3>Update Academic Details</h3>
                    <form action="{{ route('student.updateAcademic') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="year_level">Year Level</label>
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

                        <button class="change-btn" type="submit">Save Academic Info</button>
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
                        <button class="change-btn" type="submit" style="margin-top:12px;">Upload COR</button>

                        @if(!empty($acad->certificate_of_registration_path))
                        <p style="margin-top:8px;">
                            Current file:
                            <a href="{{ asset('storage/'.$acad->certificate_of_registration_path) }}" target="_blank">
                                View uploaded COR
                            </a>
                        </p>
                        @endif
                    </form>
                </div>

                {{-- Optional: Change Password box can go here --}}
            </section>
        </main>
    </div>
</div>
@endsection