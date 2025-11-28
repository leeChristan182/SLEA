<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RubricCategory extends Model
{
    use HasFactory;

    protected $table = 'rubric_categories';
    // Primary key is 'id' (auto-increment), not 'category_id'
    // The property below is not needed as 'id' is the default

    protected $fillable = [
        'key',
        'title',
        'description',
        'max_points',
        'min_required_points',
        'aggregation',
        'aggregation_params',
        'order_no',
    ];

    public function sections()
    {
        return $this->hasMany(RubricSection::class, 'category_id', 'id')
            ->orderBy('order_no');
    }
}
