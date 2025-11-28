<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SystemMonitoringAndLog extends Model
{
    use HasFactory;

    protected $table = 'system_monitoring_and_logs';
    protected $primaryKey = 'log_id';
    public $timestamps = false;

    protected $fillable = [
        'user_role',
        'user_name',
        'activity_type',
        'description',
        'created_at',
    ];

    public static function record(string $userRole, string $userName, string $activityType, ?string $description = null): ?self
    {
        // Check if table exists before trying to log
        if (!Schema::hasTable('system_monitoring_and_logs')) {
            // Silently fail if table doesn't exist (prevents errors during migration)
            return null;
        }

        try {
        return static::create([
            'user_role'     => $userRole,
            'user_name'     => $userName,
            'activity_type' => $activityType,
            'description'   => $description,
            'created_at'    => now(),
        ]);
        } catch (\Exception $e) {
            // Log error but don't break the application
            \Log::warning('Failed to create system monitoring log: ' . $e->getMessage());
            return null;
        }
    }
}
