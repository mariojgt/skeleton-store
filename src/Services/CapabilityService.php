<?php

namespace Skeleton\Store\Services;

use Carbon\Carbon;
use Skeleton\Store\Models\User;
use Skeleton\Store\Models\Capability;
use Skeleton\Store\Models\CapabilityUsage;
use Skeleton\Store\Enums\RestrictionType;
use Skeleton\Store\Enums\SubscriptionStatus;

class CapabilityService
{
    /**
     * Check if user has access to a capability
     */
    public function userHasCapability(User $user, string $capabilitySlug): bool
    {
        // Get active subscriptions
        $activeSubscriptions = $user->activeSubscriptions();

        if ($activeSubscriptions->isEmpty()) {
            return false;
        }

        // Check if any subscription gives access to this capability
        foreach ($activeSubscriptions as $subscription) {
            if ($subscription->plan->hasCapability($capabilitySlug)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has remaining uses of a capability
     */
    public function userCanUseCapability(User $user, string $capabilitySlug, int $amount = 1): bool
    {
        // Get active subscriptions
        $activeSubscriptions = $user->activeSubscriptions();

        if ($activeSubscriptions->isEmpty()) {
            return false;
        }

        // Get capability ID
        $capability = Capability::where('slug', $capabilitySlug)
            ->where('is_active', true)
            ->first();

        if (!$capability) {
            return false;
        }

        // Check each subscription for unlimited access or remaining limits
        foreach ($activeSubscriptions as $subscription) {
            $planCapability = $subscription->plan->capabilities()
                ->where('capability_id', $capability->id)
                ->first();

            if (!$planCapability) {
                continue;
            }

            // If unlimited, allow usage
            if ($planCapability->pivot->is_unlimited) {
                return true;
            }

            // Check usage against limit
            $usage = CapabilityUsage::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'capability_id' => $capability->id,
                    'subscription_id' => $subscription->id,
                ],
                [
                    'usage_count' => 0,
                    'last_reset' => now(),
                    'next_reset' => $planCapability->pivot->restriction_type->isTimeBased()
                        ? now()->addDays($planCapability->pivot->restriction_type->getDaysUntilReset())
                        : null,
                    'remaining_credits' => $planCapability->pivot->initial_credits,
                ]
            );

            // Check and reset if needed based on restriction type
            $usage->checkAndResetIfNeeded();

            // For credit-based capabilities
            if ($planCapability->pivot->restriction_type === RestrictionType::CREDITS) {
                if ($usage->remaining_credits >= $amount) {
                    return true;
                }
                continue;
            }

            // For time-based or lifetime capabilities
            if ($usage->usage_count + $amount <= $planCapability->pivot->usage_limit) {
                return true;
            }
        }

        return false;
    }

    /**
     * Record usage of a capability
     */
    public function recordCapabilityUsage(User $user, string $capabilitySlug, int $amount = 1): bool
    {
        // Get active subscriptions
        $activeSubscriptions = $user->activeSubscriptions();

        if ($activeSubscriptions->isEmpty()) {
            return false;
        }

        // Get capability
        $capability = Capability::where('slug', $capabilitySlug)
            ->where('is_active', true)
            ->first();

        if (!$capability) {
            return false;
        }

        // Find the best subscription to use (e.g., unlimited, or with most remaining)
        $bestSubscription = null;
        $bestUsage = null;
        $isUnlimited = false;

        foreach ($activeSubscriptions as $subscription) {
            $planCapability = $subscription->plan->capabilities()
                ->where('capability_id', $capability->id)
                ->first();

            if (!$planCapability) {
                continue;
            }

            // If unlimited, use this one
            if ($planCapability->pivot->is_unlimited) {
                $bestSubscription = $subscription;
                $isUnlimited = true;
                break;
            }

            // Get usage
            $usage = CapabilityUsage::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'capability_id' => $capability->id,
                    'subscription_id' => $subscription->id,
                ],
                [
                    'usage_count' => 0,
                    'last_reset' => now(),
                    'next_reset' => $planCapability->pivot->restriction_type->isTimeBased()
                        ? now()->addDays($planCapability->pivot->restriction_type->getDaysUntilReset())
                        : null,
                    'remaining_credits' => $planCapability->pivot->initial_credits,
                ]
            );

            // Check and reset if needed
            $usage->checkAndResetIfNeeded();

            // Determine if this subscription can be used
            if ($planCapability->pivot->restriction_type === RestrictionType::CREDITS) {
                if ($usage->remaining_credits >= $amount && (!$bestUsage || $usage->remaining_credits > $bestUsage->remaining_credits)) {
                    $bestSubscription = $subscription;
                    $bestUsage = $usage;
                }
            } else {
                if ($usage->usage_count + $amount <= $planCapability->pivot->usage_limit &&
                    (!$bestUsage || ($planCapability->pivot->usage_limit - $usage->usage_count) >
                     ($planCapability->pivot->usage_limit - $bestUsage->usage_count))) {
                    $bestSubscription = $subscription;
                    $bestUsage = $usage;
                }
            }
        }

