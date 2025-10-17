<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = Auth::user();

        // If no user is authenticated
        if (!$user) {
            return redirect()->route('login.show');
        }

        // If user role doesn’t match middleware requirement
        if (method_exists($user, 'getTable') && property_exists($user, 'user_role')) {
            $userRole = $user->user_role;
        } elseif (isset($user->position)) {
            // Fallback for AdminAccount (no user_role field)
            $userRole = 'admin';
        } else {
            $userRole = null;
        }

        if ($userRole !== $role) {
            return $this->redirectDashboard($userRole);
        }

        return $next($request);
    }

    /**
     * Redirect user to their correct dashboard
     */
    protected function redirectDashboard(?string $role)
    {
        return match ($role) {
            'admin'    => redirect()->route('admin.dashboard'),
            'assessor' => redirect()->route('assessor.dashboard'),
            'student'  => redirect()->route('student.dashboard'),
            default    => redirect()->route('login.show'),
        };
    }
}
