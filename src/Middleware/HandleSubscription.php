<?php

namespace Skeleton\Store\Middleware;

use Closure;
use Illuminate\Http\Request;
use Skeleton\Store\Models\Plan;
use Skeleton\Store\Models\User;
use Skeleton\Store\Factory\Stripe;
use Illuminate\Foundation\Application;
use Skeleton\Store\Enums\PaymentMethod;
use Skeleton\Store\Enums\PaymentStatus;
use Skeleton\Store\Models\StripeSession;
use Mariojgt\GameDev\Jobs\ProcessSubscription;

/**
 * [This middleware will ensure 2 steps verification]
 */
class HandleSubscription
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
                $subscription = $stripe->stripe->subscriptions->retrieve($session->subscription);
                $plan = Plan::where('stripe_price_id', $subscription->plan->id)->first();
                $user = User::find(auth()->user()->id);

                // Save the session as processed (status: completed)
                StripeSession::updateOrCreate(
                    ['session_id' => $request->session_id],
                    [
                        'status' => 'completed',
                        'user_id' => $user->id
                    ]
                );

                $payment = [
                    'user_id'        => $user->id,
                    'total_amount'   => $plan->price,
                    'discount'       => 0,
                    'tax'            => 0,
                    'payment_method' => PaymentMethod::stripe->value,
                    'status'         => PaymentStatus::processing->value,
                    'transaction_id' => $session->payment_intent,
                ];
                ProcessSubscription::dispatchSync($user, $plan, $payment, true, $session->subscription);
                return redirect()->route('home');
            }
        }
        return $next($request);
    }
}
