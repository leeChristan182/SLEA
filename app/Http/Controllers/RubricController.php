<?php

namespace App\Http\Controllers;

use App\Models\RubricCategory;
use App\Models\RubricSection;
use App\Models\RubricSubsection;
use App\Models\RubricOption;
use Illuminate\Http\Request;

class RubricController extends Controller
{
    /**
     * Main Rubrics page for Admin.
     *
     * GET /admin/rubrics
     */
    public function index()
    {
        return view('admin.rubrics.index', [
            'categories'   => RubricCategory::orderBy('order')->get(),
            'sections'     => RubricSection::orderBy('order')->get(),
            'subsections'  => RubricSubsection::orderBy('order')->get(),
            'options'      => RubricOption::orderBy('order')->get(),
        ]);
    }

    /* ===========================================================
     |  CATEGORY CRUD
     * =========================================================== */

    public function categoryStore(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order'       => ['required', 'integer'],
        ]);

        RubricCategory::create($validated);

        return redirect()
            ->route('admin.rubrics.index')
            ->with('success', 'Category created successfully.');
    }

    public function categoryUpdate(Request $request, RubricCategory $category)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order'       => ['required', 'integer'],
        ]);

        $category->update($validated);

        return redirect()
            ->route('admin.rubrics.index')
            ->with('success', 'Category updated successfully.');
    }

    public function categoryDestroy(RubricCategory $category)
    {
        $category->delete();

        return redirect()
            ->route('admin.rubrics.index')
            ->with('success', 'Category deleted successfully.');
    }

    /* ===========================================================
     |  SECTION CRUD
     * =========================================================== */

    public function sectionStore(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:rubric_categories,id'],
            'name'        => ['required', 'string', 'max:255'],
            'order'       => ['required', 'integer'],
        ]);

        RubricSection::create($validated);

        return redirect()
            ->route('admin.rubrics.index')
            ->with('success', 'Section created successfully.');
    }

    public function sectionUpdate(Request $request, RubricSection $section)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:rubric_categories,id'],
            'name'        => ['required', 'string', 'max:255'],
            'order'       => ['required', 'integer'],
        ]);

        $section->update($validated);

        return redirect()
            ->route('admin.rubrics.index')
            ->with('success', 'Section updated successfully.');
    }

    public function sectionDestroy(RubricSection $section)
    {
        $section->delete();

        return redirect()
            ->route('admin.rubrics.index')
            ->with('success', 'Section deleted successfully.');
    }

    /* ===========================================================
     |  SUBSECTION CRUD
     * =========================================================== */

    public function subsectionStore(Request $request)
    {
        $validated = $request->validate([
            'section_id'     => ['required', 'exists:rubric_sections,id'],
            'name'           => ['required', 'string', 'max:255'],
            'max_points'     => ['required', 'integer'],
            'order'          => ['required', 'integer'],
        ]);

        RubricSubsection::create($validated);

        return redirect()
            ->route('admin.rubrics.index')
            ->with('success', 'Subsection created successfully.');
    }

    public function subsectionUpdate(Request $request, RubricSubsection $subsection)
    {
        $validated = $request->validate([
            'section_id'     => ['required', 'exists:rubric_sections,id'],
            'name'           => ['required', 'string', 'max:255'],
            'max_points'     => ['required', 'integer'],
            'order'          => ['required', 'integer'],
        ]);

        $subsection->update($validated);

        return redirect()
            ->route('admin.rubrics.index')
            ->with('success', 'Subsection updated successfully.');
    }

    public function subsectionDestroy(RubricSubsection $subsection)
    {
        $subsection->delete();

        return redirect()
            ->route('admin.rubrics.index')
            ->with('success', 'Subsection deleted successfully.');
    }

    /* ===========================================================
     |  OPTIONS CRUD (if applicable)
     * =========================================================== */

    public function optionStore(Request $request)
    {
        $validated = $request->validate([
            'subsection_id' => ['required', 'exists:rubric_subsections,id'],
            'label'         => ['required', 'string', 'max:255'],
            'points'        => ['required', 'integer'],
            'order'         => ['required', 'integer'],
        ]);

        RubricOption::create($validated);

        return redirect()
            ->route('admin.rubrics.index')
            ->with('success', 'Option added successfully.');
    }

    public function optionUpdate(Request $request, RubricOption $option)
    {
        $validated = $request->validate([
            'subsection_id' => ['required', 'exists:rubric_subsections,id'],
            'label'         => ['required', 'string', 'max:255'],
            'points'        => ['required', 'integer'],
            'order'         => ['required', 'integer'],
        ]);

        $option->update($validated);

        return redirect()
            ->route('admin.rubrics.index')
            ->with('success', 'Option updated successfully.');
    }

    public function optionDestroy(RubricOption $option)
    {
        $option->delete();

        return redirect()
            ->route('admin.rubrics.index')
            ->with('success', 'Option deleted successfully.');
    }
}
