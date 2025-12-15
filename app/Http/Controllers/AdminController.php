<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpCodeMail;
use App\Mail\AccountApprovedMail;
use App\Mail\AccountRejectedMail;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\College;
use App\Models\Program;
use App\Models\StudentAcademic;
use App\Models\UserDocument;
use App\Models\SystemMonitoringAndLog;
use App\Mail\AccountCreatedMail;
use App\Models\StudentLeadership; // ✅ use the correct leadership model
use App\Mail\InitialValidationApprovedMail;
use App\Mail\InitialValidationRejectedMail;
use App\Mail\RevalidationApprovedMail;
use App\Mail\RevalidationRejectedMail;
use App\Models\Submission;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Illuminate\Support\Facades\Log;
use App\Models\AssessorFinalReview;
use App\Models\FinalReview;

class AdminController extends Controller
{
    /* =========================
     | PROFILE & PASSWORD
     * ========================= */

    // GET /admin/profile
    public function dashboard()
    {
        // A) Approved users per role
        $approvedByRole = User::query()
            ->where('status', User::STATUS_APPROVED)
            ->select('role', DB::raw('COUNT(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role');

        $roleCounts = [
            'admin'    => (int) ($approvedByRole[User::ROLE_ADMIN] ?? 0),
            'assessor' => (int) ($approvedByRole[User::ROLE_ASSESSOR] ?? 0),
            'student'  => (int) ($approvedByRole[User::ROLE_STUDENT] ?? 0),
        ];

        // B) SLEA qualified vs not qualified (StudentAcademic)
        $qualifiedCount = StudentAcademic::query()
            ->where('slea_application_status', StudentAcademic::SLEA_STATUS_QUALIFIED)
            ->count();

        $notQualifiedCount = StudentAcademic::query()
            ->where('slea_application_status', StudentAcademic::SLEA_STATUS_NOT_QUALIFIED)
            ->count();

        // C1) Scores graph source (AssessorFinalReview finalized)
        $scores = AssessorFinalReview::query()
            ->where('status', AssessorFinalReview::STATUS_FINALIZED)
            ->whereNotNull('total_score')
            ->orderBy('total_score')
            ->pluck('total_score')
            ->map(fn($v) => (float) $v)
            ->values()
            ->toArray();

        $avgScore = count($scores) ? array_sum($scores) / count($scores) : 0;

        // C2) Admin final decision counts (FinalReview.decision)
        $decisionCounts = FinalReview::query()
            ->select('decision', DB::raw('COUNT(*) as total'))
            ->groupBy('decision')
            ->pluck('total', 'decision');

        $finalDecisions = [
            'approved'      => (int) ($decisionCounts['approved'] ?? 0),
            'not_qualified' => (int) ($decisionCounts['not_qualified'] ?? 0),
        ];

        // D) Initial Validation Queue count (must match /admin/initial-validation)
        $initialValidationQueueCount = User::query()
            ->where('status', User::STATUS_APPROVED)
            ->whereIn('role', [User::ROLE_STUDENT, User::ROLE_ASSESSOR])
            ->where(function ($q) {
                // Students: approved + account-limited
                $q->where(function ($s) {
                    $s->where('role', User::ROLE_STUDENT)
                        ->where('is_account_limited', true)
                        // Initial Validation is for NEW profile completion only
                        ->where(function ($p) {
                            $p->where('profile_completed', false)
                                ->orWhereNull('profile_completed');
                        });
                })
                // Assessors: approved + incomplete (profile_completed=false OR missing assessorInfo)
                ->orWhere(function ($a) {
                    $a->where('role', User::ROLE_ASSESSOR)
                        ->where(function ($x) {
                            $x->where('profile_completed', false)
                                ->orWhereDoesntHave('assessorInfo');
                        });
                });
            })
            ->count();

        // Status distribution (donut)
        $statusCounts = Submission::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // Pending Approval Queue should match items queued for admin final review
        $pendingApproval = AssessorFinalReview::where('status', 'queued_for_admin')->count();

        $submissionStatus = [
            'approved'  => (int) ($statusCounts['approved'] ?? 0),
            'rejected'  => (int) ($statusCounts['rejected'] ?? 0),
            'in_review' => (int) ($statusCounts['in_review'] ?? $statusCounts['pending'] ?? 0),
            'complete'  => (int) ($statusCounts['complete'] ?? $statusCounts['completed'] ?? 0),
        ];

        // College breakdown (pie)
        $collegeBreakdownRaw = Submission::query()
            ->join('student_academic', 'student_academic.user_id', '=', 'submissions.user_id')
            ->join('colleges', 'colleges.id', '=', 'student_academic.college_id')
            ->select('colleges.name as college', DB::raw('COUNT(*) as total'))
            ->groupBy('colleges.name')
            ->orderByDesc('total')
            ->get();

        $collegeLabels = $collegeBreakdownRaw->pluck('college')->toArray();
        $collegeData   = $collegeBreakdownRaw->pluck('total')->toArray();
        $collegeTotal  = array_sum($collegeData);

        return view('admin.dashboard', compact(
            'roleCounts',
            'qualifiedCount',
            'notQualifiedCount',
            'scores',
            'avgScore',
            'finalDecisions',
            'initialValidationQueueCount',
            'submissionStatus',
            'collegeLabels',
            'collegeData',
            'collegeTotal',
            'pendingApproval'
        ));
    }

    public function profile()
    {
        $user = Auth::user();
        return view('admin.profile', compact('user'));
    }

    // PUT /admin/profile/update
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $data = $request->validate([
            'first_name'  => ['required', 'string', 'max:50'],
            'last_name'   => ['required', 'string', 'max:50'],
            'middle_name' => ['nullable', 'string', 'max:50'],
            'email'       => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'contact' => [
                'required',
                'regex:' . config('slea.phone_regex'),
            ],
            'birth_date'  => [
                'nullable',
                'date',
                'before:today',
                'after_or_equal:' . now()->subYears(100)->toDateString(),
                'before_or_equal:' . now()->subYears(15)->toDateString(),
            ],
        ]);

        $user->update($data);

        // If admin now has basic info, mark profile as completed
        if (Schema::hasColumn($user->getTable(), 'profile_completed')) {
            if (! $user->profile_completed && $user->contact && $user->birth_date) {
                $user->profile_completed = true;
                $user->save();
            }
        }

        // SYSTEM LOG: PROFILE UPDATE
        $adminName = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);
        SystemMonitoringAndLog::record(
            $user->role,
            $adminName ?: $user->email,
            'Update',
            "Updated profile information."
        );

