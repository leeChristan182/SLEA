<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\StudentAcademic;

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
    public function boot(): void
    {
        /**
         * ROLE + SLEA STATUS SHARING FOR ALL VIEWS
         */
        View::composer('*', function ($view) {

            $user = auth()->user();
            $role = null;
            $sleaApplicationStatus = null;
            $sleaAwarded = false;

            /** Determine role */
            if ($user instanceof \App\Models\AdminAccount) {
                $role = 'admin';
            } elseif ($user instanceof \App\Models\AssessorAccount) {
                $role = 'assessor';
            } elseif ($user) {
                $role = 'student';
            }

            /** Share SLEA Award Status ONLY FOR STUDENTS */
            if ($user && $role === 'student') {
                $academic = $user->studentAcademic; // must match relationship

                if ($academic) {
                    $sleaApplicationStatus = $academic->slea_application_status;
                    $sleaAwarded = ($sleaApplicationStatus === 'awarded');
                }
            }

            /** Make available to all Blade views */
            $view->with([
                'currentRole'             => $role,
                'sleaApplicationStatus'   => $sleaApplicationStatus,
                'sleaAwarded'             => $sleaAwarded,
            ]);
        });

        /**
         * OVERRIDE EMAILS DURING DEVELOPMENT
         */
        if (app()->environment(['local', 'development'])) {
            $override = env('MAIL_TO_OVERRIDE');
            if ($override) {
                Mail::alwaysTo($override);
            }
        }
    }
}
