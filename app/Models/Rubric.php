<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rubric extends Model
{
    use HasFactory;

    protected $fillable = [
        'position_or_title',
        'points',
        'max_points',
        'unit_note',
        'evidence',
        'category',
    ];
}
