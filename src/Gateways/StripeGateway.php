<?php

namespace Skeleton\Store\Gateways;

use Skeleton\Store\Models\Plan;
use Skeleton\Store\Models\Order;
use Skeleton\Store\Factory\Stripe;
use Skeleton\Store\DataStructure\ProductDetail;

class StripeGateway extends AbstractPaymentGateway
{
    /** @var Stripe */
    protected $stripeFactory;

    /**
     * Initialize gateway
     */
    public function __construct()
    {
        $this->stripeFactory = new Stripe();
    }

    /**
     * Get the gateway configuration key
     *
     * @return string
     */
    protected function getGatewayConfigKey(): string
    {
        return 'stripe';
    }

    /**
     * Create a product in Stripe
     *
     * @param string $name
     * @param string|null $image
     * @param bool $isSubscription
     * @param string|null $description
     * @return mixed
     */
    public function createProduct(string $name, ?string $image = null, bool $isSubscription = false, ?string $description = null)
    {
        return $this->stripeFactory->createProduct(
            $name,
            $image,
            $isSubscription,
            $description
        );
    }

    /**
     * Create a subscription price in Stripe
     *
     * @param float $amount
     * @param string $currency
     * @param string $productName
     * @param string $interval
     * @param int $intervalCount
     * @return mixed
     */
    public function createSubscriptionPrice(float $amount, string $currency, string $productName, string $interval, int $intervalCount)
    {
        return $this->stripeFactory->createPrice(
            $amount,
            $currency,
            $productName,
            $interval,
            $intervalCount
        );
    }

    /**
     * Create a one-time price in Stripe
     *
     * @param float $amount
     * @param string $productId
     * @param string $currency
     * @return mixed
     */
    public function createOneTimePrice(float $amount, string $productId, string $currency)
    {
        return $this->stripeFactory->createOneTimePrice(
            $amount,
            $productId,
            $currency
        );
    }

    /**
     * Create a checkout session in Stripe
     *
     * @param mixed $user
     * @param array $lineItems
     * @param bool $isSubscription
     * @param string $successUrl
     * @param string $cancelUrl
     * @return mixed
     */
    public function createCheckoutSession($user, array $lineItems, bool $isSubscription, string $successUrl, string $cancelUrl)
    {
        return $this->stripeFactory->createSession(
            $user,
            $lineItems,
            $isSubscription,
            $successUrl,
            $cancelUrl
        );
    }

    /**
     * Process subscription checkout with Stripe
     *
     * @param Plan $plan
     * @param mixed $user
     * @return array
     */
    public function processSubscriptionCheckout(Plan $plan, $user): array
    {
        $this->ensurePlanHasGatewayPrice($plan);

        $lineItems = $this->formatSubscriptionLineItems($plan);

        $session = $this->createCheckoutSession(
            $user,
            $lineItems,
            $plan->auto_renew,
            $this->getSuccessUrl(),
            $this->getCancelUrl()
        );

        $productDetail[] = new ProductDetail(
            $plan->name,
            $plan->price,
            $plan,
            1,
            [],
        );

        $this->dispatchOrderCreation($user, $productDetail, $session->id);

        return [
            'session' => $session->url,
        ];
    }

    /**
     * Process product checkout with Stripe
     *
     * @param array $productDetails
     * @param mixed $user
     * @return array
     */
    public function processProductCheckout(array $productDetails, $user): array
    {
        foreach ($productDetails as $item) {
            $mainImage = !empty($item->media_url[0]) ? $item->media_url[0] : null;
            $this->ensureProductHasGatewayPrice($item, $mainImage);
        }

        $lineItems = $this->formatProductLineItems($productDetails);

        $session = $this->createCheckoutSession(
            $user,
            $lineItems,
            false,
            $this->getSuccessUrl(),
            $this->getCancelUrl()
        );

        $this->dispatchOrderCreation($user, $productDetails, $session->id);

        return [
            'session' => $session->url,
        ];
    }

    /**
     * Format line items for subscription checkout
     *
     * @param Plan $plan
     * @return array
     */
    public function formatSubscriptionLineItems(Plan $plan): array
    {
        return [[
            'price' => $plan->gateway_price_id,
            'quantity' => 1,
        ]];
    }

    /**
     * Format line items for product checkout
     *
     * @param array $productDetails
     * @return array
     */
    public function formatProductLineItems(array $productDetails): array
    {
        $lineItems = [];

        foreach ($productDetails as $item) {
            $lineItems[] = [
                'price' => $item->model->gateway_price_id,
                'quantity' => $item->quantity,
            ];
        }

        return $lineItems;
    }

    /**
     * Ensure plan has a price ID in the payment gateway
     *
     * @param Plan $plan
     */
    protected function ensurePlanHasGatewayPrice(Plan $plan): void
    {
        if (empty($plan->gateway_price_id)) {
            $paymentId = $plan->auto_renew
                ? $this->createSubscriptionPrice(
                    $plan->price,
                    config('ecommerceStore')['store_currency'],
                    $plan->name,
                    'month',
                    $plan->duration
                )
                : $this->createOneTimePriceForPlan($plan);

            $plan->gateway_price_id = $paymentId->id;
            $plan->payment_gateway = $this->getGatewayConfigKey();
            $plan->save();
            $plan->refresh();
        }
    }

    /**
     * Create a one-time price for a plan
     *
     * @param Plan $plan
     * @return mixed
     */
    protected function createOneTimePriceForPlan(Plan $plan)
    {
        $stripeProduct = $this->createProduct(
            $plan->name,
            null,
            false,
            $plan->description
        );

        return $this->createOneTimePrice(
            $plan->price,
            $stripeProduct->id,
            config('ecommerceStore')['store_currency']
        );
    }

    /**
     * Ensure product has a price ID in the payment gateway
     *
     * @param ProductDetail $item
     * @param string|null $mainImage
     */
    protected function ensureProductHasGatewayPrice(ProductDetail $item, ?string $mainImage = null): void
    {
        if (empty($item->model->gateway_price_id)) {
            $stripeProduct = $this->createProduct(
                $item->name,
                $mainImage
            );

            $paymentId = $this->createOneTimePrice(
                $item->amount,
                $stripeProduct->id,
                config('ecommerceStore')['store_currency']
            );

            $item->model->gateway_price_id = $paymentId->id;
            $item->model->payment_gateway = $this->getGatewayConfigKey();
            $item->model->save();
            $item->model->refresh();
        }
    }

    /**
     * Retrieve a checkout session from Stripe
     *
     * @param string $sessionId
     * @return mixed
     */
    public function retrieveSession(string $sessionId)
    {
        return $this->stripeFactory->stripe->checkout->sessions->retrieve($sessionId);
    }

    /**
     * Retrieve a subscription from Stripe
     *
     * @param string $subscriptionId
     * @return mixed
     */
    public function retrieveSubscription(string $subscriptionId)
    {
        return $this->stripeFactory->stripe->subscriptions->retrieve($subscriptionId);
    }

    /**
     * Create an invoice and mark it as paid
     *
     * @param string $sessionId
     * @param Order $order
     * @return mixed
     */
    public function createInvoiceAndMarkAsPaid(string $sessionId, Order $order)
    {
        return $this->stripeFactory->createInvoiceAndMarkAsPaid($sessionId, $order);
    }
}
