<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessorInfo extends Model
{
    protected $table = 'assessor_info';

    protected $fillable = [
        'user_id',
        'created_by_admin_id',
        'temporary_password_hash',
        'must_change_password',
        'date_created',
    ];

    protected $casts = [
        'must_change_password' => 'boolean',
        'date_created' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }
}

