<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AssessorFinalReview extends Model
{
    protected $table = 'assessor_final_reviews';

    protected $fillable = [
        'student_id',
        'assessor_id',
        'total_score',
        'max_possible',
        'qualification',   // qualified | unqualified (enum "qualifications")
        'status',          // draft | queued_for_admin | finalized
        'reviewed_at',
        'remarks',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    /**
     * Category-level compiled scores for this assessor + student.
     * NOTE: linked by student_id (assessor filter is done in queries).
     */
    public function compiledScores(): HasMany
    {
        return $this->hasMany(AssessorCompiledScore::class, 'student_id', 'student_id');
    }

    public function finalReview(): HasOne
    {
        return $this->hasOne(FinalReview::class, 'assessor_final_review_id');
    }
}
