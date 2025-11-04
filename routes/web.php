<?php

use Illuminate\support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssessorController;
use App\Http\Controllers\RubricController;
use App\Http\Controllers\ApprovalAccountController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\RubricSectionController;
use App\Http\Controllers\RubricSubsectionLeadershipController;
/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

// Only allow guests to view login/register
Route::group([
    'middleware' => function ($request, $next) {
        foreach (['admin', 'assessor', 'student'] as $guard) {
            if (Auth::guard($guard)->check()) {
                return match ($guard) {
                    'admin' => redirect()->route('admin.profile'),
                    'assessor' => redirect()->route('assessor.profile'),
                    'student' => redirect()->route('student.profile'),
                };
            }
        }
        return $next($request);
    }
], function () {
    Route::get('/', [LoginController::class, 'show'])->name('login.show');
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate'])->name('login.auth');

    // Registration routes (student only)
    Route::get('/register', [RegisterController::class, 'show'])->name('register.show');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
    Route::get('/ajax/get-organizations', [RegisterController::class, 'getOrganizations'])->name('ajax.organizations');
    Route::get('/ajax/get-leadership-types', [RegisterController::class, 'getLeadershipTypes'])->name('ajax.leadership.types');
    Route::get('/ajax/get-programs', [RegisterController::class, 'getPrograms']);
    Route::get('/ajax/get-clusters', [RegisterController::class, 'getClusters']);
    Route::get('/ajax/get-positions', [RegisterController::class, 'getPositions']);
});


// Logout should stay outside (available to logged-in users)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


/*
|--------------------------------------------------------------------------
| STUDENT ROUTES (Protected)
|--------------------------------------------------------------------------
*/
Route::prefix('student')
    ->middleware(['auth:student'])
    ->controller(StudentController::class)
    ->group(function () {
        Route::get('/profile', [StudentController::class, 'profile'])->name('student.profile');
        Route::post('/update-academic', [StudentController::class, 'updateAcademicInfo'])->name('student.updateAcademic');
        Route::post('/change-password', [StudentController::class, 'changePassword'])->name('student.changePassword');
        Route::post('/upload-cor', [StudentController::class, 'uploadCOR'])->name('student.uploadCOR');
        Route::post('/update-avatar', [StudentController::class, 'updateAvatar'])->name('student.updateAvatar');
        Route::get('/submit', [StudentController::class, 'submit'])->name('student.submit');
        Route::get('/performance', [StudentController::class, 'performance'])->name('student.performance');
        Route::get('/criteria', [StudentController::class, 'criteria'])->name('student.criteria');
        Route::get('/history', [StudentController::class, 'history'])->name('student.history');
    });


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth:admin'])

    ->group(function () {

        // PROFILE & DASHBOARD
        Route::get('/profile', [AdminController::class, 'profile'])->name('admin.profile');
        Route::put('/profile/update', [AdminController::class, 'updateProfile'])->name('admin.profile.update');
        Route::post('/profile/avatar', [AdminController::class, 'updateAvatar'])->name('admin.profile.avatar');
        Route::put('/profile/password', [AdminController::class, 'updatePassword'])->name('admin.profile.password.update');

        // USER ACCOUNT MANAGEMENT
        Route::get('/create_assessor', [AdminController::class, 'createAssessor'])->name('admin.create_assessor');
        Route::post('/create_assessor', [AdminController::class, 'storeAssessor'])->name('admin.store_assessor');
        Route::get('/approve-reject', [AdminController::class, 'approveReject'])->name('admin.approve-reject');
        Route::post('/approve/{student_id}', [AdminController::class, 'approveUser'])->name('admin.approve');
        Route::post('/reject/{student_id}', [AdminController::class, 'rejectUser'])->name('admin.reject');
        Route::get('/manage', [AdminController::class, 'manageAccount'])->name('admin.manage');
        Route::patch('/manage/{user}/toggle', [AdminController::class, 'toggleUser'])->name('admin.manage.toggle');
        Route::delete('/manage/{user}', [AdminController::class, 'destroyUser'])->name('admin.manage.destroy');
        // ORGANIZATION MANAGEMENT
        Route::get('/organizations', [OrganizationController::class, 'index'])->name('admin.organizations.index');
        Route::post('/organizations', [OrganizationController::class, 'store'])->name('admin.organizations.store');
        Route::put('/organizations/{organization}', [OrganizationController::class, 'update'])->name('admin.organizations.update');
        Route::delete('/organizations/{organization}', [OrganizationController::class, 'destroy'])->name('admin.organizations.destroy');

        Route::get('/rubrics', [RubricSectionController::class, 'index'])->name('admin.rubrics.index');
        Route::post('/rubrics', [RubricSectionController::class, 'store'])->name('admin.rubrics.store');

        // Leadership CRUD under /admin/rubrics/leadership
        Route::prefix('rubrics')->group(function () {
            Route::resource('leadership', RubricSubsectionLeadershipController::class)
                ->except(['show'])
                ->names('admin.rubrics.leadership');
        });
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
    ->middleware(['auth:assessor'])
    ->controller(AssessorController::class)
    ->group(function () {
        Route::get('/profile', 'profile')->name('assessor.profile');
        Route::put('/profile', 'updateProfile')->name('assessor.profile.update');
        Route::patch('/password', 'updatePassword')->name('assessor.password.update');
        Route::post('/profile/picture', 'updateAvatar')->name('assessor.profile.picture');

        // Submissions
        Route::get('/pending-submissions', 'pendingSubmissions')->name('assessor.pending-submissions');
        Route::get('/submissions', 'submissions')->name('assessor.submissions');
        Route::get('/final-review', 'finalReview')->name('assessor.final-review');

        // API for review
        Route::get('/submissions/{id}/details', 'getSubmissionDetails')->name('assessor.submission.details');
        Route::post('/submissions/{id}/action', 'handleSubmissionAction')->name('assessor.submission.action');
        Route::get('/documents/{id}/download', 'downloadDocument')->name('assessor.document.download');
    });
