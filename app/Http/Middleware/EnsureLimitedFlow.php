<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class EnsureLimitedFlow
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (! $user) return $next($request);

        if ($user->isAdmin()) return $next($request);

        if ($user->status !== User::STATUS_APPROVED) return $next($request);
        if ($user->role === User::ROLE_UNASSIGNED) return $next($request);

        if (! $user->is_account_limited) return $next($request);

        // Always allowed while limited
        if ($request->routeIs(
            'login.*',
            'logout',
            'otp.*',
            'password.*',
            'check-session',
            'profile.complete.student*',
            'profile.complete.assessor*'
        )) {
            return $next($request);
        }

        // ✅ Phase 1: limited + not yet submitted requirements
        // ✅ Phase 1/2 logic WITHOUT using profile_completed

        $studentSubmitted = false;
        $assessorSubmitted = false;

        if ($user->role === User::ROLE_STUDENT) {
            $studentSubmitted =
                $user->studentAcademic &&
                in_array($user->studentAcademic->eligibility_status, ['under_review', 'needs_revalidation', 'eligible'], true);
        }

        if ($user->role === User::ROLE_ASSESSOR) {
            $assessorSubmitted =
                $user->assessorInfo &&
                !empty($user->assessorInfo->office_unit) &&
                !empty($user->assessorInfo->position);
        }

        // ✅ Phase 1: limited + NOT submitted yet -> force complete-requirements
        if ($user->role === User::ROLE_STUDENT && ! $studentSubmitted) {
            return redirect()->route('profile.complete.student');
        }

        if ($user->role === User::ROLE_ASSESSOR && ! $assessorSubmitted) {
            return redirect()->route('profile.complete.assessor');
        }

        // ✅ Phase 2: submitted, still limited -> force profile page + popup
        if ($user->role === User::ROLE_STUDENT && ! $request->routeIs('student.profile')) {
            return redirect()->route('student.profile')->with('show_waiting_modal', true);
        }

        if ($user->role === User::ROLE_ASSESSOR && ! $request->routeIs('assessor.profile')) {
            return redirect()->route('assessor.profile')->with('show_waiting_modal', true);
        }

        return $next($request);

        // ✅ Phase 2: submitted, still limited (waiting for admin)
        // Force them to stay on their profile only + show popup there
        if ($user->role === User::ROLE_STUDENT) {
            if (! $request->routeIs('student.profile')) {
                return redirect()->route('student.profile')->with('show_waiting_modal', true);
            }
            return $next($request);
        }

        if ($user->role === User::ROLE_ASSESSOR) {
            if (! $request->routeIs('assessor.profile')) {
                return redirect()->route('assessor.profile')->with('show_waiting_modal', true);
            }
            return $next($request);
        }

        return $next($request);
    }
}
