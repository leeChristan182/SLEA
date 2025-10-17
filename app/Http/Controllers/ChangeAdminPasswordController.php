<?php

namespace App\Http\Controllers;

use App\Models\ChangeAdminPassword;
use App\Models\AdminProfile;
use Illuminate\Http\Request;

class ChangeAdminPasswordController extends Controller
{
    public function index() {
        $changes = ChangeAdminPassword::with('admin')->get();
        return view('password_changes.index', compact('changes'));
    }

    public function create() {
        $admins = AdminProfile::all();
        return view('password_changes.create', compact('admins'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'admin_id' => 'required|exists:admin_profiles,admin_id',
            'old_password_hashed' => 'required|string|min:8',
            'password_hashed' => 'required|string|min:8',
            'date_pass_changed' => 'nullable|date',
        ]);

        // Hash both passwords
        $validated['old_password_hashed'] = bcrypt($validated['old_password_hashed']);
        $validated['password_hashed'] = bcrypt($validated['password_hashed']);
        
        // Set default date if not provided
        if (empty($validated['date_pass_changed'])) {
            $validated['date_pass_changed'] = now();
        }

        ChangeAdminPassword::create($validated);
        return redirect()->route('password-changes.index')->with('success', 'Password change recorded successfully!');
    }

    public function show($id) {
        $change = ChangeAdminPassword::with('admin')->findOrFail($id);
        return view('password_changes.show', compact('change'));
    }

    public function edit($id) {
        $change = ChangeAdminPassword::findOrFail($id);
        $admins = AdminProfile::all();
        return view('password_changes.edit', compact('change', 'admins'));
    }

    public function update(Request $request, $id) {
        $change = ChangeAdminPassword::findOrFail($id);
        
        $validated = $request->validate([
            'admin_id' => 'required|exists:admin_profiles,admin_id',
            'old_password_hashed' => 'required|string|min:8',
            'password_hashed' => 'required|string|min:8',
            'date_pass_changed' => 'nullable|date',
        ]);

        // Hash both passwords if provided
        if (!empty($validated['old_password_hashed'])) {
            $validated['old_password_hashed'] = bcrypt($validated['old_password_hashed']);
        }
        if (!empty($validated['password_hashed'])) {
            $validated['password_hashed'] = bcrypt($validated['password_hashed']);
        }

        $change->update($validated);
        return redirect()->route('password-changes.index')->with('success', 'Password change record updated successfully!');
    }

    public function destroy($id) {
        ChangeAdminPassword::destroy($id);
        return redirect()->route('password-changes.index')->with('success', 'Password change record deleted successfully!');
    }
}
