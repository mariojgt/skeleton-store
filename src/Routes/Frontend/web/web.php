<?php

use Illuminate\Support\Facades\Route;
use Skeleton\Store\Controllers\Frontend\FrontendHomeController;
use Skeleton\Store\Controllers\Frontend\Payment\Stripe\StripeController;

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
    Route::controller(StripeController::class)->group(function () {
        Route::post('/skeleton-store/payment/stripe', 'subscribe')->name('stripe.subscribe');
    });
});
