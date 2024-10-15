<?php

namespace Skeleton\Store\Controllers\Frontend\Payment\Stripe;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Skeleton\Store\Models\Plan;
use Skeleton\Store\Factory\Stripe;
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
        // Todo complete this tomorrow
        dd($request);
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
}
