<?php

namespace App\Http\Controllers;

use App\Models\RubricCategory;
use App\Models\RubricSection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RubricSectionController extends Controller
{
    public function index(Request $request)
    {
        $categoryId = $request->query('category_id');
        $sections = RubricSection::with('category')
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->orderBy('category_id')->orderBy('order_no')
            ->paginate(30);

        $categories = RubricCategory::orderBy('order_no')->get();
        return view('rubrics.sections.index', compact('sections','categories','categoryId'));
    }

    public function create()
    {
        $categories = RubricCategory::orderBy('order_no')->get();
        return view('rubrics.sections.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required','integer','exists:rubric_categories,category_id'],
            'title'       => ['required','string','max:255',
                              Rule::unique('rubric_sections')->where(fn($q)=>$q->where('category_id',$request->category_id))],
            'order_no'    => ['required','integer','between:1,255',
                              Rule::unique('rubric_sections')->where(fn($q)=>$q->where('category_id',$request->category_id))],
        ]);

        RubricSection::create($data);
        return redirect()->route('rubric-sections.index', ['category_id'=>$data['category_id']])
                         ->with('success', 'Section created.');
    }

    public function edit(RubricSection $rubric_section)
    {
        $categories = RubricCategory::orderBy('order_no')->get();
        return view('rubrics.sections.edit', ['section'=>$rubric_section, 'categories'=>$categories]);
    }

    public function update(Request $request, RubricSection $rubric_section)
    {
        $data = $request->validate([
            'category_id' => ['required','integer','exists:rubric_categories,category_id'],
            'title'       => ['required','string','max:255',
                              Rule::unique('rubric_sections')
                                  ->where(fn($q)=>$q->where('category_id',$request->category_id))
                                  ->ignore($rubric_section->section_id, 'section_id')],
            'order_no'    => ['required','integer','between:1,255',
                              Rule::unique('rubric_sections')
                                  ->where(fn($q)=>$q->where('category_id',$request->category_id))
                                  ->ignore($rubric_section->section_id, 'section_id')],
        ]);

        $rubric_section->update($data);
        return redirect()->route('rubric-sections.index', ['category_id'=>$data['category_id']])
                         ->with('success', 'Section updated.');
    }

    public function destroy(RubricSection $rubric_section)
    {
        $cid = $rubric_section->category_id;
        $rubric_section->delete();
        return redirect()->route('rubric-sections.index', ['category_id'=>$cid])->with('success', 'Section deleted.');
    }
}
