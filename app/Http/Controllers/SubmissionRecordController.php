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

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Does the submissions table have application_status?
        $hasApplicationStatusColumn = Schema::hasColumn('submissions', 'application_status');

        // ------------ VALIDATION ------------
        $rules = [
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
            'term' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value && trim($value) !== '') {
                        // Try different separators: " - ", "-", "–", " to "
                        $parts = null;
                        if (strpos($value, ' - ') !== false) {
                            $parts = explode(' - ', $value);
                        } elseif (strpos($value, '-') !== false) {
                            $parts = explode('-', $value);
                        } elseif (strpos($value, '–') !== false) {
                            $parts = explode('–', $value);
                        } elseif (strpos($value, ' to ') !== false) {
                            $parts = explode(' to ', $value);
                        }
                        
                        if ($parts && count($parts) >= 2) {
                            $start = (int)trim($parts[0]);
                            $end = (int)trim($parts[1]);
                            
                            // Validate years are reasonable (1900-3000)
                            if ($start < 1900 || $start > 3000 || $end < 1900 || $end > 3000) {
                                $fail('The term must contain valid years (e.g., 2023-2024 or 2023 - 2024).');
                            } elseif ($end <= $start) {
                                $fail('The ending year must be greater than the starting year.');
                            }
                        } else {
                            // If no separator found, check if it's a single year or invalid format
                            $trimmed = trim($value);
                            if (!preg_match('/^\d{4}/', $trimmed)) {
                                $fail('The term format is invalid. Please use format like "2023-2024" or "2023 - 2024".');
                            }
                        }
                    }
                },
            ],
            'issued_by'            => ['nullable', 'string', 'max:255'],
            'document_type'        => ['nullable', 'string', 'max:50'],

            'attachments'   => ['required'],
            'attachments.*' => ['file', 'max:5120', 'mimes:jpeg,jpg,png,pdf'],
        ];

        if ($hasApplicationStatusColumn) {
            $rules['application_status'] = [
                'required',
                'in:for_final_application,for_tracking',
            ];
        }

        // ✅ From here on $data['application_status'] is always defined when column exists
        $data = $request->validate($rules);

        // ------------ FILE UPLOADS ------------
        $filesMeta = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if (! $file) {
                    continue;
                }

                $path = $file->store('submissions', 'student_docs');

                $filesMeta[] = [
                    'original' => $file->getClientOriginalName(),
                    'path'     => $path,
                    'size'     => $file->getSize(),
                    'mime'     => $file->getClientMimeType(),
                ];
            }
        }

        // ------------ CREATE SUBMISSION ROW ------------
        $submissionData = [
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

            'status'       => 'pending',
            'remarks'      => null,
            'submitted_at' => now(),
        ];

        if ($hasApplicationStatusColumn) {
            $submissionData['application_status'] = $data['application_status'];
        }

        $submission = Submission::create($submissionData);

        // ------------ UPDATE student_academic BASED ON application_status ------------
        if ($hasApplicationStatusColumn) {
            $appStatus = $data['application_status'];   // safe now

            // Ensure an academic row exists
            $academic = \App\Models\StudentAcademic::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'eligibility_status'      => 'eligible',
                    'slea_application_status' => null,
                    'ready_for_rating'        => false,
                ]
            );

            if ($appStatus === 'for_final_application') {
                // Student is explicitly applying for SLEA
                $academic->ready_for_rating    = true;
                $academic->ready_for_rating_at = $academic->ready_for_rating_at ?? now();

                // Only bump to assessor stage if not already further
                if (!in_array($academic->slea_application_status, [
                    'pending_administrative_validation',
                    'qualified',
                    'not_qualified',
                ], true)) {
                    $academic->slea_application_status = 'pending_assessor_evaluation';
                }
            } else { // for_tracking
                // Treat as not ready
                $academic->ready_for_rating    = false;
                $academic->ready_for_rating_at = null;

                if (in_array($academic->slea_application_status, [
                    null,
                    'pending_assessor_evaluation',
                    'incomplete',
                ], true)) {
                    $academic->slea_application_status = null; // or 'incomplete' if you prefer
                }
            }

            $academic->save();
        }

        // ------------ REVERT RULE (same as before) ------------
        if (Schema::hasTable('assessor_final_reviews')) {
            // Verify 'draft' status exists in enum table before updating
            $draftExists = DB::table('final_review_statuses')->where('key', 'draft')->exists();
            if (!$draftExists) {
                try {
                    DB::table('final_review_statuses')->insert(['key' => 'draft']);
                } catch (\Exception $e) {
                    \Log::warning("Could not create 'draft' status: " . $e->getMessage());
                }
            }
            
            AssessorFinalReview::where('student_id', $user->id)
                ->whereIn('status', ['finalized', 'queued_for_admin'])
                ->update([
                    'status'      => 'draft',
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
        if (! Storage::disk('student_docs')->exists($path)) {
            abort(404, 'File not found on disk.');
        }

        return Storage::disk('student_docs')->response($path);
    }
}
