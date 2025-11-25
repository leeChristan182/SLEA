<?php

namespace App\Http\Controllers;

use App\Models\AssessorCompiledScore;
use App\Models\AssessorFinalReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $assessor = Auth::user();
        abort_unless($assessor->isAssessor(), 403);

        // Students who requested rating AND were marked ready for admin
        $eligibleStudentIds = StudentAcademic::query()
            ->where('ready_for_rating', true)
            ->where('slea_application_status', 'for_admin_review') // enum key
            ->pluck('user_id');

        // Group compiled scores for those students only
        $compiledByStudent = AssessorCompiledScore::with(['student'])
            ->where('assessor_id', $assessor->id)
            ->whereIn('student_id', $eligibleStudentIds)
            ->orderBy('student_id')
            ->get()
            ->groupBy('student_id');

        // Sync/update AssessorFinalReview rows
        foreach ($compiledByStudent as $studentId => $rows) {
            $totalScore  = $rows->sum('total_score');
            $maxPoints   = $rows->sum('max_points'); // per-category max, usually 20 each

            $existing = AssessorFinalReview::where('assessor_id', $assessor->id)
                ->where('student_id', $studentId)
                ->first();

            $status = $existing?->status ?? 'draft'; // enum from final_review_statuses

            AssessorFinalReview::updateOrCreate(
                [
                    'student_id'  => $studentId,
                    'assessor_id' => $assessor->id,
                ],
                [
                    'total_score' => $totalScore,
                    'max_points'  => $maxPoints,
                    'status'      => $status,
                    'reviewed_at' => $existing?->reviewed_at ?? now(),
                ]
            );
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
        $assessor = Auth::user();
        abort_unless($assessor->isAssessor(), 403);

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

        AssessorFinalReview::updateOrCreate(
            [
                'student_id'  => $studentId,
                'assessor_id' => $assessor->id,
            ],
            [
                'total_score'   => $totalScore,
                'max_points'    => $maxPossible,
                'qualification' => $qualification,        // enum key
                'status'        => 'queued_for_admin',    // enum key
                'remarks'       => $remarks,
                'reviewed_at'   => now(),
            ]
        );

        // Also ensure the student’s SLEA app status is enum-correct
        $academic = \App\Models\StudentAcademic::where('user_id', $studentId)->first();
        if ($academic) {
            $academic->slea_application_status = 'for_admin_review'; // enum key
            $academic->save();
        }

        return back()->with('status', 'Student final review has been submitted to Admin.');
    }
}
