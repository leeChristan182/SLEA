<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function dashboard()
    {
        // Debug: Log student dashboard access
        \Log::info('Student dashboard accessed by user: ' . (auth()->check() ? auth()->user()->email : 'Not authenticated'));

        $user = Auth::user();
        return view('student.dashboard', compact('user'));
    }

    public function profile()
    {
        return view('student.profile');
    }

    public function submit()
    {
        return view('student.submit');
    }

    public function performance()
    {
        return view('student.performance');
    }

    public function criteria()
    {
        return view('student.criteria');
    }

    public function history()
    {
        return view('student.history'); // ✅ fixed
    }
}
