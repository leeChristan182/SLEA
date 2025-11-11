<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class StudentPersonalInformation extends Model
{
    protected $table = 'student_personal_information';
    protected $primaryKey = 'email_address';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false; // ✅ Add this line

    protected $fillable = [
        'student_id',
        'email_address',
        'first_name',
        'last_name',
        'middle_name',
        'birth_date',
        'contact_number',
        'age',
    ];
    protected static function booted()
    {
        static::saving(function ($student) {
            if ($student->birth_date) {
                // Directly set the raw DB attribute, bypassing the accessor
                $student->attributes['age'] = \Carbon\Carbon::parse($student->birth_date)->age;
            }
        });
    }

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
