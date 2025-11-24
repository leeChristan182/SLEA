<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /* =========================
     |  LOGIN
     * ========================= */
    public function showLogin()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            $request->session()->regenerate();

            return match (Auth::user()->role) {
                User::ROLE_ADMIN    => redirect()->route('admin.profile'),
                User::ROLE_ASSESSOR => redirect()->route('assessor.profile'),
                default             => redirect()->route('student.profile'),
            };
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.show');
    }

    /* =========================
     |  REGISTER (STUDENT)
     * ========================= */
    public function showRegister()
    {
        $colleges        = $this->getCollegesList();
        $leadershipTypes = $this->getLeadershipTypesList();

        return view('auth.register', compact('colleges', 'leadershipTypes'));
    }

    public function register(Request $request)
    {
        $rules = [
            // Step 1
            'last_name'     => ['required', 'string', 'max:50'],
            'first_name'    => ['required', 'string', 'max:50'],
            'middle_name'   => ['nullable', 'string', 'max:50'],
            'birth_date'    => ['required', 'date', 'before:today'],
            'email_address' => [
                'required',
                'email',
                'max:100',
                'regex:/^[a-zA-Z0-9._%+\-]+@usep\.edu\.ph$/',
                Rule::unique('users', 'email'),
            ],
            'contact'       => ['required', 'string', 'max:20'],

            // Step 2
            'student_id'    => [
                'required',
                'string',
                'max:30',
                Rule::unique('student_academic', 'student_number'),
            ],
            'college_id'    => ['required', 'integer', 'exists:colleges,id'],
            'program_id'    => ['required', 'integer', 'exists:programs,id'],
            'major_id'      => ['nullable', 'integer', 'exists:majors,id'],
            'year_level'    => ['required', 'in:1,2,3,4,5'],

            // Step 3
            'leadership_type_id' => ['required', 'integer', 'exists:leadership_types,id'],
            'position_id'        => ['required', 'integer', 'exists:positions,id'],
            'term'               => ['required', 'string', 'max:25'],
            'issued_by'          => ['required', 'string', 'max:150'],
            'leadership_status'  => ['required', 'in:Active,Inactive'],

            // Step 4
            'password'      => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'privacy_agree' => ['accepted'],
        ];

        $messages = [
            'email_address.regex' => 'Please use a valid @usep.edu.ph email address.',
            'email_address.unique' => 'This email address is already registered. Please use a different email or try logging in.',
            'student_id.unique' => 'This student ID is already registered. Please check your student ID or contact support if you believe this is an error.',
        ];

        $validated = $request->validate($rules, $messages);

        // Cluster/org enforcement for CCO etc.
        $needsOrg = $this->leadershipRequiresOrg((int) $validated['leadership_type_id']);
        
        // Check if CCO is selected
        $isCCO = DB::table('leadership_types')
            ->where('id', (int) $validated['leadership_type_id'])
            ->where('key', 'cco')
            ->exists();

        if ($isCCO) {
            // CCO: cluster_id and organization_id should be "N/A" (stored as null)
            $request->validate([
                'cluster_id'      => ['required', 'in:N/A'],
                'organization_id' => ['required', 'in:N/A'],
            ]);
            $validated['cluster_id']      = null;
            $validated['organization_id'] = null;
        } elseif ($needsOrg) {
            // Other types that require org: normal validation
            $request->validate([
                'cluster_id'      => ['required', 'integer', 'exists:clusters,id'],
                'organization_id' => ['required', 'integer', 'exists:organizations,id'],
            ]);
            $validated['cluster_id']      = (int) $request->input('cluster_id');
            $validated['organization_id'] = (int) $request->input('organization_id');
        } else {
            // Types that don't require org
            $validated['cluster_id']      = null;
            $validated['organization_id'] = null;
        }

        // Expected grad + eligibility
        $expectedGradYear = $this->computeExpectedGradYear(
            $validated['student_id'],
            (int) $validated['year_level']
        );
        $eligibility = (now()->year > $expectedGradYear) ? 'needs_revalidation' : 'eligible';

        DB::beginTransaction();
        try {
            /** @var User $user */
            $user = User::create([
                'first_name'           => $validated['first_name'],
                'last_name'            => $validated['last_name'],
                'middle_name'          => $validated['middle_name'] ?? null,
                'email'                => $validated['email_address'],
                'password'             => $validated['password'], // hashed by mutator
                'contact'              => $validated['contact'],
                'birth_date'           => $validated['birth_date'],
                'profile_picture_path' => null,
                'role'                 => User::ROLE_STUDENT,
                'status'               => User::STATUS_PENDING,
            ]);

            // student_academic
            $user->studentAcademic()->updateOrCreate([], [
                'student_number'     => $validated['student_id'],
                'college_id'         => $validated['college_id'],
                'program_id'         => $validated['program_id'],
                'major_id'           => $validated['major_id'] ?? null,
                'year_level'         => $validated['year_level'],
                'expected_grad_year' => $expectedGradYear,
                'eligibility_status' => $eligibility,
                'revalidated_at'     => null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            // leadership record
            if (Schema::hasTable('student_leaderships')) {
                DB::table('student_leaderships')->insert([
                    'user_id'            => $user->id,
                    'leadership_type_id' => (int) $validated['leadership_type_id'],
                    'cluster_id'         => $validated['cluster_id'],
                    'organization_id'    => $validated['organization_id'],
                    'position_id'        => (int) $validated['position_id'],
                    'term'               => $validated['term'],
                    'issued_by'          => $validated['issued_by'],
                    'leadership_status'  => $validated['leadership_status'],
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route('login.show')
                ->with('status', 'Registration received. Please wait for account approval.');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            report($e);

            // Check for specific database constraint violations
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();

            // Check if it's a unique constraint violation
            if (str_contains($errorMessage, 'UNIQUE constraint failed') || 
                str_contains($errorMessage, 'Duplicate entry') ||
                str_contains($errorMessage, 'unique constraint')) {
                
                // Check which field caused the violation
                if (str_contains($errorMessage, 'email') || str_contains($errorMessage, 'users.email')) {
                    return back()
                        ->withErrors(['email_address' => 'This email address is already registered. Please use a different email or try logging in.'])
                        ->withInput();
                } elseif (str_contains($errorMessage, 'student_number') || str_contains($errorMessage, 'student_id')) {
                    return back()
                        ->withErrors(['student_id' => 'This student ID is already registered. Please check your student ID or contact support if you believe this is an error.'])
                        ->withInput();
                } else {
                    return back()
                        ->withErrors(['register' => 'This information is already registered. Please check your details or contact support.'])
                        ->withInput();
                }
            }

            // Generic database error
            return back()
                ->withErrors(['register' => 'Could not complete registration. Please try again.'])
                ->withInput();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()
                ->withErrors(['register' => 'Could not complete registration. Please try again.'])
                ->withInput();
        }
    }

    /* =========================
     |  OTP placeholders
     * ========================= */
    public function showOtp()
    {
        return view('auth.otp');
    }
    public function verifyOtp()
    {
        return back();
    }
    public function resendOtp()
    {
        return back();
    }

    /* =========================
     |  AJAX DROPDOWNS
     * ========================= */
    public function getPrograms(Request $r)
    {
        $collegeId = (int) $r->query('college_id');
        if (!$collegeId || !Schema::hasTable('programs')) return response()->json([]);

        $rows = DB::table('programs')
            ->where('college_id', $collegeId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($rows);
    }

    public function getMajors(Request $r)
    {
        $programId = (int) $r->query('program_id');
        if (!$programId || !Schema::hasTable('majors')) return response()->json([]);

        $rows = DB::table('majors')
            ->where('program_id', $programId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($rows);
    }

    private function councilOrgNames(): array
    {
        return [
            'University Student Government (USG)',
            'Obrero Student Council (OSC)',
            'Local Council (LC)',
            'Council of Clubs and Organizations (CCO)',
            'Local Government Unit (LGU)',
            'League of Class Mayors (LCM)',
        ];
    }

    public function getCouncilPositions(Request $request)
    {
        if (!Schema::hasTable('positions') || !Schema::hasTable('leadership_types')) {
            return response()->json([]);
        }

        $typeId = (int) $request->query('leadership_type_id');
        $orgId  = (int) $request->query('organization_id');

        // If organization_id is provided (for CCO), load positions via organization_position
        if ($orgId && Schema::hasTable('organization_position')) {
            $rows = DB::table('organization_position as op')
                ->join('positions as p', 'p.id', '=', 'op.position_id')
                ->where('op.organization_id', $orgId)
                ->orderBy('p.rank_order')
                ->orderBy('p.name')
                ->select('p.id', 'p.name', 'op.alias')
                ->get()
                ->map(fn($r) => [
                    'id'   => $r->id,
                    'name' => $r->alias ?: $r->name,
                ]);

            return response()->json($rows);
        }

        // Load positions directly by leadership_type_id
        if ($typeId) {
            $rows = DB::table('positions')
                ->where('leadership_type_id', $typeId)
                ->orderBy('rank_order')
                ->orderBy('name')
                ->select('id', 'name')
                ->get()
                ->map(fn($r) => [
                    'id'   => $r->id,
                    'name' => $r->name,
                ]);

            return response()->json($rows);
        }

        return response()->json([]);
    }

    public function getPositions(Request $r)
    {
        $orgId = (int) $r->query('organization_id');
        if (!$orgId || !Schema::hasTable('organization_position')) return response()->json([]);

        $rows = DB::table('organization_position as op')
            ->join('positions as p', 'p.id', '=', 'op.position_id')
            ->where('op.organization_id', $orgId)
            ->orderBy('p.name')
            ->get(['p.id', 'p.name']);

        return response()->json($rows);
    }

    public function getClusters(Request $request)
    {
        if (!Schema::hasTable('clusters')) {
            return response()->json([]);
        }

        $q = DB::table('clusters')->orderBy('name');

        if (Schema::hasColumn('clusters', 'leadership_type_id')) {
            $typeId = (int) $request->query('leadership_type_id');
            if ($typeId) {
                $q->where('leadership_type_id', $typeId);
            }
        }

        return response()->json($q->pluck('name', 'id'));
    }

    public function getOrganizations(Request $request)
    {
        $clusterId = (int) $request->query('cluster_id');
        if (!Schema::hasTable('organizations')) return response()->json([]);

        $rows = DB::table('organizations')
            ->when($clusterId, fn($q) => $q->where('cluster_id', $clusterId))
            ->orderBy('name')
            ->select('id', 'name')
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->name,
            ]);

        return response()->json($rows);
    }

    public function getLeadershipTypes()
    {
        if (!Schema::hasTable('leadership_types')) return response()->json([]);

        $rows = DB::table('leadership_types')
            ->select('id', 'name', 'key', 'requires_org')
            ->orderBy('name')
            ->get();

        return response()->json($rows);
    }

    private function getCollegesList()
    {
        if (Schema::hasTable('colleges')) {
            $cols = Schema::getColumnListing('colleges');

            $nameCol = in_array('college_name', $cols) ? 'college_name'
                : (in_array('name', $cols) ? 'name' : null);

            if ($nameCol) {
                return DB::table('colleges')
                    ->select(['id', DB::raw("$nameCol as college_name")])
                    ->whereNotNull($nameCol)
                    ->orderBy($nameCol)
                    ->get();
            }
            return collect();
        }

        if (Schema::hasTable('colleges_programs_majors')) {
            return DB::table('colleges_programs_majors')
                ->selectRaw('MIN(rowid) AS id, college_name')
                ->whereNotNull('college_name')
                ->groupBy('college_name')
                ->orderBy('college_name')
                ->get();
        }

        return collect();
    }

    private function getLeadershipTypesList()
    {
        if (!Schema::hasTable('leadership_types')) return collect();

        // Order by the same sequence as defined in LeadershipTypeSeeder
        return DB::table('leadership_types')
            ->select('id', 'name', 'key', 'requires_org')
            ->orderByRaw("CASE `key` 
                WHEN 'usg' THEN 1 
                WHEN 'osc' THEN 2 
                WHEN 'lc' THEN 3 
                WHEN 'cco' THEN 4 
                WHEN 'sco' THEN 5 
                WHEN 'lgu' THEN 6 
                WHEN 'lcm' THEN 7 
                WHEN 'eap' THEN 8 
                ELSE 99 
            END")
            ->get();
    }

    private function leadershipRequiresOrg(?int $typeId): bool
    {
        if (!$typeId || !Schema::hasTable('leadership_types')) return false;

        return (bool) DB::table('leadership_types')
            ->where('id', $typeId)
            ->value('requires_org');
    }

    private function computeExpectedGradYear(string $studentId, int $yearLevel): int
    {
        if (preg_match('/^(\d{4})/', $studentId, $m)) {
            $entryYear = (int) $m[1];
        } else {
            $entryYear = (int) now()->format('Y') - max(0, $yearLevel - 1);
        }

        $defaultDuration = 4;
        return $entryYear + $defaultDuration;
    }

    public function getAcademicsMap()
    {
        if (!Schema::hasTable('programs') || !Schema::hasTable('majors')) {
            return response()->json(['programsByCollege' => [], 'majorsByProgram' => []]);
        }

        $programs = DB::table('programs')->select('id', 'college_id', 'name')->orderBy('name')->get();
        $majors   = DB::table('majors')->select('id', 'program_id', 'name')->orderBy('name')->get();

        $pMap = [];
        foreach ($programs as $p) {
            $pMap[$p->college_id][] = ['id' => $p->id, 'name' => $p->name];
        }

        $mMap = [];
        foreach ($majors as $m) {
            $mMap[$m->program_id][] = ['id' => $m->id, 'name' => $m->name];
        }

        return response()->json([
            'programsByCollege' => $pMap,
            'majorsByProgram'   => $mMap,
        ]);
    }
}
