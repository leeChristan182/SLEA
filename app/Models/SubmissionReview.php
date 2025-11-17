<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionReview extends Model
{
    protected $table = 'submission_reviews';

    protected $fillable = [
        'submission_id',
        'assessor_id',
        'sub_section_id',
        'rubric_option_id', // adjust to your actual column name
        'decision',         // e.g. approved, rejected, returned, flagged
        'total_points',     // or score column name in your migration
        'comments',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    public function subsection(): BelongsTo
    {
        return $this->belongsTo(\App\Models\RubricSubsection::class, 'sub_section_id', 'sub_section_id');
    }

    public function rubricOption(): BelongsTo
    {
        return $this->belongsTo(\App\Models\RubricOption::class, 'rubric_option_id');
    }
}
