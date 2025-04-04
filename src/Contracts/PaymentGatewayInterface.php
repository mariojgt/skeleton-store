<?php

namespace Skeleton\Store\Contracts;

use Skeleton\Store\Models\Plan;
use Skeleton\Store\Models\Order;
use Skeleton\Store\DataStructure\ProductDetail;

interface PaymentGatewayInterface
{
    /**
     * Create a product in the payment gateway
     *
     * @param string $name
     * @param string|null $image
     * @param bool $isSubscription
     * @param string|null $description
     * @return mixed
     */
    public function createProduct(string $name, ?string $image = null, bool $isSubscription = false, ?string $description = null);

    /**
     * Create a subscription price in the payment gateway
     *
     * @param float $amount
     * @param string $currency
     * @param string $productName
     * @param string $interval
     * @param int $intervalCount
     * @return mixed
     */
    public function createSubscriptionPrice(float $amount, string $currency, string $productName, string $interval, int $intervalCount);

    /**
     * Create a one-time price in the payment gateway
     *
     * @param float $amount
     * @param string $productId
     * @param string $currency
     * @return mixed
     */
    public function createOneTimePrice(float $amount, string $productId, string $currency);

    /**
     * Create a checkout session
     *
     * @param mixed $user
     * @param array $lineItems
     * @param bool $isSubscription
     * @param string $successUrl
     * @param string $cancelUrl
     * @return mixed
     */
    public function createCheckoutSession($user, array $lineItems, bool $isSubscription, string $successUrl, string $cancelUrl);

    /**
     * Retrieve a checkout session
     *
     * @param string $sessionId
     * @return mixed
     */
    public function retrieveSession(string $sessionId);

    /**
     * Retrieve a subscription
     *
     * @param string $subscriptionId
     * @return mixed
     */
    public function retrieveSubscription(string $subscriptionId);

    /**
     * Create an invoice and mark it as paid
     *
     * @param string $sessionId
     * @param Order $order
     * @return mixed
     */
    public function createInvoiceAndMarkAsPaid(string $sessionId, Order $order);

    /**
     * Process subscription checkout
     *
     * @param Plan $plan
     * @param mixed $user
     * @return array
     */
    public function processSubscriptionCheckout(Plan $plan, $user): array;

    /**
     * Process product checkout
     *
     * @param array $productDetails
     * @param mixed $user
     * @return array
     */
    public function processProductCheckout(array $productDetails, $user): array;

    /**
     * Format line items for subscription
     *
     * @param Plan $plan
     * @return array
     */
    public function formatSubscriptionLineItems(Plan $plan): array;

    /**
     * Format line items for products
     *
     * @param array $productDetails
     * @return array
     */
    public function formatProductLineItems(array $productDetails): array;

    /**
     * Get success URL
     *
     * @return string
     */
    public function getSuccessUrl(): string;

    /**
     * Get cancel URL
     *
     * @return string
     */
    public function getCancelUrl(): string;
}
