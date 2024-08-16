<?php
namespace Skeleton\Store\Enums;

use Mariojgt\SkeletonAdmin\Enums\EnumToArray;

enum PriceType: string
{
    use EnumToArray;
    case free = 'Free';
    case paid = 'Paid';
    case subscription = 'Subscription';
    case donation = 'Donation';
    case custom = 'Custom';
    case premium = 'Premium';
    case other = 'Other';
}
