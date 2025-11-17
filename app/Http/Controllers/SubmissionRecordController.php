<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\RubricCategory;
use App\Models\AssessorFinalReview; // ⬅️ NEW: to revert final-review when new submission arrives
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

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
            // SLEA classification (FKs)
            'rubric_category_id'   => ['required', 'exists:rubric_categories,id'],
            'rubric_section_id'    => ['nullable', 'exists:rubric_sections,section_id'],
            'rubric_subsection_id' => ['nullable', 'exists:rubric_subsections,sub_section_id'],

            // Main activity fields
            'activity_title'       => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string'],

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
            'attachments'          => ['required'],
            'attachments.*'        => ['file', 'max:5120', 'mimes:jpeg,jpg,png,pdf'],
        ]);

        // Upload files → attachments JSON (array of metadata)
        $filesMeta = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if (! $file) {
                    continue;
                }

                $path = $file->store('submissions', 'public');

                $filesMeta[] = [
                    // ⬇️ NOTE: keeping this as 'original' (we'll read this key on assessor side)
                    'original' => $file->getClientOriginalName(),
                    'path'     => $path,
                    'size'     => $file->getSize(),
                    'mime'     => $file->getClientMimeType(),
                ];
            }
        }

        // Create the submission record
        $submission = Submission::create([
            'user_id'              => $user->id,
            'leadership_id'        => null, // later you can link to student_leaderships

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

            // Must match submission_statuses.key
            'status'              => 'pending',
            'remarks'             => null,
            'submitted_at'        => now(),
        ]);

        /**
         * ⬇️ REVERT RULE:
         * If this student already has entries in assessor_final_reviews
         * (meaning they were in the assessor's final-review list),
         * any new submission should move them back to "tracking" so
         * assessors know there is new evidence to re-check.
         *
         * Adjust the status keys to match your final_review_statuses table.
         */
        AssessorFinalReview::where('student_id', $user->id)
            ->whereIn('status', ['finalized', 'submitted']) // e.g. “ready_for_admin” states
            ->update([
                'status'      => 'tracking', // this key must exist in final_review_statuses
                'reviewed_at' => now(),
            ]);

        // For your JS fetch() flow on the submit page:
        if ($request->wantsJson()) {
            return response()->json([
                'ok'      => true,
                'message' => 'Record submitted for review.',
                'id'      => $submission->id,
            ]);
        }

        // Fallback for normal form posts
        return redirect()
            ->route('student.submit')
            ->with('status', 'Submission uploaded.');
    }

    /**
     * Currently downloads the *first* attachment of the submission.
     * (If you later want per-file download, add an {attachmentIndex} param.)
     */
    public function download(int $id)
    {
        if (! Schema::hasTable('submissions')) {
            abort(404);
        }

        $submission = Submission::where('user_id', Auth::id())->findOrFail($id);

        $attachments = $submission->attachments ?? [];
        if (! is_array($attachments) || empty($attachments)) {
            abort(404);
        }

        // For now, just serve the first file
        $file = $attachments[0];

        if (empty($file['path']) || ! Storage::disk('public')->exists($file['path'])) {
            abort(404);
        }

        $downloadName = $file['original'] ?? basename($file['path']);

        return Storage::disk('public')->download($file['path'], $downloadName);
    }
}
