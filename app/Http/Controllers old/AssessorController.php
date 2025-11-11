<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\AssessorAccount;
use App\Models\AssessorProfile;
use App\Models\Submission;
use App\Models\Document;
use App\Models\SystemMonitoringAndLog;

class AssessorController extends Controller
{
    /**
     * Display the assessor profile page.
     */
    public function profile()
    {
        $account = Auth::user();

        // Load or create profile
        $assessor = AssessorProfile::with('account')
            ->where('email_address', $account->email_address)
            ->first();

        if (!$assessor) {
            $assessor = AssessorProfile::create([
                'assessor_id'   => $account->assessor_id ?? uniqid('ASSR-'),
                'email_address' => $account->email_address,
                'picture_path'  => null,
                'date_upload'   => now(),
            ]);
        }

        // Attach the account relation manually for the Blade view
        $assessor->setRelation('account', $account);

        return view('assessor.profile', compact('assessor'));
    }

    /**
     * Update the assessor's profile information.
     */
    public function updateProfile(Request $request)
    {
        try {
            $account = Auth::user();

            $validated = $request->validate([
                'first_name'     => 'required|string|max:50',
                'last_name'      => 'required|string|max:50',
                'position'       => 'required|string|max:50',
                'contact_number' => 'nullable|string|max:20',
            ]);

            $account->update($validated);

            // ✅ Refresh session user to show updated data
            Auth::setUser($account->fresh());

            SystemMonitoringAndLog::create([
                'user_role' => 'Assessor',
                'user_name' => $account->email_address,
                'activity_type' => 'Update Profile',
                'description' => 'Assessor updated their profile information.',
            ]);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Profile updated successfully.']);
            }

            return redirect()->back()->with('success', 'Profile updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Assessor profile update failed: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Error saving profile.'], 500);
            }
            return redirect()->back()->withErrors(['error' => 'Error saving profile.']);
        }
    }
    /**
     * Upload and update the assessor's profile picture (auto-delete old).
     */
    public function updateAvatar(Request $request)
    {
        $assessorAccount = auth()->user(); // logged-in assessor

        $request->validate([
            'avatar' => 'required|image|max:5120', // 5MB max
        ]);

        // Define storage folder for assessors
        $folderPath = 'avatars/assessor';

        // Find existing profile
        $assessorProfile = \App\Models\AssessorProfile::where('email_address', $assessorAccount->email_address)->first();

        // Store the new image first
        $path = $request->file('avatar')->store($folderPath, 'public');

        // Delete the old image safely after successful upload
        if ($assessorProfile && $assessorProfile->picture_path && Storage::disk('public')->exists($assessorProfile->picture_path)) {
            Storage::disk('public')->delete($assessorProfile->picture_path);
        }

        // Create or update profile record
        if (!$assessorProfile) {
            $assessorProfile = \App\Models\AssessorProfile::create([
                'assessor_id'   => $assessorAccount->assessor_id ?? uniqid('ASSR-'),
                'email_address' => $assessorAccount->email_address,
                'picture_path'  => $path,
                'date_upload'   => now(),
            ]);
        } else {
            $assessorProfile->update([
                'picture_path' => $path,
                'date_upload'  => now(),
            ]);
        }

        // Log the activity
        \App\Models\SystemMonitoringAndLog::create([
            'user_role' => 'Assessor',
            'user_name' => $assessorAccount->email_address,
            'activity_type' => 'Update Profile Picture',
            'description' => 'Assessor updated their profile picture.',
        ]);

        return response()->json([
            'success' => true,
            'avatar_url' => asset('storage/' . $path),
        ]);
    }

    /**
     * Update the assessor's password.
     */
    public function updatePassword(Request $request)
    {
        $account = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $account->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $account->update(['password' => Hash::make($request->new_password)]);

        SystemMonitoringAndLog::create([
            'user_role' => 'Assessor',
            'user_name' => $account->email_address,
            'activity_type' => 'Change Password',
            'description' => 'Assessor updated their account password.',
        ]);

        return redirect()->back()->with('success', 'Password updated successfully.');
    }

    /**
     * Display pending submissions.
     */
    public function pendingSubmissions()
    {
        $pendingSubmissions = Submission::with(['student', 'documents'])
            ->where('status', 'pending')
            ->orderBy('submitted_at', 'desc')
            ->get();

        return view('assessor.pending-submissions', compact('pendingSubmissions'));
    }

    public function submissions()
    {
        return view('assessor.submissions');
    }

    public function finalReview()
    {
        return view('assessor.final-review');
    }

    /**
     * Get submission details.
     */
    public function getSubmissionDetails($id)
    {
        $submission = Submission::with(['student', 'documents'])->find($id);

        if (!$submission) {
            return response()->json(['error' => 'Submission not found'], 404);
        }

        if (!$submission->auto_generated_score) {
            $submission->auto_generated_score = $this->calculateAutoScore($submission);
            $submission->save();
        }

        return response()->json([
            'submission' => [
                'id' => $submission->id,
                'student' => [
                    'id' => $submission->student->student_id,
                    'name' => $submission->student->name,
                ],
                'document_title' => $submission->document_title,
                'slea_section' => $submission->slea_section,
                'subsection' => $submission->subsection,
                'role_in_activity' => $submission->role_in_activity,
                'activity_date' => optional($submission->activity_date)->format('Y-m-d'),
                'organizing_body' => $submission->organizing_body,
                'description' => $submission->description,
                'submitted_at' => $submission->submitted_at->format('Y-m-d H:i:s'),
                'auto_generated_score' => $submission->auto_generated_score,
                'assessor_score' => $submission->assessor_score,
                'documents' => $submission->documents->map(fn($doc) => [
                    'id' => $doc->id,
                    'original_filename' => $doc->original_filename,
                    'file_type' => $doc->file_type,
                    'file_size' => $doc->formatted_size,
                    'url' => $doc->url,
                    'is_image' => $doc->isImage(),
                    'is_pdf' => $doc->isPdf(),
                ]),
            ],
        ]);
    }

    /**
     * Handle submission action.
     */
    public function handleSubmissionAction(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject,return,flag',
            'remarks' => 'nullable|string|max:1000',
            'assessor_score' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails())
            return response()->json(['errors' => $validator->errors()], 422);

        $submission = Submission::find($id);
        if (!$submission)
            return response()->json(['error' => 'Submission not found'], 404);

        $action = $request->input('action');
        $remarks = $request->input('remarks', '');

        if (in_array($action, ['reject', 'return', 'flag']) && empty($remarks)) {
            return response()->json(['error' => 'Remarks are required for ' . $action], 422);
        }

        $updateData = [
            'assessor_remarks' => $remarks,
            'reviewed_at' => now(),
        ];

        switch ($action) {
            case 'approve':
                $updateData['status'] = 'approved';
                $updateData['assessor_score'] = $request->input('assessor_score', $submission->auto_generated_score);
                break;
            case 'reject':
                $updateData['status'] = 'rejected';
                $updateData['rejection_reason'] = $remarks;
                break;
            case 'return':
                $updateData['status'] = 'returned';
                $updateData['return_reason'] = $remarks;
                break;
            case 'flag':
                $updateData['status'] = 'flagged';
                $updateData['flag_reason'] = $remarks;
                break;
        }

        $submission->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Submission ' . $action . 'd successfully',
            'submission' => ['id' => $submission->id, 'status' => $submission->status],
        ]);
    }

    /**
     * Download a document file.
     */
    public function downloadDocument($id)
    {
        $document = Document::findOrFail($id);

        if (!Storage::exists($document->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::download($document->file_path, $document->original_filename);
    }

    /**
     * Calculate auto-generated score.
     */
    private function calculateAutoScore($submission)
    {
        $baseScore = 70;
        $bonusPoints = 0;

        switch (strtolower($submission->role_in_activity ?? '')) {
            case 'president':
            case 'chair':
            case 'director':
                $bonusPoints += 15;
                break;
            case 'vice president':
            case 'vice chair':
                $bonusPoints += 12;
                break;
            case 'secretary':
            case 'treasurer':
                $bonusPoints += 10;
                break;
            case 'coordinator':
            case 'organizer':
                $bonusPoints += 8;
                break;
            case 'member':
            case 'participant':
                $bonusPoints += 5;
                break;
        }

        switch ($submission->slea_section) {
            case 'Leadership Excellence':
                $bonusPoints += 10;
                break;
            case 'Academic Excellence':
                $bonusPoints += 8;
                break;
            case 'Community Engagement':
                $bonusPoints += 7;
                break;
            case 'Innovation & Creativity':
                $bonusPoints += 6;
                break;
        }

        if ($submission->organizing_body) $bonusPoints += 3;
        if ($submission->description && strlen($submission->description) > 50) $bonusPoints += 5;

        return round(min(100, $baseScore + $bonusPoints), 2);
    }
}
