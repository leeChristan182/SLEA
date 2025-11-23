<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Usage: ->middleware('role:admin')
     *        ->middleware('role:assessor,admin')
     *        ->middleware('role:student')
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        // Not logged in at all → let 'auth' / Authenticate redirect to login
        if (!$user) {
            return redirect()->route('login');
        }

        // Allowed role → proceed
        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        // Wrong role → ALWAYS go to their own landing page, never back to login
        $route = match ($user->role) {
            'admin'    => 'admin.profile',
            'assessor' => 'assessor.profile',
            default    => 'student.profile',
        };

        return redirect()->route($route);
    }
}
