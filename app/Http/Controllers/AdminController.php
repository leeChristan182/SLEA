<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AssessorAccount;
use App\Models\PendingSubmission;
use App\Models\SubmissionRecord;
use App\Models\SystemMonitoringAndLog;
use App\Models\AdminProfile;
use App\Models\StudentPersonalInformation;
use App\Models\Submission;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $studentCount   = StudentPersonalInformation::count();
        $assessorCount  = AssessorAccount::count();
        $adminCount     = AdminProfile::count(); // fixed reference
        $submissionCount = Submission::count();
        $logs           = SystemMonitoringAndLog::latest()->take(10)->get();
        $recentAssessors = AssessorAccount::latest()->take(5)->get();
        $recentLogs = SystemMonitoringAndLog::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'studentCount',
            'assessorCount',
            'adminCount',
            'submissionCount',
            'logs'
        ));
    }

    public function profile()
    {
        $admin = AdminProfile::where('email_address', auth()->user()->email_address)->first();
        return view('admin.profile', compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        $admin = AdminProfile::where('email_address', auth()->user()->email_address)->first();

        $admin->update($request->only(
            'first_name',
            'last_name',
            'contact_number',
            'position'
        ));

        SystemMonitoringAndLog::create([
            'user_role' => 'Admin',
            'user_name' => $admin->email_address,
            'activity_type' => 'Update Profile',
            'description' => 'Updated admin profile information',
            'created_at' => now(),
        ]);

        return back()->with('success', 'Profile updated successfully!');
    }
    public function updateAvatar(Request $request)
    {
        $request->validate(['avatar' => 'required|image|max:5120']);

        $admin = AdminProfile::where('email_address', auth()->user()->email_address)->first();

        if (!$admin) {
            return back()->withErrors(['error' => 'Admin profile not found.']);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $admin->update(['profile_picture' => $path]);

        SystemMonitoringAndLog::record(
            'Admin',
            $admin->email_address,
            'Update Profile Picture',
            'Admin changed their profile picture'
        );

        return back()->with('success', 'Profile picture updated successfully!');
    }


    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        // Get current admin profile
        $admin = AdminProfile::where('email_address', auth()->user()->email_address)->first();

        if (!$admin) {
            return back()->withErrors(['profile' => 'Admin profile not found.']);
        }

        // Get or create the admin_password record
        $adminPassword = \App\Models\AdminPassword::firstOrNew(['admin_id' => $admin->admin_id]);

        // If the record already exists, verify the current password
        if ($adminPassword->exists) {
            if (!Hash::check($request->current_password, $adminPassword->password_hashed)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
        }

        // Update password and date
        $adminPassword->password_hashed = Hash::make($request->new_password);
        $adminPassword->date_pass_created = now();
        $adminPassword->save();

        // Log activity
        SystemMonitoringAndLog::create([
            'user_role' => 'Admin',
            'user_name' => $admin->email_address,
            'activity_type' => 'Change Password',
            'description' => 'Admin updated account password',
            'created_at' => now(),
        ]);

        return back()->with('success', 'Password updated successfully!');
    }

    public function createAssessor(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name'  => 'required|string|max:50',
            'email_address' => 'required|email|unique:assessor_accounts,email_address',
            'position' => 'required|string|max:50',
        ]);

        $assessor = AssessorAccount::create([
            'email_address'     => $validated['email_address'],
            'admin_id'          => auth()->user()->admin_id,
            'first_name'        => $validated['first_name'],
            'last_name'         => $validated['last_name'],
            'middle_name'       => $request->input('middle_name'),
            'position'          => $validated['position'],
            'default_password'  => bcrypt('password123'),
            'dateacc_created'   => now(),
        ]);

        SystemMonitoringAndLog::create([
            'user_role'    => 'Admin',
            'user_name'    => auth()->user()->email_address,
            'activity_type' => 'Create Assessor',
            'description'  => 'Created assessor account for ' . $assessor->email_address,
            'created_at'   => now(),
        ]);

        return redirect()->back()->with('success', 'Assessor created successfully!');
    }

    public function approveReject(Request $request)
    {
        $query = User::with(['studentPersonalInformation', 'leadershipInformation'])
            ->where('user_role', 'student')
            ->where('is_approved', false);

        // Search functionality
        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%")
                    ->orWhere('student_id', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by role
        if ($request->filled('filter')) {
            $query->where('user_role', $request->filter);
        }

        // Sort functionality
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'name':
                    $query->orderBy('name');
                    break;
                case 'date':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'status':
                    $query->orderBy('is_approved');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $users = $query->paginate(10);

        return view('admin.approve-reject', compact('users'));
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
    public function approveUser(User $user)
    {
        $user->is_approved = true;
        $user->save();

        // Log the activity
        SystemMonitoringAndLog::create([
            'log_id' => null,
            'user_role' => 'admin',
            'user_name' => auth()->check() ? auth()->user()->name : 'Admin',
            'activity_type' => 'account_approval',
            'description' => "Approved account for user: {$user->name} ({$user->email})",
        ]);

        $message = "User '{$user->name}' has been approved successfully.";

        if (request()->ajax()) {
            return response()->json(['message' => $message, 'status' => 'approved']);
        }

        return back()->with('status', $message);
    }

    /**
     * Reject a user account
     */
    public function rejectUser(User $user)
    {
        $userName = $user->name ?? $user->email;
        $user->delete();

        // Log the activity
        SystemMonitoringAndLog::create([
            'log_id' => null,
            'user_role' => 'admin',
            'user_name' => auth()->check() ? auth()->user()->name : 'Admin',
            'activity_type' => 'account_rejection',
            'description' => "Rejected and deleted account for user: {$userName}",
        ]);

        $message = "User '{$userName}' has been rejected and removed from the system.";

        if (request()->ajax()) {
            return response()->json(['message' => $message, 'status' => 'rejected']);
        }

        return back()->with('status', $message);
    }
}
class AdminAccount extends Authenticatable
{
    use Notifiable;

    protected $primaryKey = 'admin_id';
    public $timestamps = true;

    protected $fillable = [
        'email_address',
        'password',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'password',
    ];

    // Relationship
    public function profile()
    {
        return $this->hasOne(AdminProfile::class, 'admin_id');
    }
}
