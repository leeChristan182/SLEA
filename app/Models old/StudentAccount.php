<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class StudentAccount extends Authenticatable
{
    protected $table = 'student_accounts';
    protected $primaryKey = 'student_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected $fillable = [
        'student_id',
        'email_address',
        'password',
        'status',
        // other columns...
    ];

    // ✅ Add these relationships
    public function personalInfo()
    {
        return $this->hasOne(\App\Models\StudentPersonalInformation::class, 'student_id', 'student_id');
    }

    public function academicInfo()
    {
        return $this->hasOne(\App\Models\AcademicInformation::class, 'student_id', 'student_id');
    }
}
