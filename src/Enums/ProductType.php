<?php
namespace Skeleton\Store\Enums;

use Mariojgt\SkeletonAdmin\Enums\EnumToArray;

enum ProductType: string
{
    use EnumToArray;
    case digital = 'Digital';
    case physical = 'Physical';
    case service = 'Service';
    case project_templates = 'Project Templates';
    case other = 'Other';
}
