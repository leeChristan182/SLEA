<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Major extends Model
{
    use HasFactory;

    protected $table = 'majors';

    protected $fillable = [
        'major_name',     // ex: "Software Engineering"
        'program_id',
        'code',           // optional
    ];

    public $timestamps = true;

    // Relationships
    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    // Scopes
    public function scopeAlphabetical($q)
    {
        return $q->orderBy('major_name');
    }
}
