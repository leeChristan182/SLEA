<?php

namespace App\Http\Controllers;

use App\Models\AssessorCompiledScore;
use Illuminate\Support\Facades\Auth;

class AssessorCompiledScoreController extends Controller
{
    // GET /assessor/submissions/compiled
    public function index()
    {
        $assessor = Auth::user();
        abort_unless($assessor && $assessor->isAssessor(), 403);

        // Group compiled scores per student
        $compiledByStudent = AssessorCompiledScore::with([
            'student',
            'student.studentAcademic',
            'student.studentAcademic.program.college',
            'category',
        ])
            ->where('assessor_id', $assessor->id)
            ->orderBy('student_id')
            ->orderBy('rubric_category_id')
            ->get()
            ->groupBy('student_id');

        // Build a $students collection compatible with submissions.blade.php
        $students = $compiledByStudent->map(function ($rows) {
            /** @var \App\Models\AssessorCompiledScore $first */
            $first   = $rows->first();
            $student = $first->student;
            $acad    = $student->studentAcademic;

            // Student number / ID (same logic as in AssessorStudentSubmissionController)
            $studentId = $acad->student_number
                ?? $student->student_id
                ?? $student->id;

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

            return (object) [
                'id'                 => $student->id,
                'student_id'         => $studentId,
                'user'               => $student,
                'program'            => $programName,
                'college'            => $collegeName,
                // not really used in the listing, but keep key for compatibility
                'submissions'        => collect(),
                'latest_reviewed_at' => null,
            ];
        })->values();

        // Same Blade as the other assessor submissions page
        return view('assessor.submissions.submissions', compact('students'));
    }
}
