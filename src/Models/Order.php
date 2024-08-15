<?php

namespace Skeleton\Store\Models;

use Skeleton\Store\Enums\OrderStatus;
use Mariojgt\SkeletonAdmin\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends BaseMasterModel
{
    use HasFactory;

    protected $fillable = ['user_id', 'total', 'status'];

    protected $casts = [
        'status' => OrderStatus::class,
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
