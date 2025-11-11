<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $table = 'otp';        // << important (singular)
    protected $primaryKey = 'otp_id';
    public $timestamps = true;

    protected $fillable = ['log_id','otp_code'];

    public function login()
    {
        return $this->belongsTo(\App\Models\LogIn::class, 'log_id', 'log_id');
    }
}
