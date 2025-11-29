<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\AwardEligibilityMiddleware;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\RequirePasswordChange;

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
            'require.password.change' => RequirePasswordChange::class,

        ]);

        // Add CheckUserStatus to web middleware group for all authenticated routes
        $middleware->appendToGroup('web', [CheckUserStatus::class]);

        // (optional) add something to groups
        // $middleware->appendToGroup('web', [ ... ]);
        // $middleware->appendToGroup('api', [ ... ]);
    })
    ->withExceptions(function ($exceptions) {
        //
    })->create();
