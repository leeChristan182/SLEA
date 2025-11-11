<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show the login page (shared by all roles)
     */
    public function show()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.profile');
        }

        if (Auth::guard('assessor')->check()) {
            return redirect()->route('assessor.profile');
        }

        if (Auth::guard('student')->check()) {
            return redirect()->route('student.profile');
        }

        // Default: show login page
        return view('login');
    }

    /**
     * Handle authentication for Admin, Assessor, and Student
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $guards = [
            'admin' => 'admin.profile',
            'assessor' => 'assessor.profile',
            'student' => 'student.profile',
        ];

        foreach ($guards as $guard => $redirectRoute) {
            if (Auth::guard($guard)->attempt(['email_address' => $credentials['email'], 'password' => $credentials['password']])) {
                $user = Auth::guard($guard)->user();

                // ✅ Block login if not approved
                if (!in_array($user->status, ['approved'])) {
                    Auth::guard($guard)->logout();

                    return back()->withErrors([
                        'email' => 'Your account is currently ' . $user->status . '. Please contact the administrator.',
                    ]);
                }

                $request->session()->regenerate();
                return redirect()->route($redirectRoute);
            }
        }

        return back()->withErrors([
            'email' => 'Invalid credentials or account not found.',
        ]);
    }

    /**
     * Handle user logout for any role
     */
    public function logout(Request $request)
    {
        // Determine which guard is active
        foreach (['admin', 'assessor', 'student'] as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
            }
        }

        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.show')
            ->with('status', 'You have been logged out successfully.');
    }
}
