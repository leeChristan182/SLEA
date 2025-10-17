<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CorSubmission extends Model
{
    protected $table = 'cor_submissions';
    protected $primaryKey = 'cor_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'student_id','file_name','file_type','file_size',
        'upload_date','academic_year','status','storage_path',
    ];

    protected $casts = [
        'upload_date' => 'datetime',
        'file_size'   => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $cor) {
            $cor->upload_date ??= now();
            $cor->academic_year ??= self::computeAcademicYear($cor->upload_date);
        });
    }

    public static function computeAcademicYear($date): string
    {
        $d = $date instanceof Carbon ? $date : Carbon::parse($date);
        $startYear = $d->month >= 8 ? $d->year : $d->year - 1;
        return $startYear . '-' . ($startYear + 1);
    }
}
