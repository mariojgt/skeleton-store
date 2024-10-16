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

    public function createSubscriptionSession($user, $priceId, $successUrl = null, $cancelUrl = null)
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

    public function createCheckoutSession($user, $lineItems, $successUrl = null, $cancelUrl = null)
    {
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
            'line_items' => $lineItems,
            'mode' => 'payment',
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

    public function createProduct($name, $imageUrl)
    {
        return $this->stripe->products->create([
            'name' => $name,
            'images' => [$imageUrl], // Array of image URLs
        ]);
    }

    public function createOneTimePrice($price, $productId, $currency = 'gbp')
    {
        // Convert price to cents
        $priceInCents = $price * 100;

        // Create the price for a one-time product
        return $this->stripe->prices->create([
            'currency' => $currency,
            'unit_amount' => $priceInCents,
            'product' => $productId, // Use the provided product ID
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

    public function getSubscription($subscriptionId)
    {
        try {
            // Retrieve the subscription details from Stripe
            return $this->stripe->subscriptions->retrieve($subscriptionId);
        } catch (\Exception $e) {
            // Handle any errors (such as subscription not found)
            return ['error' => $e->getMessage()];
        }
    }

}
