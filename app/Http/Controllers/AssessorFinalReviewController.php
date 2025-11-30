<?php

namespace App\Http\Controllers;

use App\Models\AssessorCompiledScore;
use App\Models\AssessorFinalReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\StudentAcademic;
use App\Models\User;
use App\Models\Submission;
use App\Models\SubmissionReview;

class AssessorFinalReviewController extends Controller
{
    /**
     * GET /assessor/final-review
     * Auto-sync rows from compiled scores, status = draft by default.
     */
    public function index()
    {
        /** @var User $assessor */
        $assessor = Auth::user();
        abort_unless($assessor && $assessor->isAssessor(), 403);

        // Students who requested rating AND were marked ready for admin
        $eligibleStudentIds = StudentAcademic::query()
            ->where('ready_for_rating', true)
            ->where('slea_application_status', 'pending_administrative_validation') // enum key
            ->pluck('user_id');

        // Group compiled scores for those students only
        // Group compiled scores for those students only
        $compiledByStudent = AssessorCompiledScore::with(['student'])
            ->where('assessor_id', $assessor->id)
            ->whereIn('student_id', $eligibleStudentIds)
            ->orderBy('student_id')
            ->get()
            ->groupBy('student_id');

        // Simple caches to avoid N+1 queries
        $studentExistsCache = [];
        $statusExistsCache  = [];

        // Verify assessor exists once
        if (!$assessor || !$assessor->exists || !User::where('id', $assessor->id)->exists()) {
            Log::warning("Assessor ID {$assessor->id} does not exist in users table, aborting assessor_final_reviews sync");
            return view('assessor.final-review', [
                // ... existing view data ...
            ]);
        }

        // Sync/update AssessorFinalReview rows
        foreach ($compiledByStudent as $studentId => $rows) {
            // ✅ Cache student existence
            if (!array_key_exists($studentId, $studentExistsCache)) {
                $studentExistsCache[$studentId] = User::where('id', $studentId)->exists();
            }

            if (!$studentExistsCache[$studentId]) {
                Log::warning("Student ID {$studentId} does not exist in users table, skipping assessor_final_reviews sync");
                continue;
            }

            $totalScore  = $rows->sum('total_score');
            $maxPoints   = $rows->sum('max_points'); // per-category max, usually 20 each

            $existing = AssessorFinalReview::where('assessor_id', $assessor->id)
                ->where('student_id', $studentId)
                ->first();

            $status = $existing?->status ?? 'draft'; // enum from final_review_statuses

            // ✅ Cache status existence
            if (!isset($statusExistsCache[$status])) {
                $statusExistsCache[$status] = DB::table('final_review_statuses')
                    ->where('key', $status)
                    ->exists();
            }

            if (!$statusExistsCache[$status]) {
                Log::warning("Invalid status '{$status}' for assessor_final_reviews, using 'draft'");
                $status = 'draft';
                if (
                    !isset($statusExistsCache['draft']) ||
                    $statusExistsCache['draft'] === false
                ) {
                    if (!DB::table('final_review_statuses')->where('key', 'draft')->exists()) {
                        DB::table('final_review_statuses')->insert(['key' => 'draft']);
                    }
                    $statusExistsCache['draft'] = true;
                }
            }
            // Build update data, excluding qualification if it's null to avoid FK constraint issues
            $updateData = [
                'total_score' => $totalScore,
                'max_possible'  => $maxPoints,
                'status'      => $status,
                'reviewed_at' => $existing?->reviewed_at ?? now(),
            ];

            // Only set qualification if it exists and is not null
            if ($existing && $existing->qualification) {
                // Verify qualification exists in enum table
                $qualExists = DB::table('qualifications')->where('key', $existing->qualification)->exists();
                if ($qualExists) {
                    $updateData['qualification'] = $existing->qualification;
                }
            }

            try {
                AssessorFinalReview::updateOrCreate(
                    [
                        'student_id'  => $studentId,
                        'assessor_id' => $assessor->id,
                    ],
                    $updateData
                );
            } catch (\Exception $e) {
                Log::error("Failed to create/update assessor_final_reviews for student {$studentId} and assessor {$assessor->id}: " . $e->getMessage());
                // Continue with next student instead of breaking
                continue;
            }
        }

        // Load items for the table + modal
        $items = AssessorFinalReview::with([
            'student.studentAcademic.program.college',
            'student.studentAcademic.major',
            'compiledScores.category',
        ])
            ->where('assessor_id', $assessor->id)
            ->whereIn('student_id', $eligibleStudentIds)
            ->orderBy('student_id')
            ->get();

        return view('assessor.submissions.final-review', compact('items'));
    }

