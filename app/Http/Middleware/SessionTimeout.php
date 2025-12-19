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
        // These should NOT count as activity
        $isCheckSession = $request->is('check-session');
        $isAjaxApi      = $request->is('api/*');

        // This SHOULD count as activity (user explicitly clicked "Stay")
        $isKeepAlive    = $request->is('keep-alive');

        if (!Auth::check()) {
            return $next($request);
        }

        $now = time();
        $lastActivity = (int) $request->session()->get('last_activity', $now);

        $timeoutSeconds = (int) config('session.lifetime', 120) * 60;

        // Expired => force logout
        if (($now - $lastActivity) > $timeoutSeconds) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $wantsJson =
                $request->expectsJson() ||
                $request->wantsJson() ||
                $request->ajax() ||
                str_contains((string) $request->header('Accept'), 'application/json');

            if ($wantsJson) {
                return response()->json([
                    'authenticated' => false,
                    'message'       => 'Session expired due to inactivity.',
                    'redirect_url'  => route('login.show'),
                ], 401);
            }

            return redirect()
                ->route('login.show')
                ->with('error', 'Your session has expired due to inactivity. Please log in again.');
        }

        /**
         * Only update activity when it reflects real user interaction:
         * - keep-alive => YES
         * - check-session => NO
         * - api/* ajax endpoints => NO
         * - POST/PUT/PATCH/DELETE => YES
         * - GET full page navigation => YES (non-AJAX)
         */
        $isGet  = $request->isMethod('GET');
        $isAjax = $request->ajax() ||
            str_contains((string) $request->header('X-Requested-With'), 'XMLHttpRequest');

        $shouldUpdate =
            $isKeepAlive ||
            (
                !$isCheckSession &&
                !$isAjaxApi &&
                (
                    (!$isGet) ||          // POST/PUT/PATCH/DELETE
                    ($isGet && !$isAjax)  // full page load/navigation
                )
            );

        if ($shouldUpdate) {
            $request->session()->put('last_activity', $now);
        }

        return $next($request);
    }
}
