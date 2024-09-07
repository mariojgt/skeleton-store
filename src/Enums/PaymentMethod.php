<?php
namespace Skeleton\Store\Enums;

use Mariojgt\SkeletonAdmin\Enums\EnumToArray;

enum PaymentMethod: string
{
    use EnumToArray;
    case stripe = 'Stripe';
}
