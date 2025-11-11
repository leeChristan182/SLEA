<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollegeProgram extends Model
{
    protected $table = 'college_programs';
    public $timestamps = true;

    protected $fillable = [
        'college_name',
        'program_name',
        'major_name',
    ];

    // Relationship: Each program may be linked to many academic infos
    public function academicInformation()
    {
        return $this->hasMany(AcademicInformation::class, 'program', 'program_name');
    }
}
