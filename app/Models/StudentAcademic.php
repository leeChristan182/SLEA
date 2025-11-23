<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentAcademic extends Model
{
    use HasFactory;

    protected $table = 'student_academic';

    protected $fillable = [
        'user_id',
        'student_number',
        'college_id',
        'program_id',
        'major_id',
        'year_level',
        'graduate_prior',
        'expected_grad_year',
        'eligibility_status',
        'revalidated_at',
    ];

    protected $casts = [
        'graduate_prior'     => 'integer',
        'expected_grad_year' => 'integer',
        'revalidated_at'     => 'datetime',
    ];

    /* ------------ Relationships ------------ */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function major()
    {
        return $this->belongsTo(Major::class);
    }
}
