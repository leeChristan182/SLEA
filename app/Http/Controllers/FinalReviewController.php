<?php
namespace App\Http\Controllers;

use App\Models\FinalReview;
use Illuminate\Http\Request;

class FinalReviewController extends Controller
{
    public function index()
    {
        $finalReviews = FinalReview::all();
        return view('final_reviews.index', compact('finalReviews'));
    }

    public function create()
    {
        return view('final_reviews.create');
    }

    public function store(Request $request)
    {
        FinalReview::create($request->all());
        return redirect()->route('final_reviews.index');
    }

    public function show(FinalReview $finalReview)
    {
        return view('final_reviews.show', compact('finalReview'));
    }

    public function edit(FinalReview $finalReview)
    {
        return view('final_reviews.edit', compact('finalReview'));
    }

    public function update(Request $request, FinalReview $finalReview)
    {
        $finalReview->update($request->all());
        return redirect()->route('final_reviews.index');
    }

    public function destroy(FinalReview $finalReview)
    {
        $finalReview->delete();
        return redirect()->route('final_reviews.index');
    }
}
