<?php

namespace Skeleton\Store\Listeners;

use Skeleton\Store\Enums\SubscriptionStatus;
use Skeleton\Store\Events\UserUnsubscribedToPlan;
use Mariojgt\SkeletonAdmin\Notifications\GenericNotification;

class UnsubscribeUserToPlan
{
    /**
     * Handle the event.
     *
     * @param UserUnsubscribedToPlan $event
     * @return void
     */
    public function handle(UserUnsubscribedToPlan $event)
    {
        // Access the user and plan from the event
        $user = $event->user;
        $plan = $event->plan;
        $paymentGateway = $event->paymentGateway;

        // Query subscriptions - optionally filter by payment gateway if provided
        $query = $user->subscriptions()->where('plan_id', $plan->id);

        if ($paymentGateway) {
            $query->where('payment_gateway', $paymentGateway);
        }

        // Update status to canceled
        $query->update([
            'status' => SubscriptionStatus::canceled->value,
        ]);

        // Notify user
        $this->notifyUser($user, $plan);
    }

    /**
     * Send notification to user about cancellation
     *
     * @param mixed $user
     * @param mixed $plan
     * @return void
     */
    protected function notifyUser($user, $plan): void
    {
        $user->notify(
            new GenericNotification(
                'Plan ' . $plan->name . ' canceled',
                'info',
                'Your subscription to the plan ' . $plan->name . ' has been canceled',
                'icon',
                true
            )
        );
    }
}
