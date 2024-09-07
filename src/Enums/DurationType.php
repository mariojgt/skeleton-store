<?php
namespace Skeleton\Store\Enums;

use Mariojgt\SkeletonAdmin\Enums\EnumToArray;

enum DurationType: string
{
    use EnumToArray;
    case days = 'Days';
    case weeks = 'Weeks';
    case months = 'Months';
    case years = 'Years';
}
