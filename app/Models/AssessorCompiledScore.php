<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessorCompiledScore extends Model
{
    protected $table = 'assessor_compiled_scores';

    protected $fillable = [
        'student_id',
        'assessor_id',
        'rubric_category_id',
        'total_points',
        'max_points',
        'min_required_points',
        'category_result',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(\App\Models\RubricCategory::class, 'rubric_category_id');
    }
}
