<?php

namespace Skeleton\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Mariojgt\SkeletonAdmin\Models\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends BaseMasterModel
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['order_id', 'product_id', 'quantity', 'price'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
