<?php

namespace App\Http\Controllers;

use App\Models\RubricCategory;
use App\Models\RubricSection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RubricSectionController extends Controller
{
    public function index()
    {
        $categories = RubricCategory::with([
            'sections.subsections',      // Academic, Awards, Community, Conduct
            'sections.leadershipPositions' // Leadership
        ])->orderBy('order_no')->get();

        return view('admin.rubrics.index', compact('categories'));
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:rubric_categories,category_id'],
            'title'       => [
                'required',
                'string',
                'max:255',
                Rule::unique('rubric_sections')
                    ->where(fn($q) => $q->where('category_id', $request->category_id))
            ],
            'order_no'    => [
                'required',
                'integer',
                'between:1,255',
                Rule::unique('rubric_sections')
                    ->where(fn($q) => $q->where('category_id', $request->category_id))
            ],
            'evidence'    => ['nullable', 'string'],
            'notes'       => ['nullable', 'string'],
            'max_points'  => ['nullable', 'numeric', 'between:-99.99,999.99'],
        ]);

        RubricSection::create($data);

        return redirect()
            ->route('admin.rubrics.index')
            ->with('success', 'Section created successfully.');
    }

    public function edit(RubricSection $rubric_section)
    {
        $categories = RubricCategory::orderBy('order_no')->get();

        return view('rubrics.sections.edit', [
            'section' => $rubric_section->load(['category', 'subsections']),
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, RubricSection $rubric_section)
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:rubric_categories,category_id'],
            'title'       => [
                'required',
                'string',
                'max:255',
                Rule::unique('rubric_sections')
                    ->where(fn($q) => $q->where('category_id', $request->category_id))
                    ->ignore($rubric_section->section_id, 'section_id')
            ],
            'order_no'    => [
                'required',
                'integer',
                'between:1,255',
                Rule::unique('rubric_sections')
                    ->where(fn($q) => $q->where('category_id', $request->category_id))
                    ->ignore($rubric_section->section_id, 'section_id')
            ],
            'evidence'    => ['nullable', 'string'],
            'notes'       => ['nullable', 'string'],
            'max_points'  => ['nullable', 'numeric', 'between:-99.99,999.99'],
        ]);

        $rubric_section->update($data);

        return redirect()
            ->route('admin.rubrics.index')
            ->with('success', 'Section updated successfully.');
    }

    public function destroy(RubricSection $rubric_section)
    {
        $rubric_section->delete();

        return redirect()
            ->route('admin.rubrics.index')
            ->with('success', 'Section deleted successfully.');
    }
}
