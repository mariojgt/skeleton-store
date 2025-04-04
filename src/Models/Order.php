<?php

namespace Skeleton\Store\Models;

use Skeleton\Store\Enums\OrderStatus;
use Mariojgt\SkeletonAdmin\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Order Model
 *
 * Represents a customer order in the system
 */
class Order extends BaseMasterModel
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'total_amount',
        'subtotal',
        'tax',
        'discount',
        'status',
        'payment_session_id',  // Renamed from stripe_session_id
        'payment_gateway',     // New field to track payment gateway
        'invoice_id',
        'invoice_url',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => OrderStatus::class,
        'total_amount' => 'float',
        'subtotal' => 'float',
        'tax' => 'float',
        'discount' => 'float',
    ];

    /**
     * Get the user that owns the Order
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all of the order items for the Order
     *
     * @return HasMany
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the payment session for this order
     *
     * @return BelongsTo
     */
    public function paymentSession(): BelongsTo
    {
        return $this->belongsTo(PaymentSession::class, 'payment_session_id', 'session_id');
    }

    /**
     * Calculate the total amount based on order items
     *
     * @return float
     */
    public function calculateTotal(): float
    {
        $subtotal = $this->orderItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $this->subtotal = $subtotal;
        $this->total_amount = $subtotal - ($this->discount ?? 0) + ($this->tax ?? 0);

        return $this->total_amount;
    }

    /**
     * Check if order is paid
     *
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->status === OrderStatus::completed ||
               $this->status === OrderStatus::processing;
    }

    /**
     * Check if order can be cancelled
     *
     * @return bool
     */
    public function canBeCancelled(): bool
    {
        return $this->status === OrderStatus::pending ||
               $this->status === OrderStatus::processing;
    }

    /**
     * Get payment gateway name
     *
     * @return string
     */
    public function getPaymentGatewayName(): string
    {
        return $this->payment_gateway ?? config('skeletonStore.payment_gateway.default');
    }
}
