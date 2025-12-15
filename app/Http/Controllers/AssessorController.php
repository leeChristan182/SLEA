<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Models\AssessorInfo;
use App\Models\AssessorFinalReview;
use App\Models\Submission;

class AssessorController extends Controller
{
    /* =========================
     | PROFILE
     * ========================= */
    public function dashboard()
    {
        $assessorId = Auth::id();

        // Status counts for THIS assessor
        $statusCounts = AssessorFinalReview::query()
            ->where('assessor_id', $assessorId)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $reviewStats = [
            'pending'   => (int) ($statusCounts[AssessorFinalReview::STATUS_DRAFT] ?? $statusCounts['draft'] ?? 0),
            'submitted' => (int) ($statusCounts[AssessorFinalReview::STATUS_QUEUED_FOR_ADMIN] ?? $statusCounts['queued_for_admin'] ?? 0),
            'finalized' => (int) ($statusCounts[AssessorFinalReview::STATUS_FINALIZED] ?? $statusCounts['finalized'] ?? 0),
        ];

        // Pending submissions queue (must match /assessor/submissions/pending-submissions)
        $pendingQueueCount = (int) Submission::query()
            ->where('status', 'pending')
            ->count();

        // Individual reviews list (latest first)
        // Adjust relations/columns to match your schema (see notes below).
        $reviews = AssessorFinalReview::query()
            ->with(['student']) // student is a User relation
            ->where('assessor_id', $assessorId)
            ->orderByDesc('updated_at')
            ->paginate(12);

        // Chart data: Monthly finalized reviews for the last 12 months
        $monthlyFinalized = AssessorFinalReview::query()
            ->where('assessor_id', $assessorId)
            ->where('status', AssessorFinalReview::STATUS_FINALIZED)
            ->select(
                DB::raw('DATE_FORMAT(updated_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('updated_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Generate labels and data for the last 12 months
        $chartLabels = [];
        $chartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $monthLabel = now()->subMonths($i)->format('M Y');
            $chartLabels[] = $monthLabel;
            $chartData[] = (int) ($monthlyFinalized[$month] ?? 0);
        }

        return view('assessor.dashboard', compact('reviewStats', 'reviews', 'chartLabels', 'chartData', 'pendingQueueCount'));
    }

    // GET /assessor/profile
    public function profile()
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Refresh user data from database to ensure we have latest state
        $user->refresh();
        
        // If account is no longer limited (approved), clear waiting modal session flags
        if (!$user->is_account_limited) {
            session()->forget(['show_waiting_modal', 'requirements_submitted']);
        }
        
        return view('assessor.profile', compact('user'));
    }

    // PUT /assessor/profile
    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            $data = $request->validate([
                'first_name' => ['required', 'string', 'max:50'],
                'last_name'  => ['required', 'string', 'max:50'],
                'middle_name' => ['nullable', 'string', 'max:50'],
                'email'      => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
                'contact' => [
                    'required',
                    'regex:' . config('slea.phone_regex'),
                ],
                'birth_date' => [
                    'nullable',
                    'date',
                    'before:today',
                    'after_or_equal:' . now()->subYears(100)->toDateString(),
                    'before_or_equal:' . now()->subYears(15)->toDateString(),
                ]
            ]);

            $user->update($data);

            // 🔹 SYSTEM LOG: PROFILE UPDATE
            $userName = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);
            \App\Models\SystemMonitoringAndLog::record(
                $user->role,
                $userName ?: $user->email,
                'Update',
                "Updated profile information."
            );

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully.',
                ]);
            }

            return back()->with('status', 'Profile updated.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return JSON response for validation errors on AJAX requests
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }
    }

    // PATCH /assessor/password
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', PasswordRule::min(12)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()],
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your current password is incorrect.',
                    'errors' => ['current_password' => ['Your current password is incorrect.']],
                ], 422);
            }
            return back()->withErrors(['current_password' => 'Your current password is incorrect.']);
        }

        // Optional: write to password_changes table if it exists
        if (Schema::hasTable('password_changes')) {
            DB::table('password_changes')->insert([
                'user_id'                => $user->id,
                'previous_password_hash' => $user->password,
                'changed_at'             => now(),
                'changed_by'             => 'self',
                'ip'                     => $request->ip(),
                'user_agent'             => substr((string)$request->userAgent(), 0, 255),
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }

        $user->password = $request->password; // model mutator will hash
        $user->save();
        AssessorInfo::where('user_id', $user->id)->update([
            'temporary_password_hash' => null,
            'must_change_password'    => false,
        ]);

        // 🔹 SYSTEM LOG: PASSWORD CHANGE
        $userName = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);
        \App\Models\SystemMonitoringAndLog::record(
            $user->role,
            $userName ?: $user->email,
            'Update',
            "Changed password."
        );

        // Return JSON response for AJAX requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully.',
            ]);
        }

        return back()->with('status', 'Password updated.');
    }

    // POST /assessor/profile/picture
    public function updateAvatar(Request $request)
    {
        try {
            // match 5MB client limit: 5 * 1024 KB = 5120
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

        /** @var User $user */
        $user = Auth::user();

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');

        // Delete old one if exists
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

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Profile picture updated.',
                'avatar_url' => $avatarUrl,
            ]);
        }

        // Fallback for non-AJAX
        return back()->with('status', 'Profile picture updated.');
    }
}
