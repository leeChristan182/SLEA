<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RubricCategory extends Model
{
    protected $table = 'rubric_categories';
    protected $primaryKey = 'category_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['title', 'max_points', 'order_no'];

    public function sections()
    {
        return $this->hasMany(RubricSection::class, 'category_id', 'category_id')
                    ->orderBy('order_no');
    }

    public function submissionRecords()
    {
        return $this->hasMany(SubmissionRecord::class, 'category_id', 'category_id');
    }
}
