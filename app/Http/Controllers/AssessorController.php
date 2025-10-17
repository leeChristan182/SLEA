<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Submission;
use App\Models\Student;
use App\Models\Document;

class AssessorController extends Controller
{
    /**
     * Display the assessor profile page.
     */
    public function profile()
    {
        return view('assessor.profile');
    }

    /**
     * Update the assessor's profile information.
     */
    public function updateProfile(Request $request)
    {
        // This will be implemented by your teammate with proper authentication
        return redirect()->back()->with('error', 'Profile update not implemented yet.');
    }

    /**
     * Update the assessor's password.
     */
    public function updatePassword(Request $request)
    {
        // This will be implemented by your teammate with proper authentication
        return redirect()->back()->with('error', 'Password update not implemented yet.');
    }

    /**
     * Upload and update profile picture.
     */
    public function updateProfilePicture(Request $request)
    {
        // This will be implemented by your teammate with proper authentication
        return redirect()->back()->with('error', 'Profile picture update not implemented yet.');
    }

    /**
     * Display the assessor dashboard.
     */
    public function dashboard()
    {
        // Add any dashboard-specific data here
        return view('assessor.dashboard');
    }


    /**
     * Display pending submissions.
     */
    public function pendingSubmissions()
    {
        // Fetch all pending submissions (no user filtering for now)
        $pendingSubmissions = Submission::with(['student', 'documents'])
            ->pending()
            ->orderBy('submitted_at', 'desc')
            ->get();
            
        return view('assessor.pending-submissions', compact('pendingSubmissions'));
    }

    /**
     * Display completed submissions.
     */
    public function submissions()
    {
        // Add logic to fetch completed submissions
        return view('assessor.submissions');
    }

    /**
     * Display final review page.
     */
    public function finalReview()
    {
        // Add logic for final review
        return view('assessor.final-review');
    }

    /**
     * Get submission details for review modal.
     */
    public function getSubmissionDetails($id)
    {
        $submission = Submission::with(['student', 'documents'])
            ->where('id', $id)
            ->first();
            
        if (!$submission) {
            return response()->json(['error' => 'Submission not found'], 404);
        }
        
        // Calculate auto-generated score if not already calculated
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
                'activity_date' => $submission->activity_date?->format('Y-m-d'),
                'organizing_body' => $submission->organizing_body,
                'description' => $submission->description,
                'submitted_at' => $submission->submitted_at->format('Y-m-d H:i:s'),
                'auto_generated_score' => $submission->auto_generated_score,
                'assessor_score' => $submission->assessor_score,
                'documents' => $submission->documents->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'original_filename' => $doc->original_filename,
                        'file_type' => $doc->file_type,
                        'file_size' => $doc->formatted_size,
                        'url' => $doc->url,
                        'is_image' => $doc->isImage(),
                        'is_pdf' => $doc->isPdf(),
                    ];
                }),
            ]
        ]);
    }

    /**
     * Handle assessor action on submission.
     */
    public function handleSubmissionAction(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject,return,flag',
            'remarks' => 'nullable|string|max:1000',
            'assessor_score' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $submission = Submission::where('id', $id)
            ->first();
            
        if (!$submission) {
            return response()->json(['error' => 'Submission not found'], 404);
        }

        $action = $request->input('action');
        $remarks = $request->input('remarks', '');
        
        // Validate remarks for actions that require them
        if (in_array($action, ['reject', 'return', 'flag']) && empty($remarks)) {
            return response()->json([
                'error' => 'Remarks are required for ' . $action . ' action'
            ], 422);
        }

        // Update submission based on action
        switch ($action) {
            case 'approve':
                $submission->update([
                    'status' => 'approved',
                    'assessor_score' => $request->input('assessor_score', $submission->auto_generated_score),
                    'assessor_remarks' => $remarks,
                    'reviewed_at' => now(),
                ]);
                break;
                
            case 'reject':
                $submission->update([
                    'status' => 'rejected',
                    'assessor_remarks' => $remarks,
                    'rejection_reason' => $remarks,
                    'reviewed_at' => now(),
                ]);
                break;
                
            case 'return':
                $submission->update([
                    'status' => 'returned',
                    'assessor_remarks' => $remarks,
                    'return_reason' => $remarks,
                    'reviewed_at' => now(),
                ]);
                break;
                
            case 'flag':
                $submission->update([
                    'status' => 'flagged',
                    'assessor_remarks' => $remarks,
                    'flag_reason' => $remarks,
                    'reviewed_at' => now(),
                ]);
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Submission ' . $action . 'd successfully',
            'submission' => [
                'id' => $submission->id,
                'status' => $submission->status,
            ]
        ]);
    }

    /**
     * Download document file.
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
     * Calculate auto-generated score based on submission criteria.
     */
    private function calculateAutoScore($submission)
    {
        $baseScore = 70; // Base score
        $bonusPoints = 0;
        
        // Add points based on role in activity
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
        
        // Add points based on SLEA section
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
        
        // Add points if organizing body is specified
        if ($submission->organizing_body) {
            $bonusPoints += 3;
        }
        
        // Add points if description is provided
        if ($submission->description && strlen($submission->description) > 50) {
            $bonusPoints += 5;
        }
        
        $finalScore = min(100, $baseScore + $bonusPoints);
        
        return round($finalScore, 2);
    }
}
