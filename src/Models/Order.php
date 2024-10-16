<?php

namespace Skeleton\Store\Models;

use Skeleton\Store\Enums\OrderStatus;
use Mariojgt\SkeletonAdmin\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends BaseMasterModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'total_amount', 'status', 'stripe_session_id'];

    protected $casts = [
        'status' => OrderStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship with OrderItems
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
