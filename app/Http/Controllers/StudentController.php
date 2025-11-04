<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\StudentPersonalInformation;
use App\Models\AcademicInformation;
use App\Models\LeadershipInformation;
use App\Models\StudentAccount;
use App\Models\SystemMonitoringAndLog;

class StudentController extends Controller
{
    /**
     * Show the logged-in student's full profile dashboard.
     */
    public function profile()
    {
        $user = Auth::user();

        // Fetch student data with all related info
        $student = StudentPersonalInformation::with([
            'academicInformation',
            'leadershipInformation'
        ])
            ->where('email_address', $user->email_address)
            ->firstOrFail();

        return view('student.profile', compact('student'));
    }

    /**
     * Update the student's year level, program, or major.
     */
    public function updateAcademicInfo(Request $request)
    {
        $validated = $request->validate([
            'year_level' => 'required|string|max:20',
            'program' => 'required|string|max:100',
            'major' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
        $academic = AcademicInformation::where('student_id', $user->student_id)->first();

        if ($academic) {
            $academic->update($validated);
        } else {
            $validated['student_id'] = $user->student_id;
            AcademicInformation::create($validated);
        }

        // Log action
        SystemMonitoringAndLog::create([
            'user_email' => $user->email_address,
            'activity' => 'Updated academic information',
        ]);

        return back()->with('success', 'Academic information updated successfully.');
    }

    /**
     * Change student password securely.
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $student = Auth::user();

        if (!Hash::check($validated['current_password'], $student->password)) {
            return back()->withErrors(['current_password' => 'Incorrect current password.']);
        }

        $student->password = Hash::make($validated['password']);
        $student->save();

        SystemMonitoringAndLog::create([
            'user_email' => $student->email_address,
            'activity' => 'Changed account password',
        ]);

        return back()->with('success', 'Password changed successfully!');
    }

    /**
     * Upload Certificate of Registration (COR).
     */
    public function uploadCOR(Request $request)
    {
        $request->validate([
            'cor' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $user = Auth::user();
        $path = $request->file('cor')->store('certificates/cor', 'public');

        $academic = AcademicInformation::where('student_id', $user->student_id)->first();
        if ($academic) {
            $academic->update(['cor_file' => $path]);
        }

        SystemMonitoringAndLog::create([
            'user_email' => $user->email_address,
            'activity' => 'Uploaded Certificate of Registration',
        ]);

        return back()->with('success', 'Certificate of Registration uploaded successfully!');
    }

    /**
     * Update student profile picture (avatar).
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();
        $path = $request->file('avatar')->store('avatars', 'public');

        $student = StudentPersonalInformation::where('student_id', $user->student_id)->first();
        if ($student) {
            if ($student->profile_picture_path) {
                Storage::disk('public')->delete($student->profile_picture_path);
            }
            $student->update(['profile_picture_path' => $path]);
        }

        SystemMonitoringAndLog::create([
            'user_email' => $user->email_address,
            'activity' => 'Updated profile picture',
        ]);

        return back()->with('success', 'Profile picture updated!');
    }
}
