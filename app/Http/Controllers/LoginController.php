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

        // Admin login
        if (Auth::guard('admin')->attempt([
            'email_address' => $credentials['email'],
            'password' => $credentials['password'],
        ])) {
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        // Assessor login
        if (Auth::guard('assessor')->attempt([
            'email_address' => $credentials['email'],
            'password' => $credentials['password'],
        ])) {
            $request->session()->regenerate();
            return redirect()->route('assessor.dashboard');
        }

        // Student login
        if (Auth::guard('student')->attempt([
            'email_address' => $credentials['email'],
            'password' => $credentials['password'],
        ])) {
            $request->session()->regenerate();
            return redirect()->route('student.dashboard');
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
