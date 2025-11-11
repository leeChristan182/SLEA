<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadershipType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'organization_id',
        'rubric_leadership_id',
    ];

    /** Relationship: belongs to an organization */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /** Relationship: links to rubric leadership (for points) */
    public function rubricLeadership()
    {
        return $this->belongsTo(RubricSubsectionLeadership::class, 'rubric_leadership_id');
    }
}
