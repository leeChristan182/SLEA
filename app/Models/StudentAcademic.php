<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAcademic extends Model
{
    protected $table = 'student_academic';

    protected $fillable = [
        'user_id',
        'student_number',
        'college_name',       // swap to college_id if normalized
        'program',            // swap to program_id if normalized
        'major',              // swap to major_id if normalized
        'year_level',
        'expected_grad_year',
        'eligibility_status', // pending|needs_revalidation|under_review|ineligible
        'cor_file',           // if you store the COR here
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
