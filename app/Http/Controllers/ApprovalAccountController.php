<?php

namespace App\Http\Controllers;

use App\Models\ApprovalOfAccount;
use App\Models\AcademicInformation;
use App\Models\AssessorAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalAccountController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('q');

        // 🔹 STUDENTS
        $students = AcademicInformation::query()
            ->when(
                $search,
                fn($q) =>
                $q->where('student_id', 'like', "%{$search}%")
                    ->orWhere('email_address', 'like', "%{$search}%")
                    ->orWhere('program', 'like', "%{$search}%")
            )
            ->leftJoin('approval_of_accounts as aoa', 'academic_information.student_id', '=', 'aoa.student_id')
            ->select([
                'academic_information.student_id',
                'academic_information.email_address',
                'academic_information.first_name',
                'academic_information.last_name',
                'academic_information.program',
                'academic_information.year_level',
                'academic_information.gpa',
                'aoa.action',
                'aoa.admin_id',
                'aoa.action_date',
            ])
            ->orderBy('academic_information.student_id')
            ->paginate(10, ['*'], 'students_page');

        // 🔹 ASSESSORS
        $assessors = AssessorAccount::query()
            ->when(
                $search,
                fn($q) =>
                $q->where('email_address', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
            )
            ->select([
                'email_address',
                'first_name',
                'last_name',
                'position',
                'dateacc_created',
                'status', // add this column in your table if not yet
            ])
            ->orderBy('email_address')
            ->paginate(10, ['*'], 'assessors_page');

        return view('admin.approve-reject', compact('students', 'assessors', 'search'));
    }

public function approveUser($student_id)
{
    $student = \App\Models\StudentAccount::where('student_id', $student_id)->firstOrFail();
    $student->update(['status' => 'approved']);

    \App\Models\SystemMonitoringAndLog::create([
        'user_role' => 'Admin',
        'user_name' => auth()->user()->email_address,
        'activity_type' => 'Approve Account',
        'description' => "Approved student account: {$student->email_address}",
    ]);

    return back()->with('success', "Student {$student->email_address} approved successfully!");
}

public function rejectUser($student_id)
{
    $student = \App\Models\StudentAccount::where('student_id', $student_id)->firstOrFail();
    $student->update(['status' => 'rejected']);

    \App\Models\SystemMonitoringAndLog::create([
        'user_role' => 'Admin',
        'user_name' => auth()->user()->email_address,
        'activity_type' => 'Reject Account',
        'description' => "Rejected student account: {$student->email_address}",
    ]);

    return back()->with('success', "Student {$student->email_address} rejected.");
}
