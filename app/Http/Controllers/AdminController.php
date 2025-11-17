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
        if ($user->profile_picture_path && \Storage::disk('public')->exists($user->profile_picture_path)) {
            \Storage::disk('public')->delete($user->profile_picture_path);
        }

        $user->update(['profile_picture_path' => $path]);

        $avatarUrl = asset('storage/' . $path);

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
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Your current password is incorrect.']);
        }

        // Optional: audit password change if table exists
        if (Schema::hasTable('password_changes')) {
            DB::table('password_changes')->insert([
                'user_id'                => $user->id,
                'previous_password_hash' => $user->password,
                'changed_at'             => now(),
                'changed_by'             => 'self',
                'ip'                     => $request->ip(),
                'user_agent'             => substr((string) $request->userAgent(), 0, 255),
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }

        $user->password = $request->password; // auto-hash via model mutator
        $user->save();

        return back()->with('status', 'Password updated.');
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
            ->paginate(20)
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
        $limit    = (int) config('slea.max_admin_accounts', 3);
        $roleList = ['admin', 'assessor'];

        $data = $request->validate([
            'role'        => ['required', 'in:' . implode(',', $roleList)],
            'last_name'   => ['required', 'string', 'max:50'],
            'first_name'  => ['required', 'string', 'max:50'],
            'middle_name' => ['nullable', 'string', 'max:50'],
            'email'       => ['required', 'email', 'max:100', 'unique:users,email'],
            'contact'     => ['nullable', 'string', 'max:20'],
        ]);

        // Enforce admin cap
        if ($data['role'] === 'admin') {
            $adminCnt = \App\Models\User::where('role', 'admin')->count();
            if ($adminCnt >= $limit) {
                return back()->withErrors(['role' => "Admin account limit of {$limit} reached."])->withInput();
            }
        }

        // 1) Create with a placeholder password (will be replaced immediately)
        $user = \App\Models\User::create([
            'first_name'  => $data['first_name'],
            'last_name'   => $data['last_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'email'       => $data['email'],
            'contact'     => $data['contact'] ?? null,
            'password'    => 'placeholder',   // will be overwritten; mutator will hash
            'role'        => $data['role'],
            'status'      => 'approved',
        ]);

        // 2) Set friendly temp password based on its auto-increment ID (no migration needed)
        $plain = 'password_' . $user->id;
        $user->password = $plain;  // hashed by your User model mutator
        $user->save();

        // (Optional) Insert admin privileges if table exists
        if ($data['role'] === 'admin' && \Illuminate\Support\Facades\Schema::hasTable('admin_privileges')) {
            \Illuminate\Support\Facades\DB::table('admin_privileges')->insert([
                'user_id'     => $user->id,
                'admin_level' => 'standard',
                'permissions' => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        return redirect()
            ->route('admin.create_user')
            ->with([
                'success'            => ucfirst($data['role']) . ' account created.',
                'generated_password' => $plain, // shown once in your modal
            ]);
    }


    // GET /admin/approve-reject
    public function approveReject(Request $request)
    {
        $status = $request->input('status', User::STATUS_PENDING);
        $search = $request->input('q');

        $students = User::query()
            ->where('role', User::ROLE_STUDENT)
            ->when($status, fn($q) => $q->where('status', $status))
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
            ->paginate(20)
            ->withQueryString();

        return view('admin.approve-reject', compact('students', 'status', 'search'));
    }


    // POST /admin/approve/{student_id}
    public function approveUser(int $student_id)
    {
        $user = User::findOrFail($student_id);

        if (! $user->isStudent() || ! $user->isPending()) {
            return back()->withErrors(['email' => 'Only pending student accounts can be approved.']);
        }

        $user->approve();
        return back()->with('status', 'Student approved.');
    }


    // POST /admin/reject/{user}
    public function rejectUser(User $user)
    {
        if (! $user->isStudent() || ! $user->isPending()) {
            return back()->withErrors(['email' => 'Only pending student accounts can be rejected.']);
        }

        $user->reject(); // assumes you have this helper on the model

        return back()->with('status', 'Student rejected.');
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
    public function revalidationQueue()
    {
        $rows = \DB::table('users as u')
            ->join('student_academic as a', 'a.user_id', '=', 'u.id')
            ->select('u.id', 'u.first_name', 'u.last_name', 'u.email', 'a.expected_grad_year', 'a.eligibility_status', 'a.updated_at')
            ->where('u.role', 'student')
            ->whereIn('a.eligibility_status', ['needs_revalidation', 'under_review'])
            ->orderByDesc('a.updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.revalidation', compact('rows'));
    }

    // POST /admin/revalidation/{user}/approve
    public function approveRevalidation(User $user)
    {
        // trust updated academic info + COR already uploaded; mark eligible again
        \DB::table('student_academic')->where('user_id', $user->id)->update([
            'eligibility_status' => 'eligible',
            'revalidated_at'     => now(),
            'updated_at'         => now(),
        ]);

        return back()->with('status', 'Revalidation approved.');
    }

    // POST /admin/revalidation/{user}/reject
    public function rejectRevalidation(User $user)
    {
        \DB::table('student_academic')->where('user_id', $user->id)->update([
            'eligibility_status' => 'ineligible',
            'updated_at'         => now(),
        ]);

        return back()->with('status', 'Revalidation rejected.');
    }

    /* =========================
     | SYSTEM PAGES (stubs)
     * ========================= */

    public function submissionOversight()
    {
        return view('admin.system.submission-oversight');
    }

    public function finalReview()
    {
        return view('admin.system.final-review');
    }

    public function awardReport()
    {
        return view('admin.system.award-report');
    }

    public function systemMonitoring()
    {
        return view('admin.system.monitoring');
    }
}
