<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $table = 'history';
    protected $primaryKey = 'history_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'pending_sub_id',
        'student_id',
        'action',
    ];

    public function pendingSubmission()
    {
        return $this->belongsTo(PendingSubmission::class, 'pending_sub_id', 'pending_sub_id');
    }

    public function student()
    {
        return $this->belongsTo(AcademicInformation::class, 'student_id', 'student_id');
    }
}
