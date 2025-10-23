<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\StudentPersonalInformation;
use App\Models\AcademicInformation;
use App\Models\LeadershipInformation;
use App\Models\ApprovalOfAccount;

class RegisterController extends Controller
{
    public function show()
    {
        // If already authenticated, redirect based on guard/role
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.profile');
        }

        if (Auth::guard('assessor')->check()) {
            return redirect()->route('assessor.profile');
        }

        if (Auth::guard('student')->check()) {
            return redirect()->route('student.profile');
        }

        // Otherwise, show login page
        return view('login');
    }


    public function submit(Request $request)
    {
        $validated = $request->validate([
            // Personal
            'last_name' => 'required|string|max:50',
            'first_name' => 'required|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'birth_date' => 'required|date|before:today',
            'age' => 'required|integer|min:16|max:100',
            'email' => 'required|email|max:255|unique:users,email',
            'contact' => 'required|string|max:15',

            // Academic
            'student_id' => 'required|string|max:20|unique:student_personal_information,student_id',
            'college' => 'required|string|in:CIC,COE',
            'program' => 'required|string|max:50',
            'major' => 'nullable|string|max:50',
            'year_level' => 'required|string|in:1st Year,2nd Year,3rd Year,4th Year',
            'expected_grad' => 'required|string|max:10',

            // Leadership
            'leadership_type' => 'required|string|in:President,Member',
            'org_name' => 'required|string|max:255',
            'org_role' => 'required|string|in:President,Vice President,Secretary,Treasurer,Member',
            'issued_by' => 'required|string|max:255',
            'leadership_status' => 'required|string|in:active,inactive',
            'term' => 'required|string|max:255',

            // Credentials
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                // Require at least one uppercase, one lowercase, one number, one special char
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/'
            ],
            'privacy_agree' => 'required|accepted',
        ], [
            // Custom error messages
            'password.regex' => 'Password must have at least 8 characters and include an uppercase letter, a lowercase letter, a number, and a special character.',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // Create user
                $user = User::create([
                    'name' => "{$validated['first_name']} {$validated['last_name']}",
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'student_id' => $validated['student_id'],
                    'user_role' => 'student',
                    'is_approved' => false,
                ]);

                // Student personal info
                StudentPersonalInformation::create([
                    'student_id' => $validated['student_id'],
                    'last_name' => $validated['last_name'],
                    'first_name' => $validated['first_name'],
                    'middle_name' => $validated['middle_name'],
                    'email_address' => $validated['email'],
                    'contact_number' => $validated['contact'],
                    'date_of_birth' => $validated['birth_date'],
                    'age' => $validated['age'],
                    'gender' => 'Other',
                    'address' => 'Not provided',
                    'dateacc_created' => now(),
                ]);

                // Academic info
                AcademicInformation::create([
                    'student_id' => $validated['student_id'],
                    'program' => $validated['program'],
                    'major' => $validated['major'],
                    'year_level' => $validated['year_level'],
                    'graduate_prior' => $validated['expected_grad'],
                ]);

                // Leadership info
                LeadershipInformation::create([
                    'student_id' => $validated['student_id'],
                    'leadership_type' => $validated['leadership_type'],
                    'organization_name' => $validated['org_name'],
                    'organization_role' => $validated['org_role'],
                    'issued_by' => $validated['issued_by'],
                    'leadership_status' => $validated['leadership_status'],
                    'term' => $validated['term'],
                ]);

                // Approval status
                ApprovalOfAccount::create([
                    'student_id' => $validated['student_id'],
                    'admin_id' => null,
                    'action' => 'pending',
                    'action_date' => null,
                ]);
            });

            return redirect()->route('login.show')
                ->with('success', 'Registration submitted successfully! Your account is pending admin approval.');
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors([
                'error' => 'Registration failed. Please try again. Error: ' . $e->getMessage()
            ]);
        }
    }
}
