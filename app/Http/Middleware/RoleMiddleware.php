<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (!$user) {
            abort(401);
        }

        // allow "role:admin,assessor" or "role:admin|assessor"
        $needles = [];
        foreach ($roles as $arg) {
            foreach (preg_split('/[|,]/', (string)$arg) as $r) {
                $r = trim($r);
                if ($r !== '') $needles[] = $r;
            }
        }

        if (empty($needles) || in_array($user->role, $needles, true)) {
            return $next($request);
        }

        abort(403);
    }
}
