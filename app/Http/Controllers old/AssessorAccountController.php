<?php
namespace App\Http\Controllers;

use App\Models\AssessorAccount;
use Illuminate\Http\Request;

class AssessorAccountController extends Controller
{
    public function index()
    {
        $assessor_accounts = AssessorAccount::all();
        return view('assessor_accounts.index', compact('assessor_accounts'));
    }

    public function create()
    {
        return view('assessor_accounts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email_address' => 'required|email|unique:assessor_accounts,email_address',
            'admin_id' => 'required',
            'last_name' => 'required',
            'first_name' => 'required',
            'position' => 'required',
            'default_password' => 'required',
            'dateacc_created' => 'required|date',
        ]);

        AssessorAccount::create($request->all());
        return redirect()->route('assessor_accounts.index')->with('success', 'Assessor Account created.');
    }

    public function show(AssessorAccount $assessor_account)
    {
        return view('assessor_accounts.show', compact('assessor_account'));
    }

    public function edit(AssessorAccount $assessor_account)
    {
        return view('assessor_accounts.edit', compact('assessor_account'));
    }

    public function update(Request $request, AssessorAccount $assessor_account)
    {
        $request->validate([
            'admin_id' => 'required',
            'last_name' => 'required',
            'first_name' => 'required',
            'position' => 'required',
            'default_password' => 'required',
            'dateacc_created' => 'required|date',
        ]);

        $assessor_account->update($request->all());
        return redirect()->route('assessor_accounts.index')->with('success', 'Assessor Account updated.');
    }

    public function destroy(AssessorAccount $assessor_account)
    {
        $assessor_account->delete();
        return redirect()->route('assessor_accounts.index')->with('success', 'Assessor Account deleted.');
    }
}
