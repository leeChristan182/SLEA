<?php

namespace App\Http\Controllers;

use App\Models\AdminProfile;
use Illuminate\Http\Request;

class AdminProfileController extends Controller
{
    public function index() {
        $admins = AdminProfile::all();
        return view('admins.index', compact('admins'));
    }

    public function create() {
        return view('admins.create');
    }

    public function store(Request $request) {
        AdminProfile::create($request->all());
        return redirect()->route('admins.index');
    }

    public function show($id) {
        $admin = AdminProfile::findOrFail($id);
        return view('admins.show', compact('admin'));
    }

    public function edit($id) {
        $admin = AdminProfile::findOrFail($id);
        return view('admins.edit', compact('admin'));
    }

    public function update(Request $request, $id) {
        $admin = AdminProfile::findOrFail($id);
        $admin->update($request->all());
        return redirect()->route('admins.index');
    }

    public function destroy($id) {
        AdminProfile::destroy($id);
        return redirect()->route('admins.index');
    }
}
