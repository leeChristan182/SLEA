<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AwardEligibilityMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Only enforce for logged-in students
        if (! $user || ! $user->isStudent()) {
            return $next($request);
        }

        /**
         * 🚫 DO NOT run revalidation logic
         * until onboarding is complete
         */
        if (! $user->profile_completed) {
            return $next($request);
        }

        /**
         * ✅ Always allow revalidation-related routes
         */
        if ($request->routeIs(
            'student.revalidation',
            'student.updateAcademic',
            'student.uploadCOR',
            'student.updateLeadership'
        )) {
            return $next($request);
        }

        /**
         * If student is NOT locked → allow normal access
         */
        if (! $user->awardLocked()) {
            return $next($request);
        }

        /**
         * 🔒 Locked due to exceeded expected_grad_year
         * Force revalidation
         */
        return redirect()
            ->route('student.revalidation')
            ->with(
                'warning',
                'Your academic information requires revalidation because your expected year to graduate has been exceeded.'
            );
    }
}
