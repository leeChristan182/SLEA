<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StudentAcademic;
use App\Models\Submission;
use App\Models\RubricCategory;
use App\Models\SubmissionReview;
use App\Models\AssessorCompiledScore;
use App\Models\FinalReview;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\StudentLeadership;

class StudentController extends Controller
{
    /* =========================
     | PROFILE & DASHBOARD
     * ========================= */


    public function profile()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $academic = Schema::hasTable('student_academic')
            ? StudentAcademic::with(['college', 'program', 'major'])
            ->where('user_id', $user->id)
            ->first()
            : null;

        if (Schema::hasTable('student_leaderships')) {
            $leaderships = DB::table('student_leaderships as sl')
                ->leftJoin('leadership_types as lt', 'sl.leadership_type_id', '=', 'lt.id')
                ->leftJoin('clusters as c', 'sl.cluster_id', '=', 'c.id')
                ->leftJoin('organizations as o', 'sl.organization_id', '=', 'o.id')
                ->leftJoin('positions as p', 'sl.position_id', '=', 'p.id')
                ->where('sl.user_id', $user->id)
                ->select(
                    'sl.*',
                    'lt.name as leadership_type_name',
                    'lt.key as leadership_type_key',
                    'c.name as cluster_name',
                    'o.name as organization_name',
                    'p.name as position_name'
                )
                ->get();
        } elseif (Schema::hasTable('leadership_information')) {
            $leaderships = \App\Models\StudentLeadership::where('student_id', $user->id)->get();
        } else {
            $leaderships = collect();
        }

        // 👇 New: for the modal dropdown
        $leadershipTypes = Schema::hasTable('leadership_types')
            ? DB::table('leadership_types')
            ->select('id', 'name', 'key', 'requires_org')
            ->orderByRaw("CASE `key`
                WHEN 'usg' THEN 1
                WHEN 'osc' THEN 2
                WHEN 'lc'  THEN 3
                WHEN 'cco' THEN 4
                WHEN 'sco' THEN 5
                WHEN 'lgu' THEN 6
                WHEN 'lcm' THEN 7
                WHEN 'eap' THEN 8
                ELSE 99
            END")
            ->get()
            : collect();

        return view('student.profile', [
            'user'           => $user,
            'academic'       => $academic,
            'leaderships'    => $leaderships,
            'leadershipTypes' => $leadershipTypes,
        ]);
    }



    // POST /student/update-avatar
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        $path = $request->file('avatar')->store('avatars', 'public');

        if ($user->profile_picture_path && Storage::disk('public')->exists($user->profile_picture_path)) {
            Storage::disk('public')->delete($user->profile_picture_path);
        }

        // Update database with new path
        $user->profile_picture_path = $path;
        $user->save();

