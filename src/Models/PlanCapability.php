<?php

namespace Skeleton\Store\Models;

use Skeleton\Store\Enums\RestrictionType;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PlanCapability extends Pivot
{
    protected $table = 'plan_capabilities';

    protected $fillable = [
        'plan_id',
        'capability_id',
        'usage_limit',
        'is_unlimited',
        'restriction_type',
        'initial_credits'
    ];

    protected $casts = [
        'usage_limit' => 'integer',
        'is_unlimited' => 'boolean',
        'restriction_type' => RestrictionType::class,
        'initial_credits' => 'integer',
    ];

    /**
     * Check if this capability is credit-based
     */
    public function isCreditBased(): bool
    {
        return $this->restriction_type === RestrictionType::CREDITS;
    }

    public function capability()
    {
        return $this->belongsTo(Capability::class);
    }
}
