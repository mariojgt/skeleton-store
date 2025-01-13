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

class StripeController extends Controller
{
    public function subscriptionCheckout(Request $request)
    {
        $request->validate([
          'plan_id' => 'required|integer|exists:plans,id',
        ]);

        $stripe = new Stripe();
        $plan = Plan::findOrFail($request->plan_id);

        if (empty($plan->stripe_price_id)) {
            if ($plan->auto_renew) {
                $paymentId = $stripe->createPrice($plan->price, config('ecommerceStore')['store_currency'], $plan->name, 'month', $plan->duration);
            } else {
                $stripeProduct = $stripe->createProduct($plan->name, null, false, $plan->description);
                $paymentId = $stripe->createOneTimePrice($plan->price, $stripeProduct->id, config('ecommerceStore')['store_currency']);
            }
            $plan->stripe_price_id = $paymentId->id;
            $plan->save();
            $plan->refresh();
        }

        $lineItems[] = [
            'price' => $plan->stripe_price_id,
            'quantity' => 1,
        ];

        // Get the user
        $user = auth()->user();
        $session = $stripe->createSession(
            $user,
            $lineItems,
            $plan->auto_renew,
            route(config('skeletonStore.payment_gateway.stripe.success_url')) .'?session_id={CHECKOUT_SESSION_ID}',
            route(config('skeletonStore.payment_gateway.stripe.cancel_url'))
        );

        // Checkout items
        $checkoutItems[] = new ProductDetail(
            $plan->name,
            $plan->price,
            $plan,
            1,
            []
        );

        // Create the order
        CreateOrderJob::dispatch($user, $checkoutItems, $session->id);

        return [
            'session' => $session->url,
        ];
    }

    public function productCheckout(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|integer', // Check if plan exists
            'products.*.quantity' => 'required|integer|min:1', // Quantity must be a positive integer
            'products.*.type' => 'required|string|in:course,product', // Type must be course or product
        ]);

        $stripe = new Stripe();
        $products = $validatedData['products'];
        $lineItems = [];
        $checkoutItems = [];

        foreach ($products as $product) {
            $checkoutItem = SkeletonStoreHelper::findProduct($product);
            $checkoutItems[] = $checkoutItem;

            if (empty($checkoutItem->model->stripe_price_id)) {
                $stripeProduct = $stripe->createProduct($checkoutItem->name, $checkoutItem->media_url);
                $paymentId = $stripe->createOneTimePrice($checkoutItem->amount, $stripeProduct->id, config('ecommerceStore')['store_currency']);
                $checkoutItem->model->stripe_price_id = $paymentId->id;
                $checkoutItem->model->save();
            }
            $checkoutItem->model->refresh();

            $lineItems[] = [
                'price' => $checkoutItem->model->stripe_price_id,
                'quantity' => $product['quantity'],
            ];
        }

        $user = auth()->user();
        $session = $stripe->createSession(
            $user,
            $lineItems,
            false,
            route(config('skeletonStore.payment_gateway.stripe.success_url')) .'?session_id={CHECKOUT_SESSION_ID}',
            route(config('skeletonStore.payment_gateway.stripe.cancel_url'))
        );

        // Create the order
        CreateOrderJob::dispatch($user, $checkoutItems, $session->id);

        return [
            'session' => $session->url,
        ];
    }
}
