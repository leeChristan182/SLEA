<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to authenticated users
        if (auth()->check()) {
            $lastActivity = $request->session()->get('last_activity');
            $currentTime = time();
            $timeout = config('session.lifetime', 120) * 60; // Convert minutes to seconds

            // Update last activity time
            $request->session()->put('last_activity', $currentTime);

            // Check if session has expired
            if ($lastActivity && ($currentTime - $lastActivity) > $timeout) {
                // Log the timeout
                \App\Models\SystemMonitoringAndLog::create([
                    'log_id' => null,
                    'user_role' => auth()->user()->user_role,
                    'user_name' => auth()->user()->name,
                    'activity_type' => 'session_timeout',
                    'description' => "Session timed out due to inactivity: " . auth()->user()->email,
                ]);

                // Logout the user
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Redirect to login with timeout message
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Session expired due to inactivity',
                        'redirect_url' => route('login.show')
                    ], 401);
                }

                return redirect()->route('login.show')
                    ->with('error', 'Your session has expired due to inactivity. Please log in again.');
            }
        }

        return $next($request);
    }
}
