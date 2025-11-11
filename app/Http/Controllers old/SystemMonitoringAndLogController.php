<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemMonitoringAndLog;
use Illuminate\Support\Facades\Auth;

class SystemMonitoringAndLogController extends Controller
{
    /**
     * Display a listing of system logs (Admin only)
     */
    public function index(Request $request)
    {
        // Only allow admin access
        if (!Auth::guard('admin')->check()) {
            abort(403, 'Unauthorized action.');
        }

        // Filters: role, activity type, date
        $query = SystemMonitoringAndLog::query();

        if ($request->filled('role')) {
            $query->where('user_role', $request->role);
        }

        if ($request->filled('type')) {
            $query->where('activity_type', $request->type);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Get logs (latest first)
        $logs = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.system-monitoring', [
            'logs' => $logs,
            'filters' => [
                'role' => $request->role,
                'type' => $request->type,
                'date' => $request->date,
            ],
        ]);
    }

    /**
     * Delete a specific log entry
     */
    public function destroy($id)
    {
        if (!Auth::guard('admin')->check()) {
            abort(403, 'Unauthorized action.');
        }

        $log = SystemMonitoringAndLog::findOrFail($id);
        $log->delete();

        SystemMonitoringAndLog::record('admin', Auth::guard('admin')->user()->name, 'Delete', "Deleted log entry #{$id}");

        return back()->with('success', 'Log entry deleted successfully.');
    }

    /**
     * Clear all logs (optional, admin only)
     */
    public function clearAll()
    {
        if (!Auth::guard('admin')->check()) {
            abort(403, 'Unauthorized action.');
        }

        SystemMonitoringAndLog::truncate();

        SystemMonitoringAndLog::record('admin', Auth::guard('admin')->user()->name, 'Delete', 'Cleared all system logs');

        return back()->with('success', 'All system logs have been cleared.');
    }
}
