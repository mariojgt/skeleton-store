<?php

namespace Skeleton\Store\Models;

use Skeleton\Store\Enums\PriceType;
use Skeleton\Store\Enums\ProductType;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends BaseMasterModel
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'price', 'file_path', 'category_id'];

    protected $casts = [
        'type'       => ProductType::class,
        'price_type' => PriceType::class,
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
