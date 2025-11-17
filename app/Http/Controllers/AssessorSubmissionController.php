<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\SubmissionReview;
use App\Models\AssessorCompiledScore;
use App\Models\Submission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class AssessorSubmissionController extends Controller
{
    // Page with pending list
    public function pending()
    {
        // include user + academic for IDs/names
        $pendingSubmissions = Submission::with(['user', 'user.studentAcademic'])
            ->where('status', 'pending')
            ->orderByDesc('submitted_at')
            ->get();

        return view('assessor.submissions.pending-submissions', compact('pendingSubmissions'));
    }

    // JSON details for modal
    public function details(Submission $submission)
    {
        // load relations, including subsection + its rubric options
        $submission->load([
            'user',
            'user.studentAcademic',
            'category',
            'section',
            'subsection.options',
        ]);

        // ---------- Attachments → documents array ----------
        $attachments = $submission->attachments ?? [];

        $documents = collect($attachments)->map(function ($att, $index) use ($submission) {
            $mime    = $att['mime'] ?? '';
            $isPdf   = str_contains($mime, 'pdf');
            $isImage = str_starts_with($mime, 'image/');

            $docId = $submission->id . ':' . $index;

            return [
                'id'                => $docId,
                'original_filename' => $att['original'] ?? basename($att['path'] ?? ''),
                'file_type'         => $mime ?: 'file',
                'file_size'         => $this->humanFileSize($att['size'] ?? null),
                'is_pdf'            => $isPdf,
                'is_image'          => $isImage,
                'view_url'          => route('assessor.documents.view', ['documentId' => $docId]),
                'download_url'      => route('assessor.documents.download', ['documentId' => $docId]),
            ];
        })->values();

        // ---------- Rubric options (for this subsection) ----------
        $rubricOptions = [];
        if ($submission->subsection) {
            $rubricOptions = $submission->subsection->options
                ->map(function ($opt) {
                    return [
                        'id'     => $opt->id,
                        'label'  => $opt->label,
                        'points' => $opt->points,
                    ];
                })
                ->values();
        }

        // ---------- Student identity ----------
        $studentId = optional($submission->user->studentAcademic)->student_number
            ?? $submission->user->student_id
            ?? $submission->user->id;

        $studentName = $submission->user->full_name
            ?? trim(($submission->user->first_name ?? '') . ' ' . ($submission->user->last_name ?? ''));

        return response()->json([
            'submission' => [
                'id' => $submission->id,
                'student' => [
                    'student_id' => $studentId,
                    'name'       => $studentName,
                ],
                'document_title'       => $submission->activity_title,
                'submitted_at'         => optional($submission->submitted_at)->toIso8601String(),

                'slea_section'         => optional($submission->section)->title,
                'subsection'           => optional($submission->subsection)->sub_section,
                'role_in_activity'     => $submission->meta['role_in_activity'] ?? null,
                'activity_date'        => $submission->meta['date_of_activity'] ?? null,
                'organizing_body'      => $submission->meta['organizing_body'] ?? null,
                'description'          => $submission->description ?? null,
                'auto_generated_score' => $submission->auto_score ?? null,

                'documents'           => $documents,
                'rubric' => [
                    'category'   => optional($submission->category)->title,
                    'section'    => optional($submission->section)->title,
                    'subsection' => optional($submission->subsection)->sub_section,
                    'options'    => $rubricOptions,
                ],
            ],
        ]);
    }

    public function handleAction(Request $request, Submission $submission)
    {
        $user = Auth::user();
        abort_unless($user->isAssessor(), 403);

        $data = $request->validate([
            'action'       => 'required|in:approve,reject,return,flag',
            'remarks'      => 'nullable|string|max:2000',
            'total_points' => 'nullable|numeric', // from rubric option
        ]);

        $action  = $data['action'];
        $remarks = $data['remarks'] ?? null;
        $points  = $data['total_points'] ?? null;

        $decisionMap = [
            'approve' => 'approved',
            'reject'  => 'rejected',
            'return'  => 'returned',
            'flag'    => 'flagged',
        ];
        $newStatus = $decisionMap[$action];

        DB::transaction(function () use ($submission, $user, $newStatus, $remarks, $points) {
            $oldStatus = $submission->status;

            // 1) per-assessor review
            SubmissionReview::updateOrCreate(
                [
                    'submission_id' => $submission->id,
                    'assessor_id'   => $user->id,
                ],
                [
                    'decision'     => $newStatus,
                    'comments'     => $remarks,
                    'total_points' => $points,
                    'reviewed_at'  => now(),
                ]
            );

            // 2) update submission
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
                    ->where('status', 'approved');
            })
            ->get();

        $totalPoints = $reviews->sum('total_points');

        $category = $basis->category;
        $max      = $category->max_points ?? 0;
        $minReq   = $category->min_required_points ?? 0;

        $resultKey = $totalPoints >= $minReq ? 'meets' : 'does_not_meet';

        AssessorCompiledScore::updateOrCreate(
            [
                'student_id'         => $basis->user_id,
                'assessor_id'        => $assessor->id,
                'rubric_category_id' => $categoryId,
            ],
            [
                'total_points'        => $totalPoints,
                'max_points'          => $max,
                'min_required_points' => $minReq,
                'category_result'     => $resultKey,
            ]
        );
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
        $attachments = $submission->attachments ?? [];

        if (!isset($attachments[$index])) {
            abort(404);
        }

        $file = $attachments[$index];

        if (empty($file['path']) || !Storage::disk('public')->exists($file['path'])) {
            abort(404);
        }

        $downloadName = $file['original'] ?? basename($file['path']);

        return Storage::disk('public')->download($file['path'], $downloadName);
    }

    // Inline view (for iframe / image preview)
    public function viewDocument(string $documentId)
    {
        [$submissionId, $index] = explode(':', $documentId . ':');

        $submission  = Submission::findOrFail($submissionId);
        $attachments = $submission->attachments ?? [];

        if (!isset($attachments[$index])) {
            abort(404);
        }

        $file = $attachments[$index];

        if (empty($file['path']) || !Storage::disk('public')->exists($file['path'])) {
            abort(404);
        }

        $path = Storage::disk('public')->path($file['path']);
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
