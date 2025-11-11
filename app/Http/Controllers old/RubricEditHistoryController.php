<?php

namespace App\Http\Controllers;

use App\Models\RubricSubsectionLeadership;
use App\Models\RubricEditHistory;
use Illuminate\Http\Request;

class RubricEditHistoryController extends Controller
{
    public function index(RubricSubsectionLeadership $subsection)
    {
        $edits = $subsection->edits()->latest('edit_timestamp')->paginate(20);
        return view('edits.index', compact('subsection','edits'));
    }

    public function create(RubricSubsectionLeadership $subsection)
    {
        return view('edits.create', compact('subsection'));
    }

    public function store(Request $request, RubricSubsectionLeadership $subsection)
    {
        $data = $request->validate([
            'admin_id'      => ['required','string','max:15'],
            'edit_timestamp'=> ['required','date'],
            'changes_made'  => ['nullable','string','max:255'],
            'field_edited'  => ['nullable','string','max:255'],
        ]);
        $data['sub_items'] = $subsection->sub_items;

        $edit = RubricEditHistory::create($data);

        return redirect()
            ->route('edits.index', $subsection)
            ->with('success','Edit logged.');
    }

    public function show(RubricSubsectionLeadership $subsection, RubricEditHistory $editHistory)
    {
        // ensure the edit belongs to this subsection
        abort_unless($editHistory->sub_items === $subsection->sub_items, 404);
        return view('edits.show', ['subsection'=>$subsection, 'edit'=>$editHistory]);
    }

    public function edit(RubricSubsectionLeadership $subsection, RubricEditHistory $editHistory)
    {
        abort_unless($editHistory->sub_items === $subsection->sub_items, 404);
        return view('edits.edit', ['subsection'=>$subsection, 'edit'=>$editHistory]);
    }

    public function update(Request $request, RubricSubsectionLeadership $subsection, RubricEditHistory $editHistory)
    {
        abort_unless($editHistory->sub_items === $subsection->sub_items, 404);

        $data = $request->validate([
            'admin_id'      => ['required','string','max:15'],
            'edit_timestamp'=> ['required','date'],
            'changes_made'  => ['nullable','string','max:255'],
            'field_edited'  => ['nullable','string','max:255'],
        ]);

        $editHistory->update($data);

        return redirect()
            ->route('edits.index', $subsection)
            ->with('success','Edit updated.');
    }

    public function destroy(RubricSubsectionLeadership $subsection, RubricEditHistory $editHistory)
    {
        abort_unless($editHistory->sub_items === $subsection->sub_items, 404);
        $editHistory->delete();

        return redirect()
            ->route('edits.index', $subsection)
            ->with('success','Edit deleted.');
    }
}
