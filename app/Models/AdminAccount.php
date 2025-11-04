<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class AdminAccount extends Authenticatable
{
    use Notifiable;

    protected $table = 'admin_profiles';
    protected $primaryKey = 'admin_id';
    public $timestamps = true;

    protected $fillable = [
        'email_address',
        'first_name',
        'last_name',
        'contact_number',
        'position',
        'status',
    ];

    protected $hidden = ['password'];
    protected $attributes = [
        'status' => 'approved',
    ];
    // 🔑 Use 'email_address' as login field
    public function getAuthIdentifierName()
    {
        return 'email_address';
    }

    // 🔒 Fetch hashed password from admin_passwords
    public function getAuthPassword()
    {
        return DB::table('admin_passwords')
            ->where('admin_id', $this->admin_id)
            ->value('password_hashed');
    }

    // 🧩 Relationships
    public function passwordRecord()
    {
        return $this->hasOne(AdminPassword::class, 'admin_id');
    }

    public function profile()
    {
        return $this->hasOne(AdminProfile::class, 'admin_id');
    }
}
