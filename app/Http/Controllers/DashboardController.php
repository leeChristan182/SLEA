<?php

namespace App\Http\Controllers;

use App\Models\AssessorAccount;
use App\Models\AssessorProfile;
use App\Models\ChangeAssessorPassword;
use App\Models\SystemMonitoringAndLog;
use App\Models\LogIn;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_assessors' => AssessorAccount::count(),
            'total_profiles' => AssessorProfile::count(),
            'total_password_changes' => ChangeAssessorPassword::count(),
            'total_logs' => SystemMonitoringAndLog::count(),
            'total_logins' => LogIn::count(),
        ];

        $recent_logs = SystemMonitoringAndLog::with('login')
            ->latest()
            ->take(5)
            ->get();

        $recent_logins = LogIn::latest()
            ->take(5)
            ->get();

        return view('dashboard.index', compact('stats', 'recent_logs', 'recent_logins'));
    }
}