        // Build public URL for JS
        $avatarUrl = asset('storage/' . $path);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Profile picture updated.',
                'avatar_url' => $avatarUrl,
            ]);
        }

        return back()->with('status', 'Profile picture updated.');
    }

    // POST /student/change-password
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your current password is incorrect.',
                ], 422);
            }

            return back()->withErrors(['current_password' => 'Your current password is incorrect.']);
        }

        $user->password = $request->password; // mutator hashes
        $user->save();

        // 🔹 SYSTEM LOG: PASSWORD CHANGE
        $userName = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);
        \App\Models\SystemMonitoringAndLog::record(
            $user->role,
            $userName ?: $user->email,
            'Update',
            "Changed password."
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Password updated.',
            ]);
        }

        return back()->with('status', 'Password updated.');
    }


    /* =========================
     | ACADEMIC INFO & LEADERSHIP
     * ========================= */

    // POST /student/update-academic
    public function updateAcademicInfo(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (! Schema::hasTable('student_academic')) {
            return back()->withErrors(['student_number' => 'Academic table not found.']);
        }

        // Validate: COR is now REQUIRED when updating academic details
        $data = $request->validate([
            'year_level' => ['required', 'integer', 'in:1,2,3,4,5,6,7,8'],
            'cor' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:6144'], // 6 MB
            'student_number' => ['nullable', 'string', 'max:20'],
            'college_id'     => ['nullable', 'integer', 'exists:colleges,id'],
            'program_id'     => ['nullable', 'integer', 'exists:programs,id'],
            'major_id'       => ['nullable', 'integer', 'exists:majors,id'],
        ], [
            'cor.required' => 'Certificate of Registration (COR) is required to update academic details.',
            'year_level.required' => 'Year level is required.',
        ]);

        // Current academic row if any
        /** @var \App\Models\StudentAcademic|null $current */
        $current = StudentAcademic::where('user_id', $user->id)->first();

        // --- Upload COR file ---
        $corPath = $current?->certificate_of_registration_path;
        
        if ($request->hasFile('cor')) {
            // Delete old COR if exists
            if ($corPath && Storage::disk('public')->exists($corPath)) {
                Storage::disk('public')->delete($corPath);
            }
            
            // Store new COR
            $corPath = $request->file('cor')->store('student_cors', 'public');
            
            // Log to user_documents if table exists
            if (Schema::hasTable('user_documents')) {
                // Delete old COR documents
                DB::table('user_documents')
                    ->where('user_id', $user->id)
                    ->where('doc_type', 'cor')
                    ->delete();
                
                // Store new COR document
                DB::table('user_documents')->insert([
                    'user_id'      => $user->id,
                    'doc_type'     => 'cor',
                    'storage_path' => $corPath,
                    'meta'         => json_encode([
                        'uploaded_via' => 'academic_update',
                        'uploaded_at'  => now()->toDateTimeString(),
                        'original_filename' => $request->file('cor')->getClientOriginalName(),
                    ]),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }

        // --- Compute expected graduation year ---
        $expectedGradYear = null;
        $numberForCalc = $data['student_number'] ?? ($current->student_number ?? null);
        $yearLevelRaw = $data['year_level'] ?? ($current->year_level ?? null);
        $totalYears = 4;

        if (is_numeric($yearLevelRaw)) {
            $level = (int) $yearLevelRaw;
            if ($level >= 4 && $level <= 10) {
                $totalYears = $level;
            }
        }

        if (is_string($numberForCalc) && preg_match('/^\s*(\d{4})/', $numberForCalc, $m)) {
            $entry = (int) $m[1];
            if ($entry > 1900 && $entry < 3000) {
                $expectedGradYear = $entry + $totalYears;
            }
        }

        // Always set to 'under_review' when updating academic details with COR
        // This ensures it appears in the admin revalidation queue
        $newEligibility = 'under_review';

        // Build payload
        $payload = [
            'user_id'            => $user->id,
            'student_number'     => $data['student_number'] ?? ($current->student_number ?? null),
            'college_id'         => $data['college_id'] ?? ($current->college_id ?? null),
            'program_id'         => $data['program_id'] ?? ($current->program_id ?? null),
            'major_id'           => $data['major_id'] ?? ($current->major_id ?? null),
            'year_level'         => $data['year_level'],
            'graduate_prior'     => $current->graduate_prior ?? null,
            'expected_grad_year' => $expectedGradYear ?? ($current->expected_grad_year ?? null),
            'eligibility_status' => $newEligibility, // Always 'under_review' for admin review
            'certificate_of_registration_path' => $corPath, // Update COR path
            'revalidated_at'     => null, // Reset revalidation timestamp
        ];

        if ($current) {
            $current->fill($payload);
            $current->save();
            $academic = $current;
        } else {
            $academic = StudentAcademic::create($payload);
        }

        // Keep account limited until admin approves
        $user->is_account_limited = true;
        $user->save();

        // System log
        $userName = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);
        \App\Models\SystemMonitoringAndLog::record(
            $user->role,
            $userName ?: $user->email,
            'Update',
            "Updated academic information and uploaded COR. Status set to 'under_review' for admin validation."
        );

        $msg = 'Your academic details and COR have been submitted for review. You will receive an email notification once the administrator has reviewed your submission.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => $msg,
                'academic' => $academic->load(['college', 'program', 'major']),
            ]);
        }

        return back()->with('status', $msg);
    }

    // POST /student/update-leadership
    public function updateLeadership(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (! Schema::hasTable('student_leaderships')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leadership table not found.',
                ], 500);
            }

            return back()->withErrors(['leadership' => 'Leadership table not found.']);
        }

        $validated = $request->validate([
            'leadership'                      => ['required', 'array'],
            'leadership.*.id'                 => ['nullable', 'integer'],
            'leadership.*.leadership_type_id' => ['required', 'integer', 'exists:leadership_types,id'],
            'leadership.*.cluster_id'         => ['nullable', 'integer', 'exists:clusters,id'],
            'leadership.*.organization_id'    => ['nullable', 'integer', 'exists:organizations,id'],
            'leadership.*.position_id'        => ['required', 'integer', 'exists:positions,id'],
            'leadership.*.leadership_status'  => ['required', 'in:Active,Inactive'],
            'leadership.*.term'               => ['required', 'string', 'max:25'],
            'leadership.*.issued_by'          => ['required', 'string', 'max:150'],
        ]);

        foreach ($validated['leadership'] as $row) {
            $base = [
                'user_id'            => $user->id,
                'leadership_type_id' => (int) $row['leadership_type_id'],
                'cluster_id'         => $row['cluster_id'] ?? null,
                'organization_id'    => $row['organization_id'] ?? null,
                'position_id'        => (int) $row['position_id'],
                'term'               => $row['term'] ?? null,
                'issued_by'          => $row['issued_by'] ?? null,
                'leadership_status'  => $row['leadership_status'] ?? 'Active',
                'updated_at'         => now(),
            ];

            if (!empty($row['id'])) {
                DB::table('student_leaderships')
                    ->where('id', $row['id'])
                    ->where('user_id', $user->id)
                    ->update($base);
            } else {
                $base['created_at'] = now();
                DB::table('student_leaderships')->insert($base);
            }
        }

        // 🔹 SYSTEM LOG: PROFILE UPDATE (Leadership Info)
        $userName = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);
        \App\Models\SystemMonitoringAndLog::record(
            $user->role,
            $userName ?: $user->email,
            'Update',
            "Updated leadership information."
        );

        $msg = 'Leadership information saved.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return back()->with('status', $msg);
    }



    // GET /student/revalidation
    public function revalidation()
    {
        /** @var User $user */
        $user = Auth::user();

        // If this student is NOT locked anymore, send them to profile (or submit page)
        if (! $user->awardLocked()) {
            return redirect()->route('student.profile'); // or route('student.submissions.create')
        }

        $academic = Schema::hasTable('student_academic')
            ? DB::table('student_academic')->where('user_id', $user->id)->first()
            : null;

        return view('student.revalidation', compact('user', 'academic'));
    }

    // POST /student/upload-cor
    public function uploadCOR(Request $request)
    {
        $request->validate([
            'cor' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:6144'], // 6 MB
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (! Schema::hasTable('student_academic')) {
            // If AJAX, send JSON error; otherwise redirect with errors
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Academic table not found.',
                ], 500);
            }

            return back()->withErrors(['cor' => 'Academic table not found.']);
        }

        // Store file
        $path = $request->file('cor')->store('student_cors', 'public');
        $now  = now();

        // Upsert into student_academic
        $data = [
            'user_id'                          => $user->id,
            'certificate_of_registration_path' => $path,
            'updated_at'                       => $now,
        ];

        $exists = DB::table('student_academic')->where('user_id', $user->id)->first();

        if ($exists) {
            DB::table('student_academic')
                ->where('user_id', $user->id)
                ->update($data);
        } else {
            $data['created_at'] = $now;
            DB::table('student_academic')->insert($data);
        }

        // Optional: log to user_documents with NEW column names
        if (Schema::hasTable('user_documents')) {
            // Delete old COR documents for this user
            DB::table('user_documents')
                ->where('user_id', $user->id)
                ->where('doc_type', 'cor')
                ->delete();
            
            // Store new COR document with original filename
            DB::table('user_documents')->insert([
                'user_id'      => $user->id,
                'doc_type'     => 'cor',
                'storage_path' => $path,
                'meta'         => json_encode([
                    'uploaded_via' => 'profile_page',
                    'uploaded_at'  => $now->toDateTimeString(),
                    'original_filename' => $request->file('cor')->getClientOriginalName(),
                ]),
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        // Recalculate eligibility ONLY for this user
        $academic = DB::table('student_academic')->where('user_id', $user->id)->first();

        $status = null;

        if ($academic) {
            $nowYear = (int) now()->year;
            $currentStatus = (string) ($academic->eligibility_status ?? 'eligible');

            $status = $currentStatus; // keep current by default

            if ($currentStatus !== 'under_review') {
                $status = 'eligible';

                if (!empty($academic->expected_grad_year) && $nowYear > (int) $academic->expected_grad_year) {
                    $status = 'needs_revalidation';
                }
            }


            DB::table('student_academic')
                ->where('user_id', $user->id)
                ->update([
                    'eligibility_status' => $status,
                    'updated_at'         => $now,
                ]);
        }


        // If AJAX (used by student_profile.js) → JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'            => true,
                'message'            => 'Certificate of Registration uploaded.',
                'cor_path'           => $path,
                'cor_url'            => route('student.cor.view'),
                'eligibility_status' => $status,
            ]);
        }

        // Fallback for normal form POST
        return back()->with('status', 'Certificate of Registration uploaded.');
    }


    public function viewCOR()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Ensure academic record exists
        $academic = $user->studentAcademic;

        if (! $academic || empty($academic->certificate_of_registration_path)) {
            abort(404, 'No COR uploaded.');
        }

        $path = $academic->certificate_of_registration_path;

        // Must match the disk used during upload
        if (! Storage::disk('public')->exists($path)) {
            abort(404, 'File not found.');
        }

        // Try to get original filename from user_documents table
        $originalFilename = null;
        if (Schema::hasTable('user_documents')) {
            $doc = DB::table('user_documents')
                ->where('user_id', $user->id)
                ->where('doc_type', 'cor')
                ->where('storage_path', $path)
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($doc && !empty($doc->meta)) {
                $meta = json_decode($doc->meta, true);
                if (is_array($meta) && isset($meta['original_filename']) && !empty($meta['original_filename'])) {
                    $originalFilename = $meta['original_filename'];
                }
            }
        }

        // Generate filename: use original if available, otherwise use student number
        $fileExtension = pathinfo($path, PATHINFO_EXTENSION);
        if ($originalFilename) {
            // Use original filename but ensure correct extension matches the stored file
            $originalExt = pathinfo($originalFilename, PATHINFO_EXTENSION);
            if (strtolower($originalExt) === strtolower($fileExtension)) {
                // Extension matches, use original filename as-is
                $filename = $originalFilename;
            } else {
                // Extension doesn't match, use original name with correct extension
                $filename = pathinfo($originalFilename, PATHINFO_FILENAME) . '.' . $fileExtension;
            }
        } else {
            // Fallback to student number format
            $studentNumber = $academic->student_number ?? 'COR';
            $filename = 'COR_' . $studentNumber . '.' . $fileExtension;
        }
        
        // Sanitize filename for safe use in headers
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        return response()->file(
            Storage::disk('public')->path($path),
            [
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
                'Content-Type' => Storage::disk('public')->mimeType($path) ?: 'application/pdf',
            ]
        );
    }

    /* =========================
     | VIEWS: PERFORMANCE / CRITERIA / HISTORY
     * ========================= */

    // GET /student/performance
    public function performance()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        abort_unless($user->isStudent(), 403);

        // Always get a fresh academic record
        $academic = \App\Models\StudentAcademic::firstOrCreate(
            ['user_id' => $user->id],
            [
                'slea_application_status' => null,
                'ready_for_rating'        => false,
            ]
        );
        $academic->refresh();

        // 1) Load rubric categories in display order
        $categories = RubricCategory::orderBy('order_no')->get();

        // 2) Get compiled scores from assessors (OFFICIAL SOURCE OF TRUTH)
        // This ensures points match exactly what assessors assigned and stored
        // Each compiled score represents one assessor's total for one category
        // We sum across all assessors to get the student's total per category
        $compiledScores = AssessorCompiledScore::where('student_id', $user->id)
            ->with('category')
            ->get();

        // 3) Check for admin final review decision (for reference)
        // Note: Admin decisions don't change the compiled scores, but we track them
        $finalReview = FinalReview::whereHas('assessorFinalReview', function($q) use ($user) {
            $q->where('student_id', $user->id);
        })->latest('reviewed_at')->first();

        // 4) Sum scores per category key from compiled scores
        // This aggregates all assessors' compiled scores for each category
        $scoresByCategoryKey = [];
        foreach ($compiledScores as $compiled) {
            $category = $compiled->category;
            if (!$category) {
                continue;
            }
            
            $key = $category->key;
            
            if (!isset($scoresByCategoryKey[$key])) {
                $scoresByCategoryKey[$key] = 0.0;
            }
            
            // Use the official compiled total_score (already validated and capped by assessor)
            // This ensures accuracy and matches exactly what assessors see in their dashboard
            $scoresByCategoryKey[$key] += (float) $compiled->total_score;
        }

        // 5) If admin has rejected the student, optionally zero out scores
        // (Uncomment the next 3 lines if you want to show 0 points when admin rejects)
        // if ($finalReview && $finalReview->decision === 'not_qualified') {
        //     $scoresByCategoryKey = [];
        // }

        // 6) Build perfData
        $roman          = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI'];
        $perfCategories = [];
        $totalEarned    = 0.0;
        $totalMax       = 0.0;
        $index          = 1;

        foreach ($categories as $cat) {
            $key       = $cat->key;
            $max       = (float) ($cat->max_points ?? 0);
            $rawEarned = (float) ($scoresByCategoryKey[$key] ?? 0);

            // Show full earned points (no clamping)
            $earned = $rawEarned;

            $labelPrefix = $roman[$index] ?? ($index . '.');
            $label       = "{$labelPrefix}. {$cat->title}";

            $perfCategories[] = [
                'key'    => $key,
                'label'  => $label,
                'earned' => round($earned, 2),
                'max'    => $max,
            ];

            $totalEarned += $earned;
            $totalMax    += $max;
            $index++;
        }

        $perfData = [
            'totals' => [
                'earned' => round($totalEarned, 2),
                'max'    => round($totalMax, 2),
            ],
            'categories' => $perfCategories,
        ];

        // Always read latest status from DB
        $status = \App\Models\StudentAcademic::where('user_id', $user->id)
            ->value('slea_application_status');

        // Fallback to relationship value just in case
        if ($status === null && $academic->slea_application_status !== null) {
            $status = $academic->slea_application_status;
        }

        Log::info('Student performance page loaded', [
            'user_id'                   => $user->id,
            'status_from_relationship'  => $academic->slea_application_status,
            'status_from_db'            => $status,
            'ready_for_rating'          => (bool) $academic->ready_for_rating,
        ]);

        // If you still want to use $currentRole / $sleaAwarded in Blade:
        $currentRole = $user->role;
        $sleaAwarded = ($status === 'qualified');

        return view('student.performance', [
            'perfData'                => $perfData,
            'slea_application_status' => $status,
            'ready_for_rating'        => (bool) $academic->ready_for_rating,
            'currentRole'             => $currentRole,
            'sleaAwarded'             => $sleaAwarded,
        ]);
    }

    // GET /student/criteria

    public function criteria()
    {
        $categories = RubricCategory::with(['sections.subsections.options'])
            ->orderBy('order_no')
            ->get();

        return view('student.criteria', compact('categories'));
    }
    // GET /student/history

    public function history()
    {
        if (! Schema::hasTable('submissions')) {
            $submissions = new LengthAwarePaginator([], 0, 5);
        } else {
            $submissions = Submission::with([
                'category',
                'leadership',
                'latestHistory',
                'reviews' => function ($q) {
                    $q->latest('reviewed_at');
                },
            ])
                ->where('user_id', Auth::id())
                ->orderByDesc('submitted_at')
                ->orderByDesc('created_at')
                ->paginate(5);
        }

        return view('student.history', compact('submissions'));
    }
}
