<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionReview extends Model
{
    use HasFactory;

    protected $table = 'submission_reviews';

    protected $fillable = [
        'submission_id',
        'assessor_id',
        'sub_section_id',
        'rubric_option_id',
        'quantity',
        'score',
        'computed_max',
        'score_source',
        'override_reason',
        'decision',
        'comments',
        'reviewed_at',
    ];

    protected $casts = [
        'score'        => 'decimal:2',
        'quantity'     => 'decimal:2',
        'computed_max' => 'decimal:2',
        'reviewed_at'  => 'datetime',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }


    public function subsection()
    {
        return $this->belongsTo(RubricSubsection::class, 'sub_section_id', 'sub_section_id');
    }

    public function rubricOption()
    {
        return $this->belongsTo(RubricOption::class, 'rubric_option_id');
    }
}