        return back()->with('status', 'Profile updated.');
    }

    // POST /admin/profile/avatar
    public function updateAvatar(Request $request)
    {
        try {
            // match client-side 5MB limit (5 * 1024 KB = 5120)
            $request->validate([
                'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors'  => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');

        // Delete old avatar if present
        if ($user->profile_picture_path && Storage::disk('public')->exists($user->profile_picture_path)) {
            Storage::disk('public')->delete($user->profile_picture_path);
        }

        // Update database with new path
        $user->profile_picture_path = $path;
        $user->save();

        // Refresh user model to ensure we have the latest data
        $user->refresh();

        // Generate avatar URL with cache-busting parameter
        $avatarUrl = asset('storage/' . $path) . '?v=' . time();

        // JSON for AJAX
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Avatar updated.',
                'avatar_url' => $avatarUrl,
            ]);
        }

        // Fallback for non-AJAX form submits
        return back()->with('status', 'Avatar updated.');
    }

    // PUT /admin/profile/password
    public function updatePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $data = $request->validate([
            'current_password'      => ['required'],
            'password'              => ['required', 'confirmed', PasswordRule::defaults()],
            'password_confirmation' => ['required'],
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Store previous hash in password_changes table (if table exists)
        if (Schema::hasTable('password_changes')) {
            DB::table('password_changes')->insert([
                'user_id'                => $user->id,
                'previous_password_hash' => $user->password,
                'changed_at'             => now(),
                'changed_by'             => 'self',
                'ip'                     => $request->ip(),
                'user_agent'             => $request->userAgent(),
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }

        // Actually change the password
        $user->password = Hash::make($data['password']);
        $user->save();

        // SYSTEM LOG: PASSWORD CHANGE
        $displayName = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);

        SystemMonitoringAndLog::record(
            $user->role,
            $displayName ?: $user->email,
            'Update',
            'User changed account password.'
        );

        return back()->with('status', 'Password updated successfully.');
    }


    /* =========================
     | USER MANAGEMENT
     * ========================= */

    // GET /admin/manage  (filters: ?role=assessor&status=approved&q=lee)
    public function manageAccount(Request $request)
    {
        $users = User::query()
            ->where('role', '!=', User::ROLE_ADMIN) // Exclude admin accounts (system accounts)
            ->when($request->filled('role'),   fn($q) => $q->where('role', $request->role))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('q'),      fn($q) => $q->where(function ($x) use ($request) {
                $like = '%' . $request->q . '%';
                $x->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('email', 'like', $like);
            }))
            ->orderBy('last_name')
            ->paginate(5)
            ->withQueryString();

        return view('admin.manage-account', compact('users'));
    }

    // GET /admin/approve-reject
    public function approveReject(Request $request)
    {
        $search = $request->input('q');

        // Show all pending users with unassigned role
        $users = User::query()
            ->where('role', User::ROLE_UNASSIGNED)
            ->where('status', User::STATUS_PENDING)
            ->when($search, function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where(function ($inner) use ($like) {
                    $inner->where('email', 'like', $like)
                        ->orWhere('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('user_code', 'like', $like);
                });
            })
            ->orderBy('created_at', 'asc') // Oldest registrations first
            ->paginate(10)
            ->withQueryString();

        return view('admin.approve-reject', compact('users', 'search'));
    }


    // POST /admin/approve/{user_id} - Assign role and approve
    public function approveUser(Request $request, $user_id)
    {
        $admin = Auth::user();

        $request->validate([
            'role' => ['required', 'in:student,assessor'],
        ]);

        $user = User::where('id', $user_id)
            ->where('role', User::ROLE_UNASSIGNED)
            ->where('status', User::STATUS_PENDING)
            ->firstOrFail();

        // Assign role + approve
        $user->role   = $request->input('role');
        $user->status = User::STATUS_APPROVED;

        // Students start LIMITED (no academic yet)
        // ✅ Students and Assessors start LIMITED until they complete requirements
        if (in_array($user->role, [User::ROLE_STUDENT, User::ROLE_ASSESSOR], true)) {
            $user->is_account_limited = true;
        }


        $user->save();

        // ❗ DO NOT CREATE student_academic HERE

        // Email
        try {
            Mail::to($user->email)->send(new AccountApprovedMail($user));
        } catch (\Throwable $e) {
            Log::warning('Failed to send AccountApprovedMail', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'error'   => $e->getMessage(),
            ]);
        }

        // System log
        $adminName = trim($admin->first_name . ' ' . ($admin->middle_name ? $admin->middle_name . ' ' : '') . $admin->last_name);
        $userName  = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);

        SystemMonitoringAndLog::record(
            $admin->role,
            $adminName ?: $admin->email,
            'Update',
            "Approved and assigned role {$user->role} for {$userName} ({$user->email})."
        );

        return back()->with('status', 'Account approved successfully.');
    }


    // POST /admin/reject/{user_id} - Reject with reason
    public function rejectUser(Request $request, $user_id)
    {
        $admin = Auth::user();

        $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user = User::where('id', $user_id)
            ->where('role', User::ROLE_UNASSIGNED)
            ->where('status', User::STATUS_PENDING)
            ->firstOrFail();

        $user->status = User::STATUS_REJECTED;
        $user->save();

        $rejectionReason = $request->input('rejection_reason', '');

        // Send rejection email with reason
        try {
            Mail::to($user->email)->send(new AccountRejectedMail($user, $rejectionReason));
        } catch (\Throwable $e) {
            Log::warning('Failed to send AccountRejectedMail', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'error'   => $e->getMessage(),
            ]);
        }

        // System log
        $adminName = trim($admin->first_name . ' ' . ($admin->middle_name ? $admin->middle_name . ' ' : '') . $admin->last_name);
        $userName  = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);

        SystemMonitoringAndLog::record(
            $admin->role,
            $adminName ?: $admin->email,
            'Update',
            "Rejected account for {$userName} ({$user->email})." . ($rejectionReason ? " Reason: {$rejectionReason}" : '')
        );

        return redirect()->back()->with('status', 'Account rejected. User has been notified via email.');
    }



    // PATCH /admin/manage/{user}/toggle   (approved <-> disabled)
    public function toggleUser(User $user)
    {
        // Safety: don't toggle yourself
        if (Auth::id() === $user->id) {
            return back()->withErrors(['email' => 'You cannot disable your own account.']);
        }

        // Safety: don't leave zero active admins
        if ($user->isAdmin()) {
            $activeAdmins = User::role(User::ROLE_ADMIN)->approved()->count();
            if ($activeAdmins <= 1 && $user->isApproved()) {
                return back()->withErrors(['email' => 'You cannot disable the last active admin.']);
            }
        }

        $oldStatus = $user->status;
        $user->toggle(); // model handles approved <-> disabled
        $newStatus = $user->status;

        // SYSTEM LOG: ACCOUNT DISABLED/ENABLED
        $admin = Auth::user();
        $adminName = trim($admin->first_name . ' ' . ($admin->middle_name ? $admin->middle_name . ' ' : '') . $admin->last_name);
        $userName = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);
        $action = $newStatus === 'disabled' ? 'Disabled' : 'Enabled';

        SystemMonitoringAndLog::record(
            $admin->role,
            $adminName ?: $admin->email,
            'Update',
            "{$action} account for {$userName} ({$user->email})."
        );

        return back()->with('status', 'User status toggled.');
    }


    /* =====================================
     | INITIAL VALIDATION (FULL ACCESS)
     |===================================== */

    // GET /admin/validation
    // Queue of students: Approved + account-limited, with academic records
    // GET /admin/initial-validation
    // GET /admin/initial-validation
    protected function studentIsReadyForApproval(User $user): bool
    {
        $academic = $user->studentAcademic;

        if (! $academic) return false;

        if (! $academic->expected_grad_year || ! $academic->program_id || ! $academic->year_level) {
            return false;
        }

        if (empty($academic->certificate_of_registration_path)) return false;

        if (! Storage::disk('public')->exists($academic->certificate_of_registration_path)) {
            return false;
        }

        $hasLeadership = StudentLeadership::where('user_id', $user->id)
            ->whereNotNull('leadership_type_id')
            ->whereNotNull('position_id')
            ->whereNotNull('term')
            ->exists();

        return $hasLeadership;
    }


    public function initialValidationQueue(Request $request)
    {
        $search = trim((string) $request->input('q', ''));
        $role   = $request->input('role'); // student | assessor | "" (all)

        $users = User::query()
            ->where('status', User::STATUS_APPROVED)
            ->whereIn('role', [User::ROLE_STUDENT, User::ROLE_ASSESSOR])

            // ✅ Queue rules:
            // - Students: approved + account-limited + NEW profile completion only (profile_completed=false)
            // - Assessors: approved + incomplete (profile_completed=false OR missing assessorInfo)
            ->where(function ($q) {
                $q->where(function ($s) {
                    $s->where('role', User::ROLE_STUDENT)
                        ->where('is_account_limited', true)
                        // IMPORTANT: exclude academic revalidation updates
                        ->where(function ($p) {
                            $p->where('profile_completed', false)
                                ->orWhereNull('profile_completed');
                        });
                })
                    ->orWhere(function ($a) {
                        $a->where('role', User::ROLE_ASSESSOR)
                            ->where(function ($x) {
                                $x->where('profile_completed', false)
                                    ->orWhereDoesntHave('assessorInfo');
                            });
                    });
            })

            // ✅ Role filter (optional)
            ->when(!empty($role), fn($q) => $q->where('role', $role))

            // ✅ Search: name/email/contact (matches your new unified table)
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . $search . '%';

                $q->where(function ($inner) use ($like) {
                    $inner->where('first_name', 'like', $like)
                        ->orWhere('middle_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('contact', 'like', $like);
                });
            })

            // ✅ Needed by your View Details modal
            ->with([
                'studentAcademic.program.college',
                'assessorInfo',
                'studentLeaderships.leadershipType',
                'studentLeaderships.position',
                'studentLeaderships.cluster',
                'studentLeaderships.organization',
            ])

            ->orderByDesc('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.initial-validation', compact('users', 'search'));
    }

    // POST /admin/validation/{user}/approve
    // This is where we check BOTH academic + leadership and grant FULL access
    // POST /admin/validation/{user}/approve
    public function approveInitialValidation(User $user)
    {
        // Only approved accounts can be validated here (both roles)
        if ($user->status !== User::STATUS_APPROVED) {
            return back()->withErrors([
                'validation' => 'Only approved accounts can be validated here.',
            ]);
        }

        /* ============================
     | STUDENT BRANCH (Full Access)
     * ============================ */
        if ($user->isStudent()) {

            // Must already be approved but LIMITED
            if (! $user->is_account_limited) {
                return back()->withErrors([
                    'validation' => 'Only approved but limited student accounts can be validated here.',
                ]);
            }

            /** @var StudentAcademic|null $academic */
            // If approving a student, DO NOT create student_academic here.
            // Only update it if it already exists (in case of re-approval/re-import).
            $academic = StudentAcademic::where('user_id', $user->id)->first();

            if ($academic) {
                if (
                    empty($academic->eligibility_status) ||
                    in_array($academic->eligibility_status, ['needs_revalidation', 'under_review', 'ineligible'], true)
                ) {
                    $academic->eligibility_status = 'eligible';
                    $academic->save();
                }
            }
            if (! $academic) {
                return back()->withErrors([
                    'academic' => 'No academic record found yet. Ask the student to complete Academic Info first.',
                ]);
            }

            // Academic completeness checks
            if (! $academic->expected_grad_year || ! $academic->program_id || ! $academic->year_level) {
                return back()->withErrors([
                    'academic' => 'Academic details are incomplete. Ask the student to update their academic information.',
                ]);
            }

            // COR requirement using student_academic.certificate_of_registration_path
            if (empty($academic->certificate_of_registration_path)) {
                return back()->withErrors([
                    'cor' => 'Student has no Certificate of Registration (COR) uploaded. Cannot approve.',
                ]);
            }

            // Ensure file exists in disk (optional but safer)
            if (! Storage::disk('public')->exists($academic->certificate_of_registration_path)) {
                return back()->withErrors([
                    'cor' => 'COR file path exists but file is missing on server. Ask student to re-upload.',
                ]);
            }

            // Leadership requirement
            $hasLeadership = false;
            if (Schema::hasTable('student_leaderships')) {
                $hasLeadership = StudentLeadership::where('user_id', $user->id)
                    ->whereNotNull('leadership_type_id')
                    ->whereNotNull('position_id')
                    ->whereNotNull('term')
                    ->where(function ($q) {
                        $q->whereNull('organization_id')
                            ->orWhereNotNull('organization_id');
                    })
                    ->exists();
            }

            if (! $hasLeadership) {
                return back()->withErrors([
                    'leadership' => 'Cannot approve: leadership information incomplete (missing type or term).'
                ]);
            }

            // Mark academic eligibility if needed
            if ($academic->eligibility_status !== StudentAcademic::ELIGIBILITY_ELIGIBLE) {
                $academic->eligibility_status = StudentAcademic::ELIGIBILITY_ELIGIBLE;
            }

            // Track validation timestamp if column exists
            if (Schema::hasColumn($academic->getTable(), 'validated_at') && empty($academic->validated_at)) {
                $academic->validated_at = now();
            }

            $academic->save();

            // Grant FULL access
            $user->is_account_limited = false;

            // Mark profile as completed once full access is granted
            if (Schema::hasColumn($user->getTable(), 'profile_completed')) {
                $user->profile_completed = true;
            }
            // Generate user_code only at final approval stage
            if (empty($user->user_code)) {
                $user->ensureUserCode(); // method you added in User model
            }

            $user->save();

            // Email: initial validation approved
            try {
                Mail::to($user->email)->send(new InitialValidationApprovedMail($user));
            } catch (\Throwable $e) {
                \Log::warning('Failed to send InitialValidationApprovedMail', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }

            // System log
            $admin     = Auth::user();
            $adminName = trim($admin->first_name . ' ' . ($admin->middle_name ? $admin->middle_name . ' ' : '') . $admin->last_name);
            $userName  = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);

            SystemMonitoringAndLog::record(
                $admin->role,
                $adminName ?: $admin->email,
                'Update',
                "Approved initial academic + leadership validation for {$userName} ({$user->email}). Full access granted."
            );

            return back()->with('status', 'Student validated successfully. Full SLEA access granted.');
        }

        /* ============================
     | ASSESSOR BRANCH (Completion)
     * ============================ */
        if ($user->isAssessor()) {

            // Must have assessor info (office_unit + position)
            $info = $user->assessorInfo;

            if (! $info) {
                return back()->withErrors([
                    'assessor' => 'No assessor profile found. Ask the assessor to complete their assessor information.',
                ]);
            }

            if (empty($info->office_unit) || empty($info->position)) {
                return back()->withErrors([
                    'assessor' => 'Assessor information is incomplete (Office/Unit and Position are required).',
                ]);
            }

            // Mark profile completed and grant full access
            if (Schema::hasColumn($user->getTable(), 'profile_completed')) {
                $user->profile_completed = true;
            }
            
            // Grant FULL access (same as students)
            $user->is_account_limited = false;

            $user->save();

            // (Optional) email to assessor here if you want
            // Mail::to($user->email)->send(new AssessorValidationApprovedMail($user));

            // System log
            $admin     = Auth::user();
            $adminName = trim($admin->first_name . ' ' . ($admin->middle_name ? $admin->middle_name . ' ' : '') . $admin->last_name);
            $userName  = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);

            SystemMonitoringAndLog::record(
                $admin->role,
                $adminName ?: $admin->email,
                'Update',
                "Approved assessor profile completion for {$userName} ({$user->email})."
            );

            return back()->with('status', 'Assessor validated successfully.');
        }

        // Any other role is not allowed
        abort(403);
    }


    // POST /admin/validation/{user}/reject
    public function rejectInitialValidation(User $user)
    {
        // Only approved accounts can be rejected here (both roles)
        if ($user->status !== User::STATUS_APPROVED) {
            return back()->withErrors([
                'validation' => 'Only approved accounts can be rejected here.',
            ]);
        }

        /* ============================
     | STUDENT BRANCH (Reject)
     * ============================ */
        if ($user->isStudent()) {

            if (! $user->is_account_limited) {
                return back()->withErrors([
                    'validation' => 'Only approved but limited student accounts can be validated here.',
                ]);
            }

            $academic = StudentAcademic::where('user_id', $user->id)->first();

            if (! $academic) {
                return back()->withErrors([
                    'academic' => 'No academic record found yet. Ask the student to complete Academic Info first.',
                ]);
            }

            // Keep them limited, mark for re-check (choose one)
            $academic->eligibility_status = 'needs_revalidation'; // or 'under_review'
            if (Schema::hasColumn($academic->getTable(), 'validated_at')) {
                $academic->validated_at = null;
            }
            $academic->save();

            // Stay LIMITED
            $user->is_account_limited = true;
            if (Schema::hasColumn($user->getTable(), 'profile_completed')) {
                $user->profile_completed = false; // optional: depends on your flow
            }
            $user->save();

            // Email: rejected
            try {
                Mail::to($user->email)->send(new InitialValidationRejectedMail($user));
            } catch (\Throwable $e) {
                \Log::warning('Failed to send InitialValidationRejectedMail', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }

            // Log
            $admin     = Auth::user();
            $adminName = trim($admin->first_name . ' ' . ($admin->middle_name ? $admin->middle_name . ' ' : '') . $admin->last_name);
            $userName  = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);

            SystemMonitoringAndLog::record(
                $admin->role,
                $adminName ?: $admin->email,
                'Update',
                "Rejected initial validation for {$userName} ({$user->email}). Account remains limited."
            );

            return back()->with('status', 'Initial validation rejected. Student remains account-limited.');
        }


        /* ============================
     | ASSESSOR BRANCH (Reject)
     * ============================ */
        if ($user->isAssessor()) {

            // Assessor rejection = keep profile incomplete
            if (Schema::hasColumn($user->getTable(), 'profile_completed')) {
                $user->profile_completed = false;
            }
            if (empty($user->user_code)) {
                $user->ensureUserCode();
            }

            $user->save();

            // Optional: email if you have one
            // Mail::to($user->email)->send(new AssessorValidationRejectedMail($user));

            $admin     = Auth::user();
            $adminName = trim($admin->first_name . ' ' . ($admin->middle_name ? $admin->middle_name . ' ' : '') . $admin->last_name);
            $userName  = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);

            SystemMonitoringAndLog::record(
                $admin->role,
                $adminName ?: $admin->email,
                'Update',
                "Rejected assessor profile completion for {$userName} ({$user->email})."
            );

            return back()->with('status', 'Assessor validation rejected. Profile remains incomplete.');
        }

        abort(403);
    }



    /* =========================
     | REVALIDATION (no leadership)
     * ========================= */

    // GET /admin/revalidation
    public function revalidationQueue()
    {
        // Use Eloquent so we can show more info and re-use relationships
        // Include both 'needs_revalidation' and 'under_review' statuses
        // Refresh to ensure we get the latest COR paths
        $rows = StudentAcademic::with(['user'])
            // Revalidation is ONLY for already-validated students (academic updates), not new profile completion
            ->whereHas('user', function ($q) {
                $q->where('profile_completed', true);
            })
            ->whereIn('eligibility_status', ['needs_revalidation', 'under_review'])
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        // Ensure all records are fresh (no stale data) - this ensures latest COR is shown
        $rows->getCollection()->each(function ($row) {
            $row->refresh();
        });

        return view('admin.revalidation', compact('rows'));
    }

    // POST /admin/revalidation/{user}/approve
    public function approveRevalidation(User $user)
    {
        if (! $user->isStudent()) {
            abort(403);
        }

        /** @var StudentAcademic $academic */
        $academic = StudentAcademic::where('user_id', $user->id)->firstOrFail();

        // Must be in revalidation-required state
        if (! in_array($academic->eligibility_status, ['needs_revalidation', 'under_review'], true)) {
            return back()->withErrors([
                'revalidation' => 'This student is not marked for revalidation.',
            ]);
        }

        if (! $user->hasCor()) {
            return back()->withErrors(['cor' => 'Student has no COR uploaded. Cannot approve.']);
        }


        /*
         * Revalidation is purely about academic eligibility + COR.
         * Leadership info is NOT required here.
         */

        // (Optional) Must have complete academic info
        if (! $academic->expected_grad_year || ! $academic->program_id || ! $academic->year_level) {
            return back()->withErrors([
                'academic' => 'Academic details incomplete. Require student to update before revalidation.',
            ]);
        }

        // Approve revalidation
        $academic->update([
            'eligibility_status' => 'eligible',
            'revalidated_at'     => now(),
        ]);

        // Restore full access if they were limited
        if ($user->is_account_limited) {
            $user->is_account_limited = false;
            $user->save();
        }

        // Email: revalidation approved
        try {
            Mail::to($user->email)->send(new RevalidationApprovedMail($user));
        } catch (\Throwable $e) {
            \Log::warning('Failed to send RevalidationApprovedMail', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        return back()->with('status', 'Revalidation approved. Student is now eligible and has full access.');
    }

    // POST /admin/revalidation/{user}/reject
    public function rejectRevalidation(User $user)
    {
        if (! $user->isStudent()) {
            abort(403);
        }

        /** @var StudentAcademic $academic */
        $academic = StudentAcademic::where('user_id', $user->id)->firstOrFail();

        if (! in_array((string) $academic->eligibility_status, ['needs_revalidation', 'under_review'], true)) {
            return back()->withErrors([
                'revalidation' => 'Only students flagged for revalidation can be rejected.',
            ]);
        }

        // Mark fully ineligible
        $academic->update([
            'eligibility_status' => 'ineligible',
        ]);

        // Limit SLEA access
        $user->is_account_limited = true;
        $user->save();

        // Email: revalidation rejected
        try {
            Mail::to($user->email)->send(new RevalidationRejectedMail($user));
        } catch (\Throwable $e) {
            \Log::warning('Failed to send RevalidationRejectedMail', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        return back()->with('status', 'Revalidation rejected. Student marked ineligible and account access is limited.');
    }

    public function viewStudentCor(User $user)
    {
        if (! $user->isStudent()) {
            abort(403);
        }

        // Refresh to get the latest COR (including any updates from revalidation)
        $academic = StudentAcademic::where('user_id', $user->id)->first();
        
        if (!$academic) {
            abort(404, 'No academic record found for this student.');
        }
        
        // Refresh the model to ensure we have the latest data
        $academic->refresh();

        if (empty($academic->certificate_of_registration_path)) {
            abort(404, 'No COR uploaded for this student.');
        }

        $path = $academic->certificate_of_registration_path;

        // Safety check
        if (str_contains($path, '..')) {
            abort(400, 'Invalid file path.');
        }

        // ✅ USE PUBLIC DISK (MATCH UPLOAD)
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            abort(404, 'COR file not found on server.');
        }

        // Inline view (PDF / image)
        return response()->file(
            $disk->path($path),
            [
                'Content-Disposition' => 'inline; filename="COR_' . $academic->student_number . '.' . pathinfo($path, PATHINFO_EXTENSION) . '"'
            ]
        );
    }


    /* =========================
     | SYSTEM PAGES (AWARDS REPORT)
     * ========================= */

    public function awardReportDashboard(Request $request)
    {
        $rows = $this->buildAwardReportRows($request);

        $stats = [
            'total'     => $rows->count(),
            'gold'      => $rows->where('award_level', 'gold')->count(),
            'silver'    => $rows->where('award_level', 'silver')->count(),
            'qualified' => $rows->where('award_level', 'qualified')->count(),
            'tracking'  => $rows->whereIn('award_level', ['tracking', 'not_qualified'])->count(),
        ];

        $page    = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;

        $pageItems = $rows
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        $students = new LengthAwarePaginator(
            $pageItems,
            $rows->count(),
            $perPage,
            $page,
            [
                'path'  => request()->url(),
                'query' => request()->query(),
            ]
        );

        $colleges = class_exists(College::class)
            ? College::orderBy('name')->get()
            : collect();

        $programs = class_exists(Program::class)
            ? Program::orderBy('name')->get()
            : collect();

        $batches = [];

        return view('admin.award-report', [
            'students' => $students,
            'stats'    => $stats,
            'colleges' => $colleges,
            'programs' => $programs,
            'batches'  => $batches,
        ]);
    }

    public function exportAwardReportPdf(Request $request)
    {
        $rows = $this->buildAwardReportRows($request);

        $students = $rows;

        $pdf = Pdf::loadView('admin.pdf.award-report', [
            'students'    => $students,
            'generatedAt' => now(),
        ])->setPaper('A4', 'portrait');

        return $pdf->download('slea-awards-report.pdf');
    }

    protected function buildAwardReportRows(Request $request): \Illuminate\Support\Collection
    {
        if (
            ! Schema::hasTable('student_academic') ||
            ! Schema::hasTable('assessor_final_reviews') ||
            ! Schema::hasTable('users')
        ) {
            return collect();
        }

        // Sync student_academic status with latest admin final decisions before building the report
        if (Schema::hasTable('final_reviews')) {
            $finals = DB::table('final_reviews as fr')
                ->join('assessor_final_reviews as afr', 'afr.id', '=', 'fr.assessor_final_review_id')
                ->join('student_academic as sa', 'sa.user_id', '=', 'afr.student_id')
                ->select('sa.user_id', 'fr.decision', 'sa.slea_application_status')
                ->get();

            foreach ($finals as $row) {
                $expected = $row->decision === 'approved' ? 'qualified' : 'not_qualified';
                if ($row->slea_application_status !== $expected) {
                    DB::table('student_academic')
                        ->where('user_id', $row->user_id)
                        ->update(['slea_application_status' => $expected]);
                }
            }
        }

        $hasSleaStatus = Schema::hasColumn('student_academic', 'slea_application_status');

        $selectFields = [
            'sa.user_id',
            'sa.student_number',
            'u.first_name',
            'u.last_name',
            'u.middle_name',
            'p.name  as program_name',
            'p.code  as program_code',
            'c.name  as college_name',
            'c.code  as college_code',
            'afr.total_score',
            'afr.max_possible as max_points',
        ];

        if ($hasSleaStatus) {
            $selectFields[] = 'sa.slea_application_status';
        }

        $query = DB::table('student_academic as sa')
            ->select($selectFields)
            ->join('users as u', 'u.id', '=', 'sa.user_id')
            ->leftJoin('assessor_final_reviews as afr', function ($join) {
                $join->on('afr.student_id', '=', 'sa.user_id')
                    ->whereIn('afr.status', ['queued_for_admin', 'finalized']);
            })
            ->leftJoin('programs as p', 'p.id', '=', 'sa.program_id')
            ->leftJoin('colleges as c', 'c.id', '=', 'sa.college_id')
            ->where('u.role', User::ROLE_STUDENT)
            ->whereNotNull('afr.total_score')
            ->whereNotNull('afr.max_possible');

        if ($hasSleaStatus) {
            $query->where('sa.slea_application_status', 'qualified');
        }

        $searchTerm = trim((string) ($request->input('q') ?: $request->input('search', '')));
        if ($searchTerm) {
            $query->where(function ($sub) use ($searchTerm) {
                $sub->where('sa.student_number', 'like', "%{$searchTerm}%")
                    ->orWhere('u.first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('u.last_name', 'like', "%{$searchTerm}%");
            });
        }

        if ($collegeId = $request->input('college_id')) {
            $query->where('sa.college_id', $collegeId);
        }

        if ($programId = $request->input('program_id')) {
            $query->where('sa.program_id', $programId);
        }

        $rows = $query->get();

        $mapped = $rows->map(function ($row) use ($hasSleaStatus) {
            $totalScore = (float) ($row->total_score ?? 0);
            $maxPoints  = (float) ($row->max_points ?? 0);

            $percent = $maxPoints > 0
                ? round(($totalScore / $maxPoints) * 100, 2)
                : 0.0;

            if ($percent >= 90) {
                $awardLevel = 'gold';
            } elseif ($percent >= 85) {
                $awardLevel = 'silver';
            } elseif ($percent >= 80) {
                $awardLevel = 'qualified';
            } elseif ($percent >= 70) {
                $awardLevel = 'tracking';
            } else {
                $awardLevel = 'not_qualified';
            }

            $sleaStatus = $hasSleaStatus ? ($row->slea_application_status ?? null) : null;
            switch ($sleaStatus) {
                case 'qualified':
                    $statusLabel = 'SLEA Qualified';
                    break;
                case 'pending_administrative_validation':
                    $statusLabel = 'For Final Review';
                    break;
                default:
                    $statusLabel = 'Tracking';
                    break;
            }

            $college = new \stdClass();
            $college->name = $row->college_name;
            $college->code = $row->college_code;

            $program = new \stdClass();
            $program->name = $row->program_name;
            $program->code = $row->program_code;

            $academic = new \stdClass();
            $academic->student_id = $row->student_number;
            $academic->college    = $college;
            $academic->program    = $program;

            $fullNameParts = array_filter([
                $row->last_name,
                ', ',
                $row->first_name,
                $row->middle_name ? ' ' . $row->middle_name : null,
            ]);
            $fullName = implode('', $fullNameParts);

            $user = new \stdClass();
            $user->id              = $row->user_id;
            $user->full_name       = $fullName;
            $user->studentAcademic = $academic;
            $user->student_id      = $row->student_number;

            $record = new \stdClass();
            $record->user            = $user;
            $record->total_points    = $percent;
            $record->award_level     = $awardLevel;
            $record->slea_status     = $statusLabel;
            $record->raw_total_score = $totalScore;
            $record->raw_max_points  = $maxPoints;
            $record->program_code    = $row->program_code;
            $record->program_name    = $row->program_name;
            $record->college_name    = $row->college_name;
            $record->student_number  = $row->student_number;

            return $record;
        });

        if ($request->filled('award_level')) {
            $level  = $request->input('award_level');
            $mapped = $mapped
                ->filter(fn($row) => $row->award_level === $level)
                ->values();
        }

        if ($request->filled('min_score')) {
            $threshold = (int) $request->input('min_score');
            $mapped    = $mapped
                ->filter(fn($row) => $row->total_points >= $threshold)
                ->values();
        }

        return $mapped->sortByDesc('total_points')->values();
    }

    public function awardReport(Request $request)
    {
        $college = $request->query('college');
        $program = $request->query('program');
        $search  = $request->query('search');

        $allRows = $this->buildAwardReportRows($request);

        $allStudents = $allRows->map(function ($row) {
            $score = $row->raw_total_score ?? 0;
            $max   = $row->raw_max_points ?? 0;
            return [
                'id'             => $row->user->id ?? 0,
                'name'           => $row->user->full_name ?? 'N/A',
                'student_id'     => $row->student_number ?? 'N/A',
                'college'        => $row->college_name ?? 'N/A',
                'program'        => $row->program_name ?? 'N/A',
                'points'         => round($score, 2),
                'max_points'     => round($max, 2),
                'points_display' => number_format($score, 2) . '/' . number_format($max, 2),
            ];
        })->toArray();

        $filteredStudents = $allStudents;

        if ($college) {
            $filteredStudents = array_filter($filteredStudents, function ($student) use ($college) {
                return $student['college'] === $college;
            });
        }

        if ($program) {
            $filteredStudents = array_filter($filteredStudents, function ($student) use ($program) {
                return $student['program'] === $program;
            });
        }

        if ($search) {
            $searchTerm = strtolower($search);
            $filteredStudents = array_filter($filteredStudents, function ($student) use ($searchTerm) {
                return strpos(strtolower($student['name']), $searchTerm) !== false
                    || strpos(strtolower($student['student_id']), $searchTerm) !== false;
            });
        }

        $filteredStudents = array_values($filteredStudents);

        $currentPage = $request->get('page', 1);
        $perPage     = 10;

        $total  = count($filteredStudents);
        $offset = ($currentPage - 1) * $perPage;
        $items  = array_slice($filteredStudents, $offset, $perPage);

        $students = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.award-report', compact('students'));
    }

    public function exportAwardReport(Request $request)
    {
        $college = $request->query('college');
        $program = $request->query('program');
        $search  = $request->query('search');

        $allRows = $this->buildAwardReportRows($request);

        $filteredStudents = $allRows->map(function ($row) {
            $score = $row->raw_total_score ?? 0;
            $max   = $row->raw_max_points ?? 0;
            return [
                'id'             => $row->user->id ?? 0,
                'name'           => $row->user->full_name ?? 'N/A',
                'student_id'     => $row->student_number ?? 'N/A',
                'college'        => $row->college_name ?? 'N/A',
                'program'        => $row->program_name ?? 'N/A',
                'points'         => round($score, 2),
                'max_points'     => round($max, 2),
                'points_display' => number_format($score, 2) . '/' . number_format($max, 2),
            ];
        })->toArray();

        if ($college) {
            $filteredStudents = array_filter($filteredStudents, function ($student) use ($college) {
                return $student['college'] === $college;
            });
        }

        if ($program) {
            $filteredStudents = array_filter($filteredStudents, function ($student) use ($program) {
                return $student['program'] === $program;
            });
        }

        if ($search) {
            $searchTerm = strtolower($search);
            $filteredStudents = array_filter($filteredStudents, function ($student) use ($searchTerm) {
                return strpos(strtolower($student['name']), $searchTerm) !== false
                    || strpos(strtolower($student['student_id']), $searchTerm) !== false;
            });
        }

        $filteredStudents = array_values($filteredStudents);

        $studentsCollection = collect($filteredStudents);

        $pdf = Pdf::loadView('admin.pdf.award-report', [
            'students'    => $studentsCollection,
            'generatedAt' => now(),
            'filters'     => [
                'college' => $college,
                'program' => $program,
                'search'  => $search,
            ],
        ])->setPaper('A4', 'portrait');

        return $pdf->download('slea-awards-report.pdf');
    }

    public function systemMonitoring()
    {
        return view('admin.system.monitoring');
    }
}
