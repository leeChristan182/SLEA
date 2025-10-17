<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPersonalInformation extends Model
{
     protected $table = 'student_personal_information';
    protected $primaryKey = 'email_address';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'student_id',
        'email_address',
        'last_name',
        'first_name',
        'middle_name',
        'date_of_birth',
        'age',
        'contact_number',
        'gender',
        'address',
        'dateacc_created',
    ];

    public function academicInformation()
    {
        return $this->hasOne(AcademicInformation::class, 'student_id', 'student_id');
    }

    public function leadershipInformation()
    {
        return $this->hasMany(LeadershipInformation::class, 'student_id', 'student_id');
    }

    public function getFullNameAttribute()
    {
        $name = $this->first_name . ' ' . $this->last_name;
        if ($this->middle_name) {
            $name = $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name;
        }
        return $name;
    }
}
