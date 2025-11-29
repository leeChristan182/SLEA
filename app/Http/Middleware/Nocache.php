<?php
// app/Http/Middleware/NoCache.php
namespace App\Http\Middleware;

use Closure;

class NoCache
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Use the headers bag instead of ->header()
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

        return $response;
    }
}
