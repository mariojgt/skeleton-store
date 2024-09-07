<?php
namespace Skeleton\Store\Enums;

use Mariojgt\SkeletonAdmin\Enums\EnumToArray;

enum SubscriptionStatus: string
{
    use EnumToArray;
    case active = 'Active';
    case pending = 'Pending';
    case canceled = 'Canceled';
    case expired = 'Expired';
    case trial = 'Trial';
    case paused = 'Paused';
    case unpaid = 'Unpaid';
    case completed = 'Completed';
    case refunded = 'Refunded';
    case failed = 'Failed';
    case processing = 'Processing';
}
