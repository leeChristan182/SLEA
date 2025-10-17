<?php

namespace App\Http\Controllers;

use App\Models\AcademicInformation;
use Illuminate\Http\Request;

class AcademicController extends Controller
{
 

public function index()
{
    $academics = AcademicInformation::all();
    return view('academics.index', compact('academics'));
}


    public function create()
    {
        return view('academics.create');
    }

    public function store(Request $request)
    {
        AcademicInformation::create($request->validate([
            'student_id' => 'required|string',
            'school_year' => 'required|string',
            'semester' => 'required|string',
            'program' => 'required|string',
            'year_level' => 'required|string',
        ]));

        return redirect()->route('academics.index');
    }
}
