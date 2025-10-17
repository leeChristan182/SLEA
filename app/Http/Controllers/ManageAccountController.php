<?php

namespace App\Http\Controllers;

use App\Models\ManageAccount;
use App\Models\AdminProfile;
use App\Models\LogIn;
use Illuminate\Http\Request;

class ManageAccountController extends Controller
{
    public function index() {
        $accounts = ManageAccount::with(['admin', 'loginRecords' => function($query) {
            $query->latest('login_datetime')->limit(5);
        }])->get();
        
        $loginStats = LogIn::getLoginStats();
        
        return view('accounts.index', compact('accounts', 'loginStats'));
    }

    public function create() {
        $admins = AdminProfile::all();
        return view('accounts.create', compact('admins'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'email_address' => 'required|email|unique:manage_accounts,email_address',
            'admin_id' => 'required|exists:admin_profiles,admin_id',
            'user_type' => 'required|in:student,assessor',
            'account_status' => 'required|in:active,inactive,suspended',
        ]);

        ManageAccount::create($validated);
        return redirect()->route('accounts.index')->with('success', 'Account created successfully!');
    }

    public function show($id) {
        $account = ManageAccount::with(['admin', 'loginRecords' => function($query) {
            $query->latest('login_datetime')->limit(20);
        }])->findOrFail($id);
        
        $loginStats = $account->getLoginStats();
        $recentLogins = $account->getRecentLogins(10);
        
        return view('accounts.show', compact('account', 'loginStats', 'recentLogins'));
    }

    public function edit($id) {
        $account = ManageAccount::findOrFail($id);
        $admins = AdminProfile::all();
        return view('accounts.edit', compact('account', 'admins'));
    }

    public function update(Request $request, $id) {
        $account = ManageAccount::findOrFail($id);
        
        $validated = $request->validate([
            'email_address' => 'required|email|unique:manage_accounts,email_address,' . $id,
            'admin_id' => 'required|exists:admin_profiles,admin_id',
            'user_type' => 'required|in:student,assessor',
            'account_status' => 'required|in:active,inactive,suspended',
        ]);

        $account->update($validated);
        return redirect()->route('accounts.index')->with('success', 'Account updated successfully!');
    }

    public function destroy($id) {
        $account = ManageAccount::findOrFail($id);
        $account->delete();
        return redirect()->route('accounts.index')->with('success', 'Account deleted successfully!');
    }

    /**
     * Show login monitoring dashboard
     */
    public function loginMonitoring() {
        $recentLogins = LogIn::with('manageAccount')
            ->latest('login_datetime')
            ->limit(50)
            ->get();
            
        $loginStats = LogIn::getLoginStats();
        
        $accountsByType = ManageAccount::selectRaw('user_type, COUNT(*) as count')
            ->groupBy('user_type')
            ->pluck('count', 'user_type')
            ->toArray();
            
        $activeAccounts = ManageAccount::active()->count();
        $inactiveAccounts = ManageAccount::where('account_status', 'inactive')->count();
        $suspendedAccounts = ManageAccount::where('account_status', 'suspended')->count();
        
        return view('accounts.login-monitoring', compact(
            'recentLogins', 
            'loginStats', 
            'accountsByType',
            'activeAccounts',
            'inactiveAccounts', 
            'suspendedAccounts'
        ));
    }

    /**
     * Get login activity for a specific account
     */
    public function accountLoginActivity($id) {
        $account = ManageAccount::with(['loginRecords' => function($query) {
            $query->latest('login_datetime');
        }])->findOrFail($id);
        
        $loginStats = $account->getLoginStats();
        $recentLogins = $account->getRecentLogins(50);
        
        return view('accounts.login-activity', compact('account', 'loginStats', 'recentLogins'));
    }

    /**
     * Suspend an account
     */
    public function suspend($id) {
        $account = ManageAccount::findOrFail($id);
        $account->update(['account_status' => 'suspended']);
        
        return redirect()->back()->with('success', 'Account suspended successfully!');
    }

    /**
     * Activate an account
     */
    public function activate($id) {
        $account = ManageAccount::findOrFail($id);
        $account->update(['account_status' => 'active']);
        
        return redirect()->back()->with('success', 'Account activated successfully!');
    }
}
