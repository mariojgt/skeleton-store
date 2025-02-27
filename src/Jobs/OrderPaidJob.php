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

class OrderPaidJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $stripeSessionId;

    /**
     * Create a new job instance.
     *
     * @param  $user
     * @param  $products
     * @return void
     */
    public function __construct($stripeSessionId)
    {
        $this->stripeSessionId = $stripeSessionId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $order = Order::where('stripe_session_id', $this->stripeSessionId)->firstOrFail();
        $order->status = OrderStatus::completed->value;
        $order->save();

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
