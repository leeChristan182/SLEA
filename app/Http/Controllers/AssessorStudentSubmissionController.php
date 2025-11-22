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

        // All submissions this assessor has already acted on
        $submissions = Submission::with([
            'user',
            'user.studentAcademic.program.college',
            'reviews' => function ($q) use ($assessor) {
                $q->where('assessor_id', $assessor->id);
            },
        ])
            ->whereIn('status', ['approved', 'rejected'])
            ->whereHas('reviews', function ($q) use ($assessor) {
                $q->where('assessor_id', $assessor->id);
            })
            ->get();

        // Group by student (user_id)
        $grouped = $submissions->groupBy('user_id');

        $students = $grouped->map(function ($subs) {
            /** @var \App\Models\Submission $first */
            $first = $subs->first();
            $user  = $first->user;
            $acad  = $user->studentAcademic;

            // student number fallback logic
            $studentId = $acad->student_number
                ?? $user->student_id
                ?? $user->id;

            // program name
            if ($acad && $acad->program) {
                $programName = $acad->program->name;
            } else {
                $programName = $acad->program_name ?? $acad->program ?? '—';
            }

            // college name
            if ($acad && $acad->program && $acad->program->college) {
                $collegeName = $acad->program->college->name;
            } else {
                $collegeName = $acad->college_name ?? $acad->college ?? '—';
            }

            // latest review date across this student's submissions
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
        })->values();

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
            'category',   // RubricCategory (SLEA section)
            'subsection', // RubricSubsection
            'reviews' => function ($q) use ($assessor) {
                $q->where('assessor_id', $assessor->id)->latest();
            },
            'reviews.assessor',
        ])
            ->where('user_id', $studentId)
            ->whereIn('status', ['approved', 'rejected'])
            ->whereHas('reviews', function ($q) use ($assessor) {
                $q->where('assessor_id', $assessor->id);
            })
            ->get();

        $categorizedSubmissions = [];
        $categoryTotals = [];
        $overallTotalScore = 0.0;

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

            // build totals from the same scores
            if (!isset($categoryTotals[$sectionTitle])) {
                $categoryTotals[$sectionTitle] = [
                    'score'     => 0.0,
                    'max_score' => 0.0,
                ];
            }

            $categoryTotals[$sectionTitle]['score'] += $score;
            $overallTotalScore += $score;
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
}
