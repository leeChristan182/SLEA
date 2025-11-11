<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpdateProgram extends Model
{
    protected $table = 'update_programs';
    protected $primaryKey = 'updateprog_id';
    public $timestamps = true;

    protected $fillable = [
        'student_id',
        'old_program',
        'old_major',
        'new_program',
        'new_major',
        'date_prog_changed',
    ];

    // Optional: relationship to academic_information
    public function academic()
    {
        return $this->belongsTo(AcademicInformation::class, 'student_id', 'student_id');
    }
}
