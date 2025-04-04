<?php

namespace Skeleton\Store\Factory;

use Stripe\StripeClient;
use Exception;

/**
 * Stripe Factory Class
 *
 * Handles all Stripe-related operations including checkout sessions, products,
 * prices, subscriptions, and invoice management.
 */
class Stripe
{
    /** @var StripeClient */
    public $stripe;

    /**
     * Initialize Stripe client with API key
     *
     * Note: The stripe property is public to maintain compatibility with existing code.
     * Consider using getter methods for better encapsulation in future updates.
     */
    public function __construct()
    {
        $this->stripe = new StripeClient(config('skeletonStore.payment_gateway.stripe.secret_key'));
    }

    /**
     * Create a Stripe checkout session
     *
     * @param object $user User object containing email and stripe_id
     * @param array $lineItems Array of items to be purchased
     * @param bool $autoRenew Whether to create a subscription or one-time payment
     * @param string|null $successUrl URL to redirect after successful payment
     * @param string|null $cancelUrl URL to redirect after cancelled payment
     * @param bool $allowPromotionCodes Whether to allow promotion codes
     * @return \Stripe\Checkout\Session
     */
    public function createSession($user, $lineItems, $autoRenew = true, $successUrl = null, $cancelUrl = null, $allowPromotionCodes = true)
    {
        // Create or retrieve Stripe customer
        $customer = $this->getOrCreateCustomer($user);

        // Create checkout session
        return $this->stripe->checkout->sessions->create([
            'customer' => $customer,
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => $autoRenew ? 'subscription' : 'payment',
            'success_url' => $successUrl ?? env('APP_URL') . '/success',
            'cancel_url' => $cancelUrl ?? env('APP_URL') . '/cancel',
            'allow_promotion_codes' => $allowPromotionCodes
        ]);
    }

    /**
     * Create or retrieve a Stripe customer
     *
     * @param object $user User object
     * @return string Customer ID
     */
    protected function getOrCreateCustomer($user)
    {
        if (!empty($user->stripe_id)) {
            return $user->stripe_id;
        }

        $customer = $this->stripe->customers->create([
            'email' => $user->email,
        ]);

        $user->stripe_id = $customer->id;
        $user->save();

        return $customer->id;
    }

    /**
     * Create a recurring price for a subscription
     *
     * @param float $price Price in the currency's base unit (e.g., dollars)
     * @param string $currency Currency code (e.g., 'gbp', 'usd')
     * @param string $productName Name of the product
     * @param string $interval Billing interval ('month', 'year', etc.)
     * @param int $intervalCount Number of intervals between billings
     * @return \Stripe\Price
     */
    public function createPrice($price, $currency = 'gbp', $productName = 'Gold Plan', $interval = 'month', $intervalCount = 1)
    {
        return $this->stripe->prices->create([
            'currency' => strtolower($currency),
            'unit_amount' => (int)($price * 100), // Convert to smallest currency unit
            'recurring' => [
                'interval' => $interval,
                'interval_count' => $intervalCount
            ],
            'product_data' => ['name' => $productName],
        ]);
    }

    /**
     * Create a Stripe product
     *
     * @param string $name Product name
     * @param string|null $imageUrl URL of product image
     * @param bool $isSubscription Whether the product is a subscription
     * @param string|null $description Product description
     * @return \Stripe\Product
     */
    public function createProduct($name, $imageUrl = null, $isSubscription = false, $description = null)
    {
        $productData = [
            'name' => $name,
            'type' => $isSubscription ? 'service' : 'good'
        ];

        if ($imageUrl) {
            $productData['images'] = [$imageUrl];
        }

        if ($description) {
            $productData['description'] = $description;
        }

        return $this->stripe->products->create($productData);
    }

    /**
     * Create a one-time price for a product
     *
     * @param float $price Price in the currency's base unit
     * @param string $productId Stripe product ID
     * @param string $currency Currency code
     * @return \Stripe\Price
     */
    public function createOneTimePrice($price, $productId, $currency = 'gbp')
    {
        return $this->stripe->prices->create([
            'currency' => strtolower($currency),
            'unit_amount' => (int)($price * 100),
            'product' => $productId,
        ]);
    }

    /**
     * Cancel a subscription immediately
     *
     * @param string $subscriptionId Stripe subscription ID
     * @return \Stripe\Subscription
     */
    public function cancelSubscription($subscriptionId)
    {
        return $this->stripe->subscriptions->cancel($subscriptionId);
    }

    /**
     * Create a billing portal session
     *
     * @param string $customerId Stripe customer ID
     * @param string|null $returnUrl URL to return to after leaving the portal
     * @return \Stripe\BillingPortal\Session
     */
    public function createBillingPortalSession($customerId, $returnUrl = null)
    {
        return $this->stripe->billingPortal->sessions->create([
            'customer' => $customerId,
            'return_url' => $returnUrl ?? env('APP_URL') . '/account',
        ]);
    }

    /**
     * Retrieve subscription details
     *
     * @param string $subscriptionId Stripe subscription ID
     * @return \Stripe\Subscription|array Array with error message if retrieval fails
     */
    public function getSubscription($subscriptionId)
    {
        try {
            return $this->stripe->subscriptions->retrieve($subscriptionId);
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Create and mark an invoice as paid for a completed checkout session
     *
     * @param string $sessionId Stripe session ID
     * @param object $order Order object containing items
     * @return array Invoice details or error message
     */
    public function createInvoiceAndMarkAsPaid($sessionId, $order)
    {
        try {
            // Retrieve checkout session
            $session = $this->stripe->checkout->sessions->retrieve($sessionId);

            // Create initial invoice
            $invoice = $this->stripe->invoices->create([
                'customer' => $session->customer,
                'auto_advance' => false,
            ]);

            // Add items to invoice
            $this->addItemsToInvoice($invoice->id, $session->customer, $order->orderItems);

            // Finalize and mark invoice as paid
            $finalizedInvoice = $this->stripe->invoices->finalizeInvoice($invoice->id);
            $paidInvoice = $this->stripe->invoices->update($finalizedInvoice->id, [
                'paid' => true
            ]);

            return [
                'invoice_url' => $paidInvoice->hosted_invoice_url,
                'invoice_id' => $paidInvoice->id,
                'amount_paid' => $paidInvoice->amount_paid,
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getStripeClient(): StripeClient
    {
        return $this->stripe;
    }

    public function retrieveSession(string $sessionId)
    {
        return $this->stripe->checkout->sessions->retrieve($sessionId);
    }

    /**
     * Add items to an invoice
     *
     * @param string $invoiceId Stripe invoice ID
     * @param string $customerId Stripe customer ID
     * @param array $orderItems Array of order items
     */
    protected function addItemsToInvoice($invoiceId, $customerId, $orderItems)
    {
        foreach ($orderItems as $item) {
            $this->stripe->invoiceItems->create([
                'customer' => $customerId,
                'price' => $item->item->stripe_price_id,
                'quantity' => $item->quantity,
                'invoice' => $invoiceId,
            ]);
        }
    }
}
