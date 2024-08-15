<?php

namespace Skeleton\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Mariojgt\SkeletonAdmin\Models\User;
use Mariojgt\SkeletonAdmin\Models\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends BaseMasterModel
{
    use HasFactory;

    protected $fillable = ['user_id', 'total', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
