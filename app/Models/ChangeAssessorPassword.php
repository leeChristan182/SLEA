<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChangeAssessorPassword extends Model
{
    use HasFactory;

    protected $primaryKey = 'change_pass_id';

    protected $fillable = [
        'email_address',
        'old_password_hashed',
        'new_password_hashed',
        'date_pass_changed',
    ];

    public function account()
    {
        return $this->belongsTo(AssessorAccount::class, 'email_address', 'email_address');
    }
}
