<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    /* =========================
     | PROFILE & DASHBOARD
     * ========================= */

    // GET /student/profile
    public function profile()
    {
        /** @var User $user */
        $user = Auth::user();

        $academic = Schema::hasTable('student_academic')
            ? DB::table('student_academic')->where('user_id', $user->id)->first()
            : null;

        return view('student.profile', compact('user', 'academic'));
    }

    // POST /student/update-avatar
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        $path = $request->file('avatar')->store('avatars', 'public');

        if ($user->profile_picture_path && Storage::disk('public')->exists($user->profile_picture_path)) {
            Storage::disk('public')->delete($user->profile_picture_path);
        }

        $user->update(['profile_picture_path' => $path]);

        return back()->with('status', 'Profile picture updated.');
    }

    // POST /student/change-password
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Your current password is incorrect.']);
        }

        $user->password = $request->password; // model mutator will hash
        $user->save();

        return back()->with('status', 'Password updated.');
    }

    /* =========================
     | ACADEMIC INFO & LEADERSHIP
     * ========================= */

    // POST /student/update-academic
    public function updateAcademicInfo(Request $request)
    {
        $data = $request->validate([
            'student_number' => ['nullable', 'string', 'max:30'],
            'year_level'     => ['nullable', 'string', 'max:20'],
            'college_id'     => ['nullable', 'integer'],
            'program_id'     => ['nullable', 'integer'],
            'major_id'       => ['nullable', 'integer'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! Schema::hasTable('student_academic')) {
            return back()->withErrors(['student_number' => 'Academic table not found.']);
        }

        // Current row (if any)
        $current = DB::table('student_academic')->where('user_id', $user->id)->first();

        // --- Compute expected graduation year ---
        // Rule: take the first 4 digits of student_number as entry year, add 4.
        $expectedGradYear = null;
        $numberForCalc = $data['student_number'] ?? ($current->student_number ?? null);
        if (is_string($numberForCalc) && preg_match('/^\s*(\d{4})/', $numberForCalc, $m)) {
            $entry = (int) $m[1];
            if ($entry > 1900 && $entry < 3000) {
                $expectedGradYear = $entry + 4; // adjust to +5 if your school uses 5-year tracks
            }
        }

        // Determine if program/major changed (which forces revalidation)
        $programChanged = isset($data['program_id']) && $current && (int)$current->program_id !== (int)$data['program_id'];
        $majorChanged   = isset($data['major_id'])   && $current && (int)$current->major_id   !== (int)$data['major_id'];

        // Exceeded expected year?
        $nowYear = (int) now()->year;
        $exceeded = $expectedGradYear ? ($nowYear > $expectedGradYear) : false;

        // Decide new eligibility_status
        // - If exceeded OR program/major changed → under_review
        // - Else keep whatever is stored (default to eligible)
        $oldEligibility = $current->eligibility_status ?? 'eligible';
        $newEligibility = ($exceeded || $programChanged || $majorChanged) ? 'under_review' : $oldEligibility;

        // Build payload
        $payload = array_merge($data, [
            'user_id'             => $user->id,
            'expected_grad_year'  => $expectedGradYear ?? ($current->expected_grad_year ?? null),
            'eligibility_status'  => $newEligibility,
            'updated_at'          => now(),
        ]);

        // Upsert
        if ($current) {
            DB::table('student_academic')->where('user_id', $user->id)->update($payload);
        } else {
            $payload['created_at'] = now();
            DB::table('student_academic')->insert($payload);
        }

        // Messaging hint for UX
        $msg = $newEligibility === 'under_review'
            ? 'Academic information saved. Your eligibility is now under review.'
            : 'Academic information saved.';

        return back()->with('status', $msg);
    }

    // POST /student/update-leadership
    public function updateLeadership(Request $request)
    {
        // Expect an array of leadership entries (id for update, else create)
        $request->validate([
            'leadership'               => ['required', 'array'],
            'leadership.*.id'          => ['nullable', 'integer'],
            'leadership.*.org_id'      => ['required', 'integer'],
            'leadership.*.position_id' => ['required', 'integer'],
            'leadership.*.scope'       => ['nullable', 'string', 'max:32'], // must match scope_levels.key if enforced
            'leadership.*.from'        => ['nullable', 'date'],
            'leadership.*.to'          => ['nullable', 'date', 'after_or_equal:leadership.*.from'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (! Schema::hasTable('student_leaderships')) {
            return back()->withErrors(['leadership' => 'Leadership table not found.']);
        }

        foreach ($request->input('leadership') as $row) {
            $base = [
                'user_id'     => $user->id,
                'org_id'      => $row['org_id'],
                'position_id' => $row['position_id'],
                'scope'       => $row['scope'] ?? null,
                'from'        => $row['from'] ?? null,
                'to'          => $row['to'] ?? null,
                'status'      => 'pending', // default; admin/assessor can approve later
                'updated_at'  => now(),
            ];

            if (!empty($row['id'])) {
                DB::table('student_leaderships')->where('id', $row['id'])->where('user_id', $user->id)->update($base);
            } else {
                $base['created_at'] = now();
                DB::table('student_leaderships')->insert($base);
            }
        }

        return back()->with('status', 'Leadership records saved.');
    }
    // GET /student/revalidation
    public function revalidation()
    {
        $user = auth()->user();
        $academic = Schema::hasTable('student_academic')
            ? DB::table('student_academic')->where('user_id', $user->id)->first()
            : null;

        return view('student.revalidation', compact('user', 'academic')); // simple page w/ 3 forms
    }

    // POST /student/upload-cor
    public function uploadCOR(Request $request)
    {
        $request->validate([
            'cor' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:6144'], // 6 MB
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (! Schema::hasTable('student_academic')) {
            return back()->withErrors(['cor' => 'Academic table not found.']);
        }

        $path = $request->file('cor')->store('cor', 'public');

        // Update academic row
        $now  = now();
        $data = [
            'user_id'                        => $user->id,
            'certificate_of_registration_path' => $path,
            'updated_at'                     => $now,
        ];

        $exists = DB::table('student_academic')->where('user_id', $user->id)->first();
        if ($exists) {
            DB::table('student_academic')->where('user_id', $user->id)->update($data);
        } else {
            $data['created_at'] = $now;
            DB::table('student_academic')->insert($data);
        }

        // Optional: log to user_documents
        if (Schema::hasTable('user_documents')) {
            DB::table('user_documents')->insert([
                'user_id'    => $user->id,
                'type'       => 'cor',
                'path'       => $path,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        // Everyone without a status -> eligible
        DB::table('student_academic')->whereNull('eligibility_status')->update([
            'eligibility_status' => 'eligible',
        ]);

        // Anyone past expected_grad_year -> needs_revalidation
        DB::table('student_academic')
            ->whereNotNull('expected_grad_year')
            ->where('expected_grad_year', '<', now()->year)
            ->update(['eligibility_status' => 'needs_revalidation']);

        return back()->with('status', 'Certificate of Registration uploaded.');
    }

    /* =========================
     | VIEWS: PERFORMANCE / CRITERIA / HISTORY
     * ========================= */

    // GET /student/performance
    public function performance()
    {
        // keep simple; you can replace with real aggregates later
        $metrics = [
            'submissions' => Schema::hasTable('submissions')
                ? DB::table('submissions')->where('user_id', Auth::id())->count()
                : 0,
            'approved' => Schema::hasTable('submissions')
                ? DB::table('submissions')->where('user_id', Auth::id())->where('status', 'approved')->count()
                : 0,
        ];

        return view('student.performance', compact('metrics'));
    }

    // GET /student/criteria
    public function criteria()
    {
        // show rubric sections/subsections if available
        $sections = Schema::hasTable('rubric_sections')
            ? DB::table('rubric_sections')->orderBy('order')->get()
            : collect();

        return view('student.criteria', compact('sections'));
    }

    // GET /student/history
    public function history()
    {
        $history = Schema::hasTable('submission_history')
            ? DB::table('submission_history')->where('user_id', Auth::id())->orderByDesc('created_at')->paginate(20)
            : collect();

        return view('student.history', compact('history'));
    }
}
