<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\StudentAccount;
use App\Models\StudentPersonalInformation;
use App\Models\AcademicInformation;
use App\Models\LeadershipInformation;
use App\Models\SystemMonitoringAndLog;

class StudentController extends Controller
{
    /**
     * Display the student profile.
     */
    public function profile()
    {
        $account = Auth::guard('student')->user();

        $student = StudentPersonalInformation::with([
            'academicInformation',
            'leadershipInformation'
        ])->where('email_address', $account->email_address)->first();

        // ensure academic info exists
        if (!$student->academicInformation) {
            AcademicInformation::create([
                'student_id' => $student->student_id,
                'program' => null,
                'major' => null,
                'year_level' => null,
                'graduate_prior' => null,
                'college' => null
            ]);
            $student->load('academicInformation');
        }

        return view('student.profile', compact('student'));
    }

    /**
     * Update academic information.
     */
    public function updateAcademicInfo(Request $request)
    {
        $student = Auth::guard('student')->user();

        $validated = $request->validate([
            'year_level' => 'required|string|max:50',
            'program' => 'required|string|max:100',
            'major' => 'nullable|string|max:100',
        ]);

        $academic = AcademicInformation::where('student_id', $student->student_id)->first();

        if ($academic) {
            $academic->update($validated);
        } else {
            AcademicInformation::create(array_merge($validated, [
                'student_id' => $student->student_id,
            ]));
        }

        SystemMonitoringAndLog::create([
            'user_role' => 'Student',
            'user_name' => $student->email_address,
            'activity_type' => 'Update Academic Information',
            'description' => 'Student updated their academic info.',
        ]);

        return response()->json(['success' => true, 'message' => 'Academic information updated successfully.']);
    }

    /**
     * Upload or replace Certificate of Registration.
     */
    public function uploadCOR(Request $request)
    {
        $student = Auth::guard('student')->user();

        $request->validate([
            'cor' => 'required|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $academic = AcademicInformation::where('student_id', $student->student_id)->first();
        $folder = 'cor_uploads/' . $student->student_id;

        // delete old file if exists
        if ($academic && $academic->cor_file && Storage::disk('public')->exists($academic->cor_file)) {
            Storage::disk('public')->delete($academic->cor_file);
        }

        $path = $request->file('cor')->store($folder, 'public');

        if ($academic) {
            $academic->update(['cor_file' => $path]);
        }

        SystemMonitoringAndLog::create([
            'user_role' => 'Student',
            'user_name' => $student->email_address,
            'activity_type' => 'Upload COR',
            'description' => 'Student uploaded a new Certificate of Registration.',
        ]);

        return response()->json(['success' => true, 'message' => 'Certificate of Registration uploaded successfully.']);
    }

    /**
     * Update avatar/profile picture.
     */
    public function updateAvatar(Request $request)
    {
        $student = Auth::guard('student')->user();

        $request->validate([
            'avatar' => 'required|image|max:5120',
        ]);

        $folderPath = 'avatars/student/' . $student->student_id;
        $studentInfo = StudentPersonalInformation::where('email_address', $student->email_address)->first();

        if ($studentInfo && $studentInfo->profile_picture_path && Storage::disk('public')->exists($studentInfo->profile_picture_path)) {
            Storage::disk('public')->delete($studentInfo->profile_picture_path);
        }

        $path = $request->file('avatar')->store($folderPath, 'public');
        $studentInfo->update(['profile_picture_path' => $path]);

        SystemMonitoringAndLog::create([
            'user_role' => 'Student',
            'user_name' => $student->email_address,
            'activity_type' => 'Update Profile Picture',
            'description' => 'Student updated their profile picture.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile picture updated successfully.',
            'avatar_url' => asset('storage/' . $path),
        ]);
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request)
    {
        $student = Auth::guard('student')->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        if (!Hash::check($request->current_password, $student->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect.'], 422);
        }

        $student->update(['password' => Hash::make($request->password)]);

        SystemMonitoringAndLog::create([
            'user_role' => 'Student',
            'user_name' => $student->email_address,
            'activity_type' => 'Change Password',
            'description' => 'Student changed their password.',
        ]);

        return response()->json(['success' => true, 'message' => 'Password changed successfully.']);
    }

    /**
     * Add or update leadership records.
     */
    public function updateLeadership(Request $request)
    {
        $student = Auth::guard('student')->user();

        $validated = $request->validate([
            'leadership_type' => 'required|string|max:100',
            'organization_name' => 'required|string|max:150',
            'position' => 'required|string|max:100',
            'term' => 'nullable|string|max:100',
            'issued_by' => 'nullable|string|max:150',
        ]);

        LeadershipInformation::updateOrCreate(
            [
                'student_id' => $student->student_id,
                'organization_name' => $validated['organization_name'],
            ],
            array_merge($validated, ['student_id' => $student->student_id])
        );

        SystemMonitoringAndLog::create([
            'user_role' => 'Student',
            'user_name' => $student->email_address,
            'activity_type' => 'Update Leadership Information',
            'description' => 'Student updated leadership records.',
        ]);

        return response()->json(['success' => true, 'message' => 'Leadership information updated successfully.']);
    }
}
