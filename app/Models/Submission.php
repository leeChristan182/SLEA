<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'application_status',
        'remarks',
        'submitted_at',
    ];

    protected $casts = [
        'attachments'  => 'array',
        'meta'         => 'array',
        'submitted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leadership(): BelongsTo
    {
        return $this->belongsTo(LeadershipInformation::class, 'leadership_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RubricCategory::class, 'rubric_category_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(RubricSection::class, 'rubric_section_id', 'section_id');
    }

    public function subsection(): BelongsTo
    {
        return $this->belongsTo(RubricSubsection::class, 'rubric_subsection_id', 'sub_section_id');
    }

    public function statusRef(): BelongsTo
    {
        return $this->belongsTo(SubmissionStatus::class, 'status', 'key');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(History::class, 'submission_id');
    }

    public function latestHistory(): HasOne
    {
        return $this->hasOne(History::class, 'submission_id')->latestOfMany();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(SubmissionReview::class, 'submission_id');
    }
}
