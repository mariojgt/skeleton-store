<?php

namespace Skeleton\Store\Models;

use Skeleton\Store\Enums\SubscriptionStatus;
use Mariojgt\SkeletonAdmin\Models\User as SkeletonUser;

class User extends SkeletonUser
{
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->subscriptions()->where('status', SubscriptionStatus::active)->orderBy('end_date', 'desc')->first();
    }

    public function stripeSessions()
    {
        return $this->hasMany(StripeSession::class);
    }
}
