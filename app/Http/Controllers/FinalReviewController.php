<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AssessorFinalReview;
use App\Models\FinalReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinalReviewController extends Controller
{
    /**
     * List students queued for Admin final review (plus already decided ones).
     */
    public function index()
    {
        $items = AssessorFinalReview::query()
            ->with([
                'student.studentAcademic.program.college',
                'compiledScores.category',
                'finalReview',
            ])
            ->whereIn('status', ['queued_for_admin', 'finalized'])
            ->orderByDesc('reviewed_at')
            ->get();

        return view('admin.final-review', compact('items'));
    }

    /**
     * Store the admin decision (approved / not_qualified) for a student.
     */
    public function storeDecision(Request $request, AssessorFinalReview $assessorFinalReview)
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approved,not_qualified'],
        ]);

        $admin = Auth::user();

        DB::transaction(function () use ($assessorFinalReview, $admin, $data) {
            // Upsert final_reviews row
            $final = $assessorFinalReview->finalReview()
                ->firstOrNew([]);

            $final->fill([
                'admin_id'    => $admin?->id,
                'decision'    => $data['decision'],
                'reviewed_at' => now(),
            ]);

            $final->save();

            // Update assessor_final_reviews
            $assessorFinalReview->status = 'finalized';
            $assessorFinalReview->qualification = $data['decision'] === 'approved'
                ? 'qualified'
                : 'unqualified';
            $assessorFinalReview->save();

            // Update student_academic.slea_application_status
            $student = $assessorFinalReview->student;
            if ($student && $student->studentAcademic) {
                $studentAcademic = $student->studentAcademic;

                $studentAcademic->slea_application_status = $data['decision'] === 'approved'
                    ? 'awarded'
                    : 'not_qualified';

                $studentAcademic->save();
            }
        });

        $msg = $data['decision'] === 'approved'
            ? 'Student marked as AWARDED for SLEA.'
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
