<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $table = 'submissions';
    protected $primaryKey = 'submission_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'pending_sub_id',
        'assessor_id',
        'action',
    ];

    public function pending()
    {
        return $this->belongsTo(\App\Models\PendingSubmission::class, 'pending_sub_id', 'pending_sub_id');
    }
}
