<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionOversight extends Model
{
    use HasFactory;

    protected $primaryKey = 'sub_oversight_id';

    protected $fillable = [
        'pending_sub_id',
        'admin_id',
        'submission_status',
        'flag',
        'action',
    ];

    // Relationships
    public function pendingSubmission()
    {
        return $this->belongsTo(PendingSubmission::class, 'pending_sub_id');
    }

    public function admin()
    {
        return $this->belongsTo(AdminProfile::class, 'admin_id', 'admin_id');
    }
}
