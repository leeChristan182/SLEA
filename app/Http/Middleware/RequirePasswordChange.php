<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * For assessors, check if they must change their password.
     * If yes, redirect to profile page (except for profile and password update routes).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to assessors
        if (Auth::check() && Auth::user()->isAssessor()) {
            $user = Auth::user();

            // Check if assessor must change password
            $assessorInfo = $user->assessorInfo;
            if ($assessorInfo && $assessorInfo->must_change_password) {
                // Allow access to profile and password update routes
                $allowedRoutes = [
                    'assessor.profile',
                    'assessor.profile.update',
                    'assessor.password.update',
                    'assessor.profile.picture', // Allow avatar update
                ];

                if (!in_array($request->route()->getName(), $allowedRoutes)) {
                    // Redirect to profile with flag to show modal
                    return redirect()
                        ->route('assessor.profile')
                        ->with('must_change_password', true);
                }
            }
        }

        return $next($request);
    }
}
