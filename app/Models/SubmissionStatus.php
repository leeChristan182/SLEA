<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmissionStatus extends Model
{
    protected $table = 'submission_statuses';

    protected $fillable = [
        'key',        // e.g. pending, in_review, approved, rejected
        'label',      // human-readable label
        'is_final',   // tinyint/bool
        'sort_order', // optional ordering
    ];

    public $timestamps = false;
}
