<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cluster extends Model
{
    protected $fillable = ['name', 'description'];
    
    /**
     * Get the organizations that belong to this cluster.
     */
    public function organizations()
    {
        return $this->hasMany(Organization::class);
    }
}
