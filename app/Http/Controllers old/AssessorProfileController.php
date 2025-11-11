<?php

namespace App\Http\Controllers;

use App\Models\AssessorProfile;
use Illuminate\Http\Request;

class AssessorProfileController extends Controller
{
    public function index()
    {
        $assessors = AssessorProfile::all();
        return view('assessors.index', compact('assessors'));
    }

    public function create()
    {
        return view('assessors.create');
    }

    public function store(Request $request)
    {
        AssessorProfile::create($request->all());
        return redirect()->route('assessors.index');
    }

    public function show($id)
    {
        $admin = AssessorProfile::findOrFail($id);
        return view('assessors.show', compact('admin'));
    }

    public function edit($id)
    {
        $admin = AssessorProfile::findOrFail($id);
        return view('assessors.edit', compact('admin'));
    }

    public function update(Request $request, $id)
    {
        $admin = AssessorProfile::findOrFail($id);
        $admin->update($request->all());
        return redirect()->route('assessors.index');
    }

    public function destroy($id)
    {
        AssessorProfile::destroy($id);
        return redirect()->route('assessors.index');
    }
}
