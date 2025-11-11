<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SubmissionRecordController extends Controller
{
    // GET /student/submissions
    public function index()
    {
        $rows = Schema::hasTable('submissions')
            ? DB::table('submissions')->where('user_id', auth()->id())->orderByDesc('created_at')->paginate(20)
            : collect();

        return view('student.submissions.index', ['submissions' => $rows]);
    }

    // GET /student/submissions/create  (also aliased as /student/submit)
    public function create()
    {
        return view('student.submissions.create');
    }

    // POST /student/submissions
    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'file'  => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:6144'],
        ]);

        $path = $request->file('file')->store('submissions', 'public');

        if (Schema::hasTable('submissions')) {
            DB::table('submissions')->insert([
                'user_id'    => auth()->id(),
                'title'      => $request->title,
                'status'     => 'pending',
                'file_path'  => $path,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('student.submissions.index')->with('status', 'Submission uploaded.');
    }

    // GET /student/submissions/download/{id}
    public function download(int $id)
    {
        if (! Schema::hasTable('submissions')) abort(404);

        $row = DB::table('submissions')->where('id', $id)->where('user_id', auth()->id())->first();
        if (! $row || empty($row->file_path) || ! Storage::disk('public')->exists($row->file_path)) abort(404);

        return Storage::disk('public')->download($row->file_path, basename($row->file_path));
    }
}
