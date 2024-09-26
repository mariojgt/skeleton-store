<?php

namespace Skeleton\Store\Factory;

use Stripe\StripeClient;

class Stripe
{
    public $stripe;
    public function __construct()
    {
        $this->stripe = new StripeClient(config('skeletonStore.payment_gateway.stripe.secret'));
    }

    public function createSession($priceId, $successUrl = null, $cancelUrl = null)
    {
        return $this->stripe->checkout->sessions->create([
            // 'customer' => $customer,
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price' => $priceId,
                    'quantity' => 1,
                ],
            ],
            'mode' => 'subscription',
            'success_url' => $successUrl ?? env('APP_URL') . '/success',
            'cancel_url' => $cancelUrl ?? env('APP_URL') . '/cancel',
        ]);
    }

    public function createPrice($price, $currency = 'gbp', $productName = 'Gold Plan')
    {
        // Convert price to cents
        $price = $price * 100;

        return $this->stripe->prices->create([
            'currency' => $currency,
            'unit_amount' => $price,
            'recurring' => ['interval' => 'month'],
            'product_data' => ['name' => $productName],
        ]);
    }
}
