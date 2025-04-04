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
     * @param UserSubscribedToPlan $event
     * @return void
     */
    public function handle(UserSubscribedToPlan $event)
    {
        // Access the user and plan from the event
        $user = $event->user;
        $plan = $event->plan;
        $payment = $event->payment ?? [];
        $autoRenew = $event->autoRenew;
        $subscriptionId = $event->subscriptionId; // Updated from stripeSubscriptionId
        $paymentGateway = $event->paymentGateway;

        // Calculate end date based on duration type
        $endDate = $this->calculateEndDate($plan);

        // Subscribe the user to the plan
        $subscription = $user->subscriptions()->create([
            'plan_id' => $plan->id,
            'start_date' => now(),
            'end_date' => $endDate,
            'status' => SubscriptionStatus::active->value,
            'auto_renew' => $autoRenew,
            'subscription_id' => $subscriptionId, // Updated from stripe_subscription_id
            'payment_gateway' => $paymentGateway,
        ]);

        // Creating a new payment associated with the subscription
        if (!empty($payment)) {
            $this->createPaymentRecord($subscription, $payment);
        }

        // Notify user
        $this->notifyUser($user, $plan);
    }

    /**
     * Calculate subscription end date based on plan duration type
     *
     * @param mixed $plan
     * @return \Carbon\Carbon
     * @throws \Exception
     */
    protected function calculateEndDate($plan)
    {
        if ($plan->duration_type === DurationType::days) {
            return now()->addDays($plan->duration);
        } elseif ($plan->duration_type === DurationType::weeks) {
            return now()->addWeeks($plan->duration);
        } elseif ($plan->duration_type === DurationType::months) {
            return now()->addMonths($plan->duration);
        } elseif ($plan->duration_type === DurationType::years) {
            return now()->addYears($plan->duration);
        } else {
            throw new \Exception('Invalid duration type');
        }
    }

    /**
     * Create a payment record for the subscription
     *
     * @param mixed $subscription
     * @param array $payment
     * @return void
     */
    protected function createPaymentRecord($subscription, array $payment)
    {
        $paymentRecord = new Payment([
            'user_id' => $payment['user_id'],
            'total_amount' => $payment['total_amount'],
            'discount' => $payment['discount'],
            'tax' => $payment['tax'] ?? $payment['discount'], // Fix: changed from discount to tax with fallback
            'payment_method' => $payment['payment_method'],
            'status' => $payment['status'],
            'transaction_id' => $payment['transaction_id'],
        ]);

        // Save the payment using the polymorphic relationship
        $subscription->payments()->save($paymentRecord);
    }

    /**
     * Notify the user about their subscription
     *
     * @param mixed $user
     * @param mixed $plan
     * @return void
     */
    protected function notifyUser($user, $plan)
    {
        $user->notify(
            new GenericNotification(
                'Plan ' . $plan->name . ' subscribed',
                'success',
                'You have successfully subscribed to the plan ' . $plan->name,
                'icon',
                true
            )
        );
    }
}
