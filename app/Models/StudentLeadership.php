<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentLeadership extends Model
{
    protected $table = 'student_leaderships';

    protected $fillable = [
        'user_id',
        'leadership_type_id',
        'cluster_id',
        'organization_id',
        'position_id',
        'term',
        'issued_by',
        'leadership_status',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function leadershipType(): BelongsTo
    {
        return $this->belongsTo(LeadershipType::class, 'leadership_type_id');
    }

    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class, 'cluster_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }
}
