<?php

namespace App\Http\Controllers;

use App\Models\RubricSubsectionLeadership;
use App\Models\RubricSubsection;
use Illuminate\Http\Request;

class RubricSubsectionLeadershipController extends Controller
{
    public function index()
    {
        $leadership = RubricSubsectionLeadership::with(['subsection.section.category'])
            ->orderBy('position_order')
            ->paginate(12);

        return view('leadership-subsections.index', compact('leadership'));
    }

    public function create()
    {
        $subsections = RubricSubsection::with(['section.category'])
            ->orderBy('section_id')
            ->orderBy('order_no')
            ->get()
            ->mapWithKeys(function ($subsection) {
                $label = sprintf(
                    '[%s] %s - %s',
                    $subsection->section->category->title ?? 'N/A',
                    $subsection->section->title ?? 'N/A',
                    $subsection->sub_section
                );
                return [$subsection->sub_items => $label];
            });
        
        return view('leadership-subsections.create', compact('subsections'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sub_items'      => ['required', 'integer', 'exists:rubric_subsections,sub_items'],
            'position'       => ['required', 'string', 'max:255'],
            'points'         => ['required', 'numeric', 'between:-99.99,99.99'],
            'position_order' => ['required', 'integer', 'between:1,255'],
        ]);

        $leadership = RubricSubsectionLeadership::create($data);

        return redirect()
            ->route('leadership-subsections.show', $leadership)
            ->with('success', 'Leadership position created.');
    }

    public function show(RubricSubsectionLeadership $leadership_subsection)
    {
        $leadership_subsection->load(['subsection.section.category', 'edits']);
        return view('leadership-subsections.show', ['leadership' => $leadership_subsection]);
    }

    public function edit(RubricSubsectionLeadership $leadership_subsection)
    {
        $subsections = RubricSubsection::with(['section.category'])
            ->orderBy('section_id')
            ->orderBy('order_no')
            ->get()
            ->mapWithKeys(function ($subsection) {
                $label = sprintf(
                    '[%s] %s - %s',
                    $subsection->section->category->title ?? 'N/A',
                    $subsection->section->title ?? 'N/A',
                    $subsection->sub_section
                );
                return [$subsection->sub_items => $label];
            });
        
        return view('leadership-subsections.edit', [
            'leadership' => $leadership_subsection, 
            'subsections' => $subsections
        ]);
    }

    public function update(Request $request, RubricSubsectionLeadership $leadership_subsection)
    {
        $data = $request->validate([
            'sub_items'      => ['required', 'integer', 'exists:rubric_subsections,sub_items'],
            'position'       => ['required', 'string', 'max:255'],
            'points'         => ['required', 'numeric', 'between:-99.99,99.99'],
            'position_order' => ['required', 'integer', 'between:1,255'],
        ]);

        $leadership_subsection->update($data);

        return redirect()
            ->route('leadership-subsections.show', $leadership_subsection)
            ->with('success', 'Leadership position updated.');
    }

    public function destroy(RubricSubsectionLeadership $leadership_subsection)
    {
        $leadership_subsection->delete();

        return redirect()
            ->route('leadership-subsections.index')
            ->with('success', 'Leadership position deleted.');
    }
}
