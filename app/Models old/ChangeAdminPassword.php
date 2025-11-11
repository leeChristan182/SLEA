<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangeAdminPassword extends Model
{
    protected $fillable = [
        'admin_id', 'old_password_hashed', 'password_hashed', 'date_pass_changed'
    ];

    public function admin() {
        return $this->belongsTo(AdminProfile::class, 'admin_id');
    }
}
