<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile;

class ProfileController extends Controller
{
    /**
     * Display a listing of profiles.
     */
    public function index()
    {
        $profiles = Profile::all();
        return view('profiles.index', compact('profiles'));
    }

    /**
     * Show the form for creating a new profile.
     */
    public function create()
    {
        return view('profiles.create');
    }

    /**
     * Store a newly created profile.
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|string|max:20',
            'profile_picture_path' => 'required|string|max:255',
            'date_upload_profile' => 'required|date',
        ]);

        Profile::create($request->all());

        return redirect()->route('profiles.index')->with('success', 'Profile created.');
    }

    /**
     * Show a single profile.
     */
    public function show($id)
    {
        $profile = Profile::findOrFail($id);
        return view('profiles.show', compact('profile'));
    }

    /**
     * Edit form.
     */
    public function edit($id)
    {
        $profile = Profile::findOrFail($id);
        return view('profiles.edit', compact('profile'));
    }

    /**
     * Update the profile.
     */
    public function update(Request $request, $id)
    {
        $profile = Profile::findOrFail($id);

        $request->validate([
            'student_id' => 'required|string|max:20',
            'profile_picture_path' => 'required|string|max:255',
            'date_upload_profile' => 'required|date',
        ]);

        $profile->update($request->all());

        return redirect()->route('profiles.index')->with('success', 'Profile updated.');
    }

    /**
     * Delete profile.
     */
    public function destroy($id)
    {
        $profile = Profile::findOrFail($id);
        $profile->delete();

        return redirect()->route('profiles.index')->with('success', 'Profile deleted.');
    }
}
