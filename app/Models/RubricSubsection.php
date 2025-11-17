<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RubricSubsection extends Model
{
    use HasFactory;

    protected $table = 'rubric_subsections';
    protected $primaryKey = 'sub_section_id';   // ✅ matches seeder
    public $incrementing = true;

    protected $fillable = [
        'section_id',
        'key',
        'sub_section',
        'evidence_needed',
        'max_points',
        'cap_points',
        'scoring_method',
        'unit',
        'score_params',
        'notes',
        'order_no',
    ];

    public function section()
    {
        return $this->belongsTo(RubricSection::class, 'section_id', 'section_id');
    }

    public function options()
    {
        return $this->hasMany(RubricOption::class, 'sub_section_id', 'sub_section_id')
            ->orderBy('order_no');
    }
}
