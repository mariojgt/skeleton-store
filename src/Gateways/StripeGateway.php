<?php

namespace Skeleton\Store\Gateways;

use Exception;
use Stripe\StripeClient;
use Skeleton\Store\Models\Order;
use Skeleton\Store\Contracts\PaymentGatewayInterface;

/**
 * Stripe Gateway Implementation
 *
 * Handles all Stripe-related operations including checkout sessions, products,
 * prices, subscriptions, and invoice management.
 */
class StripeGateway extends AbstractPaymentGateway implements PaymentGatewayInterface
{
    /** @var StripeClient */
    protected $client;

    /**
     * Initialize Stripe client with API key
     */
    public function __construct()
    {
        $this->client = new StripeClient(config('skeletonStore.payment_gateway.stripe.secret_key'));
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
     * Create a checkout session
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
        // Create or retrieve Stripe customer
        $customer = $this->getOrCreateCustomer($user);

        // Create checkout session
        return $this->client->checkout->sessions->create([
            'customer' => $customer,
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => $isSubscription ? 'subscription' : 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'allow_promotion_codes' => true
        ]);
    }

    /**
     * Create a product in the payment gateway
     *
     * @param string $name
     * @param string|null $image
     * @param bool $isSubscription
     * @param string|null $description
     * @return mixed
     */
    public function createProduct(string $name, ?string $image = null, bool $isSubscription = false, ?string $description = null)
    {
        $productData = [
            'name' => $name,
            'type' => $isSubscription ? 'service' : 'good'
        ];

        if ($image) {
            $productData['images'] = [$image];
        }

        if ($description) {
            $productData['description'] = $description;
        }

        return $this->client->products->create($productData);
    }

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
    public function createSubscriptionPrice(float $amount, string $currency, string $productName, string $interval, int $intervalCount)
    {
        return $this->client->prices->create([
            'currency' => strtolower($currency),
            'unit_amount' => (int)($amount * 100), // Convert to smallest currency unit
            'recurring' => [
                'interval' => $interval,
                'interval_count' => $intervalCount
            ],
            'product_data' => ['name' => $productName],
        ]);
    }

    /**
     * Create a one-time price in the payment gateway
     *
     * @param float $amount
     * @param string $productId
     * @param string $currency
     * @return mixed
     */
    public function createOneTimePrice(float $amount, string $productId, string $currency)
    {
        return $this->client->prices->create([
            'currency' => strtolower($currency),
            'unit_amount' => (int)($amount * 100),
            'product' => $productId,
        ]);
    }

    /**
     * Retrieve a checkout session
     *
     * @param string $sessionId
     * @return mixed
     */
    public function retrieveSession(string $sessionId)
    {
        return $this->client->checkout->sessions->retrieve($sessionId);
    }

