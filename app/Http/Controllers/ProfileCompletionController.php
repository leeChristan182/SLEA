<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StudentAcademic;
use App\Models\AssessorInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ProfileCompletionController extends Controller
{
    /**
     * Show student profile completion form
     * Now redirects to profile page where modal will be shown
     */
    public function showStudentForm()
    {
        $user = Auth::user();

        // Only students can access this
        if (!$user->isStudent() || $user->profile_completed) {
            return redirect()->route('student.profile');
        }

        // Redirect to profile page - modal will be shown there
        return redirect()->route('student.profile')
            ->with('show_profile_modal', true);
    }

    /**
     * Store student profile completion
     */
    public function storeStudentProfile(Request $request)
    {
        $user = Auth::user();

        if (!$user->isStudent() || $user->profile_completed) {
            return redirect()->route('student.profile');
        }

        $rules = [
            'student_id'    => [
                'required',
                'string',
                'max:30',
                'regex:/^\d{4}-\d{5}$/',
                Rule::unique('student_academic', 'student_number')->ignore($user->id, 'user_id'),
            ],
            'college_id'    => ['required', 'integer', 'exists:colleges,id'],
            'program_id'    => ['required', 'integer', 'exists:programs,id'],
            'major_id'      => ['nullable', 'integer', 'exists:majors,id'],
            'year_level'    => ['required', 'in:1,2,3,4,5,6,7,8'],
            'leadership_type_id' => ['required', 'integer', 'exists:leadership_types,id'],
            'position_id'        => ['required', 'integer', 'exists:positions,id'],
            'term'               => ['required', 'string', 'max:25'],
            'issued_by'          => ['required', 'string', 'max:150'],
            'leadership_status'  => ['required', 'in:Active,Inactive'],
        ];

        $validated = $request->validate($rules);

        // Compute expected graduation year
        $expectedGradYear = $this->computeExpectedGradYear(
            $validated['student_id'],
            (int) $validated['year_level']
        );
        $eligibility = (now()->year > $expectedGradYear) ? 'needs_revalidation' : 'eligible';

        DB::beginTransaction();
        try {
            // Create/update student academic
            $user->studentAcademic()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'student_number'     => $validated['student_id'],
                    'college_id'         => $validated['college_id'],
                    'program_id'         => $validated['program_id'],
                    'major_id'           => $validated['major_id'] ?? null,
                    'year_level'         => $validated['year_level'],
                    'expected_grad_year' => $expectedGradYear,
                    'eligibility_status' => $eligibility,
                ]
            );

            // Handle leadership info
            $needsOrg = $this->leadershipRequiresOrg((int) $validated['leadership_type_id']);
            $isCCO = DB::table('leadership_types')
                ->where('id', (int) $validated['leadership_type_id'])
                ->where('key', 'cco')
                ->exists();

            if ($isCCO) {
                $clusterId = null;
                $orgId = null;
            } elseif ($needsOrg) {
                $request->validate([
                    'cluster_id'      => ['required', 'integer', 'exists:clusters,id'],
                    'organization_id' => ['required', 'integer', 'exists:organizations,id'],
                ]);
                $clusterId = (int) $request->input('cluster_id');
                $orgId = (int) $request->input('organization_id');
            } else {
                $clusterId = null;
                $orgId = null;
            }

            // Create leadership record
            if (Schema::hasTable('student_leaderships')) {
                DB::table('student_leaderships')->insert([
                    'user_id'            => $user->id,
                    'leadership_type_id' => (int) $validated['leadership_type_id'],
                    'cluster_id'         => $clusterId,
                    'organization_id'    => $orgId,
                    'position_id'        => (int) $validated['position_id'],
                    'term'               => $validated['term'],
                    'issued_by'          => $validated['issued_by'],
                    'leadership_status'  => $validated['leadership_status'],
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            // Mark profile as completed
            $user->profile_completed = true;
            $user->save();

            DB::commit();

            return redirect()->route('student.profile')
                ->with('status', 'Profile completed successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()
                ->withErrors(['error' => 'Could not complete profile. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Show assessor profile completion form
     */
    public function showAssessorForm()
    {
        $user = Auth::user();

        // Only assessors can access this
        if (!$user->isAssessor() || $user->profile_completed) {
            return redirect()->route('assessor.profile');
        }

        return view('profile.complete-assessor');
    }

    /**
     * Store assessor profile completion
     */
    public function storeAssessorProfile(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAssessor() || $user->profile_completed) {
            return redirect()->route('assessor.profile');
        }

        $rules = [
            'office_unit'   => ['required', 'string', 'max:150'],
            'position'      => ['required', 'string', 'max:100'],
            'assessor_code' => ['nullable', 'string', 'max:50'],
        ];

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            // Create/update assessor info
            $user->assessorInfo()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'office_unit'   => $validated['office_unit'],
                    'position'      => $validated['position'],
                    'assessor_code' => $validated['assessor_code'] ?? null,
                    'date_created'  => now(),
                ]
            );

            // Mark profile as completed
            $user->profile_completed = true;
            $user->save();

            DB::commit();

            return redirect()->route('assessor.profile')
                ->with('status', 'Profile completed successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()
                ->withErrors(['error' => 'Could not complete profile. Please try again.'])
                ->withInput();
        }
    }

    // Helper methods (copied from AuthController)
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
        return collect();
    }

    private function getLeadershipTypesList()
    {
        if (!Schema::hasTable('leadership_types')) return collect();

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
}
