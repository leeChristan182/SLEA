<?php

namespace App\Http\Controllers;

use App\Models\RubricCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RubricCategoryController extends Controller
{
    public function index()
    {
        $categories = RubricCategory::orderBy('order_no')->paginate(20);
        return view('rubrics.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('rubrics.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => ['required','string','max:50','unique:rubric_categories,title'],
            'max_points'  => ['required','numeric','between:0,999.99'],
            'order_no'    => ['required','integer','between:1,255','unique:rubric_categories,order_no'],
        ]);

        RubricCategory::create($data);
        return redirect()->route('rubric-categories.index')->with('success', 'Category created.');
    }

    public function show(RubricCategory $rubric_category)
    {
        return view('rubrics.categories.show', ['category' => $rubric_category]);
    }

    public function edit(RubricCategory $rubric_category)
    {
        return view('rubrics.categories.edit', ['category' => $rubric_category]);
    }

    public function update(Request $request, RubricCategory $rubric_category)
    {
        $data = $request->validate([
            'title'       => ['required','string','max:50', Rule::unique('rubric_categories','title')->ignore($rubric_category->category_id, 'category_id')],
            'max_points'  => ['required','numeric','between:0,999.99'],
            'order_no'    => ['required','integer','between:1,255', Rule::unique('rubric_categories','order_no')->ignore($rubric_category->category_id, 'category_id')],
        ]);

        $rubric_category->update($data);
        return redirect()->route('rubric-categories.index')->with('success', 'Category updated.');
    }

    public function destroy(RubricCategory $rubric_category)
    {
        $rubric_category->delete(); // cascades to sections
        return redirect()->route('rubric-categories.index')->with('success', 'Category deleted.');
    }
}
