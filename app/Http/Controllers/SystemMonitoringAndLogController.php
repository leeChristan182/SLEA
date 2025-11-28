<?php

namespace App\Http\Controllers;

use App\Models\SystemMonitoringAndLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemMonitoringAndLogController extends Controller
{
    /**
     * Display a listing of system logs (Admin only).
     */
    public function index(Request $request)
    {
        // Route is already protected by middleware('role:admin') in web.php,
        // so we don't need extra guard checks here.

        $query = SystemMonitoringAndLog::query();

        // Filter: role
        if ($role = $request->input('role')) {
            $query->where('user_role', $role);
        }

        // Filter: activity_type (Login, Logout, Create, Update, Delete, etc.)
        if ($type = $request->input('activity_type')) {
            $query->where('activity_type', $type);
        }

        // Filter: date range
        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        // Search: user name / description
        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $logs = $query
            ->orderByDesc('created_at')
            ->paginate(10)
            ->appends($request->query());

        return view('admin.system-monitoring', compact('logs'));
    }

    /**
     * Clear all logs (Admin only).
     */
    public function clearAll(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Actually clear logs
        SystemMonitoringAndLog::truncate();

        // 🔹 SYSTEM LOG: CLEAR (meta-log)
        SystemMonitoringAndLog::record(
            'admin',
            trim($user->first_name . ' ' . $user->last_name) ?: $user->email,
            'Delete',
            'Cleared all system logs.'
        );

        return redirect()
            ->route('admin.system-logs.index')
            ->with('success', 'All system logs have been cleared.');
    }
}
