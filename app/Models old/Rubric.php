<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rubric extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'is_active',
    ];

    /**
     * A rubric has many categories (e.g., Leadership, Academic, etc.)
     */
    public function categories()
    {
        return $this->hasMany(RubricCategory::class);
    }
}
