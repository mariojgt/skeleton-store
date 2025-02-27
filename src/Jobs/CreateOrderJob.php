<?php

namespace Skeleton\Store\Jobs;

use Skeleton\Store\Models\Plan;
use Skeleton\Store\Models\User;
use Skeleton\Store\Models\Order;
use Skeleton\Store\Models\Payment;
use Skeleton\Store\Models\OrderItem;
use Skeleton\Store\Enums\OrderStatus;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Skeleton\Store\Events\UserSubscribedToPlan;
use Mariojgt\SkeletonAdmin\Notifications\GenericNotification;

class CreateOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $products;
    protected $totalAmount;
    protected $stripeSessionId;

    /**
     * Create a new job instance.
     *
     * @param  $user
     * @param  $products
     * @return void
     */
    public function __construct($user, $products, $stripeSessionId)
    {
        $this->user = $user;
        $this->products = $products;
        $this->totalAmount = $this->calculateTotal($products);
        $this->stripeSessionId = $stripeSessionId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Create an order
        $order = Order::create([
            'user_id'      => $this->user->id,
            'total_amount' => $this->totalAmount,
            'status'       => OrderStatus::pending->value,
            'stripe_session_id' => $this->stripeSessionId,
        ]);

        // Create order items
        foreach ($this->products as $product) {
            $orderItem = new OrderItem([
                'order_id' => $order->id,
                'price'    => $product->amount,
                'quantity' => $product->quantity,
            ]);
            // Associate the product (polymorphic relation) and save the order item
            $orderItem->item()->associate($product->model);
            $orderItem->save();
        }

        $this->user->notify(
            new GenericNotification(
                'Thank you for your order',
                'success',
                'Your order has been placed successfully and is pending payment. Please proceed to payment to complete your order.',
                'icon'
            )
        );
    }

    /**
     * Calculate the total order amount.
     *
     * @param  $products
     * @return float
     */
    protected function calculateTotal($products)
    {

        $total = 0;
        foreach ($products as $product) {
            $total += $product->amount * $product->quantity;
        }
        return $total;
    }
}
