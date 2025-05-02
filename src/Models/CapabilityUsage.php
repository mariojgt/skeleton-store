<?php

namespace Skeleton\Store\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Skeleton\Store\Enums\RestrictionType;
use Illuminate\Database\Eloquent\SoftDeletes;

class CapabilityUsage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'capability_id',
        'subscription_id',
        'usage_count',
        'last_reset',
        'next_reset',
        'remaining_credits'
    ];

    protected $casts = [
        'usage_count' => 'integer',
        'last_reset' => 'datetime',
        'next_reset' => 'datetime',
        'remaining_credits' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function capability()
    {
        return $this->belongsTo(Capability::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Check if usage needs to be reset based on restriction type
     */
    public function checkAndResetIfNeeded(): self
    {
        if (!$this->subscription || !$this->capability) {
            return $this;
        }

        $planCapability = PlanCapability::where('plan_id', $this->subscription->plan_id)
            ->where('capability_id', $this->capability_id)
            ->first();

        if (!$planCapability) {
            return $this;
        }

        // Skip if unlimited
        if ($planCapability->is_unlimited) {
            return $this;
        }

        // Skip if not time-based (i.e., credits or lifetime)
        if (!$planCapability->restriction_type->isTimeBased()) {
            return $this;
        }

        // Check if we need to reset
        if ($this->next_reset && $this->next_reset->isPast()) {
            $this->resetUsage($planCapability->restriction_type);
        }

        return $this;
    }

    /**
     * Record usage based on restriction type
     */
    public function recordUsage(int $amount = 1): bool
    {
        if (!$this->subscription || !$this->capability) {
            return false;
        }

        $planCapability = PlanCapability::where('plan_id', $this->subscription->plan_id)
            ->where('capability_id', $this->capability_id)
            ->first();

        if (!$planCapability) {
            return false;
        }

        // If unlimited, just record and return true
        if ($planCapability->is_unlimited) {
            $this->usage_count += $amount;
            $this->save();
            return true;
        }

        // Check and reset if needed first
        $this->checkAndResetIfNeeded();

        // For credit-based capabilities
        if ($planCapability->restriction_type === RestrictionType::CREDITS) {
            if ($this->remaining_credits < $amount) {
                return false; // Not enough credits
            }

            $this->remaining_credits -= $amount;
            $this->usage_count += $amount;
            $this->save();
            return true;
        }

        // For time-based capabilities
        if ($this->usage_count >= $planCapability->usage_limit) {
            return false; // Limit reached
        }

        $this->usage_count += $amount;
        $this->save();
        return true;
    }

    /**
     * Reset usage based on restriction type
     */
    public function resetUsage(RestrictionType $restrictionType): self
    {
        $this->usage_count = 0;
        $this->last_reset = now();

        // Set next reset date based on restriction type
        if ($restrictionType->isTimeBased() && $restrictionType->getDaysUntilReset()) {
            $this->next_reset = now()->addDays($restrictionType->getDaysUntilReset());
        } else {
            $this->next_reset = null;
        }

        $this->save();
        return $this;
    }

    /**
     * Initialize credits for credit-based capability
     */
    public function initializeCredits(int $initialCredits): self
    {
        $this->remaining_credits = $initialCredits;
        $this->save();
        return $this;
    }
}
