<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class AdminAccount extends Authenticatable
{
    use Notifiable;

    // ✅ Use the existing table 'admin_profiles'
    protected $table = 'admin_profiles';
    protected $primaryKey = 'admin_id';
    public $timestamps = true;

    protected $fillable = [
        'admin_id',
        'email_address',
        'first_name',
        'last_name',
        'contact_number',
        'position',
    ];

    protected $hidden = ['password'];

    // ✅ Fetch the actual password from 'admin_passwords'
    public function getAuthPassword()
    {
        return DB::table('admin_passwords')
            ->where('admin_id', $this->admin_id)
            ->value('password_hashed');
    }

    public function passwordRecord()
    {
        return $this->hasOne(AdminPassword::class, 'admin_id');
    }
}
