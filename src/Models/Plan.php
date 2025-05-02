<?php

namespace Skeleton\Store\Models;

use Skeleton\Store\Enums\DurationType;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plan extends BaseMasterModel
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration',
        'duration_type',
        'is_active',
        'product_id',
        'stripe_price_id',
        'auto_renew'
    ];

    protected $casts = [
        'duration_type'       => DurationType::class,
        'auto_renew'          => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Update the capabilities relationship to use the correct column names
    public function capabilities()
    {
        return $this->belongsToMany(Capability::class, 'plan_capabilities')
            ->using(PlanCapability::class)
            ->withPivot('usage_limit', 'is_unlimited', 'restriction_type', 'initial_credits')
            ->withTimestamps();
    }

    public function planCapabilities()
    {
        return $this->hasMany(PlanCapability::class);
    }

    /**
     * Check if plan has a specific capability
     */
    public function hasCapability(string $capabilitySlug): bool
    {
        return $this->capabilities()
            ->where('slug', $capabilitySlug)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get limit for a specific capability
     */
    public function getCapabilityLimit(string $capabilitySlug)
    {
        $planCapability = $this->capabilities()
            ->where('slug', $capabilitySlug)
            ->where('is_active', true)
            ->first();

        if (!$planCapability) {
            return 0;
        }

        if ($planCapability->pivot->is_unlimited) {
            return -1; // -1 means unlimited
        }

        return $planCapability->pivot->usage_limit;
    }
}
