<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AssessorFinalReview;
use App\Models\FinalReview;
use App\Models\RubricCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\AdminFinalReviewDecisionMail;

class FinalReviewController extends Controller
{
    /**
     * List students queued for Admin final review (plus already decided ones).
     */
    public function index()
    {
        // Ensure enum values exist
        $requiredStatuses = ['queued_for_admin', 'finalized'];
        foreach ($requiredStatuses as $status) {
            if (!DB::table('final_review_statuses')->where('key', $status)->exists()) {
                try {
                    DB::table('final_review_statuses')->insert(['key' => $status]);
                } catch (\Exception $e) {
                    // Ignore if already exists or other error
                }
            }
        }

        // Get all categories in order to ensure all 5 are shown
        $allCategories = RubricCategory::orderBy('order_no')->get()->keyBy('id');

        // Query for items with proper status
        $items = AssessorFinalReview::query()
            ->with([
                'student.studentAcademic.program.college',
                'student.studentAcademic.major',
                'compiledScores.category',
                'finalReview',
            ])
            ->whereIn('status', ['queued_for_admin', 'finalized'])
            ->orderByDesc('reviewed_at')
            ->get()
            ->map(function ($item) use ($allCategories) {
                // Ensure all categories are included in compiledScores, even if score is 0
                $compiledScores   = $item->compiledScores ?? collect();
                $scoresByCategory = $compiledScores->keyBy('rubric_category_id');

                // Add missing categories with 0 scores
                $allScores = collect();
                foreach ($allCategories as $categoryId => $category) {
                    if ($scoresByCategory->has($categoryId)) {
                        $allScores->push($scoresByCategory->get($categoryId));
                    } else {
                        // Create a placeholder compiled score with 0 values
                        $placeholder                      = new \App\Models\AssessorCompiledScore();
                        $placeholder->rubric_category_id  = $categoryId;
                        $placeholder->total_score         = 0;
                        $placeholder->max_points          = $category->max_points ?? 0;
                        $placeholder->min_required_points = $category->min_required_points ?? 0;
                        $placeholder->setRelation('category', $category);
                        $allScores->push($placeholder);
                    }
                }

                // Sort by category order_no
                $item->setRelation(
                    'compiledScores',
                    $allScores
                        ->sortBy(function ($cs) use ($allCategories) {
                            $catId = $cs->rubric_category_id;
                            return $allCategories->get($catId)->order_no ?? 999;
                        })
                        ->values()
                );

                return $item;
            });

        // Debug: Log details for troubleshooting
        \Log::info('Admin final review query:', [
            'total_items'      => $items->count(),
            'queued_count'     => $items->where('status', 'queued_for_admin')->count(),
            'finalized_count'  => $items->where('status', 'finalized')->count(),
            'sample_statuses'  => $items->pluck('status')->unique()->toArray(),
        ]);

        // Filter out items without valid student relationship
        $items = $items->filter(function ($item) {
            return $item->student !== null;
        });

        // Paginate the collection (5 items per page)
        $currentPage = request()->get('page', 1);
        $perPage     = 5;
        $total       = $items->count();
        $items       = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            [
                'path'  => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('admin.final-review', ['items' => $paginator]);
    }

    /**
     * Store the admin decision (approved / not_qualified) for a student.
     */
    public function storeDecision(Request $request, AssessorFinalReview $assessorFinalReview)
    {
        // Lock decisions once already made (admin cannot change it afterwards)
        $assessorFinalReview->loadMissing('finalReview');
        if (
            $assessorFinalReview->finalReview &&
            !empty($assessorFinalReview->finalReview->decision)
        ) {
            return back()->with('error', 'This final review already has a decision and can no longer be changed.');
        }
        if (in_array($assessorFinalReview->status, ['finalized'], true)) {
            return back()->with('error', 'This final review is already finalized and can no longer be changed.');
        }

        $data = $request->validate([
            'decision' => ['required', 'in:approved,not_qualified'],
        ]);

        $admin = Auth::user();

        // Ensure enum values exist
        $requiredDecisions = ['approved', 'not_qualified'];
        foreach ($requiredDecisions as $decision) {
            if (!DB::table('final_review_decisions')->where('key', $decision)->exists()) {
                try {
                    DB::table('final_review_decisions')->insert(['key' => $decision]);
                } catch (\Exception $e) {
                    \Log::warning("Failed to create decision '{$decision}': " . $e->getMessage());
                }
            }
        }

        // Verify the decision value exists before using it
        $decisionExists = DB::table('final_review_decisions')->where('key', $data['decision'])->exists();
        if (!$decisionExists) {
            \Log::error("Decision '{$data['decision']}' does not exist in final_review_decisions table");
            return back()->with('error', 'Invalid decision value. Please contact system administrator.');
        }

        // We want to use these AFTER the transaction (for mailing)
        $finalReview = null;
        $student     = null;

        try {
            DB::transaction(function () use ($assessorFinalReview, $admin, $data, &$finalReview, &$student) {
                // Verify admin exists
                if (!$admin || !$admin->exists) {
                    throw new \Exception('Admin user not found');
                }

                // Verify assessor final review exists
                if (!$assessorFinalReview || !$assessorFinalReview->exists) {
                    throw new \Exception('Assessor final review not found');
                }

                // Upsert final_reviews row - use updateOrCreate to handle existing records
                $finalReview = FinalReview::updateOrCreate(
                    [
                        'assessor_final_review_id' => $assessorFinalReview->id,
                        'admin_id'                 => $admin->id,
                    ],
                    [
                        'decision'    => $data['decision'],
                        'reviewed_at' => now(),
                    ]
                );

                // Update assessor_final_reviews
                $assessorFinalReview->status        = 'finalized';
                $assessorFinalReview->qualification = $data['decision'] === 'approved'
                    ? 'qualified'
                    : 'unqualified';
                $assessorFinalReview->save();

                // Update student_academic.slea_application_status
                $student = $assessorFinalReview->student;
                if ($student) {
                    // Reload student to get fresh relationship
                    $student->load('studentAcademic');

                    // Get or create student academic record
                    $studentAcademic = $student->studentAcademic;

                    if (!$studentAcademic) {
                        // If no record yet, set directly to final status based on decision
                        $status = ($data['decision'] === 'approved')
                            ? 'qualified'
                            : 'not_qualified';

                        $studentAcademic = \App\Models\StudentAcademic::create([
                            'user_id'                 => $student->id,
                            'slea_application_status' => $status,
                        ]);
                    } else {
                        // We are at ADMIN FINAL REVIEW stage.

                        // Admin decision is the source of truth at this stage
                        if ($data['decision'] === 'approved') {
                            $studentAcademic->slea_application_status = 'qualified';
                        } else {
                            // Admin marks NOT QUALIFIED
                            $studentAcademic->slea_application_status = 'not_qualified';
                        }

                        $studentAcademic->save();

                        // Clear any cached relationship
                        $student->unsetRelation('studentAcademic');
                    }

                    \Log::info('Updated student academic status', [
                        'student_id' => $student->id,
                        'status'     => $studentAcademic->slea_application_status,
                        'decision'   => $data['decision'],
                    ]);
                }
            });
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error("Database error in storeDecision: " . $e->getMessage(), [
                'decision'                 => $data['decision'],
                'assessor_final_review_id' => $assessorFinalReview->id,
                'admin_id'                 => $admin->id ?? null,
            ]);
            return back()->with('error', 'Failed to save decision. Please try again or contact system administrator.');
        } catch (\Exception $e) {
            \Log::error("Error in storeDecision: " . $e->getMessage());
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }

        /**
         * 📧 Send Admin Final Review decision email
         * We send this AFTER the transaction so a mail failure doesn't rollback DB changes.
         */
        if ($student && $finalReview && filter_var($student->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($student->email)->send(
                    new AdminFinalReviewDecisionMail($student, $finalReview, $assessorFinalReview)
                );
            } catch (\Throwable $e) {
                Log::warning('Failed to send AdminFinalReviewDecisionMail', [
                    'assessor_final_review_id' => $assessorFinalReview->id ?? null,
                    'student_id'               => $student->id ?? null,
                    'error'                    => $e->getMessage(),
                ]);
            }
        }

        $msg = $data['decision'] === 'approved'
            ? 'Student marked as QUALIFIED for SLEA.'
            : 'Student marked as NOT QUALIFIED for SLEA.';

        return redirect()
            ->route('admin.final-review')
            ->with('status', $msg);
    }

    public function decide(Request $request, AssessorFinalReview $assessorFinalReview)
    {
        return $this->storeDecision($request, $assessorFinalReview);
    }
}
