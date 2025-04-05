<?php

namespace Skeleton\Store\Controllers\Frontend\Payment;

use Illuminate\Http\Request;
use Skeleton\Store\Models\User;
use App\Http\Controllers\Controller;
use Skeleton\Store\Factory\PaymentGatewayFactory;

class PaymentManagementController extends Controller
{
    /**
     * Toggle auto-renew for active subscription
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleAutoRenew(Request $request)
    {
        $user = User::find(auth()->user()->id);
        $subscription = $user->activeSubscription();

        if ($subscription) {
            $subscription->update([
                'auto_renew' => $request['auto_renew'],
            ]);
            return redirect()->back()->with('success', 'Auto renew has been updated');
        }

        return redirect()->back()->with('error', 'No active subscription found');
    }

    /**
     * Cancel active subscription with the payment gateway
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancelSubscription(Request $request)
    {
        $user = User::find(auth()->user()->id);
        $subscription = $user->activeSubscription();

        if (!$subscription) {
            return redirect()->back()->with('error', 'No active subscription found');
        }

        // Get the payment gateway for this subscription
        $gatewayName = $subscription->payment_gateway ?? config('skeletonStore.payment_gateway.default');
        $gateway = PaymentGatewayFactory::create($gatewayName);

        // Cancel the subscription with the payment gateway if it has a subscription ID
        if (!empty($subscription->subscription_id)) {
            try {
                $gateway->cancelSubscription($subscription->subscription_id);
            } catch (\Exception $e) {
                // Log the error but continue with local cancellation
                \Log::error("Error cancelling subscription with {$gatewayName}: " . $e->getMessage());
            }
        }

        // Update local subscription record
        $subscription->update([
            'auto_renew' => false,
        ]);

        return redirect()->back()->with('success', 'Subscription has been canceled');
    }

    /**
     * Redirect to payment gateway's billing portal to update payment method
     *
     * @param Request $request
     * @return string JSON encoded redirect URL
     */
    public function updatePaymentMethod(Request $request)
    {
        $user = User::find(auth()->user()->id);

        // Get the appropriate payment gateway (use the one from active subscription if available)
        $gatewayName = $request->input('payment_gateway');

        if (!$gatewayName) {
            $activeSubscription = $user->activeSubscription();
            $gatewayName = $activeSubscription ? $activeSubscription->payment_gateway : null;
        }

        $gateway = PaymentGatewayFactory::create($gatewayName);

        // Create billing portal session
        $portal = $gateway->createBillingPortalSession($user, route('home'));

        return json_encode([
            'session' => $portal->url,
        ]);
    }
}
