<?php

namespace Skeleton\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends BaseMasterModel
{
    use HasFactory, SoftDeletes;

    // Polymorphic relationships use 'item_type' and 'item_id'
    protected $fillable = ['order_id', 'item_id', 'item_type', 'quantity', 'price'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Define the polymorphic relationship to items (products, services, etc.)
    public function item()
    {
        return $this->morphTo();
    }
}
