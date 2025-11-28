<?php

namespace App\Http\Controllers;

use App\Models\RubricCategory;
use App\Models\RubricSection;
use App\Models\RubricSubsection;
use App\Models\User;
use App\Models\RubricOption;
use App\Models\History;
use App\Models\SubmissionReview;
use App\Models\AssessorCompiledScore;
use App\Models\Submission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class AssessorSubmissionController extends Controller
{
    // Page with pending list
    public function pending()
    {
        $pendingSubmissions = Submission::with(['user', 'user.studentAcademic', 'category', 'section', 'subsection'])
            ->where('status', 'pending')
            ->orderByDesc('submitted_at')
            ->get();

        return view('assessor.submissions.pending-submissions', compact('pendingSubmissions'));
    }

    // JSON details for modal
    public function details(Submission $submission)
    {
        $submission->load([
            'user',
            'user.studentAcademic',
            'category',
            'section',
            'subsection.options',
            'reviews.assessor',
        ]);

        // 🔹 Normalize attachments to an array
        $attachments = $submission->attachments ?? [];
        if (is_string($attachments)) {
            $attachments = json_decode($attachments, true) ?: [];
        }

        $documents = collect($attachments)->map(function ($att, $index) use ($submission) {
            $mime    = $att['mime'] ?? ($att['type'] ?? '');
            $isPdf   = $mime && str_contains($mime, 'pdf');
            $isImage = $mime && str_starts_with($mime, 'image/');

            $path = $att['path'] ?? null;
            $documentId = $submission->id . ':' . $index;

            return [
                'id'                => $index,
                'original_filename' => $att['original'] ?? basename($path ?? ''),
                'file_type'         => $mime ?: 'file',
                'file_size'         => $this->humanFileSize($att['size'] ?? null),
                'is_pdf'            => $isPdf,
                'is_image'          => $isImage,
                'view_url'          => route('assessor.submissions.viewDocument', $documentId),
                'download_url'      => route('assessor.submissions.downloadDocument', $documentId),
            ];
        })->values();


        $subsection = $submission->subsection;

        // 🔹 Rubric options (for option-based subsections)
        $rubricOptions = [];
        if ($subsection) {
            $rubricOptions = $subsection->options->map(function ($opt) {
                return [
                    'id'     => $opt->id,
                    'label'  => $opt->label,
                    'points' => $opt->points,
                ];
            })->values();
        }

        // 🔹 Decode score_params for rate-based subsections (trainings, community service, etc.)
        $scoreParams = null;
        if ($subsection && $subsection->score_params) {
            $raw = $subsection->score_params;
            if (is_array($raw)) {
                $scoreParams = $raw;
            } else {
                $decoded = json_decode($raw, true);
                $scoreParams = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
            }
        }

        $studentAcademic = optional($submission->user)->studentAcademic;
        $studentId = optional($submission->user->studentAcademic)->student_number
            ?? $submission->user->student_id
            ?? $submission->user->id;

        $studentName =
            $submission->user->full_name
            ?? trim(($submission->user->first_name ?? '') . ' ' . ($submission->user->last_name ?? ''));

        return response()->json([
            'submission' => [
                'id'     => $submission->id,
                'status' => $submission->status,

                'student' => [
                    'student_id'     => $studentId,
                    'student_number' => $studentId,
                    'name'           => $studentName,
                ],

                'document_title'       => $submission->activity_title,
                'submitted_at'         => optional($submission->submitted_at)->toIso8601String(),

                'slea_section'         => optional($submission->section)->title,
                'subsection'           => optional($subsection)->sub_section,
                'role_in_activity'     => $submission->meta['role_in_activity'] ?? null,
                'activity_date'        => $submission->meta['date_of_activity'] ?? null,
                'organizing_body'      => $submission->meta['organizing_body'] ?? null,
                'description'          => $submission->description ?? null,
                'auto_generated_score' => $submission->auto_score ?? null,

                'documents'            => $documents,

                'rubric' => [
                    'category'       => optional($submission->category)->title,
                    'section'        => optional($submission->section)->title,
                    'subsection'     => optional($subsection)->sub_section,
                    'scoring_method' => optional($subsection)->scoring_method,
                    'cap_points'     => optional($subsection)->cap_points,
                    'score_params'   => $scoreParams,   // 👈 this is the key for rate-based scoring
                    'options'        => $rubricOptions,
                ],
            ],
        ]);
    }

    public function handleAction(Request $request, Submission $submission)
    {
        $user = Auth::user();
        abort_unless($user->isAssessor(), 403);

        $data = $request->validate([
            'action'         => 'required|in:approve,reject,return,flag',
            'remarks'        => 'nullable|string|max:2000',
            'total_points'   => 'nullable|numeric', // new name
            'assessor_score' => 'nullable|numeric', // old name from JS (if any)
            'rubric_option_id' => 'nullable|integer', // optional, if you ever want to trust JS id
        ]);

        $action  = $data['action'];
        $remarks = $data['remarks'] ?? null;

        // Prefer total_points, but fall back to assessor_score
        $score = $data['total_points'] ?? $data['assessor_score'] ?? null;

        // Optional: final fallback to auto_score if still null
        if ($score === null && $submission->auto_score !== null) {
            $score = $submission->auto_score;
        }

        // Map assessor actions → submission_statuses keys
        // (aligned with your enum migration: pending, under_review, resubmit, flagged, qualified, unqualified, approved, rejected)
        $statusMap = [
            'approve' => 'approved',
            'reject'  => 'rejected',
            'return'  => 'resubmit',
            'flag'    => 'flagged',
        ];
        $newStatus = $statusMap[$action];

        DB::transaction(function () use ($submission, $user, $newStatus, $remarks, $score, $data) {
            $oldStatus = $submission->status;

            // 🔹 Always store which rubric_subsection this review belongs to
            $rubricSubsectionId = $submission->rubric_subsection_id;

            // 🔹 Try to resolve the specific rubric option (for option-based items)
            $rubricOptionId = $data['rubric_option_id'] ?? null;

            // If no rubric_option_id was provided but we have a score, try best-effort lookup by points
            if (!$rubricOptionId && $rubricSubsectionId && $score !== null) {
                $rubricOption = RubricOption::where('sub_section_id', $rubricSubsectionId)
                    ->where('points', $score)
                    ->first();

                if ($rubricOption) {
                    $rubricOptionId = $rubricOption->id;
                }
            }

            // 1) per-assessor review
            SubmissionReview::updateOrCreate(
                [
                    'submission_id' => $submission->id,
                    'assessor_id'   => $user->id,
                ],
                [
                    'rubric_category_id' => $submission->rubric_category_id,

                    'sub_section_id'    => $rubricSubsectionId,
                    'rubric_option_id'  => $rubricOptionId,
                    'score'             => $score ?? 0,
                    'score_source'      => 'auto', // or 'manual' if you later support overrides
                    'reviewed_at'       => now(),

                    // Mirror workflow + remarks
                    'status'  => $newStatus,
                    'remarks' => $remarks,

                    // Keep legacy fields in sync if you want
                    'comments' => $remarks,
                    'decision' => null, // map later to category_results if you need
                ]
            );

            // 2) update submission workflow status
            $submission->status = $newStatus;
            if (!empty($remarks)) {
                $submission->remarks = $remarks;
            }
            $submission->save();

            // 3) history log
            History::create([
                'submission_id' => $submission->id,
                'changed_by'    => $user->id,
                'old_status'    => $oldStatus,
                'new_status'    => $newStatus,
                'remarks'       => $remarks,
            ]);

            // 4) recompute compiled score if approved
            if ($newStatus === 'approved') {
                $this->recomputeCompiledScoreForStudentCategory($submission, $user);

                // 5) Update SLEA application status based on application_status
                $this->updateSleaStatusBasedOnSubmission($submission);
            }
        });

        return response()->json([
            'message' => "Submission {$newStatus}.",
            'status'  => $newStatus,
        ]);
    }

    protected function recomputeCompiledScoreForStudentCategory(Submission $basis, $assessor): void
    {
        $categoryId = $basis->rubric_category_id;

        $reviews = SubmissionReview::where('assessor_id', $assessor->id)
            ->whereHas('submission', function ($q) use ($basis, $categoryId) {
                $q->where('user_id', $basis->user_id)
                    ->where('rubric_category_id', $categoryId)
                    ->where('status', 'approved'); // 👈 aligned with new status
            })
            ->get();

        $rawTotal = $reviews->sum('score');

        $category = $basis->category;
        $max      = $category->max_points ?? 20;     // 20 for most, 10 for conduct
        $minReq   = $category->min_required_points ?? 0;

        $totalScore = min($rawTotal, $max);

        AssessorCompiledScore::updateOrCreate(
            [
                'student_id'         => $basis->user_id,
                'assessor_id'        => $assessor->id,
                'rubric_category_id' => $categoryId,
            ],
            [
                'total_score'         => $totalScore,
                'max_points'          => $max,
                'min_required_points' => $minReq,
                // 'category_result'     => $resultKey, // keep commented for now
            ]
        );
    }

    /**
     * Update SLEA application status based on submission's application_status
     */
    protected function updateSleaStatusBasedOnSubmission(Submission $submission): void
    {
        $student = $submission->user;
        if (!$student) {
            return;
        }

        $academic = $student->studentAcademic;
        if (!$academic) {
            return;
        }

        // Only update if submission is for final application
        if ($submission->application_status === 'for_final_application') {
            // Check if student has any approved submissions for final application
            $hasApprovedFinalSubmissions = Submission::where('user_id', $student->id)
                ->where('application_status', 'for_final_application')
                ->where('status', 'approved')
                ->exists();

            if ($hasApprovedFinalSubmissions) {
                // If student has approved final application submissions, set to pending assessor evaluation
                // (This will be updated to pending_administrative_validation when assessor submits final review)
                if (!$academic->slea_application_status || $academic->slea_application_status === 'incomplete') {
                    $academic->slea_application_status = 'pending_assessor_evaluation';
                    $academic->save();
                }
            }
        }
        // If application_status is 'for_tracking', don't change SLEA status (keep as pending/incomplete)
    }

    // legacy shorthand, still used by JS but now delegates to handleAction
    public function action(Request $request, Submission $submission)
    {
        return $this->handleAction($request, $submission);
    }

    // Download one attachment from a submission
    public function downloadDocument(string $documentId)
    {
        [$submissionId, $index] = explode(':', $documentId . ':');

        $submission  = Submission::findOrFail($submissionId);

        // 🔒 very important: access control
        $user = Auth::user();
        if ($user->isStudent() && $submission->user_id !== $user->id) {
            abort(403);
        }
        // assessors/admins are already protected by route middleware

        $attachments = $submission->attachments ?? [];

        if (!isset($attachments[$index])) {
            abort(404);
        }

        $file = $attachments[$index];

        if (empty($file['path']) || !Storage::disk('student_docs')->exists($file['path'])) {
            abort(404);
        }

        $downloadName = $file['original'] ?? basename($file['path']);

        return Storage::disk('student_docs')->download($file['path'], $downloadName);
    }


    // Inline view (for iframe / image preview)
    public function viewDocument(string $documentId)
    {
        [$submissionId, $index] = explode(':', $documentId . ':');

        $submission  = Submission::findOrFail($submissionId);
        $attachments = $submission->attachments ?? [];

        $user = Auth::user();
        if ($user->isStudent() && $submission->user_id !== $user->id) {
            abort(403);
        }


        $file = $attachments[$index];
        if (empty($file['path']) || !Storage::disk('student_docs')->exists($file['path'])) {
            abort(404);
        }

        $path = Storage::disk('student_docs')->path($file['path']);
        $mime = $file['mime'] ?? null;

        return response()->file($path, [
            'Content-Type' => $mime ?: null,
        ]);
    }

    // helper
    protected function humanFileSize($bytes, int $decimals = 2): string
    {
        if (!$bytes || $bytes <= 0) {
            return '0 B';
        }
        $size   = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f %s", $bytes / pow(1024, $factor), $size[$factor]);
    }
}
