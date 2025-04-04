<?php

namespace Skeleton\Store\Gateways;

use Skeleton\Store\Contracts\PaymentGatewayInterface;
use Skeleton\Store\Models\Plan;
use Skeleton\Store\Jobs\CreateOrderJob;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    /**
     * Dispatch job to create order
     *
     * @param mixed $user
     * @param mixed $items
     * @param string $sessionId
     */
    protected function dispatchOrderCreation($user, $items, string $sessionId): void
    {
        CreateOrderJob::dispatch($user, $items, $sessionId);
    }

    /**
     * Get success URL from configuration
     *
     * @return string
     */
    public function getSuccessUrl(): string
    {
        $gatewayKey = $this->getGatewayConfigKey();
        return route(config("skeletonStore.payment_gateway.{$gatewayKey}.success_url"))
            . '?session_id={CHECKOUT_SESSION_ID}';
    }

    /**
     * Get cancel URL from configuration
     *
     * @return string
     */
    public function getCancelUrl(): string
    {
        $gatewayKey = $this->getGatewayConfigKey();
        return route(config("skeletonStore.payment_gateway.{$gatewayKey}.cancel_url"));
    }

    /**
     * Get the gateway configuration key
     *
     * @return string
     */
    abstract protected function getGatewayConfigKey(): string;
}
