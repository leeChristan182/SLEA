<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class College extends Model
{
    use HasFactory;

    protected $table = 'colleges';

    protected $fillable = [
        'college_name',   // ex: "College of Arts and Sciences"
        'code',           // optional short code, if you have it
    ];

    public $timestamps = true;

    // Relationships
    public function programs()
    {
        return $this->hasMany(Program::class, 'college_id');
    }

    // Scopes
    public function scopeNamed($q, string $name)
    {
        return $q->where('college_name', $name);
    }

    public function scopeAlphabetical($q)
    {
        return $q->orderBy('college_name');
    }
}