        if (!$bestSubscription) {
            return false;
        }

        // Record usage on the best subscription
        if ($isUnlimited) {
            $usage = CapabilityUsage::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'capability_id' => $capability->id,
                    'subscription_id' => $bestSubscription->id,
                ],
                [
                    'usage_count' => 0,
                    'last_reset' => now(),
                ]
            );

            $usage->usage_count += $amount;
            $usage->save();
            return true;
        }

        return $bestUsage->recordUsage($amount);
    }

    /**
     * Get remaining uses of a capability
     */
    public function getRemainingUses(User $user, string $capabilitySlug): int
    {
        // Get active subscriptions
        $activeSubscriptions = $user->activeSubscriptions();

        if ($activeSubscriptions->isEmpty()) {
            return 0;
        }

        // Get capability
        $capability = Capability::where('slug', $capabilitySlug)
            ->where('is_active', true)
            ->first();

        if (!$capability) {
            return 0;
        }

        $highestRemaining = 0;

        // Check each subscription
        foreach ($activeSubscriptions as $subscription) {
            $planCapability = $subscription->plan->capabilities()
                ->where('capability_id', $capability->id)
                ->first();

            if (!$planCapability) {
                continue;
            }

            // If unlimited, return -1
            if ($planCapability->pivot->is_unlimited) {
                return -1; // -1 represents unlimited
            }

            // Get or create usage record
            $usage = CapabilityUsage::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'capability_id' => $capability->id,
                    'subscription_id' => $subscription->id,
                ],
                [
                    'usage_count' => 0,
                    'last_reset' => now(),
                    'next_reset' => $planCapability->pivot->restriction_type->isTimeBased()
                        ? now()->addDays($planCapability->pivot->restriction_type->getDaysUntilReset())
                        : null,
                    'remaining_credits' => $planCapability->pivot->initial_credits,
                ]
            );

            // Check and reset if needed
            $usage->checkAndResetIfNeeded();

            // Calculate remaining uses
            $remaining = 0;

            if ($planCapability->pivot->restriction_type === RestrictionType::CREDITS) {
                $remaining = $usage->remaining_credits;
            } else {
                $remaining = $planCapability->pivot->usage_limit - $usage->usage_count;
            }

            if ($remaining > $highestRemaining) {
                $highestRemaining = $remaining;
            }
        }

        return $highestRemaining;
    }

    /**
     * Get detailed capability usage information
     *
     * @param User $user
     * @param string $capabilitySlug
     * @return object
     */
    public function getCapabilityUsageDetails(User $user, string $capabilitySlug): object
    {
        // Get active subscriptions
        $activeSubscriptions = $user->activeSubscriptions();

        if ($activeSubscriptions->isEmpty()) {
            return (object) [
                'usage_count' => 0,
                'usage_limit' => 0,
                'remaining' => 0,
                'next_reset' => null,
                'restriction_type' => null
            ];
        }

        // Get capability
        $capability = Capability::where('slug', $capabilitySlug)
            ->where('is_active', true)
            ->first();

        if (!$capability) {
            return (object) [
                'usage_count' => 0,
                'usage_limit' => 0,
                'remaining' => 0,
                'next_reset' => null,
                'restriction_type' => null
            ];
        }

        // Find the best subscription (one with the most remaining usage)
        $bestUsage = null;
        $bestPlanCapability = null;
        $isUnlimited = false;

        foreach ($activeSubscriptions as $subscription) {
            $planCapability = $subscription->plan->capabilities()
                ->where('capability_id', $capability->id)
                ->first();

            if (!$planCapability) {
                continue;
            }

            // If unlimited, use this one
            if ($planCapability->pivot->is_unlimited) {
                $isUnlimited = true;
                $bestPlanCapability = $planCapability;

                $bestUsage = CapabilityUsage::firstOrCreate(
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

                break;
            }

            // Get usage
            $usage = CapabilityUsage::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'capability_id' => $capability->id,
                    'subscription_id' => $subscription->id,
                ],
                [
                    'usage_count' => 0,
                    'last_reset' => now(),
                    'next_reset' => $planCapability->pivot->restriction_type->isTimeBased()
                        ? now()->addDays($planCapability->pivot->restriction_type->getDaysUntilReset())
                        : null,
                    'remaining_credits' => $planCapability->pivot->initial_credits,
                ]
            );

            // Check and reset if needed
            $usage->checkAndResetIfNeeded();

            // Determine remaining
            $remaining = 0;
            if ($planCapability->pivot->restriction_type === RestrictionType::CREDITS) {
                $remaining = $usage->remaining_credits;
            } else {
                $remaining = $planCapability->pivot->usage_limit - $usage->usage_count;
            }

            // Use this subscription if it's the best so far
            if (!$bestUsage || $remaining > ($bestUsage->remaining_credits ?? 0)) {
                $bestUsage = $usage;
                $bestPlanCapability = $planCapability;
            }
        }

        if (!$bestUsage) {
            return (object) [
                'usage_count' => 0,
                'usage_limit' => 0,
                'remaining' => 0,
                'next_reset' => null,
                'restriction_type' => null
            ];
        }

        // Calculate usage details
        $usageCount = $bestUsage->usage_count;
        $usageLimit = $isUnlimited ? -1 : $bestPlanCapability->pivot->usage_limit;

        $remaining = 0;
        if ($isUnlimited) {
            $remaining = -1; // -1 represents unlimited
        } elseif ($bestPlanCapability->pivot->restriction_type === RestrictionType::CREDITS) {
            $remaining = $bestUsage->remaining_credits;
        } else {
            $remaining = $bestPlanCapability->pivot->usage_limit - $bestUsage->usage_count;
        }

        return (object) [
            'usage_count' => $usageCount,
            'usage_limit' => $usageLimit,
            'remaining' => $remaining,
            'next_reset' => $bestUsage->next_reset,
            'restriction_type' => $bestPlanCapability->pivot->restriction_type->value
        ];
    }

    /**
     * Get the best subscription for a capability
     *
     * This is useful for determining which subscription should be used for a capability
     *
     * @param User $user
     * @param string $capabilitySlug
     * @return array|null Returns [subscription, planCapability, usage] or null if no valid subscription
     */
    public function getBestSubscriptionForCapability(User $user, string $capabilitySlug): ?array
    {
        // Get active subscriptions
        $activeSubscriptions = $user->activeSubscriptions();

        if ($activeSubscriptions->isEmpty()) {
            return null;
        }

        // Get capability
        $capability = Capability::where('slug', $capabilitySlug)
            ->where('is_active', true)
            ->first();

        if (!$capability) {
            return null;
        }

        // Find the best subscription to use (e.g., unlimited, or with most remaining)
        $bestSubscription = null;
        $bestPlanCapability = null;
        $bestUsage = null;

        foreach ($activeSubscriptions as $subscription) {
            $planCapability = $subscription->plan->capabilities()
                ->where('capability_id', $capability->id)
                ->first();

            if (!$planCapability) {
                continue;
            }

            // If unlimited, use this one
            if ($planCapability->pivot->is_unlimited) {
                $bestSubscription = $subscription;
                $bestPlanCapability = $planCapability;

                $bestUsage = CapabilityUsage::firstOrCreate(
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

                break;
            }

            // Get usage
            $usage = CapabilityUsage::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'capability_id' => $capability->id,
                    'subscription_id' => $subscription->id,
                ],
                [
                    'usage_count' => 0,
                    'last_reset' => now(),
                    'next_reset' => $planCapability->pivot->restriction_type->isTimeBased()
                        ? now()->addDays($planCapability->pivot->restriction_type->getDaysUntilReset())
                        : null,
                    'remaining_credits' => $planCapability->pivot->initial_credits,
                ]
            );

            // Check and reset if needed
            $usage->checkAndResetIfNeeded();

            // Determine remaining
            $remaining = 0;
            if ($planCapability->pivot->restriction_type === RestrictionType::CREDITS) {
                $remaining = $usage->remaining_credits;
            } else {
                $remaining = $planCapability->pivot->usage_limit - $usage->usage_count;
            }

            // Use this subscription if it's the best so far
            if (!$bestUsage || $remaining > ($bestUsage->remaining_credits ?? 0)) {
                $bestSubscription = $subscription;
                $bestPlanCapability = $planCapability;
                $bestUsage = $usage;
            }
        }

        if (!$bestSubscription) {
            return null;
        }

        return [
            'subscription' => $bestSubscription,
            'planCapability' => $bestPlanCapability,
            'usage' => $bestUsage
        ];
    }
}
