<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RubricOption extends Model
{
    use HasFactory;

    protected $table = 'rubric_options';

    protected $fillable = [
        'sub_section_id',
        'code',
        'label',
        'points',
        'order_no',
    ];

    public function subsection()
    {
        return $this->belongsTo(RubricSubsection::class, 'sub_section_id', 'sub_section_id');
    }
}
