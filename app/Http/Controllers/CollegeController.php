<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\Program;
use App\Models\Major;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CollegeController extends Controller
{
    public function index(Request $request)
    {
        $query = College::query();

        if ($collegeFilter = $request->input('college_filter')) {
            $query->where('id', $collegeFilter);
        }

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        $colleges = $query
            ->with(['programs.majors'])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

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
            'programs'   => ['nullable', 'array'],
            'programs.*' => ['required', 'string', 'max:150'],
        ], [
            'name.required' => 'College name is required.',
            'name.unique'   => 'This college already exists.',
            'code.unique'   => 'This code is already in use.',
        ]);

        return DB::transaction(function () use ($data) {
            $college = College::create([
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
            ]);

            $message = 'College added successfully.';

            if (!empty($data['programs'])) {
                $createdCount = 0;
                $skippedCount = 0;

                foreach ($data['programs'] as $programName) {
                    $programName = trim((string) $programName);
                    if ($programName === '') continue;

                    $exists = Program::where('college_id', $college->id)
                        ->where('name', $programName)
                        ->exists();

                    if ($exists) {
                        $skippedCount++;
                        continue;
                    }

                    $program = Program::create([
                        'name'       => $programName,
                        'college_id' => $college->id,
                    ]);

                    // If you *want* a default major equal to program name, keep this.
                    Major::create([
                        'name'       => $programName,
                        'program_id' => $program->id,
                    ]);

                    $createdCount++;
                }

                if ($skippedCount > 0) {
                    $message = "College added. {$createdCount} program(s) added, {$skippedCount} duplicate(s) skipped.";
                } elseif ($createdCount > 0) {
                    $message = "College and {$createdCount} program(s) added successfully.";
                }
            }

            return back()->with('success', $message);
        });
    }

    public function getProgramsMajors(College $college)
    {
        $college->load(['programs.majors']);

        return response()->json([
            'programs' => $college->programs->map(function ($program) {
                return [
                    'id'   => $program->id,
                    'name' => $program->name,
                    'code' => $program->code,
                    'majors' => $program->majors->map(function ($major) {
                        return [
                            'id'   => $major->id,
                            'name' => $major->name, // ✅ consistent
                            'code' => $major->code,
                        ];
                    })->values(),
                ];
            })->values(),
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

            'edit_programs.*.id'   => ['nullable', 'integer', 'exists:programs,id'],
            'edit_programs.*.name' => ['required_with:edit_programs', 'string', 'max:150'],
            'edit_programs.*.code' => ['nullable', 'string', 'max:50'],

            'edit_programs.*.majors' => ['nullable', 'array'],
            'edit_programs.*.majors.*.id'   => ['nullable', 'integer', 'exists:majors,id'],
            'edit_programs.*.majors.*.name' => ['nullable', 'string', 'max:150'],
        ], [
            'name.required' => 'College name is required.',
            'name.unique'   => 'This college already exists.',
            'code.unique'   => 'This code is already in use.',
        ]);

        return DB::transaction(function () use ($data, $college) {

            $college->update([
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
            ]);

            if (!empty($data['edit_programs'])) {
                foreach ($data['edit_programs'] as $programData) {

                    // ----------------------------
                    // UPDATE EXISTING PROGRAM
                    // ----------------------------
                    if (!empty($programData['id'])) {
                        $program = Program::find($programData['id']);

                        if (!$program || (int)$program->college_id !== (int)$college->id) {
                            continue; // ignore tampered IDs
                        }

                        $program->update([
                            'name' => $programData['name'],
                            'code' => $programData['code'] ?? null,
                        ]);

                        // Majors handling
                        if (array_key_exists('majors', $programData)) {
                            $majorsPayload = $programData['majors'] ?? [];

                            // IDs submitted (existing majors kept)
                            $submittedMajorIds = collect($majorsPayload)
                                ->pluck('id')
                                ->filter()
                                ->map(fn($v) => (int)$v)
                                ->toArray();

                            // ✅ Only delete "removed" majors if there is at least one submitted id.
                            // If user deleted all majors intentionally, you’ll typically receive majors=[]
                            // and we handle that case below.
                            if (!empty($submittedMajorIds)) {
                                $program->majors()
                                    ->whereNotIn('id', $submittedMajorIds)
                                    ->delete();
                            }

                            // If majors array exists but is empty => user removed all majors
                            if (is_array($majorsPayload) && count($majorsPayload) === 0) {
                                $program->majors()->delete();
                                continue;
                            }

                            // Upsert majors
                            foreach ($majorsPayload as $majorData) {
                                $majorName = trim((string)($majorData['name'] ?? ''));
                                if ($majorName === '') continue;

                                if (!empty($majorData['id'])) {
                                    $major = Major::find($majorData['id']);
                                    if ($major && (int)$major->program_id === (int)$program->id) {
                                        $major->update(['name' => $majorName]);
                                    }
                                } else {
                                    $program->majors()->create(['name' => $majorName]);
                                }
                            }
                        }

                        continue;
                    }

                    // ----------------------------
                    // CREATE NEW PROGRAM
                    // ----------------------------
                    $program = Program::create([
                        'name'       => $programData['name'],
                        'college_id' => $college->id,
                        'code'       => $programData['code'] ?? null,
                    ]);

                    // Majors for new program
                    if (!empty($programData['majors'])) {
                        foreach ($programData['majors'] as $majorData) {
                            $majorName = trim((string)($majorData['name'] ?? ''));
                            if ($majorName === '') continue;

                            $program->majors()->create([
                                'name' => $majorName,
                            ]);
                        }
                    }
                }
            }

            return back()->with('success', 'College updated successfully.');
        });
    }

    public function storeProgram(Request $request)
    {
        $data = $request->validate([
            'college_id' => ['required', 'integer', 'exists:colleges,id'],
            'program_name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('programs', 'name')->where(fn($q) => $q->where('college_id', $request->college_id)),
            ],
            'program_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('programs', 'code'),
            ],
            'major_name' => ['nullable', 'string', 'max:150'],
        ], [
            'college_id.required'   => 'Please select a college.',
            'program_name.required' => 'Program name is required.',
            'program_name.unique'   => 'This program already exists in this college.',
            'program_code.unique'   => 'This code is already in use.',
        ]);

        return DB::transaction(function () use ($data) {

            $program = Program::create([
                'name'       => $data['program_name'],
                'college_id' => $data['college_id'],
                'code'       => $data['program_code'] ?? null,
            ]);

            if (!empty($data['major_name'])) {
                $program->majors()->create([
                    'name' => trim((string)$data['major_name']),
                ]);
            }

            $message = !empty($data['major_name'])
                ? 'Program and major added successfully.'
                : 'Program added successfully.';

            return back()->with('success', $message);
        });
    }

    public function destroy(College $college)
    {
        if ($college->programs()->count() > 0) {
            return back()->withErrors([
                'college' => 'Cannot delete college. It has associated programs. Please delete or reassign programs first.',
            ]);
        }

        $college->delete();

        return redirect()
            ->route('admin.colleges.index')
            ->with('success', 'College deleted successfully.');
    }
}
