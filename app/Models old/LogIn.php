<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogIn extends Model
{
    protected $table = 'log_in';
    protected $primaryKey = 'log_id';

    protected $fillable = [
        'email_address',
        'user_role',
        'login_datetime',
    ];

    protected $casts = [
        'login_datetime' => 'datetime',
    ];
    public function otps()
    {
        return $this->hasMany(\App\Models\Otp::class, 'log_id', 'log_id');
    }
    public function logs()
    {
        return $this->hasMany(\App\Models\SystemMonitoringAndLog::class, 'log_id', 'log_id');
    }
}
