<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicInformation extends Model
{
    protected $table = 'academic_information';
    protected $primaryKey = 'student_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    /**
     * Fields that can be mass-assigned.
     */
    protected $fillable = [
        'student_id',
        'program',
        'major',
        'college',
        'year_level',
        'expected_grad_year',
        'cor_file', // for uploaded Certificate of Registration
    ];

    /**
     * Relationships
     */

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
