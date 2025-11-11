<?php
namespace App\Http\Controllers;

use App\Models\ChangeAssessorPassword;
use App\Models\AssessorAccount;
use Illuminate\Http\Request;

class ChangeAssessorPasswordController extends Controller
{
    public function index()
    {
        $password_changes = ChangeAssessorPassword::with('account')->get();
        return view('change_assessor_passwords.index', compact('password_changes'));
    }

    public function create()
    {
        $accounts = AssessorAccount::all();
        return view('change_assessor_passwords.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email_address' => 'required|exists:assessor_accounts,email_address',
            'old_password_hashed' => 'required',
            'new_password_hashed' => 'required',
            'date_pass_changed' => 'required|date',
        ]);

        ChangeAssessorPassword::create($request->all());
        return redirect()->route('change_assessor_passwords.index')->with('success', 'Password change recorded.');
    }

    public function show(ChangeAssessorPassword $change_assessor_password)
    {
        return view('change_assessor_passwords.show', compact('change_assessor_password'));
    }

    public function edit(ChangeAssessorPassword $change_assessor_password)
    {
        $accounts = AssessorAccount::all();
        return view('change_assessor_passwords.edit', compact('change_assessor_password', 'accounts'));
    }

    public function update(Request $request, ChangeAssessorPassword $change_assessor_password)
    {
        $request->validate([
            'email_address' => 'required|exists:assessor_accounts,email_address',
            'old_password_hashed' => 'required',
            'new_password_hashed' => 'required',
            'date_pass_changed' => 'required|date',
        ]);

        $change_assessor_password->update($request->all());
        return redirect()->route('change_assessor_passwords.index')->with('success', 'Password change updated.');
    }

    public function destroy(ChangeAssessorPassword $change_assessor_password)
    {
        $change_assessor_password->delete();
        return redirect()->route('change_assessor_passwords.index')->with('success', 'Password change deleted.');
    }
}
