<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RubricSubsectionLeadership extends Model
{
    protected $table = 'rubric_subsection_leadership';
    protected $primaryKey = 'id'; // matches your migration’s $table->id()

    protected $fillable = [
        'section_id',
        'position',
        'points',
        'position_order',
    ];

    /**
     * Each leadership position belongs to a rubric section.
     */
    public function subsection()
    {
        return $this->belongsTo(RubricSubsection::class, 'sub_section_id', 'sub_items');
    }

    public function section()
    {
        return $this->belongsTo(RubricSection::class, 'section_id', 'section_id');
    }


    /**
     * Each leadership position can have multiple edit history records.
     */
    public function edits()
    {
        return $this->hasMany(RubricEditHistory::class, 'leadership_id', 'id');
    }

    /**
     * Each leadership position can have multiple submission records.
     */
    public function submissionRecords()
    {
        return $this->hasMany(SubmissionRecord::class, 'leadership_id', 'id');
    }
    public function leadershipType()
    {
        return $this->belongsTo(LeadershipType::class, 'leadership_type_id');
    }
}
