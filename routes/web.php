<?php

use Illuminate\Support\Facades\Auth; // ← fix casing
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssessorController;
use App\Http\Controllers\RubricController;
use App\Http\Controllers\SubmissionRecordController;
use App\Http\Controllers\ApprovalAccountController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\RubricSectionController;
use App\Http\Controllers\RubricSubsectionLeadershipController;

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

// Only allow guests to view login/register (redirect logged-in users by role)
Route::group([
    'middleware' => function ($request, $next) {
        if (Auth::check()) {
            return match (Auth::user()->role) {
                'admin'    => redirect()->route('admin.profile'),
                'assessor' => redirect()->route('assessor.profile'),
                default    => redirect()->route('student.profile'),
            };
        }
        return $next($request);
    }
], function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login.show');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.auth');

    // Registration (students only)
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register.show');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    // OTP (optional)
    Route::get('/otp', [AuthController::class, 'showOtp'])->name('otp.show');
    Route::post('/otp', [AuthController::class, 'verifyOtp'])->name('otp.verify');
    Route::post('/otp/resend', [AuthController::class, 'resendOtp'])->name('otp.resend');
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');





/*
|--------------------------------------------------------------------------
| STUDENT ROUTES (Protected)
|--------------------------------------------------------------------------
*/
// routes/web.php (student section only)
Route::prefix('student')
    ->middleware(['auth', 'role:student'])
    ->name('student.')
    ->controller(StudentController::class)
    ->group(function () {
        // Revalidation page is always reachable for students
        Route::get('/revalidation', 'revalidation')->name('revalidation');

        // The 3 interactable actions (allowed even when locked)
        Route::post('/update-academic', 'updateAcademicInfo')->name('updateAcademic');
        Route::post('/upload-cor', 'uploadCOR')->name('uploadCOR');
        Route::post('/update-leadership', 'updateLeadership')->name('updateLeadership');

        // Everything else requires being eligible
        Route::middleware('eligible')->group(function () {
            Route::get('/profile', 'profile')->name('profile');
            Route::get('/performance', 'performance')->name('performance');
            Route::get('/criteria', 'criteria')->name('criteria');
            Route::get('/history', 'history')->name('history');

            // submissions (example)
            Route::get('/submissions', [SubmissionRecordController::class, 'index'])->name('submissions.index');
            Route::get('/submissions/create', [SubmissionRecordController::class, 'create'])->name('submissions.create');
            Route::post('/submissions', [SubmissionRecordController::class, 'store'])->name('submissions.store');
            Route::get('/submissions/download/{id}', [SubmissionRecordController::class, 'download'])->name('submissions.download');
            Route::get('/submit', [SubmissionRecordController::class, 'create'])->name('submit');
        });
    });

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
// ADMIN ROUTES
Route::prefix('admin')
    ->middleware(['auth', 'role:admin'])
    ->name('admin.') // <<< add this so routes become admin.*
    ->group(function () {

        // PROFILE & DASHBOARD
        Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
        Route::put('/profile/update', [AdminController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/avatar', [AdminController::class, 'updateAvatar'])->name('profile.avatar');
        Route::put('/profile/password', [AdminController::class, 'updatePassword'])->name('profile.password.update');

        // USER ACCOUNT MANAGEMENT
        // Admin creation (limited slots)
        Route::get('/create_user', [AdminController::class, 'createUser'])->name('create_user');
        Route::post('/create_user', [AdminController::class, 'storeUser'])->name('store_user');
        Route::get('/approve-reject', [AdminController::class, 'approveReject'])->name('approve-reject');
        Route::post('/approve/{student_id}', [AdminController::class, 'approveUser'])->name('approve');
        Route::post('/reject/{student_id}', [AdminController::class, 'rejectUser'])->name('reject');
        Route::get('/manage', [AdminController::class, 'manageAccount'])->name('manage');
        Route::patch('/manage/{user}/toggle', [AdminController::class, 'toggleUser'])->name('manage.toggle');
        Route::delete('/manage/{user}', [AdminController::class, 'destroyUser'])->name('manage.destroy');

        // Revalidation
        Route::get('/revalidation', [AdminController::class, 'revalidationQueue'])->name('revalidation');
        Route::post('/revalidation/{user}/approve', [AdminController::class, 'approveRevalidation'])->name('revalidation.approve');
        Route::post('/revalidation/{user}/reject',  [AdminController::class, 'rejectRevalidation'])->name('revalidation.reject');

        // ORGANIZATION MANAGEMENT
        Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
        Route::post('/organizations', [OrganizationController::class, 'store'])->name('organizations.store');
        Route::put('/organizations/{organization}', [OrganizationController::class, 'update'])->name('organizations.update');
        Route::delete('/organizations/{organization}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');

        // Rubrics (sections)
        Route::get('/rubrics', [RubricSectionController::class, 'index'])->name('rubrics.index');
        Route::post('/rubrics', [RubricSectionController::class, 'store'])->name('rubrics.store');

        // Leadership CRUD under /admin/rubrics/leadership
        Route::prefix('rubrics')->group(function () {
            Route::resource('leadership', RubricSubsectionLeadershipController::class)
                ->except(['show'])
                ->names('rubrics.leadership'); // results in admin.rubrics.leadership.*
        });

        // SYSTEM OPERATIONS
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
    ->middleware(['auth', 'role:assessor,admin']) // allow admins to access assessor screens too
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
