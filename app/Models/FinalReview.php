<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalReview extends Model
{
    protected $fillable = [
        'assessor_final_review_id',
        'admin_id',
        'decision',   // approved | not_qualified  (enum "final_review_decisions")
        'remarks',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function assessorFinalReview(): BelongsTo
    {
        return $this->belongsTo(AssessorFinalReview::class, 'assessor_final_review_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
