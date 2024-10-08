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

    public function createSession($user, $priceId, $successUrl = null, $cancelUrl = null)
    {
        // Get the customer or create one in Stripe
        $customer = $user->stripe_id;
        if (empty($customer)) {
            $customer = $this->stripe->customers->create([
                'email' => $user->email,
            ])->id;
            $user->stripe_id = $customer;
            $user->save();
        }
        return $this->stripe->checkout->sessions->create([
            'customer' => $customer,
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

    public function createPrice($price, $currency = 'gbp', $productName = 'Gold Plan', $interval = 'month', $intervalCount = 1)
    {
        // Convert price to cents
        $price = $price * 100;

        return $this->stripe->prices->create([
            'currency' => $currency,
            'unit_amount' => $price,
            'recurring' => [
                'interval'       => $interval,
                'interval_count' => $intervalCount
            ],
            'product_data' => ['name' => $productName],
        ]);
    }

    public function cancelSubscription($subscriptionId)
    {
        // Cancel the subscription immediately
        return $this->stripe->subscriptions->cancel($subscriptionId);
    }

    public function createBillingPortalSession($customerId, $returnUrl = null)
    {
        return $this->stripe->billingPortal->sessions->create([
            'customer' => $customerId,
            'return_url' => $returnUrl ?? env('APP_URL') . '/account',
        ]);
    }
}
