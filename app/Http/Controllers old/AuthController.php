<?php

namespace App\Http\Controllers;

use App\Services\LoginMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\AdminProfile;
use App\Models\AssessorAccount;
use App\Models\StudentPersonalInformation;

class AuthController extends Controller
{
    protected $loginMonitoringService;

    public function __construct(LoginMonitoringService $loginMonitoringService)
    {
        $this->loginMonitoringService = $loginMonitoringService;
    }

    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login attempt
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'user_type' => 'required|in:admin,assessor,student',
        ]);

        $email = $request->email;
        $password = $request->password;
        $userType = $request->user_type;

        // Record login attempt
        $this->loginMonitoringService->recordLoginAttempt($email, $userType, $request);

        // Authenticate based on user type
        $user = $this->authenticateUser($email, $password, $userType);

        if ($user) {
            // Record successful login
            $this->loginMonitoringService->recordSuccessfulLogin($email, $userType, $request);
            
            // Set session data
            session([
                'user_id' => $user->id ?? $user->admin_id ?? $user->student_id,
                'user_email' => $email,
                'user_type' => $userType,
                'user_name' => $user->name ?? $user->first_name . ' ' . $user->last_name,
                'login_success' => true,
            ]);

            return redirect()->intended('/dashboard')->with('success', 'Login successful!');
        } else {
            // Record failed login
            $this->loginMonitoringService->recordFailedLogin($email, $userType, $request);
            
            session(['login_failed' => true]);
            
            return back()->withErrors([
                'email' => 'Invalid credentials.',
            ])->withInput($request->only('email', 'user_type'));
        }
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        $email = session('user_email');
        $userType = session('user_type');
        
        // Record logout
        if ($email) {
            $this->loginMonitoringService->recordLoginAttempt($email, $userType, $request);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Logged out successfully!');
    }

    /**
     * Authenticate user based on type
     */
    private function authenticateUser(string $email, string $password, string $userType)
    {
        switch ($userType) {
            case 'admin':
                return $this->authenticateAdmin($email, $password);
            case 'assessor':
                return $this->authenticateAssessor($email, $password);
            case 'student':
                return $this->authenticateStudent($email, $password);
            default:
                return null;
        }
    }

    /**
     * Authenticate admin user
     */
    private function authenticateAdmin(string $email, string $password)
    {
        $admin = AdminProfile::where('email_address', $email)->first();
        
        if ($admin) {
            // Check password in admin_passwords table
            $adminPassword = \App\Models\AdminPassword::where('admin_id', $admin->admin_id)->first();
            
            if ($adminPassword && Hash::check($password, $adminPassword->password_hashed)) {
                return $admin;
            }
        }
        
        return null;
    }

    /**
     * Authenticate assessor user
     */
    private function authenticateAssessor(string $email, string $password)
    {
        $assessor = AssessorAccount::where('email_address', $email)->first();
        
        if ($assessor && Hash::check($password, $assessor->default_password)) {
            return $assessor;
        }
        
        return null;
    }

    /**
     * Authenticate student user
     */
    private function authenticateStudent(string $email, string $password)
    {
        $student = StudentPersonalInformation::where('email_address', $email)->first();
        
        if ($student) {
            // Check password in student_passwords table
            $studentPassword = \App\Models\StudentPassword::where('student_id', $student->student_id)->first();
            
            if ($studentPassword && Hash::check($password, $studentPassword->password_hashed)) {
                return $student;
            }
        }
        
        return null;
    }

    /**
     * Show dashboard based on user type
     */
    public function dashboard()
    {
        $userType = session('user_type');
        $userEmail = session('user_email');
        
        // Get user-specific data
        $data = [
            'user_type' => $userType,
            'user_email' => $userEmail,
            'user_name' => session('user_name'),
        ];

        // Add login monitoring data for admins
        if ($userType === 'admin') {
            $data['login_stats'] = $this->loginMonitoringService->getLoginStatistics();
        }

        return view('dashboard', $data);
    }
}
