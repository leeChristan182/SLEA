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

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date'        => 'date',
    ];

    // Auto-hash when a plain string is assigned
    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn($value) => $value && !password_get_info($value)['algo'] ? bcrypt($value) : $value
        );
    }

    // Full name accessor
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . ($this->middle_name ? $this->middle_name . ' ' : '') . $this->last_name);
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
            'status' => $this->status === self::STATUS_DISABLED ? self::STATUS_APPROVED : self::STATUS_DISABLED
        ]);
    }
    // ...
    public function awardLocked(): bool
    {
        if (! $this->isStudent() || ! $this->isApproved()) return false;

        $a = $this->studentAcademic()->first();  // use relation if you have it
        if (! $a) return true; // no academic info => lock until provided

        $exceededYear = $a->expected_grad_year && now()->year > (int) $a->expected_grad_year;
        $needsGate = in_array((string)$a->eligibility_status, ['needs_revalidation', 'under_review', 'ineligible'], true);

        return $exceededYear || $needsGate;
    }


    // --- Relationships (uncomment as you add models) ---
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
}
