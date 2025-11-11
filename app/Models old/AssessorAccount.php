<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssessorAccount extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'assessor_accounts';
    protected $primaryKey = 'email_address';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected $fillable = [
        'email_address',
        'admin_id',
        'last_name',
        'first_name',
        'middle_name',
        'position',
        'default_password',
        'dateacc_created',
        'status',
    ];

    protected $hidden = ['default_password'];
    protected $attributes = [
        'status' => 'approved',
    ];
    public function getAuthPassword()
    {
        // ✅ Tell Laravel which column to use for password validation
        return $this->default_password;
    }

    public function admin()
    {
        return $this->belongsTo(AdminAccount::class, 'admin_id', 'admin_id');
    }
}
