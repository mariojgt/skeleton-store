<?php

namespace Skeleton\Store\Models;

use Carbon\Carbon;
use Skeleton\Store\Enums\PriceType;
use Skeleton\Store\Enums\ProductType;
use Mariojgt\SkeletonAdmin\Models\User;
use Skeleton\Store\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends BaseMasterModel
{
    use HasFactory;
    use softDeletes;

    protected $fillable = [
        'user_id',
        'plan_id',
        'start_date',
        'end_date',
        'status',
        'auto_renew',
        'subscription_id'
    ];

    protected $casts = [
        'status'       => SubscriptionStatus::class,
        'start_date'   => 'datetime',
        'end_date'     => 'datetime',
        'auto_renew'   => 'boolean',
    ];

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function durationLeft()
    {
        return $this->start_date->diffForHumans($this->end_date, true);
    }
}
