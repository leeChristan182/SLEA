<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RubricCategory extends Model
{
    protected $table = 'rubric_categories';
    protected $primaryKey = 'category_id';
    public $incrementing = true;

    protected $fillable = [
        'key',          // ✅ include this
        'title',
        'description',
        'max_points',
        'order_no',
    ];

    public function sections()
    {
        return $this->hasMany(RubricSection::class, 'category_id', 'category_id')
            ->orderBy('order_no');
    }
}
