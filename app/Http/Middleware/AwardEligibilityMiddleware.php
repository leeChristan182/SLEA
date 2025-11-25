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

        // Allow these routes even if locked
        if (
            $request->routeIs('student.revalidation') ||
            $request->is('student/revalidation') ||
            $request->routeIs('student.updateAcademic') ||
            $request->routeIs('student.uploadCOR') ||
            $request->routeIs('student.updateLeadership')
        ) {
            return $next($request);
        }

        // If NOT locked, allow all normal routes
        if (! $user->awardLocked()) {
            return $next($request);
        }

        // Locked → force to revalidation page
        return redirect()->route('student.revalidation');
    }
}
