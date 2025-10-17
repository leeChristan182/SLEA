<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadershipType extends Model
{
    protected $fillable = ['name'];
    
    /**
     * Get the leadership information records associated with this type.
     */
    public function leadershipInformation()
    {
        return $this->hasMany(LeadershipInformation::class);
    }
}
