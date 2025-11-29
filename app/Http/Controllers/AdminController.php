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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\College;
use App\Models\Program;
use App\Models\StudentAcademic;
use App\Models\UserDocument;
use App\Models\SystemMonitoringAndLog;

class AdminController extends Controller
{
    /* =========================
     | PROFILE & PASSWORD
     * ========================= */

    // GET /admin/profile
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
            'first_name' => ['required', 'string', 'max:50'],
            'last_name'  => ['required', 'string', 'max:50'],
            'middle_name' => ['nullable', 'string', 'max:50'],
            'email'      => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'contact'    => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
        ]);

        $user->update($data);

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
            'password'              => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'password_confirmation' => ['required'],
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Store previous hash in password_changes table (if you already do this)
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

        // Actually change the password
        $user->password = Hash::make($data['password']);
        $user->save();

        // 🔹 SYSTEM LOG: PASSWORD CHANGE
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

    // GET /admin/create_assessor
    public function createUser()
    {
        $limit     = (int) config('slea.max_admin_accounts', 3); // change in .env via SLEA_MAX_ADMINS
        $adminCnt  = User::where('role', 'admin')->count();
        $remaining = max($limit - $adminCnt, 0);

        // points to resources/views/admin/create_user.blade.php
        return view('admin.create_user', [
            'limit'     => $limit,
            'adminCnt'  => $adminCnt,
            'remaining' => $remaining,
        ]);
    }

    // app/Http/Controllers/AdminController.php

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name'  => ['required', 'string', 'max:50'],
            'middle_name' => ['nullable', 'string', 'max:50'],
            'email'      => ['required', 'email', 'max:100', 'unique:users,email'],
            'role'       => ['nullable', 'string', 'in:admin,assessor,student'],
            'contact'    => ['nullable', 'string', 'max:50'],
            // other fields...
        ]);

        // Default to 'assessor' if role is not provided
        $data['role'] = $data['role'] ?? 'assessor';

        // Default status to 'approved' for new assessor/admin accounts
        $status = User::STATUS_APPROVED;

        $password = Str::random(10);

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'email'      => $data['email'],
            'password'   => Hash::make($password),
            'role'       => $data['role'],
            'status'     => $status,
            'contact'    => $data['contact'] ?? null,
            // contact / birth_date / etc.
        ]);

        $admin = Auth::user();

        // 🔹 SYSTEM LOG: ACCOUNT CREATION
        $adminName = trim($admin->first_name . ' ' . ($admin->middle_name ? $admin->middle_name . ' ' : '') . $admin->last_name);
        $userName  = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);

        SystemMonitoringAndLog::record(
            $admin->role,                                       // 'admin'
            $adminName ?: $admin->email,
            'Create',
            "Created {$user->role} account for {$userName} ({$user->email})."
        );

        return redirect()->route('admin.manage-account')->with('status', 'User account created successfully.');
    }



    // GET /admin/approve-reject
    public function approveReject(Request $request)
    {
        $search = $request->input('q');

        $students = User::query()
            ->where('role', User::ROLE_STUDENT)
            ->where('status', User::STATUS_PENDING) // Only show pending students
            ->when($search, function ($q) use ($search) {
                $like = '%' . $search . '%';

                $q->where(function ($inner) use ($like) {
                    $inner->where('email', 'like', $like)
                        ->orWhereHas('studentAcademic', function ($qa) use ($like) {
                            $qa->where('student_number', 'like', $like);
                        });
                });
            })
            ->with(['studentAcademic.program']) // eager load
            ->orderByDesc('created_at')
            ->paginate(5)
            ->withQueryString();

        return view('admin.approve-reject', compact('students', 'search'));
    }


    // POST /admin/approve/{student_id}
    public function approveUser($student_id)
    {
        $admin = Auth::user();

        // Only students
        $student = User::where('id', $student_id)
            ->where('role', User::ROLE_STUDENT)
            ->firstOrFail();

        // Optional: only allow approving pending accounts
        if ($student->status !== User::STATUS_PENDING) {
            return back()->withErrors([
                'email' => 'Only pending student accounts can be approved.',
            ]);
        }

        $student->status = User::STATUS_APPROVED;
        $student->save();

        // ✅ Send approval email here
        Mail::to($student->email)->send(new AccountApprovedMail($student));

        // System log
        $adminName   = trim($admin->first_name . ' ' . ($admin->middle_name ? $admin->middle_name . ' ' : '') . $admin->last_name);
        $studentName = trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name);

        SystemMonitoringAndLog::record(
            $admin->role,
            $adminName ?: $admin->email,
            'Update',
            "Approved account for {$studentName} ({$student->email})."
        );

        return redirect()->back()->with('status', 'Student account approved successfully.');
    }


    // POST /admin/reject/{user}
    public function rejectUser($student_id)
    {
        $admin = Auth::user();

        $student = User::where('id', $student_id)->firstOrFail();
        $studentName = trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name);

        $student->status = 'rejected';
        $student->save();

        $adminName = trim($admin->first_name . ' ' . ($admin->middle_name ? $admin->middle_name . ' ' : '') . $admin->last_name);

        // 🔹 SYSTEM LOG: REJECTION
        SystemMonitoringAndLog::record(
            $admin->role,
            $adminName ?: $admin->email,
            'Update',
            "Rejected account for {$studentName} ({$student->email})."
        );

        return redirect()->back()->with('status', 'Student account rejected.');
    }



    // PATCH /admin/manage/{user}/toggle   (approved <-> disabled)
    public function toggleUser(User $user)
    {
        // Safety: don’t toggle yourself
        if (Auth::id() === $user->id) {
            return back()->withErrors(['email' => 'You cannot disable your own account.']);
        }

        // Safety: don’t leave zero active admins
        if ($user->isAdmin()) {
            $activeAdmins = User::role(User::ROLE_ADMIN)->approved()->count();
            if ($activeAdmins <= 1 && $user->isApproved()) {
                return back()->withErrors(['email' => 'You cannot disable the last active admin.']);
            }
        }

        $user->toggle(); // model handles approved <-> disabled

        return back()->with('status', 'User status toggled.');
    }

    // DELETE /admin/manage/{user}
    public function destroyUser(User $user)
    {
        // Safety: don’t delete yourself
        if (Auth::id() === $user->id) {
            return back()->withErrors(['email' => 'You cannot delete your own account.']);
        }

        // Safety: don’t delete the last admin
        if ($user->isAdmin()) {
            $adminCount = User::role(User::ROLE_ADMIN)->count();
            if ($adminCount <= 1) {
                return back()->withErrors(['email' => 'You cannot delete the last admin.']);
            }
        }

        // Best-effort: delete stored avatar
        if ($user->profile_picture_path && Storage::disk('public')->exists($user->profile_picture_path)) {
            Storage::disk('public')->delete($user->profile_picture_path);
        }

        $user->delete();

        return back()->with('status', 'User deleted.');
    }
    // GET /admin/revalidation
    // GET /admin/revalidation
    public function revalidationQueue()
    {
        // Use Eloquent so we can show more info and re-use relationships
        $rows = StudentAcademic::with(['user'])
            ->whereIn('eligibility_status', ['needs_revalidation', 'under_review'])
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.revalidation', compact('rows'));
    }

    // POST /admin/revalidation/{user}/approve
    public function approveRevalidation(User $user)
    {
        if (! $user->isStudent()) {
            abort(403);
        }

        $academic = \App\Models\StudentAcademic::where('user_id', $user->id)->firstOrFail();

        // Must be in revalidation-required state
        if (! in_array($academic->eligibility_status, ['needs_revalidation', 'under_review'], true)) {
            return back()->withErrors([
                'revalidation' => 'This student is not marked for revalidation.',
            ]);
        }

        // Must have COR
        if (!$academic->hasCor()) {
            return back()->withErrors([
                'cor' => 'Student has no Certificate of Registration (COR) uploaded. Cannot approve.',
            ]);
        }

        // (Optional future rule) Must have complete academic info
        if (!$academic->expected_grad_year || !$academic->program_id || !$academic->year_level) {
            return back()->withErrors([
                'academic' => 'Academic details incomplete. Require student to update before revalidation.',
            ]);
        }

        // Approve revalidation
        $academic->update([
            'eligibility_status' => 'eligible',
            'revalidated_at'     => now(),
        ]);

        return back()->with('status', 'Revalidation approved. Student is now eligible.');
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

        // Simple: mark fully ineligible
        $academic->update([
            'eligibility_status' => 'ineligible',
        ]);

        return back()->with('status', 'Revalidation rejected. Student marked ineligible.');
    }
    public function viewStudentCor(User $user)
    {
        // Only admins should be here (route will already be under admin middleware)
        if (! $user->isStudent()) {
            abort(403);
        }

        /** @var StudentAcademic|null $academic */
        $academic = StudentAcademic::where('user_id', $user->id)->first();

        if (! $academic || empty($academic->certificate_of_registration_path)) {
            abort(404, 'No COR uploaded for this student.');
        }

        $path = $academic->certificate_of_registration_path;

        // We assume you're using the same disk as in uploadCOR(): 'student_docs'
        if (! Storage::disk('student_docs')->exists($path)) {
            abort(404, 'COR file not found on server.');
        }

        // View inline in browser (PDF/image)
        return response()->file(
            Storage::disk('student_docs')->path($path)
        );

        // If you want forced download instead, use:
        // return Storage::disk('student_docs')->download($path);
    }


    /* =========================
     | SYSTEM PAGES (stubs)
     * ========================= */
    /* =========================
     | SYSTEM PAGES (AWARDS REPORT)
     * ========================= */
    public function awardReportDashboard(Request $request)
    {
        // Build all rows (already filtered by SLEA status, etc.)
        $rows = $this->buildAwardReportRows($request);

        // Stats for summary cards
        $stats = [
            'total'     => $rows->count(),
            'gold'      => $rows->where('award_level', 'gold')->count(),
            'silver'    => $rows->where('award_level', 'silver')->count(),
            'qualified' => $rows->where('award_level', 'qualified')->count(),
            'tracking'  => $rows->whereIn('award_level', ['tracking', 'not_qualified'])->count(),
        ];

        // Manual pagination on the already-computed collection
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

        // For dropdown filters
        $colleges = class_exists(\App\Models\College::class)
            ? \App\Models\College::orderBy('name')->get()
            : collect();

        $programs = class_exists(\App\Models\Program::class)
            ? \App\Models\Program::orderBy('name')->get()
            : collect();

        // Placeholder if you later add a "batch" column
        $batches = [];

        // Main admin awards report page
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
        // buildAwardReportRows is whatever we already wrote earlier
        $rows = $this->buildAwardReportRows($request);

        $students = $rows;

        // 👇 use the actual view path: resources/views/admin/pdf/admin-report.blade.php
        $pdf = Pdf::loadView('admin.pdf.award-report', [
            'students'    => $students,
            'generatedAt' => now(),
        ])->setPaper('A4', 'portrait');

        return $pdf->download('slea-awards-report.pdf');
    }

    /**
     * Build the base dataset for the awards report.
     *
     * Returns a collection of stdClass objects:
     *  - user (stdClass with full_name, studentAcademic, etc.)
     *  - total_points (percentage 0–100)
     *  - award_level (gold/silver/qualified/tracking/not_qualified)
     *  - slea_status (e.g. "SLEA Qualified")
     *  - program_code, program_name, student_number, college_name, etc.
     */
    protected function buildAwardReportRows(Request $request): \Illuminate\Support\Collection
    {
        if (
            ! Schema::hasTable('student_academic') ||
            ! Schema::hasTable('assessor_final_reviews') ||
            ! Schema::hasTable('users')
        ) {
            return collect();
        }

        // Check if slea_application_status column exists
        $hasSleaStatus = Schema::hasColumn('student_academic', 'slea_application_status');

        // Use assessor_final_reviews as the source of truth for final scores
        // This ensures consistency with Final Review page
        // Build select fields
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
            'afr.max_possible as max_points'
        ];

        // Add slea_application_status only if column exists
        if ($hasSleaStatus) {
            $selectFields[] = 'sa.slea_application_status';
        }

        // Join directly with assessor_final_reviews to get the latest final review
        $query = DB::table('student_academic as sa')
            ->select($selectFields)
            ->join('users as u', 'u.id', '=', 'sa.user_id')
            ->leftJoin('assessor_final_reviews as afr', function ($join) {
                $join->on('afr.student_id', '=', 'sa.user_id')
                    ->whereIn('afr.status', ['queued_for_admin', 'finalized']);
            })
            ->leftJoin('programs as p', 'p.id', '=', 'sa.program_id')
            ->leftJoin('colleges as c', 'c.id', '=', 'sa.college_id')
            ->where('u.role', 'student')
            ->whereNotNull('afr.total_score')
            ->whereNotNull('afr.max_possible');

        // Only filter by slea_application_status if column exists
        if ($hasSleaStatus) {
            // Only SLEA applications that are already qualified by admin
            $query->where('sa.slea_application_status', 'qualified');
        }

        // --- filters from the list page (SEARCH) ---
        // Support both 'q' (from INCOMING) and 'search' (from HEAD view)
        $searchTerm = trim((string) ($request->input('q') ?: $request->input('search', '')));
        if ($searchTerm) {
            $query->where(function ($sub) use ($searchTerm) {
                $sub->where('sa.student_number', 'like', "%{$searchTerm}%")
                    ->orWhere('u.first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('u.last_name', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by college
        if ($collegeId = $request->input('college_id')) {
            $query->where('sa.college_id', $collegeId);
        }

        // Filter by program
        if ($programId = $request->input('program_id')) {
            $query->where('sa.program_id', $programId);
        }

        // If you later add AY/batch, plug it here.
        // if ($batch = $request->input('batch')) {
        //     $query->where('sa.batch', $batch);
        // }

        $rows = $query->get();

        // Map raw DB rows into richer objects for Blade
        $mapped = $rows->map(function ($row) use ($hasSleaStatus) {
            // Use the same calculation as Final Review
            $totalScore = (float) ($row->total_score ?? 0);
            $maxPoints = (float) ($row->max_points ?? 0);

            // Calculate percentage for display
            $percent = $maxPoints > 0
                ? round(($totalScore / $maxPoints) * 100, 2)
                : 0.0;

            // Simple award-level rules – adjust thresholds as needed
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

            // Map slea_application_status to display label
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

            // Build pseudo-relationship objects so Blade can still do:
            // $row->user->studentAcademic->program->code, etc.
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
            $record->user          = $user;
            $record->total_points  = $percent;      // percentage (keep, we still use this for filters)
            $record->award_level   = $awardLevel;
            $record->slea_status   = $statusLabel;

            // RAW scores – used by web table + PDF (same as Final Review)
            $record->raw_total_score = $totalScore;
            $record->raw_max_points  = $maxPoints;

            // extras used by some views
            $record->program_code   = $row->program_code;
            $record->program_name   = $row->program_name;
            $record->college_name   = $row->college_name;
            $record->student_number = $row->student_number;

            return $record;
        });

        // Filter again by award_level if requested
        if ($request->filled('award_level')) {
            $level  = $request->input('award_level');
            $mapped = $mapped
                ->filter(fn($row) => $row->award_level === $level)
                ->values();
        }

        // Filter again by minimum percentage score
        if ($request->filled('min_score')) {
            $threshold = (int) $request->input('min_score');
            $mapped    = $mapped
                ->filter(fn($row) => $row->total_points >= $threshold)
                ->values();
        }

        // Sort by score descending
        return $mapped->sortByDesc('total_points')->values();
    }

    public function awardReport(Request $request)
    {
        // Get filter parameters from view (college/program are names, not IDs)
        $college = $request->query('college');
        $program = $request->query('program');
        $search = $request->query('search');

        // Use buildAwardReportRows to get real data
        // buildAwardReportRows now supports both 'q' and 'search' parameters
        $allRows = $this->buildAwardReportRows($request);

        // Convert to array format compatible with existing view
        // Use raw_total_score and raw_max_points to match Final Review display
        $allStudents = $allRows->map(function ($row) {
            // Display as "score/max" format to match Final Review
            $score = $row->raw_total_score ?? 0;
            $max = $row->raw_max_points ?? 0;
            return [
                'id' => $row->user->id ?? 0,
                'name' => $row->user->full_name ?? 'N/A',
                'student_id' => $row->student_number ?? 'N/A',
                'college' => $row->college_name ?? 'N/A',
                'program' => $row->program_name ?? 'N/A',
                'points' => round($score, 2), // Use raw score, not percentage
                'max_points' => round($max, 2), // Include max for display
                'points_display' => number_format($score, 2) . '/' . number_format($max, 2), // Format: 23.70/60.00
            ];
        })->toArray();

        // Apply additional filters (college, program, search)
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

        // Re-index array after filtering
        $filteredStudents = array_values($filteredStudents);

        // Get current page from request
        $currentPage = $request->get('page', 1);
        $perPage = 10;

        // Create paginator manually
        $total = count($filteredStudents);
        $offset = ($currentPage - 1) * $perPage;
        $items = array_slice($filteredStudents, $offset, $perPage);

        // Create paginator instance
        $students = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.award-report', compact('students'));
    }

    public function exportAwardReport(Request $request)
    {
        // Get filter parameters
        $college = $request->query('college');
        $program = $request->query('program');
        $search = $request->query('search');

        // Use buildAwardReportRows to get real data
        $allRows = $this->buildAwardReportRows($request);

        // Convert to array format compatible with existing PDF view
        // Use raw_total_score and raw_max_points to match Final Review display
        $filteredStudents = $allRows->map(function ($row) {
            $score = $row->raw_total_score ?? 0;
            $max = $row->raw_max_points ?? 0;
            return [
                'id' => $row->user->id ?? 0,
                'name' => $row->user->full_name ?? 'N/A',
                'student_id' => $row->student_number ?? 'N/A',
                'college' => $row->college_name ?? 'N/A',
                'program' => $row->program_name ?? 'N/A',
                'points' => round($score, 2), // Use raw score to match Final Review
                'max_points' => round($max, 2),
                'points_display' => number_format($score, 2) . '/' . number_format($max, 2),
            ];
        })->toArray();

        // Apply additional filters
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

        // Re-index array after filtering
        $filteredStudents = array_values($filteredStudents);

        // Generate PDF or CSV export
        return view('admin.pdf.award-report', [
            'students' => $filteredStudents,
            'filters' => [
                'college' => $college,
                'program' => $program,
                'search' => $search,
            ]
        ]);
    }

    public function systemMonitoring()
    {
        return view('admin.system.monitoring');
    }
}
