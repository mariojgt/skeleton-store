<?php

namespace Skeleton\Store\Listeners;

use Skeleton\Store\Models\Payment;
use Skeleton\Store\Enums\DurationType;
use Skeleton\Store\Enums\RestrictionType;
use Skeleton\Store\Models\CapabilityUsage;
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

        // After creating the subscription, reset capability usage for the new plan
        $this->resetCapabilityUsage($user, $subscription, $plan);

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

    /**
     * Reset capability usage for a new subscription
     */
    protected function resetCapabilityUsage($user, $subscription, $plan)
    {
        // Get capabilities for this plan with the correct pivot attributes
        $planCapabilities = $plan->capabilities()->withPivot(['restriction_type', 'usage_limit', 'is_unlimited', 'initial_credits'])->get();

        foreach ($planCapabilities as $capability) {
            $usage = CapabilityUsage::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'capability_id' => $capability->id,
                    'subscription_id' => $subscription->id,
                ],
                [
                    'usage_count' => 0,
                    'last_reset' => now(),
                ]
            );

            // Get the restriction type properly
            $restrictionType = RestrictionType::MONTHLY; // Default to monthly

            if (isset($capability->pivot->restriction_type)) {
                // Check if it's already a RestrictionType instance
                if ($capability->pivot->restriction_type instanceof RestrictionType) {
                    $restrictionType = $capability->pivot->restriction_type;
                } else {
                    // If it's a string, convert it to enum
                    try {
                        $restrictionType = RestrictionType::from($capability->pivot->restriction_type);
                    } catch (\ValueError $e) {
                        // Keep the default monthly if invalid
                    }
                }
            }

            // Set next reset date based on restriction type
            if ($restrictionType->isTimeBased()) {
                $usage->next_reset = now()->addDays($restrictionType->getDaysUntilReset());
            } else {
                $usage->next_reset = null;
            }

            // Initialize credits for credit-based capabilities
            if ($restrictionType === RestrictionType::CREDITS) {
                $usage->remaining_credits = $capability->pivot->initial_credits ?? 0;
            }

            $usage->save();
        }
    }
}
