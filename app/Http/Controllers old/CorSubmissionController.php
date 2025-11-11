<?php

namespace App\Http\Controllers;

use App\Models\CorSubmission;
use App\Models\AcademicInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CorSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $studentId = $request->query('student_id');

        $submissions = CorSubmission::when($studentId, fn ($q) => $q->where('student_id', $studentId))
            ->latest('upload_date')
            ->paginate(10);

        return view('cor.index', compact('submissions', 'studentId'));
    }

    public function create(Request $request)
    {
        $studentId = $request->query('student_id');
        return view('cor.create', compact('studentId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'string', 'max:20'],
            'file'       => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
            'status'     => ['nullable', 'string', 'max:15'],
        ]);

        $file       = $validated['file'];
        $storedPath = $file->store('cor_submissions', 'public');

        $cor = CorSubmission::create([
            'student_id'   => $validated['student_id'],
            'file_name'    => $file->getClientOriginalName(),
            'file_type'    => strtolower($file->getClientOriginalExtension()),
            'file_size'    => $file->getSize(),
            'upload_date'  => now(),
            'status'       => $validated['status'] ?? 'Pending',
            'storage_path' => $storedPath,
        ]);

        // Update/create the AcademicInformation row (only touching columns that exist)
        $criteria = [
            'student_id'    => $cor->student_id,
            'academic_year' => $cor->academic_year,
        ];
        $updates = [];
        if (Schema::hasColumn('academic_informations', 'cor_status'))        $updates['cor_status']        = $cor->status;
        if (Schema::hasColumn('academic_informations', 'cor_file_path'))     $updates['cor_file_path']     = $cor->storage_path;
        if (Schema::hasColumn('academic_informations', 'has_cor_submitted')) $updates['has_cor_submitted'] = true;
        if (Schema::hasColumn('academic_informations', 'cor_uploaded_at'))   $updates['cor_uploaded_at']   = $cor->upload_date;
        if (Schema::hasColumn('academic_informations', 'document_status'))   $updates['document_status']   = $cor->status;

        AcademicInformation::updateOrCreate($criteria, $updates);

        return redirect()
            ->route('cor.index', ['student_id' => $cor->student_id])
            ->with('success', 'COR uploaded and Academic Information recorded.');
    }

   public function download($cor_id)
{
    $cor = CorSubmission::findOrFail($cor_id);

    $disk = Storage::disk('public'); // \Illuminate\Filesystem\FilesystemAdapter

    if (!$cor->storage_path || !$disk->exists($cor->storage_path)) {
        abort(404, 'File not found.');
    }

    // Get absolute path for the response() helper
    $absolutePath = $disk->path($cor->storage_path);

    // Use the framework response helper rather than $disk->download()
    return response()->download($absolutePath, $cor->file_name);
}
}
