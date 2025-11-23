<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\RubricCategory;
use App\Models\AssessorFinalReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SubmissionRecordController extends Controller
{
    /**
     * List submissions for the logged-in student.
     */
    public function index()
    {
        // TODO: later this will show “history” page
        // For now, just show the submit form like create()
        return $this->create();
    }

    /**
     * Show the submit-record page.
     */
    public function create()
    {
        if (! Schema::hasTable('submissions')) {
            abort(404);
        }

        $categories = RubricCategory::with(['sections.subsections'])
            ->orderBy('order_no')
            ->get();

        return view('student.submit', [
            'categories'    => $categories,
            'documentTypes' => $this->documentTypes(),
        ]);
    }

    /**
     * Small helper for Document Type options (if ever needed in Blade).
     */
    protected function documentTypes(): array
    {
        return [
            'certificate' => 'Certificate',
            'appointment' => 'Appointment Letter',
            'report'      => 'Accomplishment / Narrative Report',
            'moa'         => 'Memorandum of Agreement',
            'others'      => 'Others',
        ];
    }

    /**
     * Store a new submission.
     *
     * IMPORTANT: the form must send fields with these names:
     *   - rubric_category_id, rubric_section_id, rubric_subsection_id
     *   - activity_title
     *   - (optional) activity_type, role_in_activity, date_of_activity,
     *                organizing_body, note, term, issued_by, document_type
     *   - attachments[]  (your multiple file input)
     */
    public function store(Request $request)
    {
        if (! Schema::hasTable('submissions')) {
            abort(404);
        }

        $user = Auth::user();

        $data = $request->validate([
            'rubric_category_id'   => ['required', 'exists:rubric_categories,id'],
            'rubric_section_id'    => ['nullable', 'exists:rubric_sections,section_id'],
            'rubric_subsection_id' => ['nullable', 'exists:rubric_subsections,sub_section_id'],

            'activity_title'       => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string'],

            'activity_type'        => ['nullable', 'string', 'max:100'],
            'role_in_activity'     => ['nullable', 'string', 'max:255'],
            'date_of_activity'     => ['nullable', 'date'],
            'organizing_body'      => ['nullable', 'string', 'max:255'],
            'note'                 => ['nullable', 'string'],
            'term'                 => ['nullable', 'string', 'max:50'],
            'issued_by'            => ['nullable', 'string', 'max:255'],
            'document_type'        => ['nullable', 'string', 'max:50'],

            'attachments'          => ['required'],
            'attachments.*'        => ['file', 'max:5120', 'mimes:jpeg,jpg,png,pdf'],
        ]);

        // ---- upload files ----
        $filesMeta = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if (! $file) continue;

                $path = $file->store('submissions', 'public');

                $filesMeta[] = [
                    'original' => $file->getClientOriginalName(),
                    'path'     => $path,
                    'size'     => $file->getSize(),
                    'mime'     => $file->getClientMimeType(),
                ];
            }
        }

        // ---- create submission ----
        $submission = Submission::create([
            'user_id'              => $user->id,
            'leadership_id'        => null,

            'rubric_category_id'   => $data['rubric_category_id'],
            'rubric_section_id'    => $data['rubric_section_id'] ?? null,
            'rubric_subsection_id' => $data['rubric_subsection_id'] ?? null,

            'activity_title'       => $data['activity_title'],
            'description'          => $data['description'] ?? null,

            'attachments'          => $filesMeta,
            'meta'                 => [
                'activity_type'    => $data['activity_type']    ?? null,
                'role_in_activity' => $data['role_in_activity'] ?? null,
                'date_of_activity' => $data['date_of_activity'] ?? null,
                'organizing_body'  => $data['organizing_body']  ?? null,
                'note'             => $data['note']             ?? null,
                'term'             => $data['term']             ?? null,
                'issued_by'        => $data['issued_by']        ?? null,
                'document_type'    => $data['document_type']    ?? null,
            ],

            'status'       => 'pending',  // matches submission_statuses.key
            'remarks'      => null,
            'submitted_at' => now(),
        ]);

        // ---- REVERT RULE (safe) ----
        if (Schema::hasTable('assessor_final_reviews')) {
            AssessorFinalReview::where('student_id', $user->id)
                ->whereIn('status', ['finalized', 'submitted'])
                ->update([
                    'status'      => 'tracking',
                    'reviewed_at' => now(),
                ]);
        }

        // JSON response for fetch()
        if ($request->wantsJson()) {
            return response()->json([
                'ok'      => true,
                'message' => 'Record submitted for review.',
                'id'      => $submission->id,
            ]);
        }

        return redirect()
            ->route('student.submit')
            ->with('status', 'Submission uploaded.');
    }

    public function preview(int $id)
    {
        // Only the owner can preview
        $submission = Submission::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $files = $submission->attachments;   // casted to array in the model
        $path  = null;

        // Case 1: your normal case – array of file info like you showed in tinker
        if (is_array($files)) {
            // if it's a "flat" file info array
            if (isset($files['path']) && is_string($files['path'])) {
                $path = $files['path'];
            }
            // if it's an array of file info arrays
            elseif (isset($files[0])) {
                if (is_array($files[0]) && isset($files[0]['path']) && is_string($files[0]['path'])) {
                    $path = $files[0]['path'];
                } elseif (is_string($files[0])) {
                    $path = $files[0];
                }
            }
        }

        // Case 2: attachments accidentally stored as plain string
        if (! $path && is_string($files) && trim($files) !== '') {
            $path = trim($files);
        }

        if (! $path) {
            abort(404, 'No attachment found for this submission.');
        }

        // Make sure it exists on the public disk
        if (! Storage::disk('public')->exists($path)) {
            abort(404, 'File not found on disk.');
        }

        // Inline preview in browser (perfect for the iframe)
        return Storage::disk('public')->response($path);
    }
}
