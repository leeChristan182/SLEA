<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadershipInformation extends Model
{
    use HasFactory;

    protected $table = 'leadership_information';
    protected $primaryKey = 'leadership_id';

    protected $fillable = [
        'student_id',
        'leadership_type',
        'organization_name',
        'position',
        'term',
        'issued_by',
        'leadership_status',
    ];

    public function student()
    {
        return $this->belongsTo(StudentAccount::class, 'student_id', 'student_id');
    }
}
