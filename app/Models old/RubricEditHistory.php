<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RubricEditHistory extends Model
{
    protected $table = 'rubric_edit_history';
    protected $primaryKey = 'edit_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'admin_id',
        'sub_items',
        'edit_timestamp',
        'changes_made',
        'field_edited',
    ];

    public function subsection()
    {
        return $this->belongsTo(RubricSubsectionLeadership::class, 'sub_items', 'sub_items');
    }
}
