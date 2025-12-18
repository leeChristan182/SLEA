<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class SessionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        // ✅ Always allow check-session to pass (so polling doesn't count as activity)
        if ($request->is('check-session')) {
            return $next($request);
        }

        // Only apply to authenticated users
        if (Auth::check()) {
            $lastActivity = $request->session()->get('last_activity');
            $now = time();

            // session.lifetime is MINUTES
            $timeoutSeconds = (int) config('session.lifetime', 120) * 60;

            // If expired -> logout and redirect/JSON
            if ($lastActivity && ($now - $lastActivity) > $timeoutSeconds) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson() || $request->wantsJson()) {
                    return response()->json([
                        'authenticated' => false,
                        'message' => 'Session expired due to inactivity.',
                        'redirect_url' => route('login.show'),
                    ], 401);
                }

                return redirect()
                    ->route('login.show')
                    ->with('error', 'Your session has expired due to inactivity. Please log in again.');
            }

            // ✅ only update last_activity for real page navigation / actions
            $request->session()->put('last_activity', $now);
        }

        // ✅ ALWAYS return response
        return $next($request);
    }
}
