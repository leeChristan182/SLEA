<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadershipInformation extends Model
{
    protected $table = 'leadership_information';

    protected $primaryKey = 'leadership_id';

    protected $fillable = [
        'student_id',
        'leadership_type_id',
        'organization_id',
        'organization_name',
        'organization_role',
        'term',
        'issued_by',
        'leadership_status',
    ];

    public function student()
    {
        return $this->belongsTo(StudentPersonalInformation::class, 'student_id', 'student_id');
    }

    public function leadershipType()
    {
        return $this->belongsTo(LeadershipType::class, 'leadership_type_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }
}
