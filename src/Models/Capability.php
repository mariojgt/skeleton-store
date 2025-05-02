<?php

namespace Skeleton\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Capability extends BaseMasterModel
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get plans that include this capability
     */
    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'plan_capabilities')
            ->withPivot('monthly_limit', 'is_unlimited')
            ->withTimestamps();
    }
}
