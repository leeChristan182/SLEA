<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\RubricCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Small helper for the Document Type dropdown.
     */
    protected function documentTypes(): array
    {
        return [
            'certificate' => 'Certificate',
            'appointment' => 'Appointment Letter',
            'moa'         => 'Memorandum of Agreement',
            'training'    => 'Training / Seminar',
            'other'       => 'Other',
        ];
    }

    /**
     * GET /student/submit
     * Show the student submission form.
     */
    public function create()
    {
        $user = Auth::user();

        // Category → sections → subsections tree
        $categories = RubricCategory::with(['sections.subsections'])
            ->orderBy('order_no')
            ->get();

        // Student's existing submissions (optional table on the page)
        $submissions = Submission::with(['category', 'subsection'])
            ->where('user_id', $user->id)
            ->orderByDesc('submitted_at')
            ->paginate(10);

        return view('student.submit', [
            'categories'    => $categories,
            'documentTypes' => $this->documentTypes(),
            'submissions'   => $submissions,
        ]);
    }

    /**
     * POST /student/submit
     * Store new submission.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            // SLEA classification
            'rubric_category_id'   => ['required', 'exists:rubric_categories,id'],
            'rubric_section_id'    => ['nullable', 'exists:rubric_sections,section_id'],
            'rubric_subsection_id' => ['nullable', 'exists:rubric_subsections,sub_section_id'],

            // Main activity fields
            'activity_title'       => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string'], // not from UI yet but kept

            // Extra UI fields → meta JSON
            'activity_type'        => ['nullable', 'string', 'max:100'],
            'role_in_activity'     => ['nullable', 'string', 'max:255'],
            'date_of_activity'     => ['nullable', 'date'],
            'organizing_body'      => ['nullable', 'string', 'max:255'],
            'note'                 => ['nullable', 'string'],
            'term'                 => ['nullable', 'string', 'max:50'],
            'issued_by'            => ['nullable', 'string', 'max:255'],
            'document_type'        => ['nullable', 'string', 'max:50'],

            // Files: JPEG/PDF/PNG up to 5 MB each
            'attachments.*'        => ['file', 'max:5120', 'mimes:jpeg,jpg,png,pdf'],
        ]);

        // Upload files → attachments JSON
        $filesMeta = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if (!$file) {
                    continue;
                }
                $path = $file->store('submissions', 'public');

                $filesMeta[] = [
                    'original' => $file->getClientOriginalName(),
                    'path'     => $path,
                    'size'     => $file->getSize(),
                    'mime'     => $file->getClientMimeType(),
                ];
            }
        }

        Submission::create([
            'user_id'             => $user->id,
            'leadership_id'       => null, // later you can link to student_leaderships

            'rubric_category_id'  => $data['rubric_category_id'],
            'rubric_section_id'   => $data['rubric_section_id'] ?? null,
            'rubric_subsection_id' => $data['rubric_subsection_id'] ?? null,

            'activity_title'      => $data['activity_title'],
            // Use the "note" as description for now
            'description'         => $data['note'] ?? $data['description'] ?? null,

            'attachments'         => $filesMeta ?: null,
            'meta'                => [
                'activity_type'   => $data['activity_type']    ?? null,
                'role_in_activity' => $data['role_in_activity'] ?? null,
                'date_of_activity' => $data['date_of_activity'] ?? null,
                'organizing_body' => $data['organizing_body']  ?? null,
                'term'            => $data['term']             ?? null,
                'issued_by'       => $data['issued_by']        ?? null,
                'document_type'   => $data['document_type']    ?? null,
            ],

            // Must match submission_statuses.key
            'status'              => 'pending',
            'submitted_at'        => now(),
        ]);

        // 🔹 SYSTEM LOG: STUDENT SUBMISSION
        $userName = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);
        $categoryName = \App\Models\RubricCategory::find($data['rubric_category_id'])->name ?? 'Unknown Category';
        \App\Models\SystemMonitoringAndLog::record(
            $user->role,
            $userName ?: $user->email,
            'Submit',
            "Submitted {$categoryName} activity: {$data['activity_title']}."
        );

        // Two flows: Proceed vs Submit Another
        $redirectRoute = $request->has('submit_another')
            ? 'student.submit'   // stay on form
            : 'student.history'; // or performance, up to you

        return redirect()
            ->route($redirectRoute)
            ->with('status', 'Record submitted for review.');
    }

    /**
     * GET /student/history
     * Simple history view for logged-in student using submissions table.
     */
    public function history()
    {
        $user = Auth::user();

        $submissions = Submission::with(['category', 'subsection'])
            ->where('user_id', $user->id)
            ->orderByDesc('submitted_at')
            ->paginate(15);

        return view('student.history', compact('submissions'));
    }
}
