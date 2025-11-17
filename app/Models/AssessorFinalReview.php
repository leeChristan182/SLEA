<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessorFinalReview extends Model
{
    protected $table = 'assessor_final_reviews';

    protected $fillable = [
        'student_id',
        'assessor_id',
        'total_score',
        'max_points',
        'status',      // references final_review_statuses.key
        'reviewed_at',
        'remarks',     // if you add one later
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

    public function compiledScores()
    {
        return $this->hasMany(AssessorCompiledScore::class, 'student_id', 'student_id')
            ->where('assessor_id', $this->assessor_id);
    }

    public function finalReview()
    {
        return $this->hasOne(FinalReview::class, 'assessor_final_review_id');
    }
}
