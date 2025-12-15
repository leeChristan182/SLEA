<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\Program;
use App\Models\Major;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CollegeController extends Controller
{
    public function index(Request $request)
    {
        $query = College::query();

        // Filter by college if selected
        if ($collegeFilter = $request->input('college_filter')) {
            $query->where('id', $collegeFilter);
        }

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        $colleges = $query->with(['programs.majors'])->orderBy('name')->paginate(10)->withQueryString();
        $allColleges = College::orderBy('name')->get();

        return view('admin.colleges.index', compact('colleges', 'allColleges'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('colleges', 'name'),
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('colleges', 'code'),
            ],
            'programs' => ['nullable', 'array'],
            'programs.*' => ['required', 'string', 'max:150'],
        ], [
            'name.required' => 'College name is required.',
            'name.unique' => 'This college already exists.',
            'code.unique' => 'This code is already in use.',
        ]);

        $college = College::create([
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
        ]);

        $message = 'College added successfully.';

        // Create programs if provided
        if (!empty($data['programs'])) {
            $createdCount = 0;
            $skippedCount = 0;
            
            foreach ($data['programs'] as $programName) {
                $programName = trim($programName);
                if (!empty($programName)) {
                    // Check if program already exists in this college
                    $exists = Program::where('college_id', $college->id)
                        ->where('name', $programName)
                        ->exists();
                    
                    if (!$exists) {
                        $program = Program::create([
                            'name' => $programName,
                            'college_id' => $college->id,
                        ]);
                        
                        // Create a default major for each program
                        Major::create([
                            'name' => $programName,
                            'program_id' => $program->id,
                        ]);
                        $createdCount++;
                    } else {
                        $skippedCount++;
                    }
                }
            }
            
            if ($skippedCount > 0) {
                $message = "College added. {$createdCount} program(s) added, {$skippedCount} duplicate(s) skipped.";
            } else if ($createdCount > 0) {
                $message = "College and {$createdCount} program(s) added successfully.";
            }
        }

        return back()->with('success', $message);
    }

    public function getProgramsMajors(College $college)
    {
        $college->load(['programs.majors']);
        
        return response()->json([
            'programs' => $college->programs->map(function ($program) {
                return [
                    'id' => $program->id,
                    'name' => $program->name,
                    'code' => $program->code,
                    'majors' => $program->majors->map(function ($major) {
                        return [
                            'id' => $major->id,
                            'major_name' => $major->major_name,
                            'code' => $major->code,
                        ];
                    }),
                ];
            }),
        ]);
    }

    public function update(Request $request, College $college)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('colleges', 'name')->ignore($college->id),
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('colleges', 'code')->ignore($college->id),
            ],
            'edit_programs' => ['nullable', 'array'],
            'edit_programs.*.id' => ['nullable', 'integer', 'exists:programs,id'],
            'edit_programs.*.name' => ['required_with:edit_programs', 'string', 'max:150'],
            'edit_programs.*.code' => ['nullable', 'string', 'max:50'],
            'edit_programs.*.majors' => ['nullable', 'array'],
            'edit_programs.*.majors.*.id' => ['nullable', 'integer', 'exists:majors,id'],
            'edit_programs.*.majors.*.name' => ['nullable', 'string', 'max:150'],
        ], [
            'name.required' => 'College name is required.',
            'name.unique' => 'This college already exists.',
            'code.unique' => 'This code is already in use.',
        ]);

        $college->update([
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
        ]);

        // Handle programs, majors, and codes if provided
        if (!empty($data['edit_programs'])) {
            foreach ($data['edit_programs'] as $programData) {
                if (!empty($programData['id'])) {
                    // Update existing program
                    $program = Program::find($programData['id']);
                    if ($program && $program->college_id == $college->id) {
                        $program->update([
                            'name' => $programData['name'],
                            'code' => $programData['code'] ?? null,
                        ]);
                        
                        // Handle majors
                        $existingMajorIds = $program->majors->pluck('id')->toArray();
                        $submittedMajorIds = collect($programData['majors'] ?? [])
                            ->pluck('id')
                            ->filter()
                            ->toArray();
                        
                        // Delete majors that were removed
                        Major::where('program_id', $program->id)
                            ->whereNotIn('id', $submittedMajorIds)
                            ->delete();
                        
                        if (!empty($programData['majors'])) {
                            foreach ($programData['majors'] as $majorData) {
                                if (!empty($majorData['name'])) {
                                    if (!empty($majorData['id'])) {
                                        // Update existing major
                                        $major = Major::find($majorData['id']);
                                        if ($major && $major->program_id == $program->id) {
                                            $major->update([
                                                'name' => $majorData['name'],
                                            ]);
                                        }
                                    } else {
                                        // Create new major
                                        Major::create([
                                            'name' => $majorData['name'],
                                            'program_id' => $program->id,
                                        ]);
                                    }
                                }
                            }
                        } else {
                            // If no majors submitted, delete all existing majors for this program
                            Major::where('program_id', $program->id)->delete();
                        }
                    }
                } else {
                    // Create new program
                    $program = Program::create([
                        'name' => $programData['name'],
                        'college_id' => $college->id,
                        'code' => $programData['code'] ?? null,
                    ]);
                    
                    // Handle majors for new program
                    if (!empty($programData['majors'])) {
                        foreach ($programData['majors'] as $majorData) {
                            if (!empty($majorData['name'])) {
                                Major::create([
                                    'name' => $majorData['name'],
                                    'program_id' => $program->id,
                                ]);
                            }
                        }
                    }
                }
            }
        }

        return back()->with('success', 'College updated successfully.');
    }

    public function storeProgram(Request $request)
    {
        $data = $request->validate([
            'college_id' => ['required', 'integer', 'exists:colleges,id'],
            'program_name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('programs', 'name')
                    ->where(fn($q) => $q->where('college_id', $request->college_id)),
            ],
            'program_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('programs', 'code'),
            ],
            'major_name' => ['nullable', 'string', 'max:150'],
        ], [
            'college_id.required' => 'Please select a college.',
            'program_name.required' => 'Program name is required.',
            'program_name.unique' => 'This program already exists in this college.',
            'program_code.unique' => 'This code is already in use.',
        ]);

        $program = Program::create([
            'name' => $data['program_name'],
            'college_id' => $data['college_id'],
            'code' => $data['program_code'] ?? null,
        ]);

        // Create major only if provided
        if (!empty($data['major_name'])) {
            Major::create([
                'name' => trim($data['major_name']),
                'program_id' => $program->id,
            ]);
        }

        $message = !empty($data['major_name'])
            ? 'Program and major added successfully.'
            : 'Program added successfully.';

        return back()->with('success', $message);
    }

    public function destroy(College $college)
    {
        // Check if college has programs
        if ($college->programs()->count() > 0) {
            return back()->withErrors([
                'college' => 'Cannot delete college. It has associated programs. Please delete or reassign programs first.'
            ]);
        }

        $college->delete();

        return redirect()
            ->route('admin.colleges.index')
            ->with('success', 'College deleted successfully.');
    }
}

