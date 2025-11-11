<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminPassword extends Model
{
    protected $table = 'admin_passwords';
    protected $primaryKey = 'password_id';
    public $timestamps = true;

    protected $fillable = [
        'admin_id',
        'password_hashed',
        'date_pass_created',
    ];

    public function admin()
    {
        return $this->belongsTo(AdminAccount::class, 'admin_id');
    }
}
