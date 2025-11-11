<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalReview extends Model
{
    use HasFactory;

    protected $primaryKey = 'review_id';

    protected $fillable = [
        'final_review_id',
        'admin_id',
        'remarks',
        'date_reviewed',
        'action',
        'status',
    ];

    public function awardReports()
    {
        return $this->hasMany(AwardReport::class, 'review_id');
    }
}
