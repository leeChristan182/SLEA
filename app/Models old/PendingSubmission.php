<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingSubmission extends Model
{
    protected $table = 'pending_submissions';
    protected $primaryKey = 'pending_sub_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'subrec_id',
        'assessor_id',
        'action',
        'score_points',
        'remarks',
        'pending_queued_date',
        'assessed_date',
    ];

    protected $casts = [
        'pending_queued_date' => 'datetime',
        'assessed_date'       => 'datetime',
        'score_points'        => 'decimal:2',
    ];

    // Relationships
    public function submission()
    {
        return $this->belongsTo(\App\Models\SubmissionRecord::class, 'subrec_id', 'subrec_id');
    }
}
