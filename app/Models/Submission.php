<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    protected $table = 'submissions';

    protected $fillable = [
        'user_id',
        'leadership_id',
        'rubric_category_id',
        'rubric_section_id',
        'rubric_subsection_id',

        'activity_title',
        'description',
        'attachments',
        'meta',

        'status',
        'remarks',
        'submitted_at',
    ];

    protected $casts = [
        'attachments'  => 'array',
        'meta'         => 'array',
        'submitted_at' => 'datetime',
    ];

    /* ============
     | RELATIONS
     * ============ */

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function leadership(): BelongsTo
    {
        return $this->belongsTo(\App\Models\LeadershipInformation::class, 'leadership_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(\App\Models\RubricCategory::class, 'rubric_category_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(\App\Models\RubricSection::class, 'rubric_section_id', 'section_id');
    }

    public function subsection(): BelongsTo
    {
        return $this->belongsTo(\App\Models\RubricSubsection::class, 'rubric_subsection_id', 'sub_section_id');
    }

    public function statusRef(): BelongsTo
    {
        // submission_statuses.key ←→ submissions.status
        return $this->belongsTo(\App\Models\SubmissionStatus::class, 'status', 'key');
    }
}
