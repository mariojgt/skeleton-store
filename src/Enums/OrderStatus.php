<?php
namespace Skeleton\Store\Enums;

use Mariojgt\SkeletonAdmin\Enums\EnumToArray;

enum OrderStatus: string
{
    use EnumToArray;
    case completed = 'Completed';
    case pending = 'Pending';
    case processing = 'Processing';
    case on_hold = 'On hold';
    case cancelled = 'Cancelled';
    case refunded = 'Refunded';
    case failed = 'Failed';
    case trash = 'Trash';
    case draft = 'Draft';
}
