<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicInformation extends Model
{
    protected $table = 'academic_information';
    protected $primaryKey = 'student_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public function approval()
    {
        return $this->hasOne(ApprovalOfAccount::class, 'student_id', 'student_id');
    }

    public function student()
    {
        return $this->belongsTo(StudentPersonalInformation::class, 'student_id', 'student_id');
    }
}
