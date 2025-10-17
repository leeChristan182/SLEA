<?php



namespace App\Http\Controllers;

use App\Models\StudentPersonalInformation;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $students = StudentPersonalInformation::with('academicInformation')->get();
        return view('students.index', compact('students'));
    }

    public function show($email)
    {
        $student = StudentPersonalInformation::with(['academicInformation', 'leadershipInformation'])->findOrFail($email);
        return view('students.show', compact('student'));
    }

    public function create()
    {
        return view('students.create');
    }

    public function edit($email)
    {
        $student = StudentPersonalInformation::findOrFail($email);
        return view('students.edit', compact('student'));
    }

    public function update(Request $request, $email)
    {
        $student = StudentPersonalInformation::findOrFail($email);
        
        $validated = $request->validate([
            'student_id' => 'required|string|max:20|unique:student_personal_information,student_id,' . $student->student_id . ',student_id',
            'email_address' => 'required|email|unique:student_personal_information,email_address,' . $email,
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'date_of_birth' => 'required|date',
            'age' => 'nullable|integer',
            'contact_number' => 'required|string|max:15',
            'gender' => 'required|in:Male,Female,Other',
            'address' => 'required|string',
        ]);

        $student->update($validated);

        return redirect()->route('students.show', $email)->with('success', 'Student updated successfully!');
    }

    public function destroy($email)
    {
        $student = StudentPersonalInformation::findOrFail($email);
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Student deleted successfully!');
    }
  
public function table()
{
    $students = StudentPersonalInformation::with(['academicInformation'])
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->get();
    return view('students.table', compact('students'));
}
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|string|max:20|unique:student_personal_information,student_id',
            'email_address' => 'required|email|unique:student_personal_information,email_address',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'date_of_birth' => 'required|date',
            'age' => 'nullable|integer',
            'contact_number' => 'required|string|max:15',
            'gender' => 'required|in:Male,Female,Other',
            'address' => 'required|string',
        ]);

        StudentPersonalInformation::create($validated);
        
        return redirect()->route('students.index')->with('success', 'Student created successfully!');
    }
}
