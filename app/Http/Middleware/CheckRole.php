<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        // ✅ Iterate through all known guards
        foreach (['admin', 'assessor', 'student'] as $guard) {
            if (Auth::guard($guard)->check()) {
                // Found an authenticated user
                if ($guard === $role) {
                    return $next($request); // correct role → allow access
                }

                // Wrong role → redirect to their proper dashboard
                return $this->redirectDashboard($guard);
            }
        }

        // No authenticated user in any guard → go to login
        return redirect()->route('login.show');
    }

    /**
     * Redirect user to their correct dashboard based on guard
     */
    protected function redirectDashboard(string $guard)
    {
        return match ($guard) {
            'admin'    => redirect()->route('admin.profile'),
            'assessor' => redirect()->route('assessor.profile'),
            'student'  => redirect()->route('student.profile'),
            default    => redirect()->route('login.show'),
        };
    }
}
