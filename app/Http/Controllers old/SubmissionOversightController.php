<?php

namespace App\Http\Controllers;

use App\Models\SubmissionOversight;
use Illuminate\Http\Request;

class SubmissionOversightController extends Controller
{
    public function index()
    {
        $oversights = SubmissionOversight::with(['pendingSubmission', 'admin'])->get();
        return view('submission_oversights.index', compact('oversights'));
    }

    public function create()
    {
        return view('submission_oversights.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'pending_sub_id' => 'required|integer',
            'admin_id' => 'required|string|max:15',
            'submission_status' => 'required|string|max:20',
            'flag' => 'nullable|string|max:20',
            'action' => 'nullable|string|max:20',
        ]);

        SubmissionOversight::create($request->all());

        return redirect()->route('submission_oversights.index')->with('success', 'Submission Oversight created successfully.');
    }

    public function show($id)
    {
        $oversight = SubmissionOversight::findOrFail($id);
        return view('submission_oversights.show', compact('oversight'));
    }

    public function edit($id)
    {
        $oversight = SubmissionOversight::findOrFail($id);
        return view('submission_oversights.edit', compact('oversight'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pending_sub_id' => 'required|integer',
            'admin_id' => 'required|string|max:15',
            'submission_status' => 'required|string|max:20',
            'flag' => 'nullable|string|max:20',
            'action' => 'nullable|string|max:20',
        ]);

        $oversight = SubmissionOversight::findOrFail($id);
        $oversight->update($request->all());

        return redirect()->route('submission_oversights.index')->with('success', 'Submission Oversight updated successfully.');
    }

    public function destroy($id)
    {
        $oversight = SubmissionOversight::findOrFail($id);
        $oversight->delete();

        return redirect()->route('submission_oversights.index')->with('success', 'Submission Oversight deleted successfully.');
    }
}
