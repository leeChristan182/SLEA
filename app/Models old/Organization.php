<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cluster_id',
        'description',
    ];

    public function cluster()
    {
        return $this->belongsTo(Cluster::class);
    }

    /**
     * Computed accessor for combined name (not stored in DB)
     */
    public function getCombinedNameAttribute()
    {
        return ($this->cluster?->name ? $this->cluster->name . ' - ' : '') . $this->name;
    }
    public function leadershipTypes()
    {
        return $this->hasMany(LeadershipType::class);
    }
}
