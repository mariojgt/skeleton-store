<?php

namespace Skeleton\Store\Listeners;

use Skeleton\Store\Models\Payment;
use Skeleton\Store\Enums\DurationType;
use Skeleton\Store\Enums\SubscriptionStatus;
use Skeleton\Store\Events\UserSubscribedToPlan;
use Mariojgt\SkeletonAdmin\Notifications\GenericNotification;

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
        $payment = $event->payment ?? [];
        $autoRenew = $event->autoRenew;

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
        $subscription = $user->subscriptions()->create([
            'plan_id'    => $plan->id,
            'start_date' => now(),
            'end_date'   => $endDate,
            'status'     => SubscriptionStatus::active->value,
            'auto_renew' => $autoRenew,
        ]);


        // Creating a new payment associated with the subscription
        $payment = new Payment([
            'user_id'        => $payment['user_id'],
            'amount'         => $payment['amount'],
            'payment_method' => $payment['payment_method'],
            'status'         => $payment['status'],
            'transaction_id' => $payment['transaction_id'],
        ]);

        // Save the payment using the polymorphic relationship
        $subscription->payments()->save($payment);

        $user->notify(
            new GenericNotification(
                'Plan' . $plan->name . ' subscribed',
                'success',
                'You have successfully subscribed to the plan ' . $plan->name,
                'icon'
            )
        );
    }
}
