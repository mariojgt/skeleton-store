<?php

namespace Skeleton\Store\Events;

use Skeleton\Store\Models\Plan;
use Skeleton\Store\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class UserSubscribedToPlan
{
    use Dispatchable, SerializesModels;

    /**
     * User who subscribed to the plan
     *
     * @var User
     */
    public User $user;

    /**
     * Plan the user subscribed to
     *
     * @var Plan
     */
    public Plan $plan;

    /**
     * Payment details
     *
     * @var array|null
     */
    public ?array $payment;

    /**
     * Whether the subscription auto-renews
     *
     * @var bool
     */
    public bool $autoRenew;

    /**
     * Subscription ID from the payment gateway
     *
     * @var string|null
     */
    public ?string $subscriptionId;

    /**
     * Payment gateway used
     *
     * @var string|null
     */
    public ?string $paymentGateway;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param Plan $plan
     * @param array|null $payment
     * @param bool $autoRenew
     * @param string|null $subscriptionId
     * @param string|null $paymentGateway
     * @return void
     */
    public function __construct(
        User $user,
        Plan $plan,
        ?array $payment = null,
        bool $autoRenew = true,
        ?string $subscriptionId = null,
        ?string $paymentGateway = null
    ) {
        $this->user = $user;
        $this->plan = $plan;
        $this->payment = $payment;
        $this->autoRenew = $autoRenew;
        $this->subscriptionId = $subscriptionId;
        $this->paymentGateway = $paymentGateway ?? config('skeletonStore.payment_gateway.default', 'stripe');
    }
}
