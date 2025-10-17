<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RubricSubsectionLeadership extends Model
{
    protected $table = 'rubric_subsection_leadership';
    protected $primaryKey = 'leadership_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'sub_items',
        'position',
        'points',
        'position_order',
    ];

    public function subsection()
    {
        return $this->belongsTo(RubricSubsection::class, 'sub_items', 'sub_items');
    }

    public function edits()
    {
        return $this->hasMany(RubricEditHistory::class, 'sub_items', 'sub_items');
    }

    public function submissionRecords()
    {
        return $this->hasMany(SubmissionRecord::class, 'leadership_id', 'leadership_id');
    }
}
