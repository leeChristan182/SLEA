<?php

namespace App\Http\Controllers;

use App\Models\PendingSubmission;
use App\Models\SubmissionRecord;
use Illuminate\Http\Request;

class PendingSubmissionController extends Controller
{
    // List queue (filter by status or assessor or student)
    public function index(Request $request)
    {
        $action     = $request->query('action');      // Queued/Approved/Rejected
        $assessor   = $request->query('assessor_id');
        $student_id = $request->query('student_id');

        $rows = PendingSubmission::with('submission')
            ->when($action, fn($q) => $q->where('action', $action))
            ->when($assessor, fn($q) => $q->where('assessor_id', $assessor))
            ->when($student_id, fn($q) => $q->whereHas('submission', fn($qq) => $qq->where('student_id', $student_id)))
            ->latest('pending_queued_date')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('pending.index', compact('rows','action','assessor','student_id'));
    }

    // Queue a new pending item (usually from a created submission)
    public function create(Request $request)
    {
        $subrec_id = $request->query('subrec_id');
        $sub = $subrec_id ? SubmissionRecord::find($subrec_id) : null;

        return view('pending.create', compact('subrec_id','sub'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subrec_id'   => ['required','integer','exists:submission_records,subrec_id'],
            'assessor_id' => ['nullable','string','max:15'],
            'remarks'     => ['nullable','string','max:255'],
        ]);

        $row = PendingSubmission::create([
            'subrec_id'            => $data['subrec_id'],
            'assessor_id'          => $data['assessor_id'] ?? null,
            'action'               => 'Queued',
            'remarks'              => $data['remarks'] ?? null,
            'pending_queued_date'  => now(),
        ]);

        return redirect()->route('pending.index')->with('success', "Queued #{$row->pending_sub_id}.");
    }

    // Assess (approve/reject + optional score/remarks)
    public function edit(PendingSubmission $pending)
    {
        // Route model binding by pending_sub_id works automatically
        $pending->load('submission');
        return view('pending.edit', compact('pending'));
    }

    public function update(Request $request, PendingSubmission $pending)
    {
        $validated = $request->validate([
            'action'       => ['required','string','max:20','in:Queued,Approved,Rejected'],
            'assessor_id'  => ['nullable','string','max:15'],
            'score_points' => ['nullable','numeric','between:0,99.99'],
            'remarks'      => ['nullable','string','max:255'],
        ]);

        // if action is Approved/Rejected, stamp assessed_date
        $assessedDate = in_array($validated['action'], ['Approved','Rejected']) ? now() : null;

        $pending->update([
            'action'        => $validated['action'],
            'assessor_id'   => $validated['assessor_id'] ?? $pending->assessor_id,
            'score_points'  => $validated['score_points'] ?? null,
            'remarks'       => $validated['remarks'] ?? null,
            'assessed_date' => $assessedDate,
        ]);

        return redirect()->route('pending.index')->with('success', "Updated queue #{$pending->pending_sub_id}.");
    }
}
