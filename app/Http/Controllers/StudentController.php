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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;


class StudentController extends Controller
{
    /* =========================
     | PROFILE & DASHBOARD
     * ========================= */


    public function profile()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Academic info with relations (if table exists)
        $academic = Schema::hasTable('student_academic')
            ? StudentAcademic::with(['college', 'program', 'major'])
            ->where('user_id', $user->id)
            ->first()
            : null;

        // Leadership info from the new student_leaderships table if present,
        // otherwise fall back to legacy leadership_information (if it still exists).
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
                    'c.name as cluster_name',
                    'o.name as organization_name',
                    'p.name as position_name'
                )
                ->get();
        } elseif (Schema::hasTable('leadership_information')) {
            // Legacy fallback
            $leaderships = \App\Models\LeadershipInformation::where('student_id', $user->id)->get();
        } else {
            $leaderships = collect();
        }

        return view('student.profile', [
            'user'        => $user,
            'academic'    => $academic,
            'leaderships' => $leaderships,
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

        $user->update(['profile_picture_path' => $path]);

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
            return back()->withErrors(['current_password' => 'Your current password is incorrect.']);
        }

        $user->password = $request->password; // model mutator will hash
        $user->save();

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
            'student_number' => ['nullable', 'string', 'max:30'],
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

        // If you’re saving via AJAX, you can return JSON; otherwise redirect back
        if ($request->wantsJson()) {
            return response()->json([
                'message'  => $msg,
                'academic' => $academic->load(['college', 'program', 'major']),
            ]);
        }

        return back()->with('status', $msg);
    }

    // POST /student/update-leadership
    public function updateLeadership(Request $request)
    {
        // Expect an array of leadership entries (id for update, else create)
        $request->validate([
            'leadership'               => ['required', 'array'],
            'leadership.*.id'          => ['nullable', 'integer'],
            'leadership.*.org_id'      => ['required', 'integer'],
            'leadership.*.position_id' => ['required', 'integer'],
            'leadership.*.scope'       => ['nullable', 'string', 'max:32'], // must match scope_levels.key if enforced
            'leadership.*.from'        => ['nullable', 'date'],
            'leadership.*.to'          => ['nullable', 'date', 'after_or_equal:leadership.*.from'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (! Schema::hasTable('student_leaderships')) {
            return back()->withErrors(['leadership' => 'Leadership table not found.']);
        }

        foreach ($request->input('leadership') as $row) {
            $base = [
                'user_id'     => $user->id,
                'org_id'      => $row['org_id'],
                'position_id' => $row['position_id'],
                'scope'       => $row['scope'] ?? null,
                'from'        => $row['from'] ?? null,
                'to'          => $row['to'] ?? null,
                'status'      => 'pending', // default; admin/assessor can approve later
                'updated_at'  => now(),
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

        return back()->with('status', 'Leadership records saved.');
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
        $path = $request->file('cor')->store('cor', 'public');
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

        if ($academic) {
            $nowYear = (int) now()->year;
            $status  = 'eligible';

            if (!empty($academic->expected_grad_year) && $nowYear > (int) $academic->expected_grad_year) {
                // Past expected graduation year → needs revalidation
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
                'success'   => true,
                'message'   => 'Certificate of Registration uploaded.',
                'cor_path'  => $path,
                'cor_url'   => Storage::disk('public')->url($path),
                'eligibility_status' => $academic->eligibility_status ?? $status ?? null,
            ]);
        }

        // Fallback for normal form POST
        return back()->with('status', 'Certificate of Registration uploaded.');
    }

    /* =========================
     | VIEWS: PERFORMANCE / CRITERIA / HISTORY
     * ========================= */

    // GET /student/performance
    public function performance()
    {
        $user = Auth::user();
        abort_unless($user->isStudent(), 403);

        $academic = $user->studentAcademic;

        // 1) Load rubric categories in display order
        $categories = RubricCategory::orderBy('order_no')->get();

        // 2) Reviews for this student's ACCEPTED submissions
        $reviews = SubmissionReview::query()
            ->whereHas('submission', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->where('status', 'accepted'); // ✅ only accepted submissions count
            })
            ->with(['submission.category'])
            ->get();

        // 3) Sum scores per category key
        $scoresByCategoryKey = [];

        foreach ($reviews as $review) {
            $submission = $review->submission;
            if (! $submission || ! $submission->category) {
                continue;
            }

            $cat = $submission->category;
            $key = $cat->key; // e.g. leadership, academic, awards, community, conduct

            if (! isset($scoresByCategoryKey[$key])) {
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

            // Clamp earned to the category max
            $earned = $max > 0 ? min($rawEarned, $max) : $rawEarned;

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

        return view('student.performance', [
            'perfData'               => $perfData,
            'slea_application_status' => $academic?->slea_application_status,
            'ready_for_rating'       => (bool) ($academic->ready_for_rating ?? false),
            'can_mark_ready_for_slea' => $user->canMarkReadyForSlea(),
        ]);
    }
    public function markReadyForSlea()
    {
        $user = Auth::user();
        abort_unless($user->isStudent(), 403);

        $academic = $user->studentAcademic;

        if (! $academic) {
            return back()->with('error', 'No academic record found. Please contact OSAS.');
        }

        if (! $academic->canMarkReadyForSlea()) {
            return back()->with('error', 'You are not eligible to mark yourself ready for SLEA at this time.');
        }

        $academic->markReadyForSlea();

        return back()->with('success', 'You are now marked as ready to be rated for the Student Leadership Excellence Award.');
    }
    public function cancelReadyForSlea()
    {
        $user = Auth::user();
        abort_unless($user->isStudent(), 403);

        $academic = $user->studentAcademic;

        if (! $academic) {
            return back()->with('error', 'No academic record found.');
        }

        // Only allow cancel if status is still "ready_for_assessor"
        if ($academic->slea_application_status !== 'ready_for_assessor') {
            return back()->with('error', 'You can no longer cancel. Your request is already being processed.');
        }

        // Reset flags
        $academic->ready_for_rating        = false;
        $academic->ready_for_rating_at     = null;
        $academic->slea_application_status = null;
        $academic->save();

        return back()->with('success', 'Your SLEA rating request has been cancelled. You may continue submitting more requirements.');
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
            $submissions = collect();
        } else {
            $submissions = Submission::with([
                'category',
                'leadership',
                'latestHistory', // from Submission model
            ])
                ->where('user_id', Auth::id())
                ->orderByDesc('submitted_at')
                ->orderByDesc('created_at')
                ->paginate(15);
        }

        // resources/views/student/history.blade.php
        return view('student.history', [
            'submissions' => $submissions,
        ]);
    }
}
