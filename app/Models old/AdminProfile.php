<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminProfile extends Model
{
    use HasFactory;

    protected $table = 'admin_profiles';
    protected $primaryKey = 'admin_id';
    public $timestamps = true;

    protected $fillable = [
        'first_name',
        'last_name',
        'email_address',
        'contact_number',
        'position',
        'profile_picture_path',
    ];
    public function assessors()
    {
        return $this->hasMany(AssessorAccount::class, 'admin_id', 'admin_id');
    }
}
