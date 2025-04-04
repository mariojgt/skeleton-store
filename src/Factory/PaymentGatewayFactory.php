<?php

namespace Skeleton\Store\Factory;

use Skeleton\Store\Contracts\PaymentGatewayInterface;
use Skeleton\Store\Gateways\StripeGateway;
// Add other gateway imports here as needed
use InvalidArgumentException;

class PaymentGatewayFactory
{
    /**
     * Create a payment gateway instance
     *
     * @param string|null $gateway
     * @return PaymentGatewayInterface
     */
    public static function create(?string $gateway = null): PaymentGatewayInterface
    {
        $gateway = $gateway ?? config('skeletonStore.payment_gateway.default', 'stripe');

        return match (strtolower($gateway)) {
            'stripe' => new StripeGateway(),
            // Add more gateways here as needed
            // 'paypal' => new PayPalGateway(),
            // 'razorpay' => new RazorpayGateway(),
            default => throw new InvalidArgumentException("Unsupported payment gateway: {$gateway}"),
        };
    }
}
