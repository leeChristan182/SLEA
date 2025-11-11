<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SubmissionRecord extends Model
{
    protected $table = 'submission_records';
    protected $primaryKey = 'subrec_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'student_id',
        'leadership_id',
        'category_id',
        'section_id',
        'sub_items',
        'activity_title',
        'activity_type',
        'activity_role',
        'activity_date',
        'organizing_body',
        'term',
        'issued_by',
        'note',
        'document_type',
        'document_title',
        'document_title_path',
        'datedocu_submitted',
    ];

    protected $casts = [
        'activity_date'       => 'datetime',
        'datedocu_submitted'  => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(StudentPersonalInformation::class, 'student_id', 'student_id');
    }

    public function leadership()
    {
        return $this->belongsTo(LeadershipInformation::class, 'leadership_id', 'leadership_id');
    }

    public function category()
    {
        return $this->belongsTo(RubricCategory::class, 'category_id', 'category_id');
    }

    public function section()
    {
        return $this->belongsTo(RubricSection::class, 'section_id', 'section_id');
    }

    public function subsection()
    {
        return $this->belongsTo(RubricSubsection::class, 'sub_items', 'sub_items');
    }

    public function rubricLeadership()
    {
        return $this->belongsTo(RubricSubsectionLeadership::class, 'leadership_id', 'leadership_id');
    }

    // Academic Year helper used by controller
    public static function computeAcademicYear($date): string
    {
        $d = $date instanceof Carbon ? $date : Carbon::parse($date);
        $startYear = $d->month >= 8 ? $d->year : $d->year - 1; // AY starts in August
        return $startYear . '-' . ($startYear + 1);
    }
}