    /**
     * POST /assessor/final-review/{student}
     * Submit final review to admin (no flag).
     */
    public function storeForStudent(int $studentId, Request $request)
    {
        /** @var User $assessor */
        $assessor = Auth::user();
        abort_unless($assessor && $assessor->isAssessor(), 403);

        $remarks = $request->input('remarks');

        $scores = AssessorCompiledScore::where('assessor_id', $assessor->id)
            ->where('student_id', $studentId)
            ->get();

        if ($scores->isEmpty()) {
            return back()->with('error', 'No compiled scores found for this student.');
        }

        $totalScore  = $scores->sum('total_score');
        $maxPossible = $scores->sum('max_points'); // (e.g. 4 × 20 = 80, conduct handled as deduction)

        // 75% threshold; uses `qualifications` enum keys
        $thresholdScore = $maxPossible * 0.75;
        $qualification  = $maxPossible > 0 && $totalScore >= $thresholdScore
            ? 'qualified'
            : 'unqualified';

        // Verify qualification exists in enum table
        $qualificationExists = DB::table('qualifications')->where('key', $qualification)->exists();
        if (!$qualificationExists) {
            Log::warning("Invalid qualification '{$qualification}' for assessor_final_reviews");
            $qualification = null; // Set to null if invalid
        }

        // Verify status exists in enum table, create if missing
        $status = 'queued_for_admin';
        $statusExists = DB::table('final_review_statuses')->where('key', $status)->exists();
        if (!$statusExists) {
            Log::warning("Status '{$status}' not found in final_review_statuses, creating it");
            try {
                DB::table('final_review_statuses')->insert(['key' => $status]);
            } catch (\Exception $e) {
                Log::error("Failed to create status '{$status}': " . $e->getMessage());
                // Don't fall back to draft - this is critical for admin visibility
                return back()->with('error', 'Failed to submit review. Please contact system administrator.');
            }
        }

        // Build update data
        $updateData = [
            'total_score'   => $totalScore,
            'max_possible'    => $maxPossible,
            'status'        => $status,
            'remarks'       => $remarks,
            'reviewed_at'   => now(),
        ];

        // Only set qualification if it's valid
        if ($qualification) {
            $updateData['qualification'] = $qualification;
        }

        AssessorFinalReview::updateOrCreate(
            [
                'student_id'  => $studentId,
                'assessor_id' => $assessor->id,
            ],
            $updateData
        );

        // Also ensure the student’s SLEA app status is enum-correct
        $academic = \App\Models\StudentAcademic::where('user_id', $studentId)->first();
        if ($academic) {
            $academic->slea_application_status = 'pending_administrative_validation'; // enum key
            $academic->save();
        }

        return back()->with('status', 'Student final review has been submitted to Admin.');
    }
    public function rejectForStudent(Request $request, User $student)
    {
        /** @var User $assessor */
        $assessor = Auth::user();
        abort_unless($assessor && $assessor->isAssessor(), 403);

        // Optional remarks from modal textarea
        $remarks = $request->input('remarks');

        // We still use the compiled scores so the record is consistent
        $scores = AssessorCompiledScore::where('assessor_id', $assessor->id)
            ->where('student_id', $student->id)
            ->get();

        if ($scores->isEmpty()) {
            return back()->with('error', 'No compiled scores found for this student.');
        }

        $totalScore  = $scores->sum('total_score');
        $maxPossible = $scores->sum('max_points'); // conduct handled separately as deduction

        // We’ll mark the final review as finalized + unqualified,
        // and the SLEA application as not_qualified so the student
        // drops out of the pending_administrative_validation list.
        \DB::transaction(function () use ($assessor, $student, $totalScore, $maxPossible, $remarks) {
            AssessorFinalReview::updateOrCreate(
                [
                    'student_id'  => $student->id,
                    'assessor_id' => $assessor->id,
                ],
                [
                    'total_score'   => $totalScore,
                    'max_possible'  => $maxPossible,
                    'status'        => 'finalized',   // from final_review_statuses enum
                    'qualification' => 'unqualified', // from qualifications enum
                    'remarks'       => $remarks,
                    'reviewed_at'   => now(),
                ]
            );

            $academic = \App\Models\StudentAcademic::where('user_id', $student->id)->first();
            if ($academic) {
                // from slea_application_statuses enum
                $academic->slea_application_status = 'not_qualified';
                $academic->save();
            }
        });

        return back()->with('status', 'Student has been rejected and marked as not qualified.');
    }
}
