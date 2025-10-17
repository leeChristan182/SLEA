<?php

namespace App\Http\Controllers;

use App\Models\RubricSection;
use App\Models\RubricSubsection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RubricSubsectionController extends Controller
{
    public function index()
    {
        $subs = RubricSubsection::with('section')
            ->orderBy('section_id')
            ->orderBy('order_no')
            ->paginate(10);

        return view('rubric_subsections.index', compact('subs'));
    }

    public function create()
    {
        $sections = RubricSection::orderBy('section_id')->pluck('title', 'section_id');
        return view('rubric_subsections.create', compact('sections'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'section_id'      => ['required', 'integer', 'exists:rubric_sections,section_id'],
            'sub_section'     => ['required', 'string', 'max:255'],
            'evidence_needed' => ['nullable', 'string', 'max:255'],
            'order_no'        => ['required', 'integer', 'between:1,255'],
        ]);

        RubricSubsection::create($validated);

        return redirect()->route('rubric-subsections.index')
            ->with('success', 'Rubric subsection created.');
    }

    public function show(RubricSubsection $rubric_subsection)
    {
        return view('rubric_subsections.show', ['sub' => $rubric_subsection->load('section')]);
    }

    public function edit(RubricSubsection $rubric_subsection)
    {
        $sections = RubricSection::orderBy('section_id')->pluck('title', 'section_id');
        return view('rubric_subsections.edit', [
            'sub' => $rubric_subsection,
            'sections' => $sections,
        ]);
    }

    public function update(Request $request, RubricSubsection $rubric_subsection)
    {
        $validated = $request->validate([
            'section_id'      => ['required', 'integer', 'exists:rubric_sections,section_id'],
            'sub_section'     => ['required', 'string', 'max:255'],
            'evidence_needed' => ['nullable', 'string', 'max:255'],
            'order_no'        => ['required', 'integer', 'between:1,255'],
        ]);

        $rubric_subsection->update($validated);

        return redirect()->route('rubric-subsections.index')
            ->with('success', 'Rubric subsection updated.');
    }

    public function destroy(RubricSubsection $rubric_subsection)
    {
        $rubric_subsection->delete();

        return redirect()->route('rubric-subsections.index')
            ->with('success', 'Rubric subsection deleted.');
    }
}
