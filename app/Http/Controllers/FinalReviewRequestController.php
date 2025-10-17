<?php

namespace App\Http\Controllers;

use App\Models\FinalReviewRequest;
use App\Models\Submission;
use Illuminate\Http\Request;

class FinalReviewRequestController extends Controller
{
    public function index()
    {
        $finalReviews = FinalReviewRequest::with('submission')->paginate(10);
        return view('final_reviews.index', compact('finalReviews'));
    }

    public function create()
    {
        $submissions = Submission::all();
        return view('final_reviews.create', compact('submissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'submission_id' => 'required|exists:submissions,id',
            'action' => 'nullable|string|max:20',
        ]);

        FinalReviewRequest::create($request->all());

        return redirect()->route('final-reviews.index')->with('success', 'Final Review Request created successfully.');
    }

    public function show(FinalReviewRequest $finalReview)
    {
        return view('final_reviews.show', compact('finalReview'));
    }

    public function edit(FinalReviewRequest $finalReview)
    {
        $submissions = Submission::all();
        return view('final_reviews.edit', compact('finalReview', 'submissions'));
    }

    public function update(Request $request, FinalReviewRequest $finalReview)
    {
        $request->validate([
            'submission_id' => 'required|exists:submissions,id',
            'action' => 'nullable|string|max:20',
        ]);

        $finalReview->update($request->all());

        return redirect()->route('final-reviews.index')->with('success', 'Final Review Request updated successfully.');
    }

    public function destroy(FinalReviewRequest $finalReview)
    {
        $finalReview->delete();
        return redirect()->route('final-reviews.index')->with('success', 'Final Review Request deleted successfully.');
    }
}
