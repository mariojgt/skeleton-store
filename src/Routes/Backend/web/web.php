<?php

use Illuminate\Support\Facades\Route;
use Skeleton\Store\Controllers\Backend\BackendHomeController;

// Standard
Route::group([
    'middleware' => ['web'],
    'prefix'     => 'admin',
], function () {
    // Add your normal routes in here
    Route::controller(BackendHomeController::class)->group(function () {
        Route::get('/skeleton-store/backend', 'index')->name('home');
    });
});
