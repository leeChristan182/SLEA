<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AwardEligibilityMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();
        if ($u && $u->role === 'student' && $u->isApproved() && method_exists($u, 'awardLocked') && $u->awardLocked()) {
            // let them only into revalidation page & three actions
            if ($request->routeIs([
                'student.revalidation',
                'student.updateAcademic',
                'student.uploadCOR',
                'student.updateLeadership', // if leadership edits are part of revalidation, keep; else remove
            ])) {
                return $next($request);
            }
            return redirect()->route('student.revalidation')->with('status', 'Please revalidate your academic info.');
        }
        return $next($request);
    }
}
