<?php

namespace App\Http\Controllers;

use App\Models\RubricOption;
use App\Models\RubricSubsection;
use Illuminate\Http\Request;

class RubricOptionController extends Controller
{
    // List all options (optional: you could filter by subsection)
    public function index()
    {
        $options = RubricOption::with('subsection.section')
            ->orderBy('sub_section_id')
            ->orderBy('order_no')
            ->paginate(20);

        return view('admin.rubrics.options.index', compact('options'));
    }

    // Show form to create a new option
    public function create()
    {
        $subsections = RubricSubsection::with('section')
            ->orderBy('section_id')
            ->orderBy('order_no')
            ->get();

        return view('admin.rubrics.options.create', compact('subsections'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sub_section_id' => ['required', 'integer', 'exists:rubric_subsections,sub_section_id'],
            'code'           => ['nullable', 'string', 'max:100'],
            'label'          => ['required', 'string', 'max:255'],
            'points'         => ['required', 'numeric', 'between:-99.99,999.99'],
            'order_no'       => ['required', 'integer', 'between:1,255'],
        ]);

        RubricOption::create($data);

        return redirect()
            ->route('admin.rubrics.options.index')
            ->with('success', 'Rubric option created.');
    }

    public function edit(RubricOption $option)
    {
        $subsections = RubricSubsection::with('section')
            ->orderBy('section_id')
            ->orderBy('order_no')
            ->get();

        return view('admin.rubrics.options.edit', [
            'option'      => $option->load('subsection.section'),
            'subsections' => $subsections,
        ]);
    }

    public function update(Request $request, RubricOption $option)
    {
        $data = $request->validate([
            'sub_section_id' => ['required', 'integer', 'exists:rubric_subsections,sub_section_id'],
            'code'           => ['nullable', 'string', 'max:100'],
            'label'          => ['required', 'string', 'max:255'],
            'points'         => ['required', 'numeric', 'between:-99.99,999.99'],
            'order_no'       => ['required', 'integer', 'between:1,255'],
        ]);

        $option->update($data);

        return redirect()
            ->route('admin.rubrics.options.index')
            ->with('success', 'Rubric option updated.');
    }

    public function destroy(RubricOption $option)
    {
        $option->delete();

        return redirect()
            ->route('admin.rubrics.options.index')
            ->with('success', 'Rubric option deleted.');
    }
}
