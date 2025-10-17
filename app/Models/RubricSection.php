<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RubricSection extends Model
{
    protected $table = 'rubric_sections';
    protected $primaryKey = 'section_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['category_id','title','order_no'];

    public function category()
    {
        return $this->belongsTo(RubricCategory::class, 'category_id', 'category_id');
    }

    public function subsections()
    {
        return $this->hasMany(RubricSubsection::class, 'section_id', 'section_id');
    }
}
