<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Program extends Model
{
    use HasFactory;

    protected $table = 'programs';

    protected $fillable = [
        'program_name',   // ex: "BS in Computer Science"
        'college_id',
        'code',           // optional
    ];

    public $timestamps = true;

    // Relationships
    public function college()
    {
        return $this->belongsTo(College::class, 'college_id');
    }

    public function majors()
    {
        return $this->hasMany(Major::class, 'program_id');
    }

    // Scopes
    public function scopeNamed($q, string $name)
    {
        return $q->where('program_name', $name);
    }

    public function scopeAlphabetical($q)
    {
        return $q->orderBy('program_name');
    }
}
