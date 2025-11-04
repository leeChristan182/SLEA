<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RubricSubsection extends Model
{
    protected $table = 'rubric_subsections';
    protected $primaryKey = 'sub_items';
    public $incrementing = true;

    protected $fillable = [
        'section_id',
        'sub_section',
        'evidence_needed',
        'max_points',
        'notes',
        'order_no',
    ];

    public function leadershipPositions()
    {
        return $this->hasMany(RubricSubsectionLeadership::class, 'sub_section_id', 'sub_items');
    }
}
