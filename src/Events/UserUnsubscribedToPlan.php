<?php

namespace Skeleton\Store\Events;

use Skeleton\Store\Models\Plan;
use Skeleton\Store\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class UserUnsubscribedToPlan
{
    use Dispatchable, SerializesModels;

    /**
     * User who unsubscribed
     *
     * @var User
     */
    public User $user;

    /**
     * Plan the user unsubscribed from
     *
     * @var Plan
     */
    public Plan $plan;

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
     * @param string|null $paymentGateway
     * @return void
     */
    public function __construct(User $user, Plan $plan, ?string $paymentGateway = null)
    {
        $this->user = $user;
        $this->plan = $plan;
        $this->paymentGateway = $paymentGateway ?? config('skeletonStore.payment_gateway.default', 'stripe');
    }
}
