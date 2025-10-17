<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'cluster_id'];
    
    /**
     * Get the cluster that this organization belongs to.
     */
    public function cluster()
    {
        return $this->belongsTo(Cluster::class);
    }
    
    /**
     * Get the leadership information records associated with this organization.
     */
    public function leadershipInformation()
    {
        return $this->hasMany(LeadershipInformation::class);
    }
}
