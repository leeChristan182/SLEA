<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\NoCache;
use App\Http\Middleware\SessionTimeout;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssessorController;
use App\Http\Controllers\SubmissionRecordController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\RubricController;
use App\Http\Controllers\AssessorSubmissionController;
use App\Http\Controllers\AssessorCompiledScoreController;
use App\Http\Controllers\AssessorFinalReviewController;
use App\Http\Controllers\FinalReviewController;
use App\Http\Controllers\AssessorStudentSubmissionController;
use App\Http\Controllers\SystemMonitoringAndLogController;
use App\Http\Controllers\ProfileCompletionController;

/*
|--------------------------------------------------------------------------
| AUTH ROUTES (guest-only)
|--------------------------------------------------------------------------
*/

Route::middleware(['guest', NoCache::class])->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login.show');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.auth')
        ->middleware('throttle:100,1');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register.show');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store')
        ->middleware('throttle:10,10');

    Route::get('/otp', [AuthController::class, 'showOtpForm'])->name('otp.show');
    Route::post('/otp', [AuthController::class, 'verifyOtp'])->name('otp.verify');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])
        ->name('password.request');

    Route::post('/forgot-password', [AuthController::class, 'sendForgotPasswordOtp'])
        ->name('password.email');

    Route::post('/otp/resend', [AuthController::class, 'resendOtp'])
        ->name('otp.resend');

    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->name('password.update');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED COMMON ROUTES
|--------------------------------------------------------------------------
*/
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Used by SessionTimeout.js to detect if the session is still valid
Route::get('/check-session', function () {
    return response()->json([
        'authenticated' => Auth::check(),
    ]);
})->name('check-session');


/*
|--------------------------------------------------------------------------
| AJAX API ROUTES (accessible to both guest and authenticated)
|--------------------------------------------------------------------------
*/
Route::prefix('api')->name('ajax.')->group(function () {
    Route::get('/programs',      [AuthController::class, 'getPrograms'])->name('programs');
    Route::get('/majors',        [AuthController::class, 'getMajors'])->name('majors');
    Route::get('/clusters',      [AuthController::class, 'getClusters'])->name('clusters');
    Route::get('/organizations', [AuthController::class, 'getOrganizations'])->name('organizations');
    Route::get('/positions',     [AuthController::class, 'getPositions'])->name('positions');

    Route::get('/academics-map',     [AuthController::class, 'getAcademicsMap'])->name('academics.map');
    Route::get('/council-positions', [AuthController::class, 'getCouncilPositions'])->name('council.positions');
    Route::get('/council-orgs',      [AuthController::class, 'getCouncilOrgs'])->name('council_orgs');
});

