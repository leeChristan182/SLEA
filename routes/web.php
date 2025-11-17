<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssessorController;
use App\Http\Controllers\SubmissionRecordController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\RubricSectionController;
use App\Http\Controllers\RubricOptionController;
use App\Http\Controllers\AssessorSubmissionController;
use App\Http\Controllers\AssessorCompiledScoreController;
use App\Http\Controllers\AssessorFinalReviewController;
use App\Http\Controllers\FinalReviewController;
/*
|--------------------------------------------------------------------------
| AUTH ROUTES (guest-only)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login.show');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.auth');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register.show');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

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

    Route::get('/otp',         [AuthController::class, 'showOtp'])->name('otp.show');
    Route::post('/otp',        [AuthController::class, 'verifyOtp'])->name('otp.verify');
    Route::post('/otp/resend', [AuthController::class, 'resendOtp'])->name('otp.resend');
});

// logout still just needs auth
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
/*
|--------------------------------------------------------------------------
| STUDENT ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('student')
    ->middleware(['auth', 'role:student'])
    ->name('student.')
    ->controller(StudentController::class)
    ->group(function () {

        Route::get('/revalidation', 'revalidation')->name('revalidation');
        Route::post('/update-academic', 'updateAcademicInfo')->name('updateAcademic');
        Route::post('/upload-cor', 'uploadCOR')->name('uploadCOR');
        Route::post('/update-leadership', 'updateLeadership')->name('updateLeadership');
        Route::post('/profile/avatar', 'updateAvatar')->name('updateAvatar');
        Route::post('/change-password', 'changePassword')->name('changePassword');

        Route::middleware('eligible')->group(function () {
            Route::get('/profile', 'profile')->name('profile');
            Route::get('/performance', 'performance')->name('performance');
            Route::get('/criteria', 'criteria')->name('criteria');
            Route::get('/history', 'history')->name('history');

            // *** ONLY ONE submit route ***
            Route::get('/submit', [SubmissionRecordController::class, 'create'])
                ->name('submit');

            // list submissions (student dashboard)
            Route::get('/submissions', [SubmissionRecordController::class, 'index'])
                ->name('submissions.index');

            // save
            Route::post('/submissions', [SubmissionRecordController::class, 'store'])
                ->name('submissions.store');

            // download
            Route::get('/submissions/download/{id}', [SubmissionRecordController::class, 'download'])
                ->name('submissions.download');
        });
    });


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware(['auth', 'role:admin'])
    ->name('admin.')
    ->group(function () {
        Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
        Route::put('/profile/update', [AdminController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/avatar', [AdminController::class, 'updateAvatar'])->name('profile.avatar');
        Route::put('/profile/password', [AdminController::class, 'updatePassword'])->name('profile.password.update');

        Route::get('/create_user', [AdminController::class, 'createUser'])->name('create_user');
        Route::post('/create_user', [AdminController::class, 'storeUser'])->name('store_user');
        Route::get('/approve-reject', [AdminController::class, 'approveReject'])->name('approve-reject');
        Route::post('/approve/{student_id}', [AdminController::class, 'approveUser'])->name('approve');
        Route::post('/reject/{student_id}', [AdminController::class, 'rejectUser'])->name('reject');

        Route::get('/manage', [AdminController::class, 'manageAccount'])->name('manage-account');
        Route::patch('/manage/{user}/toggle', [AdminController::class, 'toggleUser'])->name('manage.toggle');
        Route::delete('/manage/{user}', [AdminController::class, 'destroyUser'])->name('manage.destroy');

        Route::get('/revalidation', [AdminController::class, 'revalidationQueue'])->name('revalidation');
        Route::post('/revalidation/{user}/approve', [AdminController::class, 'approveRevalidation'])->name('revalidation.approve');
        Route::post('/revalidation/{user}/reject',  [AdminController::class, 'rejectRevalidation'])->name('revalidation.reject');

        Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
        Route::post('/organizations', [OrganizationController::class, 'store'])->name('organizations.store');
        Route::put('/organizations/{organization}', [OrganizationController::class, 'update'])->name('organizations.update');
        Route::delete('/organizations/{organization}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');

        Route::get('/rubrics', [RubricSectionController::class, 'index'])->name('rubrics.index');
        Route::post('/rubrics', [RubricSectionController::class, 'store'])->name('rubrics.store');

        Route::prefix('rubrics')->group(function () {
            Route::resource('leadership', RubricOptionController::class)
                ->except(['show'])
                ->names('rubrics.leadership');
        });

        Route::get('/submission-oversight', [AdminController::class, 'submissionOversight'])->name('submission-oversight');
        Route::get('/final-review', [AdminController::class, 'finalReview'])->name('final-review');
        Route::get('/award-report', [AdminController::class, 'awardReport'])->name('award-report');
        Route::get('/system-monitoring', [AdminController::class, 'systemMonitoring'])->name('system-monitoring');
    });

/*
|--------------------------------------------------------------------------
| ASSESSOR ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('assessor')
    ->middleware(['auth', 'role:assessor,admin'])
    ->name('assessor.')
    ->group(function () {

        // Assessor profile stuff
        Route::controller(AssessorController::class)->group(function () {
            Route::get('/profile', 'profile')->name('profile');
            Route::put('/profile', 'updateProfile')->name('profile.update');
            Route::patch('/password', 'updatePassword')->name('password.update');
            Route::post('/profile/picture', 'updateAvatar')->name('profile.picture');
        });

        // ⬇⬇ Assessor submissions
        Route::controller(AssessorSubmissionController::class)->group(function () {

            // Pending list page -> /assessor/submissions/pending-submissions
            // Route name: assessor.submissions.pending-submissions
            Route::get('/submissions/pending-submissions', 'pending')
                ->name('submissions.pending-submissions');

            // JSON details for modal
            // Route name: assessor.submissions.details
            Route::get('/submissions/{submission}/details', 'details')
                ->name('submissions.details');

            // Handle approve/reject/return/flag (handleAction method)
            // Route name: assessor.submissions.action
            Route::post('/submissions/{submission}/action', 'handleAction')
                ->name('submissions.action');

            // Download attachments via "submissionId:index"
            // Route name: assessor.documents.download
            // Inline view (for iframe/image preview)
            // Route name: assessor.documents.view
            Route::get('/documents/{documentId}/view', 'viewDocument')
                ->name('documents.view');

            // Download attachments via "submissionId:index"
            // Route name: assessor.documents.download
            Route::get('/documents/{documentId}/download', 'downloadDocument')
                ->name('documents.download');
        });

        // 2) Consolidated per student & category
        // Route name: assessor.submissions.submissions
        Route::get(
            '/submissions/compiled',
            [AssessorCompiledScoreController::class, 'index']
        )->name('submissions.submissions');

        // 3) Assessor final review (students list + send to final)
        // Route name: assessor.final-review
        Route::get(
            '/final-review',
            [AssessorFinalReviewController::class, 'index']
        )->name('final-review');

        Route::post(
            '/final-review/{student}',
            [AssessorFinalReviewController::class, 'storeForStudent']
        )->name('final-review.store');
    });
