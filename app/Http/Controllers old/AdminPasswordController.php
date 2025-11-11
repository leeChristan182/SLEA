<?php

namespace App\Http\Controllers;

use App\Models\AdminPassword;
use App\Models\AdminProfile;
use Illuminate\Http\Request;

class AdminPasswordController extends Controller
{
    public function index() {
        $passwords = AdminPassword::with('admin')->get();
        return view('passwords.index', compact('passwords'));
    }

    public function create() {
        $admins = AdminProfile::all();
        return view('passwords.create', compact('admins'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'admin_id' => 'required|exists:admin_profiles,admin_id',
            'password_hashed' => 'required|string|min:8',
            'date_pass_created' => 'nullable|date',
        ]);

        // Hash the password
        $validated['password_hashed'] = bcrypt($validated['password_hashed']);
        
        // Set default date if not provided
        if (empty($validated['date_pass_created'])) {
            $validated['date_pass_created'] = now();
        }

        AdminPassword::create($validated);
        return redirect()->route('passwords.index')->with('success', 'Password created successfully!');
    }

    public function show($id) {
        $password = AdminPassword::with('admin')->findOrFail($id);
        return view('passwords.show', compact('password'));
    }

    public function edit($id) {
        $password = AdminPassword::findOrFail($id);
        $admins = AdminProfile::all();
        return view('passwords.edit', compact('password', 'admins'));
    }

    public function update(Request $request, $id) {
        $password = AdminPassword::findOrFail($id);
        
        $validated = $request->validate([
            'admin_id' => 'required|exists:admin_profiles,admin_id',
            'password_hashed' => 'required|string|min:8',
            'date_pass_created' => 'nullable|date',
        ]);

        // Hash the password if provided
        if (!empty($validated['password_hashed'])) {
            $validated['password_hashed'] = bcrypt($validated['password_hashed']);
        }

        $password->update($validated);
        return redirect()->route('passwords.index')->with('success', 'Password updated successfully!');
    }

    public function destroy($id) {
        AdminPassword::destroy($id);
        return redirect()->route('passwords.index')->with('success', 'Password deleted successfully!');
    }
}