    /**
     * Retrieve a subscription
     *
     * @param string $subscriptionId
     * @return mixed
     */
    public function retrieveSubscription(string $subscriptionId)
    {
        try {
            return $this->client->subscriptions->retrieve($subscriptionId);
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Cancel a subscription
     *
     * @param string $subscriptionId
     * @return mixed
     */
    public function cancelSubscription(string $subscriptionId)
    {
        return $this->client->subscriptions->cancel($subscriptionId);
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
        try {
            // Retrieve checkout session
            $session = $this->client->checkout->sessions->retrieve($sessionId);

            // Create initial invoice
            $invoice = $this->client->invoices->create([
                'customer' => $session->customer,
                'auto_advance' => false,
            ]);

            // Add items to invoice
            $this->addItemsToInvoice($invoice->id, $session->customer, $order->orderItems);

            // Finalize and mark invoice as paid
            $finalizedInvoice = $this->client->invoices->finalizeInvoice($invoice->id);
            $paidInvoice = $this->client->invoices->update($finalizedInvoice->id, [
                'paid' => true
            ]);

            // Update order with invoice details
            $order->invoice_id = $paidInvoice->id;
            $order->invoice_url = $paidInvoice->hosted_invoice_url;
            $order->save();

            return [
                'invoice_url' => $paidInvoice->hosted_invoice_url,
                'invoice_id' => $paidInvoice->id,
                'amount_paid' => $paidInvoice->amount_paid,
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Create a billing portal session
     *
     * @param mixed $user
     * @param string|null $returnUrl
     * @return mixed
     */
    public function createBillingPortalSession($user, ?string $returnUrl = null)
    {
        $customerId = $this->getOrCreateCustomer($user);

        return $this->client->billingPortal->sessions->create([
            'customer' => $customerId,
            'return_url' => $returnUrl ?? env('APP_URL') . '/account',
        ]);
    }

    /**
     * Get the Stripe client
     *
     * @return StripeClient
     */
    public function getClient(): StripeClient
    {
        return $this->client;
    }

    /**
     * Format line items for subscription checkout
     *
     * @param mixed $plan
     * @return array
     */
    public function formatSubscriptionLineItems($plan): array
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
     * Create or retrieve a customer
     *
     * @param mixed $user
     * @return string
     */
    protected function getOrCreateCustomer($user)
    {
        // Check if user has a gateway_customer_id for Stripe
        $gatewayId = null;

        if (method_exists($user, 'getGatewayCustomerId')) {
            $gatewayId = $user->getGatewayCustomerId('stripe');
        } elseif (isset($user->gateway_customer_ids) && is_array($user->gateway_customer_ids) && isset($user->gateway_customer_ids['stripe'])) {
            $gatewayId = $user->gateway_customer_ids['stripe'];
        } elseif (isset($user->stripe_id)) {
            $gatewayId = $user->stripe_id;
        }

        if (!empty($gatewayId)) {
            return $gatewayId;
        }

        // Create new customer
        $customer = $this->client->customers->create([
            'email' => $user->email,
        ]);

        // Save customer ID
        if (method_exists($user, 'setGatewayCustomerId')) {
            $user->setGatewayCustomerId('stripe', $customer->id);
        } elseif (isset($user->gateway_customer_ids) && is_array($user->gateway_customer_ids)) {
            $user->gateway_customer_ids['stripe'] = $customer->id;
            $user->save();
        } elseif (isset($user->stripe_id)) {
            $user->stripe_id = $customer->id;
            $user->save();
        }

        return $customer->id;
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
            $priceId = $item->item->gateway_price_id ?? $item->item->stripe_price_id;

            $this->client->invoiceItems->create([
                'customer' => $customerId,
                'price' => $priceId,
                'quantity' => $item->quantity,
                'invoice' => $invoiceId,
            ]);
        }
    }

    /**
     * Process subscription checkout with Stripe
     *
     * @param \Skeleton\Store\Models\Plan $plan
     * @param mixed $user
     * @return array
     */
    public function processSubscriptionCheckout(\Skeleton\Store\Models\Plan $plan, $user): array
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

        $productDetail[] = new \Skeleton\Store\DataStructure\ProductDetail(
            $plan->name,
            $plan->price,
            $plan,
            1,
            [],
        );

        $this->dispatchOrderCreation($user, $productDetail, $session->id, $this->getGatewayConfigKey());

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

        $this->dispatchOrderCreation($user, $productDetails, $session->id, $this->getGatewayConfigKey());

        return [
            'session' => $session->url,
        ];
    }

    /**
     * Ensure plan has a price ID in the payment gateway
     *
     * @param \Skeleton\Store\Models\Plan $plan
     */
    protected function ensurePlanHasGatewayPrice(\Skeleton\Store\Models\Plan $plan): void
    {
        $gatewayKey = $this->getGatewayConfigKey();

        if (empty($plan->gateway_price_id) || $plan->payment_gateway !== $gatewayKey) {
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
            $plan->payment_gateway = $gatewayKey;
            $plan->save();
            $plan->refresh();
        }
    }

    /**
     * Create a one-time price for a plan
     *
     * @param \Skeleton\Store\Models\Plan $plan
     * @return mixed
     */
    protected function createOneTimePriceForPlan(\Skeleton\Store\Models\Plan $plan)
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
     * @param \Skeleton\Store\DataStructure\ProductDetail $item
     * @param string|null $mainImage
     */
    protected function ensureProductHasGatewayPrice(\Skeleton\Store\DataStructure\ProductDetail $item, ?string $mainImage = null): void
    {
        $gatewayKey = $this->getGatewayConfigKey();

        if (empty($item->model->gateway_price_id) || $item->model->payment_gateway !== $gatewayKey) {
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
            $item->model->payment_gateway = $gatewayKey;
            $item->model->save();
            $item->model->refresh();
        }
    }

    /**
     * Dispatch job to create order
     *
     * @param mixed $user
     * @param mixed $items
     * @param string $sessionId
     * @param string|null $gateway
     */
    protected function dispatchOrderCreation($user, $items, string $sessionId, ?string $gateway = null): void
    {
        $gateway = $gateway ?? $this->getGatewayConfigKey();
        activity()
            ->withProperties([
                'products' => $items,
                'user_id' => $user->id,
                'payment_gateway' => $gateway,
            ])
            ->causedBy($user)
            ->log('Dispatching order creation job');
        \Skeleton\Store\Jobs\CreateOrderJob::dispatch($user, $items, $sessionId, $gateway);
    }
}
