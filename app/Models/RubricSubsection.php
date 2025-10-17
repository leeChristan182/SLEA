<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RubricSubsection extends Model
{
    protected $table = 'rubric_subsections';
    protected $primaryKey = 'sub_items';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'section_id',
        'sub_section',
        'evidence_needed',
        'order_no',
    ];

    public function section()
    {
        return $this->belongsTo(RubricSection::class, 'section_id', 'section_id');
    }

    public function leadershipPositions()
    {
        return $this->hasMany(RubricSubsectionLeadership::class, 'sub_items', 'sub_items');
    }

    public function submissionRecords()
    {
        return $this->hasMany(SubmissionRecord::class, 'sub_items', 'sub_items');
    }
}
