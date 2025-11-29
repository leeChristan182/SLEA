<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StudentAcademic;
use App\Models\Submission;
use App\Models\RubricCategory;
use App\Models\SubmissionReview;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;


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
            $leaderships = \App\Models\LeadershipInformation::where('student_id', $user->id)->get();
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

        // Validate only real columns / foreign keys
        $data = $request->validate([
            'student_number' => ['nullable', 'string', 'max:20'],
            'year_level'     => ['nullable', 'string', 'max:20'],

            'college_id'     => ['nullable', 'exists:colleges,id'],
            'program_id'     => ['nullable', 'exists:programs,id'],
            'major_id'       => ['nullable', 'exists:majors,id'],
        ]);

        // Current academic row if any
        /** @var \App\Models\StudentAcademic|null $current */
        $current = StudentAcademic::where('user_id', $user->id)->first();

        // --- Compute expected graduation year ---
        // Rule (same as before): take first 4 digits of student_number as entry year, add 4
        $expectedGradYear = null;
        $numberForCalc = $data['student_number']
            ?? ($current->student_number ?? null);

        if (is_string($numberForCalc) && preg_match('/^\s*(\d{4})/', $numberForCalc, $m)) {
            $entry = (int) $m[1];
            if ($entry > 1900 && $entry < 3000) {
                $expectedGradYear = $entry + 4; // adjust if you use 5-year programs
            }
        }

        // Determine if program/major changed (forces revalidation)
        $programChanged = isset($data['program_id']) && $current
            && (int) $current->program_id !== (int) $data['program_id'];

        $majorChanged = isset($data['major_id']) && $current
            && (int) $current->major_id !== (int) $data['major_id'];

        // Exceeded expected year?
        $nowYear  = (int) now()->year;
        $oldExpected = $current ? $current->expected_grad_year : null;
        $baseExpected = $expectedGradYear ?? $oldExpected;
        $exceeded = $baseExpected ? ($nowYear > $baseExpected) : false;

        // Decide new eligibility_status
        // - If exceeded OR program/major changed → under_review
        // - Else → eligible
        $oldEligibility = $current ? ($current->eligibility_status ?? 'eligible') : 'eligible';

        if ($exceeded || $programChanged || $majorChanged) {
            $newEligibility = 'under_review';
        } else {
            $newEligibility = 'eligible';
        }

        // Build payload (fall back to current values when fields are omitted)
        $payload = [
            'user_id'            => $user->id,
            'student_number'     => $data['student_number'] ?? ($current->student_number ?? null),
            'college_id'         => $data['college_id'] ?? ($current->college_id ?? null),
            'program_id'         => $data['program_id'] ?? ($current->program_id ?? null),
            'major_id'           => $data['major_id'] ?? ($current->major_id ?? null),
            'year_level'         => $data['year_level'] ?? ($current->year_level ?? null),
            'graduate_prior'     => $current->graduate_prior ?? null,
            'expected_grad_year' => $baseExpected,
            'eligibility_status' => $newEligibility,
            // Keep revalidated_at as-is here; you probably have a separate flow to set it.
            'revalidated_at'     => $current->revalidated_at ?? null,
        ];

        if ($current) {
            $current->fill($payload);
            $current->save();
            $academic = $current;
        } else {
            $academic = StudentAcademic::create($payload);
        }

        // Messaging hint for UX
        $msg = $newEligibility === 'under_review'
            ? 'Academic information saved. Your eligibility is now under review.'
            : 'Academic information saved.';

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
        $path = $request->file('cor')->store('cor', 'student_docs');
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
            DB::table('user_documents')->insert([
                'user_id'      => $user->id,
                'doc_type'     => 'cor',
                'storage_path' => $path,
                'meta'         => json_encode([
                    'uploaded_via' => 'profile_page',
                    'uploaded_at'  => $now->toDateTimeString(),
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
            $status  = 'eligible';

            if (!empty($academic->expected_grad_year) && $nowYear > (int) $academic->expected_grad_year) {
                $status = 'needs_revalidation';
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
        /** @var User $user */
        $user = Auth::user();

        // Ensure academic exists
        $academic = \DB::table('student_academic')->where('user_id', $user->id)->first();

        if (!$academic || empty($academic->certificate_of_registration_path)) {
            abort(404, 'No COR uploaded.');
        }

        // Serve the file directly
        $path = $academic->certificate_of_registration_path;

        if (!\Storage::disk('student_docs')->exists($path)) {
            abort(404, 'File not found.');
        }

        return response()->file(
            Storage::disk('student_docs')->path($path)
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

        // Refresh the relationship to get the latest status
        /** @var \App\Models\User $user */
        $user->load('studentAcademic');
        $academic = $user->studentAcademic;

        // If academic record doesn't exist, create a basic one
        if (!$academic) {
            $academic = \App\Models\StudentAcademic::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'slea_application_status' => null,
                    'ready_for_rating' => false,
                ]
            );
            // Reload to get the fresh record
            $academic->refresh();
        } else {
            // Refresh to get latest data from database
            $academic->refresh();
        }

        // 1) Load rubric categories in display order
        $categories = RubricCategory::orderBy('order_no')->get();

        // 2) Get all reviews for this student's APPROVED submissions (from all assessors)
        // We need to aggregate scores across all assessors for the same submission
        $reviews = SubmissionReview::query()
            ->whereHas('submission', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->where('status', 'approved'); // ✅ only approved submissions count
            })
            ->with(['submission.category'])
            ->get();

        // 3) Sum scores per category key
        // Group by submission_id first to get the latest review per submission (in case multiple assessors reviewed)
        $scoresByCategoryKey = [];
        $reviewsBySubmission = $reviews->groupBy('submission_id');

        foreach ($reviewsBySubmission as $submissionId => $submissionReviews) {
            // Get the latest review for this submission (most recent)
            $review = $submissionReviews->sortByDesc('reviewed_at')->first();

            // Skip if no score (null), but allow 0 scores as they might be valid
            if (!isset($review->score) || $review->score === null) {
                continue;
            }

            $submission = $review->submission;
            if (!$submission) {
                continue;
            }

            // Try to get category from submission first, then from review's rubric_category_id
            $category = $submission->category;
            if (!$category && $review->rubric_category_id) {
                $category = RubricCategory::find($review->rubric_category_id);
            }

            if (!$category) {
                continue;
            }

            $key = $category->key; // e.g. leadership, academic, awards, community, conduct

            if (!isset($scoresByCategoryKey[$key])) {
                $scoresByCategoryKey[$key] = 0.0;
            }

            $scoresByCategoryKey[$key] += (float) $review->score;
        }

        // 4) Build perfData
        $roman = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI'];
        $perfCategories = [];
        $totalEarned = 0.0;
        $totalMax    = 0.0;
        $index       = 1;

        foreach ($categories as $cat) {
            $key       = $cat->key;
            $max       = (float) ($cat->max_points ?? 0);
            $rawEarned = (float) ($scoresByCategoryKey[$key] ?? 0);

            // ❌ Old: $earned = $max > 0 ? min($rawEarned, $max) : $rawEarned;
            // ✅ New: show full earned points
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

        // Get the status directly from database to ensure we have the latest value
        $status = $academic ? \App\Models\StudentAcademic::where('user_id', $user->id)
            ->value('slea_application_status') : null;

        // Log for debugging
        Log::info('Student performance page loaded', [
            'user_id' => $user->id,
            'status_from_relationship' => $academic?->slea_application_status,
            'status_from_db' => $status,
            'ready_for_rating' => (bool) ($academic->ready_for_rating ?? false),
        ]);

        return view('student.performance', [
            'perfData'               => $perfData,
            'slea_application_status' => $status ?? $academic?->slea_application_status,
            'ready_for_rating'       => (bool) ($academic->ready_for_rating ?? false),
        ]);
    }

    // GET /student/criteria

    public function criteria()
    {
        // If rubric tables are missing (dev / migration issue), avoid crashing
        if (!Schema::hasTable('rubric_categories')) {
            return view('student.criteria', [
                'categories' => collect(),
            ]);
        }

        // Load the full rubric: category → sections → subsections → options
        $categories = RubricCategory::with([
            'sections.subsections.options',
        ])
            ->orderBy('order_no')
            ->get();

        return view('student.criteria', [
            'categories' => $categories,
        ]);
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
