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
use Illuminate\Support\Facades\Cookie; // ⭐ NEW

class AuthController extends Controller
{
    /* =========================
     |  LOGIN
     * ========================= */
    public function showLogin(Request $request) // ⭐ changed to accept Request
    {
        // ⭐ NEW: read remembered email from cookie (if any)
        $rememberedEmail = $request->cookie('slea_remembered_email');

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
        if (!Auth::validate($credentials)) {
            return back()
                ->withErrors(['email' => 'Invalid credentials.'])
                ->withInput($request->only('email'));
        }

        /** @var \App\Models\User $user */
        $user = User::where('email', $data['email'])->firstOrFail();

        // 2) Account status check
        if ($user->status !== 'approved') {
            return back()
                ->withErrors(['email' => 'Your account is not approved yet.'])
                ->withInput($request->only('email'));
        }

        // 3) Optional: SLEA eligibility check for students (same logic as in verifyOtp)
        if (
            method_exists($user, 'isStudent')
            && method_exists($user, 'canLoginToSlea')
            && $user->isStudent()
            && !$user->canLoginToSlea()
        ) {
            return back()
                ->withErrors(['email' => $user->loginBlockReason()])
                ->withInput($request->only('email'));
        }

        // 4) Decide if OTP is required (first login or forced by making this column null)
        $otpRequired = false;

        if (Schema::hasColumn('users', 'otp_last_verified_at')) {
            // First-ever login OR you manually reset otp_last_verified_at to null
            if (is_null($user->otp_last_verified_at)) {
                $otpRequired = true;
            }
        }

        if ($otpRequired) {
            // --- OTP FLOW (do NOT log in yet) ---

            // Store who is pending OTP
            session([
                'otp_pending_user_id' => $user->id,
                'otp_context'         => 'login',
                'otp_remember_me'     => $request->boolean('remember'), // already there
                'otp_display_email'   => $user->email,
            ]);

            // Generate + email OTP
            $this->sendOtp($user, 'login');

            return redirect()
                ->route('login.show')
                ->with('status', 'We sent a one-time password (OTP) to your email.')
                ->with('show_otp_modal', true);
        }

        // 5) No OTP required → proceed with normal login
        $remember = $request->boolean('remember');  // ⭐ NEW: store once

        Auth::login($user, $remember);
        $request->session()->regenerate();

        // ⭐ NEW: tie email "autofill" to remember-me only
        if ($remember) {
            // store for 30 days (60 min * 24 hours * 30 days)
            Cookie::queue('slea_remembered_email', $user->email, 60 * 24 * 30);
        } else {
            Cookie::queue(Cookie::forget('slea_remembered_email'));
        }

        // 🔹 SYSTEM LOG: LOGIN
        $displayName = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);

        SystemMonitoringAndLog::record(
            $user->role,               // 'admin', 'assessor', 'student'
            $displayName ?: $user->email,
            'Login',
            'User logged in.'
        );

