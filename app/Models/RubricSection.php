<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RubricSection extends Model
{
    protected $table = 'rubric_sections';
    protected $primaryKey = 'section_id';

    protected $fillable = [
        'category_id',
        'key',
        'title',
        'evidence',
        'aggregation',
        'aggregation_params',
        'notes',
        'max_points',
        'order_no',
    ];

    public function category()
    {
        return $this->belongsTo(RubricCategory::class, 'category_id', 'id');
    }

    public function subsections()
    {
        return $this->hasMany(RubricSubsection::class, 'section_id', 'section_id')
            ->orderBy('order_no');
    }
}
