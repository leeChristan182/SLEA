<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RubricSection extends Model
{
    protected $primaryKey = 'section_id';

    protected $fillable = [
        'category_id',
        'section_key',
        'title',
        'order_no',
        'max_points',
    ];

    /**
     * A section belongs to a rubric category.
     */
    public function category()
    {
        return $this->belongsTo(RubricCategory::class, 'category_id', 'category_id');
    }

    /**
     * A section has many rubric items (used in some scoring rubrics).
     */
    public function items()
    {
        return $this->hasMany(RubricItem::class, 'section_id', 'section_id')
            ->orderBy('order_no');
    }

    /**
     * A section has many subsections (regular, non-leadership).
     */
    public function subsections()
    {
        return $this->hasMany(RubricSubsection::class, 'section_id', 'section_id')
            ->orderBy('order_no');
    }

    public function leadershipPositions()
    {
        return $this->hasMany(RubricSubsectionLeadership::class, 'section_id', 'section_id')
            ->orderBy('position_order', 'asc');
    }
}
