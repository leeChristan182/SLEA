<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    // Matches PositionSeeder columns
    protected $fillable = ['name', 'key', 'rank_order', 'is_executive', 'is_elected', 'leadership_type_id'];

    protected $casts = [
        'rank_order'   => 'integer',
        'is_executive' => 'boolean',
        'is_elected'   => 'boolean',
    ];

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_position')
            ->withPivot('alias')
            ->withTimestamps();
    }
}
