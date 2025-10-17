<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessorProfile extends Model
{
    use HasFactory;

    protected $primaryKey = 'assessor_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'assessor_id',
        'email_address',
        'picture_path',
        'date_upload',
    ];

    public function account()
    {
        return $this->belongsTo(AssessorAccount::class, 'email_address', 'email_address');
    }
}
