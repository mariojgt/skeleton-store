<?php

namespace Skeleton\Store\Middleware;

use Closure;
use Illuminate\Http\Request;
use Skeleton\Store\Models\Plan;
use Skeleton\Store\Models\User;
use Skeleton\Store\Models\Order;
use Illuminate\Support\Facades\DB;
use Skeleton\Store\Jobs\OrderPaidJob;
use Illuminate\Foundation\Application;
use Skeleton\Store\Enums\PaymentMethod;
use Skeleton\Store\Enums\PaymentStatus;
use Skeleton\Store\Models\PaymentSession;
use Mariojgt\GameDev\Jobs\ProcessSubscription;
use Skeleton\Store\Factory\PaymentGatewayFactory;

/**
 * Payment handler middleware to process completed payments
 */
class PaymentHandlerMiddleware
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Constructor
     *
     * @param Application $app
     * @param Request $request
     */
    public function __construct(Application $app, Request $request)
    {
        $this->app = $app;
        $this->request = $request;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'web')
    {
        $sessionId = $request->session_id;

        if (empty($sessionId)) {
            return $next($request);
        }

        // Check if the session has already been processed
        if ($this->isSessionAlreadyProcessed($sessionId)) {
            return redirect()->route('home')->with('error', 'This payment session has already been processed.');
        }

        // Get the payment gateway from the order
        $order = Order::where('payment_session_id', $sessionId)->first();

        if (!$order) {
            return redirect()->route('home')->with('error', 'Payment session not found.');
        }

        DB::beginTransaction();
        $gatewayName = $order->payment_gateway ?? config('skeletonStore.payment_gateway.default');
        $gateway = PaymentGatewayFactory::create($gatewayName);

        $sessionDetails = $gateway->retrieveSession($sessionId);

        if (!$sessionDetails || $sessionDetails->payment_status !== 'paid') {
            return $next($request);
        }

        // Process the paid session
        $user = auth()->user();
        $user = User::find($user->id);

        // Mark the session as processed
        $this->markSessionAsProcessed($sessionId, $user->id);

        // Generate invoice if needed
        $this->generateInvoiceIfNeeded($gateway, $sessionId, $order, $sessionDetails);

        // Handle subscription if applicable
        $this->handleSubscription($user, $order, $sessionDetails, $gateway);

        // Process the order
        OrderPaidJob::dispatch($sessionId);

        DB::commit();

        return redirect()->route('home')->with('success', 'Payment completed successfully.');
    }

    /**
     * Check if a session has already been processed
     *
     * @param string $sessionId
     * @return bool
     */
    protected function isSessionAlreadyProcessed(string $sessionId): bool
    {
        $existingSession = PaymentSession::where('session_id', $sessionId)->first();

        return $existingSession && $existingSession->status === 'completed';
    }

    /**
     * Mark a session as processed
     *
     * @param string $sessionId
     * @param int $userId
     * @return void
     */
    protected function markSessionAsProcessed(string $sessionId, int $userId): void
    {
        PaymentSession::updateOrCreate(
            ['session_id' => $sessionId],
            [
                'status' => 'completed',
                'user_id' => $userId
            ]
        );
    }

    /**
     * Generate invoice if needed
     *
     * @param mixed $gateway
     * @param string $sessionId
     * @param Order $order
     * @param mixed $sessionDetails
     * @return void
     */
    protected function generateInvoiceIfNeeded($gateway, string $sessionId, Order $order, $sessionDetails): void
    {
        if (empty($sessionDetails->invoice) && method_exists($gateway, 'createInvoiceAndMarkAsPaid')) {
            $gateway->createInvoiceAndMarkAsPaid($sessionId, $order);
        }
    }

    /**
     * Handle subscription if applicable
     *
     * @param User $user
     * @param Order $order
     * @param mixed $sessionDetails
     * @param mixed $gateway
     * @return void
     */
    protected function handleSubscription(User $user, Order $order, $sessionDetails, $gateway): void
    {
        $orderItem = $order->orderItems->first()->item;
        $isSubscription = $sessionDetails->subscription || get_class($orderItem) === Plan::class;

        if (!$isSubscription) {
            return;
        }

        $plan = $this->determinePlan($sessionDetails, $orderItem, $gateway);

        if (!$plan) {
            return;
        }

        $payment = [
            'user_id' => $user->id,
            'total_amount' => $plan->price,
            'discount' => 0,
            'tax' => 0,
            'payment_method' => $this->determinePaymentMethod($order->payment_gateway),
            'status' => PaymentStatus::processing->value,
            'transaction_id' => $sessionDetails->payment_intent,
        ];

        $subscriptionId = $sessionDetails->subscription ?? $sessionDetails->id;

        ProcessSubscription::dispatchSync($user, $plan, $payment, $plan->auto_renew, $subscriptionId);
    }

    /**
     * Determine the plan for a subscription
     *
     * @param mixed $sessionDetails
     * @param mixed $orderItem
     * @param mixed $gateway
     * @return Plan|null
     */
    protected function determinePlan($sessionDetails, $orderItem, $gateway): ?Plan
    {
        if ($sessionDetails->subscription) {
            $subscriptionDetails = $gateway->retrieveSubscription($sessionDetails->subscription);
            return Plan::where('gateway_price_id', $subscriptionDetails->plan->id)
                ->where('payment_gateway', $orderItem->payment_gateway)
                ->first();
        }

        return get_class($orderItem) === Plan::class ? $orderItem : null;
    }

    /**
     * Determine payment method based on gateway
     *
     * @param string $gateway
     * @return string
     */
    protected function determinePaymentMethod(string $gateway): string
    {
        return match($gateway) {
            'stripe' => PaymentMethod::stripe->value,
            'paypal' => PaymentMethod::paypal->value,
            default => PaymentMethod::other->value
        };
    }
}
