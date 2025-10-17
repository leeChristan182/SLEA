<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        View::composer('*', function ($view) {
            $user = auth()->user();
            $role = null;

            if ($user instanceof \App\Models\AdminAccount) $role = 'admin';
            elseif ($user instanceof \App\Models\AssessorAccount) $role = 'assessor';
            elseif ($user) $role = 'student';

            $view->with('currentRole', $role);
        });
    }
}
