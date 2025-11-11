<?php

namespace App\Services;

use App\Models\RubricSubsectionLeadership;
use App\Models\RubricSubsectionAcademic;
use App\Models\RubricSubsectionAwards;
use App\Models\RubricSubsectionCommunity;
use App\Models\RubricSubsectionConduct;

class RubricService
{
    public static function getCategoryModel($category)
    {
        return match ($category) {
            'leadership' => RubricSubsectionLeadership::class,
            'academic' => RubricSubsectionAcademic::class,
            'awards' => RubricSubsectionAwards::class,
            'community' => RubricSubsectionCommunity::class,
            'conduct' => RubricSubsectionConduct::class,
            default => null,
        };
    }

    public static function getAllCategories()
    {
        return [
            'leadership' => 'I. Leadership Excellence',
            'academic' => 'II. Academic Excellence',
            'awards' => 'III. Awards/Recognition',
            'community' => 'IV. Community Involvement',
            'conduct' => 'V. Good Conduct',
        ];
    }
}
