<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cluster extends Model
{
    use HasFactory;

    // Seeder writes `key`, table has `name` (and maybe `description`)
    protected $fillable = ['key', 'name', 'description'];

    public function organizations()
    {
        return $this->hasMany(Organization::class);
    }
}
