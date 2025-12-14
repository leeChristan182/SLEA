<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireProfileCompletion
{
    /**
     * Handle an incoming request.
     * Blocks navigation to student routes if profile is not completed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Only enforce for logged-in students
        if (!$user || !$user->isStudent()) {
            return $next($request);
        }

        // Allow profile completion routes, profile page, and revalidation
        if (
            $request->routeIs('profile.complete.*') ||
            $request->routeIs('student.profile') ||
            $request->routeIs('student.revalidation') ||
            $request->routeIs('student.updateAcademic') ||
            $request->routeIs('student.uploadCOR') ||
            $request->routeIs('student.updateLeadership') ||
            $request->routeIs('student.changePassword') ||
            $request->routeIs('student.cor.view') ||
            $request->routeIs('student.updateAvatar')
        ) {
            return $next($request);
        }

        // Check if user is approved
        if ($user->status !== 'approved') {
            return $next($request);
        }
        if ($user->is_account_limited) {
            return $next($request);
        }
        // If profile is not completed, redirect to profile page (modal will be shown)
        if (!$user->profile_completed) {
            // For AJAX requests, return JSON response
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Please complete your profile first to continue.',
                    'redirect' => route('student.profile')
                ], 403);
            }

            return redirect()->route('student.profile')
                ->with('warning', 'Please complete your profile details first to continue.');
        }

        return $next($request);
    }
}
