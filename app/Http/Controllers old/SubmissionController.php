<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\PendingSubmission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubmissionController extends Controller
{
    // list finalized submissions
    public function index(Request $request)
    {
        $items = Submission::with('pending.submission') // pending -> submission_record
            ->latest()
            ->paginate(15);

        return view('assessed.index', compact('items'));
    }

    // finalize (create a row in submissions from a pending one)
    public function store(Request $request)
    {
        $data = $request->validate([
            'pending_sub_id' => [
                'required',
                'integer',
                'exists:pending_submissions,pending_sub_id',
                Rule::unique('submissions', 'pending_sub_id'), // prevent duplicates
            ],
            'assessor_id' => ['nullable','string','max:15'],
            'action'      => ['required','string','max:20','in:Approved,Rejected'],
        ]);

        $pending = PendingSubmission::findOrFail($data['pending_sub_id']);

        // Create the final submission
        $sub = Submission::create([
            'pending_sub_id' => $pending->pending_sub_id,
            'assessor_id'    => $data['assessor_id'] ?? $pending->assessor_id,
            'action'         => $data['action'],
        ]);

        // Reflect status back on the pending row (and stamp date if not set)
        $pending->update([
            'action'        => $data['action'],
            'assessor_id'   => $data['assessor_id'] ?? $pending->assessor_id,
            'assessed_date' => $pending->assessed_date ?? now(),
        ]);
      
if (in_array($pending->action, ['Approved','Rejected'])) {
    Submission::firstOrCreate(
        ['pending_sub_id' => $pending->pending_sub_id],
        [
            'assessor_id' => $pending->assessor_id,
            'action'      => $pending->action,
        ]
    );
}


        return redirect()->route('assessed.index')->with('success', "Finalized submission #{$sub->submission_id}.");
    }
}
