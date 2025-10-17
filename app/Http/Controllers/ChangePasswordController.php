<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChangePassword;

class ChangePasswordController extends Controller
{
    // Show the form
    public function create()
    {
        return view('change-password.form');
    }

    // Store submitted form
    public function store(Request $request)
    {
        $validated = $request->validate([
            'password_id' => 'required|integer',
            'new_password' => 'required|string|min:6',
        ]);

        ChangePassword::create([
            'password_id' => $validated['password_id'],
            'new_password_hashed' => bcrypt($validated['new_password']),
            'date_pass_changed' => now(),
        ]);

        return redirect('/change-password/list')->with('success', 'Password changed successfully.');
    }

    // Show all changed passwords in a table
    public function index()
    {
        $changes = ChangePassword::all();
        return view('change-password.list', compact('changes'));
    }
}
