<?php

namespace Skeleton\Store\Controllers\Frontend\Payment\Stripe;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Skeleton\Store\Models\Plan;
use Skeleton\Store\Factory\Stripe;
use App\Helpers\SkeletonStoreHelper;
use App\Http\Controllers\Controller;

class StripeController extends Controller
{
    public function subscribe(Request $request)
    {
        $stripe = new Stripe();
        $plan = Plan::findOrFail($request->plan_id);

        if (empty($plan->stripe_price_id)) {
            $paymentId = $stripe->createPrice($plan->price, 'gbp', $plan->name, 'month', $plan->duration);
            $plan->stripe_price_id = $paymentId->id;
            $plan->save();
            $plan->refresh();
        }

        // Get the user
        $user = auth()->user();
        $session = $stripe->createSubscriptionSession(
            $user,
            $plan->stripe_price_id,
            route(config('skeletonStore.payment_gateway.stripe.success_url')) .'?session_id={CHECKOUT_SESSION_ID}',
            route(config('skeletonStore.payment_gateway.stripe.cancel_url'))
        );
        return [
            'session' => $session->url,
        ];
    }

    public function productCheckout(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|integer|exists:plans,id', // Check if plan exists
            'products.*.quantity' => 'required|integer|min:1', // Quantity must be a positive integer
            'products.*.type' => 'required|string|in:course', // Assuming 'type' can only be 'course'
        ]);

        $stripe = new Stripe();
        $products = $validatedData['products'];
        $lineItems = [];

        foreach ($products as $product) {
            $checkoutItem = SkeletonStoreHelper::findProduct($product);
            if (empty($checkoutItem['model']->stripe_price_id)) {
                $stripeProduct = $stripe->createProduct($checkoutItem['name'], $checkoutItem['media_url']);
                $paymentId = $stripe->createOneTimePrice($checkoutItem['amount'], $stripeProduct->id, 'gbp');
                $checkoutItem['model']->stripe_price_id = $paymentId->id;
                $checkoutItem['model']->save();
            }
            $checkoutItem['model']->refresh();
            $lineItems[] = [
                'price' => $checkoutItem['model']->stripe_price_id,
                'quantity' => $product['quantity'],
            ];
        }

        $user = auth()->user();
        $session = $stripe->createCheckoutSession(
            $user,
            $lineItems,
            route(config('skeletonStore.payment_gateway.stripe.success_url')) .'?session_id={CHECKOUT_SESSION_ID}',
            route(config('skeletonStore.payment_gateway.stripe.cancel_url'))
        );

        return [
            'session' => $session->url,
        ];
    }

}
