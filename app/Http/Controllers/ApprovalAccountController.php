<?php

namespace App\Http\Controllers;

use App\Models\ApprovalOfAccount;
use App\Models\AcademicInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;   // <-- add this

class ApprovalAccountController extends Controller
{
    // List students with their approval status
    public function index(Request $request)
    {
        $q = $request->query('q');

        $rows = AcademicInformation::query()
            ->when($q, fn($qq) =>
                $qq->where('student_id','like',"%{$q}%")
                   ->orWhere('email_address','like',"%{$q}%")
                   ->orWhere('program','like',"%{$q}%"))
            ->leftJoin('approval_of_accounts as aoa', 'academic_information.student_id', '=', 'aoa.student_id')
            ->select([
                'academic_information.student_id',
                'academic_information.program',
                'academic_information.year_level',
                'aoa.action',
                'aoa.admin_id',
                'aoa.action_date',
            ])
            ->orderBy('academic_information.student_id')
            ->paginate(20)
            ->withQueryString();

        return view('approvals.index', compact('rows','q'));
    }

    // Approve
    public function approve(Request $request, string $student_id)
    {
        $request->validate([
            'admin_id' => ['nullable','string','max:20'],
        ]);

        $adminId = $request->input('admin_id') ?: (Auth::check() ? (string) Auth::id() : null);

        ApprovalOfAccount::updateOrCreate(
            ['student_id' => $student_id],
            [
                'admin_id'    => $adminId,
                'action'      => 'Approved',
                'action_date' => now(),
            ]
        );

        return back()->with('success', "Student {$student_id} approved.");
    }

    // Reject
    public function reject(Request $request, string $student_id)
    {
        $request->validate([
            'admin_id' => ['nullable','string','max:20'],
        ]);

        $adminId = $request->input('admin_id') ?: (Auth::check() ? (string) Auth::id() : null);

        ApprovalOfAccount::updateOrCreate(
            ['student_id' => $student_id],
            [
                'admin_id'    => $adminId,
                'action'      => 'Rejected',
                'action_date' => now(),
            ]
        );

        return back()->with('success', "Student {$student_id} rejected.");
    }
}
