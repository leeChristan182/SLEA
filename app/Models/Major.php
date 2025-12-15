<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Major extends Model
{
    use HasFactory;

    protected $table = 'majors';

    protected $fillable = [
        'name',           // Database column is 'name'
        'program_id',
        'code',           // optional
    ];

    public $timestamps = true;

    // Accessor for major_name (maps to 'name' column)
    public function getMajorNameAttribute()
    {
        return $this->attributes['name'] ?? null;
    }

    // Mutator for major_name (maps to 'name' column)
    public function setMajorNameAttribute($value)
    {
        $this->attributes['name'] = $value;
    }

    // Relationships
    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    // Scopes
    public function scopeAlphabetical($q)
    {
        return $q->orderBy('name');
    }
}
