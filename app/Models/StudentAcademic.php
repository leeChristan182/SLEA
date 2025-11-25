<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentAcademic extends Model
{
    use HasFactory;

    protected $table = 'student_academic';

    protected $fillable = [
        'user_id',
        'student_number',
        'college_id',
        'program_id',
        'major_id',
        'year_level',
        'graduate_prior',
        'expected_grad_year',
        'eligibility_status',
        'revalidated_at',
        'ready_for_rating',
        'ready_for_rating_at',
        'slea_application_status',
    ];

    protected $casts = [
        'graduate_prior'     => 'integer',
        'expected_grad_year' => 'integer',
        'revalidated_at'     => 'datetime',

        'ready_for_rating'        => 'boolean',
        'ready_for_rating_at'     => 'datetime',

    ];

    /* ------------ Relationships ------------ */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function major()
    {
        return $this->belongsTo(Major::class);
    }
    /**
     * Is this student in their expected graduation year?
     */
    /* ------------ SLEA Helpers ------------ */

    public function isGraduatingThisYear(): bool
    {
        // Primary rule: based on year_level (4th year)
        $level = strtolower(trim((string) $this->year_level));

        $isFourthYear = in_array($level, [
            '4th year',
            '4th yr',
            'fourth year',
            '4',
            'year 4',
        ], true);

        if ($isFourthYear) {
            return true;
        }

        // Fallback: based on expected graduation year (if you want to keep it)
        if ($this->expected_grad_year) {
            return (int) $this->expected_grad_year === (int) now()->year;
        }

        return false;
    }

    public function hasSleaApplication(): bool
    {
        return $this->ready_for_rating || !empty($this->slea_application_status);
    }

    public function canMarkReadyForSlea(): bool
    {
        // Must be graduating (4th year / equivalent)
        if (! $this->isGraduatingThisYear()) {
            return false;
        }

        // Already clicked / in SLEA flow
        if ($this->ready_for_rating) {
            return false;
        }

        if (! empty($this->slea_application_status)) {
            return false;
        }

        return true;
    }

    public function markReadyForSlea(): void
    {
        $this->ready_for_rating        = true;
        $this->ready_for_rating_at     = now();
        $this->slea_application_status = 'ready_for_assessor';
        $this->save();
    }
    public function markReadyForAdminReview(): void
    {
        // enum from slea_application_statuses table
        $this->slea_application_status = 'for_admin_review';
        $this->save();
    }
    // in StudentAcademic.php

    public function markAwarded()
    {
        $this->slea_application_status = 'awarded';
        $this->save();
    }

    public function markNotQualified()
    {
        $this->slea_application_status = 'not_qualified';
        $this->save();
    }
}
