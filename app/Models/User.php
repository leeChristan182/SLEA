<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // --- Roles (match user_roles.key) ---
    public const ROLE_ADMIN    = 'admin';
    public const ROLE_ASSESSOR = 'assessor';
    public const ROLE_STUDENT  = 'student';

    // --- Statuses (match user_statuses.key) ---
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_DISABLED = 'disabled';

    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'email',
        'password',
        'contact',
        'birth_date',
        'profile_picture_path',
        'role',
        'status',
    ];

    // Keep contact & birth_date hidden (from your unmerged version)
    protected $hidden = [
        'password',
        'remember_token',
        'contact',
        'birth_date',
    ];

    protected $casts = [
        'email_verified_at'    => 'datetime',
        'birth_date'           => 'date',
        'otp_last_verified_at' => 'datetime', // IMPORTANT for OTP freshness
    ];

    /**
     * Auto-hash when a plain string is assigned.
     */
    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn($value) =>
            $value && ! password_get_info($value)['algo']
                ? bcrypt($value)
                : $value
        );
    }

    /* ------------ SLEA Helpers ------------ */

    public function sleaAcademic()
    {
        // convenience alias
        return $this->studentAcademic;
    }

    public function isSleaGraduating(): bool
    {
        return $this->studentAcademic
            ? $this->studentAcademic->isGraduatingThisYear()
            : false;
    }

    public function canMarkReadyForSlea(): bool
    {
        return $this->studentAcademic
            ? $this->studentAcademic->canMarkReadyForSlea()
            : false;
    }

    public function markReadyForSlea(): void
    {
        if ($this->studentAcademic) {
            $this->studentAcademic->markReadyForSlea();
        }
    }

    // Full name accessor
    public function getFullNameAttribute(): string
    {
        return trim(
            $this->first_name . ' ' .
                ($this->middle_name ? $this->middle_name . ' ' : '') .
                $this->last_name
        );
    }

    // --- Role helpers ---
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isAssessor(): bool
    {
        return $this->role === self::ROLE_ASSESSOR;
    }

    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    // --- Status helpers ---
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isDisabled(): bool
    {
        return $this->status === self::STATUS_DISABLED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    // --- Scopes ---
    public function scopeRole($q, string $role)
    {
        return $q->where('role', $role);
    }

    public function scopeStatus($q, string $stat)
    {
        return $q->where('status', $stat);
    }

    public function scopeApproved($q)
    {
        return $q->where('status', self::STATUS_APPROVED);
    }

    public function scopeAssessors($q)
    {
        return $q->where('role', self::ROLE_ASSESSOR);
    }

    public function scopeStudents($q)
    {
        return $q->where('role', self::ROLE_STUDENT);
    }

    // --- Transitions ---
    public function approve(): void
    {
        $this->update(['status' => self::STATUS_APPROVED]);
    }

    public function reject(): void
    {
        $this->update(['status' => self::STATUS_REJECTED]);
    }

    public function disable(): void
    {
        $this->update(['status' => self::STATUS_DISABLED]);
    }

    public function enable(): void
    {
        $this->update(['status' => self::STATUS_APPROVED]);
    }

    public function toggle(): void
    {
        $this->update([
            'status' => $this->status === self::STATUS_DISABLED
                ? self::STATUS_APPROVED
                : self::STATUS_DISABLED,
        ]);
    }

    /**
     * Lock awards if student not eligible (uses same logic as login).
     */
    public function awardLocked(): bool
    {
        if (! $this->isStudent() || ! $this->isApproved()) {
            return false;
        }

        // no academic info => lock until provided
        if (! $this->studentAcademic) {
            return true;
        }

        return ! $this->isEligible();
    }

    // --- OTP relationship & helpers ---
    public function otps()
    {
        return $this->hasMany(UserOtp::class);
    }

    public function needsLoginOtp(): bool
    {
        // First time ever = force OTP
        if (is_null($this->otp_last_verified_at)) {
            return true;
        }

        $days = config('auth.otp.login_fresh_days', 30);

        return $this->otp_last_verified_at->lt(now()->subDays($days));
    }

    /**
     * Academic relation alias (optional convenience).
     */
    public function academic()
    {
        return $this->studentAcademic();
    }

    /**
     * Is the student academically eligible for SLEA?
     */
    public function isEligible(): bool
    {
        if (! $this->studentAcademic) {
            return false;
        }

        $a = $this->studentAcademic;

        // 1) Expected grad year window:
        if ($a->expected_grad_year) {
            $gradYear = (int) $a->expected_grad_year;
            if (now()->year > $gradYear) {
                // beyond expected_grad_year → not eligible
                return false;
            }
        }

        // 2) Gate by eligibility_status for revalidation rules
        if (in_array(
            (string) $a->eligibility_status,
            ['needs_revalidation', 'under_review', 'ineligible'],
            true
        )) {
            return false;
        }

        // 3) Otherwise treat as eligible
        return true;
    }

    /**
     * Overall gate used by login / OTP.
     */
    public function canLoginToSlea(): bool
    {
        // Admin / assessor: always allowed
        if (! $this->isStudent()) {
            return true;
        }

        // Student must be approved AND academically eligible
        if (! $this->isApproved()) {
            return false;
        }

        if (! $this->isEligible()) {
            return false;
        }

        return true;
    }

    /**
     * Message shown when login/OTP is blocked.
     */
    public function loginBlockReason(): string
    {
        if (! $this->isStudent()) {
            return 'You are not allowed to log in to the SLEA portal.';
        }

        if (! $this->isApproved()) {
            return 'Your SLEA account is not yet approved. Please wait for OSAS to approve your registration.';
        }

        if (! $this->isEligible()) {
            return 'You are currently not eligible to access SLEA. Please check your academic eligibility or revalidation status with OSAS.';
        }

        return 'You are not allowed to log in to the SLEA portal.';
    }

    // --- Relationships ---
    public function studentAcademic()
    {
        return $this->hasOne(\App\Models\StudentAcademic::class);
    }

    public function submissions()
    {
        return $this->hasMany(\App\Models\Submission::class);
    } // as student

    public function submissionReviews()
    {
        return $this->hasMany(\App\Models\SubmissionReview::class, 'assessor_id');
    } // as assessor

    public function documents()
    {
        return $this->hasMany(\App\Models\UserDocument::class);
    }

    public function assessorInfo()
    {
        return $this->hasOne(\App\Models\AssessorInfo::class);
    }
    /**
     * Get the student’s latest Certificate of Registration (COR).
     */
    public function latestCor()
    {
        return $this->documents()
            ->where('doc_type', 'cor')
            ->latest('created_at')
            ->first();
    }

    /**
     * Does the student have at least one COR uploaded?
     */
    public function hasCor(): bool
    {
        return $this->documents()
            ->where('doc_type', 'cor')
            ->exists();
    }
}
