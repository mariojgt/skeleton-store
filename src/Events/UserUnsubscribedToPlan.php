<?php

namespace Skeleton\Store\Events;

use Skeleton\Store\Models\Plan;
use Skeleton\Store\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class UserUnsubscribedToPlan
{
    use Dispatchable, SerializesModels;

    public $user;
    public $plan;

    /**
     * Create a new event instance.
     *
     * @param  User  $user
     * @param  Plan  $plan
     * @return void
     */
    public function __construct(User $user, Plan $plan)
    {
        $this->user = $user;
        $this->plan = $plan;
    }
}
