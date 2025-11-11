<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RubricSubsectionLeadershipController extends Controller
{
    // GET /admin/rubrics/leadership
    public function index()
    {
        $rows = Schema::hasTable('rubric_leadership')
            ? DB::table('rubric_leadership')->orderBy('id')->get()
            : collect();

        return view('admin.rubrics.leadership.index', compact('rows'));
    }

    // GET /admin/rubrics/leadership/create
    public function create()
    {
        return view('admin.rubrics.leadership.create');
    }

    // POST /admin/rubrics/leadership
    public function store(Request $request)
    {
        $request->validate([
            'name'       => ['required', 'string', 'max:150'],
            'max_points' => ['required', 'numeric', 'min:0'],
        ]);

        if (Schema::hasTable('rubric_leadership')) {
            DB::table('rubric_leadership')->insert([
                'name'       => $request->name,
                'max_points' => $request->max_points,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('admin.rubrics.leadership.index')->with('status', 'Leadership rubric added.');
    }

    // GET /admin/rubrics/leadership/{id}/edit
    public function edit(int $id)
    {
        $row = Schema::hasTable('rubric_leadership')
            ? DB::table('rubric_leadership')->where('id', $id)->first()
            : null;

        abort_if(! $row, 404);
        return view('admin.rubrics.leadership.edit', compact('row'));
    }

    // PUT/PATCH /admin/rubrics/leadership/{id}
    public function update(Request $request, int $id)
    {
        $request->validate([
            'name'       => ['required', 'string', 'max:150'],
            'max_points' => ['required', 'numeric', 'min:0'],
        ]);

        if (Schema::hasTable('rubric_leadership')) {
            DB::table('rubric_leadership')->where('id', $id)->update([
                'name'       => $request->name,
                'max_points' => $request->max_points,
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('admin.rubrics.leadership.index')->with('status', 'Leadership rubric updated.');
    }

    // DELETE /admin/rubrics/leadership/{id}
    public function destroy(int $id)
    {
        if (Schema::hasTable('rubric_leadership')) {
            DB::table('rubric_leadership')->where('id', $id)->delete();
        }

        return redirect()->route('admin.rubrics.leadership.index')->with('status', 'Leadership rubric deleted.');
    }
}
