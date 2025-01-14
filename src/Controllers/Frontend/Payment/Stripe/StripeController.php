<?php

namespace Skeleton\Store\Controllers\Frontend\Payment\Stripe;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Skeleton\Store\Models\Plan;
use Skeleton\Store\Factory\Stripe;
use App\Helpers\SkeletonStoreHelper;
use App\Http\Controllers\Controller;
use Skeleton\Store\Jobs\CreateOrderJob;
use Skeleton\Store\DataStructure\ProductDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * Handles Stripe payment processing for subscriptions and products
 */
class StripeController extends Controller
{
    /** @var Stripe */
    protected $stripeFactory;

    /**
     * Initialize controller with Stripe factory
     */
    public function __construct()
    {
        $this->stripeFactory = new Stripe();
    }

    /**
     * Process subscription checkout
     *
     * @param Request $request
     * @return array
     * @throws ValidationException
     */
    public function subscriptionCheckout(Request $request)
    {
        $this->validateSubscriptionRequest($request);

        $plan = $this->getPlan($request->plan_id);
        $this->ensurePlanHasStripePrice($plan);

        $lineItems = $this->createLineItems($plan);
        $user = auth()->user();

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
     * Process product checkout
     *
     * @param Request $request
     * @return array
     * @throws ValidationException
     */
    public function productCheckout(Request $request)
    {
        $validatedData = $this->validateProductRequest($request);

        $checkoutItems = $this->processProducts($validatedData['products']);
        $lineItems = $this->createProductLineItems($checkoutItems);

        $user = auth()->user();
        $session = $this->createCheckoutSession(
            $user,
            $lineItems,
            false,
            $this->getSuccessUrl(),
            $this->getCancelUrl()
        );

        $this->dispatchOrderCreation($user, $checkoutItems, $session->id);

        return [
            'session' => $session->url,
        ];
    }

    /**
     * Validate subscription request
     *
     * @param Request $request
     * @throws ValidationException
     */
    protected function validateSubscriptionRequest(Request $request): void
    {
        $request->validate([
            'plan_id' => 'required|integer|exists:plans,id',
        ]);
    }

    /**
     * Validate product request
     *
     * @param Request $request
     * @return array
     * @throws ValidationException
     */
    protected function validateProductRequest(Request $request): array
    {
        return $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|integer',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.type' => 'required|string|in:course,product',
        ]);
    }

    /**
     * Get plan by ID
     *
     * @param int $planId
     * @return Plan
     */
    protected function getPlan(int $planId): Plan
    {
        return Plan::findOrFail($planId);
    }

    /**
     * Ensure plan has a Stripe price ID
     *
     * @param Plan $plan
     */
    protected function ensurePlanHasStripePrice(Plan $plan): void
    {
        if (empty($plan->stripe_price_id)) {
            $paymentId = $plan->auto_renew
                ? $this->createSubscriptionPrice($plan)
                : $this->createOneTimePrice($plan);

            $plan->stripe_price_id = $paymentId->id;
            $plan->save();
            $plan->refresh();
        }
    }

    /**
     * Create subscription price for plan
     *
     * @param Plan $plan
     * @return mixed
     */
    protected function createSubscriptionPrice(Plan $plan)
    {
        return $this->stripeFactory->createPrice(
            $plan->price,
            config('ecommerceStore')['store_currency'],
            $plan->name,
            'month',
            $plan->duration
        );
    }

    /**
     * Create one-time price for plan
     *
     * @param Plan $plan
     * @return mixed
     */
    protected function createOneTimePrice(Plan $plan)
    {
        $stripeProduct = $this->stripeFactory->createProduct(
            $plan->name,
            null,
            false,
            $plan->description
        );

        return $this->stripeFactory->createOneTimePrice(
            $plan->price,
            $stripeProduct->id,
            config('ecommerceStore')['store_currency']
        );
    }

    /**
     * Process products and create checkout items
     *
     * @param array $products
     * @return array
     */
    protected function processProducts(array $products): array
    {
        $checkoutItems = [];
        foreach ($products as $product) {
            $checkoutItem = SkeletonStoreHelper::findProduct($product);
            // Access the nested image structure
            $mainImage = !empty($checkoutItem->media_url[0]) ? $checkoutItem->media_url[0] : null;
            $this->ensureProductHasStripePrice($checkoutItem, $mainImage);
            $checkoutItems[] = $checkoutItem;
        }
        return $checkoutItems;
    }

    /**
     * Ensure product has a Stripe price ID
     *
     * @param ProductDetail $checkoutItem
     */
    protected function ensureProductHasStripePrice(ProductDetail $checkoutItem): void
    {

        if (empty($checkoutItem->model->stripe_price_id)) {
            $stripeProduct = $this->stripeFactory->createProduct(
                $checkoutItem->name,
                $checkoutItem->media_url
            );

            $paymentId = $this->stripeFactory->createOneTimePrice(
                $checkoutItem->amount,
                $stripeProduct->id,
                config('ecommerceStore')['store_currency']
            );

            $checkoutItem->model->stripe_price_id = $paymentId->id;
            $checkoutItem->model->save();
            $checkoutItem->model->refresh();
        }
    }

    /**
     * Create line items for subscription
     *
     * @param Plan $plan
     * @return array
     */
    protected function createLineItems(Plan $plan): array
    {
        return [[
            'price' => $plan->stripe_price_id,
            'quantity' => 1,
        ]];
    }

    /**
     * Create line items for products
     *
     * @param array $checkoutItems
     * @return array
     */
    protected function createProductLineItems(array $checkoutItems): array
    {
        $lineItems = [];
        foreach ($checkoutItems as $index => $checkoutItem) {
            $lineItems[] = [
                'price' => $checkoutItem->model->stripe_price_id,
                'quantity' => $checkoutItem->quantity,
            ];
        }
        return $lineItems;
    }

    /**
     * Create checkout session
     *
     * @param mixed $user
     * @param array $lineItems
     * @param bool $autoRenew
     * @param string $successUrl
     * @param string $cancelUrl
     * @return mixed
     */
    protected function createCheckoutSession($user, array $lineItems, bool $autoRenew, string $successUrl, string $cancelUrl)
    {
        return $this->stripeFactory->createSession(
            $user,
            $lineItems,
            $autoRenew,
            $successUrl,
            $cancelUrl
        );
    }

    /**
     * Dispatch order creation job
     *
     * @param mixed $user
     * @param mixed $items
     * @param string $sessionId
     */
    protected function dispatchOrderCreation($user, $items, string $sessionId): void
    {
        CreateOrderJob::dispatch($user, $items, $sessionId);
    }

    /**
     * Get success URL for checkout
     *
     * @return string
     */
    protected function getSuccessUrl(): string
    {
        return route(config('skeletonStore.payment_gateway.stripe.success_url'))
            . '?session_id={CHECKOUT_SESSION_ID}';
    }

    /**
     * Get cancel URL for checkout
     *
     * @return string
     */
    protected function getCancelUrl(): string
    {
        return route(config('skeletonStore.payment_gateway.stripe.cancel_url'));
    }
}
