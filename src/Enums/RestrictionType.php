<?php

namespace Skeleton\Store\Enums;

enum RestrictionType: string
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
    case CREDITS = 'credits';
    case LIFETIME = 'lifetime';

    /**
     * Get days until reset
     */
    public function getDaysUntilReset(): ?int
    {
        return match ($this) {
            self::DAILY => 1,
            self::WEEKLY => 7,
            self::MONTHLY => 30,
            self::YEARLY => 365,
            default => null, // CREDITS and LIFETIME don't have time-based resets
        };
    }

    /**
     * Check if this restriction type is time-based
     */
    public function isTimeBased(): bool
    {
        return $this !== self::CREDITS && $this !== self::LIFETIME;
    }

    /**
     * Get readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::DAILY => 'Daily Limit',
            self::WEEKLY => 'Weekly Limit',
            self::MONTHLY => 'Monthly Limit',
            self::YEARLY => 'Yearly Limit',
            self::CREDITS => 'Credit-Based',
            self::LIFETIME => 'Lifetime Limit',
        };
    }
}
