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
                ->leftJoin('leadership_types as lt', 'lt.id', '=', 'sl.leadership_type_id')
                ->leftJoin('organizations as org', 'org.id', '=', 'sl.organization_id')
                ->leftJoin('positions as pos', 'pos.id', '=', 'sl.position_id')
                ->select([
                    'sl.id',
                    'sl.leadership_type_id',
                    'sl.organization_id',
                    'sl.position_id',
                    'sl.start_year',
                    'sl.end_year',
                    'sl.is_current',
                    'sl.created_at',
                    'sl.updated_at',
                    'lt.name as leadership_type_name',
                    'lt.key  as leadership_type_key',
                    'org.name as organization_name',
                    'org.category',
                    'pos.name as position_name',
                    'pos.is_top_tier',
                    'pos.leadership_type_id as pos_leadership_type_id',
                ])
                ->where('sl.user_id', $user->id)
                ->orderByDesc('sl.start_year')
                ->orderByDesc('sl.is_current')
                ->get();
        } else {
            $leaderships = collect();
        }

        // Pre-load leadership types (with custom sort using CASE for known keys)
        if (Schema::hasTable('leadership_types')) {
            $leadershipTypes = DB::table('leadership_types')
                ->select('*')
                ->orderByRaw("
                    CASE `key`
                        WHEN 'usg' THEN 1
                        WHEN 'osc' THEN 2
                        WHEN 'lc'  THEN 3
                        WHEN 'cco' THEN 4
                        WHEN 'sco' THEN 5
                        WHEN 'lgu' THEN 6
                        WHEN 'lcm' THEN 7
                        WHEN 'eap' THEN 8
                        ELSE 99
                    END
                ")
                ->get();
        } else {
            $leadershipTypes = collect();
        }

        return view('student.profile', [
            'user'            => $user,
            'academic'        => $academic,
            'leaderships'     => $leaderships,
            'leadershipTypes' => $leadershipTypes,
        ]);
    }



    // POST /student/update-avatar
    public function updateAvatar(Request $request)
    {
        $request->validate(
            [
                'profile_picture' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ],
            [
                'profile_picture.required' => 'Please select an image to upload.',
                'profile_picture.image'    => 'The file must be an image.',
                'profile_picture.mimes'    => 'Only JPG, JPEG, PNG, and WEBP files are allowed.',
                'profile_picture.max'      => 'The image must not be greater than 5MB.',
            ]
        );

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Store the new profile picture
        $path = $request->file('profile_picture')->store('avatars', 'public');

        // Delete old profile picture if it exists
        if ($user->profile_picture_path && Storage::disk('public')->exists($user->profile_picture_path)) {
            Storage::disk('public')->delete($user->profile_picture_path);
        }

        // Update user's profile picture path
        $user->profile_picture_path = $path;
        $user->save();

        // Refresh user model to ensure we have the latest data
        $user->refresh();

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

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Your current password is incorrect.',
            ]);
        }

        // Store old hash in a password history table if needed, or log it
        DB::table('password_changes')->insert([
            'user_id'                => $user->id,
            'previous_password_hash' => $user->password,
            'changed_at'             => now(),
        ]);

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('status', 'Password updated successfully.');
    }

    /* =========================
     | ACADEMIC & ELIGIBILITY
     * ========================= */

    // GET /student/academic
    public function academic()
    {
        /** @var User $user */
        $user = Auth::user();

        $colleges = Schema::hasTable('colleges')
            ? DB::table('colleges')->orderBy('name')->get()
            : collect();

        $programs = Schema::hasTable('programs')
            ? DB::table('programs')->orderBy('name')->get()
            : collect();

        $majors = Schema::hasTable('majors')
            ? DB::table('majors')->orderBy('name')->get()
            : collect();

        $academic = Schema::hasTable('student_academic')
            ? StudentAcademic::with(['college', 'program', 'major'])
            ->where('user_id', $user->id)
            ->first()
            : null;

        return view('student.academic', compact('user', 'academic', 'colleges', 'programs', 'majors'));
    }

    // POST /student/academic
    public function saveAcademic(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validate([
            'student_number' => ['nullable', 'string', 'max:30'],
            'year_level'     => ['nullable', 'string', 'max:20'],
            'expected_grad_year' => ['nullable', 'integer', 'digits:4'],

            'college_id'     => ['nullable', 'exists:colleges,id'],
            'program_id'     => ['nullable', 'exists:programs,id'],
            'major_id'       => ['nullable', 'exists:majors,id'],
        ]);

        // Current academic row if any
        /** @var \App\Models\StudentAcademic|null $current */
        $current = StudentAcademic::where('user_id', $user->id)->first();

        // --- Compute expected graduation year ---
        // Rule (same as before):
        //   If explicitly given (expected_grad_year), use that;
        //   otherwise preserve previous expected_grad_year if it exists.
        $expectedGradYear = $data['expected_grad_year'] ?? null;
        $nowYear  = (int) now()->year;
        $oldExpected = $current ? $current->expected_grad_year : null;
        $baseExpected = $expectedGradYear ?? $oldExpected;
        $exceeded = $baseExpected ? ($nowYear > $baseExpected) : false;

        // Decide new eligibility_status
        // - If exceeded OR program/major changed → under_review
        // - Else → eligible
        $oldEligibility = $current
            ? ($current->eligibility_status ?? StudentAcademic::ELIG_ELIGIBLE)
            : StudentAcademic::ELIG_ELIGIBLE; // CHANGED: use StudentAcademic constants instead of hardcoded 'eligible'

        $programChanged = $current && $data['program_id'] && (int) $current->program_id !== (int) $data['program_id'];
        $majorChanged   = $current && array_key_exists('major_id', $data) && $data['major_id'] !== $current->major_id;

        if ($exceeded || $programChanged || $majorChanged) {
            $newEligibility = StudentAcademic::ELIG_UNDER_REVIEW; // CHANGED: use StudentAcademic::ELIG_UNDER_REVIEW instead of raw string
        } else {
            $newEligibility = StudentAcademic::ELIG_ELIGIBLE; // CHANGED: use StudentAcademic::ELIG_ELIGIBLE instead of raw string
        }

        // Build payload (fall back to current values when fields are omitted)
        $payload = [
            'user_id'            => $user->id,
            'student_number'     => $data['student_number'] ?? ($current->student_number ?? null),
            'year_level'         => $data['year_level'] ?? ($current->year_level ?? null),
            'expected_grad_year' => $expectedGradYear ?? $oldExpected,
            'college_id'         => $data['college_id'] ?? ($current->college_id ?? null),
            'program_id'         => $data['program_id'] ?? ($current->program_id ?? null),
            'major_id'           => array_key_exists('major_id', $data)
                ? $data['major_id']
                : ($current->major_id ?? null),
            'eligibility_status' => $newEligibility,
        ];

        if ($current) {
            $current->update($payload);
        } else {
            $current = StudentAcademic::create($payload);
        }

        // Messaging hint for UX
        $msg = $newEligibility === StudentAcademic::ELIG_UNDER_REVIEW // CHANGED: compare using constant, not string
            ? 'Academic information saved. Your eligibility is now under review.'
            : 'Academic information saved.';

        // If you’re saving via AJAX, you can return JSON; otherwise redirect back
        if ($request->wantsJson()) {
            return response()->json([
                'message'       => $msg,
                'academic'      => $current,
                'eligibility'   => $newEligibility,
                'old_eligibility' => $oldEligibility,
            ]);
        }

        return back()->with('status', $msg);
    }

    /* =========================
     | REVALIDATION & COR
     * ========================= */

    // GET /student/revalidation
    public function revalidation()
    {
        /** @var User $user */
        $user = Auth::user();

        $academic = DB::table('student_academic')
            ->where('user_id', $user->id)
            ->first();

        return view('student.revalidation', compact('user', 'academic'));
    }

    // POST /student/upload-cor
    public function uploadCOR(Request $request)
    {
        $request->validate([
            'cor' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:6144'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        $file = $request->file('cor');
        $path = $file->store('cor', 'public');

        // Save COR path on the user (or a dedicated table if needed)
        DB::table('user_documents')->updateOrInsert(
            [
                'user_id' => $user->id,
                'type'    => 'cor',
            ],
            [
                'path'       => $path,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // Recalculate eligibility ONLY for this user
        $academic = DB::table('student_academic')->where('user_id', $user->id)->first();

        $status = null;

        if ($academic) {
            $nowYear = (int) now()->year;
            $status  = StudentAcademic::ELIG_ELIGIBLE; // CHANGED: use constant for default eligibility

            if (!empty($academic->expected_grad_year) && $nowYear > (int) $academic->expected_grad_year) {
                $status = StudentAcademic::ELIG_NEEDS_REVALIDATION; // CHANGED: use constant when marking as needs revalidation
            }

            DB::table('student_academic')
                ->where('user_id', $user->id)
                ->update([
                    'eligibility_status' => $status,
                    'updated_at'         => now(),
                ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message'            => 'Certificate of Registration uploaded.',
                'cor_path'           => $path,
                'cor_url'            => route('student.cor.view'),
                'eligibility_status' => $status,
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
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1) Load rubric categories in display order
        $categories = RubricCategory::orderBy('order_no')->get();

        // 2) Get all reviews for this student's APPROVED submissions (from all assessors)
        // We need to aggregate scores across all assessors for the same submission
        $reviews = SubmissionReview::query()
            ->select(
                'submission_id',
                'rubric_category_id',
                DB::raw('AVG(total_score) as avg_score'),
                DB::raw('MAX(max_score)   as max_score')
            )
            ->whereHas('submission', function ($q) use ($user) {
                $q->where('student_id', $user->id)
                    ->where('status', 'approved'); // status of submission (APPROVED)
            })
            ->groupBy('submission_id', 'rubric_category_id')
            ->get();

        // 3) Aggregate per category
        $categoryScores = [];
        foreach ($reviews as $r) {
            if (!isset($categoryScores[$r->rubric_category_id])) {
                $categoryScores[$r->rubric_category_id] = [
                    'total_score' => 0,
                    'max_score'   => 0,
                ];
            }
            $categoryScores[$r->rubric_category_id]['total_score'] += $r->avg_score;
            $categoryScores[$r->rubric_category_id]['max_score']   += $r->max_score;
        }

        // 4) Build performance data array for the view
        $perfData = $categories->map(function ($cat) use ($categoryScores) {
            $scores = $categoryScores[$cat->id] ?? ['total_score' => 0, 'max_score' => 0];

            return [
                'category'    => $cat->name,
                'description' => $cat->description,
                'total_score' => $scores['total_score'],
                'max_score'   => $scores['max_score'],
                'percentage'  => $scores['max_score'] > 0
                    ? round(($scores['total_score'] / $scores['max_score']) * 100, 2)
                    : 0,
            ];
        });

        // Extra: get the student's SLEA application status & ready_for_rating flag
        $academic = StudentAcademic::where('user_id', $user->id)->first();

        return view('student.performance', [
            'perfData'                => $perfData,
            'slea_application_status' => $academic?->slea_application_status,
            'ready_for_rating'        => (bool) ($academic->ready_for_rating ?? false),
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
        /** @var User $user */
        $user = Auth::user();

        // Only show submissions from the logged-in student
        $submissions = Submission::query()
            ->where('student_id', $user->id)
            ->with([
                'rubricCategory',
                'reviews' => function ($q) {
                    $q->orderByDesc('reviewed_at');
                },
            ])
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at')
            ->paginate(5); // still hardcoded page size (view-specific UX choice)

        return view('student.history', [
            'submissions' => $submissions,
        ]);
    }
}
