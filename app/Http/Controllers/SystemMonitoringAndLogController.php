class SystemMonitoringAndLog extends Model
{
protected $primaryKey = 'logs_id';
public $incrementing = true;
protected $keyType = 'int';

protected $fillable = [
'log_id',
'user_role',
'user_name',
'activity_type',
'description',
'created_at',
];

public function login()
{
return $this->belongsTo(LogIn::class, 'log_id', 'log_id');
}

/**
* Static helper for quick logging anywhere
*/
public static function record($role, $name, $type, $desc, $logId = null)
{
self::create([
'log_id' => $logId,
'user_role' => $role,
'user_name' => $name,
'activity_type' => $type,
'description' => $desc,
'created_at' => now(),
]);
}
}