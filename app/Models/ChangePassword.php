<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangePassword extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'password_id',
        'new_password_hashed',
        'date_pass_changed',
    ];
}
