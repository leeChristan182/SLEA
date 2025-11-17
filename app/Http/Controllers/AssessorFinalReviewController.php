<?php

namespace App\Http\Controllers;

use App\Models\AssessorCompiledScore;
use App\Models\AssessorFinalReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssessorFinalReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // POST /assessor/final-review/{student}
    public function storeForStudent(int $studentId, Request $request)
    {
        $assessor = Auth::user();
        abort_unless($assessor->isAssessor(), 403);

        $scores = AssessorCompiledScore::where('assessor_id', $assessor->id)
            ->where('student_id', $studentId)
            ->get();

        if ($scores->isEmpty()) {
            return back()->with('error', 'No compiled scores found for this student.');
        }

        $totalScore = $scores->sum('total_points');
        $maxPoints  = $scores->sum('max_points');

        AssessorFinalReview::updateOrCreate(
            [
                'student_id'  => $studentId,
                'assessor_id' => $assessor->id,
            ],
            [
                'total_score' => $totalScore,
                'max_points'  => $maxPoints,
                'status'      => 'finalized', // must exist in final_review_statuses
                'reviewed_at' => now(),
            ]
        );

        return back()->with('status', 'Student moved to final review list.');
    }

    // Assessor’s list of students ready for admin final review
    // GET /assessor/final-review
    public function index()
    {
        $assessor = Auth::user();
        abort_unless($assessor->isAssessor(), 403);

        $items = AssessorFinalReview::with('student.studentAcademic')
            ->where('assessor_id', $assessor->id)
            ->orderByDesc('reviewed_at')
            ->get();

        return view('assessor.final_review_list', compact('items'));
    }
}
