<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\SubmissionRecord;
use App\Models\PendingSubmission;
use App\Models\AcademicInformation;
use App\Models\RubricCategory;
use App\Models\RubricSection;
use App\Models\RubricSubsection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SubmissionRecordController extends Controller
{
    /**
     * Display a paginated list of a student's submissions.
     */
    public function index(Request $request)
    {
        $studentId = $request->query('student_id') ?? auth()->user()->student_id;

        $records = SubmissionRecord::with(['category', 'section', 'subsection', 'rubricLeadership'])
            ->when($studentId, fn($q) => $q->where('student_id', $studentId))
            ->latest('activity_date')
            ->latest()
            ->paginate(10);

        return view('submissions.index', compact('records', 'studentId'));
    }

    /**
     * Show submission creation form.
     */
    public function create(Request $request)
    {
        $studentId = $request->query('student_id') ?? auth()->user()->student_id;
        $categories = RubricCategory::with('sections.subsections')->get();


        return view('student.submit', compact('studentId', 'categories'));
    }


    /**
     * Store a new submission record (student upload)
     * and queue it for assessor review.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'leadership_id'    => ['nullable', 'integer', 'exists:rubric_subsection_leadership,leadership_id'],
            'category_id'      => ['nullable', 'integer', 'exists:rubric_categories,category_id'],
            'section_id'       => ['nullable', 'integer', 'exists:rubric_sections,section_id'],
            'sub_items'        => ['nullable', 'integer', 'exists:rubric_subsections,sub_items'],
            'activity_title'   => ['required', 'string', 'max:255'],
            'activity_type'    => ['required', 'string', 'max:255'],
            'activity_role'    => ['required', 'string', 'max:255'],
            'activity_date'    => ['required', 'date'],
            'organizing_body'  => ['required', 'string', 'max:255'],
            'term'             => ['nullable', 'string', 'max:50'],
            'issued_by'        => ['nullable', 'string', 'max:50'],
            'note'             => ['nullable', 'string', 'max:50'],
            'document_type'    => ['nullable', 'string', 'max:255'],
            'document_title'   => ['nullable', 'string', 'max:255'],
            'document_file'    => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
            'datedocu_submitted' => ['nullable', 'date'],
        ]);

        $studentId = auth()->user()->student_id ?? $request->input('student_id');

        // --- Handle file upload ---
        $storedPath = null;
        if ($request->hasFile('document_file')) {
            $storedPath = $request->file('document_file')->store('submission_docs', 'public');
        }

        // --- Create submission record ---
        $record = SubmissionRecord::create([
            'student_id'           => $studentId,
            'leadership_id'        => $validated['leadership_id'] ?? null,
            'category_id'          => $validated['category_id'] ?? null,
            'section_id'           => $validated['section_id'] ?? null,
            'sub_items'            => $validated['sub_items'] ?? null,
            'activity_title'       => $validated['activity_title'],
            'activity_type'        => $validated['activity_type'],
            'activity_role'        => $validated['activity_role'],
            'activity_date'        => $validated['activity_date'],
            'organizing_body'      => $validated['organizing_body'],
            'term'                 => $validated['term'] ?? null,
            'issued_by'            => $validated['issued_by'] ?? null,
            'note'                 => $validated['note'] ?? null,
            'document_type'        => $validated['document_type'] ?? null,
            'document_title'       => $validated['document_title'] ?? null,
            'document_title_path'  => $storedPath,
            'datedocu_submitted'   => $validated['datedocu_submitted'] ?? now(),
        ]);

        // --- Auto-queue for assessor review ---
        PendingSubmission::create([
            'subrec_id' => $record->subrec_id,
            'action' => 'Queued',
            'pending_queued_date' => now(),
        ]);

        // --- Update Academic Information if columns exist ---
        $aiTable = (new AcademicInformation())->getTable();
        $ay = SubmissionRecord::computeAcademicYear($record->activity_date);

        $updates = [];
        if (Schema::hasColumn($aiTable, 'school_year')) {
            $updates['school_year'] = $ay;
        }
        if (!empty($record->term) && Schema::hasColumn($aiTable, 'semester')) {
            $updates['semester'] = $record->term;
        }

        if (!empty($updates)) {
            AcademicInformation::where('student_id', $record->student_id)->update($updates);
        }

        // --- ✅ Handle AJAX vs normal request ---
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Submission saved, queued for review, and academic info updated.'
            ]);
        }

        // --- ✅ Fallback for normal form submit (ensure correct route name) ---
        return redirect()
            ->route('student.submissions.index', ['student_id' => $record->student_id])
            ->with('success', 'Submission saved, queued for review, and academic info updated.');
    }

    /**
     * Download uploaded document.
     */
    public function download($subrec_id)
    {
        $rec = SubmissionRecord::findOrFail($subrec_id);

        if (!$rec->document_title_path || !Storage::disk('public')->exists($rec->document_title_path)) {
            abort(404, 'File not found.');
        }

        $abs = Storage::disk('public')->path($rec->document_title_path);
        $downloadName = $rec->document_title ?: basename($abs);

        return response()->download($abs, $downloadName);
    }
}
