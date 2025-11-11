<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UpdateProgram;

class UpdateProgramController extends Controller
{
    public function index()
    {
        $updates = UpdateProgram::all();
        return view('program-update.index', compact('updates'));
    }

    public function create()
    {
        return view('program-update.create');
        return view('update_programs.index', compact('updates')); // folder: resources/views/update_programs/
return view('update_programs.create');
return view('update_programs.edit', compact('update'));

    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|string|max:20',
            'old_program' => 'required|string|max:50',
            'old_major' => 'nullable|string|max:50',
            'new_program' => 'required|string|max:50',
            'new_major' => 'nullable|string|max:50',
            'date_prog_changed' => 'required|date',
        ]);

        UpdateProgram::create($request->all());

        return redirect()->route('program-update.index')->with('success', 'Program updated.');
    }

    public function edit($id)
    {
        $programUpdate = UpdateProgram::findOrFail($id);
        return view('program-update.edit', compact('programUpdate'));
    }

    public function update(Request $request, $id)
    {
        $programUpdate = UpdateProgram::findOrFail($id);

        $request->validate([
            'student_id' => 'required|string|max:20',
            'old_program' => 'required|string|max:50',
            'old_major' => 'nullable|string|max:50',
            'new_program' => 'required|string|max:50',
            'new_major' => 'nullable|string|max:50',
            'date_prog_changed' => 'required|date',
        ]);

        $programUpdate->update($request->all());

        return redirect()->route('program-update.index')->with('success', 'Program update updated.');
    }

    public function destroy($id)
    {
        $programUpdate = UpdateProgram::findOrFail($id);
        $programUpdate->delete();

        return redirect()->route('program-update.index')->with('success', 'Program update deleted.');
    }
   

}
