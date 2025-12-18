<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\College;
use App\Models\Major;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProgramController extends Controller
{
    public function index(Request $request)
    {
        $query = Program::with('college');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%')
                    ->orWhereHas('college', function ($q2) use ($search) {
                        $q2->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($collegeId = $request->input('college_filter')) {
            $query->where('college_id', $collegeId);
        }

        $programs = $query->orderBy('name')->paginate(10)->withQueryString();
        $colleges = College::orderBy('name')->get();

        return view('admin.programs.index', compact('programs', 'colleges'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('programs', 'name')
                    ->where(fn($q) => $q->where('college_id', $request->college_id)),
            ],
            'college_id' => ['required', 'integer', 'exists:colleges,id'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('programs', 'code')->whereNull('deleted_at'),
            ],
            'major_name' => ['required', 'string', 'max:150'],
        ], [
            'name.required' => 'Program name is required.',
            'name.unique' => 'This program already exists in this college.',
            'college_id.required' => 'Please select a college.',
            'code.unique' => 'This code is already in use.',
            'major_name.required' => 'Major name is required.',
        ]);

        $program = Program::create([
            'name' => $data['name'],
            'college_id' => $data['college_id'],
            'code' => $data['code'] ?? null,
        ]);

        // Create the major associated with the program
        Major::create([
            'name' => $data['major_name'],
            'program_id' => $program->id,
        ]);

        return back()->with('success', 'Program and major added successfully.');
    }

    public function update(Request $request, Program $program)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('programs', 'name')
                    ->ignore($program->id)
                    ->where(fn($q) => $q->where('college_id', $request->college_id)),
            ],
            'college_id' => ['required', 'integer', 'exists:colleges,id'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('programs', 'code')->ignore($program->id)->whereNull('deleted_at'),
            ],
        ], [
            'name.required' => 'Program name is required.',
            'name.unique' => 'This program already exists in this college.',
            'college_id.required' => 'Please select a college.',
            'code.unique' => 'This code is already in use.',
        ]);

        $program->update($data);

        return back()->with('success', 'Program updated successfully.');
    }

    public function destroy(Program $program)
    {
        // Check if program has majors
        if ($program->majors()->count() > 0) {
            return back()->withErrors([
                'program' => 'Cannot delete program. It has associated majors. Please delete or reassign majors first.'
            ]);
        }

        $program->delete();

        return redirect()
            ->route('admin.programs.index')
            ->with('success', 'Program deleted successfully.');
    }
}

