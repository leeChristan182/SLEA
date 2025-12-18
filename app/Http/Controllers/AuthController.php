<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserOtp;
use App\Mail\OtpCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Models\SystemMonitoringAndLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;
use App\Models\Program;
use App\Models\Major;
use App\Models\Cluster;
use App\Models\Organization;
use App\Models\Position;
use App\Models\LeadershipType;
use App\Models\StudentLeadership;
use App\Models\College;
use App\Http\Controllers\OrganizationController;

class AuthController extends Controller
{
    /* =========================
     |  LOGIN
     * ========================= */

    public function showLogin(Request $request)
    {
        $rememberedEmail = $request->cookie('slea_remembered_email');

        // Clear OTP session data if it's for an admin account
        if (session()->has('otp_pending_user_id')) {
            $pendingUser = User::find(session('otp_pending_user_id'));
            if ($pendingUser && $pendingUser->isAdmin()) {
                session()->forget(['otp_pending_user_id', 'otp_context', 'otp_remember_me', 'otp_display_email', 'show_otp_modal']);
            }
        }

        return view('auth.login', [
            'rememberedEmail' => $rememberedEmail,
        ]);
    }

    public function authenticate(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credentials = [
            'email'    => $data['email'],
            'password' => $data['password'],
        ];

        // 1) Validate credentials (no login yet)
        if (! Auth::validate($credentials)) {
            return back()
                ->withErrors(['email' => 'Invalid credentials.'])
                ->withInput($request->only('email'));
        }

        /** @var User $user */
        $user = User::where('email', $data['email'])->firstOrFail();

        // 2) Status check
        if ($user->status !== User::STATUS_APPROVED) {
            $msg = match ($user->status) {
                User::STATUS_PENDING  => 'Your account is pending approval. Please wait for the admin to approve your account.',
                User::STATUS_REJECTED => 'Your account was rejected. Please contact the administrator.',
                User::STATUS_DISABLED => 'Your account is disabled. Please contact the administrator.',
                default               => 'Your account is not approved yet.',
            };

            return back()
                ->withErrors(['email' => $msg])
                ->withInput($request->only('email'));
        }

        // 2.5) Role check
        if ($user->role === User::ROLE_UNASSIGNED) {
            return back()
                ->withErrors(['email' => 'Your account role has not been assigned yet. Please wait for admin approval.'])
                ->withInput($request->only('email'));
        }

        // 3) Eligibility check for students (ALLOW limited users to login)
        if (
            $user->isStudent()
            && ! $user->is_account_limited
            && method_exists($user, 'canLoginToSlea')
            && ! $user->canLoginToSlea()
        ) {
            return back()
                ->withErrors(['email' => $user->loginBlockReason()])
                ->withInput($request->only('email'));
        }

        // 4) OTP required? (USE YOUR MODEL LOGIC)
        // Admin accounts never require OTP
        $otpRequired = false;

        if (!$user->isAdmin() && Schema::hasColumn('users', 'otp_last_verified_at')) {
            // ✅ uses auth.otp.login_fresh_days + handles null
            $otpRequired = $user->needsLoginOtp();
        }

        if ($otpRequired) {
            session([
                'otp_pending_user_id' => $user->id,
                'otp_context'         => 'login',
                'otp_remember_me'     => $request->boolean('remember'),
                'otp_display_email'   => $user->email,
            ]);

            $this->sendOtp($user, 'login');

            return redirect()
                ->route('login.show')
                ->with('status', 'We sent a one-time password (OTP) to your email.')
                ->with('show_otp_modal', true);
        }

        // 5) No OTP required → proceed with normal login
        $remember = $request->boolean('remember');

        Auth::login($user, $remember);
        $request->session()->regenerate();

        // tie email autofill to remember-me only
        if ($remember) {
            Cookie::queue('slea_remembered_email', $user->email, 60 * 24 * 30);
        } else {
            Cookie::queue(Cookie::forget('slea_remembered_email'));
        }

        // LOG
        $displayName = $user->full_name
            ?? trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);

        SystemMonitoringAndLog::record(
            $user->role,
            $displayName ?: $user->email,
            'Login',
            'User logged in.'
        );

