<?php

namespace Skeleton\Store\Models;

use Skeleton\Store\Enums\PriceType;
use Skeleton\Store\Enums\ProductType;
use Skeleton\Store\Enums\DurationType;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plan extends BaseMasterModel
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'description', 'price', 'duration', 'duration_type', 'is_active', 'product_id', 'stripe_price_id'];

    protected $casts = [
        'duration_type'       => DurationType::class
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