        return $this->redirectAfterLogin($user);
    }


    protected function redirectAfterLogin(User $user)
    {
        return match ($user->role) {
            User::ROLE_ADMIN    => redirect()->route('admin.profile'),
            User::ROLE_ASSESSOR => redirect()->route('assessor.profile'),
            default             => redirect()->route('student.profile'),
        };
    }

    public function logout(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user) {
            $displayName = trim(
                $user->first_name . ' ' .
                    ($user->middle_name ? $user->middle_name . ' ' : '') .
                    $user->last_name
            );

            // 🔹 SYSTEM LOG: LOGOUT
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

        // (Optional) you *can* clear the remember-email cookie on manual logout if you want:
        // Cookie::queue(Cookie::forget('slea_remembered_email'));

        // ✅ JSON-friendly response for AJAX / fetch() calls
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'redirect_url' => route('login'),
            ]);
        }

        // Fallback for normal form POST
        return redirect()->route('login');
    }

    /* =========================
     |  REGISTER (STUDENT)
     * ========================= */
    public function showRegister()
    {
        $colleges        = $this->getCollegesList();
        $leadershipTypes = $this->getLeadershipTypesList();

        return view('auth.register', compact('colleges', 'leadershipTypes'));
    }

    public function register(Request $request)
    {
        $rules = [
            // Step 1
            'last_name'     => ['required', 'string', 'max:50'],
            'first_name'    => ['required', 'string', 'max:50'],
            'middle_name'   => ['nullable', 'string', 'max:50'],
            'birth_date' => [
                'nullable',
                'date',
                'before:today',
                'after_or_equal:' . now()->subYears(100)->toDateString(),
                'before_or_equal:' . now()->subYears(15)->toDateString(),
            ],
            'email_address' => [
                'required',
                'email',
                'max:100',
                'regex:/^[a-zA-Z0-9._%+\-]+@usep\.edu\.ph$/',
                Rule::unique('users', 'email'),
            ],
            'contact'       => ['required', 'string', 'regex:/^09\d{9}$/', 'max:15'],

            // Step 2
            'student_id'    => [
                'required',
                'string',
                'max:30',
                // NOTE: this targets the table/column you used before the merge
                Rule::unique('student_academic', 'student_number'),
            ],
            'college_id'    => ['required', 'integer', 'exists:colleges,id'],
            'program_id'    => ['required', 'integer', 'exists:programs,id'],
            'major_id'      => ['nullable', 'integer', 'exists:majors,id'],
            'year_level'    => ['required', 'in:1,2,3,4,5,6,7,8'],

            // Step 3
            'leadership_type_id' => ['required', 'integer', 'exists:leadership_types,id'],
            'position_id'        => ['required', 'integer', 'exists:positions,id'],
            'term'               => ['required', 'string', 'max:25'],
            'issued_by'          => ['required', 'string', 'max:150'],
            'leadership_status'  => ['required', 'in:Active,Inactive'],

            // Step 4
            'password'      => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'privacy_agree' => ['accepted'],
        ];

        $messages = [
            'email_address.regex'  => 'Please use a valid @usep.edu.ph email address.',
            'email_address.unique' => 'This email address is already registered. Please use a different email or try logging in.',
            'student_id.unique'    => 'This student ID is already registered. Please check your student ID or contact support if you believe this is an error.',
            'contact.regex'        => 'Please enter a valid Philippine mobile number in the format 09XXXXXXXXX.',
        ];


        $validated = $request->validate($rules, $messages);
        // Normalize names to "Proper Case"
        $validated['first_name']  = Str::title(Str::lower(trim($validated['first_name'])));
        $validated['last_name']   = Str::title(Str::lower(trim($validated['last_name'])));
        if (!empty($validated['middle_name'])) {
            $validated['middle_name'] = Str::title(Str::lower(trim($validated['middle_name'])));
        }

        // Normalize contact (strip spaces/dashes but keep 09 format)
        $digits = preg_replace('/\D/', '', $validated['contact']); // keep only numbers

        // If user typed 9XXXXXXXXX or +639XXXXXXXXX, convert to 09XXXXXXXXX
        if (Str::startsWith($digits, '63') && strlen($digits) === 12) {
            // 63 + 10 digits -> 0 + 10 digits
            $digits = '0' . substr($digits, 2);
        } elseif (Str::startsWith($digits, '9') && strlen($digits) === 10) {
            $digits = '0' . $digits;
        }

        $validated['contact'] = $digits;

        // Cluster/org enforcement for CCO etc.
        $needsOrg = $this->leadershipRequiresOrg((int) $validated['leadership_type_id']);

        // Check if CCO is selected
        $isCCO = DB::table('leadership_types')
            ->where('id', (int) $validated['leadership_type_id'])
            ->where('key', 'cco')
            ->exists();

        if ($isCCO) {
            // CCO: cluster_id and organization_id should be "N/A" (stored as null)
            $request->validate([
                'cluster_id'      => ['required', 'in:N/A'],
                'organization_id' => ['required', 'in:N/A'],
            ]);
            $validated['cluster_id']      = null;
            $validated['organization_id'] = null;
        } elseif ($needsOrg) {
            // Other types that require org: normal validation
            $request->validate([
                'cluster_id'      => ['required', 'integer', 'exists:clusters,id'],
                'organization_id' => ['required', 'integer', 'exists:organizations,id'],
            ]);
            $validated['cluster_id']      = (int) $request->input('cluster_id');
            $validated['organization_id'] = (int) $request->input('organization_id');
        } else {
            // Types that don't require org
            $validated['cluster_id']      = null;
            $validated['organization_id'] = null;
        }

        // Expected grad + eligibility
        $expectedGradYear = $this->computeExpectedGradYear(
            $validated['student_id'],
            (int) $validated['year_level']
        );
        $eligibility = (now()->year > $expectedGradYear) ? 'needs_revalidation' : 'eligible';

        DB::beginTransaction();
        try {
            /** @var User $user */
            $user = User::create([
                'first_name'           => $validated['first_name'],
                'last_name'            => $validated['last_name'],
                'middle_name'          => $validated['middle_name'] ?? null,
                'email'                => $validated['email_address'],
                'password'             => $validated['password'],
                'contact'              => $validated['contact'],    // now normalized 09XXXXXXXXX
                'birth_date'           => $validated['birth_date'],
                'profile_picture_path' => null,
                'role'                 => User::ROLE_STUDENT,
                'status'               => User::STATUS_PENDING,
            ]);

            // student_academic via relation
            $user->studentAcademic()->updateOrCreate([], [
                'student_number'     => $validated['student_id'],
                'college_id'         => $validated['college_id'],
                'program_id'         => $validated['program_id'],
                'major_id'           => $validated['major_id'] ?? null,
                'year_level'         => $validated['year_level'],
                'expected_grad_year' => $expectedGradYear,
                'eligibility_status' => $eligibility,
                'revalidated_at'     => null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            // leadership record
            if (Schema::hasTable('student_leaderships')) {
                DB::table('student_leaderships')->insert([
                    'user_id'            => $user->id,
                    'leadership_type_id' => (int) $validated['leadership_type_id'],
                    'cluster_id'         => $validated['cluster_id'],
                    'organization_id'    => $validated['organization_id'],
                    'position_id'        => (int) $validated['position_id'],
                    'term'               => $validated['term'],
                    'issued_by'          => $validated['issued_by'],
                    'leadership_status'  => $validated['leadership_status'],
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route('login.show')
                ->with('status', 'Registration received. Please wait for account approval.');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            report($e);

            $errorMessage = $e->getMessage();

            // Unique constraint handling
            if (
                str_contains($errorMessage, 'UNIQUE constraint failed') ||
                str_contains($errorMessage, 'Duplicate entry') ||
                str_contains($errorMessage, 'unique constraint')
            ) {

                if (str_contains($errorMessage, 'email') || str_contains($errorMessage, 'users.email')) {
                    return back()
                        ->withErrors(['email_address' => 'This email address is already registered. Please use a different email or try logging in.'])
                        ->withInput();
                } elseif (str_contains($errorMessage, 'student_number') || str_contains($errorMessage, 'student_id')) {
                    return back()
                        ->withErrors(['student_id' => 'This student ID is already registered. Please check your student ID or contact support if you believe this is an error.'])
                        ->withInput();
                } else {
                    return back()
                        ->withErrors(['register' => 'This information is already registered. Please check your details or contact support.'])
                        ->withInput();
                }
            }

            // Generic DB error
            return back()
                ->withErrors(['register' => 'Could not complete registration. Please try again.'])
                ->withInput();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()
                ->withErrors(['register' => 'Could not complete registration. Please try again.'])
                ->withInput();
        }
    }

    /* =========================
     |  OTP / VERIFICATION
     * ========================= */

    /**
     * Generate & send OTP for given user/context.
     */
    protected function sendOtp(User $user, string $context = 'login'): void
    {
        // Remove existing active OTPs for this user + context
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

    /**
     * Alias for GET /otp if your routes still point to otp.show
     */
    public function showOtp()
    {
        return $this->showOtpForm();
    }

    /**
     * Used by GET /otp to re-open the login page with OTP modal.
     */
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

        if (!$userId) {
            return redirect()->route('login.show');
        }

        /** @var User|null $user */
        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login.show');
        }

        // If you use student eligibility checks, re-check them here
        if (
            $context === 'login' &&
            method_exists($user, 'isStudent') &&
            method_exists($user, 'canLoginToSlea') &&
            $user->isStudent() &&
            !$user->canLoginToSlea()
        ) {

            return redirect()
                ->route('login.show')
                ->withErrors(['email' => $user->loginBlockReason()])
                ->with('show_otp_modal', false);
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

        if ($otpRecord->attempts >= 5) {
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

        // Success
        $otpRecord->used_at = now();
        $otpRecord->save();

        if ($context === 'login') {
            if (Schema::hasColumn('users', 'otp_last_verified_at')) {
                $user->otp_last_verified_at = now();
                $user->save();
            }

            $remember = session('otp_remember_me', false);

            session()->forget(['otp_pending_user_id', 'otp_remember_me', 'otp_context', 'otp_display_email']);

            Auth::login($user, $remember);
            $request->session()->regenerate();

            // ⭐ NEW: sync email cookie with remember-me after OTP-login
            if ($remember) {
                Cookie::queue('slea_remembered_email', $user->email, 60 * 24 * 30);
            } else {
                Cookie::queue(Cookie::forget('slea_remembered_email'));
            }

            // 🔹 SYSTEM LOG: LOGIN (after successful OTP)
            $displayName = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);

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

        if (!$userId) {
            return redirect()->route('login.show');
        }

        /** @var User|null $user */
        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login.show');
        }

        $this->sendOtp($user, $context);

        return redirect()
            ->route('login.show')
            ->with('status', 'A new OTP has been sent to your email.')
            ->with('show_otp_modal', true);
    }

    /* =========================
     |  FORGOT PASSWORD (OTP-BASED)
     * ========================= */

    public function showForgotPasswordForm()
    {
        // use the login page modal instead of a separate page
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
                ->withErrors([
                    'email' => 'Your password reset session has expired. Please request a new OTP.',
                ])
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
        if (!Schema::hasTable('positions') || !Schema::hasTable('leadership_types')) {
            return response()->json([]);
        }

        $typeId = (int) $request->query('leadership_type_id');
        $orgId  = (int) $request->query('organization_id');

        // If organization_id is provided (for CCO), load positions via organization_position
        if ($orgId && Schema::hasTable('organization_position')) {
            $rows = DB::table('organization_position as op')
                ->join('positions as p', 'p.id', '=', 'op.position_id')
                ->where('op.organization_id', $orgId)
                ->orderBy('p.rank_order')
                ->orderBy('p.name')
                ->select('p.id', 'p.name', 'op.alias')
                ->get()
                ->map(fn($r) => [
                    'id'   => $r->id,
                    'name' => $r->alias ?: $r->name,
                ]);

            return response()->json($rows);
        }

        // Load positions directly by leadership_type_id
        if ($typeId) {
            $rows = DB::table('positions')
                ->where('leadership_type_id', $typeId)
                ->orderBy('rank_order')
                ->orderBy('name')
                ->select('id', 'name')
                ->get()
                ->map(fn($r) => [
                    'id'   => $r->id,
                    'name' => $r->name,
                ]);

            return response()->json($rows);
        }

        return response()->json([]);
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

    public function getPositions(Request $r)
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

    public function getClusters(Request $request)
    {
        if (!Schema::hasTable('clusters')) {
            return response()->json([]);
        }

        $q = DB::table('clusters')->orderBy('name');

        // Only filter by leadership_type_id if that column actually exists
        if (Schema::hasColumn('clusters', 'leadership_type_id')) {
            $leadershipTypeId = $request->input('leadership_type_id');
            if ($leadershipTypeId) {
                $q->where('leadership_type_id', $leadershipTypeId);
            }
        }

        $clusters = $q->pluck('name', 'id'); // { id: "Cluster Name", ... }

        return response()->json($clusters);
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
        if (!Schema::hasTable('organizations')) {
            return response()->json([]);
        }

        $clusterId = $request->input('cluster_id');

        $q = DB::table('organizations')->orderBy('name');

        if ($clusterId) {
            $q->where('cluster_id', $clusterId);
        }

        $organizations = $q->pluck('name', 'id'); // { id: "Org Name", ... }

        return response()->json($organizations);
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

    private function leadershipRequiresOrg(?int $typeId): bool
    {
        if (!$typeId || !Schema::hasTable('leadership_types')) return false;

        return (bool) DB::table('leadership_types')
            ->where('id', $typeId)
            ->value('requires_org');
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
