<?php

namespace Skeleton\Store\Middleware;

use Closure;
use Illuminate\Http\Request;
use Skeleton\Store\Models\Plan;
use Skeleton\Store\Models\User;
use Skeleton\Store\Models\Order;
use Skeleton\Store\Factory\Stripe;
use Skeleton\Store\Jobs\OrderPaidJob;
use Illuminate\Foundation\Application;
use Skeleton\Store\Enums\PaymentMethod;
use Skeleton\Store\Enums\PaymentStatus;
use Skeleton\Store\Models\StripeSession;
use Mariojgt\GameDev\Jobs\ProcessSubscription;

/**
 * [This middleware will ensure 2 steps verification]
 */
class HandleStripePayment
{
    public function __construct(Application $app, Request $request)
    {
        $this->app     = $app;
        $this->request = $request;
    }

    /**
     * Handle an incoming request.
     * This will check if the user has the permission to manage this.
     * Remember session are generate in the server side
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'web')
    {
        if ($request->session_id) {
            $stripe = new Stripe();

            // Check if the session_id has already been processed
            $existingSession = StripeSession::where('session_id', $request->session_id)->first();
            if ($existingSession && $existingSession->status === 'completed') {
                // If session has already been processed, deny the request
                return redirect()->route('home')->with('error', 'This session has already been processed.');
            }

            $session = $stripe->stripe->checkout->sessions->retrieve($request->session_id);

            if ($session->payment_status === 'paid') {
                $user = User::find(auth()->user()->id);
                // Save the session as processed (status: completed)
                StripeSession::updateOrCreate(
                    ['session_id' => $request->session_id],
                    [
                        'status' => 'completed',
                        'user_id' => $user->id
                    ]
                );

                // Check if the product is a one time payment or a subscription
                $order = Order::where('stripe_session_id', $request->session_id)->first();
                $orderItem = $order->orderItems->first()->item;

                // Auto generate the invoice for the order if not already generated
                if (empty($session->invoice)) {
                    $stripe->createInvoiceAndMarkAsPaid($request->session_id, $order);
                }

                // Check if is a subscription
                if ($session->subscription || get_class($orderItem) === Plan::class) {
                    if ($session->subscription) {
                        $subscription = $stripe->stripe->subscriptions->retrieve($session->subscription);
                        $plan = Plan::where('stripe_price_id', $subscription->plan->id)->first();
                    } else {
                        $plan = $orderItem;
                    }

                    $payment = [
                        'user_id'        => $user->id,
                        'total_amount'   => $plan->price,
                        'discount'       => 0,
                        'tax'            => 0,
                        'payment_method' => PaymentMethod::stripe->value,
                        'status'         => PaymentStatus::processing->value,
                        'transaction_id' => $session->payment_intent,
                    ];

                    ProcessSubscription::dispatchSync($user, $plan, $payment, $plan->auto_renew, $session->subscription ?? $session->id);
                }

                // Create the order
                OrderPaidJob::dispatch($session->id);
                return redirect()->route('home');
            }
        }
        return $next($request);
    }
}