/*
|--------------------------------------------------------------------------
| PROFILE COMPLETION ROUTES (used by limited flow)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', NoCache::class])
    ->prefix('profile')
    ->name('profile.complete.')
    ->group(function () {
        Route::get('/student', [ProfileCompletionController::class, 'showStudentForm'])
            ->middleware('role:student')
            ->name('student');

        Route::post('/student', [ProfileCompletionController::class, 'storeStudentProfile'])
            ->middleware('role:student')
            ->name('student.store');

        Route::get('/assessor', [ProfileCompletionController::class, 'showAssessorForm'])
            ->middleware('role:assessor')
            ->name('assessor');

        Route::post('/assessor', [ProfileCompletionController::class, 'storeAssessorProfile'])
            ->middleware('role:assessor')
            ->name('assessor.store');
    });

/*
|--------------------------------------------------------------------------
| STUDENT ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('student')
    ->middleware(['auth', NoCache::class, 'role:student', \App\Http\Middleware\EnsureLimitedFlow::class])
    ->name('student.')
    ->controller(StudentController::class)
    ->group(function () {
        Route::get('/profile', 'profile')->name('profile');

        Route::get('/revalidation', 'revalidation')->name('revalidation');
        Route::post('/update-academic', 'updateAcademicInfo')->name('updateAcademic');
        Route::post('/upload-cor', 'uploadCOR')->name('uploadCOR');
        Route::post('/update-leadership', 'updateLeadership')->name('updateLeadership');
        Route::post('/profile/avatar', 'updateAvatar')->name('updateAvatar');
        Route::post('/change-password', 'changePassword')->name('changePassword');
        Route::get('/cor/view', 'viewCOR')->name('cor.view');

        Route::middleware('eligible')->group(function () {
            Route::get('/criteria', 'criteria')->name('criteria');
            Route::get('/performance', 'performance')->name('performance');
            Route::get('/history', 'history')->name('history');

            Route::get('/submit', [SubmissionRecordController::class, 'create'])
                ->name('submit');

            Route::get('/submissions', [SubmissionRecordController::class, 'index'])
                ->name('submissions.index');

            Route::post('/submissions', [SubmissionRecordController::class, 'store'])
                ->name('submissions.store');

            Route::get('/submissions/preview/{id}', [SubmissionRecordController::class, 'preview'])
                ->name('submissions.preview');
        });
    });

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth', NoCache::class, 'role:admin'])
    ->name('admin.')
    ->group(function () {
        Route::get('/', fn() => redirect()->route('admin.dashboard'))->name('home');

        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
        Route::put('/profile/update', [AdminController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/avatar', [AdminController::class, 'updateAvatar'])->name('profile.avatar');
        Route::put('/profile/password', [AdminController::class, 'updatePassword'])->name('profile.password.update');

        Route::get('/initial-validation', [AdminController::class, 'initialValidationQueue'])
            ->name('initial-validation');
        Route::post('/initial-validation/{user}/approve', [AdminController::class, 'approveInitialValidation'])
            ->name('initial-validation.approve');
        Route::post('/initial-validation/{user}/reject', [AdminController::class, 'rejectInitialValidation'])
            ->name('initial-validation.reject');

        Route::get('/students/{user}/cor', [AdminController::class, 'viewStudentCor'])
            ->name('students.cor');

        Route::get('/approve-reject', [AdminController::class, 'approveReject'])->name('approve-reject');
        Route::post('/approve/{user_id}', [AdminController::class, 'approveUser'])->name('approve');
        Route::post('/reject/{user_id}', [AdminController::class, 'rejectUser'])->name('reject');

        Route::get('/manage', [AdminController::class, 'manageAccount'])->name('manage-account');
        Route::patch('/manage/{user}/toggle', [AdminController::class, 'toggleUser'])->name('manage.toggle');

        Route::get('/revalidation', [AdminController::class, 'revalidationQueue'])->name('revalidation');
        Route::post('/revalidation/{user}/approve', [AdminController::class, 'approveRevalidation'])->name('revalidation.approve');
        Route::post('/revalidation/{user}/reject',  [AdminController::class, 'rejectRevalidation'])->name('revalidation.reject');
        Route::get('/revalidation/{user}/cor', [AdminController::class, 'viewStudentCor'])
            ->name('revalidation.cor');

        Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
        Route::post('/organizations', [OrganizationController::class, 'store'])->name('organizations.store');
        Route::put('/organizations/{organization}', [OrganizationController::class, 'update'])->name('organizations.update');
        Route::delete('/organizations/{organization}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');

        Route::prefix('rubrics')->name('rubrics.')->group(function () {
            Route::get('/', [RubricController::class, 'index'])->name('index');

            Route::post('/categories', [RubricController::class, 'categoryStore'])->name('categories.store');
            Route::put('/categories/{category}', [RubricController::class, 'categoryUpdate'])->name('categories.update');
            Route::delete('/categories/{category}', [RubricController::class, 'categoryDestroy'])->name('categories.destroy');

            Route::post('/sections', [RubricController::class, 'sectionStore'])->name('sections.store');
            Route::put('/sections/{section}', [RubricController::class, 'sectionUpdate'])->name('sections.update');
            Route::delete('/sections/{section}', [RubricController::class, 'sectionDestroy'])->name('sections.destroy');

            Route::post('/subsections', [RubricController::class, 'subsectionStore'])->name('subsections.store');
            Route::put('/subsections/{subsection}', [RubricController::class, 'subsectionUpdate'])->name('subsections.update');
            Route::delete('/subsections/{subsection}', [RubricController::class, 'subsectionDestroy'])->name('subsections.destroy');

            Route::post('/options', [RubricController::class, 'optionStore'])->name('options.store');
            Route::put('/options/{option}', [RubricController::class, 'optionUpdate'])->name('options.update');
            Route::delete('/options/{option}', [RubricController::class, 'optionDestroy'])->name('options.destroy');
        });

        Route::get('/final-review', [FinalReviewController::class, 'index'])->name('final-review');
        Route::post('/final-review/{assessorFinalReview}', [FinalReviewController::class, 'storeDecision'])
            ->name('final-review.decision');

        Route::get('/award-report', [AdminController::class, 'awardReport'])->name('award-report');
        Route::get('/award-report/export', [AdminController::class, 'exportAwardReport'])->name('award-report.export');

        Route::get('/system-monitoring', [SystemMonitoringAndLogController::class, 'index'])
            ->name('system-logs.index');
        Route::delete('/system-monitoring', [SystemMonitoringAndLogController::class, 'clearAll'])
            ->name('system-logs.clear');
    });

/*
|--------------------------------------------------------------------------
| ASSESSOR ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('assessor')
    ->middleware(['auth', NoCache::class, 'role:assessor,admin', \App\Http\Middleware\EnsureLimitedFlow::class])
    ->name('assessor.')
    ->group(function () {

        Route::controller(AssessorController::class)->group(function () {
            Route::get('/', fn() => redirect()->route('assessor.dashboard'))->name('home');

            Route::get('/dashboard', [AssessorController::class, 'dashboard'])->name('dashboard');
            Route::get('/profile', 'profile')->name('profile');
            Route::put('/profile', 'updateProfile')->name('profile.update');
            Route::patch('/password', 'updatePassword')->name('password.update');
            Route::post('/profile/picture', 'updateAvatar')->name('profile.picture');
        });

        Route::controller(AssessorSubmissionController::class)->group(function () {
            Route::get('/submissions/pending-submissions', 'pending')->name('submissions.pending-submissions');
            Route::get('/submissions/{submission}/details', 'details')->name('submissions.details');
            Route::post('/submissions/{submission}/action', 'handleAction')->name('submissions.action');

            Route::get('/documents/{documentId}/view', 'viewDocument')->name('documents.view');
            Route::get('/documents/{documentId}/download', 'downloadDocument')->name('documents.download');
        });

        Route::get('/students/submissions', [AssessorStudentSubmissionController::class, 'index'])
            ->name('students.submissions');
        Route::get('/students/{student}/details', [AssessorStudentSubmissionController::class, 'studentDetails'])
            ->name('students.details');
        Route::post('/students/{student}/ready-status', [AssessorStudentSubmissionController::class, 'updateReadyStatus'])
            ->name('students.ready-status');

        Route::get('/submissions/compiled', [AssessorCompiledScoreController::class, 'index'])
            ->name('submissions.compiled');

        Route::get('/final-review', [AssessorFinalReviewController::class, 'index'])
            ->name('final-review.index');
        Route::post('/final-review/{student}', [AssessorFinalReviewController::class, 'storeForStudent'])
            ->name('final-review.store');
        Route::post('/final-review/{student}/reject', [AssessorFinalReviewController::class, 'rejectForStudent'])
            ->name('final-review.reject');
    });

Route::fallback(function () {
    return response()->view('errors.route-not-found', [], 404);
});
