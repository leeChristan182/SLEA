<?php

// app/Models/LeadershipType.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadershipType extends Model
{
    protected $fillable = ['key', 'name', 'domain', 'scope', 'requires_org'];
    protected $casts = ['requires_org' => 'bool'];
}
