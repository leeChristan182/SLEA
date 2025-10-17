<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessorAccount extends Model
{
    use HasFactory;

    protected $table = 'assessor_accounts';
    protected $primaryKey = 'email_address';  // string PK
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
    ];

    public function admin()
    {
        return $this->belongsTo(AdminProfile::class, 'admin_id', 'admin_id');
    }
}
