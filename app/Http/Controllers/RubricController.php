<?php

namespace App\Http\Controllers;

use App\Models\RubricCategory;
use App\Models\RubricSection;
use App\Models\RubricSubsection;
use App\Models\RubricOption; // or RubricSubsectionLeadership if that’s the one
use App\Models\RubricEditHistory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RubricController extends Controller
{
    /* -----------------------------
     *  STUDENT VIEW (READ ONLY)
     * ----------------------------- */

    // /student/criteria
    public function index()
    {
        $categories = \App\Models\RubricCategory::with([
            'sections.subsections.options'
        ])
            ->orderBy('order_no')
            ->get();

        return view('admin.rubrics.index', compact('categories'));
    }


    /* -----------------------------
     *  ADMIN – CATEGORY CRUD
     * ----------------------------- */

    // GET /admin/rubrics/categories
    public function categoryIndex()
    {
        $categories = RubricCategory::orderBy('order_no')->paginate(20);

        return view('rubrics.categories.index', compact('categories'));
    }

    // GET /admin/rubrics/categories/create
    public function categoryCreate()
    {
        return view('rubrics.categories.create');
    }

    // POST /admin/rubrics/categories
    public function categoryStore(Request $request)
    {
        $data = $request->validate([
            'title'    => ['required', 'string', 'max:255', 'unique:rubric_categories,title'],
            'order_no' => ['nullable', 'integer'],
        ]);

        RubricCategory::create($data);

        return redirect()
            ->route('rubrics.categories.index')
            ->with('success', 'Category created.');
    }

    // GET /admin/rubrics/categories/{category}/edit
    public function categoryEdit(RubricCategory $category)
    {
        return view('rubrics.categories.edit', compact('category'));
    }

    // PUT /admin/rubrics/categories/{category}
    public function categoryUpdate(Request $request, RubricCategory $category)
    {
        $data = $request->validate([
            'title'    => [
                'required',
                'string',
                'max:255',
                Rule::unique('rubric_categories', 'title')->ignore($category->id),
            ],
            'order_no' => ['nullable', 'integer'],
        ]);

        $category->update($data);

        return redirect()
            ->route('rubrics.categories.index')
            ->with('success', 'Category updated.');
    }

    // DELETE /admin/rubrics/categories/{category}
    public function categoryDestroy(RubricCategory $category)
    {
        $category->delete();

        return redirect()
            ->route('rubrics.categories.index')
            ->with('success', 'Category deleted.');
    }

    /* -----------------------------
     *  ADMIN – SECTION CRUD
     * ----------------------------- */

    // GET /admin/rubrics/sections
    public function sectionIndex()
    {
        $sections = RubricSection::with('category')
            ->orderBy('category_id')
            ->orderBy('order_no')
            ->paginate(20);

        $categories = RubricCategory::orderBy('order_no')->get();

        return view('rubrics.sections.index', compact('sections', 'categories'));
    }

    // GET /admin/rubrics/sections/create
    public function sectionCreate()
    {
        $categories = RubricCategory::orderBy('order_no')->get();

        return view('rubrics.sections.create', compact('categories'));
    }

    // POST /admin/rubrics/sections
    public function sectionStore(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:rubric_categories,id'],
            'title'       => ['required', 'string', 'max:255'],
            'evidence'    => ['nullable', 'string'],
            'notes'       => ['nullable', 'string'],
            'max_points'  => ['nullable', 'numeric'],
            'order_no'    => ['nullable', 'integer'],
        ]);

        RubricSection::create($data);

        return redirect()
            ->route('rubrics.sections.index')
            ->with('success', 'Section created.');
    }

    // GET /admin/rubrics/sections/{section}/edit
    public function sectionEdit(RubricSection $section)
    {
        $categories = RubricCategory::orderBy('order_no')->get();

        return view('rubrics.sections.edit', compact('section', 'categories'));
    }

    // PUT /admin/rubrics/sections/{section}
    public function sectionUpdate(Request $request, RubricSection $section)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:rubric_categories,id'],
            'title'       => ['required', 'string', 'max:255'],
            'evidence'    => ['nullable', 'string'],
            'notes'       => ['nullable', 'string'],
            'max_points'  => ['nullable', 'numeric'],
            'order_no'    => ['nullable', 'integer'],
        ]);

        $section->update($data);

        return redirect()
            ->route('rubrics.sections.index')
            ->with('success', 'Section updated.');
    }

    // DELETE /admin/rubrics/sections/{section}
    public function sectionDestroy(RubricSection $section)
    {
        $section->delete();

        return redirect()
            ->route('rubrics.sections.index')
            ->with('success', 'Section deleted.');
    }

    /* -----------------------------
     *  ADMIN – SUBSECTION CRUD
     * ----------------------------- */

    // GET /admin/rubrics/subsections
    public function subsectionIndex()
    {
        $subsections = RubricSubsection::with('section.category')
            ->orderBy('section_id')
            ->orderBy('order_no')
            ->paginate(20);

        $sections = RubricSection::orderBy('order_no')->get();

        return view('rubrics.subsections.index', compact('subsections', 'sections'));
    }

    // GET /admin/rubrics/subsections/create
    public function subsectionCreate()
    {
        $sections = RubricSection::with('category')->orderBy('order_no')->get();

        return view('rubrics.subsections.create', compact('sections'));
    }

    // POST /admin/rubrics/subsections
    public function subsectionStore(Request $request)
    {
        $data = $request->validate([
            'section_id'      => ['required', 'exists:rubric_sections,id'],
            'sub_section'     => ['required', 'string', 'max:255'],
            'evidence_needed' => ['nullable', 'string'],
            'max_points'      => ['nullable', 'numeric'],
            'notes'           => ['nullable', 'string'],
            'order_no'        => ['nullable', 'integer'],
        ]);

        RubricSubsection::create($data);

        return redirect()
            ->route('rubrics.subsections.index')
            ->with('success', 'Subsection created.');
    }

    // GET /admin/rubrics/subsections/{subsection}/edit
    public function subsectionEdit(RubricSubsection $subsection)
    {
        $sections = RubricSection::with('category')->orderBy('order_no')->get();

        return view('rubrics.subsections.edit', compact('subsection', 'sections'));
    }

    // PUT /admin/rubrics/subsections/{subsection}
    public function subsectionUpdate(Request $request, RubricSubsection $subsection)
    {
        $data = $request->validate([
            'section_id'      => ['required', 'exists:rubric_sections,section_id'],
            'sub_section'     => ['required', 'string', 'max:255'],
            'evidence_needed' => ['nullable', 'string'],
            'max_points'      => ['nullable', 'numeric'],
            'notes'           => ['nullable', 'string'],
            'order_no'        => ['nullable', 'integer'],
        ]);

        $subsection->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Subsection updated successfully.']);
        }

        return redirect()
            ->route('rubrics.index')
            ->with('success', 'Subsection updated.');
    }

    // DELETE /admin/rubrics/subsections/{subsection}
    public function subsectionDestroy(RubricSubsection $subsection, Request $request)
    {
        $subsection->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Subsection deleted successfully.']);
        }

        return redirect()
            ->route('rubrics.index')
            ->with('success', 'Subsection deleted.');
    }

    /* -----------------------------
     *  ADMIN – OPTIONS / ROLES CRUD
     * ----------------------------- */

    // Example only – adjust to your actual model/columns
    public function optionIndex()
    {
        $options = RubricOption::with('subsection.section.category')
            ->orderBy('subsection_id')
            ->paginate(20);

        return view('rubrics.options.index', compact('options'));
    }

    public function optionCreate()
    {
        $subsections = RubricSubsection::with('section.category')->orderBy('order_no')->get();

        return view('rubrics.options.create', compact('subsections'));
    }

    public function optionStore(Request $request)
    {
        $data = $request->validate([
            'subsection_id' => ['required', 'exists:rubric_subsections,sub_section_id'],
            'label'         => ['required', 'string', 'max:255'], // e.g. President, VP, etc.
            'points'        => ['required', 'numeric'],
            'order_no'      => ['nullable', 'integer'],
        ]);

        // Map subsection_id to sub_section_id for the model
        if (isset($data['subsection_id'])) {
            $data['sub_section_id'] = $data['subsection_id'];
            unset($data['subsection_id']);
        }

        RubricOption::create($data);

        return redirect()
            ->route('rubrics.options.index')
            ->with('success', 'Option created.');
    }

    public function optionEdit(RubricOption $option)
    {
        $subsections = RubricSubsection::with('section.category')->orderBy('order_no')->get();

        return view('rubrics.options.edit', compact('option', 'subsections'));
    }

    public function optionUpdate(Request $request, RubricOption $option)
    {
        $data = $request->validate([
            'subsection_id' => ['required', 'exists:rubric_subsections,sub_section_id'],
            'label'         => ['required', 'string', 'max:255'],
            'points'        => ['required', 'numeric'],
            'order_no'      => ['nullable', 'integer'],
        ]);

        // Map subsection_id to sub_section_id for the model
        if (isset($data['subsection_id'])) {
            $data['sub_section_id'] = $data['subsection_id'];
            unset($data['subsection_id']);
        }

        $option->update($data);

        return redirect()
            ->route('rubrics.options.index')
            ->with('success', 'Option updated.');
    }

    public function optionDestroy(RubricOption $option)
    {
        $option->delete();

        return redirect()
            ->route('rubrics.options.index')
            ->with('success', 'Option deleted.');
    }

    /* -----------------------------
     *  (OPTIONAL) EDIT HISTORY
     * ----------------------------- */

    // Here you can wrap whatever logic you currently have
    // for RubricEditHistoryController into methods like:
    //
    // public function editHistoryIndex(RubricSubsection $subsection) { ... }
    // public function editHistoryStore(...) { ... }
    // public function editHistoryRevert(...) { ... }
}
