<?php

namespace Skeleton\Store\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mariojgt\SkeletonAdmin\Notifications\GenericNotification;
use Skeleton\Store\Enums\OrderStatus;
use Skeleton\Store\Events\UserSubscribedToPlan;
use Skeleton\Store\Models\Order;
use Skeleton\Store\Models\Plan;
use Skeleton\Store\Models\User;

class OrderPaidJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Payment session ID
     *
     * @var string
     */
    protected $sessionId;

    /**
     * Payment gateway used
     *
     * @var string|null
     */
    protected $paymentGateway;

    /**
     * Create a new job instance.
     *
     * @param string $sessionId
     * @param string|null $paymentGateway
     * @return void
     */
    public function __construct(string $sessionId, ?string $paymentGateway = null)
    {
        $this->sessionId = $sessionId;
        $this->paymentGateway = $paymentGateway;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Find the order by payment session ID
        $order = $this->findOrder();

        if (!$order) {
            logger()->error("Order not found for payment session: {$this->sessionId}");
            return;
        }

        // Update order status
        $this->updateOrderStatus($order);

        // Process order items if needed
        $this->processOrderItems($order);

        // Notify the user
        $this->notifyUser($order);
    }

    /**
     * Find the order by payment session ID.
     *
     * @return Order|null
     */
    protected function findOrder(): ?Order
    {
        // Try to find by new payment_session_id field first
        $order = Order::where('payment_session_id', $this->sessionId)->first();

        return $order;
    }

    /**
     * Update the order status to completed.
     *
     * @param Order $order
     * @return void
     */
    protected function updateOrderStatus(Order $order): void
    {
        $order->status = OrderStatus::completed->value;
        $order->save();
    }

    /**
     * Process order items after payment.
     * Override this method to implement additional processing.
     *
     * @param Order $order
     * @return void
     */
    protected function processOrderItems(Order $order): void
    {
        // Implement any post-payment processing for order items
        // For example, activating digital products, updating inventory, etc.

        // Check if any items are subscriptions and handle accordingly
        foreach ($order->orderItems as $item) {
            if (get_class($item->item) === Plan::class) {
                $user = $order->user;
                $user = User::find($user->id);
                // If a Plan, trigger subscription event
                event(new UserSubscribedToPlan($user, $item->item, []));
            }
        }
    }

    /**
     * Notify the user about their completed order.
     *
     * @param Order $order
     * @return void
     */
    protected function notifyUser(Order $order): void
    {
        $user = $order->user;

        $user->notify(new GenericNotification(
            'Thank you for your order',
            'success',
            'Your order has been paid successfully, order #' . $order->id,
            'icon',
            true
        ));
    }
}
