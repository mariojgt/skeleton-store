<?php

namespace Skeleton\Store\Jobs;

use Skeleton\Store\Models\User;
use Skeleton\Store\Models\Order;
use Skeleton\Store\Models\OrderItem;
use Skeleton\Store\Enums\OrderStatus;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Mariojgt\SkeletonAdmin\Notifications\GenericNotification;

class CreateOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * User who placed the order
     *
     * @var User
     */
    protected $user;

    /**
     * Products in the order
     *
     * @var array
     */
    protected $products;

    /**
     * Total order amount
     *
     * @var float
     */
    protected $totalAmount;

    /**
     * Payment session ID
     *
     * @var string
     */
    protected $sessionId;

    /**
     * Payment gateway used
     *
     * @var string
     */
    protected $paymentGateway;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param array $products
     * @param string $sessionId
     * @param string|null $paymentGateway
     * @return void
     */
    public function __construct($user, $products, string $sessionId, ?string $paymentGateway = null)
    {
        $this->user = $user;
        $this->products = $products;
        $this->totalAmount = $this->calculateTotal($products);
        $this->sessionId = $sessionId;
        $this->paymentGateway = $paymentGateway ?? config('skeletonStore.payment_gateway.default', 'stripe');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Calculate financial details
        $subtotal = $this->calculateSubtotal($this->products);
        $tax = $this->calculateTax($subtotal);
        $discount = $this->calculateDiscount($subtotal);
        $totalAmount = $subtotal + $tax - $discount;

        // Create an order
        $order = Order::create([
            'user_id' => $this->user->id,
            'total_amount' => $totalAmount,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'status' => OrderStatus::pending->value,
            'payment_session_id' => $this->sessionId,
            'payment_gateway' => $this->paymentGateway,
        ]);

        // Create order items
        $this->createOrderItems($order);

        // Notify the user
        $this->notifyUser();
    }

    /**
     * Calculate the total order amount.
     *
     * @param array $products
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

    /**
     * Calculate the subtotal (before tax and discounts).
     *
     * @param array $products
     * @return float
     */
    protected function calculateSubtotal($products)
    {
        return $this->calculateTotal($products);
    }

    /**
     * Calculate tax amount.
     * Override this method to implement tax calculations.
     *
     * @param float $subtotal
     * @return float
     */
    protected function calculateTax(float $subtotal): float
    {
        // Implement your tax calculation logic here
        // For example: return $subtotal * 0.1; // 10% tax
        return 0;
    }

    /**
     * Calculate discount amount.
     * Override this method to implement discount calculations.
     *
     * @param float $subtotal
     * @return float
     */
    protected function calculateDiscount(float $subtotal): float
    {
        // Implement your discount calculation logic here
        // For example: return $subtotal * 0.05; // 5% discount
        return 0;
    }

    /**
     * Create order items for the order.
     *
     * @param Order $order
     * @return void
     */
    protected function createOrderItems(Order $order): void
    {
        foreach ($this->products as $product) {
            $orderItem = new OrderItem([
                'order_id' => $order->id,
                'price' => $product->amount,
                'quantity' => $product->quantity,
                'total' => $product->amount * $product->quantity,
                'name' => $product->name ?? 'Product',
            ]);

            // Associate the product (polymorphic relation) and save the order item
            $orderItem->item()->associate($product->model);
            $orderItem->save();
        }
    }

    /**
     * Notify the user about their order.
     *
     * @return void
     */
    protected function notifyUser(): void
    {
        $this->user->notify(
            new GenericNotification(
                'Thank you for your order',
                'success',
                'Your order has been placed successfully and is pending payment. Please proceed to payment to complete your order.',
                'icon'
            )
        );
    }
}
