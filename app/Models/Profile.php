<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $table = 'profile';

    protected $primaryKey = 'profile_id';

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'profile_picture_path',
        'date_upload_profile',
    ];

    public function student()
    {
        return $this->belongsTo(AcademicInformation::class, 'student_id', 'student_id');
    }
}
