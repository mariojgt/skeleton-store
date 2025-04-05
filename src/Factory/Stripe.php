<?php

namespace Skeleton\Store\Factory;

use Stripe\StripeClient;
use Skeleton\Store\Gateways\StripeGateway;

/**
 * Backward compatibility layer for the Stripe factory
 *
 * This class maintains backward compatibility with existing code
 * by delegating calls to the new StripeGateway implementation.
 *
 * @deprecated Use StripeGateway directly or via PaymentGatewayFactory
 */
class Stripe
{
    /** @var StripeClient */
    public $stripe;

    /** @var StripeGateway */
    protected $gateway;

    /**
     * Initialize Stripe gateway and client
     */
    public function __construct()
    {
        $this->gateway = new StripeGateway();
        $this->stripe = $this->gateway->getClient();
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
        return $this->gateway->createCheckoutSession(
            $user,
            $lineItems,
            $autoRenew,
            $successUrl ?? env('APP_URL') . '/success',
            $cancelUrl ?? env('APP_URL') . '/cancel'
        );
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
        return $this->gateway->createSubscriptionPrice(
            $price,
            $currency,
            $productName,
            $interval,
            $intervalCount
        );
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
        return $this->gateway->createProduct(
            $name,
            $imageUrl,
            $isSubscription,
            $description
        );
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
        return $this->gateway->createOneTimePrice(
            $price,
            $productId,
            $currency
        );
    }

    /**
     * Cancel a subscription immediately
     *
     * @param string $subscriptionId Stripe subscription ID
     * @return \Stripe\Subscription
     */
    public function cancelSubscription($subscriptionId)
    {
        return $this->gateway->cancelSubscription($subscriptionId);
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
        // Adapt the call format for backward compatibility
        $user = (object) ['stripe_id' => $customerId];

        return $this->gateway->createBillingPortalSession(
            $user,
            $returnUrl
        );
    }

    /**
     * Retrieve subscription details
     *
     * @param string $subscriptionId Stripe subscription ID
     * @return \Stripe\Subscription|array Array with error message if retrieval fails
     */
    public function getSubscription($subscriptionId)
    {
        return $this->gateway->retrieveSubscription($subscriptionId);
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
        return $this->gateway->createInvoiceAndMarkAsPaid($sessionId, $order);
    }

    /**
     * Get the Stripe client
     *
     * @return StripeClient
     */
    public function getStripeClient(): StripeClient
    {
        return $this->stripe;
    }
}
