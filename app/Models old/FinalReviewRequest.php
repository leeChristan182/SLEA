<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalReviewRequest extends Model
{
    use HasFactory;

    protected $primaryKey = 'final_review_id';

    protected $fillable = [
        'submission_id',
        'action',
        'request_date',
        'status',
        'remarks',
    ];

    // Relationship
    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }
}
