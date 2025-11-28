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
            'first_name'  => ['required', 'string', 'max:50'],
            'last_name'   => ['required', 'string', 'max:50'],
            'middle_name' => ['nullable', 'string', 'max:50'],
            'email'       => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'contact'     => ['nullable', 'string', 'max:20'],
            'birth_date'  => ['nullable', 'date'],
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
        } catch (ValidationException $e) { // CHANGED: imported class alias used directly
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
            'password'              => ['required', 'confirmed', PasswordRule::defaults()], // CHANGED: use aliased PasswordRule
            'password_confirmation' => ['required'],
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Store previous hash in password_changes table
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
        $perPage = config('slea.pagination.manage_accounts', 5);

        $users = User::query()
            // still filtering by raw request->role/status, which is fine because UI sends valid codes
            ->when($request->filled('role'),   fn($q) => $q->where('role', $request->role))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('q'),      fn($q) => $q->where(function ($x) use ($request) {
                $like = '%' . $request->q . '%';
                $x->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('email', 'like', $like);
            }))
            ->orderBy('last_name')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.manage-account', compact('users'));
    }

    // GET /admin/create_assessor
    public function createUser()
    {
        $limit     = (int) config('slea.max_admin_accounts', 3);
        $adminCnt  = User::where('role', User::ROLE_ADMIN)->count(); // CHANGED: role constant
        $remaining = max($limit - $adminCnt, 0);

        return view('admin.create_user', compact('limit', 'adminCnt', 'remaining'));
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'first_name'  => ['required', 'string', 'max:50'],
            'last_name'   => ['required', 'string', 'max:50'],
            'middle_name' => ['nullable', 'string', 'max:50'],
            'email'       => ['required', 'email', 'max:100', 'unique:users,email'],
            'role'        => [
                'nullable',
                'string',
                Rule::in([
                    User::ROLE_ADMIN,
                    User::ROLE_ASSESSOR,
                    User::ROLE_STUDENT,
                ]),
            ],
            'contact'     => ['nullable', 'string', 'max:50'],
        ]);

        // Default to assessor if role is not provided
        $data['role'] = $data['role'] ?? User::ROLE_ASSESSOR; // CHANGED: constant

        // Default status to 'approved' for new assessor/admin accounts
        $status = User::STATUS_APPROVED; // CHANGED: constant

        $password = Str::random(10);

        $user = User::create([
            'first_name'  => $data['first_name'],
            'last_name'   => $data['last_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'email'       => $data['email'],
            'password'    => Hash::make($password),
            'role'        => $data['role'],
            'status'      => $status,
            'contact'     => $data['contact'] ?? null,
        ]);

        $admin = Auth::user();

        // 🔹 SYSTEM LOG: ACCOUNT CREATION
        $adminName = trim($admin->first_name . ' ' . ($admin->middle_name ? $admin->middle_name . ' ' : '') . $admin->last_name);
        $userName  = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);

        SystemMonitoringAndLog::record(
            $admin->role,
            $adminName ?: $admin->email,
            'Create',
            "Created {$user->role} account for {$userName} ({$user->email})."
        );

        return redirect()
            ->route('admin.manage-account')
            ->with('status', 'User account created successfully.');
    }

    // GET /admin/approve-reject
    public function approveReject(Request $request)
    {
        $search = $request->input('q');

        $perPage = config('slea.pagination.approve_reject', 5); // CHANGED: config-based pagination

        $students = User::query()
            ->where('role', User::ROLE_STUDENT)         // CHANGED: role constant
            ->where('status', User::STATUS_PENDING)     // CHANGED: status constant
            ->when($search, function ($q) use ($search) {
                $like = '%' . $search . '%';

                $q->where(function ($inner) use ($like) {
                    $inner->where('email', 'like', $like)
                        ->orWhereHas('studentAcademic', function ($qa) use ($like) {
                            $qa->where('student_number', 'like', $like);
                        });
                });
            })
            ->with(['studentAcademic.program'])
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.approve-reject', compact('students', 'search'));
    }

    // POST /admin/approve/{student_id}
    public function approveUser($student_id)
    {
        $admin = Auth::user();

        $student = User::where('id', $student_id)->firstOrFail();
        $student->status = User::STATUS_APPROVED; // CHANGED
        $student->save();

        $adminName   = trim($admin->first_name . ' ' . ($admin->middle_name ? $admin->middle_name . ' ' : '') . $admin->last_name);
        $studentName = trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name);

        // 🔹 SYSTEM LOG: APPROVAL
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
        $student->status = User::STATUS_REJECTED; // CHANGED
        $student->save();

        $adminName   = trim($admin->first_name . ' ' . ($admin->middle_name ? $admin->middle_name . ' ' : '') . $admin->last_name);
        $studentName = trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name); // CHANGED: added studentName

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
            $activeAdmins = User::role(User::ROLE_ADMIN)->approved()->count(); // CHANGED: constant
            if ($activeAdmins <= 1 && $user->isApproved()) {
                return back()->withErrors(['email' => 'You cannot disable the last active admin.']);
            }
        }

        $user->toggle();

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
            $adminCount = User::role(User::ROLE_ADMIN)->count(); // CHANGED: constant
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
    public function revalidationQueue()
    {
        $perPage = config('slea.pagination.revalidation_queue', 20);

        $rows = StudentAcademic::with(['user'])
            ->whereIn('eligibility_status', [
                StudentAcademic::ELIG_NEEDS_REVALIDATION,
                StudentAcademic::ELIG_UNDER_REVIEW,
            ])
            ->orderByDesc('updated_at')
            ->paginate($perPage)
            ->withQueryString();

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
        if (! in_array(
            $academic->eligibility_status,
            [StudentAcademic::ELIG_NEEDS_REVALIDATION, StudentAcademic::ELIG_UNDER_REVIEW],
            true
        )) { // CHANGED: added braces and error response
            return back()->withErrors([
                'revalidation' => 'This student is not marked for revalidation.',
            ]);
        }

        // Must have COR
        if (! $academic->hasCor()) {
            return back()->withErrors([
                'cor' => 'Student has no Certificate of Registration (COR) uploaded. Cannot approve.',
            ]);
        }

        // (Optional future rule) Must have complete academic info
        if (! $academic->expected_grad_year || ! $academic->program_id || ! $academic->year_level) {
            return back()->withErrors([
                'academic' => 'Academic details incomplete. Require student to update before revalidation.',
            ]);
        }

        // Approve revalidation
        $academic->update([
            'eligibility_status' => StudentAcademic::ELIG_ELIGIBLE,
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

        // Must be in revalidation-required state
        if (! in_array(
            (string) $academic->eligibility_status,
            [StudentAcademic::ELIG_NEEDS_REVALIDATION, StudentAcademic::ELIG_UNDER_REVIEW],
            true
        )) { // CHANGED: added braces & message
            return back()->withErrors([
                'revalidation' => 'Only students flagged for revalidation can be rejected.',
            ]);
        }

        // Simple: mark fully ineligible
        $academic->update([
            'eligibility_status' => StudentAcademic::ELIG_INELIGIBLE,
        ]);

        return back()->with('status', 'Revalidation rejected. Student marked ineligible.');
    }

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
        $perPage = config('slea.pagination.award_report_list', 20);

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
        $colleges = class_exists(College::class)
            ? College::orderBy('name')->get()
            : collect();

        $programs = class_exists(Program::class)
            ? Program::orderBy('name')->get()
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

        $pdf = Pdf::loadView('admin.pdf.award-report', [
            'students'    => $students,
            'generatedAt' => now(),
        ])->setPaper('A4', 'portrait');

        return $pdf->download('slea-awards-report.pdf');
    }

    /**
     * Build the base dataset for the awards report.
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

        // Statuses from assessor_final_reviews to include in the report
        $statusForReport = ['queued_for_admin', 'finalized'];

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
            'afr.max_possible as max_points',
        ];

        if ($hasSleaStatus) {
            $selectFields[] = 'sa.slea_application_status';
        }

        // Join directly with assessor_final_reviews to get the latest final review
        $query = DB::table('student_academic as sa')
            ->select($selectFields)
            ->join('users as u', 'u.id', '=', 'sa.user_id')
            ->leftJoin('assessor_final_reviews as afr', function ($join) use ($statusForReport) {
                $join->on('afr.student_id', '=', 'sa.user_id')
                    ->whereIn('afr.status', $statusForReport);
            })
            ->leftJoin('programs as p', 'p.id', '=', 'sa.program_id')
            ->leftJoin('colleges as c', 'c.id', '=', 'sa.college_id')
            ->where('u.role', User::ROLE_STUDENT) // CHANGED: constant
            ->whereNotNull('afr.total_score')
            ->whereNotNull('afr.max_possible');

        if ($hasSleaStatus) {
            $query->where('sa.slea_application_status', StudentAcademic::SLEA_STATUS_QUALIFIED); // CHANGED: constant
        }

        // --- filters from the list page (SEARCH) ---
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

        $rows = $query->get();

        // Get award thresholds from config once
        $thresholds = config('slea.award_thresholds', [
            'gold'          => 90,
            'silver'        => 85,
            'qualified'     => 80,
            'tracking'      => 70,
            'not_qualified' => 0,
        ]);

        // Map raw DB rows into richer objects for Blade
        $mapped = $rows->map(function ($row) use ($hasSleaStatus, $thresholds) {
            $totalScore = (float) ($row->total_score ?? 0);
            $maxPoints  = (float) ($row->max_points ?? 0);

            $percent = $maxPoints > 0
                ? round(($totalScore / $maxPoints) * 100, 2)
                : 0.0;

            // CHANGED: use injected $thresholds instead of re-calling config()
            if ($percent >= $thresholds['gold']) {
                $awardLevel = 'gold';
            } elseif ($percent >= $thresholds['silver']) {
                $awardLevel = 'silver';
            } elseif ($percent >= $thresholds['qualified']) {
                $awardLevel = 'qualified';
            } elseif ($percent >= $thresholds['tracking']) {
                $awardLevel = 'tracking';
            } else {
                $awardLevel = 'not_qualified';
            }

            // Map slea_application_status to display label
            $sleaStatus = $hasSleaStatus ? ($row->slea_application_status ?? null) : null;

            switch ($sleaStatus) {
                case StudentAcademic::SLEA_STATUS_QUALIFIED:
                    $statusLabel = 'SLEA Qualified';
                    break;
                case StudentAcademic::SLEA_STATUS_PENDING_ADMIN_VALIDATION:
                    $statusLabel = 'For Final Review';
                    break;
                default:
                    $statusLabel = 'Tracking';
                    break;
            }

            // Build pseudo-relationship objects
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
            $record->total_points  = $percent;
            $record->award_level   = $awardLevel;
            $record->slea_status   = $statusLabel;

            // RAW scores
            $record->raw_total_score = $totalScore;
            $record->raw_max_points  = $maxPoints;

            // extras
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
        $perPage     = config('slea.pagination.award_report_page', 10);

        // Create paginator manually
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

        return view('admin.pdf.award-report', [
            'students' => $filteredStudents,
            'filters'  => [
                'college' => $college,
                'program' => $program,
                'search'  => $search,
            ],
        ]);
    }

    public function systemMonitoring()
    {
        return view('admin.system.monitoring');
    }
}
