<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    // Seeders also set slug/domain/scope_level/is_active/parent_id
    protected $fillable = [
        'name',
        'slug',
        'cluster_id',
        'parent_id',
        'domain',
        'scope_level',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function cluster()
    {
        return $this->belongsTo(Cluster::class);
    }

    public function positions()
    {
        // Pivot table: organization_position (with `alias`)
        return $this->belongsToMany(Position::class, 'organization_position')
            ->withPivot('alias')
            ->withTimestamps();
    }

    public function getCombinedNameAttribute(): string
    {
        $prefix = $this->cluster?->name ? $this->cluster->name . ' - ' : '';
        return $prefix . $this->name;
    }
}
