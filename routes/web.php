<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssessorController;

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

// Login
Route::get('/', [LoginController::class, 'show'])->name('login.show');
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate'])->name('login.auth');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Registration
Route::get('/register', [RegisterController::class, 'show'])->name('register.show');
Route::post('/register', [RegisterController::class, 'submit'])->name('register.submit');


/*
|--------------------------------------------------------------------------
| STUDENT ROUTES (Protected)
|--------------------------------------------------------------------------
*/
Route::prefix('student')
    ->middleware(['auth', 'role:student', 'session.timeout'])
    ->controller(StudentController::class)
    ->group(function () {
        Route::get('/dashboard', 'dashboard')->name('student.dashboard');
        Route::get('/profile', 'profile')->name('student.profile');
        Route::get('/submit', 'submit')->name('student.submit');
        Route::get('/performance', 'performance')->name('student.performance');
        Route::get('/criteria', 'criteria')->name('student.criteria');
        Route::get('/history', 'history')->name('student.history');
    });


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth', 'role:admin'])
    ->controller(AdminController::class)
    ->group(function () {
        Route::get('/dashboard', 'dashboard')->name('admin.dashboard');
        Route::get('/profile', 'profile')->name('admin.profile');
        // Update admin profile info
        Route::put('/admin/profile/update', [AdminController::class, 'updateProfile'])
            ->name('admin.profile.update');
        Route::post('/admin/profile/avatar', [AdminController::class, 'updateAvatar'])->name('admin.profile.avatar');

        // Update admin password (the missing one)
        Route::put('/admin/profile/password', [AdminController::class, 'updatePassword'])
            ->name('admin.profile.password.update');
        // Assessor Management
        Route::post('/create-assessor', 'createAssessor')->name('admin.create.assessor');

        // System Functions
        Route::get('/approve-reject', 'approveReject')->name('admin.approve-reject');
        Route::get('/submission-oversight', 'submissionOversight')->name('admin.submission-oversight');
        Route::get('/final-review', 'finalReview')->name('admin.final-review');
        Route::get('/award-report', 'awardReport')->name('admin.award-report');
        Route::get('/system-monitoring', 'systemMonitoring')->name('admin.system-monitoring');

        // Manage Accounts
        Route::get('/manage', 'manageAccount')->name('admin.manage');
        Route::patch('/manage/{user}/toggle', 'toggleUser')->name('admin.manage.toggle');
        Route::delete('/manage/{user}', 'destroyUser')->name('admin.manage.destroy');
    });


/*
|--------------------------------------------------------------------------
| ASSESSOR ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('assessor')
    ->middleware(['auth', 'role:assessor', 'session.timeout'])
    ->controller(AssessorController::class)
    ->group(function () {
        Route::get('/dashboard', 'dashboard')->name('assessor.dashboard');
        Route::get('/profile', 'profile')->name('assessor.profile');
        Route::patch('/profile', 'updateProfile')->name('assessor.profile.update');
        Route::patch('/password', 'updatePassword')->name('assessor.password.update');
        Route::post('/profile-picture', 'updateProfilePicture')->name('assessor.profile.picture');

        // Submissions
        Route::get('/pending-submissions', 'pendingSubmissions')->name('assessor.pending-submissions');
        Route::get('/submissions', 'submissions')->name('assessor.submissions');
        Route::get('/final-review', 'finalReview')->name('assessor.final-review');

        // API for review
        Route::get('/submissions/{id}/details', 'getSubmissionDetails')->name('assessor.submission.details');
        Route::post('/submissions/{id}/action', 'handleSubmissionAction')->name('assessor.submission.action');
        Route::get('/documents/{id}/download', 'downloadDocument')->name('assessor.document.download');
    });
