<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemMonitoringAndLog extends Model
{
    use HasFactory;

    protected $table = 'system_monitoring_and_logs';
    protected $primaryKey = 'log_id';
    public $timestamps = false; // Only created_at exists

    protected $fillable = [
        'user_role',
        'user_name',
        'activity_type',
        'description',
        'created_at',
    ];
}