        // ✅ Enforce limited flow *immediately* after login (not only via middleware)
        if ($user->is_account_limited) {

            // ===== ASSESSOR LIMITED FLOW =====
            if ($user->role === User::ROLE_ASSESSOR) {
                $assessorSubmitted =
                    $user->assessorInfo
                    && ! empty($user->assessorInfo->office_unit)
                    && ! empty($user->assessorInfo->position);

                // Not submitted → force complete requirements
                if (! $assessorSubmitted) {
                    return redirect()->route('profile.complete.assessor');
                }

                // Submitted but still limited → go to profile with waiting modal
                return redirect()
                    ->route('assessor.profile')
                    ->with('show_waiting_modal', true);
            }

            // ===== STUDENT LIMITED FLOW =====
            if ($user->role === User::ROLE_STUDENT) {
                // If you use eligibility_status to indicate submission:
                $studentSubmitted =
                    $user->studentAcademic
                    && ! empty($user->studentAcademic->eligibility_status)
                    && in_array(
                        $user->studentAcademic->eligibility_status,
                        ['under_review', 'needs_revalidation', 'eligible'],
                        true
                    );

                // Not submitted → force complete requirements
                if (! $studentSubmitted) {
                    return redirect()->route('profile.complete.student');
                }

                // Submitted but still limited → go to profile with waiting modal
                return redirect()
                    ->route('student.profile')
                    ->with('show_waiting_modal', true);
            }
        }

