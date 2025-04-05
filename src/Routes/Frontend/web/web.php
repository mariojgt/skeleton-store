<?php

use Illuminate\Support\Facades\Route;
use Skeleton\Store\Controllers\Frontend\FrontendHomeController;
use Skeleton\Store\Controllers\Frontend\Payment\PaymentController;
use Skeleton\Store\Controllers\Frontend\Payment\Stripe\StripeController;
use Skeleton\Store\Controllers\Frontend\Payment\PaymentManagementController;

// Standard
Route::group([
    'middleware' => ['web'],
    'prefix'     => 'skeleton-store',
], function () {
    // Add your normal routes in here
    // Route::controller(FrontendHomeController::class)->group(function () {
    //     Route::get('/skeleton-store/frontend', 'index')->name('store');
    // });
});


Route::group([
    'middleware' => ['web', 'auth', 'verified'],
    'prefix' => config('skeleton.route_prefix_front'),
], function () {
    Route::controller(PaymentController::class)->group(function () {
        // Generic payment routes that work with any gateway
        Route::post('/skeleton-store/payment/subscription', 'subscriptionCheckout')->name('payment.subscribe');
        Route::post('/skeleton-store/payment/product', 'productCheckout')->name('payment.product.checkout');

        // Keep legacy routes for backward compatibility
        Route::post('/skeleton-store/payment/stripe', 'subscriptionCheckout')->name('stripe.subscribe');
        Route::post('/skeleton-store/payment/stripe/checkout', 'productCheckout')->name('stripe.product.checkout');
    });

    // Payment management routes
    Route::prefix('payment')->group(function () {
        // Update payment method
        Route::post('/update-payment-method', [PaymentManagementController::class, 'updatePaymentMethod'])
            ->name('payment.update-method');
        // Toggle auto renew
        Route::post('/toggle-auto-renew', [PaymentManagementController::class, 'toggleAutoRenew'])
            ->name('payment.toggle-auto-renew');
        // Cancel subscription
        Route::post('/cancel-subscription', [PaymentManagementController::class, 'cancelSubscription'])
            ->name('payment.cancel-subscription');
    });
});
