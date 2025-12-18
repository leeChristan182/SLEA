<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     * Check if the logged-in user's account has been disabled.
     * If disabled, set a session flag to show the disabled modal.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip check for logout route to allow logout to proceed
        if ($request->routeIs('logout')) {
            return $next($request);
        }
        
        // Only check for authenticated users
        if (Auth::check()) {
            $user = Auth::user();
            
            // Refresh the user model to get the latest status from database
            $user->refresh();
            
            // If user is disabled, set session flag to show modal
            if ($user->isDisabled()) {
                session()->flash('account_disabled', true);
            }
        }

        return $next($request);
    }
}

