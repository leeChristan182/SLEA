<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\StudentAccount;
use App\Models\AssessorAccount;
use App\Models\PendingSubmission;
use App\Models\SubmissionRecord;
use App\Models\SystemMonitoringAndLog;
use App\Models\AdminProfile;
use App\Models\StudentPersonalInformation;
use App\Models\Submission;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{

    public function profile()
    {
        $admin = AdminProfile::where('email_address', auth()->user()->email_address)->first();
        return view('admin.profile', compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        $admin = AdminProfile::where('email_address', auth()->user()->email_address)->first();

        $admin->update($request->only('first_name', 'last_name', 'contact_number', 'position'));

        SystemMonitoringAndLog::create([
            'user_role' => 'Admin',
            'user_name' => $admin->email_address,
            'activity_type' => 'Update Profile',
            'description' => 'Updated admin profile information',
            'created_at' => now(),
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Profile updated successfully!');
    }
    /**
     * Upload and update the admin's profile picture (auto-delete old).
     */
    public function updateAvatar(Request $request)
    {
        $adminAccount = auth()->user();

        $request->validate([
            'avatar' => 'required|image|max:5120',
        ]);

        // Folder specific to admins
        $folderPath = 'avatars/admin';

        // Store new file first
        $path = $request->file('avatar')->store($folderPath, 'public');

        // Delete old picture if it exists
        if ($adminAccount->profile_picture_path && Storage::disk('public')->exists($adminAccount->profile_picture_path)) {
            Storage::disk('public')->delete($adminAccount->profile_picture_path);
        }

        // Update the admin's profile record
        $adminAccount->update(['profile_picture_path' => $path]);

        // Log the activity
        \App\Models\SystemMonitoringAndLog::create([
            'user_role' => 'Admin',
            'user_name' => $adminAccount->email_address,
            'activity_type' => 'Update Profile Picture',
            'description' => 'Admin updated their profile picture.',
        ]);

        return response()->json([
            'success' => true,
            'avatar_url' => asset('storage/' . $path),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $admin = auth()->user();

        // Validate input
        $request->validate([
            'current_password' => 'required',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/', // uppercase
                'regex:/[a-z]/', // lowercase
                'regex:/[0-9]/', // number
                'regex:/[!@#$%^&*(),.?":{}|<>]/', // special
                'confirmed',
            ],
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $admin->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update password
        $admin->password = Hash::make($request->new_password);
        $admin->save();

        // Log the password change
        \App\Models\SystemMonitoringAndLog::create([
            'log_id' => null, // not tied to login
            'user_role' => 'Admin',
            'user_name' => $admin->email_address,
            'activity_type' => 'Change Password',
            'description' => 'Admin updated account password.',
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function createAssessor(Request $request)
    {
        return view('admin.create_assessor');
    }

    public function storeAssessor(Request $request)
    {
        $validated = $request->validate([
            'first_name'    => 'required|string|max:50',
            'last_name'     => 'required|string|max:50',
            'middle_name'   => 'nullable|string|max:50',
            'email_address' => 'required|email|max:50|unique:assessor_accounts,email_address',
            'position'      => 'required|string|max:50',
        ]);

        // ✅ Determine the next default password (e.g., password_1, password_2, ...)
        $latestAssessor = AssessorAccount::orderByDesc('dateacc_created')->first();
        $nextNumber = 1;

        if ($latestAssessor && preg_match('/password_(\d+)/', $latestAssessor->default_password, $matches)) {
            // We cannot get the plain password since it's hashed, so we track using an internal counter instead
            $latestId = AssessorAccount::count(); // simpler & consistent
            $nextNumber = $latestId + 1;
        } else {
            $nextNumber = AssessorAccount::count() + 1;
        }

        $defaultPasswordPlain = 'password_' . $nextNumber;
        $defaultPasswordHashed = Hash::make($defaultPasswordPlain);

        // ✅ Create the new assessor account
        $assessor = AssessorAccount::create([
            'email_address'    => $validated['email_address'],
            'admin_id'         => auth()->user()->admin_id,
            'first_name'       => $validated['first_name'],
            'last_name'        => $validated['last_name'],
            'middle_name'      => $request->input('middle_name'),
            'position'         => $validated['position'],
            'default_password' => $defaultPasswordHashed,
            'dateacc_created'  => now(),
        ]);

        // ✅ Log the creation
        SystemMonitoringAndLog::create([
            'user_role'     => 'Admin',
            'user_name'     => auth()->user()->email_address,
            'activity_type' => 'Create Assessor',
            'description'   => "Created assessor account for {$assessor->email_address}",
            'created_at'    => now(),
        ]);

        // ✅ Return success with the plain password for admin viewing
        return redirect()->back()->with([
            'success' => "Assessor account created successfully!",
            'default_password' => $defaultPasswordPlain,
        ]);
    }


    public function approveReject(Request $request)
    {
        // ✅ Fetch students only
        $query = \App\Models\StudentAccount::query()
            ->with('personalInfo', 'academicInfo');

        // Optional filters
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where('email_address', 'like', "%{$search}%")
                ->orWhereHas('academicInfo', fn($q) => $q->where('student_id', 'like', "%{$search}%"));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'pending');
        }

        $students = $query->paginate(10);

        return view('admin.approve-reject', compact('students'));
    }

    public function submissionOversight(Request $request)
    {
        $query = PendingSubmission::with(['submission.studentPersonalInformation'])
            ->join('submission_records', 'pending_submissions.subrec_id', '=', 'submission_records.subrec_id')
            ->select('pending_submissions.*', 'submission_records.document_title', 'submission_records.student_id', 'submission_records.category');

        // Search functionality
        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('submission_records.document_title', 'like', "%{$searchTerm}%")
                    ->orWhere('submission_records.student_id', 'like', "%{$searchTerm}%");
            });
        }

        // Filter functionality
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'pending':
                    $query->whereNull('assessed_date');
                    break;
                case 'approved':
                    $query->where('action', 'approved');
                    break;
                case 'rejected':
                    $query->where('action', 'rejected');
                    break;
                case 'flagged':
                    $query->where('is_flagged', true);
                    break;
            }
        }

        // Sort functionality
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'title':
                    $query->orderBy('submission_records.document_title');
                    break;
                case 'student':
                    $query->orderBy('submission_records.student_id');
                    break;
                case 'category':
                    $query->orderBy('submission_records.category');
                    break;
                case 'status':
                    $query->orderBy('action');
                    break;
                case 'date':
                    $query->orderBy('pending_submissions.pending_queued_date', 'desc');
                    break;
                default:
                    $query->orderBy('pending_submissions.pending_queued_date', 'desc');
            }
        } else {
            $query->orderBy('pending_submissions.pending_queued_date', 'desc');
        }

        $submissions = $query->paginate(10);

        return view('admin.submission-oversight', compact('submissions'));
    }

    public function finalReview()
    {
        // For now, return the view without data
        // Later you can add logic to fetch students for final review from database
        return view('admin.final-review');
    }

    public function awardReport()
    {
        // For now, return the view without data
        // Later you can add logic to fetch award report data from database
        return view('admin.award-report');
    }

    public function systemMonitoring()
    {
        // Placeholder view for system monitoring and logs
        return view('admin.system-monitoring');
    }

    /**
     * Manage Account – list, filter, sort, search
     */
    public function manageAccount(Request $request)
    {
        // Start query on assessor_accounts
        $q = AssessorAccount::query()->with('admin'); // eager load admin info if needed

        // 🔍 Search
        if ($term = $request->input('q')) {
            $q->where(function ($w) use ($term) {
                $w->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('email_address', 'like', "%{$term}%")
                    ->orWhere('position', 'like', "%{$term}%");
            });
        }

        // 🧩 Filter (status)
        if ($filter = $request->input('filter')) {
            match ($filter) {
                'active'   => $q->where('status', 'active'),
                'inactive' => $q->where('status', 'inactive'),
                'pending'  => $q->where('status', 'pending'),
                default    => null,
            };
        }

        // 📅 Sort options
        if ($sort = $request->input('sort')) {
            match ($sort) {
                'name'       => $q->orderBy('first_name'),
                'email'      => $q->orderBy('email_address'),
                'date'       => $q->orderByDesc('dateacc_created'),
                'position'   => $q->orderBy('position'),
                'status'     => $q->orderBy('status'),
                default      => $q->latest('dateacc_created'),
            };
        } else {
            $q->latest('dateacc_created');
        }

        // 🔢 Pagination (10 per page)
        $assessors = $q->paginate(10);

        // ✅ Pass to Blade
        return view('admin.manage-account', compact('assessors'));
    }

    /**
     * Toggle enable/disable
     */
    public function toggleUser(User $user)
    {
        $user->is_disabled = ! (bool) $user->is_disabled;
        $user->save();

        $message = $user->is_disabled ? 'User disabled successfully.' : 'User enabled successfully.';

        if (request()->ajax()) {
            return response()->json(['message' => $message, 'status' => $user->is_disabled ? 'disabled' : 'active']);
        }

        return back()->with('status', $message);
    }

    /**
     * Delete user
     */
    public function destroyUser(User $user)
    {
        $userName = $user->name ?? $user->email;
        $user->delete();

        $message = "User '{$userName}' deleted successfully.";

        if (request()->ajax()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('status', $message);
    }

    /**
     * Approve a user account
     */
    public function approveUser($student_id)
    {
        $student = \App\Models\StudentAccount::where('student_id', $student_id)->firstOrFail();
        $student->update(['status' => 'approved']);

        \App\Models\SystemMonitoringAndLog::create([
            'user_role' => 'Admin',
            'user_name' => auth()->user()->email_address,
            'activity_type' => 'Approve Account',
            'description' => "Approved student account: {$student->email_address}",
        ]);

        return back()->with('success', "Student {$student->email_address} approved successfully!");
    }

    public function rejectUser($student_id)
    {
        $student = \App\Models\StudentAccount::where('student_id', $student_id)->firstOrFail();
        $student->update(['status' => 'rejected']);

        \App\Models\SystemMonitoringAndLog::create([
            'user_role' => 'Admin',
            'user_name' => auth()->user()->email_address,
            'activity_type' => 'Reject Account',
            'description' => "Rejected student account: {$student->email_address}",
        ]);

        return back()->with('success', "Student {$student->email_address} rejected.");
    }
}
