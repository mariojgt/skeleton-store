<?php

namespace Skeleton\Store\Models;

use Skeleton\Store\Enums\OrderStatus;
use Skeleton\Store\Enums\SubscriptionStatus;
use Mariojgt\SkeletonAdmin\Models\User as SkeletonUser;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends SkeletonUser
{
    /**
     * Get user's cart items
     *
     * @return HasMany
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get user's subscriptions
     *
     * @return HasMany
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get user's orders
     *
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get user's paid orders
     *
     * @return HasMany
     */
    public function paidOrders(): HasMany
    {
        return $this->orders()->where('status', OrderStatus::completed->value);
    }

    /**
     * Get user's payment sessions (gateway agnostic)
     *
     * @return HasMany
     */
    public function paymentSessions(): HasMany
    {
        return $this->hasMany(PaymentSession::class);
    }

    /**
     * Get user's active subscription
     *
     * @param string|null $paymentGateway Filter by payment gateway
     * @return Subscription|null
     */
    public function activeSubscription(?string $paymentGateway = null)
    {
        $query = $this->subscriptions()
            ->where('status', SubscriptionStatus::active)
            ->orderBy('end_date', 'desc');

        if ($paymentGateway) {
            $query->where('payment_gateway', $paymentGateway);
        }

        return $query->first();
    }

    /**
     * Get user's active subscriptions (multiple if they exist)
     *
     * @param string|null $paymentGateway Filter by payment gateway
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function activeSubscriptions(?string $paymentGateway = null)
    {
        $query = $this->subscriptions()
            ->where('status', SubscriptionStatus::active);

        if ($paymentGateway) {
            $query->where('payment_gateway', $paymentGateway);
        }

        return $query->orderBy('end_date', 'desc')->get();
    }

    /**
     * Get stripe sessions (for backward compatibility)
     *
     * @deprecated Use paymentSessions() instead
     * @return HasMany
     */
    public function stripeSessions(): HasMany
    {
        // Keep this method for backward compatibility
        if (class_exists(StripeSession::class)) {
            return $this->hasMany(StripeSession::class);
        }

        // Fallback to payment sessions filtered by Stripe
        return $this->paymentSessions()->where('payment_gateway', 'stripe');
    }
}
