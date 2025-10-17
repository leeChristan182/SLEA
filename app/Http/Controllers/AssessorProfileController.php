<?php
namespace App\Http\Controllers;

use App\Models\AssessorProfile;
use App\Models\AssessorAccount;
use Illuminate\Http\Request;

class AssessorProfileController extends Controller
{
    public function index()
    {
        $assessor_profiles = AssessorProfile::with('account')->get();
        return view('assessor_profiles.index', compact('assessor_profiles'));
    }

    public function create()
    {
        $accounts = AssessorAccount::all();
        return view('assessor_profiles.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'assessor_id' => 'required|unique:assessor_profiles,assessor_id',
            'email_address' => 'required|exists:assessor_accounts,email_address',
            'picture_path' => 'nullable|string',
            'date_upload' => 'required|date',
        ]);

        AssessorProfile::create($request->all());
        return redirect()->route('assessor_profiles.index')->with('success', 'Profile created.');
    }

    public function show(AssessorProfile $assessor_profile)
    {
        return view('assessor_profiles.show', compact('assessor_profile'));
    }

    public function edit(AssessorProfile $assessor_profile)
    {
        $accounts = AssessorAccount::all();
        return view('assessor_profiles.edit', compact('assessor_profile', 'accounts'));
    }

    public function update(Request $request, AssessorProfile $assessor_profile)
    {
        $request->validate([
            'email_address' => 'required|exists:assessor_accounts,email_address',
            'picture_path' => 'nullable|string',
            'date_upload' => 'required|date',
        ]);

        $assessor_profile->update($request->all());
        return redirect()->route('assessor_profiles.index')->with('success', 'Profile updated.');
    }

    public function destroy(AssessorProfile $assessor_profile)
    {
        $assessor_profile->delete();
        return redirect()->route('assessor_profiles.index')->with('success', 'Profile deleted.');
    }
}
