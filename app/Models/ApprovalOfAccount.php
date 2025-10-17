<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalOfAccount extends Model
{
    protected $table = 'approval_of_accounts';
    protected $primaryKey = 'student_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public function academicInformation()
    {
        return $this->belongsTo(AcademicInformation::class, 'student_id', 'student_id');
    }
}
