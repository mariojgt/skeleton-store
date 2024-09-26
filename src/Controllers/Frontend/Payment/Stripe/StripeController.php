<?php

namespace Skeleton\Store\Controllers\Frontend\Payment\Stripe;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Skeleton\Store\Models\Plan;
use Skeleton\Store\Factory\Stripe;
use App\Http\Controllers\Controller;

class StripeController extends Controller
{
    /**
     * @return [blade view]
     */
    public function subscribe(Request $request)
    {
        $stripe = new Stripe();
        $plan = Plan::find($request->plan_id);

        if ($plan->stripe_price_id === null) {
            $paymentId = $stripe->createPrice($plan->price, 'gbp', $plan->name);
            $plan->stripe_price_id = $paymentId->id;
            $plan->save();
            $plan->refresh();
        }

        $session = $stripe->createSession(
            $plan->stripe_price_id,
            route(config('skeletonStore.payment_gateway.stripe.success_url')) .'?session_id={CHECKOUT_SESSION_ID}',
            route(config('skeletonStore.payment_gateway.stripe.cancel_url'))
        );
        return [
            'session' => $session->url,
        ];
    }
}
