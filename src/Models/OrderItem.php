<?php

namespace Skeleton\Store\Models;

use Mariojgt\GameDev\Models\Course;
use Skeleton\Store\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    /**
     * Scope to get paid items of specific types
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|string $types Array of model class names or single class name
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaidProducts($query, $types = null)
    {
        $query = $query->whereHas('order', function ($query) {
            $query->where('status', OrderStatus::completed->value);
        });

        if ($types !== null) {
            // Convert single string to array
            $types = is_array($types) ? $types : [$types];

            // Filter by the provided types
            $query->where(function ($query) use ($types) {
                foreach ($types as $index => $type) {
                    if ($index === 0) {
                        $query->where('item_type', $type);
                    } else {
                        $query->orWhere('item_type', $type);
                    }
                }
            });
        }

        return $query;
    }
}
