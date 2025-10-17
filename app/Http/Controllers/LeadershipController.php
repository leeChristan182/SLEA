<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeadershipInformation;
use App\Models\StudentPersonalInformation;

class LeadershipController extends Controller
{
    public function index()
    {
        $leaderships = LeadershipInformation::with('student')->get();
        return view('leadership.index', compact('leaderships'));
    }

    public function create()
    {
        $students = StudentPersonalInformation::orderBy('last_name')->orderBy('first_name')->get();
        return view('leadership.create', compact('students'));
    }

    public function show($id)
    {
        $leadership = LeadershipInformation::with('student')->findOrFail($id);
        return view('leadership.show', compact('leadership'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|string|max:20',
            'organization_name' => 'required|string|max:255',
            'organization_role' => 'required|string|max:255',
            'term' => 'required|string|max:255',
            
            'leadership_status' => 'required|string|max:255',
        ]);

        LeadershipInformation::create($request->all());

        return redirect()->route('leadership.index')->with('success', 'Leadership information added successfully!');
    }

    public function edit($id)
    {
        $leadership = LeadershipInformation::findOrFail($id);
        $students = StudentPersonalInformation::orderBy('last_name')->orderBy('first_name')->get();
        return view('leadership.edit', compact('leadership', 'students'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'student_id' => 'required|string|max:20',
            'leadership_type' => 'required|string|max:255',
            'organization_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'term' => 'required|string|max:255',
            'leadership_status' => 'required|string|max:255',
        ]);

        $leadership = LeadershipInformation::findOrFail($id);
        $leadership->update($request->all());

        return redirect()->route('leadership.index')->with('success', 'Leadership information updated successfully!');
    }

    public function destroy($id)
    {
        $leadership = LeadershipInformation::findOrFail($id);
        $leadership->delete();

        return redirect()->route('leadership.index')->with('success', 'Leadership information deleted successfully!');
    }
}