        return $this->redirectAfterLogin($user);
    }

    protected function redirectAfterLogin(User $user)
    {
        if ($user->role === User::ROLE_ASSESSOR) {
            $assessorSubmitted =
                $user->assessorInfo &&
                !empty($user->assessorInfo->office_unit) &&
                !empty($user->assessorInfo->position);

            if (! $assessorSubmitted) {
                return redirect()->route('profile.complete.assessor');
            }
        }

        return match ($user->role) {
            User::ROLE_ADMIN    => redirect()->route('admin.dashboard'),
            User::ROLE_ASSESSOR => redirect()->route('assessor.profile'),
            User::ROLE_STUDENT  => redirect()->route('student.profile'),
            default             => redirect()->route('login.show')
                ->withErrors(['email' => 'Your account role has not been assigned yet.']),
        };
    }



    public function logout(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user) {
            $displayName = $user->full_name ?? trim(
                $user->first_name . ' ' .
                    ($user->middle_name ? $user->middle_name . ' ' : '') .
                    $user->last_name
            );

            SystemMonitoringAndLog::record(
                $user->role,
                $displayName ?: $user->email,
                'Logout',
                'User logged out.'
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'redirect_url' => route('login'),
            ]);
        }

        return redirect()->route('login');
    }

    /* =========================
     |  REGISTER (GLOBAL)
     * ========================= */

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // ✅ FIX: use your slea.php keys
        $emailDomains = config('slea.email_domains', ['usep.edu.ph']); // array
        $phoneRegex   = config('slea.phone_regex', '/^09\d{9}$/');

        // build a regex that accepts ANY of the allowed domains
        // example: user@usep.edu.ph OR user@students.usep.edu.ph
        $domainsRegex = implode('|', array_map(fn($d) => preg_quote($d, '/'), $emailDomains));
        $emailRuleRegex = "/^[a-zA-Z0-9._%+\-]+@($domainsRegex)$/";

        $rules = [
            'last_name'   => ['required', 'string', 'max:50'],
            'first_name'  => ['required', 'string', 'max:50'],
            'middle_name' => ['nullable', 'string', 'max:50'],
            'birth_date'  => [
                'nullable',
                'date',
                'before:today',
                'after_or_equal:' . now()->subYears(100)->toDateString(),
                'before_or_equal:' . now()->subYears(15)->toDateString(),
            ],

            // NOTE: your form field is email_address, but db column is email
            // Allow re-registration if previous account was rejected
            'email_address' => [
                'required',
                'email',
                'max:100',
                "regex:$emailRuleRegex",
                Rule::unique('users', 'email')->where(function ($query) {
                    $query->where('status', '!=', User::STATUS_REJECTED);
                }),
            ],

            'contact' => [
                'required',
                'string',
                "regex:$phoneRegex",
                'max:15',
            ],

            'password'      => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'privacy_agree' => ['accepted'],
        ];

        $messages = [
            'email_address.regex'   => 'Please use a valid institutional email address.',
            'email_address.unique'  => 'This email address is already registered. Please use a different email or try logging in.',
            'contact.regex'         => 'Please enter a valid Philippine mobile number in the format 09XXXXXXXXX.',
            'privacy_agree.accepted' => 'You must agree to the Data Privacy Statement to continue.',
        ];

        $validated = $request->validate($rules, $messages);

        // normalize names
        $first  = Str::title(Str::lower(trim($validated['first_name'])));
        $last   = Str::title(Str::lower(trim($validated['last_name'])));
        $middle = isset($validated['middle_name'])
            ? Str::title(Str::lower(trim($validated['middle_name'])))
            : null;

        // check lookup tables exist
        if (!DB::table('user_roles')->where('key', User::ROLE_UNASSIGNED)->exists()) {
            $error = 'System configuration error. Please contact support.';
            \Log::error('Missing unassigned role in user_roles table');
            return back()->withErrors(['register' => $error])->withInput();
        }

        if (!DB::table('user_statuses')->where('key', User::STATUS_PENDING)->exists()) {
            $error = 'System configuration error. Please contact support.';
            \Log::error('Missing pending status in user_statuses table');
            return back()->withErrors(['register' => $error])->withInput();
        }

        DB::beginTransaction();
        try {
            // Check if a rejected user with this email exists
            $existingRejectedUser = User::where('email', $validated['email_address'])
                ->where('status', User::STATUS_REJECTED)
                ->first();

            if ($existingRejectedUser) {
                // Update the rejected user's record for re-registration
                $existingRejectedUser->update([
                    'first_name'        => $first,
                    'last_name'         => $last,
                    'middle_name'       => $middle,
                    'password'          => Hash::make($validated['password']),
                    'contact'           => $validated['contact'],
                    'birth_date'        => $validated['birth_date'] ?? null,
                    'role'              => User::ROLE_UNASSIGNED,
                    'status'            => User::STATUS_PENDING,
                    'profile_completed' => false,
                    'user_code'         => null, // Reset user code for new registration
                ]);
            } else {
                // Create a new user record
                User::create([
                    'first_name'        => $first,
                    'last_name'         => $last,
                    'middle_name'       => $middle,
                    'email'             => $validated['email_address'],
                    'password'          => Hash::make($validated['password']),
                    'contact'           => $validated['contact'],
                    'birth_date'        => $validated['birth_date'] ?? null,
                    'profile_picture_path' => null,

                    'role'              => User::ROLE_UNASSIGNED,
                    'status'            => User::STATUS_PENDING,
                    'profile_completed' => false,

                    // If you have it in DB column:
                    // 'is_account_limited' => true,
                ]);
            }

            DB::commit();

            $msg = 'Registration received. Please wait for account approval. You will receive an email notification once your account is reviewed.';

            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success'  => true,
                    'message'  => $msg,
                    'redirect' => route('login.show'),
                ], 200);
            }

            return redirect()->route('login.show')->with('status', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            $error = 'Could not complete registration. Please try again.';
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $error,
                    'errors'  => ['register' => [$error]],
                ], 500);
            }
            return back()->withErrors(['register' => $error])->withInput();
        }
    }

    /* =========================
     |  OTP / VERIFICATION
     * ========================= */

    protected function sendOtp(User $user, string $context = 'login'): void
    {
        UserOtp::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('context', $context)
            ->delete();

        $rawCode = (string) random_int(100000, 999999);

        UserOtp::create([
            'user_id'    => $user->id,
            'code_hash'  => hash('sha256', $rawCode),
            'context'    => $context,
            'attempts'   => 0,
            'expires_at' => now()->addMinutes(config('auth.otp.lifetime_minutes', 10)),
        ]);

        Mail::to($user->email)->send(
            new OtpCodeMail($user, $rawCode, $context === 'login' ? 'login' : 'password reset')
        );
    }

    public function showOtp()
    {
        return $this->showOtpForm();
    }

    public function showOtpForm()
    {
        if (!session()->has('otp_pending_user_id')) {
            return redirect()->route('login.show');
        }

        return redirect()
            ->route('login.show')
            ->with('show_otp_modal', true);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $userId  = session('otp_pending_user_id');
        $context = session('otp_context', 'login');

        if (!$userId) return redirect()->route('login.show');

        /** @var User|null $user */
        $user = User::find($userId);
        if (!$user) return redirect()->route('login.show');

        if ($context === 'login' && $user->role === User::ROLE_UNASSIGNED) {
            session()->forget(['otp_pending_user_id', 'otp_remember_me', 'otp_context', 'otp_display_email']);

            return redirect()
                ->route('login.show')
                ->withErrors(['email' => 'Your account role has not been assigned yet. Please wait for admin approval.']);
        }

        if (
            $context === 'login'
            && $user->isStudent()
            && !$user->is_account_limited
            && method_exists($user, 'canLoginToSlea')
            && !$user->canLoginToSlea()
        ) {
            return redirect()
                ->route('login.show')
                ->withErrors(['email' => $user->loginBlockReason()]);
        }

        /** @var UserOtp|null $otpRecord */
        $otpRecord = UserOtp::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('context', $context)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (!$otpRecord) {
            return redirect()
                ->route('login.show')
                ->withErrors(['otp' => 'Your OTP session has expired. Please request a new code.'])
                ->with('show_otp_modal', true);
        }

        $maxAttempts = (int) config('slea.otp_max_attempts', 5);

        if ($otpRecord->attempts >= $maxAttempts) {
            return redirect()
                ->route('login.show')
                ->withErrors(['otp' => 'Too many incorrect attempts. Please request a new code.'])
                ->with('show_otp_modal', true);
        }

        $otpRecord->attempts++;

        if (!hash_equals($otpRecord->code_hash, hash('sha256', $request->otp))) {
            $otpRecord->save();

            return redirect()
                ->route('login.show')
                ->withErrors(['otp' => 'Incorrect OTP. Please try again.'])
                ->with('show_otp_modal', true);
        }

        // ✅ success
        $otpRecord->used_at = now();
        $otpRecord->save();

        if ($context === 'login') {
            // ✅ mark OTP validated
            if (Schema::hasColumn('users', 'otp_last_verified_at')) {
                $user->otp_last_verified_at = now();
                $user->save();
            }

            $remember = session('otp_remember_me', false);
            session()->forget(['otp_pending_user_id', 'otp_remember_me', 'otp_context', 'otp_display_email']);

            Auth::login($user, $remember);
            $request->session()->regenerate();

            if ($remember) {
                Cookie::queue('slea_remembered_email', $user->email, 60 * 24 * 30);
            } else {
                Cookie::queue(Cookie::forget('slea_remembered_email'));
            }

            $displayName = $user->full_name ?? trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);

            SystemMonitoringAndLog::record(
                $user->role,
                $displayName ?: $user->email,
                'Login',
                'User logged in (OTP verified).'
            );

            return $this->redirectAfterLogin($user);
        }

        if ($context === 'password_reset') {
            session(['password_reset_user_id' => $user->id]);
            session()->forget(['otp_pending_user_id', 'otp_context']);

            return redirect()
                ->route('login.show')
                ->with('status', 'OTP verified. You can now set a new password.')
                ->with('show_reset_modal', true);
        }

        return redirect()->route('login.show');
    }

    public function resendOtp(Request $request)
    {
        $userId  = session('otp_pending_user_id');
        $context = session('otp_context', 'login');

        if (!$userId) return redirect()->route('login.show');

        /** @var User|null $user */
        $user = User::find($userId);
        if (!$user) return redirect()->route('login.show');

        $this->sendOtp($user, $context);

        return redirect()
            ->route('login.show')
            ->with('status', 'A new OTP has been sent to your email.')
            ->with('show_otp_modal', true);
    }

    /* =========================
     |  FORGOT PASSWORD (OTP)
     * ========================= */

    public function showForgotPasswordForm()
    {
        return redirect()
            ->route('login.show')
            ->with('show_forgot_modal', true);
    }

    public function sendForgotPasswordOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        /** @var User|null $user */
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()
                ->route('login.show')
                ->withErrors(['email' => 'We could not find that email address.'])
                ->withInput()
                ->with('show_forgot_modal', true);
        }

        session([
            'otp_pending_user_id' => $user->id,
            'otp_context'         => 'password_reset',
            'otp_display_email'   => $user->email,
        ]);

        $this->sendOtp($user, 'password_reset');

        return redirect()
            ->route('login.show')
            ->with('status', 'We sent a one-time password (OTP) to your email.')
            ->with('show_otp_modal', true);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $userId = session('password_reset_user_id');

        if (!$userId) {
            return redirect()
                ->route('login.show')
                ->withErrors(['email' => 'Your password reset session has expired. Please request a new OTP.'])
                ->with('show_forgot_modal', true);
        }

        /** @var User|null $user */
        $user = User::find($userId);

        if (!$user) {
            return redirect()
                ->route('login.show')
                ->withErrors(['email' => 'User not found. Please request a new OTP.'])
                ->with('show_forgot_modal', true);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        session()->forget('password_reset_user_id');

        return redirect()
            ->route('login.show')
            ->with('status', 'Your password has been updated. You can now log in.');
    }
    /* =========================
     |  AJAX DROPDOWNS & HELPERS
     * ========================= */

    public function getPrograms(Request $r)
    {
        $collegeId = (int) $r->query('college_id');
        if (!$collegeId || !Schema::hasTable('programs')) return response()->json([]);

        $rows = DB::table('programs')
            ->where('college_id', $collegeId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($rows);
    }

    public function getMajors(Request $r)
    {
        $programId = (int) $r->query('program_id');
        if (!$programId || !Schema::hasTable('majors')) return response()->json([]);

        $rows = DB::table('majors')
            ->where('program_id', $programId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($rows);
    }

    public function getCouncilPositions(Request $request)
    {
        if (!Schema::hasTable('positions')) {
            return response()->json([]);
        }

        $typeId = (int) $request->query('leadership_type_id');
        if (!$typeId) {
            return response()->json([]);
        }

        return DB::table('positions')
            ->where('leadership_type_id', $typeId)
            ->orderBy('rank_order')
            ->orderBy('name')
            ->select('id', 'name')
            ->get()
            ->map(fn($r) => [
                'id'   => $r->id,
                'name' => $r->name,
            ])
            ->values();
    }

    public function getRubricOptions(Request $request)
    {
        if (!Schema::hasTable('rubric_options')) {
            return response()->json([]);
        }

        $subsectionId = (int) $request->query('subsection_id');
        if (!$subsectionId) {
            return response()->json([]);
        }

        return DB::table('rubric_options')
            ->where('sub_section_id', $subsectionId)
            ->orderBy('order_no')
            ->orderBy('label')
            ->select('id', 'label as name')
            ->get()
            ->map(fn($r) => [
                'id'   => $r->id,
                'name' => $r->name,
            ])
            ->values();
    }


    protected function councilOrgNames(): array
    {
        return [
            'University Student Government (USG)',
            'Obrero Student Council (OSC)',
            'Local Council (LC)',
            'Council of Clubs and Organizations (CCO)',
            'Local Government Unit (LGU)',
            'League of Class Mayors (LCM)',
        ];
    }

    /* =========================
        - public function getPositions(Request $r)
    {
        $orgId = (int) $r->query('organization_id');
        if (!$orgId || !Schema::hasTable('organization_position')) return response()->json([]);

        $rows = DB::table('organization_position as op')
            ->join('positions as p', 'p.id', '=', 'op.position_id')
            ->where('op.organization_id', $orgId)
            ->orderBy('p.name')
            ->get(['p.id', 'p.name']);

        return response()->json($rows);
    }
     * ========================= */

    public function getClusters(Request $request)
    {
        if (!Schema::hasTable('clusters')) return response()->json([]);

        $rows = DB::table('clusters')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($rows);
    }

    public function getCouncilOrgs(Request $request)
    {
        $leadershipTypeId = $request->input('leadership_type_id');

        if (!Schema::hasTable('organizations')) {
            return response()->json([]);
        }

        $orgs = DB::table('organizations')
            ->when($leadershipTypeId, function ($q) use ($leadershipTypeId) {
                $q->where('leadership_type_id', $leadershipTypeId);
            })
            ->orderBy('name')
            ->pluck('name', 'id');

        return response()->json($orgs);
    }

    public function getOrganizations(Request $request)
    {
        if (!Schema::hasTable('organizations')) return response()->json([]);

        $clusterId = (int) $request->input('cluster_id');

        $q = DB::table('organizations')->orderBy('name');

        if ($clusterId) $q->where('cluster_id', $clusterId);

        $rows = $q->get(['id', 'name']);

        return response()->json($rows);
    }

    public function getLeadershipTypes()
    {
        if (!Schema::hasTable('leadership_types')) return response()->json([]);

        $rows = DB::table('leadership_types')
            ->select('id', 'name', 'key', 'requires_org')
            ->orderBy('name')
            ->get();

        return response()->json($rows);
    }

    public function getAcademicsMap()
    {
        if (!Schema::hasTable('programs') || !Schema::hasTable('majors')) {
            return response()->json(['programsByCollege' => [], 'majorsByProgram' => []]);
        }

        $programs = DB::table('programs')->select('id', 'college_id', 'name')->orderBy('name')->get();
        $majors   = DB::table('majors')->select('id', 'program_id', 'name')->orderBy('name')->get();

        $pMap = [];
        foreach ($programs as $p) {
            $pMap[$p->college_id][] = ['id' => $p->id, 'name' => $p->name];
        }

        $mMap = [];
        foreach ($majors as $m) {
            $mMap[$m->program_id][] = ['id' => $m->id, 'name' => $m->name];
        }

        return response()->json([
            'programsByCollege' => $pMap,
            'majorsByProgram'   => $mMap,
        ]);
    }

    /* =========================
     |  PRIVATE HELPERS
     * ========================= */

    private function getCollegesList()
    {
        if (Schema::hasTable('colleges')) {
            $cols = Schema::getColumnListing('colleges');

            $nameCol = in_array('college_name', $cols) ? 'college_name'
                : (in_array('name', $cols) ? 'name' : null);

            if ($nameCol) {
                return DB::table('colleges')
                    ->select(['id', DB::raw("$nameCol as college_name")])
                    ->whereNotNull($nameCol)
                    ->orderBy($nameCol)
                    ->get();
            }
            return collect();
        }

        if (Schema::hasTable('colleges_programs_majors')) {
            return DB::table('colleges_programs_majors')
                ->selectRaw('MIN(rowid) AS id, college_name')
                ->whereNotNull('college_name')
                ->groupBy('college_name')
                ->orderBy('college_name')
                ->get();
        }

        return collect();
    }

    private function getLeadershipTypesList()
    {
        if (!Schema::hasTable('leadership_types')) return collect();

        // Order by the same sequence as defined in LeadershipTypeSeeder
        return DB::table('leadership_types')
            ->select('id', 'name', 'key', 'requires_org')
            ->orderByRaw("CASE `key`
                WHEN 'usg' THEN 1
                WHEN 'osc' THEN 2
                WHEN 'lc' THEN 3
                WHEN 'cco' THEN 4
                WHEN 'sco' THEN 5
                WHEN 'lgu' THEN 6
                WHEN 'lcm' THEN 7
                WHEN 'eap' THEN 8
                ELSE 99
            END")
            ->get();
    }
    private function computeExpectedGradYear(string $studentId, int $yearLevel): int
    {
        if (preg_match('/^(\d{4})/', $studentId, $m)) {
            $entryYear = (int) $m[1];
        } else {
            $entryYear = (int) now()->format('Y') - max(0, $yearLevel - 1);
        }

        $defaultDuration = 4;
        return $entryYear + $defaultDuration;
    }
}
