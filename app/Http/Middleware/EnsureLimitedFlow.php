<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class EnsureLimitedFlow
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (! $user) return $next($request);

        // Admin bypass
        if ($user->isAdmin()) return $next($request);

        // Only apply “limited flow” rules to approved + assigned users
        if ($user->status !== User::STATUS_APPROVED) return $next($request);
        if ($user->role === User::ROLE_UNASSIGNED) return $next($request);

        // If not limited, no restrictions at all
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

        /**
         * ✅ STUDENT: keep strict gating
         */
        if ($user->role === User::ROLE_STUDENT) {
            $studentSubmitted =
                $user->studentAcademic &&
                in_array($user->studentAcademic->eligibility_status, ['under_review', 'needs_revalidation', 'eligible'], true);

            if (! $studentSubmitted) {
                return redirect()->route('profile.complete.student');
            }

            // submitted but still limited → keep them on profile
            if (! $request->routeIs('student.profile')) {
                return redirect()->route('student.profile')->with('show_waiting_modal', true);
            }

            return $next($request);
        }

        /**
         * ✅ ASSESSOR: SOFT gating (allow navigation)
         * Only block critical “finalize/submit” actions by route name.
         */
        if ($user->role === User::ROLE_ASSESSOR) {
            $assessorComplete =
                $user->assessorInfo &&
                !empty($user->assessorInfo->office_unit) &&
                !empty($user->assessorInfo->position);

            // show a banner/modal flag if incomplete
            if (! $assessorComplete) {
                session()->flash('assessor_profile_incomplete', true);

                // Block only final/submit routes (edit these route names to match yours)
                if ($request->routeIs(
                    'assessor.reviews.finalize',
                    'assessor.reviews.submit',
                    'assessor.final-review.submit'
                )) {
                    return redirect()
                        ->back()
                        ->withErrors(['profile' => 'Please complete your assessor profile before finalizing/submitting reviews.']);
                }
            }

            // ✅ allow everything else
            return $next($request);
        }

        return $next($request);
    }
}
