<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManageAccount extends Model
{
    protected $fillable = [
        'email_address', 'admin_id', 'user_type',
        'account_status', 'last_login', 'action'
    ];

    protected $casts = [
        'last_login' => 'datetime',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(AdminProfile::class, 'admin_id');
    }

    public function loginRecords(): HasMany
    {
        return $this->hasMany(LogIn::class, 'email_address', 'email_address');
    }

    /**
     * Get recent login activity for this account
     */
    public function getRecentLogins($limit = 10)
    {
        return $this->loginRecords()
            ->latest('login_datetime')
            ->limit($limit)
            ->get();
    }

    /**
     * Check if account is active
     */
    public function isActive(): bool
    {
        return $this->account_status === 'active';
    }

    /**
     * Get login statistics
     */
    public function getLoginStats()
    {
        $totalLogins = $this->loginRecords()->count();
        $recentLogins = $this->loginRecords()
            ->where('login_datetime', '>=', now()->subDays(30))
            ->count();
        
        return [
            'total_logins' => $totalLogins,
            'recent_logins' => $recentLogins,
            'last_login' => $this->last_login,
            'account_status' => $this->account_status,
        ];
    }

    /**
     * Scope for active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('account_status', 'active');
    }

    /**
     * Scope for accounts by user type
     */
    public function scopeByUserType($query, $userType)
    {
        return $query->where('user_type', $userType);
    }
}
