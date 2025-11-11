<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'leadership_type_id',
        'name',
    ];

    /**
     * Each position belongs to a specific leadership type.
     */
    public function leadershipType()
    {
        return $this->belongsTo(LeadershipType::class);
    }
}
