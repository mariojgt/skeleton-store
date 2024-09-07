<?php

namespace Skeleton\Store\Listeners;

use Skeleton\Store\Enums\DurationType;
use Skeleton\Store\Enums\SubscriptionStatus;
use Skeleton\Store\Events\UserSubscribedToPlan;

class SubscribeUserToPlan
{
    /**
     * Handle the event.
     *
     * @param  UserSubscribedToPlan  $event
     * @return void
     */
    public function handle(UserSubscribedToPlan $event)
    {
        // Access the user and plan from the event
        $user = $event->user;
        $plan = $event->plan;

        if ($plan->duration_type === DurationType::days) {
            $endDate = now()->addDays($plan->duration);
        } elseif ($plan->duration_type === DurationType::weeks) {
            $endDate = now()->addWeeks($plan->duration);
        } elseif ($plan->duration_type === DurationType::months) {
            $endDate = now()->addMonths($plan->duration);
        } elseif ($plan->duration_type === DurationType::years) {
            $endDate = now()->addYears($plan->duration);
        } else {
            throw new \Exception('Invalid duration type');
        }

        // Subscribe the user to the plan
        $user->subscriptions()->create([
            'plan_id'    => $plan->id,
            'start_date' => now(),
            'end_date'   => $endDate,
            'status'     => SubscriptionStatus::active->value,
        ]);
    }
}
