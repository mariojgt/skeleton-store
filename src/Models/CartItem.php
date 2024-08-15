<?php

namespace Skeleton\Store\Models;

use Mariojgt\SkeletonAdmin\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartItem extends BaseMasterModel
{
    use HasFactory;

    protected $fillable = ['user_id', 'product_id', 'quantity'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
