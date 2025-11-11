<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicInformation extends Model
{
    protected $table = 'academic_information';
    protected $primaryKey = 'id'; // ✅ actual primary key in your migration
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'student_id',
        'college',           // ✅ correct field name
        'program',
        'major',
        'year_level',
        'graduate_prior',    // ✅ correct DB column name
        'cor_file',
    ];

    public function getExpectedGradYearAttribute($value)
    {
        if ($value) return $value;

        $year = preg_replace('/\D/', '', $this->year_level); // extract number only
        $year = (int) $year;

        if ($year < 1 || $year > 4) return null;

        return date('Y') + (5 - $year); // 1st→+4, 2nd→+3, etc.
    }


    public function collegeProgram()
    {
        return $this->belongsTo(CollegeProgram::class, 'program', 'program_name');
    }

    protected static function booted()
    {
        static::saving(function ($academic) {
            if (!$academic->college && $academic->program) {
                $match = \App\Models\CollegeProgram::where('program_name', $academic->program)->first();
                if ($match) {
                    $academic->college = $match->college_name;
                }
            }
        });
    }

    // Each academic info record belongs to one student
    public function student()
    {
        return $this->belongsTo(StudentPersonalInformation::class, 'student_id', 'student_id');
    }

    // Each academic record may have an approval status
    public function approval()
    {
        return $this->hasOne(ApprovalOfAccount::class, 'student_id', 'student_id');
    }
}
