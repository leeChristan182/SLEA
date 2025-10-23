<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssessorController;
use App\Http\Controllers\RubricController;

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

// Only allow guests to view login/register
Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'show'])->name('login.show');
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate'])->name('login.auth');

    // Registration routes (if only students can register, leave inside guest)
    Route::get('/register', [RegisterController::class, 'show'])->name('register.show');
    Route::post('/register', [RegisterController::class, 'submit'])->name('register.submit');
});

// Logout should stay outside (available to logged-in users)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


/*
|--------------------------------------------------------------------------
| STUDENT ROUTES (Protected)
|--------------------------------------------------------------------------
*/
Route::prefix('student')
    ->middleware(['auth:student', 'role:student', 'session.timeout'])
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
    ->middleware(['auth:admin', 'role:admin'])
    ->group(function () {

        // PROFILE & DASHBOARD
        Route::get('/profile', [AdminController::class, 'profile'])->name('admin.profile');
        Route::put('/profile/update', [AdminController::class, 'updateProfile'])->name('admin.profile.update');
        Route::post('/profile/avatar', [AdminController::class, 'updateAvatar'])->name('admin.profile.avatar');
        Route::put('/profile/password', [AdminController::class, 'updatePassword'])->name('admin.profile.password.update');

        // USER ACCOUNT MANAGEMENT
        Route::get('/create-assessor', [AdminController::class, 'createAssessor'])->name('admin.create_assessor');
        Route::post('/create-assessor', [AdminController::class, 'storeAssessor'])->name('admin.store_assessor');
        Route::get('/approve-reject', [AdminController::class, 'approveReject'])->name('admin.approve-reject');
        Route::post('/approve/{user}', [AdminController::class, 'approveUser'])->name('admin.approve');
        Route::post('/reject/{user}', [AdminController::class, 'rejectUser'])->name('admin.reject');
        Route::get('/manage', [AdminController::class, 'manageAccount'])->name('admin.manage');
        Route::patch('/manage/{user}/toggle', [AdminController::class, 'toggleUser'])->name('admin.manage.toggle');
        Route::delete('/manage/{user}', [AdminController::class, 'destroyUser'])->name('admin.manage.destroy');


        Route::get('/rubrics', [RubricController::class, 'index'])->name('admin.rubrics.index');
        Route::post('/rubrics', [RubricController::class, 'store'])->name('admin.rubrics.store');
        Route::patch('/rubrics/{id}', [RubricController::class, 'update'])->name('admin.rubrics.update');
        Route::delete('/rubrics/{id}', [RubricController::class, 'destroy'])->name('admin.rubrics.destroy');

        // SYSTEM OPERATIONS
        Route::get('/submission-oversight', [AdminController::class, 'submissionOversight'])->name('admin.submission-oversight');
        Route::get('/final-review', [AdminController::class, 'finalReview'])->name('admin.final-review');
        Route::get('/award-report', [AdminController::class, 'awardReport'])->name('admin.award-report');
        Route::get('/system-monitoring', [AdminController::class, 'systemMonitoring'])->name('admin.system-monitoring');
    });


/*
|--------------------------------------------------------------------------
| ASSESSOR ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('assessor')
    ->middleware(['auth:assessor', 'role:assessor', 'session.timeout'])
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
