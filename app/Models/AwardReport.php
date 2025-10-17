<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AwardReport extends Model
{
    use HasFactory;

    protected $primaryKey = 'award_id';

    protected $fillable = [
        'review_id',
        'admin_id',
        'action',
        'award_type',
        'award_date',
        'remarks',
    ];

    public function finalReview()
    {
        return $this->belongsTo(FinalReview::class, 'review_id');
    }
}
