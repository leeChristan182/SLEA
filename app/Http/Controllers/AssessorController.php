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

class AssessorController extends Controller
{
    /* =========================
     | PROFILE
     * ========================= */

    // GET /assessor/profile
    public function profile()
    {
        $user = Auth::user();
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
                'contact'    => ['nullable', 'string', 'max:20'],
            ]);

            $user->update($data);

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
