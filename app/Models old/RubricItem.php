<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RubricItem extends Model
{
    protected $primaryKey = 'item_id';
    protected $fillable = ['section_id', 'name', 'points', 'max_points', 'evidence', 'notes', 'order_no'];

    public function section()
    {
        return $this->belongsTo(RubricSection::class, 'section_id', 'section_id');
    }
}
