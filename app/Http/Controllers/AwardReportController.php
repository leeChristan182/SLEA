<?php
namespace App\Http\Controllers;

use App\Models\AwardReport;
use App\Models\FinalReview;
use Illuminate\Http\Request;

class AwardReportController extends Controller
{
    public function index()
    {
        $awardReports = AwardReport::with('finalReview')->get();
        return view('award_reports.index', compact('awardReports'));
    }

    public function create()
    {
        $finalReviews = FinalReview::all();
        return view('award_reports.create', compact('finalReviews'));
    }

    public function store(Request $request)
    {
        AwardReport::create($request->all());
        return redirect()->route('award_reports.index');
    }

    public function show(AwardReport $awardReport)
    {
        return view('award_reports.show', compact('awardReport'));
    }

    public function edit(AwardReport $awardReport)
    {
        $finalReviews = FinalReview::all();
        return view('award_reports.edit', compact('awardReport','finalReviews'));
    }

    public function update(Request $request, AwardReport $awardReport)
    {
        $awardReport->update($request->all());
        return redirect()->route('award_reports.index');
    }

    public function destroy(AwardReport $awardReport)
    {
        $awardReport->delete();
        return redirect()->route('award_reports.index');
    }
}
