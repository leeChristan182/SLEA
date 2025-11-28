<?php

namespace App\Http\Controllers;

use App\Models\AssessorCompiledScore;
use App\Models\SubmissionReview;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class AssessorCompiledScoreController extends Controller
{
    // GET /assessor/submissions/compiled

    // GET /assessor/submissions/compiled
    public function index()
    {
        $assessor = Auth::user();
        abort_unless($assessor->isAssessor(), 403);

        $compiled = AssessorCompiledScore::with([
            'student',                  // basic student
            'student.studentAcademic',  // student academic (if exists)
            'student.studentAcademic.program.college',
            'category',                 // rubric category
        ])
            ->where('assessor_id', $assessor->id)
            ->orderBy('student_id')
            ->orderBy('rubric_category_id')
            ->get()
            ->groupBy('student_id');       // → one group per student

        return view('assessor.submissions.submissions', compact('compiled'));
    }
}
