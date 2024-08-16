<?php

use Illuminate\Support\Facades\Route;
use Skeleton\Store\Controllers\Frontend\FrontendHomeController;

// Standard
Route::group([
    'middleware' => ['web'],
    'prefix'     => 'skeleton-store',
], function () {
    // Add your normal routes in here
    Route::controller(FrontendHomeController::class)->group(function () {
        Route::get('/skeleton-store/frontend', 'index')->name('store');
    });
});
