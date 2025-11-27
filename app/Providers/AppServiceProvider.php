<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /**
         * SHARE ROLE + SLEA STATUS WITH ALL VIEWS
         */
        View::composer('*', function ($view) {

            $user = auth()->user();
            $role = null;
            $sleaApplicationStatus = null;
            $sleaAwarded = false;

            /** Determine role */
            if ($user) {
                $role = $user->role;   // <-- FIXED
            }

            /** Only students have SLEA status */
            if ($role === 'student' && $user->studentAcademic) {
                $academic = $user->studentAcademic;
                $sleaApplicationStatus = $academic->slea_application_status;
                $sleaAwarded = ($sleaApplicationStatus === 'qualified');
            }

            /** Make available globally */
            $view->with([
                'currentRole'           => $role,
                'sleaApplicationStatus' => $sleaApplicationStatus,
                'sleaAwarded'           => $sleaAwarded,
            ]);
        });

        /**
         * OVERRIDE EMAIL DURING DEVELOPMENT
         */
        if (app()->environment(['local', 'development'])) {
            $override = env('MAIL_TO_OVERRIDE');
            if ($override) {
                Mail::alwaysTo($override);
            }
        }
    }
}
