<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AssessorController extends Controller
{
    /* =========================
     | PROFILE
     * ========================= */

    // GET /assessor/profile
    public function profile()
    {
        $user = Auth::user();
        return view('assessor.profile', compact('user'));
    }

    // PUT /assessor/profile
    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name'  => ['required', 'string', 'max:50'],
            'middle_name' => ['nullable', 'string', 'max:50'],
            'email'      => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'contact'    => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($data);

        return back()->with('status', 'Profile updated.');
    }

    // PATCH /assessor/password
    public function updatePassword(Request $request)
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

        // Optional: write to password_changes table if it exists
        if (Schema::hasTable('password_changes')) {
            DB::table('password_changes')->insert([
                'user_id'                => $user->id,
                'previous_password_hash' => $user->password,
                'changed_at'             => now(),
                'changed_by'             => 'self',
                'ip'                     => $request->ip(),
                'user_agent'             => substr((string)$request->userAgent(), 0, 255),
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }

        $user->password = $request->password; // model mutator will hash
        $user->save();

        return back()->with('status', 'Password updated.');
    }

    // POST /assessor/profile/picture
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

    /* =========================
     | SUBMISSIONS & REVIEW
     * ========================= */

    // GET /assessor/pending-submissions
    public function pendingSubmissions(Request $request)
    {
        // If you have models, prefer Eloquent; using DB for portability here.
        if (! Schema::hasTable('submissions')) {
            return view('assessor.submissions.index', ['submissions' => collect(), 'empty' => true]);
        }

        $query = DB::table('submissions as s')
            ->select('s.id', 's.user_id', 's.title', 's.created_at', 's.status')
            ->whereIn('s.status', ['pending', 'under_review'])
            ->orderByDesc('s.created_at');

        // Optional: show only those assigned to this assessor if you have an assignment table
        if (Schema::hasTable('submission_reviews')) {
            $query->leftJoin('submission_reviews as r', 'r.submission_id', '=', 's.id')
                ->where(function ($q) {
                    $q->whereNull('r.assessor_id')
                        ->orWhere('r.assessor_id', Auth::id());
                });
        }

        $submissions = $query->paginate(20)->withQueryString();

        return view('assessor.submissions.index', compact('submissions'));
    }

    // GET /assessor/submissions
    public function submissions()
    {
        if (! Schema::hasTable('submission_reviews')) {
            // Fall back to all submissions if no reviews table yet
            $subs = Schema::hasTable('submissions')
                ? DB::table('submissions')->orderByDesc('created_at')->paginate(20)
                : collect();
            return view('assessor.submissions.mine', ['submissions' => $subs]);
        }

        $submissions = DB::table('submission_reviews as r')
            ->join('submissions as s', 's.id', '=', 'r.submission_id')
            ->select('s.id', 's.title', 's.status', 's.created_at', 'r.score', 'r.updated_at as reviewed_at')
            ->where('r.assessor_id', Auth::id())
            ->orderByDesc('r.updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('assessor.submissions.mine', compact('submissions'));
    }

    // GET /assessor/final-review
    public function finalReview()
    {
        // Show a high-level list; actual finalization is admin
        if (! Schema::hasTable('final_reviews')) {
            return view('assessor.final-review.index', ['rows' => collect()]);
        }

        $rows = DB::table('final_reviews as f')
            ->join('submissions as s', 's.id', '=', 'f.submission_id')
            ->select('s.id', 's.title', 'f.status', 'f.total_score', 'f.updated_at')
            ->orderByDesc('f.updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('assessor.final-review.index', compact('rows'));
    }

    // GET /assessor/submissions/{id}/details
    public function getSubmissionDetails(int $id)
    {
        if (! Schema::hasTable('submissions')) {
            abort(404);
        }

        $submission = DB::table('submissions as s')
            ->leftJoin('users as u', 'u.id', '=', 's.user_id')
            ->select('s.*', 'u.first_name', 'u.last_name', 'u.email')
            ->where('s.id', $id)
            ->first();

        if (! $submission) {
            abort(404);
        }

        $documents = Schema::hasTable('user_documents')
            ? DB::table('user_documents')->where('user_id', $submission->user_id)->get()
            : collect();

        $academic = Schema::hasTable('student_academic')
            ? DB::table('student_academic')->where('user_id', $submission->user_id)->first()
            : null;

        return response()->json([
            'submission' => $submission,
            'documents'  => $documents,
            'academic'   => $academic,
        ]);
    }

    // POST /assessor/submissions/{id}/action
    public function handleSubmissionAction(Request $request, int $id)
    {
        $request->validate([
            'action' => ['required', Rule::in(['approve', 'reject', 'resubmit', 'flagged', 'under_review'])],
            'score'  => ['nullable', 'numeric', 'min:0'],
            'notes'  => ['nullable', 'string', 'max:2000'],
        ]);

        if (! Schema::hasTable('submissions')) {
            return back()->withErrors(['action' => 'Submissions table not found.']);
        }

        $submission = DB::table('submissions')->where('id', $id)->first();
        if (! $submission) {
            return back()->withErrors(['action' => 'Submission not found.']);
        }

        // Write a review row if table exists
        if (Schema::hasTable('submission_reviews')) {
            DB::table('submission_reviews')->updateOrInsert(
                ['submission_id' => $id, 'assessor_id' => Auth::id()],
                [
                    'score'      => $request->input('score'),
                    'notes'      => $request->input('notes'),
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, CURRENT_TIMESTAMP)'),
                ]
            );
        }

        // Update submission status based on action (map to your code table values)
        $statusMap = [
            'approve'      => 'qualified',
            'reject'       => 'unqualified',
            'resubmit'     => 'resubmit',
            'flagged'      => 'flagged',
            'under_review' => 'under_review',
        ];

        DB::table('submissions')->where('id', $id)->update([
            'status'     => $statusMap[$request->action] ?? 'under_review',
            'updated_at' => now(),
        ]);

        return back()->with('status', 'Submission updated.');
    }

    // GET /assessor/documents/{id}/download
    public function downloadDocument(int $id)
    {
        if (! Schema::hasTable('user_documents')) {
            abort(404);
        }

        $doc = DB::table('user_documents')->where('id', $id)->first();
        if (! $doc || ! $doc->path) {
            abort(404);
        }

        if (! Storage::disk('public')->exists($doc->path)) {
            abort(404);
        }

        return Storage::disk('public')->download($doc->path, basename($doc->path));
    }
}
