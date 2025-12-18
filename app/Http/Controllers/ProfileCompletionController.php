<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StudentAcademic;
use App\Models\StudentLeadership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;

class ProfileCompletionController extends Controller
{
    /**
     * Show student profile completion form
     */
    public function showStudentForm()
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->isStudent()) {
            abort(403);
        }

        // If already submitted, bounce to profile (popup handled there)
        if ($user->profile_completed) {
            return redirect()
                ->route('student.profile')
                ->with('show_waiting_modal', true);
        }

        return view('student.complete-requirements', [
            'user'            => $user,
            'colleges'        => $this->getCollegesList(),
            'leadershipTypes' => $this->getLeadershipTypesList(),
        ]);
    }

    /**
     * Store student profile completion
     */
    public function storeStudentProfile(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->isStudent() || $user->profile_completed) {
            return redirect()
                ->route('student.profile')
                ->with('show_waiting_modal', true);
        }

        $existingCor = $user->studentAcademic?->certificate_of_registration_path;

        $rules = [
            'student_id' => [
                'required',
                'string',
                'max:30',
                'regex:/^\d{4}-\d{5}$/',
                Rule::unique('student_academic', 'student_number')
                    ->ignore($user->id, 'user_id'),
            ],
            'college_id' => ['required', 'integer', 'exists:colleges,id'],
            'program_id' => ['required', 'integer', 'exists:programs,id'],
            'major_id'   => ['nullable', 'integer', 'exists:majors,id'],
            'year_level' => ['required', 'in:1,2,3,4,5,6,7,8'],

            'cor' => [
                $existingCor ? 'nullable' : 'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],

            'leadership_type_id' => ['required', 'integer', 'exists:leadership_types,id'],
            'position_id'        => ['required', 'integer', 'exists:positions,id'],
            'term'               => ['required', 'string', 'max:25'],
            'issued_by'          => ['required', 'string', 'max:150'],
            'leadership_status'  => ['required', 'in:Active,Inactive'],
        ];

        $validated = $request->validate($rules);

        $expectedGradYear = $this->computeExpectedGradYear(
            $validated['student_id'],
            (int) $validated['year_level']
        );

        DB::beginTransaction();

        try {
            /* =======================
             * COR UPLOAD (public disk)
             * ======================= */
            $corPath = $existingCor;

            if ($request->hasFile('cor')) {
                if ($existingCor) {
                    Storage::disk('public')->delete($existingCor);
                }
                $corPath = $request->file('cor')->store('student_cors', 'public');
            }

            /* =======================
             * ACADEMIC INFO
             * ======================= */
            $user->studentAcademic()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'student_number' => $validated['student_id'],
                    'college_id'     => $validated['college_id'],
                    'program_id'     => $validated['program_id'],
                    'major_id'       => $validated['major_id'] ?? null,
                    'year_level'     => $validated['year_level'],
                    'expected_grad_year' => $expectedGradYear,

                    // ✅ keep this for initial submission (NOT revalidation)
                    'eligibility_status' => 'under_review',

                    'certificate_of_registration_path' => $corPath,
                ]
            );

            /* =======================
             * LEADERSHIP (requires_org logic)
             * ======================= */
            $type = DB::table('leadership_types')
                ->select('requires_org')
                ->where('id', (int) $validated['leadership_type_id'])
                ->first();

            $requiresOrg = $type && (int) $type->requires_org === 1;

            $clusterId = null;
            $orgId     = null;

            if ($requiresOrg) {
                $request->validate([
                    'cluster_id'      => ['required', 'integer', 'exists:clusters,id'],
                    'organization_id' => ['required', 'integer', 'exists:organizations,id'],
                ]);

                $clusterId = (int) $request->cluster_id;
                $orgId     = (int) $request->organization_id;
            }

            // Conflict check (type+position+term [+org if required])
            $conflictQuery = StudentLeadership::query()
                ->where('leadership_type_id', (int) $validated['leadership_type_id'])
                ->where('position_id', (int) $validated['position_id'])
                ->where('term', $validated['term']);

            if ($requiresOrg) {
                $conflictQuery->where('organization_id', $orgId);
            }

            if ($conflictQuery->exists()) {
                DB::rollBack();
                return back()
                    ->withErrors([
                        'position_id' => 'This leadership position has already been recorded for the selected school year.',
                    ])
                    ->withInput();
            }

            StudentLeadership::create([
                'user_id'            => $user->id,
                'leadership_type_id' => (int) $validated['leadership_type_id'],
                'cluster_id'         => $clusterId,
                'organization_id'    => $orgId,
                'position_id'        => (int) $validated['position_id'],
                'term'               => $validated['term'],
                'issued_by'          => $validated['issued_by'],
                'leadership_status'  => $validated['leadership_status'],
            ]);

            /* =======================
             * MARK PROFILE COMPLETED (submitted)
             * ======================= */
            $user->profile_completed = false; // ✅ do NOT mark completed here
            $user->is_account_limited = true;
            $user->save();

            DB::commit();

            return redirect()
                ->route('student.profile')
                // Show a one-time success confirmation popup on the profile page
                ->with('requirements_submitted', true)
                ->with('status', 'Your information has been submitted and is under review.');
        } catch (QueryException $e) {
            DB::rollBack();

            if ($e->getCode() === '23000') {
                return back()
                    ->withErrors([
                        'position_id' => 'This leadership position has already been recorded for the selected school year.',
                    ])
                    ->withInput();
            }

            throw $e;
        }
    }

    /**
     * Show assessor profile completion
     */
    public function showAssessorForm()
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->isAssessor()) {
            return redirect()
                ->route('assessor.profile')
                ->with('show_waiting_modal', true);
        }

        // Check if assessor has already submitted requirements
        $assessorSubmitted =
            $user->assessorInfo
            && ! empty($user->assessorInfo->office_unit)
            && ! empty($user->assessorInfo->position);

        // If already submitted, redirect to profile with waiting modal
        if ($assessorSubmitted) {
            return redirect()
                ->route('assessor.profile')
                ->with('show_waiting_modal', true);
        }

        return view('assessor.complete-requirements', compact('user'));
    }

    /**
     * Store assessor profile completion
     */
    public function storeAssessorProfile(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->isAssessor()) {
            return redirect()
                ->route('assessor.profile')
                ->with('show_waiting_modal', true);
        }

        // Check if assessor has already submitted requirements
        $assessorSubmitted =
            $user->assessorInfo
            && ! empty($user->assessorInfo->office_unit)
            && ! empty($user->assessorInfo->position);

        // If already submitted, redirect to profile with waiting modal
        if ($assessorSubmitted) {
            return redirect()
                ->route('assessor.profile')
                ->with('show_waiting_modal', true);
        }

        $validated = $request->validate([
            'office_unit'   => ['required', 'string', 'max:150'],
            'position'      => ['required', 'string', 'max:100'],
            'assessor_code' => ['nullable', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($user, $validated) {
            $user->assessorInfo()->updateOrCreate(
                ['user_id' => $user->id],
                $validated
            );

            $user->profile_completed = false;  // ✅ stay incomplete until admin validates
            $user->is_account_limited = true;  // ✅ optional but recommended to unify flow
            $user->save();
        });

        return redirect()
            ->route('assessor.profile')
            ->with('requirements_submitted', true)
            ->with('status', 'Your information has been submitted and is under review.')
            ->with('show_waiting_modal', true);
    }

    /* ================= HELPERS ================= */

    private function getCollegesList()
    {
        if (! Schema::hasTable('colleges')) return collect();

        $cols = Schema::getColumnListing('colleges');
        $nameCol = in_array('college_name', $cols) ? 'college_name' : 'name';

        return DB::table('colleges')
            ->select('id', DB::raw("$nameCol as college_name"))
            ->orderBy($nameCol)
            ->get();
    }

    private function getLeadershipTypesList()
    {
        return Schema::hasTable('leadership_types')
            ? DB::table('leadership_types')
            ->select('id', 'name', 'key', 'requires_org')
            ->orderByRaw("FIELD(`key`,
                    'usg','osc','lc','cco','sco','lgu','lcm','eap')")
            ->get()
            : collect();
    }

    private function computeExpectedGradYear(string $studentId, int $yearLevel): int
    {
        if (preg_match('/^(\d{4})/', $studentId, $m)) {
            return ((int) $m[1]) + 4;
        }

        return now()->year + (4 - max(1, $yearLevel));
    }
}
