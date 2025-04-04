<?php

namespace Skeleton\Store\Controllers\Frontend\Payment;

use Illuminate\Http\Request;
use Skeleton\Store\Models\Plan;
use App\Helpers\SkeletonStoreHelper;
use App\Http\Controllers\Controller;
use Skeleton\Store\DataStructure\ProductDetail;
use Skeleton\Store\Factory\PaymentGatewayFactory;
use Illuminate\Validation\ValidationException;

/**
 * Handles payment processing for subscriptions and products
 */
class PaymentController extends Controller
{
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
        $user = auth()->user();

        // Get the preferred payment gateway (from request or default)
        $gatewayName = $request->input('payment_gateway', config('skeletonStore.payment_gateway.default'));

        $gateway = PaymentGatewayFactory::create($gatewayName);

        return $gateway->processSubscriptionCheckout($plan, $user);
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
        $user = auth()->user();

        // Get the preferred payment gateway (from request or default)
        $gatewayName = $request->input('payment_gateway', config('skeletonStore.payment_gateway.default'));

        $gateway = PaymentGatewayFactory::create($gatewayName);

        return $gateway->processProductCheckout($checkoutItems, $user);
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
            'payment_gateway' => 'sometimes|string',
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
        return         $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|integer',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.type' => 'required|string|in:course,product',
            'payment_gateway' => 'sometimes|string',
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
            $checkoutItems[] = $checkoutItem;
        }

        return $checkoutItems;
    }
}
