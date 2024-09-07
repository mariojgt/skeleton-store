<?php
namespace Skeleton\Store\Enums;

use Mariojgt\SkeletonAdmin\Enums\EnumToArray;

enum PaymentStatus: string
{
    use EnumToArray;
    case pending = 'Pending';
    case completed = 'Completed';
    case refunded = 'Refunded';
    case failed = 'Failed';
    case processing = 'Processing';
}
