<?php

namespace Skeleton\Store\Events;

use Skeleton\Store\Models\Plan;
use Skeleton\Store\Models\User;
use Skeleton\Store\Models\Payment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class UserSubscribedToPlan
{
    use Dispatchable, SerializesModels;

    public User $user;
    public Plan $plan;
    public bool $autoRenew;

    public array $payment;

    public function __construct(User $user, Plan $plan, array $payment = null, bool $autoRenew = true)
    {
        $this->user = $user;
        $this->plan = $plan;
        $this->payment = $payment;
        $this->autoRenew = $autoRenew;
    }
}
