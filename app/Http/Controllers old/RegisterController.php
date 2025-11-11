<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CollegeProgram;
use App\Models\Cluster;
use App\Models\Organization;
use App\Models\LeadershipType;
use App\Models\StudentAccount;
use App\Models\LeadershipInformation;
use App\Models\RubricSubsectionLeadership;
use Carbon\Carbon;

class RegisterController extends Controller
{
    /**
     * Show the registration form
     */
    public function show()
    {
        $colleges = CollegeProgram::select('college_name')
            ->distinct()
            ->orderBy('college_name')
            ->get();

        $leadershipTypes = LeadershipType::orderBy('name')->get();

        return view('register', compact('colleges', 'leadershipTypes'));
    }

    /**
     * Fetch clusters for a given leadership type (AJAX)
     */
    public function getClusters(Request $request)
    {
        $typeId = $request->leadership_type_id;
        $typeName = LeadershipType::find($typeId)?->name;

        if ($typeName === 'CCO') {
            $clusters = Cluster::orderBy('name')->get(['id', 'name']);
        } elseif (in_array($typeName, ['USG', 'OSC', 'LC', 'LGU'])) {
            $clusters = collect();
        } else {
            $clusters = Cluster::where('leadership_type_id', $typeId)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return response()->json($clusters);
    }

    public function getOrganizations(Request $request)
    {
        $typeId = $request->leadership_type_id;
        $typeName = LeadershipType::find($typeId)?->name;

        if ($typeName === 'CCO') {
            $orgs = Organization::orderBy('name')->get(['id', 'name']);
        } elseif (in_array($typeName, ['USG', 'OSC', 'LC', 'LGU'])) {
            $orgs = collect();
        } else {
            $orgs = Organization::where('cluster_id', $request->cluster_id)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return response()->json($orgs);
    }

    public function getPositions(Request $request)
    {
        $typeId = $request->leadership_type_id;

        $positions = DB::table('positions')
            ->where('leadership_type_id', $typeId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($positions);
    }

    /**
     * Fetch programs for a given college (AJAX)
     */
    public function getPrograms(Request $request)
    {
        $collegeName = $request->college_name;

        $programs = DB::table('college_programs')
            ->where('college_name', $collegeName)
            ->select('program_name', 'major_name')
            ->orderBy('program_name')
            ->get();

        $grouped = $programs->groupBy('program_name')->map(function ($items) {
            return $items->pluck('major_name')->filter()->unique()->values();
        });

        return response()->json($grouped);
    }

    /**
     * Store student registration
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Personal
            'student_id' => 'required|string|unique:student_accounts,student_id',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'birth_date' => 'required|date|before:today',
            'email_address' => [
                'required',
                'email',
                'regex:/@usep\.edu\.ph$/',
                'unique:student_accounts,email_address'
            ],
            'contact' => 'required|string|max:15',
            'password' => 'required|min:8|confirmed',

            // Academic
            'college_name' => 'required|string|in:' .
                implode(',', DB::table('college_programs')->distinct()->pluck('college_name')->toArray()),
            'program' => 'required|string|max:150',
            'major_name' => 'nullable|string|max:150',
            'year_level' => 'required|integer|min:1|max:5',

            // Leadership
            'leadership_type_id' => 'required|exists:leadership_types,id',
            'cluster_id' => 'required|exists:clusters,id',
            'organization_id' => 'required|exists:organizations,id',
            'position_id' => 'required|exists:positions,id',
            'term' => 'required|string|max:255',
            'issued_by' => 'required|string|max:255',
            'leadership_status' => 'required|string|max:255',
        ]);

        // Auto compute graduation and age
        $entryYear = intval(substr($validated['student_id'], 0, 4));
        $graduationYear = $entryYear + 4;
        $age = Carbon::parse($validated['birth_date'])->age;

        DB::transaction(function () use ($validated, $graduationYear, $age) {
            // Personal Info
            DB::table('student_personal_information')->insert([
                'student_id' => $validated['student_id'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'email_address' => $validated['email_address'],
                'contact_number' => $validated['contact'],
                'birth_date' => $validated['birth_date'],
                'age' => $age,
                'dateacc_created' => now(),
            ]);

            // Academic Info
            DB::table('academic_information')->insert([
                'student_id' => $validated['student_id'],
                'program' => $validated['program'],
                'major' => $validated['major_name'],
                'year_level' => $validated['year_level'],
                'graduate_prior' => $graduationYear,
                'college' => $validated['college_name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Account Info
            DB::table('student_accounts')->insert([
                'student_id' => $validated['student_id'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'email_address' => $validated['email_address'],
                'contact' => $validated['contact'],
                'password' => bcrypt($validated['password']),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Leadership Info
            DB::table('leadership_information')->insert([
                'student_id' => $validated['student_id'],
                'leadership_type' => \App\Models\LeadershipType::find($validated['leadership_type_id'])->name,
                'organization_name' => \App\Models\Organization::find($validated['organization_id'])->name,
                'position' => \App\Models\Position::find($validated['position_id'])->name,
                'term' => $validated['term'],
                'issued_by' => $validated['issued_by'],
                'leadership_status' => $validated['leadership_status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('login.show')->with(
            'status',
            'Your registration has been submitted for review. You will be able to log in once approved.'
        );
    }
}
