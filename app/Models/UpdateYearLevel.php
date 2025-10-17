<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpdateYearLevel extends Model
{
    protected $table = 'update_year_level';
    protected $primaryKey = 'update_year_id';
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'old_year_level',
        'new_year_level',
        'date_year_level_changed',
    ];

    public function academic(): BelongsTo
    {
        return $this->belongsTo(AcademicInformation::class, 'student_id', 'student_id');
    }
    public function yearLevelUpdates()
{
    return $this->hasMany(UpdateYearLevel::class, 'student_id');
}

}
