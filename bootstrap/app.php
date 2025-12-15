<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\AwardEligibilityMiddleware;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\EnsureStudentProfileApproved;
use App\Http\Middleware\RequireProfileCompletion;
use App\Http\Middleware\SessionTimeout;

return Illuminate\Foundation\Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // register as a route middleware alias
        $middleware->alias([
            'eligible' => AwardEligibilityMiddleware::class,
            'role' => RoleMiddleware::class,
            'guest' => RedirectIfAuthenticated::class,
            'check.status' => CheckUserStatus::class,
            'require.profile.completion' => \App\Http\Middleware\EnsureLimitedFlow::class,


        ]);

        // Add CheckUserStatus to web middleware group for all authenticated routes
        $middleware->appendToGroup('web', [
            SessionTimeout::class,
            CheckUserStatus::class,
        ]);
        // (optional) add something to groups
        // $middleware->appendToGroup('web', [ ... ]);
        // $middleware->appendToGroup('api', [ ... ]);
    })
    ->withExceptions(function ($exceptions) {
        // Handle 419 CSRF token mismatch errors
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'CSRF token mismatch. Please refresh the page and try again.'], 419);
            }
            
            // For login page, redirect back with error message
            if ($request->is('login') || $request->routeIs('login.*')) {
                return redirect()->route('login.show')
                    ->withErrors(['email' => 'Your session has expired. Please try logging in again.'])
                    ->withInput($request->except('password', '_token'));
            }
            
            // For other pages, redirect back with error
            return back()->withErrors(['error' => 'Your session has expired. Please refresh the page and try again.'])->withInput();
        });
    })->create();
