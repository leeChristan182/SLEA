<?php

namespace App\Http\Controllers;

use App\Models\AssessorCompiledScore;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssessorStudentSubmissionController extends Controller
{
    /**
     * Show all students who have approved / rejected submissions
     * reviewed by the current assessor, grouped by student.
     *
     * GET /assessor/students/submissions
     */
    public function index()
    {
        $assessor = Auth::user();
        abort_unless($assessor && $assessor->isAssessor(), 403);

        // IMPORTANT:
        //  - We ONLY filter by submission status + reviews by this assessor.
        //  - We DO NOT filter by ready_for_rating or slea_application_status here,
        //    so a student marking "ready to be rated" from Performance will NOT disappear.
        $submissions = Submission::with([
            'user',
            'user.studentAcademic.program.college',
            'reviews' => function ($q) use ($assessor) {
                $q->where('assessor_id', $assessor->id);
            },
        ])
            ->whereHas('reviews', function ($q) use ($assessor) {
                $q->where('assessor_id', $assessor->id);
            })
            ->whereIn('status', [
                'accepted',
                'rejected',
                // add these if you also want them to appear:
                // 'returned',
                // 'flagged',
            ])
            ->get();

        // Group by student (user_id)
        $grouped = $submissions->groupBy('user_id');

        $students = $grouped->map(function ($subs) {
            /** @var \App\Models\Submission $first */
            $first = $subs->first();
            $user  = $first->user;
            $acad  = $user->studentAcademic;

            // Student number / ID
            $studentId = $acad->student_number
                ?? $user->student_id
                ?? $user->id;

            // Program name
            if ($acad && $acad->program) {
                $programName = $acad->program->name;
            } else {
                $programName = $acad->program_name ?? $acad->program ?? '—';
            }

            // College name
            if ($acad && $acad->program && $acad->program->college) {
                $collegeName = $acad->program->college->name;
            } else {
                $collegeName = $acad->college_name ?? $acad->college ?? '—';
            }

            // Latest review date across this student's submissions (by this assessor)
            $latestReviewedAt = $subs
                ->flatMap(function ($sub) {
                    return $sub->reviews;
                })
                ->filter()
                ->max('reviewed_at');

            return (object) [
                'id'                 => $user->id,
                'student_id'         => $studentId,
                'user'               => $user,
                'program'            => $programName,
                'college'            => $collegeName,
                'submissions'        => $subs,
                'latest_reviewed_at' => $latestReviewedAt,
            ];
        })
            ->values();

        return view('assessor.submissions.submissions', compact('students'));
    }

    /**
     * JSON details for one student:
     *  - categorized submissions
     *  - per-category totals
     *  - overall total score
     *
     * GET /assessor/students/{student}/details
     */
    public function studentDetails($studentId)
    {
        $assessor = Auth::user();
        abort_unless($assessor && $assessor->isAssessor(), 403);

        $student = User::with('studentAcademic.program.college')->findOrFail($studentId);

        // All finalized submissions for this student that this assessor reviewed
        $submissions = Submission::with([
            'category',
            'subsection',
            'reviews' => function ($q) use ($assessor) {
                $q->where('assessor_id', $assessor->id)->latest();
            },
            'reviews.assessor',
        ])
            ->where('user_id', $studentId)
            ->whereIn('status', ['accepted', 'rejected'])
            ->whereHas('reviews', function ($q) use ($assessor) {
                $q->where('assessor_id', $assessor->id);
            })
            ->get();

        $categorizedSubmissions = [];
        $categoryTotals         = [];
        $overallTotalScore      = 0.0;

        foreach ($submissions as $sub) {
            $sectionTitle = optional($sub->category)->title ?? 'Uncategorized';
            $latestReview = $sub->reviews->first();
            $score        = $latestReview ? (float) $latestReview->score : 0.0;

            if (!isset($categorizedSubmissions[$sectionTitle])) {
                $categorizedSubmissions[$sectionTitle] = [];
            }

            $categorizedSubmissions[$sectionTitle][] = [
                'id'               => $sub->id,
                'document_title'   => $sub->activity_title,
                'subsection'       => optional($sub->subsection)->sub_section,
                'role_in_activity' => $sub->role_in_activity ?? ($sub->meta['role_in_activity'] ?? null),
                'reviewed_at'      => optional($latestReview?->reviewed_at)?->toIso8601String(),
                'assessor'         => $latestReview && $latestReview->assessor
                    ? [
                        'id'   => $latestReview->assessor->id,
                        'name' => $latestReview->assessor->full_name ?? $latestReview->assessor->name,
                    ]
                    : null,
                'status'           => $sub->status,
                'assessor_score'   => $score,
            ];

            if (!isset($categoryTotals[$sectionTitle])) {
                $categoryTotals[$sectionTitle] = [
                    'score'     => 0.0,
                    'max_score' => 0.0,
                ];
            }

            $categoryTotals[$sectionTitle]['score'] += $score;
            $overallTotalScore                       += $score;
        }

        // Overlay MAX points per category from assessor_compiled_scores
        $compiledScores = AssessorCompiledScore::where('assessor_id', $assessor->id)
            ->where('student_id', $studentId)
            ->with('category')
            ->get();

        foreach ($compiledScores as $row) {
            $sectionName = optional($row->category)->title ?? 'Uncategorized';
            $max         = (float) $row->max_points;

            if (!isset($categoryTotals[$sectionName])) {
                $categoryTotals[$sectionName] = [
                    'score'     => 0.0,
                    'max_score' => $max,
                ];
            } else {
                $categoryTotals[$sectionName]['max_score'] = $max;
            }
        }

        $acad = $student->studentAcademic;

        if ($acad && $acad->program) {
            $programName = $acad->program->name;
        } else {
            $programName = $acad->program_name ?? $acad->program ?? '—';
        }

        if ($acad && $acad->program && $acad->program->college) {
            $collegeName = $acad->program->college->name;
        } else {
            $collegeName = $acad->college_name ?? $acad->college ?? '—';
        }

        return response()->json([
            'student' => [
                'id'         => $student->id,
                'student_id' => $acad->student_number
                    ?? $student->student_id
                    ?? $student->id,
                'user' => [
                    'name'  => $student->full_name ?? $student->name,
                    'email' => $student->email,
                ],
                'program' => $programName,
                'college' => $collegeName,
            ],
            'submissions'         => $categorizedSubmissions,
            'category_totals'     => $categoryTotals,
            'overall_total_score' => $overallTotalScore,
        ]);
    }

    /**
     * Assessor marks a student as ready / not ready for SLEA rating.
     *
     * POST /assessor/students/{student}/ready-status
     */
    public function updateReadyStatus(int $studentId, Request $request)
    {
        $assessor = Auth::user();
        abort_unless($assessor && $assessor->isAssessor(), 403);

        $data = $request->validate([
            'ready' => ['required', 'boolean'],
        ]);

        $student  = User::with('studentAcademic')->findOrFail($studentId);
        $academic = $student->studentAcademic;

        if (!$academic) {
            return response()->json([
                'success' => false,
                'error'   => 'Student has no academic record.',
            ], 422);
        }

        if ($data['ready']) {
            /**
             * ASSESSOR SAYS: STUDENT IS READY
             * --------------------------------
             * - keep ready_for_rating = 1
             * - move status forward to: for_admin_review
             *   (this matches the Performance page text:
             *   "For Admin Final Review")
             */
            $academic->ready_for_rating    = true;
            $academic->ready_for_rating_at = $academic->ready_for_rating_at ?? now();
            $academic->slea_application_status = 'for_admin_review';
            $academic->save();

            $statusKey   = 'for_admin_review';
            $statusLabel = 'For admin final review';
        } else {
            /**
             * ASSESSOR SAYS: STUDENT IS NOT READY
             * -----------------------------------
             * - clear ready flag
             * - send them back to pre-application state
             *   (they can apply again later)
             */
            $academic->ready_for_rating    = false;
            $academic->ready_for_rating_at = null;
            $academic->slea_application_status = null;
            $academic->save();

            $statusKey   = 'not_ready';
            $statusLabel = 'Not ready';
        }

        return response()->json([
            'success'     => true,
            'ready'       => (bool) $academic->ready_for_rating,
            'slea_status' => [
                'key'   => $statusKey,
                'label' => $statusLabel,
            ],
        ]);
    }
}
