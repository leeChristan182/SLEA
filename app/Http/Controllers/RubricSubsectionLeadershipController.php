<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RubricSection;
use App\Models\RubricSubsectionLeadership;

class RubricSubsectionLeadershipController extends Controller
{
    /**
     * Admin view showing leadership rubric table
     */
    public function adminView()
    {
        $sections = \App\Models\RubricSection::with([
            'subsections.leadershipPositions', // eager-load nested relationships
            'category'
        ])
            ->whereHas('category', fn($q) => $q->where('key', 'leadership'))
            ->orderBy('order_no')
            ->get();

        // ✅ passes $sections to the Blade view
        return view('admin.rubrics.sections.leadership', compact('sections'));
    }

    // ✅ Resource CRUD methods remain the same
    public function index()
    {
        $leadership = RubricSubsectionLeadership::with(['section.category'])
            ->orderBy('section_id')
            ->orderBy('position_order')
            ->paginate(12);

        return view('leadership-subsections.index', compact('leadership'));
    }

    public function create()
    {
        $sections = RubricSection::with('category')
            ->orderBy('category_id')
            ->orderBy('order_no')
            ->get()
            ->mapWithKeys(fn($s) => [$s->section_id => '[' . ($s->category->title ?? 'N/A') . '] ' . $s->title]);

        return view('leadership-subsections.create', compact('sections'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'section_id'     => ['required', 'integer', 'exists:rubric_sections,section_id'],
            'sub_section_id' => ['nullable', 'integer', 'exists:rubric_subsections,sub_items'],
            'position'       => ['required', 'string', 'max:255'],
            'points'         => ['required', 'numeric', 'between:-99.99,99.99'],
            'position_order' => ['required', 'integer', 'between:1,255'],
        ]);

        RubricSubsectionLeadership::create($data);
        return redirect()->route('admin.leadership')
            ->with('success', 'Leadership position created successfully.');
    }

    public function edit(RubricSubsectionLeadership $leadership_subsection)
    {
        $sections = RubricSection::with('category')
            ->orderBy('category_id')
            ->orderBy('order_no')
            ->get()
            ->mapWithKeys(fn($s) => [$s->section_id => '[' . ($s->category->title ?? 'N/A') . '] ' . $s->title]);

        return view('leadership-subsections.edit', compact('leadership_subsection', 'sections'));
    }

    public function update(Request $request, RubricSubsectionLeadership $leadership_subsection)
    {
        $data = $request->validate([
            'section_id'     => ['required', 'integer', 'exists:rubric_sections,section_id'],
            'sub_section_id' => ['nullable', 'integer', 'exists:rubric_subsections,sub_items'],
            'position'       => ['required', 'string', 'max:255'],
            'points'         => ['required', 'numeric', 'between:-99.99,99.99'],
            'position_order' => ['required', 'integer', 'between:1,255'],
        ]);

        $leadership_subsection->update($data);
        return redirect()->route('admin.leadership')
            ->with('success', 'Leadership position updated successfully.');
    }

    public function destroy(RubricSubsectionLeadership $leadership_subsection)
    {
        $leadership_subsection->delete();
        return redirect()->route('admin.leadership')
            ->with('success', 'Leadership position deleted successfully.');
    }
}
