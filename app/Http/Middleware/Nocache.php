<?php
// app/Http/Middleware/NoCache.php
namespace App\Http\Middleware;

use Closure;

class NoCache
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        /**
         * Reduce "white flash" between navigations by allowing the browser to keep a cached copy
         * but always revalidate it. This keeps security reasonable while avoiding full reload feel.
         */
        $response->headers->set('Cache-Control', 'private, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
