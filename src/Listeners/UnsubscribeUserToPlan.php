<?php

namespace Skeleton\Store\Listeners;

use Skeleton\Store\Enums\DurationType;
use Skeleton\Store\Enums\SubscriptionStatus;
use Skeleton\Store\Events\UserSubscribedToPlan;
use Skeleton\Store\Events\UserUnsubscribedToPlan;

class UnsubscribeUserToPlan
{
    /**
     * Handle the event.
     *
     * @param  UserSubscribedToPlan  $event
     * @return void
     */
    public function handle(UserUnsubscribedToPlan $event)
    {
        // Access the user and plan from the event
        $user = $event->user;
        $plan = $event->plan;

        // Subscribe the user to the plan
        $user->subscriptions()->where('plan_id', $plan->id)->update([
            'status' => SubscriptionStatus::canceled->value,
        ]);
    }
}
