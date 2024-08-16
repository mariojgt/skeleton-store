<?php
namespace Skeleton\Store\Enums;

use Mariojgt\SkeletonAdmin\Enums\EnumToArray;

enum ProductType: string
{
    use EnumToArray;
    case digital = 'Digital';
    case physical = 'Physical';
    case service = 'Service';
    case other = 'Other';
}
