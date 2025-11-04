<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class College extends Model
{
    protected $table = 'college_programs'; // use your existing table

    public $timestamps = true;

    protected $fillable = [
        'college_name',
        'program_name',
        'major_name',
    ];
}
